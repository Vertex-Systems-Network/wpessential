<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Roles read seam SKIP (WordPress fixture unavailable)\n");
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
define('DB_PASSWORD', getenv('WPE_TEST_MYSQL_PASSWORD') ?: 'root');
define('DB_HOST', getenv('WPE_TEST_WP_DB_HOST') ?: '127.0.0.1:3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('AUTH_KEY',         'wpessential-roles-read-auth-key');
define('SECURE_AUTH_KEY',  'wpessential-roles-read-secure-auth-key');
define('LOGGED_IN_KEY',    'wpessential-roles-read-logged-in-key');
define('NONCE_KEY',        'wpessential-roles-read-nonce-key');
define('AUTH_SALT',        'wpessential-roles-read-auth-salt');
define('SECURE_AUTH_SALT', 'wpessential-roles-read-secure-auth-salt');
define('LOGGED_IN_SALT',   'wpessential-roles-read-logged-in-salt');
define('NONCE_SALT',       'wpessential-roles-read-nonce-salt');
$table_prefix = 'wperoles_';
define('WP_DEBUG', false);
require_once ABSPATH . 'wp-settings.php';
PHP;
    if (file_put_contents($wpDir . '/wp-config.php', $config . "\n") === false) {
        fwrite(STDERR, "FAIL: unable to create WordPress Roles read-seam configuration\n");
        exit(1);
    }
}

require $wpDir . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

$root = dirname(__DIR__, 2);
require_once $root . '/vendor/autoload.php';

use WPEssential\Bootstrap\Plugin;
use WPEssential\Modules\Roles\RolesModule;
use WPEssential\Modules\Roles\RolesReadService;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

function rolesReadExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Roles Read Seam',
        'wpessential_roles_admin',
        'roles-admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    rolesReadExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_roles_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user(
        'wpessential_roles_admin',
        'test-password-strong',
        'roles-admin@example.test',
    );
    rolesReadExpect(is_int($userId) && $userId > 0, 'Roles administrator must be created');
    $admin = get_user_by('id', $userId);
}
rolesReadExpect($admin instanceof WP_User, 'Roles administrator must exist');
$admin->set_role('administrator');
wp_set_current_user($admin->ID);

remove_role('wpe_roles_probe');
$probe = add_role(
    'wpe_roles_probe',
    'WPE Roles Probe',
    [
        'read' => true,
        'edit_posts' => false,
        'wpe_probe_cap' => true,
    ],
);
rolesReadExpect($probe instanceof WP_Role, 'Probe role must be created for the integration fixture');

$registry = wp_roles();
rolesReadExpect($registry instanceof WP_Roles, 'WordPress role registry must be available');
$beforeRoles = $registry->roles;

Plugin::registerModule(new RolesModule());
$kernel = Plugin::boot();
rolesReadExpect($kernel !== null, 'Plugin kernel must boot with Roles read module');

$services = $kernel->services();
$read = $services->get(RolesModule::SERVICE_READ);
$abilities = $services->get('platform.abilities');
rolesReadExpect($read instanceof RolesReadService, 'Canonical Roles read service must register');
rolesReadExpect($abilities instanceof AbilityRegistry, 'Shared Ability Registry must be available');

$catalogDescriptor = $abilities->descriptor(RolesReadService::ABILITY_CATALOG);
$impactDescriptor = $abilities->descriptor(RolesReadService::ABILITY_IMPACT);
rolesReadExpect($catalogDescriptor !== null, 'Roles catalog ability must register');
rolesReadExpect($impactDescriptor !== null, 'Roles impact ability must register');
rolesReadExpect($catalogDescriptor->ownerSurfaceId === 30, 'Roles catalog ability must be owned by Surface 30');
rolesReadExpect($impactDescriptor->ownerSurfaceId === 30, 'Roles impact ability must be owned by Surface 30');
rolesReadExpect($catalogDescriptor->mutates === false, 'Roles catalog ability must be read-only');
rolesReadExpect($impactDescriptor->mutates === false, 'Roles impact ability must be read-only');

