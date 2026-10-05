<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentBlueprintCatalog;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDiagnosticsAbilityHandler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRuntimeClock;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityCompiler;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetDiagnosticsAbilityHandlerTest extends TestCase
{
    private const READY_ID = '11111111-1111-4111-8111-111111111111';
    private const DRAFT_ID = '22222222-2222-4222-8222-222222222222';
    private const BLOCKED_ID = '33333333-3333-4333-8333-333333333333';

    public function testReportsReadyPublishedDefinitionWithSafeCompiledProjection(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $definitions->save($this->definition(self::READY_ID, 'ready-widget', DefinitionStatus::Published));

        $result = $this->handler($definitions)->handle([], $this->context());

        self::assertSame([
            'definitions' => 1,
            'published' => 1,
            'inactive' => 0,
            'ready' => 1,
            'blocked' => 0,
            'unhealthy' => 0,
        ], $result['summary']);

        $item = $result['definitions'][0];
        self::assertSame(self::READY_ID, $item['id']);
        self::assertSame('ready-widget', $item['slug']);
        self::assertSame('valid', $item['checksum_status']);
        self::assertSame('ready', $item['runtime_state']);
        self::assertSame('rich_text', $item['content_type']);
        self::assertSame([
            'context' => 'normal',
            'priority' => 'default',
            'network_dashboard' => false,
            'site_scope' => null,
            'site_ids' => [],
            'default_hidden' => false,
            'default_collapsed' => false,
        ], $item['target']);
        self::assertSame([
            'schedule_start' => null,
            'schedule_end' => null,
            'state' => 'active',
        ], $item['lifecycle']);
        self::assertSame([
            'background_job_id' => null,
            'action_ability_id' => null,
            'action_input_present' => false,
        ], $item['references']);
        self::assertSame(0, $item['dependency_count']);
        self::assertSame([], $item['issues']);
        self::assertArrayNotHasKey('payload', $item);
    }

    public function testClassifiesNonPublishedDefinitionAsInactiveWithoutRuntimeCompilation(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $definitions->save($this->definition(
            self::DRAFT_ID,
            'draft-widget',
            DefinitionStatus::Draft,
            widget: ['unsafe_shape' => true],
        ));

        $result = $this->handler($definitions)->handle(
            ['definition_id' => self::DRAFT_ID],
            $this->context(),
        );

        self::assertSame(1, $result['summary']['inactive']);
        self::assertSame(0, $result['summary']['blocked']);
        self::assertSame('inactive', $result['definitions'][0]['runtime_state']);
        self::assertNull($result['definitions'][0]['content_type']);
        self::assertNull($result['definitions'][0]['target']);
        self::assertNull($result['definitions'][0]['lifecycle']);
    }

    public function testReportsCanonicalLifecycleStateAtInjectedClock(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $widget = $this->widget('scheduled-widget');
        $widget['lifecycle'] = [
            'schedule_start' => '2025-01-01T00:00:00Z',
            'schedule_end' => '2025-02-01T00:00:00Z',
        ];
        $definitions->save($this->definition(
            self::READY_ID,
            'scheduled-widget',
            DefinitionStatus::Published,
            widget: $widget,
        ));

        $result = $this->handler(
            $definitions,
            new DashboardWidgetRuntimeClock(static fn (): int => 1735689599),
        )->handle(['definition_id' => self::READY_ID], $this->context());

        self::assertSame([
            'schedule_start' => '2025-01-01T00:00:00Z',
            'schedule_end' => '2025-02-01T00:00:00Z',
            'state' => 'before_schedule',
        ], $result['definitions'][0]['lifecycle']);
        self::assertSame('ready', $result['definitions'][0]['runtime_state']);

        $active = $this->handler(
            $definitions,
            new DashboardWidgetRuntimeClock(static fn (): int => 1737000000),
        )->handle(['definition_id' => self::READY_ID], $this->context());
        self::assertSame('active', $active['definitions'][0]['lifecycle']['state']);

        $expired = $this->handler(
            $definitions,
            new DashboardWidgetRuntimeClock(static fn (): int => 1738368000),
        )->handle(['definition_id' => self::READY_ID], $this->context());
        self::assertSame('expired', $expired['definitions'][0]['lifecycle']['state']);
    }

    public function testLifecycleClockFailureIsBoundedDiagnosticEvidence(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $definitions->save($this->definition(self::READY_ID, 'ready-widget', DefinitionStatus::Published));

        $result = $this->handler(
            $definitions,
            new DashboardWidgetRuntimeClock(static fn (): string => 'invalid'),
        )->handle(['definition_id' => self::READY_ID], $this->context());

        self::assertSame('blocked', $result['definitions'][0]['runtime_state']);
        self::assertNull($result['definitions'][0]['lifecycle']['state']);
        self::assertSame(
            ['dashboard-widget.diagnostics.lifecycle-clock-unavailable'],
            array_column($result['definitions'][0]['issues'], 'id'),
        );
    }

    public function testReportsPublishedRegistrationFailureAndChecksumMismatchWithoutLeakingPayload(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $definitions->save($this->definition(
            self::BLOCKED_ID,
            'blocked-widget',
            DefinitionStatus::Published,
            widget: [
                'key' => 'blocked-widget',
                'title' => 'Blocked Widget',
                'type' => 'rich_text',
                'context' => 'normal',
                'priority' => 'default',
                'network_dashboard' => false,
            ],
            checksum: str_repeat('0', 64),
        ));

        $result = $this->handler($definitions)->handle(
            ['definition_id' => self::BLOCKED_ID],
            $this->context(),
        );

        self::assertSame(1, $result['summary']['blocked']);
        self::assertSame(1, $result['summary']['unhealthy']);
        $item = $result['definitions'][0];
        self::assertSame('mismatch', $item['checksum_status']);
        self::assertSame('blocked', $item['runtime_state']);
        self::assertSame('rich_text', $item['content_type']);
        self::assertSame(
            [
                'dashboard-widget.diagnostics.checksum-mismatch',
                'dashboard-widget.diagnostics.registration-invalid',
            ],
            array_column($item['issues'], 'id'),
        );
        self::assertArrayNotHasKey('payload', $item);
    }

    public function testRejectsUnknownInputMalformedIdAndNonOwnedDefinition(): void
    {
        $handler = $this->handler(new InMemoryDefinitionRepository());

        try {
            $handler->handle(['save' => true], $this->context());
            self::fail('Expected unknown diagnostics input to fail closed.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        try {
            $handler->handle(['definition_id' => 'not-a-uuid'], $this->context());
            self::fail('Expected malformed diagnostics definition id to fail closed.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $this->expectException(RuntimeException::class);
        $handler->handle(
            ['definition_id' => '44444444-4444-4444-8444-444444444444'],
            $this->context(),
        );
    }

    private function handler(
        InMemoryDefinitionRepository $definitions,
        ?DashboardWidgetRuntimeClock $clock = null,
    ): DashboardWidgetDiagnosticsAbilityHandler
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $blueprints = new ComponentBlueprintRegistry();
        foreach ($catalog->all() as $blueprint) {
            $blueprints->register($blueprint);
        }

        $content = new DashboardWidgetContentClassCompiler();
        $renderSource = new DashboardWidgetRenderSourceCompiler($blueprints, $content, $catalog);
        $registration = new DashboardWidgetRegistrationCompiler(
            new DashboardWidgetVisibilityCompiler(),
            $content,
            $renderSource,
        );

        return new DashboardWidgetDiagnosticsAbilityHandler(
            $definitions,
            $content,
            $registration,
            $clock,
        );
    }

    /**
     * @param null|array<string,mixed> $widget
     */
    private function definition(
        string $id,
        string $slug,
        DefinitionStatus $status,
        ?array $widget = null,
        ?string $checksum = null,
    ): Definition {
        $widget ??= $this->widget($slug);
        $candidate = new Definition(
            id: $id,
            slug: $slug,
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            status: $status,
            payload: ['widget' => $widget],
            revision: 3,
            dependencies: [],
        );

        return new Definition(
            id: $candidate->id,
            slug: $candidate->slug,
            type: $candidate->type,
            schemaVersion: $candidate->schemaVersion,
            ownerSurfaceId: $candidate->ownerSurfaceId,
            status: $candidate->status,
            payload: $candidate->payload,
            revision: $candidate->revision,
            dependencies: [],
            checksum: $checksum ?? $candidate->computedChecksum(),
        );
    }

    /** @return array<string,mixed> */
    private function widget(string $key): array
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $blueprint = $catalog->forContentType('rich_text');
        self::assertNotNull($blueprint);

        return [
            'key' => $key,
            'title' => 'Diagnostic Widget',
            'type' => 'rich_text',
            'context' => 'normal',
            'priority' => 'default',
            'network_dashboard' => false,
            'render_source' => [
                'kind' => 'component_blueprint',
                'blueprint_id' => $blueprint->id,
                'blueprint_revision' => $blueprint->revision,
                'bindings' => [
                    'content' => ['source' => 'literal', 'value' => 'Orders'],
                ],
            ],
        ];
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 17, networkId: 9);
    }
}
