<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Admin Columns read performance SKIP (WordPress fixture unavailable)\n");
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
define('AUTH_KEY',         'wpessential-columns-performance-auth-key');
define('SECURE_AUTH_KEY',  'wpessential-columns-performance-secure-auth-key');
define('LOGGED_IN_KEY',    'wpessential-columns-performance-logged-in-key');
define('NONCE_KEY',        'wpessential-columns-performance-nonce-key');
define('AUTH_SALT',        'wpessential-columns-performance-auth-salt');
define('SECURE_AUTH_SALT', 'wpessential-columns-performance-secure-auth-salt');
define('LOGGED_IN_SALT',   'wpessential-columns-performance-logged-in-salt');
define('NONCE_SALT',       'wpessential-columns-performance-nonce-salt');
$table_prefix = 'wpequery_';
define('WP_DEBUG', false);
require_once ABSPATH . 'wp-settings.php';
PHP;
    if (file_put_contents($wpDir . '/wp-config.php', $config . "\n") === false) {
        fwrite(STDERR, "FAIL: unable to create WordPress Admin Columns performance configuration\n");
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
use WPEssential\Modules\Query\QueryModule;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DataSources\DataSourceRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

function columnsPerformanceExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Admin Columns Performance',
        'wpessential_columns_perf_admin',
        'columns-perf-admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    columnsPerformanceExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_columns_perf_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user(
        'wpessential_columns_perf_admin',
        'test-password-strong',
        'columns-perf-admin@example.test',
    );
    columnsPerformanceExpect(is_int($userId) && $userId > 0, 'performance administrator must be created');
    $admin = get_user_by('id', $userId);
}
columnsPerformanceExpect($admin instanceof WP_User, 'performance administrator must exist');
$admin->set_role('administrator');
wp_set_current_user($admin->ID);

for ($index = 1; $index <= 60; $index++) {
    $id = wp_insert_post([
        'post_title' => sprintf('WPE Admin Columns Performance %03d', $index),
        'post_name' => sprintf('wpe-admin-columns-performance-%03d', $index),
        'post_content' => 'Bounded Admin Columns performance reference row ' . $index,
        'post_excerpt' => 'Performance row ' . $index,
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_author' => $admin->ID,
    ], true);
    columnsPerformanceExpect(is_int($id) && $id > 0, 'performance reference post must be created');
}

$checker = new class implements CapabilityCheckerInterface {
    public int $calls = 0;

    public function can(ExecutionContext $context, string $capability): bool
    {
        $this->calls++;
        return current_user_can($capability);
    }
};

$services = new ServiceRegistry();
$services->set('platform.data-sources', new DataSourceRegistry());
$services->set('platform.abilities.policy', new PolicyEngine($checker));
(new QueryModule())->register($services);
$query = $services->get(QueryModule::SERVICE_READ_CONSUMER);
columnsPerformanceExpect(
    $query instanceof QueryReadConsumerInterface,
    'canonical Query read consumer must register',
);

$repository = new class implements DefinitionRepositoryInterface {
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
    $repository,
    new AdminColumnsViewDefinitionNormalizer(),
    static fn (): string => '01990f6e-1f30-4000-8000-000000000460',
);
$view = $views->save([
    'view_key' => 'posts_performance_reference',
    'name' => 'Posts performance reference',
    'enabled' => true,
    'target' => ['type' => 'post_type', 'key' => 'post'],
    'columns' => [
        [
            'uuid' => '01990f6e-1f30-4000-8000-000000000461',
            'key' => 'id',
            'label' => 'ID',
            'source' => ['owner' => 'native', 'reference' => 'post.id'],
            'format' => 'number',
            'primary' => true,
        ],
        [
            'uuid' => '01990f6e-1f30-4000-8000-000000000462',
            'key' => 'title',
            'label' => 'Title',
            'source' => ['owner' => 'native', 'reference' => 'post.title'],
            'format' => 'text',
            'primary' => false,
        ],
        [
            'uuid' => '01990f6e-1f30-4000-8000-000000000463',
            'key' => 'status',
            'label' => 'Status',
            'source' => ['owner' => 'native', 'reference' => 'post.status'],
            'format' => 'text',
            'primary' => false,
        ],
    ],
], DefinitionStatus::Published);

$adapter = new AdminColumnsReadAdapter($views, $query);
$context = new ExecutionContext(new Principal($admin->ID), max(1, get_current_blog_id()));

global $wpdb;
columnsPerformanceExpect($wpdb instanceof wpdb, 'WordPress database adapter must be available');

wp_cache_flush();
$smallBefore = (int) $wpdb->num_queries;
$small = $adapter->read($view->id, [
    'page_size' => 5,
    'offset' => 0,
    'order_by' => [['column_key' => 'id', 'direction' => 'asc']],
], $context);
$smallQueries = (int) $wpdb->num_queries - $smallBefore;
columnsPerformanceExpect(($small['ok'] ?? false) === true, 'small bounded Admin Columns read must succeed');
columnsPerformanceExpect(($small['returned'] ?? null) === 5, 'small bounded read must return exactly five rows');
columnsPerformanceExpect($smallQueries >= 1, 'small real WordPress read must execute database work');

wp_cache_flush();
$largeBefore = (int) $wpdb->num_queries;
$large = $adapter->read($view->id, [
    'page_size' => 50,
    'offset' => 0,
    'order_by' => [['column_key' => 'id', 'direction' => 'asc']],
], $context);
$largeQueries = (int) $wpdb->num_queries - $largeBefore;
columnsPerformanceExpect(($large['ok'] ?? false) === true, 'large bounded Admin Columns read must succeed');
columnsPerformanceExpect(($large['returned'] ?? null) === 50, 'large bounded read must return exactly fifty rows');
columnsPerformanceExpect(count($large['rows'] ?? []) === 50, 'large bounded row count must match returned count');
columnsPerformanceExpect(
    $largeQueries <= $smallQueries + 4,
    sprintf(
        'SQL query count must not grow with rows (small=%d large=%d)',
        $smallQueries,
        $largeQueries,
    ),
);
columnsPerformanceExpect(
    $largeQueries <= 12,
    sprintf('large bounded read SQL query count exceeded sanity ceiling: %d', $largeQueries),
);

foreach ($large['rows'] as $row) {
    columnsPerformanceExpect(
        is_array($row) && array_keys($row) === ['id', 'title', 'status'],
        'large read rows must contain only the canonical projected Admin Columns keys',
    );
    columnsPerformanceExpect(($row['status'] ?? null) === 'publish', 'large read must preserve published status projection');
}

columnsPerformanceExpect(
    $checker->calls === 2,
    sprintf('two adapter reads must authorize exactly twice, got %d policy calls', $checker->calls),
);

fwrite(
    STDOUT,
    sprintf(
        "WPEssential Admin Columns no-N+1 real-WordPress reference PASS (small=%d SQL queries, large=%d SQL queries)\n",
        $smallQueries,
        $largeQueries,
    ),
);
