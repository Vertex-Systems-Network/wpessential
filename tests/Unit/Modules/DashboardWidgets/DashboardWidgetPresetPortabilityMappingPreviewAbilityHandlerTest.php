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

final class DashboardWidgetPresetPortabilityMappingPreviewAbilityHandlerTest extends TestCase
{
    public function testStrictHandlerReadsOnlyAndReturnsAdvisoryCandidate(): void
    {
        $repo = $this->repo();
        $handler = new DashboardWidgetPresetPortabilityMappingPreviewAbilityHandler($this->service($repo));
        $input = [
            'widget_id_map' => $this->map(),
            'target_id' => self::TARGET_PRESET,
            'snapshot' => $this->snapshot(),
        ];
        $result = $handler->handle($input, new ExecutionContext(new Principal(7), 3));
        self::assertSame('valid_candidate', $result['status']);
        self::assertFalse($result['applicable']);
        self::assertNotNull($result['candidate_snapshot']);
        self::assertNull($repo->get(self::TARGET_PRESET));
    }

    public function testRejectsMissingExtraOrNonObjectInputs(): void
    {
        $handler = new DashboardWidgetPresetPortabilityMappingPreviewAbilityHandler($this->service($this->repo()));
        foreach ([
            [],
            ['snapshot' => $this->snapshot()],
            ['snapshot' => 'string', 'target_id' => self::TARGET_PRESET, 'widget_id_map' => $this->map()],
            ['snapshot' => $this->snapshot(), 'target_id' => [], 'widget_id_map' => $this->map()],
            ['snapshot' => $this->snapshot(), 'target_id' => self::TARGET_PRESET, 'widget_id_map' => 'json'],
            ['snapshot' => $this->snapshot(), 'target_id' => self::TARGET_PRESET, 'widget_id_map' => $this->map(), 'import' => true],
        ] as $input) {
            try {
                $handler->handle($input, new ExecutionContext(new Principal(7), 3));
                self::fail('Invalid mapping preview ability request accepted.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
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
