<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\AuditLoggerInterface;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Abilities\InputValidation\AbilityInputValidator;
use WPEssential\Platform\Assets\AssetDescriptor;
use WPEssential\Platform\Assets\AssetLoadStrategy;
use WPEssential\Platform\Assets\AssetRegistry;
use WPEssential\Platform\Assets\AssetScope;
use WPEssential\Platform\Assets\AssetServices;
use WPEssential\Platform\Assets\TrustedAssetBuildEntry;
use WPEssential\Platform\Assets\TrustedAssetBuildEntryRegistry;
use WPEssential\Platform\Audit\AuditServices;
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
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AbilityAjaxHandler;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;
use WPEssential\Platform\WordPress\Ajax\AjaxRoute;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Ajax\WordPressAjaxGateway;
use WPEssential\Platform\WordPress\Auth\WordPressAuthorizationServices;
use WPEssential\Platform\WordPress\Security\NonceOperation;

final class DashboardWidgetsModule implements ModuleInterface
{
    public const SERVICE_READ = 'module.dashboard-widgets.read-service';
    public const SERVICE_DIAGNOSTICS = 'module.dashboard-widgets.diagnostics';
    public const SERVICE_PERSONAL_PREFERENCES = 'module.dashboard-widgets.personal-preferences';
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
    public const SERVICE_ACTION_AUTHORIZATION_EVALUATOR = 'module.dashboard-widgets.action-authorization-evaluator';
    public const SERVICE_ACTION_INPUT_BINDER = 'module.dashboard-widgets.action-input-binder';
    public const SERVICE_FORM_ACTION_PRESENTER = 'module.dashboard-widgets.form-action-presenter';
    public const SERVICE_FORM_ACTION_PREFLIGHT = 'module.dashboard-widgets.form-action-preflight';
    public const SERVICE_FORM_ACTION_EXECUTION = 'module.dashboard-widgets.form-action-execution';
    public const SERVICE_FORM_ACTION_RESULT_ADAPTER = 'module.dashboard-widgets.form-action-result-adapter';
    public const SERVICE_RUNTIME_RENDER_EXECUTOR = 'module.dashboard-widgets.runtime-render-executor';
    public const SERVICE_WORDPRESS_ADAPTER = 'module.dashboard-widgets.wordpress-adapter';
    public const ABILITY_GET = 'wpessential/dashboard-widgets/get';
    public const ABILITY_CATALOG = 'wpessential/dashboard-widgets/catalog';
    public const ABILITY_DIAGNOSTICS = 'wpessential/dashboard-widgets/diagnostics';
    public const ABILITY_DISMISS = DashboardWidgetPersonalPreferenceAbilityHandler::ABILITY_DISMISS;
    public const ABILITY_RESET_LAYOUT = DashboardWidgetPersonalPreferenceAbilityHandler::ABILITY_RESET;
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

        foreach ([
            'platform.abilities.contexts',
            'platform.ajax.routes',
            'platform.ajax.dispatcher',
            'platform.ajax.gateway',
            AssetServices::REGISTRY,
            AssetServices::BUILD_ENTRIES,
            AuditServices::LOGGER,
        ] as $serviceId) {
            if (!$services->has($serviceId)) {
                throw new LogicException(sprintf(
                    'Dashboard Widgets form-action preflight requires canonical shared service "%s".',
                    $serviceId,
                ));
            }
        }
        $contexts = $services->get('platform.abilities.contexts');
        $ajaxRoutes = $services->get('platform.ajax.routes');
        $ajaxDispatcher = $services->get('platform.ajax.dispatcher');
        $ajaxGateway = $services->get('platform.ajax.gateway');
        $assetRegistry = $services->get(AssetServices::REGISTRY);
        $assetBuildEntries = $services->get(AssetServices::BUILD_ENTRIES);
        $audit = $services->get(AuditServices::LOGGER);

