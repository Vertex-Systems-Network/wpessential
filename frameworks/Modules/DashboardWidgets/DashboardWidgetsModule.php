<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Modules\Cron\CronModule;
use WPEssential\Modules\Cron\CronReadService;
use WPEssential\Modules\Query\QueryModule;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\Rendering\BlueprintRendererDispatcher;
use WPEssential\Platform\Rendering\RenderingServiceRegistrar;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityExposure;
use WPEssential\Platform\WordPress\Auth\WordPressAuthorizationServices;

final class DashboardWidgetsModule implements ModuleInterface
{
    public const SERVICE_READ = 'module.dashboard-widgets.read-service';
    public const SERVICE_REGISTRATION_COMPILER = 'module.dashboard-widgets.registration-compiler';
    public const SERVICE_CONTENT_CLASS_COMPILER = 'module.dashboard-widgets.content-class-compiler';
    public const SERVICE_RENDER_SOURCE_COMPILER = 'module.dashboard-widgets.render-source-compiler';
    public const SERVICE_QUERY_BINDING_EXECUTOR = 'module.dashboard-widgets.query-binding-executor';
    public const SERVICE_DYNAMIC_BINDING_EXECUTOR = 'module.dashboard-widgets.dynamic-binding-executor';
    public const SERVICE_COMPONENT_CATALOG = 'module.dashboard-widgets.component-catalog';
    public const SERVICE_TRUSTED_COMPONENT_RENDERER = 'module.dashboard-widgets.trusted-component-renderer';
    public const SERVICE_COMPONENT_REGISTRAR = 'module.dashboard-widgets.component-registrar';
    public const SERVICE_VISIBILITY_COMPILER = 'module.dashboard-widgets.visibility-compiler';
    public const SERVICE_VISIBILITY_EVALUATOR = 'module.dashboard-widgets.visibility-evaluator';
    public const SERVICE_RUNTIME_RENDER_EXECUTOR = 'module.dashboard-widgets.runtime-render-executor';
    public const SERVICE_WORDPRESS_ADAPTER = 'module.dashboard-widgets.wordpress-adapter';
    public const ABILITY_GET = 'wpessential/dashboard-widgets/get';
    public const ABILITY_CATALOG = 'wpessential/dashboard-widgets/catalog';
    public const CAPABILITY = 'manage_options';

    public function __construct(
        private readonly ?DashboardWidgetWordPressEnvironmentInterface $dashboardEnvironment = null,
    ) {}

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'dashboard-widgets',
            name: 'Dashboard Widgets',
            version: '0.1.0',
            edition: 'pro',
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $definitions = $services->get('platform.definitions');
        $abilities = $services->get('platform.abilities');
        $bridge = $services->get('platform.abilities.wordpress');
        $capabilityChecker = $services->get(WordPressAuthorizationServices::CAPABILITY_CHECKER);
        $dataSources = $services->get('platform.data-sources');
        $queryReadConsumer = $services->get(QueryModule::SERVICE_READ_CONSUMER);
        $dynamicValues = $services->get(RenderingServiceRegistrar::SERVICE_DYNAMIC_VALUES);
        $cronRead = null;
        if ($services->has(CronModule::SERVICE_READ)) {
            $candidateCronRead = $services->get(CronModule::SERVICE_READ);
            if (!$candidateCronRead instanceof CronReadService) {
                throw new LogicException('Dashboard Widgets optional Cron integration requires the canonical Cron read service.');
            }
            $cronRead = $candidateCronRead;
        }

        if (
            !$services->has(RenderingServiceRegistrar::SERVICE_BLUEPRINTS)
            || !$services->has(RenderingServiceRegistrar::SERVICE_RENDERER)
        ) {
            throw new LogicException('Dashboard Widgets requires the canonical shared rendering services.');
        }
        $blueprints = $services->get(RenderingServiceRegistrar::SERVICE_BLUEPRINTS);
        $dispatcher = $services->get(RenderingServiceRegistrar::SERVICE_RENDERER);

        if (!$definitions instanceof DefinitionRepositoryInterface) {
            throw new LogicException('Dashboard Widgets requires the shared Definition Repository.');
        }
        if (!$abilities instanceof AbilityRegistry) {
            throw new LogicException('Dashboard Widgets requires the shared Ability Registry.');
        }
        if (!$bridge instanceof WordPressAbilityBridge) {
            throw new LogicException('Dashboard Widgets requires the shared WordPress Ability bridge.');
        }
        if (!$capabilityChecker instanceof CapabilityCheckerInterface) {
            throw new LogicException('Dashboard Widgets requires the canonical WordPress capability checker.');
        }
        if (!$blueprints instanceof ComponentBlueprintRegistry) {
            throw new LogicException('Dashboard Widgets requires the canonical concrete Component Blueprint registry.');
        }
        if (!$dispatcher instanceof BlueprintRendererDispatcher) {
            throw new LogicException('Dashboard Widgets requires the canonical Blueprint Renderer dispatcher.');
        }
        if (!$dataSources instanceof DataSourceRegistryInterface) {
            throw new LogicException('Dashboard Widgets requires the canonical Data Source Registry.');
        }
        if (!$queryReadConsumer instanceof QueryReadConsumerInterface) {
            throw new LogicException('Dashboard Widgets requires the canonical Query read-consumer service.');
        }
        if (!$dynamicValues instanceof DynamicValueResolverInterface) {
            throw new LogicException('Dashboard Widgets requires the canonical shared Dynamic Value resolver.');
        }

