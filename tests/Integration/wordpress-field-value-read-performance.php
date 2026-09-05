<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Field value read performance SKIP (WordPress fixture unavailable)\n");
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
define('AUTH_KEY',         'wpessential-field-value-read-auth-key');
define('SECURE_AUTH_KEY',  'wpessential-field-value-read-secure-auth-key');
define('LOGGED_IN_KEY',    'wpessential-field-value-read-logged-in-key');
define('NONCE_KEY',        'wpessential-field-value-read-nonce-key');
define('AUTH_SALT',        'wpessential-field-value-read-auth-salt');
define('SECURE_AUTH_SALT', 'wpessential-field-value-read-secure-auth-salt');
define('LOGGED_IN_SALT',   'wpessential-field-value-read-logged-in-salt');
define('NONCE_SALT',       'wpessential-field-value-read-nonce-salt');
$table_prefix = 'wpequery_';
define('WP_DEBUG', false);
require_once ABSPATH . 'wp-settings.php';
PHP;
    if (file_put_contents($wpDir . '/wp-config.php', $config . "\n") === false) {
        fwrite(STDERR, "FAIL: unable to create WordPress Field value read configuration\n");
        exit(1);
    }
}

require $wpDir . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

$root = dirname(__DIR__, 2);
require_once $root . '/vendor/autoload.php';

use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupRuntimeStorageProjection;
use WPEssential\Modules\Fields\FieldQueryConsumer;
use WPEssential\Modules\Fields\FieldValueNormalizer;
use WPEssential\Modules\Fields\FieldValueReadConsumer;
use WPEssential\Modules\Fields\FieldValueTargetResolver;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\PostMetaValueStore;
use WPEssential\Modules\Fields\WordPressPostResourceAuthorizer;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

function fieldValueReadExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Field Value Read Performance',
        'wpessential_field_read_admin',
        'field-read-admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    fieldValueReadExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_field_read_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user(
        'wpessential_field_read_admin',
        'test-password-strong',
        'field-read-admin@example.test',
    );
    fieldValueReadExpect(is_int($userId) && $userId > 0, 'Field value read administrator must be created');
    $admin = get_user_by('id', $userId);
}
fieldValueReadExpect($admin instanceof WP_User, 'Field value read administrator must exist');
$admin->set_role('administrator');
wp_set_current_user($admin->ID);

$groupId = '11111111-1111-4111-8111-111111111191';
$fieldId = '22222222-2222-4222-8222-222222222191';
$fieldRef = 'fields.' . $groupId . '.' . $fieldId;

$groups = new FieldGroupDefinitionNormalizer();
$payload = $groups->normalize([
    'group_key' => 'field_value_read_reference',
    'title' => 'Field Value Read Reference',
    'fields' => [[
        'uuid' => $fieldId,
        'key' => 'reference_label',
        'label' => 'Reference label',
        'type' => 'text',
    ]],
    'locations' => [[[
        'source' => 'post_type',
        'operator' => 'equals',
        'value' => 'post',
    ]]],
    'storage' => ['mode' => 'native_post_meta'],
], true);

$repository = new InMemoryDefinitionRepository();
$repository->save(new Definition(
    id: $groupId,
    slug: 'field-group-field-value-read-reference',
    type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
    schemaVersion: 1,
    ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
    status: DefinitionStatus::Published,
    payload: $payload,
    revision: 7,
));

$field = $payload['fields'][0] ?? null;
fieldValueReadExpect(is_array($field), 'normalized Field definition must exist');
$normalizer = new FieldValueNormalizer();
$compiler = new PostMetaRegistrationCompiler($normalizer);
$registration = $compiler->compile($field, 'post');
$metaKey = $registration['meta_key'] ?? null;
fieldValueReadExpect(is_string($metaKey) && $metaKey !== '', 'compiled Field meta key must exist');