        if (
            !$contexts instanceof WordPressExecutionContextFactory
            || !$ajaxRoutes instanceof AjaxRouteRegistry
            || !$ajaxDispatcher instanceof AjaxDispatcher
            || !$ajaxGateway instanceof WordPressAjaxGateway
            || !$assetRegistry instanceof AssetRegistry
            || !$assetBuildEntries instanceof TrustedAssetBuildEntryRegistry
            || !$audit instanceof AuditLoggerInterface
        ) {
            throw new LogicException(
                'Dashboard Widgets form-action preflight requires canonical WordPress AJAX/context/audit services.',
            );
        }

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

        $assetRegistry->register(new AssetDescriptor(
            handle: 'wpe-dashboard-form-action',
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            scope: AssetScope::Admin,
            loadStrategy: AssetLoadStrategy::AdminRoute,
            adminRoutes: [
                '/wp-admin/index.php',
                '/wp-admin/network/index.php',
            ],
        ));
        $assetBuildEntries->register(new TrustedAssetBuildEntry(
            assetHandle: 'wpe-dashboard-form-action',
            scriptEntry: 'main',
        ));

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
        $actionAuthorizationEvaluator = new DashboardWidgetActionAuthorizationEvaluator($abilities);
        $abilityResolver = static function (string $name) use ($abilities): ?array {
            $descriptor = $abilities->descriptor($name);
            if (!$descriptor instanceof AbilityDescriptor) {
                return null;
            }

            return [
                'name' => $descriptor->name,
                'owner_surface_id' => $descriptor->ownerSurfaceId,
                'mutates' => $descriptor->mutates,
                'ui_allowed' => $descriptor->allows(ExecutionChannel::Ui),
                'input_schema' => $descriptor->inputSchema,
            ];
        };
        $registrationCompiler = new DashboardWidgetRegistrationCompiler(
            $visibilityCompiler,
            $contentClassCompiler,
            $renderSourceCompiler,
            $cronRead !== null
                ? static fn (string $id): ?array => $cronRead->get($id)
                : null,
            $abilityResolver,
        );
        $actionInputBinder = new DashboardWidgetActionInputBinder(
            new AbilityInputValidator(),
            $dynamicValues,
            $abilityResolver,
        );
        $diagnostics = new DashboardWidgetDiagnosticsAbilityHandler(
            $definitions,
            $contentClassCompiler,
            $registrationCompiler,
        );
        $personalPreferences = new DashboardWidgetPersonalPreferenceStore();
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
        $preflightHandler = new DashboardWidgetFormActionPreflightAjaxHandler(
            $definitions,
            $contentClassCompiler,
            $registrationCompiler,
            $actionInputBinder,
            $actionAuthorizationEvaluator,
            $contexts,
            $audit,
        );
        $ajaxRoutes->register(new AjaxRoute(
            type: DashboardWidgetFormActionPresenter::ROUTE_TYPE,
            handler: $preflightHandler,
            operation: NonceOperation::Apply,
            capability: null,
            allowGuests: false,
            requiresNonce: true,
        ));
        $executionResultAdapter = new DashboardWidgetFormActionExecutionResultAdapter();
        $executionHandler = new DashboardWidgetFormActionExecutionAjaxHandler(
            $definitions,
            $contentClassCompiler,
            $registrationCompiler,
            $actionInputBinder,
            $actionAuthorizationEvaluator,
            $contexts,
            $audit,
            $abilities,
            $executionResultAdapter,
        );
        $ajaxRoutes->register(new AjaxRoute(
            type: DashboardWidgetFormActionExecutionAjaxHandler::ROUTE_TYPE,
            handler: $executionHandler,
            operation: NonceOperation::Apply,
            capability: null,
            allowGuests: false,
            requiresNonce: true,
        ));
        $formActionPresenter = new DashboardWidgetFormActionPresenter(
            $ajaxGateway->action(),
            static fn (): string => $ajaxDispatcher->createNonce(
                DashboardWidgetFormActionPresenter::ROUTE_TYPE,
            ),
            static fn (): string => $ajaxDispatcher->createNonce(
                DashboardWidgetFormActionExecutionAjaxHandler::ROUTE_TYPE,
            ),
        );
        $dashboardEnvironment = $this->dashboardEnvironment
            ?? new NativeWordPressDashboardWidgetEnvironment();
        $wordpressAdapter = new DashboardWidgetWordPressAdapter(
            $definitions,
            $registrationCompiler,
            $runtimeRenderExecutor,
            $dashboardEnvironment,
            $contentClassCompiler,
            $formActionPresenter,
            $personalPreferences,
        );

