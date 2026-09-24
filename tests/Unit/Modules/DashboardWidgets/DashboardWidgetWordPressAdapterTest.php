<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentBlueprintCatalog;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRoleMembershipProviderInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRuntimeRenderExecutor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityEvaluator;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetWordPressAdapter;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetWordPressEnvironmentInterface;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Rendering\RenderFailureCode;
use WPEssential\Platform\Rendering\RenderInput;
use WPEssential\Platform\Rendering\RenderOutput;

final class DashboardWidgetWordPressAdapterTest extends TestCase
{
    public function testRegistersExactHooksOnce(): void
    {
        [$adapter, , $environment] = $this->harness([]);

        $adapter->registerHooks();
        $adapter->registerHooks();

        self::assertSame(
            ['wp_dashboard_setup', 'wp_network_dashboard_setup'],
            array_column($environment->hooks, 'hook'),
        );
        self::assertSame(['default_hidden_meta_boxes'], array_column($environment->filters, 'hook'));
        self::assertSame(2, $environment->filters[0]['acceptedArgs']);
    }

    public function testPlanningSortsCanonicallyAndSuppressesAllSameTargetColliders(): void
    {
        $definitions = [
            $this->definition('40000000-0000-4000-8000-000000000004', 'zeta-widget', 'zeta'),
            $this->definition('40000000-0000-4000-8000-000000000002', 'collision-b', 'duplicate'),
            $this->definition('40000000-0000-4000-8000-000000000001', 'alpha-widget', 'alpha'),
            $this->definition('40000000-0000-4000-8000-000000000003', 'collision-a', 'duplicate'),
        ];
        [$adapter, , $environment] = $this->harness($definitions);

        $adapter->registerSiteDashboard();

        self::assertSame(
            ['wpe_dashboard_widget_alpha', 'wpe_dashboard_widget_zeta'],
            array_column($environment->widgets, 'id'),
        );
        self::assertNotContains('wpe_dashboard_widget_duplicate', array_column($environment->widgets, 'id'));
    }

    public function testDefaultHiddenFilterUsesExactTargetAndCollisionPlanning(): void
    {
        $definitions = [
            $this->definition(
                '40000000-0000-4000-8000-000000000081',
                'hidden-site',
                'hidden-site',
                defaultHidden: true,
            ),
            $this->definition(
                '40000000-0000-4000-8000-000000000082',
                'visible-site',
                'visible-site',
            ),
            $this->definition(
                '40000000-0000-4000-8000-000000000083',
                'other-site',
                'other-site',
                target: ['scope' => 'site_ids', 'site_ids' => [99]],
                defaultHidden: true,
            ),
            $this->definition(
                '40000000-0000-4000-8000-000000000084',
                'collision-a',
                'collision',
                defaultHidden: true,
            ),
            $this->definition(
                '40000000-0000-4000-8000-000000000085',
                'collision-b',
                'collision',
                defaultHidden: true,
            ),
            $this->definition(
                '40000000-0000-4000-8000-000000000086',
                'hidden-network',
                'hidden-network',
                networkDashboard: true,
                defaultHidden: true,
            ),
        ];
        [$adapter, , $environment] = $this->harness($definitions);
        $adapter->registerHooks();

        $filter = $environment->filters[0]['callback'];
        $siteHidden = $filter(
            ['dashboard_right_now', 'wpe_dashboard_widget_hidden-site'],
            (object) ['id' => 'dashboard'],
        );
        self::assertSame(
            ['dashboard_right_now', 'wpe_dashboard_widget_hidden-site'],
            $siteHidden,
        );
        self::assertNotContains('wpe_dashboard_widget_other-site', $siteHidden);
        self::assertNotContains('wpe_dashboard_widget_collision', $siteHidden);
        self::assertNotContains('wpe_dashboard_widget_hidden-network', $siteHidden);

        $networkHidden = $filter(
            ['dashboard_primary'],
            (object) ['id' => 'dashboard-network'],
        );
        self::assertSame(
            ['dashboard_primary', 'wpe_dashboard_widget_hidden-network'],
            $networkHidden,
        );

        self::assertSame(
            ['existing'],
            $filter(['existing'], (object) ['id' => 'edit-post']),
        );

        $environment->throwOnScreenId = true;
        self::assertSame(
            ['existing'],
            $filter(['existing'], (object) ['id' => 'dashboard']),
        );
    }

