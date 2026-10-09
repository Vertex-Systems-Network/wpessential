<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetImportPreflightService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityMappingPreviewService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityMappingPreviewAbilityHandler;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetPresetPortabilityMappingPreviewServiceTest extends TestCase
{
    public function testDeterministicCrossSiteMappingProducesCandidateOnlyAndNeverWrites(): void
    {
        $repo = $this->repo();
        $beforeA = $repo->get(self::TARGET_A);
        $beforeB = $repo->get(self::TARGET_B);
        $service = $this->service($repo);
        $snapshot = $this->snapshot();
        $first = $service->preview($snapshot, self::TARGET_PRESET, $this->map());

        self::assertSame('valid_candidate', $first['status']);
        self::assertFalse($first['applicable']);
        self::assertNotNull($first['candidate_snapshot']);
        self::assertSame($first, $service->preview($snapshot, self::TARGET_PRESET, $this->map()));
        $candidate = $first['candidate_snapshot'];
        self::assertSame(['format', 'version', 'payload', 'sha256'], array_keys($candidate));
        self::assertSame(self::TARGET_PRESET, $candidate['payload']['definition_id']);
        self::assertSame(2, $candidate['payload']['revision']);
        self::assertSame([self::TARGET_B, self::TARGET_A], $candidate['payload']['widget_definition_ids']);
        self::assertSame(['administrator', 'editor'], $candidate['payload']['assignment']['roles']);
        self::assertSame(hash('sha256', json_encode(
            $candidate['payload'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        )), $candidate['sha256']);
        self::assertNull($repo->get(self::TARGET_PRESET));
        self::assertSame($beforeA, $repo->get(self::TARGET_A));
        self::assertSame($beforeB, $repo->get(self::TARGET_B));
    }

    public function testInvalidSourceOrMappingFailsClosedWithNoCandidate(): void
    {
        $service = $this->service($this->repo());
        $base = $this->snapshot();
        $invalid = [];
        $invalid[] = [$base, self::TARGET_PRESET, [self::SOURCE_A => self::TARGET_A]];
        $invalid[] = [$base, self::TARGET_PRESET, [
            self::SOURCE_A => self::TARGET_A, self::SOURCE_B => self::TARGET_A,
        ]];
        $invalid[] = [$base, self::TARGET_PRESET, [
            self::SOURCE_A => self::TARGET_A,
            'cccccccc-cccc-4ccc-8ccc-cccccccccccc' => self::TARGET_B,
        ]];
        $invalid[] = [$base, self::TARGET_PRESET, [
            self::SOURCE_A => self::TARGET_A, self::SOURCE_B => 'invalid',
        ]];
        $invalid[] = [$base, strtoupper(self::SOURCE_A), $this->map()];
        $extra = $base;
        $extra['import'] = true;
        $invalid[] = [$extra, self::TARGET_PRESET, $this->map()];
        $badVersion = $base;
        $badVersion['version'] = 2;
        $invalid[] = [$badVersion, self::TARGET_PRESET, $this->map()];
        $overlong = $base['payload'];
        $overlong['label'] = str_repeat('a', 161);
        $invalid[] = [$this->signed($overlong), self::TARGET_PRESET, $this->map()];
        $roleOrder = $base['payload'];
        $roleOrder['assignment']['roles'] = ['editor', 'administrator'];
        $invalid[] = [$this->signed($roleOrder), self::TARGET_PRESET, $this->map()];
        $duplicateRef = $base['payload'];
        $duplicateRef['widget_definition_ids'] = [self::SOURCE_A, self::SOURCE_A];
        $invalid[] = [$this->signed($duplicateRef), self::TARGET_PRESET, $this->map()];
        $badAssignment = $base['payload'];
        $badAssignment['assignment']['network_default'] = true;
        $invalid[] = [$this->signed($badAssignment), self::TARGET_PRESET, $this->map()];
        $extraKey = $base['payload'];
        $extraKey['remote_url'] = 'https://untrusted.example';
        $invalid[] = [$this->signed($extraKey), self::TARGET_PRESET, $this->map()];
        $wrongId = $base['payload'];
        $wrongId['definition_id'] = 'invalid';
        $invalid[] = [$this->signed($wrongId), self::TARGET_PRESET, $this->map()];
        foreach ($invalid as [$envelope, $targetId, $mapping]) {
            self::assertSame(
                ['status' => 'invalid_snapshot', 'applicable' => false, 'candidate_snapshot' => null],
                $service->preview($envelope, $targetId, $mapping),
            );
        }

        $wrongHash = $base;
        $wrongHash['sha256'] = str_repeat('0', 64);
        self::assertSame(
            ['status' => 'integrity_mismatch', 'applicable' => false, 'candidate_snapshot' => null],
            $service->preview($wrongHash, self::TARGET_PRESET, $this->map()),
        );
    }

    public function testMissingDraftForeignOrConflictingDestinationNeverLeaksCandidate(): void
    {
        $repo = $this->repo();
        $service = $this->service($repo);
        $repo->save(new Definition(
            id: self::TARGET_PRESET, slug: 'existing-preset', type: 'dashboard-widget-preset',
            schemaVersion: 1, ownerSurfaceId: 10, status: DefinitionStatus::Published,
            payload: ['preset' => []],
        ));
        self::assertSame(
            ['status' => 'id_conflict', 'applicable' => false, 'candidate_snapshot' => null],
            $service->preview($this->snapshot(), self::TARGET_PRESET, $this->map()),
        );

        $repo2 = $this->repo();
        $repo2->save($this->widget('55555555-5555-4555-8555-555555555555', 'draft', DefinitionStatus::Draft));
        $repo2->save($this->widget('66666666-6666-4666-8666-666666666666', 'foreign', owner: 9));
        foreach ([
            '55555555-5555-4555-8555-555555555555',
            '66666666-6666-4666-8666-666666666666',
            '77777777-7777-4777-8777-777777777777',
        ] as $notPublished) {
            $map = $this->map();
            $map[self::SOURCE_B] = $notPublished;
            self::assertSame(
                ['status' => 'invalid_snapshot', 'applicable' => false, 'candidate_snapshot' => null],
                $this->service($repo2)->preview($this->snapshot(), self::TARGET_PRESET, $map),
            );
        }
        self::assertNull($repo2->get(self::TARGET_PRESET));
    }

    private const SOURCE_A = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    private const SOURCE_B = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
    private const TARGET_A = '11111111-1111-4111-8111-111111111111';
    private const TARGET_B = '22222222-2222-4222-8222-222222222222';
    private const TARGET_PRESET = '33333333-3333-4333-8333-333333333333';

    private function repo(): InMemoryDefinitionRepository
    {
        $repo = new InMemoryDefinitionRepository();
        foreach ([self::TARGET_A, self::TARGET_B] as $index => $id) {
            $repo->save($this->widget($id, 'widget-' . $index));
        }

        return $repo;
    }

    private function widget(
        string $id,
        string $slug,
        DefinitionStatus $status = DefinitionStatus::Published,
        int $owner = 10,
    ): Definition {
        return new Definition(
            id: $id,
            slug: $slug,
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: $owner,
            status: $status,
            payload: [],
        );
    }

    /** @return array<string,mixed> */
    private function snapshot(): array
    {
        $payload = [
            'definition_id' => '44444444-4444-4444-8444-444444444444',
            'revision' => 2,
            'label' => 'Cross Site Preset',
            'widget_definition_ids' => [self::SOURCE_B, self::SOURCE_A],
            'assignment' => [
                'roles' => ['administrator', 'editor'],
                'network_default' => false,
            ],
        ];

        return $this->signed($payload);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    private function signed(array $payload): array
    {
        return [
            'format' => 'wpessential-dashboard-preset',
            'version' => 1,
            'payload' => $payload,
            'sha256' => hash('sha256', json_encode(
                $payload,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            )),
        ];
    }

    /** @return array<string,string> */
    private function map(): array
    {
        return [self::SOURCE_A => self::TARGET_A, self::SOURCE_B => self::TARGET_B];
    }

    private function service(InMemoryDefinitionRepository $repo): DashboardWidgetPresetPortabilityMappingPreviewService
    {
        return new DashboardWidgetPresetPortabilityMappingPreviewService(
            new DashboardWidgetPresetImportPreflightService($repo, new DashboardWidgetPresetCompiler($repo)),
        );
    }
}
