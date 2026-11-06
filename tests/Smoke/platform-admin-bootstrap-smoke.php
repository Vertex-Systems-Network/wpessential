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
    return $capability === 'manage_options';
}

function esc_html__(string $text, string $domain = 'default'): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function esc_html(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function __(string $text, string $domain = 'default'): string
{
    return $text;
}

function esc_attr(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function esc_attr__(string $text, string $domain = 'default'): string
{
    return esc_attr($text);
}

function esc_url(string $url): string
{
    return htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
expect_admin_bootstrap(str_contains($rendered, 'System status'), 'Platform admin shell must render the System status heading without requiring built assets');
expect_admin_bootstrap(str_contains($rendered, 'Read-only diagnostics'), 'Platform admin shell must declare the diagnostics surface read-only');
expect_admin_bootstrap(str_contains($rendered, 'Kernel booted'), 'Platform admin shell must render kernel diagnostics');
expect_admin_bootstrap(str_contains($rendered, 'wpessential-admin-bootstrap'), 'Platform admin shell must preserve the JSON bootstrap payload for progressive enhancement');
expect_admin_bootstrap(!str_contains($rendered, 'Loading the Platform diagnostics shell'), 'Platform admin shell must not stall on an asset-dependent loading placeholder');

expect_admin_bootstrap(substr_count($rendered, '<details class="wpe-ui-group"') === 3, 'System status must render its Runtime, Free / Pro and Modules groups');
expect_admin_bootstrap(str_contains($rendered, 'href="#wpe-status-runtime"'), 'System status section nav must link to its groups');

// Shared option group helpers escape what they print and count their rows.
$group = \WPEssential\Platform\Admin\Ui\OptionGroup::render(
    'g<1>',
    'Title <b>',
    'System',
    'key.<x>',
    [
        \WPEssential\Platform\Admin\Ui\OptionGroup::row('Label <i>', 'Help <u>', \WPEssential\Platform\Admin\Ui\OptionGroup::readonly('v<1>')),
        \WPEssential\Platform\Admin\Ui\OptionGroup::row('Second', '', ''),
    ]
);
expect_admin_bootstrap(!str_contains($group, '<b>') && !str_contains($group, '<i>') && !str_contains($group, '<u>') && !str_contains($group, 'v<1>'), 'OptionGroup must escape titles, labels, help and read-only values');
expect_admin_bootstrap(substr_count($group, 'class="wpe-ui-option"') === 2 && str_contains($group, 'wpe-ui-chip--system'), 'OptionGroup must render one row per entry and the tier chip');
$nav = \WPEssential\Platform\Admin\Ui\SectionNav::layout('Nav', ['Head <h>' => [['a"b', 'Link', 3]]], '<p>body</p>');
expect_admin_bootstrap(str_contains($nav, '<p>body</p>') && !str_contains($nav, 'Head <h>') && !str_contains($nav, 'href="#a"b"'), 'SectionNav must escape headings and anchors and keep the prepared body');

fwrite(STDOUT, "WPEssential Platform admin bootstrap smoke PASS\n");