    public function testDefaultCollapsedUsesNativePostboxClassOnlyWithoutSavedPreference(): void
    {
        $definitions = [
            $this->definition(
                '40000000-0000-4000-8000-000000000091',
                'collapsed-site',
                'collapsed-site',
                defaultCollapsed: true,
            ),
            $this->definition(
                '40000000-0000-4000-8000-000000000092',
                'open-site',
                'open-site',
            ),
            $this->definition(
                '40000000-0000-4000-8000-000000000093',
                'other-site',
                'other-site',
                target: ['scope' => 'site_ids', 'site_ids' => [99]],
                defaultCollapsed: true,
            ),
            $this->definition(
                '40000000-0000-4000-8000-000000000094',
                'collision-a',
                'collapse-collision',
                defaultCollapsed: true,
            ),
            $this->definition(
                '40000000-0000-4000-8000-000000000095',
                'collision-b',
                'collapse-collision',
                defaultCollapsed: true,
            ),
            $this->definition(
                '40000000-0000-4000-8000-000000000096',
                'collapsed-network',
                'collapsed-network',
                networkDashboard: true,
                defaultCollapsed: true,
            ),
        ];
        [$adapter, , $environment] = $this->harness($definitions);
        $adapter->registerHooks();
        $adapter->registerSiteDashboard();

        $siteFilters = array_values(array_filter(
            $environment->filters,
            static fn (array $filter): bool =>
                str_starts_with($filter['hook'], 'postbox_classes_dashboard_'),
        ));
        self::assertCount(1, $siteFilters);
        self::assertSame(
            'postbox_classes_dashboard_wpe_dashboard_widget_collapsed-site',
            $siteFilters[0]['hook'],
        );

        $siteCallback = $siteFilters[0]['callback'];
        $environment->closedPostboxPreferenceByScreen['dashboard'] = false;
        self::assertSame(['postbox', 'closed'], $siteCallback(['postbox']));
        self::assertSame(['closed'], $siteCallback(['closed']));

        $environment->closedPostboxPreferenceByScreen['dashboard'] = true;
        self::assertSame(['postbox'], $siteCallback(['postbox']));

        $environment->throwOnClosedPostboxPreference = true;
        self::assertSame(['postbox'], $siteCallback(['postbox']));
        $environment->throwOnClosedPostboxPreference = false;

        $adapter->registerNetworkDashboard();
        $networkFilters = array_values(array_filter(
            $environment->filters,
            static fn (array $filter): bool =>
                str_starts_with($filter['hook'], 'postbox_classes_dashboard-network_'),
        ));
        self::assertCount(1, $networkFilters);
        self::assertSame(
            'postbox_classes_dashboard-network_wpe_dashboard_widget_collapsed-network',
            $networkFilters[0]['hook'],
        );

        [$failedAdapter, , $failedEnvironment] = $this->harness([
            $this->definition(
                '40000000-0000-4000-8000-000000000097',
                'failed-collapse',
                'failed-collapse',
                defaultCollapsed: true,
            ),
        ]);
        $failedEnvironment->throwOnWidgetRegistration = true;
        $failedAdapter->registerSiteDashboard();
        self::assertSame([], $failedEnvironment->filters);
    }

    public function testDiscoversRegisteredDashboardWidgetsThroughBoundedReadOnlySeam(): void
    {
        [$adapter, , $environment] = $this->harness([]);

        $environment->registeredDashboardWidgetsByScreen['dashboard'] = [
            ['id' => 'z_widget', 'context' => 'side', 'priority' => 'low'],
            ['id' => 'a-widget', 'context' => 'normal', 'priority' => 'high'],
            ['id' => 'a-widget', 'context' => 'normal', 'priority' => 'high'],
        ];
        self::assertSame(
            [
                ['id' => 'a-widget', 'context' => 'normal', 'priority' => 'high'],
                ['id' => 'z_widget', 'context' => 'side', 'priority' => 'low'],
            ],
            $adapter->discoverRegisteredDashboardWidgets(),
        );

        $environment->registeredDashboardWidgetsByScreen['dashboard-network'] = [
            ['id' => 'network_widget', 'context' => 'normal', 'priority' => 'core'],
        ];
        self::assertSame(
            [['id' => 'network_widget', 'context' => 'normal', 'priority' => 'core']],
            $adapter->discoverRegisteredDashboardWidgets(true),
        );

        $environment->throwOnRegisteredDashboardWidgetDiscovery = true;
        self::assertSame([], $adapter->discoverRegisteredDashboardWidgets());
        $environment->throwOnRegisteredDashboardWidgetDiscovery = false;

        $environment->registeredDashboardWidgetsByScreen['dashboard'] = [
            ['id' => 'leak', 'context' => 'normal', 'priority' => 'high', 'callback' => 'secret'],
        ];
        self::assertSame([], $adapter->discoverRegisteredDashboardWidgets());

        $environment->registeredDashboardWidgetsByScreen['dashboard'] = [
            ['id' => '<unsafe>', 'context' => 'normal', 'priority' => 'high'],
        ];
        self::assertSame([], $adapter->discoverRegisteredDashboardWidgets());
    }

