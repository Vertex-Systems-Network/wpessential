<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Admin Columns Fields performance SKIP (WordPress fixture unavailable)\n");
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
define('AUTH_KEY',         'wpessential-columns-fields-auth-key');
define('SECURE_AUTH_KEY',  'wpessential-columns-fields-secure-auth-key');
define('LOGGED_IN_KEY',    'wpessential-columns-fields-logged-in-key');
define('NONCE_KEY',        'wpessential-columns-fields-nonce-key');
define('AUTH_SALT',        'wpessential-columns-fields-auth-salt');
define('SECURE_AUTH_SALT', 'wpessential-columns-fields-secure-auth-salt');
define('LOGGED_IN_SALT',   'wpessential-columns-fields-logged-in-salt');
define('NONCE_SALT',       'wpessential-columns-fields-nonce-salt');
$table_prefix = 'wpequery_';
define('WP_DEBUG', false);
require_once ABSPATH . 'wp-settings.php';
PHP;
    if (file_put_contents($wpDir . '/wp-config.php', $config . "\n") === false) {
        fwrite(STDERR, "FAIL: unable to create WordPress Admin Columns Fields configuration\n");
        exit(1);
    }
}

require $wpDir . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

$root = dirname(__DIR__, 2);
require_once $root . '/vendor/autoload.php';

use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\AdminColumns\AdminColumnsReadAdapter;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionService;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupRuntimeStorageProjection;
use WPEssential\Modules\Fields\FieldQueryConsumer;
use WPEssential\Modules\Fields\FieldValueNormalizer;
use WPEssential\Modules\Fields\FieldValueReadConsumer;
use WPEssential\Modules\Fields\FieldValueTargetResolver;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\PostMetaValueStore;
use WPEssential\Modules\Fields\WordPressPostResourceAuthorizer;
use WPEssential\Modules\Query\QueryModule;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DataSources\DataSourceRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

function columnsFieldsExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Admin Columns Fields Performance',
        'wpessential_columns_fields_admin',
        'columns-fields-admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    columnsFieldsExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_columns_fields_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user(
        'wpessential_columns_fields_admin',
        'test-password-strong',
        'columns-fields-admin@example.test',
    );
    columnsFieldsExpect(is_int($userId) && $userId > 0, 'Fields composition administrator must be created');
    $admin = get_user_by('id', $userId);
}
columnsFieldsExpect($admin instanceof WP_User, 'Fields composition administrator must exist');
$admin->set_role('administrator');
wp_set_current_user($admin->ID);

$groupId = '11111111-1111-4111-8111-111111111291';
$fieldId = '22222222-2222-4222-8222-222222222291';
$fieldRef = 'fields.' . $groupId . '.' . $fieldId;
$groups = new FieldGroupDefinitionNormalizer();
$fieldPayload = $groups->normalize([
    'group_key' => 'admin_columns_fields_reference',
    'title' => 'Admin Columns Fields Reference',
    'fields' => [[
        'uuid' => $fieldId,
        'key' => 'reference_headline',
        'label' => 'Reference headline',
        'type' => 'text',
    ]],
    'locations' => [[[
        'source' => 'post_type',
        'operator' => 'equals',
        'value' => 'post',
    ]]],
    'storage' => ['mode' => 'native_post_meta'],
], true);

$fieldRepository = new InMemoryDefinitionRepository();
$fieldRepository->save(new Definition(
    id: $groupId,
    slug: 'field-group-admin-columns-fields-reference',
    type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
    schemaVersion: 1,
    ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
    status: DefinitionStatus::Published,
    payload: $fieldPayload,
    revision: 9,
));

$field = $fieldPayload['fields'][0] ?? null;
columnsFieldsExpect(is_array($field), 'normalized Field definition must exist');
$normalizer = new FieldValueNormalizer();
$compiler = new PostMetaRegistrationCompiler($normalizer);
$registration = $compiler->compile($field, 'post');
$metaKey = $registration['meta_key'] ?? null;
columnsFieldsExpect(is_string($metaKey) && $metaKey !== '', 'compiled Field meta key must exist');

$postIds = [];
for ($index = 1; $index <= 60; $index++) {
    $id = wp_insert_post([
        'post_title' => sprintf('WPE Columns Fields %03d', $index),
        'post_name' => sprintf('wpe-columns-fields-%03d', $index),
        'post_content' => 'Admin Columns Fields composition reference ' . $index,
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_author' => $admin->ID,
    ], true);
    columnsFieldsExpect(is_int($id) && $id > 0, 'Fields composition reference post must be created');
    columnsFieldsExpect(
        update_post_meta($id, $metaKey, sprintf('owner-value-%03d', $index)) !== false,
        'Fields composition reference meta must persist',
    );
    $postIds[] = $id;
}

$targets = new FieldValueTargetResolver($fieldRepository, $groups);
$store = new PostMetaValueStore($compiler, $normalizer);
$authorization = new WordPressPostResourceAuthorizer();
$fieldQuery = new FieldQueryConsumer(
    $fieldRepository,
    $groups,
    new FieldGroupRuntimeStorageProjection(),
    $targets,
    $compiler,
    $store,
    $normalizer,
    $authorization,
);
$fieldReads = new FieldValueReadConsumer($fieldQuery, $targets, $store, $authorization);

