<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentBlueprintCatalog;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetManualRefreshAjaxHandler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRoleMembershipProviderInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRuntimeClock;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRuntimeRenderExecutor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityEvaluator;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\Rendering\RenderInput;
use WPEssential\Platform\Rendering\RenderOutput;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

final class DashboardWidgetManualRefreshAjaxHandlerTest extends TestCase
{
    private const ID = '11111111-1111-4111-8111-111111111111';

    public function testReturnsTrustedFreshHtmlForExactCurrentSiteDefinition(): void
    {
        [$handler] = $this->harness($this->definition());

        $result = $handler->handle($this->request());

        self::assertSame(DashboardWidgetManualRefreshAjaxHandler::STATE_RENDERED, $result['state']);
        self::assertSame('Widget refreshed.', $result['notice']);
        self::assertStringContainsString('Fresh content', $result['html']);
        self::assertStringContainsString('wpe-dashboard-widget--rich-text', $result['html']);
    }

    public function testRejectsStaleRevisionWrongTargetDisabledManualAndMalformedRequest(): void
    {
        [$handler] = $this->harness($this->definition());

        $stale = $this->request();
        $stale['definition_revision'] = 2;
        self::assertSame(
            DashboardWidgetManualRefreshAjaxHandler::STATE_STALE_DEFINITION,
            $handler->handle($stale)['state'],
        );

        $network = $this->request();
        $network['screen'] = 'network';
        self::assertSame(
            DashboardWidgetManualRefreshAjaxHandler::STATE_UNAVAILABLE,
            $handler->handle($network)['state'],
        );

        [$disabled] = $this->harness($this->definition(manual: false));
        self::assertSame(
            DashboardWidgetManualRefreshAjaxHandler::STATE_UNAVAILABLE,
            $disabled->handle($this->request())['state'],
        );

        $extra = $this->request();
        $extra['source'] = 'provider';
        self::assertSame(
            DashboardWidgetManualRefreshAjaxHandler::STATE_INVALID_DEFINITION,
            $handler->handle($extra)['state'],
        );
    }

    public function testExpiredWidgetAndGuestFailWithoutReturningHtml(): void
    {
        $expired = $this->definition(
            lifecycle: ['schedule_end' => '2025-01-01T00:00:00Z'],
        );
        [$handler] = $this->harness(
            $expired,
            clock: new DashboardWidgetRuntimeClock(static fn (): int => 1735689600),
        );

        $result = $handler->handle($this->request());
        self::assertSame(DashboardWidgetManualRefreshAjaxHandler::STATE_UNAVAILABLE, $result['state']);
        self::assertArrayNotHasKey('html', $result);

        [$guest] = $this->harness($this->definition(), userId: null);
        $guestResult = $guest->handle($this->request());
        self::assertSame(DashboardWidgetManualRefreshAjaxHandler::STATE_UNAVAILABLE, $guestResult['state']);
        self::assertArrayNotHasKey('html', $guestResult);
    }

    public function testRejectsRefreshedOutputThatRequiresNewAssets(): void
    {
        [$handler] = $this->harness(
            $this->definition(),
            renderer: new ManualRefreshAssetRenderer(),
        );

        $result = $handler->handle($this->request());

        self::assertSame(DashboardWidgetManualRefreshAjaxHandler::STATE_RUNTIME_FAILURE, $result['state']);
        self::assertArrayNotHasKey('html', $result);
    }