    public function testClassifiesOnlyCanonicalCoreWidgetIdsPerDashboardScreen(): void
    {
        [$adapter, , $environment] = $this->harness([]);

        $environment->registeredDashboardWidgetsByScreen['dashboard'] = [
            ['id' => 'dashboard_primary', 'context' => 'side', 'priority' => 'core'],
            ['id' => 'dashboard_right_now', 'context' => 'normal', 'priority' => 'sorted'],
            ['id' => 'dashboard_site_health', 'context' => 'normal', 'priority' => 'core'],
            ['id' => 'dashboard_browser_nag', 'context' => 'normal', 'priority' => 'high'],
            ['id' => 'dashboard_php_nag', 'context' => 'normal', 'priority' => 'high'],
            ['id' => 'dashboard_activity', 'context' => 'normal', 'priority' => 'core'],
            ['id' => 'dashboard_quick_press', 'context' => 'side', 'priority' => 'core'],
            ['id' => 'network_dashboard_right_now', 'context' => 'normal', 'priority' => 'core'],
            ['id' => 'plugin_dashboard_widget', 'context' => 'normal', 'priority' => 'core'],
            ['id' => 'dashboard_custom_guess', 'context' => 'normal', 'priority' => 'core'],
        ];

        self::assertSame(
            [
                ['id' => 'dashboard_browser_nag', 'context' => 'normal', 'priority' => 'high'],
                ['id' => 'dashboard_php_nag', 'context' => 'normal', 'priority' => 'high'],
                ['id' => 'dashboard_right_now', 'context' => 'normal', 'priority' => 'sorted'],
                ['id' => 'dashboard_activity', 'context' => 'normal', 'priority' => 'core'],
                ['id' => 'dashboard_site_health', 'context' => 'normal', 'priority' => 'core'],
                ['id' => 'dashboard_primary', 'context' => 'side', 'priority' => 'core'],
                ['id' => 'dashboard_quick_press', 'context' => 'side', 'priority' => 'core'],
            ],
            $adapter->discoverCoreDashboardWidgets(),
        );

        $environment->registeredDashboardWidgetsByScreen['dashboard-network'] = [
            ['id' => 'dashboard_browser_nag', 'context' => 'normal', 'priority' => 'high'],
            ['id' => 'dashboard_php_nag', 'context' => 'normal', 'priority' => 'high'],
            ['id' => 'network_dashboard_right_now', 'context' => 'normal', 'priority' => 'core'],
            ['id' => 'dashboard_primary', 'context' => 'side', 'priority' => 'core'],
            ['id' => 'dashboard_site_health', 'context' => 'normal', 'priority' => 'core'],
            ['id' => 'dashboard_right_now', 'context' => 'normal', 'priority' => 'core'],
            ['id' => 'network_plugin_widget', 'context' => 'side', 'priority' => 'core'],
        ];

        self::assertSame(
            [
                ['id' => 'dashboard_browser_nag', 'context' => 'normal', 'priority' => 'high'],
                ['id' => 'dashboard_php_nag', 'context' => 'normal', 'priority' => 'high'],
                ['id' => 'network_dashboard_right_now', 'context' => 'normal', 'priority' => 'core'],
                ['id' => 'dashboard_primary', 'context' => 'side', 'priority' => 'core'],
            ],
            $adapter->discoverCoreDashboardWidgets(true),
        );
    }

