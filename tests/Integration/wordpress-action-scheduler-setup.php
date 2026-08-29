<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDERR, "FAIL: WordPress fixture unavailable for Action Scheduler setup\n");
    exit(1);
}

require $wpDir . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

$newer = 'action-scheduler-4.1.0/action-scheduler.php';
$older = 'action-scheduler-3.9.3/action-scheduler.php';

foreach ([$newer, $older] as $plugin) {
    if (!is_file(WP_PLUGIN_DIR . '/' . $plugin)) {
        fwrite(STDERR, "FAIL: missing Action Scheduler fixture plugin {$plugin}\n");
        exit(1);
    }
}

if (!is_plugin_active($newer)) {
    $activation = activate_plugin($newer, '', false, true);
    if (is_wp_error($activation)) {
        fwrite(STDERR, 'FAIL: unable to activate Action Scheduler 4.1.0: ' . $activation->get_error_message() . "\n");
        exit(1);
    }
}

$active = get_option('active_plugins', []);
$active = is_array($active) ? $active : [];
$active = array_values(array_filter($active, static fn ($plugin): bool => is_string($plugin) && !in_array($plugin, [$older, $newer], true)));
array_unshift($active, $newer);
array_unshift($active, $older);

if (!update_option('active_plugins', $active) && get_option('active_plugins') !== $active) {
    fwrite(STDERR, "FAIL: unable to persist Action Scheduler coexistence plugin order\n");
    exit(1);
}

fwrite(STDOUT, "WPEssential Action Scheduler coexistence setup PASS\n");
