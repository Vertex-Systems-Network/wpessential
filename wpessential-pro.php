<?php
/**
 * Plugin Name: WPEssential Pro
 * Plugin URI: https://wpessential.org
 * Description: Premium module add-on for the WPEssential WordPress application platform.
 * Version: 0.1.0-dev
 * Requires at least: 6.9
 * Requires PHP: 8.2
 * Requires Plugins: wpessential
 * Author: VSN Team
 * Author URI: https://wpessential.org
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wpessential-pro
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WPE_PRO_VERSION')) {
    define('WPE_PRO_VERSION', '0.1.0-dev');
}

if (!defined('WPE_PRO_PACKAGE_ACTIVE')) {
    define('WPE_PRO_PACKAGE_ACTIVE', true);
}

$proNotice = static function (string $message): void {
    if (!function_exists('add_action')) {
        return;
    }

    add_action('admin_notices', static function () use ($message): void {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html($message)
        );
    });
};

if (PHP_VERSION_ID < 80200) {
    $proNotice('WPEssential Pro requires PHP 8.2 or newer.');
    return;
}

/**
 * The Pro package owns only WPEssential\Modules\* implementation source.
 * Platform/Kernel/Contracts continue to resolve from the active Free plugin.
 */
spl_autoload_register(
    static function (string $class): void {
        $prefix = 'WPEssential\\Modules\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        if ($relative === '' || str_contains($relative, '..')) {
            return;
        }

        $file = __DIR__ . '/frameworks/Modules/' . str_replace('\\', '/', $relative) . '.php';
        if (is_readable($file)) {
            require_once $file;
        }
    },
    true,
    true,
);

if (!function_exists('add_action')) {
    return;
}

add_action('plugins_loaded', static function () use ($proNotice): void {
    if (!class_exists(\WPEssential\Bootstrap\Plugin::class)) {
        $proNotice('WPEssential Pro requires the compatible WPEssential Free platform plugin to be active.');
        return;
    }

    $configuredState = defined('WPE_PRO_LOCAL_ENTITLEMENT_STATE')
        ? (string) WPE_PRO_LOCAL_ENTITLEMENT_STATE
        : \WPEssential\Platform\Entitlements\ProductEntitlementState::VerificationUnavailable->value;
    $state = \WPEssential\Platform\Entitlements\ProductEntitlementState::tryFrom($configuredState)
        ?? \WPEssential\Platform\Entitlements\ProductEntitlementState::VerificationUnavailable;
    $reason = $state->value === $configuredState ? null : 'invalid_local_entitlement_state';
    $entitlements = new \WPEssential\Platform\Entitlements\LocalProductEntitlementProvider(
        new \WPEssential\Platform\Entitlements\ProductEntitlementSnapshot($state, $reason),
    );
    $operations = new \WPEssential\Platform\Entitlements\PremiumOperationPolicy($entitlements);

    \WPEssential\Bootstrap\Plugin::setModuleActivationPolicy(
        new \WPEssential\Platform\Entitlements\EntitlementAwareModuleActivationPolicy($entitlements),
    );

    if (!defined('WPE_PRO_ENTITLEMENT_STATE')) {
        define('WPE_PRO_ENTITLEMENT_STATE', $entitlements->snapshot()->state->value);
    }
    if (!defined('WPE_PRO_PREMIUM_READS_ALLOWED')) {
        define('WPE_PRO_PREMIUM_READS_ALLOWED', $operations->allowsRead());
    }
    if (!defined('WPE_PRO_PREMIUM_MUTATIONS_ALLOWED')) {
        define('WPE_PRO_PREMIUM_MUTATIONS_ALLOWED', $operations->allowsMutation());
    }

    $moduleClasses = [
        \WPEssential\Modules\Roles\RolesModule::class,
        \WPEssential\Modules\AdminMenu\AdminMenuModule::class,
        \WPEssential\Modules\Settings\SettingsModule::class,
        \WPEssential\Modules\Dashboard\DashboardModule::class,
        \WPEssential\Modules\Profiles\ProfilesModule::class,
        \WPEssential\Modules\Membership\MembershipModule::class,
        \WPEssential\Modules\BuilderWidgets\BuilderWidgetsModule::class,
        \WPEssential\Modules\FormsWorkflows\FormsWorkflowsModule::class,
        \WPEssential\Modules\Cron\CronModule::class,
        \WPEssential\Modules\Notifications\NotificationsModule::class,
        \WPEssential\Modules\Emails\EmailsModule::class,
        \WPEssential\Modules\Chat\ChatModule::class,
    ];

    foreach ($moduleClasses as $moduleClass) {
        if (!class_exists($moduleClass)) {
            $proNotice(sprintf('WPEssential Pro package is incomplete: missing %s.', $moduleClass));
            return;
        }
    }

    foreach ($moduleClasses as $moduleClass) {
        \WPEssential\Bootstrap\Plugin::registerModule(new $moduleClass());
    }
}, -200);