    public function testCoreWidgetClassificationFailsClosedWithInventoryDiscoveryFailure(): void
    {
        [$adapter, , $environment] = $this->harness([]);
        $environment->throwOnRegisteredDashboardWidgetDiscovery = true;

        self::assertSame([], $adapter->discoverCoreDashboardWidgets());
        self::assertSame([], $adapter->discoverCoreDashboardWidgets(true));
    }

    public function testClassifiesRegisteredNonCoreWidgetsWithoutClaimingProvenance(): void
    {
        [$adapter, , $environment] = $this->harness([]);

        $environment->registeredDashboardWidgetsByScreen['dashboard'] = [
            ['id' => 'dashboard_browser_nag', 'context' => 'normal', 'priority' => 'high'],
            ['id' => 'vendor_widget', 'context' => 'normal', 'priority' => 'core'],
            ['id' => 'dashboard_custom_guess', 'context' => 'normal', 'priority' => 'core'],
            ['id' => 'dashboard_primary', 'context' => 'side', 'priority' => 'core'],
            ['id' => 'another.plugin:widget', 'context' => 'side', 'priority' => 'low'],
        ];

        self::assertSame(
            [
                ['id' => 'dashboard_custom_guess', 'context' => 'normal', 'priority' => 'core'],
                ['id' => 'vendor_widget', 'context' => 'normal', 'priority' => 'core'],
                ['id' => 'another.plugin:widget', 'context' => 'side', 'priority' => 'low'],
            ],
            $adapter->discoverNonCoreDashboardWidgets(),
        );

        $environment->registeredDashboardWidgetsByScreen['dashboard-network'] = [
            ['id' => 'network_dashboard_right_now', 'context' => 'normal', 'priority' => 'core'],
            ['id' => 'dashboard_site_health', 'context' => 'normal', 'priority' => 'core'],
            ['id' => 'network_vendor_widget', 'context' => 'side', 'priority' => 'core'],
            ['id' => 'dashboard_primary', 'context' => 'side', 'priority' => 'core'],
        ];

        self::assertSame(
            [
                ['id' => 'dashboard_site_health', 'context' => 'normal', 'priority' => 'core'],
                ['id' => 'network_vendor_widget', 'context' => 'side', 'priority' => 'core'],
            ],
            $adapter->discoverNonCoreDashboardWidgets(true),
        );
    }

    public function testNonCoreClassificationFailsClosedWithInventoryDiscoveryFailure(): void
    {
        [$adapter, , $environment] = $this->harness([]);
        $environment->throwOnRegisteredDashboardWidgetDiscovery = true;

        self::assertSame([], $adapter->discoverNonCoreDashboardWidgets());
        self::assertSame([], $adapter->discoverNonCoreDashboardWidgets(true));
    }

    public function testProjectsOnlySavedCurrentUserHiddenWidgetIds(): void
    {
        [$adapter, , $environment] = $this->harness([]);

        $environment->hiddenDashboardWidgetIdsByScreen['dashboard'] = [
            'z_widget',
            'a-widget',
            'z_widget',
        ];
        self::assertSame(
            ['a-widget', 'z_widget'],
            $adapter->currentUserHiddenDashboardWidgetIds(),
        );

        $environment->hiddenDashboardWidgetIdsByScreen['dashboard-network'] = [
            'network_widget',
        ];
        self::assertSame(
            ['network_widget'],
            $adapter->currentUserHiddenDashboardWidgetIds(true),
        );

        $environment->throwOnHiddenDashboardWidgetPreference = true;
        self::assertSame([], $adapter->currentUserHiddenDashboardWidgetIds());
        $environment->throwOnHiddenDashboardWidgetPreference = false;

        $environment->hiddenDashboardWidgetIdsByScreen['dashboard'] = [
            'safe_widget',
            '<unsafe>',
        ];
        self::assertSame([], $adapter->currentUserHiddenDashboardWidgetIds());
    }

