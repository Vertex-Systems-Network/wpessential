<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if (!function_exists('get_bloginfo')) {
    if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
        fwrite(STDOUT, "WPEssential Taxonomy role-impact SKIP (WordPress fixture unavailable)\n");
        exit(0);
    }

    $_SERVER['HTTP_HOST'] ??= 'localhost';
    $_SERVER['SERVER_NAME'] ??= 'localhost';
    $_SERVER['SERVER_PORT'] ??= '80';
    $_SERVER['REQUEST_URI'] ??= '/';
    $_SERVER['REQUEST_METHOD'] ??= 'GET';
    $_SERVER['SERVER_PROTOCOL'] ??= 'HTTP/1.1';
    $_SERVER['REMOTE_ADDR'] ??= '127.0.0.1';

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
define('WP_HOME', 'http://localhost');
define('WP_SITEURL', 'http://localhost');
define('AUTH_KEY',         'wpessential-tax-role-impact-auth-key');
define('SECURE_AUTH_KEY',  'wpessential-tax-role-impact-secure-auth-key');
define('LOGGED_IN_KEY',    'wpessential-tax-role-impact-logged-in-key');
define('NONCE_KEY',        'wpessential-tax-role-impact-nonce-key');
define('AUTH_SALT',        'wpessential-tax-role-impact-auth-salt');
define('SECURE_AUTH_SALT', 'wpessential-tax-role-impact-secure-auth-salt');
define('LOGGED_IN_SALT',   'wpessential-tax-role-impact-logged-in-salt');
define('NONCE_SALT',       'wpessential-tax-role-impact-nonce-salt');
$table_prefix = 'wpetaxrole_';
define('WP_DEBUG', false);
require_once ABSPATH . 'wp-settings.php';
PHP;
        if (file_put_contents($wpDir . '/wp-config.php', $config . "\n") === false) {
            fwrite(STDERR, "FAIL: unable to create WordPress Taxonomy role-impact configuration\n");
            exit(1);
        }
    }

    require $wpDir . '/wp-load.php';
}

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

$root = dirname(__DIR__, 2);
require_once $root . '/vendor/autoload.php';

use WPEssential\Bootstrap\Plugin;
use WPEssential\Kernel\Kernel;
use WPEssential\Modules\Roles\RolesModule;
use WPEssential\Modules\Roles\RolesReadService;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

function taxonomyRoleImpactExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @param array<string,mixed> $impact @return array<string,mixed>|null */
function taxonomyRoleImpactOperation(array $impact, string $operation): ?array
{
    foreach (($impact['operations'] ?? []) as $row) {
        if (is_array($row) && ($row['operation'] ?? null) === $operation) {
            return $row;
        }
    }
    return null;
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Taxonomy Role Impact',
        'wpessential_tax_role_admin',
        'tax-role-admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    taxonomyRoleImpactExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_tax_role_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user(
        'wpessential_tax_role_admin',
        'test-password-strong',
        'tax-role-admin@example.test',
    );
    taxonomyRoleImpactExpect(is_int($userId) && $userId > 0, 'Taxonomy role-impact administrator must be created');
    $admin = get_user_by('id', $userId);
}
taxonomyRoleImpactExpect($admin instanceof WP_User, 'Taxonomy role-impact administrator must exist');
$admin->set_role('administrator');
wp_set_current_user((int) $admin->ID);
taxonomyRoleImpactExpect(current_user_can('manage_options'), 'Taxonomy role-impact principal must have manage_options');

remove_role('wpe_tax_role_probe');
$probe = add_role(
    'wpe_tax_role_probe',
    'WPE Taxonomy Role Probe',
    [
        'read' => true,
        'wpe_tax_allow' => true,
        'wpe_tax_deny' => false,
    ],
);
taxonomyRoleImpactExpect($probe instanceof WP_Role, 'Taxonomy role-impact probe role must be created');

$registry = wp_roles();
taxonomyRoleImpactExpect($registry instanceof WP_Roles, 'WordPress role registry must be available');
$roleSnapshot = $registry->roles;

$kernel = Plugin::kernel();
if (!$kernel instanceof Kernel) {
    Plugin::registerModule(new RolesModule());
    $kernel = Plugin::boot();
}
taxonomyRoleImpactExpect($kernel instanceof Kernel && $kernel->isBooted(), 'Production plugin kernel must be booted');

$services = $kernel->services();
$abilities = $services->get('platform.abilities');
$rolesRead = $services->get(RolesModule::SERVICE_READ);
taxonomyRoleImpactExpect($abilities instanceof AbilityRegistry, 'Shared Ability Registry must be available');
taxonomyRoleImpactExpect($rolesRead instanceof RolesReadService, 'Canonical Surface 30 role-impact service must be registered');

$siteId = max(1, (int) get_current_blog_id());
$networkId = function_exists('get_current_network_id') ? max(1, (int) get_current_network_id()) : 1;
$context = new ExecutionContext(new Principal((int) $admin->ID), $siteId, networkId: $networkId);
$payload = [
    'taxonomy_key' => 'role_impact_probe_tax',
    'object_types' => ['post'],
    'name' => 'Role Impact Probe Taxonomies',
    'singular_name' => 'Role Impact Probe Taxonomy',
    'capabilities' => [
        'manage_terms' => 'wpe_tax_allow',
        'edit_terms' => 'wpe_tax_deny',
        'delete_terms' => 'wpe_tax_absent',
        'assign_terms' => 'edit_post',
    ],
];

