<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Status reference prepare SKIP (WordPress fixture unavailable)\n");
    exit(0);
}

if (!defined('ABSPATH')) {
    define('ABSPATH', $wpDir . '/');
}

if (!is_file($wpDir . '/wp-config.php')) {
    $config = <<<'PHP'
<?php
define('DB_NAME', getenv('WPE_TEST_WP_DB') ?: 'wpessential_test');
define('DB_USER', getenv('WPE_TEST_MYSQL_USER') ?: 'root');
define('DB_PASSWORD', getenv('WPE_TEST_MYSQL_PASSWORD') ?: '');
define('DB_HOST', getenv('WPE_TEST_WP_DB_HOST') ?: '127.0.0.1:3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
foreach (['AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT'] as $wpeSaltName) {
    if (!defined($wpeSaltName)) {
        define($wpeSaltName, hash('sha256', __FILE__ . ':' . $wpeSaltName));
    }
}
$table_prefix = 'wpestatus_';
define('WP_DEBUG', false);
require_once ABSPATH . 'wp-settings.php';
PHP;
    if (file_put_contents($wpDir . '/wp-config.php', $config . "\n") === false) {
        fwrite(STDERR, "FAIL: unable to create WordPress Status reference configuration\n");
        exit(1);
    }
}

require $wpDir . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

function statusPrepareExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Status Reference',
        'wpessential_status_admin',
        'status-admin@example.test',
        false,
        '',
        wp_generate_password(32, true, true),
    );
    statusPrepareExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_status_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user('wpessential_status_admin', wp_generate_password(32, true, true), 'status-admin@example.test');
    statusPrepareExpect(is_int($userId) && $userId > 0, 'reference administrator must be created');
    $admin = get_user_by('id', $userId);
}
statusPrepareExpect($admin instanceof WP_User, 'reference administrator must exist');
$admin->set_role('administrator');

$contributor = get_user_by('login', 'wpessential_status_contributor');
if (!$contributor instanceof WP_User) {
    $userId = wp_create_user('wpessential_status_contributor', wp_generate_password(32, true, true), 'status-contributor@example.test');
    statusPrepareExpect(is_int($userId) && $userId > 0, 'reference contributor must be created');
    $contributor = get_user_by('id', $userId);
}
statusPrepareExpect($contributor instanceof WP_User, 'reference contributor must exist');
$contributor->set_role('contributor');

fwrite(STDOUT, "WPEssential Status reference prepare PASS\n");
