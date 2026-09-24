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
if (!defined('WPE_PRO_MIN_FREE_VERSION')) {
    define('WPE_PRO_MIN_FREE_VERSION', '0.1.0-dev');
}
if (!defined('WPE_PRO_MAX_FREE_VERSION')) {
    define('WPE_PRO_MAX_FREE_VERSION', '0.1.0-dev');
}
if (!defined('WPE_PRO_MIN_PLATFORM_API_VERSION')) {
    define('WPE_PRO_MIN_PLATFORM_API_VERSION', '0.1.0');
}
if (!defined('WPE_PRO_MAX_PLATFORM_API_VERSION')) {
    define('WPE_PRO_MAX_PLATFORM_API_VERSION', '0.1.0');
}
if (!defined('WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION')) {
    define('WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION', 1);
}
if (!defined('WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION')) {
    define('WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION', 1);
}
if (!defined('WPE_PRO_SCHEMA_GENERATION')) {
    define('WPE_PRO_SCHEMA_GENERATION', 1);
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
 * Compatibility code is the only Pro module namespace allowed to autoload before
 * the local Free/Pro preflight passes. All other Pro implementation remains inert.
 */
spl_autoload_register(
    static function (string $class): void {
        $prefix = 'WPEssential\\Modules\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $compatibilityPrefix = 'WPEssential\\Modules\\Compatibility\\';
        $compatibility = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
        if (
            !str_starts_with($class, $compatibilityPrefix)
            && (!is_array($compatibility) || ($compatibility['state'] ?? '') !== 'compatible')
        ) {
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

$moduleClasses = [
    \WPEssential\Modules\Roles\RolesModule::class,
    \WPEssential\Modules\AdminMenu\AdminMenuModule::class,
    \WPEssential\Modules\Settings\SettingsModule::class,
    \WPEssential\Modules\Dashboard\DashboardModule::class,
    \WPEssential\Modules\Query\QueryModule::class,
    \WPEssential\Modules\DashboardWidgets\DashboardWidgetsModule::class,
    \WPEssential\Modules\Profiles\ProfilesModule::class,
    \WPEssential\Modules\Membership\MembershipModule::class,
    \WPEssential\Modules\BuilderWidgets\BuilderWidgetsModule::class,
    \WPEssential\Modules\FormsWorkflows\FormsWorkflowsModule::class,
    \WPEssential\Modules\Cron\CronModule::class,
    \WPEssential\Modules\Notifications\NotificationsModule::class,
    \WPEssential\Modules\Emails\EmailsModule::class,
    \WPEssential\Modules\Chat\ChatModule::class,
];

$moduleFileForClass = static function (string $class): ?string {
    $prefix = 'WPEssential\\Modules\\';
    if (!str_starts_with($class, $prefix)) {
        return null;
    }

    $relative = substr($class, strlen($prefix));
    if ($relative === '' || str_contains($relative, '..')) {
        return null;
    }

    return __DIR__ . '/frameworks/Modules/' . str_replace('\\', '/', $relative) . '.php';
};

$publishCompatibility = static function (array $result): void {
    // Canonical request-local authority. Compatibility mirror constants below are observability only.
    $GLOBALS['wpe_pro_compatibility_result'] = $result;

    $constants = [
        'WPE_PRO_COMPATIBILITY_STATE' => (string) ($result['state'] ?? 'pro_package_incomplete'),
        'WPE_PRO_COMPATIBILITY_DIMENSION' => (string) ($result['dimension'] ?? 'package'),
        'WPE_PRO_COMPATIBILITY_REASON' => (string) ($result['reason'] ?? 'compatibility_result_unavailable'),
        'WPE_PRO_COMPATIBILITY_REMEDIATION' => (string) ($result['remediation'] ?? 'reinstall_pro'),
        'WPE_PRO_COMPATIBILITY_FREE_VERSION' => (string) ($result['free_version'] ?? 'unknown'),
        'WPE_PRO_COMPATIBILITY_PLATFORM_API' => (string) ($result['platform_api'] ?? 'unknown'),
        'WPE_PRO_COMPATIBILITY_PLATFORM_SCHEMA' => $result['platform_schema'] ?? 'unknown',
        'WPE_PRO_COMPATIBILITY_PRO_SCHEMA' => $result['pro_schema'] ?? 'unknown',
        'WPE_PRO_COMPATIBILITY_BOOT_ALLOWED' => ($result['premium_boot_allowed'] ?? false) === true,
        'WPE_PRO_COMPATIBILITY_MIGRATIONS_ALLOWED' => ($result['premium_migrations_allowed'] ?? false) === true,
    ];

    foreach ($constants as $name => $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }
};

add_action(
    'plugins_loaded',
    static function () use ($proNotice, $moduleClasses, $moduleFileForClass, $publishCompatibility): void {
        $preflightClass = \WPEssential\Modules\Compatibility\LocalCompatibilityPreflight::class;
        if (!class_exists($preflightClass)) {
            $publishCompatibility([
                'state' => 'pro_package_incomplete',
                'dimension' => 'package',
                'reason' => 'compatibility_preflight_missing',
                'remediation' => 'reinstall_pro',
                'free_version' => defined('WPE_VERSION') ? (string) WPE_VERSION : 'unknown',
                'platform_api' => defined('WPE_PLATFORM_API_VERSION') ? (string) WPE_PLATFORM_API_VERSION : 'unknown',
                'platform_schema' => defined('WPE_PLATFORM_SCHEMA_GENERATION')
                    ? WPE_PLATFORM_SCHEMA_GENERATION
                    : 'unknown',
                'pro_schema' => WPE_PRO_SCHEMA_GENERATION,
                'premium_boot_allowed' => false,
                'premium_migrations_allowed' => false,
            ]);
            $proNotice('WPEssential Pro package is incomplete: compatibility preflight is missing.');
            return;
        }

        $packageComplete = true;
        foreach ($moduleClasses as $moduleClass) {
            $moduleFile = $moduleFileForClass($moduleClass);
            if ($moduleFile === null || !is_readable($moduleFile)) {
                $packageComplete = false;
                break;
            }
        }

        $compatibility = $preflightClass::evaluateRuntime($packageComplete);
        if (($compatibility['state'] ?? '') !== 'compatible') {
            $publishCompatibility($compatibility);
            $proNotice(sprintf(
                'WPEssential Pro is inactive because local Free/Pro compatibility failed (%s). Recovery: %s.',
                (string) ($compatibility['reason'] ?? 'unknown'),
                (string) ($compatibility['remediation'] ?? 'repair_packages'),
            ));
            return;
        }

        $requiredFreeRuntimeClasses = [
            \WPEssential\Bootstrap\Plugin::class,
            \WPEssential\Platform\Entitlements\ProductEntitlementState::class,
            \WPEssential\Platform\Entitlements\LocalProductEntitlementProvider::class,
            \WPEssential\Platform\Entitlements\ProductEntitlementSnapshot::class,
            \WPEssential\Platform\Entitlements\PremiumOperationPolicy::class,
            \WPEssential\Platform\Entitlements\EntitlementAwareModuleActivationPolicy::class,
        ];
        foreach ($requiredFreeRuntimeClasses as $requiredFreeRuntimeClass) {
            if (!class_exists($requiredFreeRuntimeClass)) {
                $compatibility['state'] = 'free_bootstrap_incomplete';
                $compatibility['dimension'] = 'package';
                $compatibility['reason'] = 'free_platform_api_profile_incomplete';
                $compatibility['remediation'] = 'repair_free';
                $compatibility['premium_boot_allowed'] = false;
                $compatibility['premium_migrations_allowed'] = false;
                $publishCompatibility($compatibility);
                $proNotice('WPEssential Pro found compatible metadata but the Free Platform API is incomplete.');
                return;
            }
        }

        // Publish compatible only after the declared Free API profile actually resolves.
        $publishCompatibility($compatibility);

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

        foreach ($moduleClasses as $moduleClass) {
            if (!class_exists($moduleClass)) {
                $proNotice(sprintf('WPEssential Pro package is incomplete: missing %s.', $moduleClass));
                return;
            }
        }

        foreach ($moduleClasses as $moduleClass) {
            \WPEssential\Bootstrap\Plugin::registerModule(new $moduleClass());
        }
    },
    -200,
);
