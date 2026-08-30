<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$GLOBALS['wpe_smoke_actions'] = [];

function add_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
{
    $GLOBALS['wpe_smoke_actions'][] = [
        'hook' => $hook,
        'callback' => $callback,
        'priority' => $priority,
        'accepted_args' => $acceptedArgs,
    ];

    return true;
}

function plugin_dir_url(string $file): string
{
    return 'https://example.test/wp-content/plugins/wpessential/';
}

function current_user_can(string $capability): bool
{
    return $capability === PlatformAdminController::CAPABILITY;
}

function esc_html__(string $text, string $domain = 'default'): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function esc_html(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function wp_json_encode(mixed $value, int $flags = 0, int $depth = 512): string|false
{
    return json_encode($value, $flags, $depth);
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__, 2) . '/frameworks/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use WPEssential\Bootstrap\Plugin;
use WPEssential\Platform\Admin\PlatformAdminController;

function expect_admin_bootstrap(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$kernel = Plugin::boot();
expect_admin_bootstrap($kernel !== null, 'plugin boot should return a kernel');
expect_admin_bootstrap($kernel->isBooted(), 'kernel should be booted');

$services = $kernel->services();
expect_admin_bootstrap($services->has('platform.admin.diagnostics'), 'runtime diagnostics service must be registered');
expect_admin_bootstrap($services->has('platform.admin.assets'), 'admin asset manifest service must be registered');
expect_admin_bootstrap($services->has('platform.admin.controller'), 'Platform admin controller service must be registered');
expect_admin_bootstrap(
    $services->get('platform.admin.controller') instanceof PlatformAdminController,
    'Platform admin controller service must use the canonical controller',
);

$hooks = array_map(
    static fn (array $registration): string => (string) $registration['hook'],
    $GLOBALS['wpe_smoke_actions'],
);
expect_admin_bootstrap(in_array('admin_menu', $hooks, true), 'Platform admin controller must register admin_menu');
expect_admin_bootstrap(in_array('admin_enqueue_scripts', $hooks, true), 'Platform admin controller must register admin_enqueue_scripts');

$controller = $services->get('platform.admin.controller');
ob_start();
$controller->render();
$rendered = (string) ob_get_clean();
expect_admin_bootstrap(str_contains($rendered, 'Runtime Observatory'), 'Platform admin shell must render the Runtime Observatory heading without requiring built assets');
expect_admin_bootstrap(str_contains($rendered, 'Read-only bounded diagnostics'), 'Platform admin shell must declare the diagnostics surface read-only');
expect_admin_bootstrap(str_contains($rendered, 'Kernel booted'), 'Platform admin shell must render kernel diagnostics');
expect_admin_bootstrap(str_contains($rendered, 'wpessential-admin-bootstrap'), 'Platform admin shell must preserve the JSON bootstrap payload for progressive enhancement');
expect_admin_bootstrap(!str_contains($rendered, 'Loading the Platform diagnostics shell'), 'Platform admin shell must not stall on an asset-dependent loading placeholder');

fwrite(STDOUT, "WPEssential Platform admin bootstrap smoke PASS\n");
