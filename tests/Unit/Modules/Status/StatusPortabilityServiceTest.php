<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Status;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Status\Portability\StatusPortabilityService;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class StatusPortabilityServiceTest extends TestCase
{
    private const STATUS_ID = '11111111-1111-4111-8111-111111111111';
    private const POLICY_ID = '22222222-2222-4222-8222-222222222222';

    public function testExportIsDeterministicAndImportIsCreateSafeIdempotent(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $service = new StatusPortabilityService($repository);
        $status = $this->statusDefinition();
        $policy = $this->policyDefinition();

        $first = $service->export([$policy, $status]);
        $second = $service->export([$status, $policy]);

        self::assertSame($first, $second);
        self::assertSame(['status', 'status-transition-policy'], array_column($first['definitions'], 'type'));
        self::assertArrayNotHasKey('posts', $first);
        self::assertArrayNotHasKey('site_id', $first);

        $imported = $service->import($first);
        self::assertCount(2, $imported);
        self::assertSame(1, $repository->get(self::STATUS_ID)?->revision);
        self::assertSame('review-ready', $repository->get(self::STATUS_ID)?->payload['key']);

        $again = $service->import($first);
        self::assertSame($repository->get(self::STATUS_ID), $again[0]);
        self::assertCount(1, $repository->byType('status'));
        self::assertCount(1, $repository->byType('status-transition-policy'));
    }

    public function testTamperedPackageFailsBeforePersistence(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $service = new StatusPortabilityService($repository);
        $package = $service->export([$this->statusDefinition()]);
        $package['definitions'][0]['payload']['labels']['label'] = 'Tampered';

        $this->expectException(InvalidArgumentException::class);
        try {
            $service->import($package);
        } finally {
            self::assertNull($repository->get(self::STATUS_ID));
        }
    }

    public function testDivergentSameIdFailsClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->statusDefinition(label: 'Local Label'));
        $service = new StatusPortabilityService($repository);
        $package = $service->export([$this->statusDefinition(label: 'Imported Label')]);

        $this->expectException(InvalidArgumentException::class);
        $service->import($package);
    }

    public function testDifferentDefinitionCannotReuseExistingStatusKey(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->statusDefinition());
        $service = new StatusPortabilityService($repository);
        $other = $this->statusDefinition(
            id: '33333333-3333-4333-8333-333333333333',
            slug: 'other-review',
        );
        $package = $service->export([$other]);

        $this->expectException(InvalidArgumentException::class);
        $service->import($package);
    }

    public function testTransitionEdgeCollisionAcrossDefinitionsFailsClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->policyDefinition());
        $service = new StatusPortabilityService($repository);
        $other = $this->policyDefinition(
            id: '44444444-4444-4444-8444-444444444444',
            slug: 'other-policy',
        );
        $package = $service->export([$other]);

        $this->expectException(InvalidArgumentException::class);
        $service->import($package);
    }

    public function testExecutablePayloadChannelIsRejectedAtExport(): void
    {
        $payload = $this->statusPayload();
        $payload['labels']['label'] = '<script>alert(1)</script>';
        $definition = new Definition(
            id: self::STATUS_ID,
            slug: 'review-ready',
            type: 'status',
            schemaVersion: 1,
            ownerSurfaceId: 5,
            status: DefinitionStatus::Draft,
            payload: $payload,
        );

        $this->expectException(InvalidArgumentException::class);
        (new StatusPortabilityService(new InMemoryDefinitionRepository()))->export([$definition]);
    }

    private function statusDefinition(
        string $id = self::STATUS_ID,
        string $slug = 'review-ready',
        string $label = 'Review Ready',
    ): Definition {
        $payload = $this->statusPayload();
        $payload['labels']['label'] = $label;
        return new Definition(
            id: $id,
            slug: $slug,
            type: 'status',
            schemaVersion: 1,
            ownerSurfaceId: 5,
            status: DefinitionStatus::Published,
            payload: $payload,
            revision: 4,
        );
    }

    private function policyDefinition(
        string $id = self::POLICY_ID,
        string $slug = 'editorial-policy',
    ): Definition {
        return new Definition(
            id: $id,
            slug: $slug,
            type: 'status-transition-policy',
            schemaVersion: 1,
            ownerSurfaceId: 5,
            status: DefinitionStatus::Published,
            payload: [
                'edges' => [[
                    'from' => 'draft',
                    'to' => 'review-ready',
                    'capability' => 'edit_posts',
                    'reason_required' => true,
                    'bulk_allowed' => false,
                    'programmatic_allowed' => false,
                ]],
            ],
            revision: 2,
        );
    }

    /** @return array<string,mixed> */
    private function statusPayload(): array
    {
        return [
            'key' => 'review-ready',
            'labels' => [
                'label' => 'Review Ready',
                'count_singular' => 'Review Ready',
                'count_plural' => 'Review Ready',
            ],
            'visibility' => ['protected' => true],
            'post_types' => ['post'],
        ];
    }
}
