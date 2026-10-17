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
use WPEssential\Contracts\DefinitionCreateOnlyRepositoryInterface;
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
	public const SERVICE_PRESET_COMPILER = 'module.dashboard-widgets.preset-compiler';
	public const SERVICE_PRESET_RESOLVER = 'module.dashboard-widgets.preset-resolver';
	public const SERVICE_PRESET_READ = 'module.dashboard-widgets.preset-read';
	public const SERVICE_PRESET_PORTABILITY = 'module.dashboard-widgets.preset-portability';
	public const SERVICE_PRESET_PORTABILITY_FRESHNESS = 'module.dashboard-widgets.preset-portability-freshness';
	public const SERVICE_PRESET_IMPORT_PREFLIGHT = 'module.dashboard-widgets.preset-import-preflight';
	public const SERVICE_PRESET_DRAFT_IMPORT = 'module.dashboard-widgets.preset-draft-import';
	public const SERVICE_PRESET_MAPPED_DRAFT_IMPORT = 'module.dashboard-widgets.preset-mapped-draft-import';
	public const SERVICE_PRESET_DRAFT_PUBLISH_REVIEW = 'module.dashboard-widgets.preset-draft-publish-review';
	public const SERVICE_PRESET_MAPPING_PREVIEW = 'module.dashboard-widgets.preset-mapping-preview';
	public const SERVICE_MULTISITE_POLICY_COMPILER = 'module.dashboard-widgets.multisite-policy-compiler';
	public const SERVICE_MULTISITE_POLICY_RESOLVER = 'module.dashboard-widgets.multisite-policy-resolver';
	public const SERVICE_MULTISITE_POLICY_READ = 'module.dashboard-widgets.multisite-policy-read';
	public const SERVICE_SUBSITE_PRESET_OVERRIDE_COMPILER = 'module.dashboard-widgets.subsite-preset-override-compiler';
	public const SERVICE_EFFECTIVE_MULTISITE_PRESET = 'module.dashboard-widgets.effective-multisite-preset';
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
	public const SERVICE_MANUAL_REFRESH_PRESENTER = 'module.dashboard-widgets.manual-refresh-presenter';
	public const SERVICE_MANUAL_REFRESH_HANDLER = 'module.dashboard-widgets.manual-refresh-handler';
	public const SERVICE_RUNTIME_RENDER_EXECUTOR = 'module.dashboard-widgets.runtime-render-executor';
	public const SERVICE_RUNTIME_CLOCK = 'module.dashboard-widgets.runtime-clock';
	public const SERVICE_WORDPRESS_ADAPTER = 'module.dashboard-widgets.wordpress-adapter';
	public const ABILITY_GET = 'wpessential/dashboard-widgets/get';
	public const ABILITY_CATALOG = 'wpessential/dashboard-widgets/catalog';
	public const ABILITY_DIAGNOSTICS = 'wpessential/dashboard-widgets/diagnostics';
	public const ABILITY_PRESET_GET = 'wpessential/dashboard-widgets/preset-get';
	public const ABILITY_PRESET_PORTABILITY = 'wpessential/dashboard-widgets/preset-portability-snapshot';
	public const ABILITY_PRESET_PORTABILITY_FRESHNESS = 'wpessential/dashboard-widgets/preset-portability-freshness';
	public const ABILITY_PRESET_IMPORT_PREFLIGHT = 'wpessential/dashboard-widgets/preset-import-preflight';
	public const ABILITY_PRESET_MAPPING_PREVIEW = 'wpessential/dashboard-widgets/preset-portability-mapping-preview';
	public const ABILITY_PRESET_CATALOG = 'wpessential/dashboard-widgets/preset-catalog';
	public const ABILITY_EFFECTIVE_PRESET = 'wpessential/dashboard-widgets/effective-preset';
	public const ABILITY_MULTISITE_POLICY_GET = 'wpessential/dashboard-widgets/multisite-policy-get';
	public const ABILITY_MULTISITE_POLICY_CATALOG = 'wpessential/dashboard-widgets/multisite-policy-catalog';
	public const ABILITY_EFFECTIVE_MULTISITE_POLICY = 'wpessential/dashboard-widgets/effective-multisite-policy';
	public const ABILITY_EFFECTIVE_MULTISITE_PRESET = 'wpessential/dashboard-widgets/effective-multisite-preset';
	public const ABILITY_DISMISS = DashboardWidgetPersonalPreferenceAbilityHandler::ABILITY_DISMISS;
	public const ABILITY_RESET_LAYOUT = DashboardWidgetPersonalPreferenceAbilityHandler::ABILITY_RESET;
	public const CAPABILITY = 'manage_options';

	public function __construct(
		private readonly ?DashboardWidgetWordPressEnvironmentInterface $dashboardEnvironment = null,
		private readonly ?DashboardWidgetRuntimeClock $runtimeClock = null,
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

	public function register( ServiceRegistryInterface $services ): void
	{
		// Fail closed before constructing or publishing any widget services.
		foreach (
			array(
				RenderingServiceRegistrar::SERVICE_DYNAMIC_VALUES,
				RenderingServiceRegistrar::SERVICE_BLUEPRINTS,
				RenderingServiceRegistrar::SERVICE_RENDERER,
			) as $required_rendering_service
		) {
			if ( ! $services->has( $required_rendering_service ) ) {
				throw new LogicException( 'Dashboard Widgets requires the canonical shared rendering services.' );
			}
		}

		$definitions = $services->get('platform.definitions');
		$abilities = $services->get('platform.abilities');
		$bridge = $services->get('platform.abilities.wordpress');
		$capability_checker = $services->get( WordPressAuthorizationServices::CAPABILITY_CHECKER );
		$data_sources = $services->get('platform.data-sources');
		$query_read_consumer = $services->get( QueryModule::SERVICE_READ_CONSUMER );
		$dynamic_values = $services->get( RenderingServiceRegistrar::SERVICE_DYNAMIC_VALUES );

		foreach ( [
			'platform.abilities.contexts',
			'platform.ajax.routes',
			'platform.ajax.dispatcher',
			'platform.ajax.gateway',
			AssetServices::REGISTRY,
			AssetServices::BUILD_ENTRIES,
			AuditServices::LOGGER,
		] as $service_id ) {
			if ( !$services->has( $service_id ) ) {
				throw new LogicException(sprintf(
					'Dashboard Widgets form-action preflight requires canonical shared service "%s".',
					$service_id,
				));
			}
		}
		$contexts = $services->get('platform.abilities.contexts');
		$ajax_routes = $services->get('platform.ajax.routes');
		$ajax_dispatcher = $services->get('platform.ajax.dispatcher');
		$ajax_gateway = $services->get('platform.ajax.gateway');
		$asset_registry = $services->get( AssetServices::REGISTRY );
		$asset_build_entries = $services->get( AssetServices::BUILD_ENTRIES );
		$audit = $services->get( AuditServices::LOGGER );

		if (
			!$contexts instanceof WordPressExecutionContextFactory
			|| !$ajax_routes instanceof AjaxRouteRegistry
			|| !$ajax_dispatcher instanceof AjaxDispatcher
			|| !$ajax_gateway instanceof WordPressAjaxGateway
			|| !$asset_registry instanceof AssetRegistry
			|| !$asset_build_entries instanceof TrustedAssetBuildEntryRegistry
			|| !$audit instanceof AuditLoggerInterface
		) {
			throw new LogicException(
				'Dashboard Widgets form-action preflight requires canonical WordPress AJAX/context/audit services.',
			);
		}

		$cron_read = null;
		if ( $services->has( CronModule::SERVICE_READ ) ) {
			$candidate_cron_read = $services->get( CronModule::SERVICE_READ );
			if ( !$candidate_cron_read instanceof CronReadService ) {
				throw new LogicException('Dashboard Widgets optional Cron integration requires the canonical Cron read service.');
			}
			$cron_read = $candidate_cron_read;
		}

		if (
			!$services->has( RenderingServiceRegistrar::SERVICE_BLUEPRINTS )
			|| !$services->has( RenderingServiceRegistrar::SERVICE_RENDERER )
		) {
			throw new LogicException('Dashboard Widgets requires the canonical shared rendering services.');
		}
		$blueprints = $services->get( RenderingServiceRegistrar::SERVICE_BLUEPRINTS );
		$dispatcher = $services->get( RenderingServiceRegistrar::SERVICE_RENDERER );

		if ( !$definitions instanceof DefinitionRepositoryInterface ) {
			throw new LogicException('Dashboard Widgets requires the shared Definition Repository.');
		}
		if ( !$abilities instanceof AbilityRegistry ) {
			throw new LogicException('Dashboard Widgets requires the shared Ability Registry.');
		}
		if ( !$bridge instanceof WordPressAbilityBridge ) {
			throw new LogicException('Dashboard Widgets requires the shared WordPress Ability bridge.');
		}
		if ( !$capability_checker instanceof CapabilityCheckerInterface ) {
			throw new LogicException('Dashboard Widgets requires the canonical WordPress capability checker.');
		}
		if ( !$blueprints instanceof ComponentBlueprintRegistry ) {
			throw new LogicException('Dashboard Widgets requires the canonical concrete Component Blueprint registry.');
		}
		if ( !$dispatcher instanceof BlueprintRendererDispatcher ) {
			throw new LogicException('Dashboard Widgets requires the canonical Blueprint Renderer dispatcher.');
		}
		if ( !$data_sources instanceof DataSourceRegistryInterface ) {
			throw new LogicException('Dashboard Widgets requires the canonical Data Source Registry.');
		}
		if ( !$query_read_consumer instanceof QueryReadConsumerInterface ) {
			throw new LogicException('Dashboard Widgets requires the canonical Query read-consumer service.');
		}
		if ( !$dynamic_values instanceof DynamicValueResolverInterface ) {
			throw new LogicException('Dashboard Widgets requires the canonical shared Dynamic Value resolver.');
		}

		$asset_registry->register(new AssetDescriptor(
			handle: 'wpe-dashboard-form-action',
			ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
			scope: AssetScope::Admin,
			loadStrategy: AssetLoadStrategy::AdminRoute,
			adminRoutes: [
				'/wp-admin/index.php',
				'/wp-admin/network/index.php',
			],
		));
		$asset_build_entries->register(new TrustedAssetBuildEntry(
			assetHandle: 'wpe-dashboard-form-action',
			scriptEntry: 'main',
		));

		$read = new DashboardWidgetsReadService( $definitions );
		$content_class_compiler = new DashboardWidgetContentClassCompiler();
		$component_catalog = new DashboardWidgetComponentBlueprintCatalog();
		$render_source_compiler = new DashboardWidgetRenderSourceCompiler(
			$blueprints,
			$content_class_compiler,
			$component_catalog,
		);
		$query_binding_executor = new DashboardWidgetQueryBindingExecutor( $data_sources, $query_read_consumer );
		$dynamic_binding_executor = new DashboardWidgetDynamicBindingExecutor( $dynamic_values );
		$trusted_component_renderer = new DashboardWidgetTrustedComponentRenderer( $component_catalog );
		$component_registrar = new DashboardWidgetComponentRegistrar(
			$blueprints,
			$dispatcher,
			$component_catalog,
			$trusted_component_renderer,
		);
		$visibility_compiler = new DashboardWidgetVisibilityCompiler();
		$role_memberships = new WordPressDashboardWidgetRoleMembershipProvider();
		$visibility_evaluator = new DashboardWidgetVisibilityEvaluator(
			$capability_checker,
			$role_memberships,
		);
		$preset_compiler = new DashboardWidgetPresetCompiler( $definitions );
		$preset_resolver = new DashboardWidgetPresetResolver(
			$definitions,
			$preset_compiler,
			$role_memberships,
		);
		$preset_read = new DashboardWidgetPresetReadService(
			$definitions,
			$preset_compiler,
			$preset_resolver,
		);
		$preset_portability = new DashboardWidgetPresetPortabilityReadService( $preset_read );
		$preset_portability_freshness = new DashboardWidgetPresetPortabilityFreshnessService( $preset_portability );
		$preset_import_preflight = new DashboardWidgetPresetImportPreflightService( $definitions, $preset_compiler );
		$preset_mapping_preview = new DashboardWidgetPresetPortabilityMappingPreviewService( $preset_import_preflight );
		$multisite_policy_compiler = new DashboardWidgetMultisitePolicyCompiler( $definitions, $preset_compiler );
		$multisite_policy_resolver = new DashboardWidgetMultisitePolicyResolver(
			$definitions,
			$multisite_policy_compiler,
			$role_memberships,
			$capability_checker,
		);
		$multisite_policy_read = new DashboardWidgetMultisitePolicyReadService(
			$definitions,
			$multisite_policy_compiler,
			$multisite_policy_resolver,
		);
		$subsite_override_compiler = new DashboardWidgetSubsitePresetOverrideCompiler( $definitions, $preset_compiler );
		$effective_multisite_preset = new DashboardWidgetMultisiteEffectivePresetResolver(
			$multisite_policy_resolver,
			$definitions,
			$subsite_override_compiler,
		);
		$action_authorization_evaluator = new DashboardWidgetActionAuthorizationEvaluator( $abilities );
		$ability_resolver = static function ( string $name ) use ( $abilities ): ?array {
			$descriptor = $abilities->descriptor( $name );
			if ( !$descriptor instanceof AbilityDescriptor ) {
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
		$registration_compiler = new DashboardWidgetRegistrationCompiler(
			$visibility_compiler,
			$content_class_compiler,
			$render_source_compiler,
			$cron_read !== null
				? static fn ( string $id ): ?array => $cron_read->get( $id )
				: null,
			$ability_resolver,
		);
		$runtime_clock = $this->runtimeClock ?? new DashboardWidgetRuntimeClock();
		$action_input_binder = new DashboardWidgetActionInputBinder(
			new AbilityInputValidator(),
			$dynamic_values,
			$ability_resolver,
		);
		$diagnostics = new DashboardWidgetDiagnosticsAbilityHandler(
			$definitions,
			$content_class_compiler,
			$registration_compiler,
			$runtime_clock,
		);
		$personal_preferences = new DashboardWidgetPersonalPreferenceStore();
		$runtime_render_executor = new DashboardWidgetRuntimeRenderExecutor(
			$definitions,
			$registration_compiler,
			$visibility_compiler,
			$visibility_evaluator,
			$render_source_compiler,
			$dispatcher,
			$query_binding_executor,
			$dynamic_binding_executor,
			$runtime_clock,
		);
		$manual_refresh_handler = new DashboardWidgetManualRefreshAjaxHandler(
			$definitions,
			$registration_compiler,
			$runtime_render_executor,
			$contexts,
		);
		$ajax_routes->register(new AjaxRoute(
			type: DashboardWidgetManualRefreshAjaxHandler::ROUTE_TYPE,
			handler: $manual_refresh_handler,
			operation: NonceOperation::Apply,
			capability: null,
			allowGuests: false,
			requiresNonce: true,
		));
		$manual_refresh_presenter = new DashboardWidgetManualRefreshPresenter(
			$ajax_gateway->action(),
			static fn (): string => $ajax_dispatcher->createNonce(
				DashboardWidgetManualRefreshAjaxHandler::ROUTE_TYPE,
			),
		);

		$preflight_handler = new DashboardWidgetFormActionPreflightAjaxHandler(
			$definitions,
			$content_class_compiler,
			$registration_compiler,
			$action_input_binder,
			$action_authorization_evaluator,
			$contexts,
			$audit,
			$runtime_clock,
		);
		$ajax_routes->register(new AjaxRoute(
			type: DashboardWidgetFormActionPresenter::ROUTE_TYPE,
			handler: $preflight_handler,
			operation: NonceOperation::Apply,
			capability: null,
			allowGuests: false,
			requiresNonce: true,
		));
		$execution_result_adapter = new DashboardWidgetFormActionExecutionResultAdapter();
		$execution_handler = new DashboardWidgetFormActionExecutionAjaxHandler(
			$definitions,
			$content_class_compiler,
			$registration_compiler,
			$action_input_binder,
			$action_authorization_evaluator,
			$contexts,
			$audit,
			$abilities,
			$execution_result_adapter,
			$runtime_clock,
		);
		$ajax_routes->register(new AjaxRoute(
			type: DashboardWidgetFormActionExecutionAjaxHandler::ROUTE_TYPE,
			handler: $execution_handler,
			operation: NonceOperation::Apply,
			capability: null,
			allowGuests: false,
			requiresNonce: true,
		));
		$form_action_presenter = new DashboardWidgetFormActionPresenter(
			$ajax_gateway->action(),
			static fn (): string => $ajax_dispatcher->createNonce(
				DashboardWidgetFormActionPresenter::ROUTE_TYPE,
			),
			static fn (): string => $ajax_dispatcher->createNonce(
				DashboardWidgetFormActionExecutionAjaxHandler::ROUTE_TYPE,
			),
		);
		$dashboard_environment = $this->dashboardEnvironment
			?? new NativeWordPressDashboardWidgetEnvironment();
		$wordpress_adapter = new DashboardWidgetWordPressAdapter(
			$definitions,
			$registration_compiler,
			$runtime_render_executor,
			$dashboard_environment,
			$content_class_compiler,
			$form_action_presenter,
			$personal_preferences,
			$runtime_clock,
			$manual_refresh_presenter,
		);

		$component_registrar->register();

		$services->set( self::SERVICE_READ, $read );
		$services->set( self::SERVICE_DIAGNOSTICS, $diagnostics );
		$services->set( self::SERVICE_PERSONAL_PREFERENCES, $personal_preferences );
		$services->set( self::SERVICE_PRESET_COMPILER, $preset_compiler );
		$services->set( self::SERVICE_PRESET_RESOLVER, $preset_resolver );
		$services->set( self::SERVICE_PRESET_READ, $preset_read );
		$services->set( self::SERVICE_PRESET_PORTABILITY, $preset_portability );
		$services->set( self::SERVICE_PRESET_PORTABILITY_FRESHNESS, $preset_portability_freshness );
		$services->set( self::SERVICE_PRESET_IMPORT_PREFLIGHT, $preset_import_preflight );
		if ( $definitions instanceof DefinitionCreateOnlyRepositoryInterface ) {
			$draft_importer = new DashboardWidgetPresetDraftImportService(
				$definitions,
				$preset_import_preflight,
				$capability_checker,
			);
			$services->set( self::SERVICE_PRESET_DRAFT_IMPORT, $draft_importer );
			// Internal-only Draft eligibility assessment. Never grant publish
			// permissions or register an Ability/UI/REST/AJAX route.
			$services->set(
				self::SERVICE_PRESET_DRAFT_PUBLISH_REVIEW,
				new DashboardWidgetPresetDraftPublishReviewService(
					$definitions,
					$preset_compiler,
					$draft_importer,
				),
			);
			// Service-only. No REST/UI/CLI/AI/WordPress Ability registration.
			$services->set(
				self::SERVICE_PRESET_MAPPED_DRAFT_IMPORT,
				new DashboardWidgetPresetMappedDraftImportService(
					$preset_mapping_preview,
					$draft_importer,
				),
			);
		}
		$services->set( self::SERVICE_PRESET_MAPPING_PREVIEW, $preset_mapping_preview );
		$services->set( self::SERVICE_MULTISITE_POLICY_COMPILER, $multisite_policy_compiler );
		$services->set( self::SERVICE_MULTISITE_POLICY_RESOLVER, $multisite_policy_resolver );
		$services->set( self::SERVICE_MULTISITE_POLICY_READ, $multisite_policy_read );
		$services->set( self::SERVICE_SUBSITE_PRESET_OVERRIDE_COMPILER, $subsite_override_compiler );
		$services->set( self::SERVICE_EFFECTIVE_MULTISITE_PRESET, $effective_multisite_preset );
		$services->set( self::SERVICE_CONTENT_CLASS_COMPILER, $content_class_compiler );
		$services->set( self::SERVICE_RENDER_SOURCE_COMPILER, $render_source_compiler );
		$services->set( self::SERVICE_QUERY_BINDING_EXECUTOR, $query_binding_executor );
		$services->set( self::SERVICE_DYNAMIC_BINDING_EXECUTOR, $dynamic_binding_executor );
		$services->set( self::SERVICE_COMPONENT_CATALOG, $component_catalog );
		$services->set( self::SERVICE_TRUSTED_COMPONENT_RENDERER, $trusted_component_renderer );
		$services->set( self::SERVICE_COMPONENT_REGISTRAR, $component_registrar );
		$services->set( self::SERVICE_VISIBILITY_COMPILER, $visibility_compiler );
		$services->set( self::SERVICE_REGISTRATION_COMPILER, $registration_compiler );
		$services->set( self::SERVICE_VISIBILITY_EVALUATOR, $visibility_evaluator );
		$services->set( self::SERVICE_ACTION_AUTHORIZATION_EVALUATOR, $action_authorization_evaluator );
		$services->set( self::SERVICE_ACTION_INPUT_BINDER, $action_input_binder );
		$services->set( self::SERVICE_FORM_ACTION_PRESENTER, $form_action_presenter );
		$services->set( self::SERVICE_FORM_ACTION_PREFLIGHT, $preflight_handler );
		$services->set( self::SERVICE_FORM_ACTION_RESULT_ADAPTER, $execution_result_adapter );
		$services->set( self::SERVICE_FORM_ACTION_EXECUTION, $execution_handler );
		$services->set( self::SERVICE_MANUAL_REFRESH_PRESENTER, $manual_refresh_presenter );
		$services->set( self::SERVICE_MANUAL_REFRESH_HANDLER, $manual_refresh_handler );
		$services->set( self::SERVICE_RUNTIME_RENDER_EXECUTOR, $runtime_render_executor );
		$services->set( self::SERVICE_RUNTIME_CLOCK, $runtime_clock );
		$services->set( self::SERVICE_WORDPRESS_ADAPTER, $wordpress_adapter );

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
			new DashboardWidgetsReadAbilityHandler( $read, DashboardWidgetsReadAbilityHandler::GET ),
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
			new DashboardWidgetsReadAbilityHandler( $read, DashboardWidgetsReadAbilityHandler::CATALOG ),
			'Read Dashboard Widgets catalog',
			'Reads the deterministic canonical Dashboard Widgets definition catalog without mutation.',
		);
		$this->registerAbility(
			$abilities,
			$bridge,
			new AbilityDescriptor(
				name: self::ABILITY_PRESET_GET,
				ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
				capability: self::CAPABILITY,
				mutates: false,
				channels: $channels,
				inputSchema: [
					'type' => 'object',
					'required' => ['id'],
					'properties' => [
						'id' => ['type' => 'string', 'minLength' => 36, 'maxLength' => 36],
					],
					'additionalProperties' => false,
				],
				outputSchema: ['type' => ['object', 'null']],
			),
			new DashboardWidgetPresetReadAbilityHandler(
				$preset_read,
				DashboardWidgetPresetReadAbilityHandler::GET,
			),
			'Read Dashboard Widget preset',
			'Reads one validated Published Surface-10 Dashboard Widget preset without mutating layout or authorization state.',
		);
		$this->registerAbility(
			$abilities,
			$bridge,
			new AbilityDescriptor(
				name: self::ABILITY_PRESET_PORTABILITY,
				ownerSurfaceId: DashboardWidgetPresetDefinition::OWNER_SURFACE_ID,
				capability: self::CAPABILITY,
				mutates: false,
				channels: $channels,
				inputSchema: [
					'type' => 'object',
					'required' => ['id'],
					'properties' => [
						'id' => ['type' => 'string', 'minLength' => 36, 'maxLength' => 36],
					],
					'additionalProperties' => false,
				],
				outputSchema: ['type' => ['object', 'null']],
			),
			new DashboardWidgetPresetPortabilityAbilityHandler( $preset_portability ),
			'Read Dashboard preset portability snapshot',
			'Exports only validated Published Dashboard preset fields with a non-authenticating SHA-256 fingerprint.',
		);
		$this->registerAbility(
			$abilities,
			$bridge,
			new AbilityDescriptor(
				name: self::ABILITY_PRESET_PORTABILITY_FRESHNESS,
				ownerSurfaceId: DashboardWidgetPresetDefinition::OWNER_SURFACE_ID,
				capability: self::CAPABILITY,
				mutates: false,
				channels: $channels,
				inputSchema: [
					'type' => 'object',
					'required' => ['id', 'sha256'],
					'properties' => [
						'id' => ['type' => 'string', 'minLength' => 36, 'maxLength' => 36],
						'sha256' => ['type' => 'string', 'minLength' => 64, 'maxLength' => 64],
					],
					'additionalProperties' => false,
				],
				outputSchema: ['type' => 'object'],
			),
			new DashboardWidgetPresetPortabilityFreshnessAbilityHandler( $preset_portability_freshness ),
			'Read Dashboard preset fingerprint freshness',
			'Compares a non-authenticating fingerprint to a canonical current Published preset without returning raw payload or changing layout.',
		);
		$this->registerAbility(
			$abilities,
			$bridge,
			new AbilityDescriptor(
				name: self::ABILITY_PRESET_IMPORT_PREFLIGHT,
				ownerSurfaceId: DashboardWidgetPresetDefinition::OWNER_SURFACE_ID,
				capability: self::CAPABILITY,
				mutates: false,
				channels: $channels,
				inputSchema: [
					'type' => 'object',
					'required' => ['snapshot'],
					'properties' => [
						'snapshot' => ['type' => 'object'],
					],
					'additionalProperties' => false,
				],
				outputSchema: ['type' => 'object'],
			),
			new DashboardWidgetPresetImportPreflightAbilityHandler( $preset_import_preflight ),
			'Read Dashboard preset import compatibility preflight',
			'Checks a bounded portability envelope and Published widget references without importing, writing, signing or applying a preset.',
		);
		$this->registerAbility(
			$abilities,
			$bridge,
			new AbilityDescriptor(
				name: self::ABILITY_PRESET_MAPPING_PREVIEW,
				ownerSurfaceId: DashboardWidgetPresetDefinition::OWNER_SURFACE_ID,
				capability: self::CAPABILITY,
				mutates: false,
				channels: $channels,
				inputSchema: [
					'type' => 'object',
					'required' => ['snapshot', 'target_id', 'widget_id_map'],
					'properties' => [
						'snapshot' => ['type' => 'object'],
						'target_id' => ['type' => 'string', 'minLength' => 36, 'maxLength' => 36],
						'widget_id_map' => ['type' => 'object'],
					],
					'additionalProperties' => false,
				],
				outputSchema: ['type' => 'object'],
			),
			new DashboardWidgetPresetPortabilityMappingPreviewAbilityHandler( $preset_mapping_preview ),
			'Preview Dashboard preset cross-site widget UUID mapping',
			'Maps validated export UUIDs into a destination candidate and rechecks its Published widgets without importing, trusting, or writing WordPress preferences.',
		);
		$this->registerAbility(
			$abilities,
			$bridge,
			new AbilityDescriptor(
				name: self::ABILITY_PRESET_CATALOG,
				ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
				capability: self::CAPABILITY,
				mutates: false,
				channels: $channels,
				inputSchema: ['type' => 'object', 'additionalProperties' => false],
				outputSchema: ['type' => 'array'],
			),
			new DashboardWidgetPresetReadAbilityHandler(
				$preset_read,
				DashboardWidgetPresetReadAbilityHandler::CATALOG,
			),
			'Read Dashboard Widget preset catalog',
			'Reads the deterministic Published Surface-10 Dashboard Widget preset catalog without mutation.',
		);
		$this->registerAbility(
			$abilities,
			$bridge,
			new AbilityDescriptor(
				name: self::ABILITY_EFFECTIVE_PRESET,
				ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
				capability: self::CAPABILITY,
				mutates: false,
				channels: $channels,
				inputSchema: ['type' => 'object', 'additionalProperties' => false],
				outputSchema: ['type' => 'object'],
			),
			new DashboardWidgetPresetReadAbilityHandler(
				$preset_read,
				DashboardWidgetPresetReadAbilityHandler::EFFECTIVE,
			),
			'Read effective Dashboard Widget preset',
			'Resolves current-user role-default then network-default preset evidence without mutating WordPress layout or preferences.',
		);

		$policy_abilities = [
			[self::ABILITY_MULTISITE_POLICY_GET, DashboardWidgetMultisitePolicyReadAbilityHandler::GET,
				['type' => 'object', 'required' => ['id'], 'properties' => [
					'id' => ['type' => 'string', 'minLength' => 36, 'maxLength' => 36],
				], 'additionalProperties' => false],
				['type' => ['object', 'null']], 'Read Dashboard Multisite policy'],
			[self::ABILITY_MULTISITE_POLICY_CATALOG, DashboardWidgetMultisitePolicyReadAbilityHandler::CATALOG,
				['type' => 'object', 'additionalProperties' => false],
				['type' => 'array'], 'Read Dashboard Multisite policy catalog'],
			[self::ABILITY_EFFECTIVE_MULTISITE_POLICY, DashboardWidgetMultisitePolicyReadAbilityHandler::EFFECTIVE,
				['type' => 'object', 'additionalProperties' => false],
				['type' => 'object'], 'Read effective Dashboard Multisite policy'],
		];
		foreach ( $policy_abilities as [$name, $action, $input_schema, $output_schema, $label] ) {
			$this->registerAbility(
				$abilities,
				$bridge,
				new AbilityDescriptor(
					name: $name,
					ownerSurfaceId: DashboardWidgetMultisitePolicyDefinition::OWNER_SURFACE_ID,
					capability: self::CAPABILITY,
					mutates: false,
					channels: $channels,
					inputSchema: $input_schema,
					outputSchema: $output_schema,
				),
				new DashboardWidgetMultisitePolicyReadAbilityHandler( $multisite_policy_read, $action ),
				$label,
				'Reads bounded Surface-10 Multisite policy evidence without mutating WordPress Dashboard preferences.',
			);
		}

		$this->registerAbility(
			$abilities,
			$bridge,
			new AbilityDescriptor(
				name: self::ABILITY_EFFECTIVE_MULTISITE_PRESET,
				ownerSurfaceId: DashboardWidgetSubsitePresetOverrideDefinition::OWNER_SURFACE_ID,
				capability: self::CAPABILITY,
				mutates: false,
				channels: $channels,
				inputSchema: ['type' => 'object', 'additionalProperties' => false],
				outputSchema: ['type' => 'object'],
			),
			new DashboardWidgetMultisiteEffectivePresetAbilityHandler( $effective_multisite_preset ),
			'Read effective Dashboard Multisite inherited preset',
			'Resolves read-only network inherited versus explicitly scoped subsite preset; does not mutate WordPress layout.',
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

		$preference_actions = [
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
		foreach ( $preference_actions as $action => [$ability_name, $ajax_type, $input_schema] ) {
			$descriptor = new AbilityDescriptor(
				name: $ability_name,
				ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
				capability: self::CAPABILITY,
				mutates: true,
				channels: [ExecutionChannel::Internal, ExecutionChannel::Ui],
				inputSchema: $input_schema,
				outputSchema: ['type' => 'object'],
			);
			$handler = new DashboardWidgetPersonalPreferenceAbilityHandler(
				$definitions,
				$registration_compiler,
				$personal_preferences,
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
				false,
			);
			$ajax_routes->register(new AjaxRoute(
				type: $ajax_type,
				handler: new AbilityAjaxHandler( $abilities, $descriptor->name, $contexts ),
				operation: NonceOperation::Update,
			));
		}
	}

	public function boot( ServiceRegistryInterface $services ): void
	{
		$adapter = $services->get( self::SERVICE_WORDPRESS_ADAPTER );
		if ( !$adapter instanceof DashboardWidgetWordPressAdapter ) {
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
		bool $showInRest = true,
	): void {
		$abilities->register( $descriptor, $handler );
		$bridge->expose(new WordPressAbilityExposure(
			internalName: $descriptor->name,
			label: $label,
			description: $description,
			showInRest: $showInRest,
		));
	}
}