        $componentRegistrar->register();

        $services->set(self::SERVICE_READ, $read);
        $services->set(self::SERVICE_DIAGNOSTICS, $diagnostics);
        $services->set(self::SERVICE_PERSONAL_PREFERENCES, $personalPreferences);
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
        $services->set(self::SERVICE_ACTION_AUTHORIZATION_EVALUATOR, $actionAuthorizationEvaluator);
        $services->set(self::SERVICE_ACTION_INPUT_BINDER, $actionInputBinder);
        $services->set(self::SERVICE_FORM_ACTION_PRESENTER, $formActionPresenter);
        $services->set(self::SERVICE_FORM_ACTION_PREFLIGHT, $preflightHandler);
        $services->set(self::SERVICE_FORM_ACTION_RESULT_ADAPTER, $executionResultAdapter);
        $services->set(self::SERVICE_FORM_ACTION_EXECUTION, $executionHandler);
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
        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: self::ABILITY_DIAGNOSTICS,
                ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'definition_id' => [
                            'type' => 'string',
                            'minLength' => 36,
                            'maxLength' => 36,
                        ],
                    ],
                    'additionalProperties' => false,
                ],
                outputSchema: ['type' => 'object'],
            ),
            $diagnostics,
            'Read Dashboard Widgets diagnostics',
            'Reads a bounded safe Dashboard Widgets runtime-readiness snapshot without exposing raw payloads or mutating state.',
        );

        $preferenceActions = [
            DashboardWidgetPersonalPreferenceAbilityHandler::DISMISS => [
                self::ABILITY_DISMISS,
                DashboardWidgetPersonalPreferenceAbilityHandler::AJAX_DISMISS,
                [
                    'type' => 'object',
                    'required' => ['definition_id', 'expected_revision', 'screen'],
                    'properties' => [
                        'definition_id' => ['type' => 'string', 'minLength' => 36, 'maxLength' => 36],
                        'expected_revision' => ['type' => 'integer', 'minimum' => 1],
                        'screen' => ['type' => 'string', 'enum' => ['site', 'network']],
                    ],
                    'additionalProperties' => false,
                ],
            ],
            DashboardWidgetPersonalPreferenceAbilityHandler::RESET => [
                self::ABILITY_RESET_LAYOUT,
                DashboardWidgetPersonalPreferenceAbilityHandler::AJAX_RESET,
                [
                    'type' => 'object',
                    'required' => ['screen'],
                    'properties' => [
                        'screen' => ['type' => 'string', 'enum' => ['site', 'network']],
                    ],
                    'additionalProperties' => false,
                ],
            ],
        ];
        foreach ($preferenceActions as $action => [$abilityName, $ajaxType, $inputSchema]) {
            $descriptor = new AbilityDescriptor(
                name: $abilityName,
                ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: true,
                channels: [ExecutionChannel::Internal, ExecutionChannel::Ui],
                inputSchema: $inputSchema,
                outputSchema: ['type' => 'object'],
            );
            $handler = new DashboardWidgetPersonalPreferenceAbilityHandler(
                $definitions,
                $registrationCompiler,
                $personalPreferences,
                $action,
            );
            $this->registerAbility(
                $abilities,
                $bridge,
                $descriptor,
                $handler,
                $action === DashboardWidgetPersonalPreferenceAbilityHandler::DISMISS
                    ? 'Dismiss Dashboard Widget'
                    : 'Reset Dashboard Widget layout',
                $action === DashboardWidgetPersonalPreferenceAbilityHandler::DISMISS
                    ? 'Dismisses one canonical dismissible WPE Dashboard Widget for the current user.'
                    : 'Resets only WPE-owned Dashboard Widget preferences while preserving core and third-party state.',
            );
            $ajaxRoutes->register(new AjaxRoute(
                type: $ajaxType,
                handler: new AbilityAjaxHandler($abilities, $descriptor->name, $contexts),
                operation: NonceOperation::Update,
            ));
        }
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
