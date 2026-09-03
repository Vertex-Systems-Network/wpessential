<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Query native execution reference SKIP (WordPress fixture unavailable)\n");
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
define('AUTH_KEY',         'wpessential-query-test-auth-key');
define('SECURE_AUTH_KEY',  'wpessential-query-test-secure-auth-key');
define('LOGGED_IN_KEY',    'wpessential-query-test-logged-in-key');
define('NONCE_KEY',        'wpessential-query-test-nonce-key');
define('AUTH_SALT',        'wpessential-query-test-auth-salt');
define('SECURE_AUTH_SALT', 'wpessential-query-test-secure-auth-salt');
define('LOGGED_IN_SALT',   'wpessential-query-test-logged-in-salt');
define('NONCE_SALT',       'wpessential-query-test-nonce-salt');
$table_prefix = 'wpequery_';
define('WP_DEBUG', false);
require_once ABSPATH . 'wp-settings.php';
PHP;
    if (file_put_contents($wpDir . '/wp-config.php', $config . "\n") === false) {
        fwrite(STDERR, "FAIL: unable to create WordPress Query reference configuration\n");
        exit(1);
    }
}

require $wpDir . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

$root = dirname(__DIR__, 2);
require_once $root . '/vendor/autoload.php';

use RuntimeException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Query\QueryAuthorizedExecutor;
use WPEssential\Modules\Query\QueryExecutionError;
use WPEssential\Modules\Query\QueryExecutionResult;
use WPEssential\Modules\Query\QueryModule;
use WPEssential\Modules\Query\QueryProviderExecutorInterface;
use WPEssential\Modules\Query\QueryProviderPlan;
use WPEssential\Modules\Query\QueryValidationBudget;
use WPEssential\Modules\Query\WordPressPostsQueryExecutor;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DataSources\DataSourceRegistry;

function queryReferenceExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @return list<string> */
function queryReferenceIssueCodes(object $validation): array
{
    return array_map(
        static fn ($issue): string => $issue->code,
        $validation->issues,
    );
}

/** @param list<string> $projection @param array<string,mixed>|null $filter @param list<array<string,string>> $orderBy */
function queryReferenceAst(array $projection, ?array $filter, array $orderBy, int $pageSize, int $offset): array
{
    return [
        'identity' => [
            'uuid' => '01990f6e-1f30-7000-8000-000000000150',
            'key' => 'posts.query-reference',
            'name' => 'Query native reference',
            'revision' => 1,
            'lifecycle' => 'draft',
        ],
        'ast_version' => 1,
        'source' => [
            'source_ref' => 'wordpress.posts',
            'source_type' => 'wordpress.posts',
            'capability_version' => 1,
        ],
        'operation' => 'select',
        'projection' => $projection,
        'parameters' => [],
        'filter' => $filter,
        'order_by' => $orderBy,
        'pagination' => [
            'mode' => 'offset',
            'page_size' => $pageSize,
            'offset' => $offset,
        ],
        'distinct' => false,
        'execution_policy' => [],
        'cache_policy' => [],
    ];
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Query Native Reference',
        'wpessential_query_admin',
        'query-admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    queryReferenceExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_query_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user('wpessential_query_admin', 'test-password-strong', 'query-admin@example.test');
    queryReferenceExpect(is_int($userId) && $userId > 0, 'reference administrator must be created');
    $admin = get_user_by('id', $userId);
}
queryReferenceExpect($admin instanceof WP_User, 'reference administrator must exist');
$admin->set_role('administrator');
wp_set_current_user($admin->ID);

$posts = [];
foreach ([
    ['Alpha One', 'publish'],
    ['Beta Two', 'publish'],
    ['Alpha Three', 'publish'],
    ['Alpha Draft', 'draft'],
] as [$title, $status]) {
    $id = wp_insert_post([
        'post_title' => $title,
        'post_name' => sanitize_title($title),
        'post_content' => 'Reference content for ' . $title,
        'post_excerpt' => 'Reference excerpt for ' . $title,
        'post_status' => $status,
        'post_type' => 'post',
        'post_author' => $admin->ID,
    ], true);
    queryReferenceExpect(is_int($id) && $id > 0, 'reference post must be created: ' . $title);
    $posts[$title] = $id;
}

