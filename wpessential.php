<?php
/**
 * Plugin Name: WPEssential
 * Description: Modular WordPress application platform.
 * Version: 0.1.0-dev
 * Requires at least: 6.9
 * Requires PHP: 8.2
 * Author: WPEssential
 * License: GPL-2.0-or-later
 * Text Domain: wpessential
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

$compatibilityNotice = static function (string $message): void {
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
    $compatibilityNotice('WPEssential requires PHP 8.2 or newer.');
    return;
}

if (function_exists('get_bloginfo')) {
    $wordpressVersion = (string) get_bloginfo('version');
    if ($wordpressVersion !== '' && version_compare($wordpressVersion, '6.9', '<')) {
        $compatibilityNotice('WPEssential requires WordPress 6.9 or newer.');
        return;
    }
}

$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_readable($autoload)) {
    $compatibilityNotice('WPEssential could not load its Composer autoloader. Reinstall the plugin package.');
    return;
}

require_once $autoload;

add_action('plugins_loaded', static function (): void {
    \WPEssential\Bootstrap\Plugin::boot();
}, -100);
