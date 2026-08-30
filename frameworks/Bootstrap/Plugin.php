<?php

declare(strict_types=1);

namespace WPEssential\Bootstrap;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeModule;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\Admin\RuntimeDiagnosticsSnapshot;
use WPEssential\Platform\Database\Migrations\MigrationRegistry;
use WPEssential\Platform\Database\Migrations\MigrationRunner;
use WPEssential\Platform\Database\Migrations\WpdbMigrationStateStore;
use WPEssential\Platform\Database\NativeWpdbAdapter;
use WPEssential\Platform\Definitions\DefinitionScope;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\Definitions\Migrations\CreateDefinitionTablesMigration;
use WPEssential\Platform\Definitions\PersistentDefinitionRepository;
use WPEssential\Platform\Definitions\WpdbDefinitionTableGateway;
use WPEssential\Platform\Observability\BoundedInMemoryTraceRecorder;
use WPEssential\Platform\Observability\NullTraceRecorder;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Ajax\NativeWordPressAjaxEnvironment;
use WPEssential\Platform\WordPress\Ajax\WordPressAjaxGateway;
use WPEssential\Platform\WordPress\Registrations\AtomicCompiledRegistrationStore;
use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationScope;
use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationStoreInterface;
use WPEssential\Platform\WordPress\Registrations\InMemoryCompiledRegistrationStore;
use WPEssential\Platform\WordPress\Registrations\Migrations\CreateCompiledRegistrationTablesMigration;
use WPEssential\Platform\WordPress\Registrations\PostTypeRuntimeRegistrar;
use WPEssential\Platform\WordPress\Registrations\RegistrationCompilationStatus;
use WPEssential\Platform\WordPress\Registrations\RegistrationCompiler;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinitionProviderRegistry;
use WPEssential\Platform\WordPress\Registrations\RegistrationRuntimeLoader;
use WPEssential\Platform\WordPress\Registrations\WpdbCompiledRegistrationPersistenceGateway;
use WPEssential\Platform\WordPress\Security\NativeWordPressNonceEnvironment;
use WPEssential\Platform\WordPress\Security\NonceManager;

final class Plugin
{
    public const VERSION = '0.1.0-dev';
    public const MINIMUM_WORDPRESS = '6.9';
    public const MINIMUM_PHP = '8.2';

    private static ?Kernel $kernel = null;

    public static function boot(): ?Kernel
    {
        if (self::$kernel instanceof Kernel) {
            return self::$kernel;
        }
        if (!self::environmentSupported()) {
            return null;
        }

        self::$kernel = new Kernel();
        $services = self::$kernel->services();

        $nonceAction = defined('WPE_NONCE_ACTION') ? (string) WPE_NONCE_ACTION : 'wpessential_request';
        $ajaxAction = defined('WPE_AJAX_ACTION') ? (string) WPE_AJAX_ACTION : 'wpessential_dispatch';
        $debug = defined('WPE_DEBUG') && WPE_DEBUG === true;

        $nonceManager = new NonceManager(new NativeWordPressNonceEnvironment(), $nonceAction);
        $ajaxEnvironment = new NativeWordPressAjaxEnvironment();
        $ajaxRoutes = new AjaxRouteRegistry();
        $ajaxDispatcher = new AjaxDispatcher($ajaxRoutes, $nonceManager, [$ajaxEnvironment, 'currentUserCan']);
        $ajaxGateway = new WordPressAjaxGateway($ajaxAction, $ajaxDispatcher, $ajaxEnvironment);

        [$definitionRepository, $registrationStore, $database] = self::createPersistenceServices();
        $registrationProviders = new RegistrationDefinitionProviderRegistry();
        $registrationCompiler = new RegistrationCompiler($registrationStore);
        $registrationRuntime = new RegistrationRuntimeLoader($registrationStore);
        $registrationStatus = new RegistrationCompilationStatus();
        $postTypeRegistrar = new PostTypeRuntimeRegistrar($registrationRuntime);
        $traces = $debug ? new BoundedInMemoryTraceRecorder() : new NullTraceRecorder();

        $pluginRoot = dirname(__DIR__, 2);
        $pluginFile = $pluginRoot . '/wpessential.php';
        $pluginUrl = function_exists('plugin_dir_url') ? (string) plugin_dir_url($pluginFile) : '';
        $runtimeDiagnostics = new RuntimeDiagnosticsSnapshot(self::$kernel, $traces, $debug);
        $adminAssets = new AdminAssetManifest($pluginRoot, $pluginUrl);
        $adminController = new PlatformAdminController($runtimeDiagnostics, $adminAssets);

        $services->set('platform.nonce', $nonceManager);
        $services->set('platform.ajax.routes', $ajaxRoutes);
        $services->set('platform.ajax.dispatcher', $ajaxDispatcher);
        $services->set('platform.ajax.gateway', $ajaxGateway);
        if ($database instanceof NativeWpdbAdapter) {
            $services->set('platform.database', $database);
        }
        $services->set('platform.definitions', $definitionRepository);
        $services->set('platform.registrations.store', $registrationStore);
        $services->set('platform.registrations.providers', $registrationProviders);
        $services->set('platform.registrations.compiler', $registrationCompiler);
        $services->set('platform.registrations.runtime', $registrationRuntime);
        $services->set('platform.registrations.compilation-status', $registrationStatus);
        $services->set('platform.registrations.post-types', $postTypeRegistrar);
        $services->set('platform.traces', $traces);
        $services->set('platform.admin.diagnostics', $runtimeDiagnostics);
        $services->set('platform.admin.assets', $adminAssets);
        $services->set('platform.admin.controller', $adminController);

        self::$kernel->registerModule(new CustomPostTypeModule());

        if (function_exists('add_action')) {
            $ajaxGateway->register();
            $adminController->register();
        }

        self::$kernel->boot();

        try {
            $manifest = $registrationCompiler->compileAndPublishIfChanged($registrationProviders->definitions());
            $registrationStatus->markSuccess($manifest);
        } catch (Throwable $exception) {
            $registrationStatus->markFailure($exception);
        }
        $postTypeRegistrar->register();

        return self::$kernel;
    }

