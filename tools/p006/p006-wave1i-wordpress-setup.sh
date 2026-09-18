#!/usr/bin/env bash
set -euo pipefail

: "${WPE_P006_EXPECTED_WP:?missing WPE_P006_EXPECTED_WP}"
: "${WPE_TEST_WORDPRESS_DIR:?missing WPE_TEST_WORDPRESS_DIR}"
: "${WPE_P006_FREE_ZIP_PATH:?missing WPE_P006_FREE_ZIP_PATH}"
: "${WPE_P006_PRO_ZIP_PATH:?missing WPE_P006_PRO_ZIP_PATH}"
: "${WPE_TEST_WP_DB:?missing WPE_TEST_WP_DB}"
: "${WPE_TEST_WP_DB_HOST:?missing WPE_TEST_WP_DB_HOST}"
: "${WPE_TEST_MYSQL_USER:?missing WPE_TEST_MYSQL_USER}"
: "${WPE_TEST_MYSQL_PASSWORD:?missing WPE_TEST_MYSQL_PASSWORD}"

wp_dir="${WPE_TEST_WORDPRESS_DIR}"
archive="/tmp/p006-wave1i-wordpress-${WPE_P006_EXPECTED_WP}.tar.gz"

rm -rf "${wp_dir}" "${archive}"
curl --fail --silent --show-error --location --retry 3 \
  "https://wordpress.org/wordpress-${WPE_P006_EXPECTED_WP}.tar.gz" \
  --output "${archive}"
tar -xzf "${archive}" -C /tmp

test -f "${wp_dir}/wp-load.php"
mkdir -p "${wp_dir}/wp-content/plugins" "${wp_dir}/wp-content/mu-plugins"

unzip -q "${WPE_P006_FREE_ZIP_PATH}" -d "${wp_dir}/wp-content/plugins"
unzip -q "${WPE_P006_PRO_ZIP_PATH}" -d "${wp_dir}/wp-content/plugins"

test -f "${wp_dir}/wp-content/plugins/wpessential/wpessential.php"
test -f "${wp_dir}/wp-content/plugins/wpessential-pro/wpessential-pro.php"

cat > "${wp_dir}/wp-config.php" <<'PHP'
<?php
define('DB_NAME', getenv('WPE_TEST_WP_DB') ?: 'wpessential_test');
define('DB_USER', getenv('WPE_TEST_MYSQL_USER') ?: 'root');
define('DB_PASSWORD', getenv('WPE_TEST_MYSQL_PASSWORD') ?: 'root');
define('DB_HOST', getenv('WPE_TEST_WP_DB_HOST') ?: '127.0.0.1:3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('AUTH_KEY', 'p006-wave1i-auth-key');
define('SECURE_AUTH_KEY', 'p006-wave1i-secure-auth-key');
define('LOGGED_IN_KEY', 'p006-wave1i-logged-in-key');
define('NONCE_KEY', 'p006-wave1i-nonce-key');
define('AUTH_SALT', 'p006-wave1i-auth-salt');
define('SECURE_AUTH_SALT', 'p006-wave1i-secure-auth-salt');
define('LOGGED_IN_SALT', 'p006-wave1i-logged-in-salt');
define('NONCE_SALT', 'p006-wave1i-nonce-salt');
define('WP_DEBUG', false);
define('DISABLE_WP_CRON', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);
define('WP_ENVIRONMENT_TYPE', 'development');
$table_prefix = 'wpep006i_';
require_once ABSPATH . 'wp-settings.php';
PHP

cat > "${wp_dir}/wp-content/mu-plugins/p006-wave1i-core-install-network-deny.php" <<'PHP'
<?php
add_filter('pre_http_request', static function ($preempt, $parsedArgs, $url) {
    if (function_exists('wp_installing') && wp_installing()) {
        return new WP_Error(
            'p006_wave1i_core_install_network_blocked',
            'Core installer HTTP is denied inside the disposable P-006 Wave 1I fixture.'
        );
    }
    return $preempt;
}, PHP_INT_MIN, 3);
PHP

cat > /tmp/p006-wave1i-core-install.php <<'PHP'
<?php
declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');

$_SERVER['HTTP_HOST'] = 'p006.test';
$_SERVER['SERVER_NAME'] = 'p006.test';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTPS'] = 'off';
$_SERVER['REQUEST_URI'] = '/wp-admin/install.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

if (!defined('WP_INSTALLING')) {
    define('WP_INSTALLING', true);
}

require $wpDir . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential P-006 Wave 1I',
        'p006_admin',
        'p006-admin@example.test',
        false,
        '',
        'p006-test-password-strong'
    );

    if (is_wp_error($installed)) {
        fwrite(STDERR, "Disposable WordPress installation failed\n");
        exit(1);
    }
}

if (function_exists('wp_installing')) {
    wp_installing(false);
}

if (!is_blog_installed()) {
    fwrite(STDERR, "Disposable WordPress installation did not reach installed state\n");
    exit(1);
}
PHP

php /tmp/p006-wave1i-core-install.php

rm -f \
  /tmp/p006-wave1i-core-install.php \
  "${wp_dir}/wp-content/mu-plugins/p006-wave1i-core-install-network-deny.php"

test -f "${wp_dir}/wp-config.php"
