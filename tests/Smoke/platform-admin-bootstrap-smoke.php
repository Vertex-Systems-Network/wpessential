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

fwrite(STDOUT, "WPEssential Platform admin bootstrap smoke PASS\n");