$checker = new class implements CapabilityCheckerInterface {
    public int $calls = 0;

    public function can(ExecutionContext $context, string $capability): bool
    {
        ++$this->calls;
        return current_user_can($capability);
    }
};
$services = new ServiceRegistry();
$services->set('platform.data-sources', new DataSourceRegistry());
$services->set('platform.abilities.policy', new PolicyEngine($checker));
(new QueryModule())->register($services);
$query = $services->get(QueryModule::SERVICE_READ_CONSUMER);
columnsFieldsExpect($query instanceof QueryReadConsumerInterface, 'canonical Query read consumer must register');

$viewRepository = new class implements DefinitionRepositoryInterface {
    /** @var array<string,Definition> */
    private array $definitions = [];

    public function save(Definition $definition): void
    {
        $this->definitions[$definition->id] = $definition;
    }

    public function get(string $id): ?Definition
    {
        return $this->definitions[$id] ?? null;
    }

    public function byType(string $type): array
    {
        return array_values(array_filter(
            $this->definitions,
            static fn (Definition $definition): bool => $definition->type === $type,
        ));
    }

    public function dependentsOf(string $id): array
    {
        return [];
    }
};
$views = new AdminColumnsViewDefinitionService(
    $viewRepository,
    new AdminColumnsViewDefinitionNormalizer(),
    static fn (): string => '01990f6e-1f30-4000-8000-000000000560',
);
$view = $views->save([
    'view_key' => 'posts_fields_performance_reference',
    'name' => 'Posts Fields performance reference',
    'enabled' => true,
    'target' => ['type' => 'post_type', 'key' => 'post'],
    'columns' => [
        [
            'uuid' => '01990f6e-1f30-4000-8000-000000000561',
            'key' => 'title',
            'label' => 'Title',
            'source' => ['owner' => 'native', 'reference' => 'post.title'],
            'format' => 'text',
            'primary' => true,
        ],
        [
            'uuid' => '01990f6e-1f30-4000-8000-000000000562',
            'key' => 'headline',
            'label' => 'Headline',
            'source' => ['owner' => 'fields', 'reference' => $fieldRef],
            'format' => 'text',
        ],
    ],
], DefinitionStatus::Published);

$adapter = new AdminColumnsReadAdapter($views, $query, $fieldReads);
$context = new ExecutionContext(
    new Principal($admin->ID),
    max(1, get_current_blog_id()),
    networkId: function_exists('get_current_network_id') ? get_current_network_id() : null,
);

global $wpdb;
columnsFieldsExpect($wpdb instanceof wpdb, 'WordPress database adapter must be available');

wp_cache_flush();
$smallBefore = (int) $wpdb->num_queries;
$small = $adapter->read($view->id, [
    'search' => 'WPE Columns Fields',
    'page_size' => 5,
    'offset' => 0,
    'order_by' => [['column_key' => 'title', 'direction' => 'asc']],
], $context);
$smallQueries = (int) $wpdb->num_queries - $smallBefore;
columnsFieldsExpect(($small['ok'] ?? false) === true, 'small mixed Admin Columns read must succeed');
columnsFieldsExpect(($small['returned'] ?? null) === 5, 'small mixed read must return exactly five rows');
columnsFieldsExpect($smallQueries >= 1, 'small mixed read must execute real database work');

wp_cache_flush();
$largeBefore = (int) $wpdb->num_queries;
$large = $adapter->read($view->id, [
    'search' => 'WPE Columns Fields',
    'page_size' => 50,
    'offset' => 0,
    'order_by' => [['column_key' => 'title', 'direction' => 'asc']],
], $context);
$largeQueries = (int) $wpdb->num_queries - $largeBefore;
columnsFieldsExpect(($large['ok'] ?? false) === true, 'large mixed Admin Columns read must succeed');
columnsFieldsExpect(($large['returned'] ?? null) === 50, 'large mixed read must return exactly fifty rows');
columnsFieldsExpect(count($large['rows'] ?? []) === 50, 'large mixed row count must match returned count');
columnsFieldsExpect(
    $largeQueries <= $smallQueries + 4,
    sprintf('mixed Fields SQL count must remain bounded (small=%d large=%d)', $smallQueries, $largeQueries),
);
columnsFieldsExpect(
    $largeQueries <= 18,
    sprintf('large mixed Fields SQL count exceeded sanity ceiling: %d', $largeQueries),
);

foreach ($large['rows'] as $row) {
    columnsFieldsExpect(
        is_array($row) && array_keys($row) === ['title', 'headline'],
        'mixed row must expose only View column keys; internal post.id must not leak',
    );
    columnsFieldsExpect(
        is_string($row['headline'] ?? null) && str_starts_with($row['headline'], 'owner-value-'),
        'mixed row must contain the owner-certified Field value',
    );
}
columnsFieldsExpect(
    $checker->calls === 2,
    sprintf('two mixed adapter reads must authorize Query exactly twice, got %d', $checker->calls),
);

fwrite(
    STDOUT,
    sprintf(
        "WPEssential Admin Columns Fields no-N+1 reference PASS (small=%d SQL queries, large=%d SQL queries)\n",
        $smallQueries,
        $largeQueries,
    ),
);