    public static function kernel(): ?Kernel
    {
        return self::$kernel;
    }

    /** @return array{DefinitionRepositoryInterface, CompiledRegistrationStoreInterface, NativeWpdbAdapter|null} */
    private static function createPersistenceServices(): array
    {
        $wpdb = $GLOBALS['wpdb'] ?? null;
        if (!is_object($wpdb) || !self::supportsMysqlPersistence($wpdb)) {
            return [new InMemoryDefinitionRepository(), new InMemoryCompiledRegistrationStore(), null];
        }

        $database = new NativeWpdbAdapter($wpdb);
        $migrations = new MigrationRegistry();
        $migrations->register(new CreateCompiledRegistrationTablesMigration($database));
        $migrations->register(new CreateDefinitionTablesMigration($database));
        (new MigrationRunner($migrations, new WpdbMigrationStateStore($database)))->runPending();

        $networkId = function_exists('get_current_network_id') ? max(1, (int) get_current_network_id()) : 1;
        $siteId = function_exists('get_current_blog_id') ? max(1, (int) get_current_blog_id()) : 1;
        $definitionScope = DefinitionScope::site($networkId, $siteId);
        $registrationScope = CompiledRegistrationScope::site($networkId, $siteId);

        $definitions = new PersistentDefinitionRepository(new WpdbDefinitionTableGateway($database, $definitionScope));
        $registrations = new AtomicCompiledRegistrationStore(
            new WpdbCompiledRegistrationPersistenceGateway($database),
            $registrationScope,
        );
        return [$definitions, $registrations, $database];
    }

    private static function supportsMysqlPersistence(object $wpdb): bool
    {
        $class = strtolower($wpdb::class);
        if (str_contains($class, 'sqlite')) {
            return false;
        }
        foreach (['SQLITE_DB_DROPIN_VERSION', 'WP_SQLITE_AST_DRIVER_VERSION'] as $constant) {
            if (defined($constant)) {
                return false;
            }
        }
        foreach (['prepare', 'get_row', 'get_results', 'get_var', 'query', 'insert'] as $method) {
            if (!method_exists($wpdb, $method)) {
                return false;
            }
        }
        return true;
    }

    private static function environmentSupported(): bool
    {
        if (PHP_VERSION_ID < 80200) {
            return false;
        }
        if (function_exists('get_bloginfo')) {
            $version = (string) get_bloginfo('version');
            if ($version !== '' && version_compare($version, self::MINIMUM_WORDPRESS, '<')) {
                return false;
            }
        }
        return true;
    }
}
