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

$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_readable($autoload)) {
    return;
}

require_once $autoload;

add_action('plugins_loaded', static function (): void {
    \WPEssential\Bootstrap\Plugin::boot();
}, -100);