$events = new class {
    /** @var list<string> */
    public array $items = [];
};
$checker = new class($events) implements CapabilityCheckerInterface {
    public function __construct(private object $events) {}

    public function can(ExecutionContext $context, string $capability): bool
    {
        $this->events->items[] = 'policy:' . $capability;
        return current_user_can($capability);
    }
};

$services = new ServiceRegistry();
$dataSources = new DataSourceRegistry();
$services->set('platform.data-sources', $dataSources);
$services->set('platform.abilities.policy', new PolicyEngine($checker));
(new QueryModule())->register($services);

$validator = $services->get(QueryModule::SERVICE_VALIDATOR);
$planner = $services->get(QueryModule::SERVICE_PLANNER);
queryReferenceExpect($validator instanceof WPEssential\Modules\Query\QueryAstValidator, 'Query validator service must register');
queryReferenceExpect($planner instanceof WPEssential\Modules\Query\QueryAuthorizedPlanner, 'Query authorized planner service must register');

$native = new WordPressPostsQueryExecutor();
$provider = new class($native, $events) implements QueryProviderExecutorInterface {
    public int $calls = 0;
    /** @var array<string,mixed> */
    public array $lastArguments = [];

    public function __construct(
        private WordPressPostsQueryExecutor $native,
        private object $events,
    ) {}

    public function supports(QueryProviderPlan $plan): bool
    {
        return $this->native->supports($plan);
    }

    public function execute(QueryProviderPlan $plan): QueryExecutionResult|QueryExecutionError
    {
        $this->events->items[] = 'provider';
        $this->calls++;
        $this->lastArguments = $plan->arguments;
        return $this->native->execute($plan);
    }
};
$executor = new QueryAuthorizedExecutor($planner, $provider);
$context = new ExecutionContext(new Principal($admin->ID), max(1, get_current_blog_id()));
$budget = new QueryValidationBudget(100000, 8, 100, 100, 100, 1);

$fullAst = queryReferenceAst(
    ['post.id', 'post.title', 'post.slug', 'post.status', 'post.author_id'],
    ['type' => 'comparison', 'field_ref' => 'post.status', 'operator' => 'eq', 'value' => 'publish'],
    [['field_ref' => 'post.id', 'direction' => 'asc']],
    2,
    1,
);
$fullValidation = $validator->validate($fullAst, $budget, $context);
queryReferenceExpect($fullValidation->isValid() && $fullValidation->definition !== null, 'bounded full-row AST must validate');
$events->items = [];
$fullResult = $executor->execute($fullValidation->definition, $context);
queryReferenceExpect($fullResult instanceof QueryExecutionResult, 'bounded full-row Query must execute successfully');
queryReferenceExpect($events->items === ['policy:read', 'provider'], 'Policy authorization must occur before provider execution');
queryReferenceExpect($provider->calls === 1, 'one Query execution must invoke the provider exactly once');
queryReferenceExpect(($provider->lastArguments['posts_per_page'] ?? null) === 2, 'provider page size must match bounded validated AST');
queryReferenceExpect(($provider->lastArguments['offset'] ?? null) === 1, 'provider offset must match bounded validated AST');
queryReferenceExpect(($provider->lastArguments['ignore_sticky_posts'] ?? null) === true, 'sticky posts must be disabled for deterministic execution');
queryReferenceExpect(($provider->lastArguments['suppress_filters'] ?? null) === true, 'provider filters must be suppressed for deterministic execution');
queryReferenceExpect($fullResult->returned === 2, 'full-row offset query must return exactly two rows');
$fullIds = array_column($fullResult->rows, 'post.id');
$publishedIds = [$posts['Alpha One'], $posts['Beta Two'], $posts['Alpha Three']];
sort($publishedIds, SORT_NUMERIC);
queryReferenceExpect($fullIds === array_slice($publishedIds, 1, 2), 'real WP_Query offset/order semantics must match certified provider plan');
foreach ($fullResult->rows as $row) {
    queryReferenceExpect(array_keys($row) === $fullResult->projection, 'normalized full rows must contain projection keys only');
    queryReferenceExpect(($row['post.status'] ?? null) === 'publish', 'status filter must remain exact in normalized rows');
    queryReferenceExpect(($row['post.author_id'] ?? null) === $admin->ID, 'author projection must normalize the real WordPress author id');
}

