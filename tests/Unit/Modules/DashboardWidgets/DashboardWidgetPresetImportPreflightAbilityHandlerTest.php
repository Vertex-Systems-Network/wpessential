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

final class DashboardWidgetPresetImportPreflightAbilityHandlerTest extends TestCase
{
    public function testHandlerRoutesValidCandidateWithoutRepositoryMutation(): void
    {
        $repo = $this->repository();
        $handler = new DashboardWidgetPresetImportPreflightAbilityHandler($this->service($repo));
        $before = $repo->get(self::SOURCE);
        self::assertSame(
            ['status' => 'valid_candidate', 'applicable' => false],
            $handler->handle(['snapshot' => $this->snapshot()], new ExecutionContext(new Principal(7), 3)),
        );
        self::assertNull($repo->get(self::CANDIDATE));
        self::assertSame($before, $repo->get(self::SOURCE));
    }

    public function testHandlerRejectsMissingExtraAndNonObjectPayload(): void
    {
        $handler = new DashboardWidgetPresetImportPreflightAbilityHandler($this->service($this->repository()));
        foreach ([
            [],
            ['snapshot' => 'json-string'],
            ['snapshot' => 42],
            ['snapshot' => $this->snapshot(), 'import' => true],
        ] as $candidate) {
            try {
                $handler->handle($candidate, new ExecutionContext(new Principal(7), 3));
                self::fail('Preflight accepted an invalid ability envelope.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
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