    public function testSiteTargetingFiltersBeforeCollisionGrouping(): void
    {
        $definitions = [
            $this->definition(
                '40000000-0000-4000-8000-000000000051',
                'site-eleven',
                'shared-target',
                false,
                ['scope' => 'site_ids', 'site_ids' => [11]],
            ),
            $this->definition(
                '40000000-0000-4000-8000-000000000052',
                'site-twelve',
                'shared-target',
                false,
                ['scope' => 'site_ids', 'site_ids' => [12]],
            ),
        ];
        [$adapter, , $environment] = $this->harness($definitions);
        $environment->siteId = 11;

        $adapter->registerSiteDashboard();

        self::assertSame(['wpe_dashboard_widget_shared-target'], array_column($environment->widgets, 'id'));

        $collidingDefinitions = [
            $this->definition(
                '40000000-0000-4000-8000-000000000053',
                'site-eleven-a',
                'eligible-collision',
                false,
                ['scope' => 'site_ids', 'site_ids' => [11]],
            ),
            $this->definition(
                '40000000-0000-4000-8000-000000000054',
                'site-eleven-b',
                'eligible-collision',
                false,
                ['scope' => 'all_sites'],
            ),
        ];
        [$collidingAdapter, , $collidingEnvironment] = $this->harness($collidingDefinitions);
        $collidingEnvironment->siteId = 11;

        $collidingAdapter->registerSiteDashboard();

        self::assertSame([], $collidingEnvironment->widgets);
    }

    public function testTargetAbsentAndAllSitesRemainSiteEligible(): void
    {
        $definitions = [
            $this->definition('40000000-0000-4000-8000-000000000061', 'legacy-site', 'legacy-site'),
            $this->definition(
                '40000000-0000-4000-8000-000000000062',
                'all-sites',
                'all-sites',
                false,
                ['scope' => 'all_sites'],
            ),
            $this->definition(
                '40000000-0000-4000-8000-000000000063',
                'other-site',
                'other-site',
                false,
                ['scope' => 'site_ids', 'site_ids' => [99]],
            ),
        ];
        [$adapter, , $environment] = $this->harness($definitions);
        $environment->siteId = 11;

        $adapter->registerSiteDashboard();

        self::assertSame(
            ['wpe_dashboard_widget_all-sites', 'wpe_dashboard_widget_legacy-site'],
            array_column($environment->widgets, 'id'),
        );
    }

    public function testInvalidOrThrowingCurrentSiteEvidenceHasNoSiteRegistrationSideEffects(): void
    {
        $definition = $this->definition(
            '40000000-0000-4000-8000-000000000071',
            'site-target',
            'site-target',
            false,
            ['scope' => 'all_sites'],
        );

        [$invalidAdapter, , $invalidEnvironment] = $this->harness([$definition]);
        $invalidEnvironment->siteId = 0;
        $invalidAdapter->registerSiteDashboard();
        self::assertSame([], $invalidEnvironment->widgets);

        [$throwingAdapter, , $throwingEnvironment] = $this->harness([$definition]);
        $throwingEnvironment->throwOnCurrentSiteId = true;
        $throwingAdapter->registerSiteDashboard();
        self::assertSame([], $throwingEnvironment->widgets);

        $networkDefinition = $this->definition(
            '40000000-0000-4000-8000-000000000072',
            'network-target',
            'network-target',
            true,
        );
        [$networkAdapter, , $networkEnvironment] = $this->harness([$networkDefinition]);
        $networkEnvironment->throwOnCurrentSiteId = true;
        $networkAdapter->registerNetworkDashboard();
        self::assertSame(['wpe_dashboard_widget_network-target'], array_column($networkEnvironment->widgets, 'id'));
    }

    public function testSiteAndNetworkTargetsUseIndependentCollisionDomains(): void
    {
        $definitions = [
            $this->definition('40000000-0000-4000-8000-000000000011', 'site-shared', 'shared', false),
            $this->definition('40000000-0000-4000-8000-000000000012', 'network-shared', 'shared', true),
        ];
        [$adapter, , $environment] = $this->harness($definitions);

        $adapter->registerSiteDashboard();
        $adapter->registerNetworkDashboard();

        self::assertCount(2, $environment->widgets);
        self::assertSame(
            ['wpe_dashboard_widget_shared', 'wpe_dashboard_widget_shared'],
            array_column($environment->widgets, 'id'),
        );
    }