$siteId = max(1, (int) get_current_blog_id());
$networkId = function_exists('get_current_network_id') ? max(1, (int) get_current_network_id()) : 1;
$context = new ExecutionContext(new Principal($admin->ID), $siteId, networkId: $networkId);

$catalog = $abilities->execute(RolesReadService::ABILITY_CATALOG, [], $context);
rolesReadExpect(is_array($catalog), 'Roles catalog ability must return an array');
rolesReadExpect(($catalog['state'] ?? null) === 'healthy', 'Roles catalog must resolve healthy current-site role truth');

$probeRow = null;
foreach (($catalog['roles'] ?? []) as $role) {
    if (is_array($role) && ($role['key'] ?? null) === 'wpe_roles_probe') {
        $probeRow = $role;
        break;
    }
}
rolesReadExpect(is_array($probeRow), 'Probe role must appear in canonical catalog');
rolesReadExpect(($probeRow['capabilities']['read'] ?? null) === true, 'Explicit true capability must be preserved');
rolesReadExpect(array_key_exists('edit_posts', $probeRow['capabilities']), 'Explicit false capability key must remain present');
rolesReadExpect($probeRow['capabilities']['edit_posts'] === false, 'Explicit false capability value must be preserved');
rolesReadExpect(!array_key_exists('upload_files', $probeRow['capabilities']), 'Absent capability must remain absent');
rolesReadExpect(($probeRow['provenance'] ?? null) === 'unknown', 'Unproven role provenance must remain unknown');

$allowed = $abilities->execute(
    RolesReadService::ABILITY_IMPACT,
    ['capability' => 'wpe_probe_cap'],
    $context,
);
rolesReadExpect(in_array('wpe_roles_probe', $allowed['explicit_allow_roles'] ?? [], true), 'Probe role must be explicit allow for probe capability');

$denied = $abilities->execute(
    RolesReadService::ABILITY_IMPACT,
    ['capability' => 'edit_posts'],
    $context,
);
rolesReadExpect(in_array('wpe_roles_probe', $denied['explicit_deny_roles'] ?? [], true), 'Probe role must be explicit deny for edit_posts');

$absent = $abilities->execute(
    RolesReadService::ABILITY_IMPACT,
    ['capability' => 'upload_files'],
    $context,
);
rolesReadExpect(in_array('wpe_roles_probe', $absent['absent_roles'] ?? [], true), 'Probe role must remain absent for upload_files');

$meta = $abilities->execute(
    RolesReadService::ABILITY_IMPACT,
    ['capability' => 'edit_post'],
    $context,
);
rolesReadExpect(($meta['meta_capability'] ?? false) === true, 'Contextual edit_post must be marked as a meta capability');
rolesReadExpect(($meta['context_required'] ?? false) === true, 'Contextual edit_post must require object context for effective authorization');

global $wpdb;
rolesReadExpect($wpdb instanceof wpdb, 'WordPress database adapter must be available');
wp_cache_flush();
$queryBefore = (int) $wpdb->num_queries;
for ($index = 0; $index < 50; $index++) {
    $impact = $read->capabilityImpact('wpe_probe_cap', $context);
    rolesReadExpect(($impact['state'] ?? null) === 'healthy', 'Repeated bounded impact reads must remain healthy');
}
$queryDelta = (int) $wpdb->num_queries - $queryBefore;
rolesReadExpect(
    $queryDelta <= 2,
    sprintf('Fifty current-site role impact reads must stay bounded, got %d SQL queries', $queryDelta),
);

$afterRoles = wp_roles()->roles;
rolesReadExpect($beforeRoles === $afterRoles, 'Roles read seam must not mutate WordPress role definitions');

try {
    $read->roleCatalog(new ExecutionContext(new Principal(null), $siteId, networkId: $networkId));
    rolesReadExpect(false, 'Unauthenticated direct service access must be denied');
} catch (RuntimeException $exception) {
    rolesReadExpect(
        str_contains($exception->getMessage(), 'unauthenticated'),
        'Unauthenticated service denial must preserve Policy reason',
    );
}

fwrite(
    STDOUT,
    sprintf(
        "WPEssential Roles canonical read-seam real-WordPress PASS (50 reads=%d SQL queries)\n",
        $queryDelta,
    ),
);