        $read = new DashboardWidgetsReadService($definitions);
        $contentClassCompiler = new DashboardWidgetContentClassCompiler();
        $componentCatalog = new DashboardWidgetComponentBlueprintCatalog();
        $renderSourceCompiler = new DashboardWidgetRenderSourceCompiler(
            $blueprints,
            $contentClassCompiler,
            $componentCatalog,
        );
        $queryBindingExecutor = new DashboardWidgetQueryBindingExecutor($dataSources, $queryReadConsumer);
        $dynamicBindingExecutor = new DashboardWidgetDynamicBindingExecutor($dynamicValues);
        $trustedComponentRenderer = new DashboardWidgetTrustedComponentRenderer($componentCatalog);
        $componentRegistrar = new DashboardWidgetComponentRegistrar(
            $blueprints,
            $dispatcher,
            $componentCatalog,
            $trustedComponentRenderer,
        );
        $visibilityCompiler = new DashboardWidgetVisibilityCompiler();
        $visibilityEvaluator = new DashboardWidgetVisibilityEvaluator(
            $capabilityChecker,
            new WordPressDashboardWidgetRoleMembershipProvider(),
        );
        $registrationCompiler = new DashboardWidgetRegistrationCompiler(
            $visibilityCompiler,
            $contentClassCompiler,
            $renderSourceCompiler,
            $cronRead !== null
                ? static fn (string $id): ?array => $cronRead->get($id)
                : null,
        );
        $runtimeRenderExecutor = new DashboardWidgetRuntimeRenderExecutor(
            $definitions,
            $registrationCompiler,
            $visibilityCompiler,
            $visibilityEvaluator,
            $renderSourceCompiler,
            $dispatcher,
            $queryBindingExecutor,
            $dynamicBindingExecutor,
        );
        $wordpressAdapter = new DashboardWidgetWordPressAdapter(
            $definitions,
            $registrationCompiler,
            $runtimeRenderExecutor,
            $this->dashboardEnvironment ?? new NativeWordPressDashboardWidgetEnvironment(),
        );

        $componentRegistrar->register();

        $services->set(self::SERVICE_READ, $read);
        $services->set(self::SERVICE_CONTENT_CLASS_COMPILER, $contentClassCompiler);
        $services->set(self::SERVICE_RENDER_SOURCE_COMPILER, $renderSourceCompiler);
        $services->set(self::SERVICE_QUERY_BINDING_EXECUTOR, $queryBindingExecutor);
        $services->set(self::SERVICE_DYNAMIC_BINDING_EXECUTOR, $dynamicBindingExecutor);
        $services->set(self::SERVICE_COMPONENT_CATALOG, $componentCatalog);
        $services->set(self::SERVICE_TRUSTED_COMPONENT_RENDERER, $trustedComponentRenderer);
        $services->set(self::SERVICE_COMPONENT_REGISTRAR, $componentRegistrar);
        $services->set(self::SERVICE_VISIBILITY_COMPILER, $visibilityCompiler);
        $services->set(self::SERVICE_REGISTRATION_COMPILER, $registrationCompiler);
        $services->set(self::SERVICE_VISIBILITY_EVALUATOR, $visibilityEvaluator);
        $services->set(self::SERVICE_RUNTIME_RENDER_EXECUTOR, $runtimeRenderExecutor);
        $services->set(self::SERVICE_WORDPRESS_ADAPTER, $wordpressAdapter);

        $channels = [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest];
        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: self::ABILITY_GET,
                ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: [
                    'type' => 'object',
                    'required' => ['id'],
                    'properties' => ['id' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 191]],
                    'additionalProperties' => false,
                ],
                outputSchema: ['type' => ['object', 'null']],
            ),
            new DashboardWidgetsReadAbilityHandler($read, DashboardWidgetsReadAbilityHandler::GET),
            'Read Dashboard Widget definition',
            'Reads one canonical Dashboard Widget definition without mutating dashboard widget or authorization state.',
        );
        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: self::ABILITY_CATALOG,
                ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: ['type' => 'object', 'additionalProperties' => false],
                outputSchema: ['type' => 'array'],
            ),
            new DashboardWidgetsReadAbilityHandler($read, DashboardWidgetsReadAbilityHandler::CATALOG),
            'Read Dashboard Widgets catalog',
            'Reads the deterministic canonical Dashboard Widgets definition catalog without mutation.',
        );
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        $adapter = $services->get(self::SERVICE_WORDPRESS_ADAPTER);
        if (!$adapter instanceof DashboardWidgetWordPressAdapter) {
            throw new LogicException('Dashboard Widgets requires its WordPress Dashboard adapter service.');
        }
        $adapter->registerHooks();
    }

    private function registerAbility(
        AbilityRegistry $abilities,
        WordPressAbilityBridge $bridge,
        AbilityDescriptor $descriptor,
        AbilityHandlerInterface $handler,
        string $label,
        string $description,
    ): void {
        $abilities->register($descriptor, $handler);
        $bridge->expose(new WordPressAbilityExposure(
            internalName: $descriptor->name,
            label: $label,
            description: $description,
            showInRest: true,
        ));
    }
}