    public function testExpectedInvalidDefinitionIsSkippedAndUnexpectedPlanningFailureHasNoSideEffects(): void
    {
        $valid = $this->definition('40000000-0000-4000-8000-000000000021', 'valid-widget', 'valid');
        $invalid = new Definition(
            id: '40000000-0000-4000-8000-000000000022',
            slug: 'draft-widget',
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Draft,
            payload: $valid->payload,
            revision: 1,
            dependencies: [],
        );
        [$adapter, , $environment] = $this->harness([$invalid, $valid]);
        $adapter->registerSiteDashboard();
        self::assertSame(['wpe_dashboard_widget_valid'], array_column($environment->widgets, 'id'));

        [$failingAdapter, $repository, $failingEnvironment] = $this->harness([$valid]);
        $repository->throwOnByType = true;
        $failingAdapter->registerSiteDashboard();
        self::assertSame([], $failingEnvironment->widgets);
    }

    public function testCallbackUsesFreshUiContextAndEmitsOnlyTrustedRenderedHtml(): void
    {
        $definition = $this->definition('40000000-0000-4000-8000-000000000031', 'rendered-widget', 'rendered');
        [$adapter, , $environment, $renderer] = $this->harness(
            [$definition],
            new RenderOutput(true, '<p>Trusted</p>', ['wpe-dashboard-safe']),
        );
        $environment->userId = 7;
        $environment->siteId = 11;
        $environment->networkId = 13;

        $adapter->registerSiteDashboard();
        ($environment->widgets[0]['callback'])();

        self::assertSame(['<p>Trusted</p>'], $environment->outputs);
        self::assertInstanceOf(ExecutionContext::class, $renderer->lastContext);
        self::assertSame(7, $renderer->lastContext->principal->userId);
        self::assertSame(11, $renderer->lastContext->siteId);
        self::assertSame(13, $renderer->lastContext->networkId);
        self::assertSame(ExecutionChannel::Ui, $renderer->lastContext->channel);
    }

    public function testCallbackEmitsTrustedRenderedErrorHtmlButNotFailureMetadata(): void
    {
        $definition = $this->definition(
            '40000000-0000-4000-8000-000000000032',
            'error-widget',
            'error-widget',
            false,
            null,
            true,
        );
        [$adapter, , $environment, $renderer] = $this->harness(
            [$definition],
            new RenderOutput(false, '', [], RenderFailureCode::DependencyMismatch),
            new RenderOutput(true, '<p>Trusted error state</p>', ['wpe-dashboard-error']),
        );

        $adapter->registerSiteDashboard();
        ($environment->widgets[0]['callback'])();

        self::assertSame(['<p>Trusted error state</p>'], $environment->outputs);
        self::assertSame(2, $renderer->calls);
        self::assertSame(
            ['text' => 'Please try again later.', 'title' => 'Widget unavailable'],
            $renderer->lastInput?->bindings,
        );
    }

    public function testNonRenderedStatesAndEnvironmentFailuresEmitNothing(): void
    {
        $definition = $this->definition('40000000-0000-4000-8000-000000000041', 'failed-widget', 'failed');
        [$adapter, $repository, $environment] = $this->harness(
            [$definition],
            new RenderOutput(false, '', [], RenderFailureCode::InvalidInput),
        );
        $adapter->registerSiteDashboard();
        ($environment->widgets[0]['callback'])();
        self::assertSame([], $environment->outputs);

        $repository->throwOnGet = true;
        ($environment->widgets[0]['callback'])();
        self::assertSame([], $environment->outputs);

        [$registrationFailureAdapter, , $registrationFailureEnvironment] = $this->harness([$definition]);
        $registrationFailureEnvironment->throwOnWidgetRegistration = true;
        $registrationFailureAdapter->registerSiteDashboard();
        self::assertSame([], $registrationFailureEnvironment->widgets);
    }

    public function testHookFailuresAreContainedIndependently(): void
    {
        [$adapter, , $environment] = $this->harness([]);
        $environment->throwOnFirstHook = true;

        $adapter->registerHooks();

        self::assertSame(['wp_network_dashboard_setup'], array_column($environment->hooks, 'hook'));
    }

