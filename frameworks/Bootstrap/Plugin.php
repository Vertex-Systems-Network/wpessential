<?php

declare(strict_types=1);

namespace WPEssential\Bootstrap;


if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Kernel\Kernel;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Admin\PlatformAdminController;
use WPEssential\Platform\Admin\RuntimeDiagnosticsSnapshot;
use WPEssential\Platform\Observability\BoundedInMemoryTraceRecorder;
use WPEssential\Platform\Observability\NullTraceRecorder;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Ajax\NativeWordPressAjaxEnvironment;
use WPEssential\Platform\WordPress\Ajax\WordPressAjaxGateway;
use WPEssential\Platform\WordPress\Registrations\InMemoryCompiledRegistrationStore;
use WPEssential\Platform\WordPress\Registrations\RegistrationCompiler;
use WPEssential\Platform\WordPress\Registrations\RegistrationRuntimeLoader;
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

        $registrationStore = new InMemoryCompiledRegistrationStore();
        $registrationCompiler = new RegistrationCompiler($registrationStore);
        $registrationRuntime = new RegistrationRuntimeLoader($registrationStore);
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
        $services->set('platform.registrations.store', $registrationStore);
        $services->set('platform.registrations.compiler', $registrationCompiler);
        $services->set('platform.registrations.runtime', $registrationRuntime);
        $services->set('platform.traces', $traces);
        $services->set('platform.admin.diagnostics', $runtimeDiagnostics);
        $services->set('platform.admin.assets', $adminAssets);
        $services->set('platform.admin.controller', $adminController);

        if (function_exists('add_action')) {
            $ajaxGateway->register();
            $adminController->register();
        }

        self::$kernel->boot();
        return self::$kernel;
    }

    public static function kernel(): ?Kernel
    {
        return self::$kernel;
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
