#!/usr/bin/env bash
set -euo pipefail

: "${WPE_P006_EXPECTED_WP:?missing WPE_P006_EXPECTED_WP}"
: "${WPE_TEST_WORDPRESS_DIR:?missing WPE_TEST_WORDPRESS_DIR}"
: "${WPE_TEST_WP_DB:?missing WPE_TEST_WP_DB}"
: "${WPE_TEST_WP_DB_HOST:?missing WPE_TEST_WP_DB_HOST}"
: "${WPE_TEST_MYSQL_USER:?missing WPE_TEST_MYSQL_USER}"
: "${WPE_TEST_MYSQL_PASSWORD:?missing WPE_TEST_MYSQL_PASSWORD}"
: "${WPE_P006_TABLE_PREFIX:?missing WPE_P006_TABLE_PREFIX}"
: "${WPE_P006_NETWORK_LOG:?missing WPE_P006_NETWORK_LOG}"

if [[ "${WPE_TEST_WP_DB}" != "wpessential_b6d" ]]; then
  echo "invalid B6d disposable database" >&2
  exit 1
fi
if [[ "${WPE_P006_TABLE_PREFIX}" != "wpep006b6d_" ]]; then
  echo "invalid B6d table prefix" >&2
  exit 1
fi

wp_dir="${WPE_TEST_WORDPRESS_DIR}"
archive="/tmp/p006-b6d-wordpress-${WPE_P006_EXPECTED_WP}.tar.gz"

rm -rf "${wp_dir}" "${archive}"
curl --proto '=https' --tlsv1.2 --fail --silent --show-error --location --retry 3 \
  "https://wordpress.org/wordpress-${WPE_P006_EXPECTED_WP}.tar.gz" \
  --output "${archive}"

tar -xzf "${archive}" -C /tmp
test -f "${wp_dir}/wp-load.php"
mkdir -p "${wp_dir}/wp-content/mu-plugins"

cat > "${wp_dir}/wp-config.php" <<'PHP'
<?php
define('DB_NAME', getenv('WPE_TEST_WP_DB') ?: 'wpessential_b6d');
define('DB_USER', getenv('WPE_TEST_MYSQL_USER') ?: 'root');
define('DB_PASSWORD', getenv('WPE_TEST_MYSQL_PASSWORD') ?: 'root');
define('DB_HOST', getenv('WPE_TEST_WP_DB_HOST') ?: '127.0.0.1:3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('AUTH_KEY', 'p006-b6d-auth-key');
define('SECURE_AUTH_KEY', 'p006-b6d-secure-auth-key');
define('LOGGED_IN_KEY', 'p006-b6d-logged-in-key');
define('NONCE_KEY', 'p006-b6d-nonce-key');
define('AUTH_SALT', 'p006-b6d-auth-salt');
define('SECURE_AUTH_SALT', 'p006-b6d-secure-auth-salt');
define('LOGGED_IN_SALT', 'p006-b6d-logged-in-salt');
define('NONCE_SALT', 'p006-b6d-nonce-salt');
define('WP_DEBUG', false);
define('DISABLE_WP_CRON', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);
define('WP_ENVIRONMENT_TYPE', 'development');
$tablePrefix = getenv('WPE_P006_TABLE_PREFIX');
if (!is_string($tablePrefix) || $tablePrefix !== 'wpep006b6d_') {
    die('Invalid B6d table prefix');
}
$table_prefix = $tablePrefix;
require_once ABSPATH . 'wp-settings.php';
PHP

cat > "${wp_dir}/wp-content/mu-plugins/p006-b6d-network-block.php" <<'PHP'
<?php
if (!defined('P006_B6D_NETWORK_BLOCK_ACTIVE')) {
    define('P006_B6D_NETWORK_BLOCK_ACTIVE', true);
}
add_filter('pre_http_request', static function ($preempt, $parsedArgs, $url) {
    $log = trim((string) getenv('WPE_P006_NETWORK_LOG'));
    if ($log !== '') {
        file_put_contents($log, (string) $url . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    return new WP_Error(
        'p006_b6d_network_blocked',
        'Outbound WordPress HTTP is denied in B6d prerequisite evidence.',
    );
}, PHP_INT_MIN, 3);
PHP

: > "${WPE_P006_NETWORK_LOG}"
test -f "${wp_dir}/wp-config.php"
test -f "${wp_dir}/wp-content/mu-plugins/p006-b6d-network-block.php"
