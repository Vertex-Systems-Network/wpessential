<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Fields storage-key migration integration SKIP (WordPress fixture unavailable)\n");
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
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = $root . '/frameworks/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldStorageKeyMigrationService;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\WordPressPostMetaRegistrar;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

function fieldsMigrationExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @param array<string,mixed> $field */
function fieldsMigrationDefinition(string $id, string $slug, array $field): Definition
{
    return new Definition(
        id: $id,
        slug: $slug,
        type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
        schemaVersion: 1,
        ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
        status: DefinitionStatus::Published,
        payload: [
            'group_key' => str_replace('-', '_', $slug),
            'title' => ucwords(str_replace('-', ' ', $slug)),
            'fields' => [$field],
            'locations' => [[
                ['source' => 'post_type', 'operator' => 'equals', 'value' => 'wpe_migrate_book'],
            ]],
            'storage' => ['mode' => 'native_post_meta'],
            'show_in_rest' => false,
            'revision_policy' => 'disabled',
        ],
        revision: 3,
    );
}

function fieldsMigrationRegisterSource(Definition $definition): void
{
    $group = (new FieldGroupDefinitionNormalizer())->normalize($definition->payload, true);
    $field = $group['fields'][0] ?? null;
    fieldsMigrationExpect(is_array($field), 'normalized migration Field must exist');
    $registration = (new PostMetaRegistrationCompiler())->compile($field, 'wpe_migrate_book');
    (new WordPressPostMetaRegistrar())->register($registration);
}

function fieldsMigrationPost(string $title): int
{
    $postId = wp_insert_post([
        'post_type' => 'wpe_migrate_book',
        'post_status' => 'publish',
        'post_title' => $title,
    ], true);
    fieldsMigrationExpect(is_int($postId) && $postId > 0, 'migration fixture post must be created');
    return $postId;
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Fields Migration Integration',
        'wpessential_admin',
        'admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    fieldsMigrationExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user('wpessential_admin', 'test-password-strong', 'admin@example.test');
    fieldsMigrationExpect(is_int($userId) && $userId > 0, 'integration administrator must be created');
    $admin = get_user_by('id', $userId);
}
fieldsMigrationExpect($admin instanceof WP_User, 'integration administrator must exist');
$admin->set_role('administrator');
wp_set_current_user($admin->ID);

register_post_type('wpe_migrate_book', [
    'public' => false,
    'show_ui' => false,
    'show_in_rest' => false,
    'supports' => ['title', 'custom-fields', 'revisions'],
]);

$repository = new InMemoryDefinitionRepository();
$migrations = new FieldStorageKeyMigrationService($repository);

$scalarDefinition = fieldsMigrationDefinition(
    '71111111-1111-4111-8111-111111111111',
    'migration-scalar',
    [
        'uuid' => '72111111-1111-4111-8111-111111111111',
        'key' => 'wpe_migrate_headline',
        'label' => 'Headline',
        'type' => 'text',
    ],
);
$repository->save($scalarDefinition);
fieldsMigrationRegisterSource($scalarDefinition);
$scalarPostOne = fieldsMigrationPost('Scalar migration one');
$scalarPostTwo = fieldsMigrationPost('Scalar migration two');
update_post_meta($scalarPostOne, 'wpe_migrate_headline', wp_slash('Quote "A\\B"'));
update_post_meta($scalarPostTwo, 'wpe_migrate_headline', 'Second');

$scalarResult = $migrations->migrate(
    $scalarDefinition->id,
    3,
    '72111111-1111-4111-8111-111111111111',
    'wpe_migrate_title',
);
fieldsMigrationExpect($scalarResult->changed, 'scalar storage-key migration must report changed');
fieldsMigrationExpect($scalarResult->migratedObjects === 2, 'scalar migration must report both migrated objects');
fieldsMigrationExpect(!metadata_exists('post', $scalarPostOne, 'wpe_migrate_headline'), 'scalar source key must be absent after migration');
fieldsMigrationExpect(get_post_meta($scalarPostOne, 'wpe_migrate_title', true) === 'Quote "A\\B"', 'scalar destination must preserve slash-sensitive canonical value');
fieldsMigrationExpect(get_post_meta($scalarPostTwo, 'wpe_migrate_title', true) === 'Second', 'second scalar destination must match source');
$scalarRegistered = get_registered_meta_keys('post', 'wpe_migrate_book');
fieldsMigrationExpect(!isset($scalarRegistered['wpe_migrate_headline']), 'scalar source registration must be retired');
fieldsMigrationExpect(isset($scalarRegistered['wpe_migrate_title']), 'scalar destination registration must exist');
$scalarPersisted = $repository->get($scalarDefinition->id);
fieldsMigrationExpect($scalarPersisted instanceof Definition && $scalarPersisted->revision === 4, 'scalar Field Group revision must advance after migration');
fieldsMigrationExpect(($scalarPersisted->payload['fields'][0]['uuid'] ?? null) === '72111111-1111-4111-8111-111111111111', 'scalar migration must preserve stable Field UUID');
fieldsMigrationExpect(($scalarPersisted->payload['fields'][0]['key'] ?? null) === 'wpe_migrate_title', 'scalar persisted Field key must advance to destination');

