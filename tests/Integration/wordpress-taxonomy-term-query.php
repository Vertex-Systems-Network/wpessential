<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}
if (!function_exists('get_bloginfo')) {
    fwrite(STDERR, "FAIL: WordPress must be loaded before the Taxonomy term-query integration test.\n");
    exit(1);
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Registrations\RegistrationCompilationStatus;

function taxonomyTermQueryExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @param array<string,mixed> $payload */
function taxonomyTermQueryDefinition(
    string $id,
    DefinitionStatus $status,
    array $payload,
    int $revision = 1,
): Definition {
    return new Definition(
        id: $id,
        slug: 'library-order-genre-definition',
        type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
        schemaVersion: 1,
        ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
        status: $status,
        payload: $payload,
        revision: $revision,
    );
}

/**
 * Run the native object-term read from a cold relationship cache while capturing only SQL emitted by the read itself.
 *
 * @return array{terms:list<int>,queries:list<string>}
 */
function taxonomyTermQueryCapture(int $postId, string $taxonomy): array
{
    clean_object_term_cache($postId, 'post');
    wp_cache_delete($postId, $taxonomy . '_relationships');

    $queries = [];
    $capture = static function (string $query) use (&$queries): string {
        $queries[] = $query;
        return $query;
    };

    add_filter('query', $capture);
    try {
        $terms = wp_get_object_terms($postId, $taxonomy);
    } finally {
        remove_filter('query', $capture);
    }

    taxonomyTermQueryExpect(!is_wp_error($terms) && is_array($terms), 'captured native object-term query must succeed');

    return [
        'terms' => array_map('intval', $terms),
        'queries' => $queries,
    ];
}

$kernel = \WPEssential\Bootstrap\Plugin::kernel();
taxonomyTermQueryExpect($kernel instanceof Kernel && $kernel->isBooted(), 'production plugin kernel must be booted');
$services = $kernel->services();
$definitions = $services->get('platform.definitions');
$status = $services->get('platform.registrations.compilation-status');
taxonomyTermQueryExpect($definitions instanceof DefinitionRepositoryInterface, 'shared Definition Repository must be available');
taxonomyTermQueryExpect($status instanceof RegistrationCompilationStatus, 'registration compilation status must be available');

$definitionId = '66666666-6666-4666-8666-666666666666';
$taxonomy = 'library_order_genre';
$payload = [
    'taxonomy_key' => $taxonomy,
    'object_types' => ['post'],
    'name' => 'Library Order Genres',
    'singular_name' => 'Library Order Genre',
    'public' => true,
    'show_in_rest' => true,
    'hierarchical' => false,
    'rewrite' => false,
    'sort' => true,
    'args' => [
        'orderby' => 'term_order',
        'order' => 'ASC',
        'fields' => 'ids',
    ],
];
$mode = getenv('WPE_TAXONOMY_TERM_QUERY_MODE') ?: '';

if ($mode === 'seed') {
    taxonomyTermQueryExpect($definitions->get($definitionId) === null, 'term-query fixture must start absent');
    taxonomyTermQueryExpect(!taxonomy_exists($taxonomy), 'term-query taxonomy must not be registered before its published Definition is compiled');

    $definitions->save(taxonomyTermQueryDefinition(
        $definitionId,
        DefinitionStatus::Published,
        $payload,
    ));

    fwrite(STDOUT, "Taxonomy term-query seed PASS\n");
    return;
}

if ($mode === 'verify') {
    global $wpdb;

    taxonomyTermQueryExpect($status->passed(), 'published term-query Taxonomy definition compilation must pass');
    taxonomyTermQueryExpect(taxonomy_exists($taxonomy), 'published term-query Taxonomy must register on the next real WordPress request');

    $taxonomyObject = get_taxonomy($taxonomy);
    taxonomyTermQueryExpect($taxonomyObject instanceof WP_Taxonomy, 'registered term-query Taxonomy object must be available');
    taxonomyTermQueryExpect($taxonomyObject->sort === true, 'native Taxonomy object must retain sort=true');
    taxonomyTermQueryExpect(is_array($taxonomyObject->args), 'native Taxonomy object must retain bounded object-term query args');
    taxonomyTermQueryExpect(($taxonomyObject->args['orderby'] ?? null) === 'term_order', 'bounded args.orderby must survive projection');
    taxonomyTermQueryExpect(($taxonomyObject->args['order'] ?? null) === 'ASC', 'bounded args.order must survive projection');
    taxonomyTermQueryExpect(($taxonomyObject->args['fields'] ?? null) === 'ids', 'bounded args.fields must survive projection');

    $admin = get_user_by('login', 'wpessential_admin');
    taxonomyTermQueryExpect($admin instanceof WP_User, 'WordPress fixture must provide the administrator user');
    wp_set_current_user((int) $admin->ID);

    $postId = wp_insert_post([
        'post_type' => 'post',
        'post_status' => 'draft',
        'post_title' => 'Taxonomy Term Query Probe',
    ], true);
    taxonomyTermQueryExpect(!is_wp_error($postId) && is_int($postId) && $postId > 0, 'term-query probe post must be created');

    $alpha = wp_insert_term('Alpha Genre', $taxonomy, ['slug' => 'alpha-genre']);
    $beta = wp_insert_term('Beta Genre', $taxonomy, ['slug' => 'beta-genre']);
    $gamma = wp_insert_term('Gamma Genre', $taxonomy, ['slug' => 'gamma-genre']);
    taxonomyTermQueryExpect(!is_wp_error($alpha) && !is_wp_error($beta) && !is_wp_error($gamma), 'term-query probe terms must be created');

    $alphaId = (int) $alpha['term_id'];
    $betaId = (int) $beta['term_id'];
    $gammaId = (int) $gamma['term_id'];
    $expectedOrder = [$gammaId, $alphaId, $betaId];

    $setTerms = wp_set_object_terms($postId, $expectedOrder, $taxonomy, false);
    taxonomyTermQueryExpect(!is_wp_error($setTerms) && is_array($setTerms), 'ordered term assignment must succeed through native WordPress');
    taxonomyTermQueryExpect(count($setTerms) === 3, 'ordered term assignment must return all three probe relationship IDs');
    $probeTtIds = array_map('intval', $setTerms);

    $allRelationshipRows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT term_taxonomy_id, term_order FROM {$wpdb->term_relationships} WHERE object_id = %d ORDER BY term_order ASC",
            $postId,
        ),
        ARRAY_A,
    );
    taxonomyTermQueryExpect(is_array($allRelationshipRows), 'native relationship table query must return an array');
    $storedRows = array_values(array_filter(
        $allRelationshipRows,
        static fn (array $row): bool => in_array((int) $row['term_taxonomy_id'], $probeTtIds, true),
    ));
    taxonomyTermQueryExpect(count($storedRows) === 3, 'native relationship table must contain all three probe taxonomy relationships');
    $storedOrders = array_map(static fn (array $row): int => (int) $row['term_order'], $storedRows);
    taxonomyTermQueryExpect($storedOrders === [1, 2, 3], 'sort=true must persist sequential native term_order values');
    $storedTtIds = array_map(static fn (array $row): int => (int) $row['term_taxonomy_id'], $storedRows);
    taxonomyTermQueryExpect(
        $storedTtIds === $probeTtIds,
        'persisted term_order sequence must match the supplied wp_set_object_terms relationship order',
    );

    $defaultQuery = wp_get_object_terms($postId, $taxonomy);
    taxonomyTermQueryExpect(!is_wp_error($defaultQuery) && is_array($defaultQuery), 'default object-term query must succeed');
    taxonomyTermQueryExpect(
        array_map('intval', $defaultQuery) === $expectedOrder,
        'registered bounded args must automatically return term IDs in persisted term_order ASC order',
    );

    $observedArgs = [];
    $captureArgs = static function (mixed $terms, array $objectIds, array $taxonomies, array $args) use (&$observedArgs): mixed {
        $observedArgs = $args;
        return $terms;
    };
    add_filter('get_object_terms', $captureArgs, 10, 4);
    $conflictingQuery = wp_get_object_terms($postId, $taxonomy, [
        'orderby' => 'name',
        'order' => 'DESC',
        'fields' => 'names',
    ]);
    remove_filter('get_object_terms', $captureArgs, 10);

    taxonomyTermQueryExpect(!is_wp_error($conflictingQuery) && is_array($conflictingQuery), 'conflicting per-call object-term query must still succeed');
    taxonomyTermQueryExpect(
        array_map('intval', $conflictingQuery) === $expectedOrder,
        'registered bounded taxonomy args must override conflicting per-call query keys per native WordPress semantics',
    );
    taxonomyTermQueryExpect(($observedArgs['orderby'] ?? null) === 'term_order', 'final native query args must retain registered orderby=term_order');
    taxonomyTermQueryExpect(($observedArgs['order'] ?? null) === 'ASC', 'final native query args must retain registered order=ASC');
    taxonomyTermQueryExpect(($observedArgs['fields'] ?? null) === 'ids', 'final native query args must retain registered fields=ids');

    $smallPerformance = taxonomyTermQueryCapture($postId, $taxonomy);
    taxonomyTermQueryExpect($smallPerformance['terms'] === $expectedOrder, 'small performance probe must retain native ordered ID output');
    $smallQueryCount = count($smallPerformance['queries']);
    taxonomyTermQueryExpect($smallQueryCount > 0 && $smallQueryCount <= 4, 'small bounded term query must stay inside the fixed SQL budget');

    $performanceTermIds = [];
    for ($index = 1; $index <= 32; ++$index) {
        $inserted = wp_insert_term(
            sprintf('Performance Genre %02d', $index),
            $taxonomy,
            ['slug' => sprintf('performance-genre-%02d', $index)],
        );
        taxonomyTermQueryExpect(!is_wp_error($inserted), 'performance probe terms must be created through native WordPress');
        $performanceTermIds[] = (int) $inserted['term_id'];
    }

    $largeExpectedOrder = array_merge($expectedOrder, $performanceTermIds);
    $largeSetTerms = wp_set_object_terms($postId, $largeExpectedOrder, $taxonomy, false);
    taxonomyTermQueryExpect(!is_wp_error($largeSetTerms) && is_array($largeSetTerms), 'large ordered term assignment must succeed through native WordPress');
    taxonomyTermQueryExpect(count($largeSetTerms) === count($largeExpectedOrder), 'large ordered assignment must retain every probe relationship');

    $largePerformance = taxonomyTermQueryCapture($postId, $taxonomy);
    taxonomyTermQueryExpect($largePerformance['terms'] === $largeExpectedOrder, 'large performance probe must retain native ordered ID output');
    $largeQueryCount = count($largePerformance['queries']);
    taxonomyTermQueryExpect($largeQueryCount > 0 && $largeQueryCount <= 4, 'large bounded term query must stay inside the fixed SQL budget');
    taxonomyTermQueryExpect(
        $largeQueryCount <= $smallQueryCount + 1,
        'native bounded term-query SQL count must remain stable as relationship count grows',
    );

    $performanceQueries = array_merge($smallPerformance['queries'], $largePerformance['queries']);
    $termMetaTable = (string) $wpdb->termmeta;
    $termMetaQueries = array_values(array_filter(
        $performanceQueries,
        static fn (string $query): bool => stripos($query, $termMetaTable) !== false || stripos($query, 'termmeta') !== false,
    ));
    taxonomyTermQueryExpect($termMetaQueries === [], 'bounded fields=ids reads must not introduce term-meta SQL');

    $existing = $definitions->get($definitionId);
    taxonomyTermQueryExpect($existing instanceof Definition && $existing->status === DefinitionStatus::Published, 'term-query fixture must remain published until runtime evidence completes');
    $definitions->save(taxonomyTermQueryDefinition(
        $definitionId,
        DefinitionStatus::Disabled,
        $existing->payload,
        2,
    ));
    $disabled = $definitions->get($definitionId);
    taxonomyTermQueryExpect($disabled instanceof Definition && $disabled->status === DefinitionStatus::Disabled, 'term-query fixture must be disabled after evidence collection');
    taxonomyTermQueryExpect($disabled->revision === 2, 'term-query fixture disable transition must advance its revision');

    wp_delete_post($postId, true);
    foreach (array_merge([$alphaId, $betaId, $gammaId], $performanceTermIds) as $termId) {
        taxonomyTermQueryExpect(wp_delete_term($termId, $taxonomy) === true, 'term-query probe terms must clean up through native WordPress');
    }

    $evidencePath = getenv('WPE_TAXONOMY_TERM_QUERY_EVIDENCE_PATH') ?: '';
    if ($evidencePath !== '') {
        $directory = dirname($evidencePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($evidencePath, json_encode([
            'schema' => 'wpessential-taxonomy-term-query-evidence-v2',
            'result' => 'PASS',
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'taxonomy_key' => $taxonomy,
            'probe_term_count' => count($expectedOrder),
            'sort_enabled' => true,
            'term_order_persisted' => true,
            'registered_args_applied' => true,
            'registered_args_override_conflicting_call_args' => true,
            'bounded_fields_ids' => true,
            'performance_small_relationship_count' => count($expectedOrder),
            'performance_large_relationship_count' => count($largeExpectedOrder),
            'performance_small_sql_queries' => $smallQueryCount,
            'performance_large_sql_queries' => $largeQueryCount,
            'performance_fixed_query_budget' => $smallQueryCount <= 4 && $largeQueryCount <= 4,
            'performance_query_count_stable' => $largeQueryCount <= $smallQueryCount + 1,
            'performance_no_termmeta_sql' => $termMetaQueries === [],
            'fixture_definition_disabled' => true,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    fwrite(STDOUT, "Taxonomy bounded term-query runtime and performance PASS\n");
    return;
}

fwrite(STDERR, "FAIL: unknown WPE_TAXONOMY_TERM_QUERY_MODE {$mode}\n");
exit(1);