    /**
     * @param list<Definition> $definitions
     * @return array{0:DashboardWidgetWordPressAdapter,1:object,2:object,3:object}
     */
    private function harness(
        array $definitions,
        ?RenderOutput $renderOutput = null,
        ?RenderOutput $fallbackRenderOutput = null,
    ): array
    {
        $repository = new class($definitions) implements DefinitionRepositoryInterface {
            /** @var array<string,Definition> */
            public array $definitions = [];
            public bool $throwOnByType = false;
            public bool $throwOnGet = false;

            /** @param list<Definition> $definitions */
            public function __construct(array $definitions)
            {
                foreach ($definitions as $definition) {
                    $this->definitions[$definition->id] = $definition;
                }
            }

            public function save(Definition $definition): void
            {
                $this->definitions[$definition->id] = $definition;
            }

            public function get(string $id): ?Definition
            {
                if ($this->throwOnGet) {
                    throw new RuntimeException('repository get failed');
                }
                return $this->definitions[$id] ?? null;
            }

            public function byType(string $type): array
            {
                if ($this->throwOnByType) {
                    throw new RuntimeException('repository catalog failed');
                }
                return array_values(array_filter(
                    $this->definitions,
                    static fn (Definition $definition): bool => $definition->type === $type,
                ));
            }

            public function dependentsOf(string $id): array
            {
                return [];
            }
        };

        $environment = new class implements DashboardWidgetWordPressEnvironmentInterface {
            /** @var list<array{hook:string,callback:callable}> */
            public array $hooks = [];
            /** @var list<array{id:string,title:string,callback:callable,context:string,priority:string}> */
            public array $widgets = [];
            /** @var list<array{hook:string,callback:callable,acceptedArgs:int}> */
            public array $filters = [];
            /** @var list<string> */
            public array $outputs = [];
            public ?int $userId = 7;
            public int $siteId = 11;
            public ?int $networkId = null;
            public bool $throwOnWidgetRegistration = false;
            public bool $throwOnFirstHook = false;
            public bool $throwOnCurrentSiteId = false;
            public bool $throwOnScreenId = false;
            public bool $throwOnClosedPostboxPreference = false;
            public bool $throwOnRegisteredDashboardWidgetDiscovery = false;
            public bool $throwOnHiddenDashboardWidgetPreference = false;
            /** @var array<string,bool> */
            public array $closedPostboxPreferenceByScreen = [];
            /** @var array<string,list<string>> */
            public array $hiddenDashboardWidgetIdsByScreen = [];
            /** @var array<string,list<array<string,mixed>>> */
            public array $registeredDashboardWidgetsByScreen = [];
            private int $hookAttempts = 0;

            public function registerAction(string $hook, callable $callback): void
            {
                ++$this->hookAttempts;
                if ($this->throwOnFirstHook && $this->hookAttempts === 1) {
                    throw new RuntimeException('hook unavailable');
                }
                $this->hooks[] = ['hook' => $hook, 'callback' => $callback];
            }

            public function registerFilter(string $hook, callable $callback, int $acceptedArgs = 1): void
            {
                $this->filters[] = compact('hook', 'callback', 'acceptedArgs');
            }

            public function registerDashboardWidget(
                string $id,
                string $title,
                callable $callback,
                string $context,
                string $priority,
            ): void {
                if ($this->throwOnWidgetRegistration) {
                    throw new RuntimeException('dashboard registration unavailable');
                }
                $this->widgets[] = compact('id', 'title', 'callback', 'context', 'priority');
            }

            public function currentUserId(): ?int { return $this->userId; }
            public function currentSiteId(): int
            {
                if ($this->throwOnCurrentSiteId) {
                    throw new RuntimeException('current site unavailable');
                }
                return $this->siteId;
            }
            public function currentNetworkId(): ?int { return $this->networkId; }
            public function screenId(mixed $screen): ?string
            {
                if ($this->throwOnScreenId) {
                    throw new RuntimeException('screen id unavailable');
                }
                return is_object($screen) && isset($screen->id) && is_string($screen->id)
                    ? $screen->id
                    : null;
            }
            public function hasClosedPostboxPreference(string $screenId): bool
            {
                if ($this->throwOnClosedPostboxPreference) {
                    throw new RuntimeException('closed postbox preference unavailable');
                }
                return $this->closedPostboxPreferenceByScreen[$screenId] ?? false;
            }
            public function currentUserHiddenDashboardWidgetIds(string $screenId): array
            {
                if ($this->throwOnHiddenDashboardWidgetPreference) {
                    throw new RuntimeException('hidden dashboard widget preference unavailable');
                }
                return $this->hiddenDashboardWidgetIdsByScreen[$screenId] ?? [];
            }
            public function discoverRegisteredDashboardWidgets(string $screenId): array
            {
                if ($this->throwOnRegisteredDashboardWidgetDiscovery) {
                    throw new RuntimeException('dashboard inventory unavailable');
                }
                return $this->registeredDashboardWidgetsByScreen[$screenId] ?? [];
            }
            public function outputTrustedHtml(string $html): void { $this->outputs[] = $html; }
        };

        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $blueprints = new ComponentBlueprintRegistry();
        foreach ($catalog->all() as $blueprint) {
            $blueprints->register($blueprint);
        }
        $contentCompiler = new DashboardWidgetContentClassCompiler();
        $renderSourceCompiler = new DashboardWidgetRenderSourceCompiler($blueprints, $contentCompiler, $catalog);
        $visibilityCompiler = new DashboardWidgetVisibilityCompiler();
        $registrationCompiler = new DashboardWidgetRegistrationCompiler(
            $visibilityCompiler,
            $contentCompiler,
            $renderSourceCompiler,
        );
        $capabilities = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool { return true; }
        };
        $roles = new class implements DashboardWidgetRoleMembershipProviderInterface {
            public function isCurrentUserContext(ExecutionContext $context): bool
            {
                return $context->principal->userId === 7 && $context->siteId === 11;
            }
            public function hasAnyRole(ExecutionContext $context, array $roles): bool { return true; }
        };
        $visibilityEvaluator = new DashboardWidgetVisibilityEvaluator($capabilities, $roles);
        $renderer = new class(
            $renderOutput ?? new RenderOutput(true, '<p>Rendered</p>'),
            $fallbackRenderOutput,
        ) implements RendererInterface {
            public ?ExecutionContext $lastContext = null;
            public ?RenderInput $lastInput = null;
            public int $calls = 0;

            public function __construct(
                private RenderOutput $output,
                private ?RenderOutput $fallbackOutput,
            ) {}

            public function render(RenderInput $input, ExecutionContext $context): RenderOutput
            {
                ++$this->calls;
                $this->lastContext = $context;
                $this->lastInput = $input;

                if ($this->calls === 2 && $this->fallbackOutput !== null) {
                    return $this->fallbackOutput;
                }

                return $this->output;
            }
        };
        $executor = new DashboardWidgetRuntimeRenderExecutor(
            $repository,
            $registrationCompiler,
            $visibilityCompiler,
            $visibilityEvaluator,
            $renderSourceCompiler,
            $renderer,
        );