$validation = $abilities->execute(
    'wpessential/taxonomy/validate',
    ['payload' => $payload],
    $context,
);
taxonomyRoleImpactExpect(is_array($validation), 'Taxonomy validation ability must return a structured report');
taxonomyRoleImpactExpect(($validation['valid'] ?? false) === true, 'Role-impact probe taxonomy must validate');
$validationDiagnostics = $validation['diagnostics'] ?? null;
taxonomyRoleImpactExpect(is_array($validationDiagnostics), 'Taxonomy validation diagnostics must be available');
$validationImpact = $validationDiagnostics['role_impact'] ?? null;
taxonomyRoleImpactExpect(is_array($validationImpact), 'Validation diagnostics must expose role impact');
taxonomyRoleImpactExpect(($validationImpact['state'] ?? null) === 'healthy', 'Validation role impact must resolve healthy canonical Surface 30 truth');

$manage = taxonomyRoleImpactOperation($validationImpact, 'manage_terms');
$edit = taxonomyRoleImpactOperation($validationImpact, 'edit_terms');
$delete = taxonomyRoleImpactOperation($validationImpact, 'delete_terms');
$assign = taxonomyRoleImpactOperation($validationImpact, 'assign_terms');
taxonomyRoleImpactExpect(is_array($manage) && is_array($edit) && is_array($delete) && is_array($assign), 'All four taxonomy capability operations must be represented');

taxonomyRoleImpactExpect(
    in_array('wpe_tax_role_probe', $manage['impact']['explicit_allow_roles'] ?? [], true),
    'Probe role must remain explicit allow for manage_terms capability',
);
taxonomyRoleImpactExpect(
    in_array('wpe_tax_role_probe', $edit['impact']['explicit_deny_roles'] ?? [], true),
    'Probe role must remain explicit deny for edit_terms capability',
);
taxonomyRoleImpactExpect(
    in_array('wpe_tax_role_probe', $delete['impact']['absent_roles'] ?? [], true),
    'Probe role must remain absent for delete_terms capability',
);
taxonomyRoleImpactExpect(($assign['impact']['classification'] ?? null) === 'contextual_meta', 'edit_post must remain classified as contextual meta capability');
taxonomyRoleImpactExpect(($assign['impact']['context_required'] ?? false) === true, 'edit_post must report required object context');
taxonomyRoleImpactExpect(
    str_contains(implode(' ', $validationImpact['caveats'] ?? []), 'not final user authorization'),
    'Role-impact diagnostics must state that role-entry truth is not final authorization',
);

$saved = $abilities->execute(
    'wpessential/taxonomy/save',
    [
        'payload' => $payload,
        'status' => 'draft',
    ],
    $context,
);
taxonomyRoleImpactExpect(is_array($saved) && is_array($saved['definition'] ?? null), 'Taxonomy save must return the canonical definition');
$definition = $saved['definition'];
$definitionImpact = $definition['read_model']['role_impact'] ?? null;
taxonomyRoleImpactExpect(is_array($definitionImpact), 'Saved Taxonomy read model must expose role impact');
taxonomyRoleImpactExpect(($definitionImpact['state'] ?? null) === 'healthy', 'Saved Taxonomy read model role impact must be healthy');
$definitionManage = taxonomyRoleImpactOperation($definitionImpact, 'manage_terms');
taxonomyRoleImpactExpect(
    is_array($definitionManage)
        && in_array('wpe_tax_role_probe', $definitionManage['impact']['explicit_allow_roles'] ?? [], true),
    'Saved Taxonomy read model must preserve canonical explicit allow role truth',
);

$listed = $abilities->execute('wpessential/taxonomy/list', [], $context);
taxonomyRoleImpactExpect(is_array($listed) && is_array($listed['definitions'] ?? null), 'Taxonomy list must return definitions');
$listedDefinition = null;
foreach ($listed['definitions'] as $candidate) {
    if (is_array($candidate) && ($candidate['id'] ?? null) === ($definition['id'] ?? null)) {
        $listedDefinition = $candidate;
        break;
    }
}
taxonomyRoleImpactExpect(is_array($listedDefinition), 'Saved role-impact probe must appear in Taxonomy list');
taxonomyRoleImpactExpect(
    ($listedDefinition['read_model']['role_impact']['state'] ?? null) === 'healthy',
    'Taxonomy list read model must expose healthy role impact',
);

taxonomyRoleImpactExpect($roleSnapshot === wp_roles()->roles, 'Taxonomy role-impact reads must not mutate WordPress role definitions');

$evidencePath = getenv('WPE_TAXONOMY_ROLE_IMPACT_EVIDENCE_PATH') ?: '';
if ($evidencePath !== '') {
    $directory = dirname($evidencePath);
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
    file_put_contents($evidencePath, json_encode([
        'schema' => 'wpessential-taxonomy-role-impact-evidence-v1',
        'result' => 'PASS',
        'wordpress_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
        'canonical_surface30_service_consumed' => true,
        'validation_role_impact_state' => $validationImpact['state'],
        'definition_role_impact_state' => $definitionImpact['state'],
        'explicit_allow_preserved' => in_array('wpe_tax_role_probe', $manage['impact']['explicit_allow_roles'] ?? [], true),
        'explicit_deny_preserved' => in_array('wpe_tax_role_probe', $edit['impact']['explicit_deny_roles'] ?? [], true),
        'absent_preserved' => in_array('wpe_tax_role_probe', $delete['impact']['absent_roles'] ?? [], true),
        'meta_context_reported' => ($assign['impact']['context_required'] ?? false) === true,
        'diagnostic_not_final_authorization' => str_contains(implode(' ', $validationImpact['caveats'] ?? []), 'not final user authorization'),
        'role_registry_unchanged' => $roleSnapshot === wp_roles()->roles,
        'taxonomy_definition_id' => $definition['id'] ?? null,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
}

remove_role('wpe_tax_role_probe');

fwrite(STDOUT, "WPEssential Taxonomy canonical Surface 30 role-impact consumer PASS\n");