$postIds = [];
for ($index = 1; $index <= 60; $index++) {
    $id = wp_insert_post([
        'post_title' => sprintf('WPE Field Value Read %03d', $index),
        'post_name' => sprintf('wpe-field-value-read-%03d', $index),
        'post_content' => 'Bounded Field value read performance reference row ' . $index,
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_author' => $admin->ID,
    ], true);
    fieldValueReadExpect(is_int($id) && $id > 0, 'Field value read reference post must be created');
    fieldValueReadExpect(
        update_post_meta($id, $metaKey, sprintf('field-value-%03d', $index)) !== false,
        'Field value read reference meta must be persisted',
    );
    $postIds[] = $id;
}

$targets = new FieldValueTargetResolver($repository, $groups);
$store = new PostMetaValueStore($compiler, $normalizer);
$authorization = new WordPressPostResourceAuthorizer();
$query = new FieldQueryConsumer(
    $repository,
    $groups,
    new FieldGroupRuntimeStorageProjection(),
    $targets,
    $compiler,
    $store,
    $normalizer,
    $authorization,
);
$consumer = new FieldValueReadConsumer($query, $targets, $store, $authorization);
$context = new ExecutionContext(
    new Principal($admin->ID),
    max(1, get_current_blog_id()),
    networkId: function_exists('get_current_network_id') ? get_current_network_id() : null,
);

global $wpdb;
fieldValueReadExpect($wpdb instanceof wpdb, 'WordPress database adapter must be available');

$smallIds = array_slice($postIds, 0, 5);
wp_cache_flush();
$smallBefore = (int) $wpdb->num_queries;
$small = $consumer->readValues($fieldRef, $smallIds, $context);
$smallQueries = (int) $wpdb->num_queries - $smallBefore;
fieldValueReadExpect(count($small['rows'] ?? []) === 5, 'small bounded Field value read must return five rows');
fieldValueReadExpect($smallQueries >= 1, 'small Field value read must execute real database work after cache flush');

$largeIds = array_slice($postIds, 0, 50);
wp_cache_flush();
$largeBefore = (int) $wpdb->num_queries;
$large = $consumer->readValues($fieldRef, $largeIds, $context);
$largeQueries = (int) $wpdb->num_queries - $largeBefore;
fieldValueReadExpect(count($large['rows'] ?? []) === 50, 'large bounded Field value read must return fifty rows');
fieldValueReadExpect(($large['group_revision'] ?? null) === 7, 'owner group revision must be preserved');
fieldValueReadExpect(($large['field_uuid'] ?? null) === $fieldId, 'owner Field UUID must be preserved');
fieldValueReadExpect(($large['logical_type'] ?? null) === 'string', 'owner logical type must be preserved');
fieldValueReadExpect(($large['storage_owner'] ?? null) === 'native_post_meta', 'owner storage identity must be preserved');

foreach ($large['rows'] as $offset => $row) {
    fieldValueReadExpect(
        is_array($row) && ($row['post_id'] ?? null) === $largeIds[$offset],
        'Field value read rows must preserve requested post-id order',
    );
    fieldValueReadExpect(
        ($row['value'] ?? null) === sprintf('field-value-%03d', $offset + 1),
        'Field value read row must preserve canonical stored value',
    );
}

fieldValueReadExpect(
    $largeQueries <= $smallQueries + 4,
    sprintf(
        'Field value SQL query count must remain bounded as rows grow (small=%d large=%d)',
        $smallQueries,
        $largeQueries,
    ),
);
fieldValueReadExpect(
    $largeQueries <= 12,
    sprintf('large bounded Field value read SQL query count exceeded sanity ceiling: %d', $largeQueries),
);

fwrite(
    STDOUT,
    sprintf(
        "WPEssential Field value read no-N+1 real-WordPress reference PASS (small=%d SQL queries, large=%d SQL queries)\n",
        $smallQueries,
        $largeQueries,
    ),
);
