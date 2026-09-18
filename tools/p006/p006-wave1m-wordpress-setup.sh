#!/usr/bin/env bash
set -euo pipefail

: "${WPE_P006_EXPECTED_WP:?missing WPE_P006_EXPECTED_WP}"
: "${WPE_P006_BASE_FREE_NODE:?missing WPE_P006_BASE_FREE_NODE}"
: "${WPE_P006_BASE_PRO_NODE:?missing WPE_P006_BASE_PRO_NODE}"
: "${WPE_TEST_WORDPRESS_DIR:?missing WPE_TEST_WORDPRESS_DIR}"
: "${WPE_P006_F0_ZIP_PATH:?missing WPE_P006_F0_ZIP_PATH}"
: "${WPE_P006_F1_ZIP_PATH:?missing WPE_P006_F1_ZIP_PATH}"
: "${WPE_P006_P0_ZIP_PATH:?missing WPE_P006_P0_ZIP_PATH}"
: "${WPE_TEST_WP_DB:?missing WPE_TEST_WP_DB}"
: "${WPE_TEST_WP_DB_HOST:?missing WPE_TEST_WP_DB_HOST}"
: "${WPE_TEST_MYSQL_USER:?missing WPE_TEST_MYSQL_USER}"
: "${WPE_TEST_MYSQL_PASSWORD:?missing WPE_TEST_MYSQL_PASSWORD}"
: "${WPE_P006_NETWORK_LOG:?missing WPE_P006_NETWORK_LOG}"

wp_dir="${WPE_TEST_WORDPRESS_DIR}"
archive="/tmp/p006-wave1m-wordpress-${WPE_P006_EXPECTED_WP}.tar.gz"
plugins_dir="${wp_dir}/wp-content/plugins"
mu_dir="${wp_dir}/wp-content/mu-plugins"

case "${WPE_P006_BASE_FREE_NODE}" in
  F0) base_free_zip="${WPE_P006_F0_ZIP_PATH}" ;;
  F1) base_free_zip="${WPE_P006_F1_ZIP_PATH}" ;;
  *) echo "Unsupported baseline Free node: ${WPE_P006_BASE_FREE_NODE}" >&2; exit 1 ;;
esac

case "${WPE_P006_BASE_PRO_NODE}" in
  P0) base_pro_zip="${WPE_P006_P0_ZIP_PATH}" ;;
  *) echo "Unsupported baseline Pro node: ${WPE_P006_BASE_PRO_NODE}" >&2; exit 1 ;;
esac

rm -rf "${wp_dir}" "${archive}"
curl --fail --silent --show-error --location --retry 3 \
  "https://wordpress.org/wordpress-${WPE_P006_EXPECTED_WP}.tar.gz" \
  --output "${archive}"

tar -xzf "${archive}" -C /tmp

test -f "${wp_dir}/wp-load.php"
mkdir -p "${plugins_dir}" "${mu_dir}"

rm -rf "${plugins_dir}/wpessential" "${plugins_dir}/wpessential-pro"
unzip -q "${base_free_zip}" -d "${plugins_dir}"
unzip -q "${base_pro_zip}" -d "${plugins_dir}"

test -d "${plugins_dir}/wpessential"
test -d "${plugins_dir}/wpessential-pro"
test ! -L "${plugins_dir}/wpessential"
test ! -L "${plugins_dir}/wpessential-pro"
test -f "${plugins_dir}/wpessential/wpessential.php"
test -f "${plugins_dir}/wpessential-pro/wpessential-pro.php"

cat > "${wp_dir}/wp-config.php" <<'PHP'
<?php
define('DB_NAME', getenv('WPE_TEST_WP_DB') ?: 'wpessential_test');
define('DB_USER', getenv('WPE_TEST_MYSQL_USER') ?: 'root');
define('DB_PASSWORD', getenv('WPE_TEST_MYSQL_PASSWORD') ?: 'root');
define('DB_HOST', getenv('WPE_TEST_WP_DB_HOST') ?: '127.0.0.1:3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('AUTH_KEY', 'p006-wave1m-auth-key');
define('SECURE_AUTH_KEY', 'p006-wave1m-secure-auth-key');
define('LOGGED_IN_KEY', 'p006-wave1m-logged-in-key');
define('NONCE_KEY', 'p006-wave1m-nonce-key');
define('AUTH_SALT', 'p006-wave1m-auth-salt');
define('SECURE_AUTH_SALT', 'p006-wave1m-secure-auth-salt');
define('LOGGED_IN_SALT', 'p006-wave1m-logged-in-salt');
define('NONCE_SALT', 'p006-wave1m-nonce-salt');
define('WP_DEBUG', false);
define('DISABLE_WP_CRON', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);
define('WP_ENVIRONMENT_TYPE', 'development');
define('FS_METHOD', 'direct');
$table_prefix = 'wpep006m_';
require_once ABSPATH . 'wp-settings.php';
PHP

cat > "${mu_dir}/p006-wave1m-network-deny.php" <<'PHP'
<?php
if (!defined('WPE_P006M_NETWORK_DENY_ACTIVE')) {
    define('WPE_P006M_NETWORK_DENY_ACTIVE', true);
}
add_filter('pre_http_request', static function ($preempt, $args, $url) {
    $log = trim((string) getenv('WPE_P006_NETWORK_LOG'));
    if ($log !== '') {
        file_put_contents($log, (string) $url . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    return new WP_Error(
        'p006_wave1m_network_blocked',
        'Outbound WordPress HTTP is denied during P-006 Wave 1M FP-58 evidence.'
    );
}, PHP_INT_MIN, 3);
PHP

cat > /tmp/p006-wave1m-core-install.php <<'PHP'
<?php
declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '') {
    fwrite(STDERR, "Missing WPE_TEST_WORDPRESS_DIR\n");
    exit(1);
}

$_SERVER['HTTP_HOST'] = 'p006-wave1m.test';
$_SERVER['SERVER_NAME'] = 'p006-wave1m.test';
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
        'WPEssential P-006 Wave 1M',
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

: > "${WPE_P006_NETWORK_LOG}"
php /tmp/p006-wave1m-core-install.php
rm -f /tmp/p006-wave1m-core-install.php

test -f "${wp_dir}/wp-config.php"
test -f "${mu_dir}/p006-wave1m-network-deny.php"
test -d "${plugins_dir}/wpessential"
test -d "${plugins_dir}/wpessential-pro"
test ! -L "${plugins_dir}/wpessential"
test ! -L "${plugins_dir}/wpessential-pro"
