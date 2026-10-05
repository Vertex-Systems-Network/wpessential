<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentBlueprintCatalog;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPersonalPreferenceAbilityHandler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPersonalPreferenceStore;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityCompiler;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetPersonalPreferenceAbilityHandlerTest extends TestCase
{
    private const ID = '11111111-1111-4111-8111-111111111111';

    public function testDismissUsesCanonicalDefinitionRevisionAndDerivedWidgetId(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $definitions->save($this->definition(dismissible: true));
        $meta = [];
        $options = [];
        $handler = new DashboardWidgetPersonalPreferenceAbilityHandler(
            $definitions,
            $this->compiler(),
            $this->store($meta, $options),
            DashboardWidgetPersonalPreferenceAbilityHandler::DISMISS,
        );

        $result = $handler->handle([
            'definition_id' => self::ID,
            'expected_revision' => 3,
            'screen' => 'site',
        ], $this->context());

        self::assertSame('dashboard', $result['screen_id']);
        self::assertSame(['wpe_dashboard_widget_sales-overview'], $result['dismissed']);
    }

    public function testDismissRejectsNonDismissibleWrongRevisionAndWrongTarget(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $definitions->save($this->definition(dismissible: false));
        $meta = [];
        $options = [];
        $handler = new DashboardWidgetPersonalPreferenceAbilityHandler(
            $definitions,
            $this->compiler(),
            $this->store($meta, $options),
            DashboardWidgetPersonalPreferenceAbilityHandler::DISMISS,
        );

        try {
            $handler->handle([
                'definition_id' => self::ID,
                'expected_revision' => 3,
                'screen' => 'site',
            ], $this->context());
            self::fail('Expected non-dismissible definition to be rejected.');
        } catch (RuntimeException) {
            self::assertSame([], $meta);
        }

        $definitions = new InMemoryDefinitionRepository();
        $definitions->save($this->definition(dismissible: true));
        $handler = new DashboardWidgetPersonalPreferenceAbilityHandler(
            $definitions,
            $this->compiler(),
            $this->store($meta, $options),
            DashboardWidgetPersonalPreferenceAbilityHandler::DISMISS,
        );

        try {
            $handler->handle([
                'definition_id' => self::ID,
                'expected_revision' => 2,
                'screen' => 'site',
            ], $this->context());
            self::fail('Expected stale revision to be rejected.');
        } catch (RuntimeException) {
            self::assertSame([], $meta);
        }

        $this->expectException(RuntimeException::class);
        $handler->handle([
            'definition_id' => self::ID,
            'expected_revision' => 3,
            'screen' => 'network',
        ], $this->context());
    }

    public function testResetDelegatesOnlyCurrentUserAndScreen(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $meta = [
            '7:_wpessential_dashboard_widgets_dismissed_dashboard-network' => [
                'wpe_dashboard_widget_sales-overview',
            ],
        ];
        $options = [
            '7:metaboxhidden_dashboard-network' => ['dashboard_primary', 'wpe_dashboard_widget_sales-overview'],
        ];
        $handler = new DashboardWidgetPersonalPreferenceAbilityHandler(
            $definitions,
            $this->compiler(),
            $this->store($meta, $options),
            DashboardWidgetPersonalPreferenceAbilityHandler::RESET,
        );

        $result = $handler->handle(['screen' => 'network'], $this->context());

        self::assertSame('dashboard-network', $result['screen_id']);
        self::assertSame(1, $result['removed']['dismissed']);
        self::assertSame(1, $result['removed']['hidden']);
        self::assertSame(['dashboard_primary'], $options['7:metaboxhidden_dashboard-network']);
    }

    public function testRejectsUnknownInputAndUnauthenticatedPrincipal(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $meta = [];
        $options = [];
        $handler = new DashboardWidgetPersonalPreferenceAbilityHandler(
            $definitions,
            $this->compiler(),
            $this->store($meta, $options),
            DashboardWidgetPersonalPreferenceAbilityHandler::RESET,
        );

        try {
            $handler->handle(['screen' => 'site', 'user_id' => 99], $this->context());
            self::fail('Expected unknown input to fail.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $this->expectException(RuntimeException::class);
        $handler->handle(
            ['screen' => 'site'],
            new ExecutionContext(new Principal(null, 'user'), 11, networkId: 9),
        );
    }

    private function compiler(): DashboardWidgetRegistrationCompiler
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $blueprints = new ComponentBlueprintRegistry();
        foreach ($catalog->all() as $blueprint) {
            $blueprints->register($blueprint);
        }
        $content = new DashboardWidgetContentClassCompiler();

        return new DashboardWidgetRegistrationCompiler(
            new DashboardWidgetVisibilityCompiler(),
            $content,
            new DashboardWidgetRenderSourceCompiler($blueprints, $content, $catalog),
        );
    }

    private function definition(bool $dismissible): Definition
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $blueprint = $catalog->forContentType('rich_text');
        self::assertNotNull($blueprint);

        return new Definition(
            id: self::ID,
            slug: 'sales-overview',
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: [
                'widget' => [
                    'key' => 'sales-overview',
                    'title' => 'Sales Overview',
                    'type' => 'rich_text',
                    'context' => 'normal',
                    'priority' => 'default',
                    'network_dashboard' => false,
                    'presentation' => ['dismissible' => $dismissible],
                    'render_source' => [
                        'kind' => 'component_blueprint',
                        'blueprint_id' => $blueprint->id,
                        'blueprint_revision' => $blueprint->revision,
                        'bindings' => [
                            'content' => ['source' => 'literal', 'value' => 'Orders'],
                        ],
                    ],
                ],
            ],
            revision: 3,
            dependencies: [],
        );
    }

    /**
     * @param array<string,mixed> $meta
     * @param array<string,mixed> $options
     */
    private function store(array &$meta, array &$options): DashboardWidgetPersonalPreferenceStore
    {
        return new DashboardWidgetPersonalPreferenceStore(
            metaReader: static fn (int $userId, string $key): mixed =>
                $meta[$userId . ':' . $key] ?? null,
            metaWriter: static function (int $userId, string $key, array $value) use (&$meta): void {
                $meta[$userId . ':' . $key] = $value;
            },
            metaDeleter: static function (int $userId, string $key) use (&$meta): void {
                unset($meta[$userId . ':' . $key]);
            },
            optionReader: static fn (int $userId, string $key): mixed =>
                $options[$userId . ':' . $key] ?? null,
            optionWriter: static function (int $userId, string $key, mixed $value) use (&$options): void {
                $options[$userId . ':' . $key] = $value;
            },
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 11, networkId: 9);
    }
}