$searchAst = queryReferenceAst(
    ['post.id'],
    ['type' => 'text', 'field_ref' => null, 'search_scope' => 'posts.default', 'mode' => 'contains', 'value' => 'Alpha'],
    [['field_ref' => 'post.id', 'direction' => 'asc']],
    100,
    0,
);
$searchValidation = $validator->validate($searchAst, $budget, $context);
queryReferenceExpect($searchValidation->isValid() && $searchValidation->definition !== null, 'ID-only search AST at max page size must validate');
$events->items = [];
$beforeCalls = $provider->calls;
$searchResult = $executor->execute($searchValidation->definition, $context);
queryReferenceExpect($searchResult instanceof QueryExecutionResult, 'ID-only real WordPress search must execute successfully');
queryReferenceExpect($events->items === ['policy:read', 'provider'], 'Policy must still precede provider on ID-only search');
queryReferenceExpect($provider->calls === $beforeCalls + 1, 'ID-only search must invoke provider once');
queryReferenceExpect(($provider->lastArguments['posts_per_page'] ?? null) === 100, 'maximum certified page size must compile exactly to 100');
queryReferenceExpect(($provider->lastArguments['fields'] ?? null) === 'ids', 'ID-only projection must use native WP_Query ids mode');
$expectedSearchIds = [$posts['Alpha One'], $posts['Alpha Three']];
sort($expectedSearchIds, SORT_NUMERIC);
$actualSearchIds = array_map(static fn (array $row): int => (int) $row['post.id'], $searchResult->rows);
queryReferenceExpect($actualSearchIds === $expectedSearchIds, 'real WordPress search must exclude the draft and preserve deterministic ID ordering');
queryReferenceExpect($searchResult->returned === 2, 'ID-only search returned count must equal normalized row count');
queryReferenceExpect(!property_exists($searchResult, 'total'), 'deferred total-count semantics must remain absent from execution V1');

$oversizedAst = $searchAst;
$oversizedAst['pagination']['page_size'] = 101;
$oversizedValidation = $validator->validate($oversizedAst, $budget, $context);
queryReferenceExpect(!$oversizedValidation->isValid(), 'page size above 100 must fail validation before provider planning');
queryReferenceExpect(in_array('wpe_query_cost_blocked', queryReferenceIssueCodes($oversizedValidation), true), 'page size above 100 must use stable cost-blocked taxonomy');
$beforeOversizedCalls = $provider->calls;
queryReferenceExpect($oversizedValidation->definition === null, 'oversized AST must not produce an executable definition');
queryReferenceExpect($provider->calls === $beforeOversizedCalls, 'oversized AST must not reach the provider');

$throwingProvider = new class($events) implements QueryProviderExecutorInterface {
    public function __construct(private object $events) {}

    public function supports(QueryProviderPlan $plan): bool
    {
        return true;
    }

    public function execute(QueryProviderPlan $plan): QueryExecutionResult|QueryExecutionError
    {
        $this->events->items[] = 'provider';
        throw new RuntimeException('SELECT secret_column FROM wp_posts -- provider internals');
    }
};
$failureExecutor = new QueryAuthorizedExecutor($planner, $throwingProvider);
$events->items = [];
$failure = $failureExecutor->execute($searchValidation->definition, $context);
queryReferenceExpect($failure instanceof QueryExecutionError, 'provider exception must normalize to QueryExecutionError');
queryReferenceExpect($events->items === ['policy:read', 'provider'], 'Policy must precede even a failing provider');
queryReferenceExpect($failure->errorCode === 'wpe_query_provider_failed', 'provider exception must use stable provider-failed taxonomy');
queryReferenceExpect(!str_contains($failure->message, 'SELECT') && !str_contains($failure->message, 'secret_column'), 'provider diagnostics must not leak through normalized error');

fwrite(STDOUT, "WPEssential Query native execution real-WordPress reference PASS\n");
