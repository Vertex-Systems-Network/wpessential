<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Fields runtime reference integration SKIP (WordPress fixture unavailable)\n");
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
define('AUTH_KEY',         'wpessential-test-auth-key');
define('SECURE_AUTH_KEY',  'wpessential-test-secure-auth-key');
define('LOGGED_IN_KEY',    'wpessential-test-logged-in-key');
define('NONCE_KEY',        'wpessential-test-nonce-key');
define('AUTH_SALT',        'wpessential-test-auth-salt');
define('SECURE_AUTH_SALT', 'wpessential-test-secure-auth-salt');
define('LOGGED_IN_SALT',   'wpessential-test-logged-in-salt');
define('NONCE_SALT',       'wpessential-test-nonce-salt');
$table_prefix = 'wpcore_';
define('WP_DEBUG', false);
require_once ABSPATH . 'wp-settings.php';
PHP;
    if (file_put_contents($wpDir . '/wp-config.php', $config . "\n") === false) {
        fwrite(STDERR, "FAIL: unable to create WordPress test configuration\n");
        exit(1);
    }
}

require $wpDir . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

$root = dirname(__DIR__, 2);
require_once $root . '/vendor/autoload.php';

use WPEssential\Bootstrap\Plugin;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\ModuleActivationPolicyInterface;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldsModule;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

function fieldsRuntimeReferenceExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @param array<string,string> $extra */
function fieldsRuntimeReferenceRunChild(string $root, array $extra): void
{
    $previous = [];
    foreach ($extra as $name => $value) {
        $previous[$name] = getenv($name);
        putenv($name . '=' . $value);
    }

    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(
        $root . '/tests/Integration/wordpress-fields-runtime-reference-core.php',
    );
    passthru($command, $exitCode);

    foreach ($previous as $name => $value) {
        if ($value === false) {
            putenv($name);
        } else {
            putenv($name . '=' . $value);
        }
    }

    fieldsRuntimeReferenceExpect($exitCode === 0, 'fresh WordPress reference child must pass');
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Fields Runtime Reference',
        'wpessential_admin',
        'admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    fieldsRuntimeReferenceExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user('wpessential_admin', 'test-password-strong', 'admin@example.test');
    fieldsRuntimeReferenceExpect(is_int($userId) && $userId > 0, 'integration administrator must be created');
    $admin = get_user_by('id', $userId);
}
fieldsRuntimeReferenceExpect($admin instanceof WP_User, 'integration administrator must exist');
$admin->set_role('administrator');
wp_set_current_user($admin->ID);

Plugin::setModuleActivationPolicy(new class implements ModuleActivationPolicyInterface {
    public function allows(ModuleManifest $manifest): bool
    {
        return $manifest->edition === 'free' || $manifest->id === 'custom-fields';
    }
});
Plugin::registerModule(new FieldsModule());
$kernel = Plugin::boot();
fieldsRuntimeReferenceExpect($kernel !== null, 'WPEssential kernel must boot in seed process');
fieldsRuntimeReferenceExpect($kernel->modules()->has('custom-fields'), 'explicit external policy must admit concrete Fields module');

$services = $kernel->services();
$abilities = $services->get('platform.abilities');
$contexts = $services->get('platform.abilities.contexts');
$definitions = $services->get('platform.definitions');
fieldsRuntimeReferenceExpect($abilities instanceof AbilityRegistry, 'shared Ability Registry must be available');
fieldsRuntimeReferenceExpect($contexts instanceof WordPressExecutionContextFactory, 'shared execution context factory must be available');
fieldsRuntimeReferenceExpect($definitions instanceof DefinitionRepositoryInterface, 'shared Definition Repository must be available');
$context = $contexts->current();

