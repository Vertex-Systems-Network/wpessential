#!/usr/bin/env bash
set -euo pipefail

: "${WPE_P006_EXPECTED_WP:?missing WPE_P006_EXPECTED_WP}"
: "${WPE_TEST_WORDPRESS_DIR:?missing WPE_TEST_WORDPRESS_DIR}"
: "${WPE_P006_F0_ZIP_PATH:?missing WPE_P006_F0_ZIP_PATH}"
: "${WPE_P006_F2_ZIP_PATH:?missing WPE_P006_F2_ZIP_PATH}"
: "${WPE_P006_P0_ZIP_PATH:?missing WPE_P006_P0_ZIP_PATH}"
: "${WPE_TEST_WP_DB:?missing WPE_TEST_WP_DB}"
: "${WPE_TEST_WP_DB_HOST:?missing WPE_TEST_WP_DB_HOST}"
: "${WPE_TEST_MYSQL_USER:?missing WPE_TEST_MYSQL_USER}"
: "${WPE_TEST_MYSQL_PASSWORD:?missing WPE_TEST_MYSQL_PASSWORD}"

wp_dir="${WPE_TEST_WORDPRESS_DIR}"
archive="/tmp/p006-wave1t-wordpress-${WPE_P006_EXPECTED_WP}.tar.gz"
stage_root="${wp_dir}/wp-content/p006-wave1t-packages"
plugins_dir="${wp_dir}/wp-content/plugins"

rm -rf "${wp_dir}" "${archive}"
curl --fail --silent --show-error --location --retry 3 \
  "https://wordpress.org/wordpress-${WPE_P006_EXPECTED_WP}.tar.gz" \
  --output "${archive}"

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

extract_node "${WPE_P006_F0_ZIP_PATH}" f0 wpessential wpessential.php
extract_node "${WPE_P006_F2_ZIP_PATH}" f2 wpessential wpessential.php
extract_node "${WPE_P006_P0_ZIP_PATH}" p0 wpessential-pro wpessential-pro.php

ln -s "../p006-wave1t-packages/f0/wpessential" "${plugins_dir}/wpessential"
ln -s "../p006-wave1t-packages/p0/wpessential-pro" "${plugins_dir}/wpessential-pro"

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
define('AUTH_KEY', 'p006-wave1t-auth-key');
define('SECURE_AUTH_KEY', 'p006-wave1t-secure-auth-key');
define('LOGGED_IN_KEY', 'p006-wave1t-logged-in-key');
define('NONCE_KEY', 'p006-wave1t-nonce-key');
define('AUTH_SALT', 'p006-wave1t-auth-salt');
define('SECURE_AUTH_SALT', 'p006-wave1t-secure-auth-salt');
define('LOGGED_IN_SALT', 'p006-wave1t-logged-in-salt');
define('NONCE_SALT', 'p006-wave1t-nonce-salt');
define('WP_DEBUG', false);
define('DISABLE_WP_CRON', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);
define('WP_ENVIRONMENT_TYPE', 'development');
$entitlement = getenv('WPE_P006_LOCAL_ENTITLEMENT_STATE');
if (is_string($entitlement) && $entitlement !== '') {
    define('WPE_PRO_LOCAL_ENTITLEMENT_STATE', $entitlement);
}
$tablePrefix = getenv('WPE_P006_TABLE_PREFIX');
if (!is_string($tablePrefix) || preg_match('/^wpep006t[0-9]{2}_$/', $tablePrefix) !== 1) {
    die('Invalid Wave 1T table prefix');
}
$table_prefix = $tablePrefix;
require_once ABSPATH . 'wp-settings.php';
PHP

cat > "${wp_dir}/wp-content/mu-plugins/p006-wave1t-probes.php" <<'PHP'
<?php
if (!defined('WPE_P006T_PROBE_ACTIVE')) {
    define('WPE_P006T_PROBE_ACTIVE', true);
}
add_filter('pre_http_request', static function ($preempt, $parsedArgs, $url) {
    if (function_exists('wp_installing') && wp_installing()) {
        return new WP_Error(
            'p006_wave1t_core_install_network_blocked',
            'Core installer HTTP is denied inside the disposable P-006 Wave 1T fixture.'
        );
    }
    $log = trim((string) getenv('WPE_P006_NETWORK_LOG'));
    if ($log !== '') {
        file_put_contents($log, (string) $url . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    return new WP_Error('p006_wave1t_network_blocked', 'Outbound HTTP denied during formal Wave 1T runtime evidence.');
}, PHP_INT_MIN, 3);
add_filter('query', static function ($query) {
    if (is_string($query) && stripos($query, 'wpe_') !== false) {
        $log = trim((string) getenv('WPE_P006_SQL_LOG'));
        if ($log !== '') {
            $normalized = preg_replace('/\s+/', ' ', trim($query));
            file_put_contents($log, (string) $normalized . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
    return $query;
}, PHP_INT_MIN, 1);
PHP

test -f "${wp_dir}/wp-config.php"
test -f "${wp_dir}/wp-content/mu-plugins/p006-wave1t-probes.php"
test -L "${plugins_dir}/wpessential"
test -L "${plugins_dir}/wpessential-pro"