$arrayDefinition = fieldsMigrationDefinition(
    '73333333-3333-4333-8333-333333333333',
    'migration-array',
    [
        'uuid' => '74444444-4444-4444-8444-444444444444',
        'key' => 'wpe_migrate_aliases',
        'label' => 'Aliases',
        'type' => 'text',
        'cloneable' => true,
        'max_clones' => 10,
    ],
);
$repository->save($arrayDefinition);
fieldsMigrationRegisterSource($arrayDefinition);
$arrayPost = fieldsMigrationPost('Array migration');
update_post_meta($arrayPost, 'wpe_migrate_aliases', ['One', 'Two\\Three', 'One']);
$migrations->migrate($arrayDefinition->id, 3, '74444444-4444-4444-8444-444444444444', 'wpe_migrate_alt_names');
fieldsMigrationExpect(
    get_post_meta($arrayPost, 'wpe_migrate_alt_names', true) === ['One', 'Two\\Three', 'One'],
    'single-array migration must preserve order, duplicates, and slash-sensitive values',
);
fieldsMigrationExpect(!metadata_exists('post', $arrayPost, 'wpe_migrate_aliases'), 'single-array source must be retired');

$rowsDefinition = fieldsMigrationDefinition(
    '75555555-5555-4555-8555-555555555555',
    'migration-rows',
    [
        'uuid' => '76666666-6666-4666-8666-666666666666',
        'key' => 'wpe_migrate_rows',
        'label' => 'Rows',
        'type' => 'text',
        'cloneable' => true,
        'clone_as_multiple' => true,
        'max_clones' => 10,
    ],
);
$repository->save($rowsDefinition);
fieldsMigrationRegisterSource($rowsDefinition);
$rowsPost = fieldsMigrationPost('Multi-row migration');
add_post_meta($rowsPost, 'wpe_migrate_rows', 'One');
add_post_meta($rowsPost, 'wpe_migrate_rows', 'One');
add_post_meta($rowsPost, 'wpe_migrate_rows', 'Two');
$migrations->migrate($rowsDefinition->id, 3, '76666666-6666-4666-8666-666666666666', 'wpe_migrate_new_rows');
fieldsMigrationExpect(
    get_post_meta($rowsPost, 'wpe_migrate_new_rows', false) === ['One', 'One', 'Two'],
    'multi-row migration must preserve row order and duplicate multiplicity',
);
fieldsMigrationExpect(!metadata_exists('post', $rowsPost, 'wpe_migrate_rows'), 'multi-row source must be retired');

$collisionDefinition = fieldsMigrationDefinition(
    '77777777-7777-4777-8777-777777777777',
    'migration-collision',
    [
        'uuid' => '78888888-8888-4888-8888-888888888888',
        'key' => 'wpe_migrate_source',
        'label' => 'Source',
        'type' => 'text',
    ],
);
$repository->save($collisionDefinition);
fieldsMigrationRegisterSource($collisionDefinition);
$collisionPost = fieldsMigrationPost('Collision migration');
update_post_meta($collisionPost, 'wpe_migrate_source', 'Keep me');
register_post_meta('wpe_migrate_book', 'wpe_foreign_dest', [
    'type' => 'string',
    'single' => true,
    'description' => 'Foreign integration registration.',
    'show_in_rest' => false,
]);
try {
    $migrations->migrate($collisionDefinition->id, 3, '78888888-8888-4888-8888-888888888888', 'wpe_foreign_dest');
    fieldsMigrationExpect(false, 'foreign destination registration must block migration');
} catch (RuntimeException $exception) {
    fieldsMigrationExpect(str_contains($exception->getMessage(), 'already owned by another registration'), 'foreign destination failure must report ownership collision');
}
fieldsMigrationExpect(get_post_meta($collisionPost, 'wpe_migrate_source', true) === 'Keep me', 'foreign collision must preserve source data');
fieldsMigrationExpect(!metadata_exists('post', $collisionPost, 'wpe_foreign_dest'), 'foreign collision must not create destination data');

$rollbackDefinition = fieldsMigrationDefinition(
    '79999999-9999-4999-8999-999999999999',
    'migration-rollback',
    [
        'uuid' => '7aaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'key' => 'wpe_rollback_source',
        'label' => 'Rollback source',
        'type' => 'text',
    ],
);
$repository->save($rollbackDefinition);
fieldsMigrationRegisterSource($rollbackDefinition);
$rollbackPost = fieldsMigrationPost('Rollback migration');
update_post_meta($rollbackPost, 'wpe_rollback_source', 'Original');
$faultingMigrations = new FieldStorageKeyMigrationService(
    definitions: $repository,
    deletePostMeta: static function (int $postId, string $metaKey): bool {
        if ($metaKey === 'wpe_rollback_source') {
            return false;
        }
        return delete_post_meta($postId, $metaKey);
    },
);
try {
    $faultingMigrations->migrate(
        $rollbackDefinition->id,
        3,
        '7aaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'wpe_rollback_dest',
    );
    fieldsMigrationExpect(false, 'fault-injected source retirement must remain a failed migration');
} catch (RuntimeException $exception) {
    fieldsMigrationExpect(str_contains($exception->getMessage(), 'verified as restored'), 'recoverable failure must report verified rollback');
}
fieldsMigrationExpect(get_post_meta($rollbackPost, 'wpe_rollback_source', true) === 'Original', 'rollback must preserve original source value');
fieldsMigrationExpect(!metadata_exists('post', $rollbackPost, 'wpe_rollback_dest'), 'rollback must remove migration-owned destination value');
$rollbackRegistered = get_registered_meta_keys('post', 'wpe_migrate_book');
fieldsMigrationExpect(isset($rollbackRegistered['wpe_rollback_source']), 'rollback must retain source registration');
fieldsMigrationExpect(!isset($rollbackRegistered['wpe_rollback_dest']), 'rollback must retire migration-created destination registration');
fieldsMigrationExpect($repository->get($rollbackDefinition->id)?->revision === 3, 'rollback must leave Field Group definition revision unchanged');

fwrite(STDOUT, "WPEssential Fields storage-key migration integration PASS\n");
