#!/usr/bin/env bash
set -euo pipefail

: "${WPE_P006_EXPECTED_WP:?missing WPE_P006_EXPECTED_WP}"
: "${WPE_TEST_WORDPRESS_DIR:?missing WPE_TEST_WORDPRESS_DIR}"
: "${WPE_P006_F1_ZIP_PATH:?missing WPE_P006_F1_ZIP_PATH}"
: "${WPE_P006_F2_ZIP_PATH:?missing WPE_P006_F2_ZIP_PATH}"
: "${WPE_P006_P0_ZIP_PATH:?missing WPE_P006_P0_ZIP_PATH}"
: "${WPE_P006_P2_ZIP_PATH:?missing WPE_P006_P2_ZIP_PATH}"
: "${WPE_TEST_WP_DB:?missing WPE_TEST_WP_DB}"
: "${WPE_TEST_WP_DB_HOST:?missing WPE_TEST_WP_DB_HOST}"
: "${WPE_TEST_MYSQL_USER:?missing WPE_TEST_MYSQL_USER}"
: "${WPE_TEST_MYSQL_PASSWORD:?missing WPE_TEST_MYSQL_PASSWORD}"

wp_dir="${WPE_TEST_WORDPRESS_DIR}"
archive="/tmp/p006-wave1l-wordpress-${WPE_P006_EXPECTED_WP}.tar.gz"
stage_root="${wp_dir}/wp-content/p006-wave1l-packages"
plugins_dir="${wp_dir}/wp-content/plugins"

rm -rf "${wp_dir}" "${archive}"
curl --fail --silent --show-error --location --retry 3   "https://wordpress.org/wordpress-${WPE_P006_EXPECTED_WP}.tar.gz"   --output "${archive}"

tar -xzf "${archive}" -C /tmp

test -f "${wp_dir}/wp-load.php"
mkdir -p "${plugins_dir}" "${wp_dir}/wp-content/mu-plugins" "${stage_root}"

extract_node() {
  local zip_path="$1"
  local instance="$2"
  local slug="$3"
  local main_file="$4"
  local target="${stage_root}/${instance}"

  rm -rf "${target}"
  mkdir -p "${target}"
  unzip -q "${zip_path}" -d "${target}"
  test -f "${target}/${slug}/${main_file}"
}

extract_node "${WPE_P006_F1_ZIP_PATH}" f1 wpessential wpessential.php
extract_node "${WPE_P006_F2_ZIP_PATH}" f2 wpessential wpessential.php
extract_node "${WPE_P006_P0_ZIP_PATH}" p0 wpessential-pro wpessential-pro.php
extract_node "${WPE_P006_P2_ZIP_PATH}" p2 wpessential-pro wpessential-pro.php

ln -s "../p006-wave1l-packages/f1/wpessential" "${plugins_dir}/wpessential"
ln -s "../p006-wave1l-packages/p0/wpessential-pro" "${plugins_dir}/wpessential-pro"

test -L "${plugins_dir}/wpessential"
test -L "${plugins_dir}/wpessential-pro"
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
define('AUTH_KEY', 'p006-wave1l-auth-key');
define('SECURE_AUTH_KEY', 'p006-wave1l-secure-auth-key');
define('LOGGED_IN_KEY', 'p006-wave1l-logged-in-key');
define('NONCE_KEY', 'p006-wave1l-nonce-key');
define('AUTH_SALT', 'p006-wave1l-auth-salt');
define('SECURE_AUTH_SALT', 'p006-wave1l-secure-auth-salt');
define('LOGGED_IN_SALT', 'p006-wave1l-logged-in-salt');
define('NONCE_SALT', 'p006-wave1l-nonce-salt');
define('WP_DEBUG', false);
define('DISABLE_WP_CRON', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);
define('WP_ENVIRONMENT_TYPE', 'development');
$table_prefix = 'wpep006l_';
require_once ABSPATH . 'wp-settings.php';
PHP

cat > "${wp_dir}/wp-content/mu-plugins/p006-wave1l-core-install-network-deny.php" <<'PHP'
<?php
add_filter('pre_http_request', static function ($preempt, $parsedArgs, $url) {
    if (function_exists('wp_installing') && wp_installing()) {
        return new WP_Error(
            'p006_wave1l_core_install_network_blocked',
            'Core installer HTTP is denied inside the disposable P-006 Wave 1L fixture.'
        );
    }
    return $preempt;
}, PHP_INT_MIN, 3);
PHP

cat > /tmp/p006-wave1l-core-install.php <<'PHP'
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
        'WPEssential P-006 Wave 1L',
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

php /tmp/p006-wave1l-core-install.php

rm -f   /tmp/p006-wave1l-core-install.php   "${wp_dir}/wp-content/mu-plugins/p006-wave1l-core-install-network-deny.php"

test -f "${wp_dir}/wp-config.php"
test -L "${plugins_dir}/wpessential"
test -L "${plugins_dir}/wpessential-pro"