$cpt = $abilities->execute('wpessential/cpt/save', [
    'payload' => [
        'post_type_key' => 'wpe_ref_book',
        'name' => 'Reference Books',
        'singular_name' => 'Reference Book',
        'public' => true,
        'show_in_rest' => true,
        'supports' => ['title', 'custom-fields', 'revisions'],
    ],
], $context)['definition'];
$cpt = $abilities->execute('wpessential/cpt/status', [
    'id' => $cpt['id'],
    'expected_revision' => $cpt['revision'],
    'status' => 'published',
], $context)['definition'];
fieldsRuntimeReferenceExpect(($cpt['status'] ?? null) === 'published', 'reference CPT must publish through shared Ability boundary');

$created = $abilities->execute('wpessential/fields/save-group', [
    'payload' => [
        'group_key' => 'runtime_reference_book',
        'title' => 'Runtime Reference Book',
        'fields' => [
            [
                'key' => 'wpe_ref_headline',
                'label' => 'Headline',
                'type' => 'text',
                'show_in_rest' => true,
            ],
            [
                'key' => 'wpe_ref_score',
                'label' => 'Score',
                'type' => 'number',
                'settings' => ['integer' => true],
                'show_in_rest' => true,
            ],
        ],
        'locations' => [[
            ['source' => 'post_type', 'operator' => 'equals', 'value' => 'wpe_ref_book'],
            ['source' => 'post_status', 'operator' => 'equals', 'value' => 'publish'],
        ]],
        'storage' => ['mode' => 'native_post_meta'],
        'show_in_rest' => true,
        'revision_policy' => 'enabled',
    ],
], $context)['definition'];
$published = $abilities->execute('wpessential/fields/status-group', [
    'id' => $created['id'],
    'expected_revision' => $created['revision'],
    'status' => 'published',
], $context)['definition'];
fieldsRuntimeReferenceExpect(($published['status'] ?? null) === 'published', 'reference Field Group must publish through shared Ability boundary');

$draft = $abilities->execute('wpessential/fields/save-group', [
    'payload' => [
        'group_key' => 'runtime_reference_draft',
        'title' => 'Runtime Reference Draft',
        'fields' => [[
            'key' => 'wpe_ref_draft',
            'label' => 'Draft Value',
            'type' => 'text',
            'show_in_rest' => true,
        ]],
        'locations' => [[
            ['source' => 'post_type', 'operator' => 'equals', 'value' => 'wpe_ref_book'],
        ]],
        'storage' => ['mode' => 'native_post_meta'],
        'show_in_rest' => true,
    ],
], $context)['definition'];
fieldsRuntimeReferenceExpect(($draft['status'] ?? null) === 'draft', 'draft Field Group must remain non-runtime');

$groupNormalizer = new FieldGroupDefinitionNormalizer();
$foreignPayload = $groupNormalizer->normalize([
    'group_key' => 'runtime_reference_foreign',
    'title' => 'Runtime Reference Foreign',
    'fields' => [[
        'uuid' => '93333333-3333-4333-8333-333333333333',
        'key' => 'wpe_ref_foreign',
        'label' => 'Foreign Value',
        'type' => 'text',
        'show_in_rest' => true,
    ]],
    'locations' => [[
        ['source' => 'post_type', 'operator' => 'equals', 'value' => 'wpe_ref_book'],
    ]],
    'storage' => ['mode' => 'native_post_meta'],
    'show_in_rest' => true,
], true);
$definitions->save(new Definition(
    id: '94444444-4444-4444-8444-444444444444',
    slug: 'field-group-runtime-reference-foreign',
    type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
    schemaVersion: 1,
    ownerSurfaceId: 4,
    status: DefinitionStatus::Published,
    payload: $foreignPayload,
    revision: 1,
));

$fields = $published['payload']['fields'] ?? [];
fieldsRuntimeReferenceExpect(is_array($fields) && count($fields) === 2, 'published reference group must retain two canonical Fields');
$fixture = [
    'admin_id' => $admin->ID,
    'group_id' => $published['id'],
    'group_revision' => $published['revision'],
    'headline_uuid' => $fields[0]['uuid'],
    'score_uuid' => $fields[1]['uuid'],
    'post_type' => 'wpe_ref_book',
];
$fixturePath = sys_get_temp_dir() . '/wpessential-fields-runtime-reference.json';
fieldsRuntimeReferenceExpect(
    file_put_contents($fixturePath, json_encode($fixture, JSON_THROW_ON_ERROR)) !== false,
    'reference fixture metadata must be written',
);