        return [
            new DashboardWidgetWordPressAdapter($repository, $registrationCompiler, $executor, $environment),
            $repository,
            $environment,
            $renderer,
        ];
    }

    /**
     * @param array<string,mixed>|null $target
     */
    private function definition(
        string $id,
        string $slug,
        string $key,
        bool $networkDashboard = false,
        ?array $target = null,
        bool $withErrorState = false,
        bool $defaultHidden = false,
        bool $defaultCollapsed = false,
    ): Definition {
        $widget = [
            'key' => $key,
            'title' => ucfirst(str_replace('-', ' ', $slug)),
            'type' => 'rich_text',
            'context' => 'normal',
            'priority' => 'default',
            'network_dashboard' => $networkDashboard,
            'render_source' => [
                'kind' => 'component_blueprint',
                'blueprint_id' => '31000000-0000-4000-8000-000000000001',
                'blueprint_revision' => 1,
                'bindings' => [
                    'content' => ['source' => 'literal', 'value' => 'Safe'],
                ],
            ],
        ];
        if ($target !== null) {
            $widget['target'] = $target;
        }

        if ($defaultHidden) {
            $widget['inventory'] = ['default_hidden' => true];
        }

        if ($defaultCollapsed) {
            $widget['presentation'] = ['default_collapsed' => true];
        }

        if ($withErrorState) {
            $widget['render_source']['error_state'] = [
                'kind' => 'component_blueprint',
                'blueprint_id' => '31000000-0000-4000-8000-000000000005',
                'blueprint_revision' => 1,
                'bindings' => [
                    'title' => ['source' => 'literal', 'value' => 'Widget unavailable'],
                    'text' => ['source' => 'literal', 'value' => 'Please try again later.'],
                ],
            ];
        }

        return new Definition(
            id: $id,
            slug: $slug,
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: ['widget' => $widget],
            revision: 1,
            dependencies: [],
        );
    }
}