    /**
     * @return array{DashboardWidgetManualRefreshAjaxHandler,InMemoryDefinitionRepository}
     */
    private function harness(
        Definition $definition,
        ?DashboardWidgetRuntimeClock $clock = null,
        ?RendererInterface $renderer = null,
        ?int $userId = 7,
    ): array {
        $definitions = new InMemoryDefinitionRepository();
        $definitions->save($definition);

        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $blueprints = new ComponentBlueprintRegistry();
        foreach ($catalog->all() as $blueprint) {
            $blueprints->register($blueprint);
        }

        $content = new DashboardWidgetContentClassCompiler();
        $renderSource = new DashboardWidgetRenderSourceCompiler($blueprints, $content, $catalog);
        $visibilityCompiler = new DashboardWidgetVisibilityCompiler();
        $registrations = new DashboardWidgetRegistrationCompiler(
            $visibilityCompiler,
            $content,
            $renderSource,
        );

        $capabilities = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return true;
            }
        };
        $roles = new class implements DashboardWidgetRoleMembershipProviderInterface {
            public function isCurrentUserContext(ExecutionContext $context): bool
            {
                return true;
            }

            public function hasAnyRole(ExecutionContext $context, array $roles): bool
            {
                return true;
            }
        };

        $runtime = new DashboardWidgetRuntimeRenderExecutor(
            $definitions,
            $registrations,
            $visibilityCompiler,
            new DashboardWidgetVisibilityEvaluator($capabilities, $roles),
            $renderSource,
            $renderer ?? new \WPEssential\Modules\DashboardWidgets\DashboardWidgetTrustedComponentRenderer($catalog),
            clock: $clock,
        );

        $contexts = new WordPressExecutionContextFactory(new ManualRefreshAbilityEnvironment($userId));

        return [
            new DashboardWidgetManualRefreshAjaxHandler(
                $definitions,
                $registrations,
                $runtime,
                $contexts,
            ),
            $definitions,
        ];
    }

    /** @return array{definition_id:string,definition_revision:int,screen:string} */
    private function request(): array
    {
        return [
            'definition_id' => self::ID,
            'definition_revision' => 3,
            'screen' => 'site',
        ];
    }

    private function definition(
        bool $manual = true,
        bool $network = false,
        array $lifecycle = [],
    ): Definition {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $richText = $catalog->forContentType('rich_text');
        $announcement = $catalog->forContentType('announcement');
        self::assertNotNull($richText);
        self::assertNotNull($announcement);

        $widget = [
            'key' => 'manual-refresh-widget',
            'title' => 'Manual refresh widget',
            'type' => 'rich_text',
            'context' => 'normal',
            'priority' => 'default',
            'network_dashboard' => $network,
            'render_source' => [
                'kind' => 'component_blueprint',
                'blueprint_id' => $richText->id,
                'blueprint_revision' => $richText->revision,
                'bindings' => [
                    'content' => ['source' => 'literal', 'value' => 'Fresh content'],
                ],
            ],
        ];

        if ($manual) {
            $widget['refresh'] = ['manual' => true];
            $widget['render_source']['loading_state'] = [
                'kind' => 'component_blueprint',
                'blueprint_id' => $announcement->id,
                'blueprint_revision' => $announcement->revision,
                'bindings' => [
                    'title' => ['source' => 'literal', 'value' => 'Refreshing'],
                    'text' => ['source' => 'literal', 'value' => 'Fetching the latest data.'],
                ],
            ];
        }
        if ($lifecycle !== []) {
            $widget['lifecycle'] = $lifecycle;
        }

        return new Definition(
            id: self::ID,
            slug: 'manual-refresh-widget',
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: ['widget' => $widget],
            revision: 3,
            dependencies: [],
        );
    }
}

final class ManualRefreshAbilityEnvironment implements WordPressAbilityEnvironmentInterface
{
    public function __construct(private readonly ?int $userId) {}

    public function abilitiesApiAvailable(): bool { return false; }
    public function doingAction(string $hook): bool { return false; }
    public function currentUserId(): ?int { return $this->userId; }
    public function currentSiteId(): int { return 11; }
    public function currentNetworkId(): ?int { return 13; }
    public function currentUserCan(string $capability): bool { return true; }
    public function isRestRequest(): bool { return false; }
    public function isCli(): bool { return false; }
    public function registerCategory(string $slug, array $args): bool { return false; }
    public function registerAbility(string $name, array $args): bool { return false; }
}

final class ManualRefreshAssetRenderer implements RendererInterface
{
    public function render(RenderInput $input, ExecutionContext $context): RenderOutput
    {
        return new RenderOutput(
            true,
            '<div class="trusted-with-asset">Fresh content</div>',
            ['wpe-dashboard-dynamic-extra'],
        );
    }
}
