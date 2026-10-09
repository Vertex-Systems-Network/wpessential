<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetImportPreflightService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetImportPreflightAbilityHandler;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetPresetImportPreflightServiceTest extends TestCase
{
    public function testBoundedPublishedCandidateIsCompatibleWithoutImportingOrWriting(): void
    {
        $repo = $this->repository();
        $service = $this->service($repo);
        $beforePreset = $repo->get(self::SOURCE);
        $beforeWidget = $repo->get(self::WIDGET);
        $snapshot = $this->snapshot();

        self::assertSame(['status' => 'valid_candidate', 'applicable' => false], $service->preflight($snapshot));
        self::assertSame(['status' => 'valid_candidate', 'applicable' => false], $service->preflight($snapshot));
        self::assertNull($repo->get(self::CANDIDATE));
        self::assertSame($beforePreset, $repo->get(self::SOURCE));
        self::assertSame($beforeWidget, $repo->get(self::WIDGET));
    }

    public function testExistingPresetDefinitionIdIsAdvisoryConflictOnly(): void
    {
        $repo = $this->repository();
        $snapshot = $this->snapshot(self::SOURCE);
        self::assertSame(['status' => 'id_conflict', 'applicable' => false], $this->service($repo)->preflight($snapshot));
        self::assertNotNull($repo->get(self::SOURCE));
    }

    public function testWellFormedPayloadWithWrongFingerprintFailsIntegrityOnly(): void
    {
        $repo = $this->repository();
        $bad = $this->snapshot();
        $bad['sha256'] = str_repeat('0', 64);
        self::assertSame(['status' => 'integrity_mismatch', 'applicable' => false], $this->service($repo)->preflight($bad));
    }

    public function testInvalidContractAndUnknownOrUnpublishedWidgetReferencesFailClosed(): void
    {
        $repo = $this->repository();
        $service = $this->service($repo);
        $base = $this->snapshot();
        $cases = [];

        $unknown = $base;
        $unknown['extra'] = 'inject';
        $cases[] = $unknown;
        $wrongVersion = $base;
        $wrongVersion['version'] = 2;
        $cases[] = $wrongVersion;
        $wrongFormat = $base;
        $wrongFormat['format'] = 'different';
        $cases[] = $wrongFormat;
        $uppercaseHash = $base;
        $uppercaseHash['sha256'] = strtoupper($base['sha256']);
        // Uppercase of a numerical-only digest is a no-op; force a letter.
        $uppercaseHash['sha256'] = str_repeat('A', 64);
        $cases[] = $uppercaseHash;
        $extraPayload = $base['payload'];
        $extraPayload['source_url'] = 'https://untrusted.example';
        $cases[] = $this->signPayload($extraPayload);
        $overlongLabel = $base['payload'];
        $overlongLabel['label'] = str_repeat('a', 161);
        $cases[] = $this->signPayload($overlongLabel);
        $markupLabel = $base['payload'];
        $markupLabel['label'] = '<h1>';
        $cases[] = $this->signPayload($markupLabel);
        $mixedRoles = $base['payload'];
        $mixedRoles['assignment'] = ['roles' => ['editor'], 'network_default' => true];
        $cases[] = $this->signPayload($mixedRoles);
        $duplicate = $base['payload'];
        $duplicate['widget_definition_ids'] = [self::WIDGET, self::WIDGET];
        $cases[] = $this->signPayload($duplicate);
        $missing = $base['payload'];
        $missing['widget_definition_ids'] = ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'];
        $cases[] = $this->signPayload($missing);
        $notSorted = $base['payload'];
        $notSorted['assignment']['roles'] = ['editor', 'administrator'];
        $cases[] = $this->signPayload($notSorted);
        $overflow = $base['payload'];
        $overflow['widget_definition_ids'] = array_fill(0, 101, self::WIDGET);
        $cases[] = $this->signPayload($overflow);
        $wrongType = $base['payload'];
        $wrongType['revision'] = '1';
        $cases[] = $this->signPayload($wrongType);
        $wrongNested = $base['payload'];
        $wrongNested['assignment']['roles'] = [['hacker']];
        $cases[] = $this->signPayload($wrongNested);
        $wrongUid = $base['payload'];
        $wrongUid['definition_id'] = 'not-a-uuid';
        $cases[] = $this->signPayload($wrongUid);
        foreach ($cases as $candidate) {
            self::assertSame(
                ['status' => 'invalid_snapshot', 'applicable' => false],
                $service->preflight($candidate),
            );
        }

        $repo->save(new Definition(
            id: 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
            slug: 'draft-widget',
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: 10,
            status: DefinitionStatus::Draft,
            payload: [],
        ));
        $draft = $base['payload'];
        $draft['widget_definition_ids'] = ['cccccccc-cccc-4ccc-8ccc-cccccccccccc'];
        self::assertSame(['status' => 'invalid_snapshot', 'applicable' => false], $service->preflight($this->signPayload($draft)));

        $repo->save($this->widget('dddddddd-dddd-4ddd-8ddd-dddddddddddd', owner: 9));
        $foreign = $base['payload'];
        $foreign['widget_definition_ids'] = ['dddddddd-dddd-4ddd-8ddd-dddddddddddd'];
        self::assertSame(['status' => 'invalid_snapshot', 'applicable' => false], $service->preflight($this->signPayload($foreign)));
    }

    private const WIDGET = '11111111-1111-4111-8111-111111111111';
    private const SOURCE = '22222222-2222-4222-8222-222222222222';
    private const CANDIDATE = '33333333-3333-4333-8333-333333333333';

    private function repository(): InMemoryDefinitionRepository
    {
        $repo = new InMemoryDefinitionRepository();
        $repo->save($this->widget(self::WIDGET));
        $repo->save(new Definition(
            id: self::SOURCE,
            slug: 'existing-preset',
            type: DashboardWidgetPresetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: 10,
            status: DefinitionStatus::Published,
            payload: ['preset' => [
                'label' => 'Fixture',
                'widget_definition_ids' => [self::WIDGET],
                'assignment' => ['roles' => ['editor']],
            ]],
        ));
        return $repo;
    }

    private function widget(
        string $id,
        DefinitionStatus $status = DefinitionStatus::Published,
        int $owner = 10,
    ): Definition {
        return new Definition(
            id: $id,
            slug: 'target-widget',
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: $owner,
            status: $status,
            payload: [],
        );
    }

    /** @return array<string,mixed> */
    private function snapshot(string $id = self::CANDIDATE): array
    {
        $payload = [
            'definition_id' => $id,
            'revision' => 1,
            'label' => 'Fixture',
            'widget_definition_ids' => [self::WIDGET],
            'assignment' => ['roles' => ['editor'], 'network_default' => false],
        ];
        return $this->signPayload($payload);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    private function signPayload(array $payload): array
    {
        return [
            'format' => 'wpessential-dashboard-preset',
            'version' => 1,
            'payload' => $payload,
            'sha256' => hash('sha256', json_encode(
                $payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            )),
        ];
    }

    private function service(InMemoryDefinitionRepository $repo): DashboardWidgetPresetImportPreflightService
    {
        return new DashboardWidgetPresetImportPreflightService($repo, new DashboardWidgetPresetCompiler($repo));
    }
}