$muDir = $wpDir . '/wp-content/mu-plugins';
if (!is_dir($muDir)) {
    fieldsRuntimeReferenceExpect(mkdir($muDir, 0777, true), 'must-use plugin directory must be created');
}
$muPlugin = <<<'PHP'
<?php
declare(strict_types=1);

$root = (string) getenv('WPE_TEST_PLUGIN_ROOT');
if ($root === '' || !is_file($root . '/vendor/autoload.php')) {
    return;
}
require_once $root . '/vendor/autoload.php';

\WPEssential\Bootstrap\Plugin::setModuleActivationPolicy(new class implements \WPEssential\Contracts\ModuleActivationPolicyInterface {
    public function allows(\WPEssential\Platform\Modules\ModuleManifest $manifest): bool
    {
        return $manifest->edition === 'free' || $manifest->id === 'custom-fields';
    }
});
\WPEssential\Bootstrap\Plugin::registerModule(new \WPEssential\Modules\Fields\FieldsModule());
add_action('plugins_loaded', static function (): void {
    \WPEssential\Bootstrap\Plugin::boot();
}, -100);
PHP;
fieldsRuntimeReferenceExpect(
    file_put_contents($muDir . '/wpessential-fields-runtime-reference.php', $muPlugin . "\n") !== false,
    'must-use Fields contributor fixture must be written',
);

fieldsRuntimeReferenceRunChild($root, [
    'WPE_TEST_PLUGIN_ROOT' => $root,
    'WPE_FIELDS_REFERENCE_FIXTURE' => $fixturePath,
    'WPE_FIELDS_REFERENCE_MODE' => 'success',
]);

$collisionPayloadA = $groupNormalizer->normalize([
    'group_key' => 'runtime_reference_collision_a',
    'title' => 'Runtime Reference Collision A',
    'fields' => [[
        'uuid' => '95555555-5555-4555-8555-555555555555',
        'key' => 'wpe_ref_collision',
        'label' => 'Collision A',
        'type' => 'text',
    ]],
    'locations' => [[['source' => 'post_type', 'operator' => 'equals', 'value' => 'wpe_ref_book']]],
    'storage' => ['mode' => 'native_post_meta'],
], true);
$collisionPayloadB = $groupNormalizer->normalize([
    'group_key' => 'runtime_reference_collision_b',
    'title' => 'Runtime Reference Collision B',
    'fields' => [[
        'uuid' => '96666666-6666-4666-8666-666666666666',
        'key' => 'wpe_ref_collision',
        'label' => 'Collision B',
        'type' => 'text',
    ]],
    'locations' => [[['source' => 'post_type', 'operator' => 'equals', 'value' => 'wpe_ref_book']]],
    'storage' => ['mode' => 'native_post_meta'],
], true);
$definitions->save(new Definition(
    id: '97777777-7777-4777-8777-777777777777',
    slug: 'field-group-runtime-reference-collision-a',
    type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
    schemaVersion: 1,
    ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
    status: DefinitionStatus::Published,
    payload: $collisionPayloadA,
    revision: 1,
));
$definitions->save(new Definition(
    id: '98888888-8888-4888-8888-888888888888',
    slug: 'field-group-runtime-reference-collision-b',
    type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
    schemaVersion: 1,
    ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
    status: DefinitionStatus::Published,
    payload: $collisionPayloadB,
    revision: 1,
));

fieldsRuntimeReferenceRunChild($root, [
    'WPE_TEST_PLUGIN_ROOT' => $root,
    'WPE_FIELDS_REFERENCE_FIXTURE' => $fixturePath,
    'WPE_FIELDS_REFERENCE_MODE' => 'collision',
]);

@unlink($muDir . '/wpessential-fields-runtime-reference.php');
@unlink($fixturePath);

fwrite(STDOUT, "WPEssential Fields runtime reference integration PASS\n");
