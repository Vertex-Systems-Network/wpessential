<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}
if (!function_exists('get_bloginfo')) {
    fwrite(STDERR, "FAIL: WordPress must be loaded before the Taxonomy runtime integration test.\n");
    exit(1);
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Modules\Taxonomies\TaxonomyRewriteRefreshCoordinator;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Modules\ModuleState;
use WPEssential\Platform\WordPress\Registrations\RegistrationCompilationStatus;
use WPEssential\Platform\WordPress\Registrations\RegistrationKind;
use WPEssential\Platform\WordPress\Registrations\RegistrationRuntimeLoader;

function taxonomyRuntimeExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @param array<string,mixed> $payload */
function taxonomyRuntimeDefinition(
    string $id,
    string $slug,
    DefinitionStatus $status,
    array $payload,
    int $revision = 1,
): Definition {
    return new Definition(
        id: $id,
        slug: $slug,
        type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
        schemaVersion: 1,
        ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
        status: $status,
        payload: $payload,
        revision: $revision,
    );
}

$kernel = \WPEssential\Bootstrap\Plugin::kernel();
taxonomyRuntimeExpect($kernel instanceof Kernel && $kernel->isBooted(), 'production plugin kernel must be booted');
taxonomyRuntimeExpect($kernel->modules()->state('taxonomies') === ModuleState::Booted, 'Taxonomy free module must be booted');
$services = $kernel->services();
$definitions = $services->get('platform.definitions');
$status = $services->get('platform.registrations.compilation-status');
$runtime = $services->get('platform.registrations.runtime');
$rewriteRefresh = $services->get('module.taxonomies.rewrite-refresh');
taxonomyRuntimeExpect($definitions instanceof DefinitionRepositoryInterface, 'shared Definition Repository must be available');
taxonomyRuntimeExpect($status instanceof RegistrationCompilationStatus, 'registration compilation status must be available');
taxonomyRuntimeExpect($runtime instanceof RegistrationRuntimeLoader, 'compiled registration runtime must be available');
taxonomyRuntimeExpect($rewriteRefresh instanceof TaxonomyRewriteRefreshCoordinator, 'Taxonomy rewrite refresh coordinator must be available');

$genreId = '33333333-3333-4333-8333-333333333333';
$conflictId = '44444444-4444-4444-8444-444444444444';
$genrePayload = [
    'taxonomy_key' => 'library_genre',
    'object_types' => ['post'],
    'name' => 'Library Genres',
    'singular_name' => 'Library Genre',
    'public' => true,
    'show_in_rest' => true,
    'hierarchical' => true,
    'rewrite' => ['slug' => 'library/genres', 'with_front' => false, 'hierarchical' => true],
    'query_var' => 'library_genre',
    'show_admin_column' => true,
    'default_term' => [
        'name' => 'General',
        'slug' => 'general',
        'description' => 'Default library genre',
    ],
];
$mode = getenv('WPE_TAXONOMY_TEST_MODE') ?: '';
$evidencePath = getenv('WPE_TAXONOMY_EVIDENCE_PATH') ?: '';

if ($mode === 'seed-active') {
    taxonomyRuntimeExpect($definitions->get($genreId) === null, 'taxonomy fixture must start absent');
    taxonomyRuntimeExpect(
        update_option('permalink_structure', '/%postname%/'),
        'rewrite refresh fixture must enable pretty permalinks before the next WordPress request',
    );
    $definition = taxonomyRuntimeDefinition(
        $genreId,
        'library-genre-definition',
        DefinitionStatus::Published,
        $genrePayload,
    );
    $definitions->save($definition);
    taxonomyRuntimeExpect(
        $rewriteRefresh->scheduleForMutation(null, $definition),
        'publishing a new routed Taxonomy must schedule a rewrite refresh',
    );
    taxonomyRuntimeExpect(
        in_array(get_option(TaxonomyRewriteRefreshCoordinator::OPTION_KEY, false), [1, '1', true], true),
        'rewrite refresh marker must persist for the next WordPress request',
    );
    fwrite(STDOUT, "Taxonomy seed-active PASS\n");
    return;
}

if ($mode === 'verify-active') {
    taxonomyRuntimeExpect($status->passed(), 'active Taxonomy definition compilation must pass');
    taxonomyRuntimeExpect(taxonomy_exists('library_genre'), 'published Taxonomy must register on the next real WordPress request');
    taxonomyRuntimeExpect(
        get_option(TaxonomyRewriteRefreshCoordinator::OPTION_KEY, false) === false,
        'wp_loaded rewrite refresh must clear the pending marker after successful soft flush',
    );
    $rewriteRules = get_option('rewrite_rules', []);
    taxonomyRuntimeExpect(is_array($rewriteRules), 'WordPress rewrite rule cache must be available after refresh');
    $libraryRewriteFound = false;
    foreach (array_keys($rewriteRules) as $pattern) {
        if (is_string($pattern) && str_contains($pattern, 'library/genres')) {
            $libraryRewriteFound = true;
            break;
        }
    }
    taxonomyRuntimeExpect($libraryRewriteFound, 'soft refresh must persist the registered library/genres taxonomy rewrite rules');

    $object = get_taxonomy('library_genre');
    taxonomyRuntimeExpect($object instanceof WP_Taxonomy, 'registered Taxonomy object must be available');
    taxonomyRuntimeExpect($object->public === true && $object->show_in_rest === true, 'public + REST semantics must survive projection');
    taxonomyRuntimeExpect($object->hierarchical === true, 'hierarchical semantics must survive projection');
    taxonomyRuntimeExpect(in_array('post', $object->object_type, true), 'Taxonomy object-type association must survive projection');
    taxonomyRuntimeExpect(is_array($object->rewrite) && ($object->rewrite['slug'] ?? null) === 'library/genres', 'Taxonomy rewrite slug must survive projection');
    taxonomyRuntimeExpect(isset($runtime->forKind(RegistrationKind::Taxonomy)['library_genre']), 'compiled manifest must contain the Taxonomy');

    $defaultTermId = (int) get_option('default_term_library_genre', 0);
    taxonomyRuntimeExpect($defaultTermId > 0, 'WordPress must persist the custom taxonomy default term id');
    $defaultTerm = get_term($defaultTermId, 'library_genre');
    taxonomyRuntimeExpect($defaultTerm instanceof WP_Term, 'WordPress must create the projected custom taxonomy default term');
    taxonomyRuntimeExpect($defaultTerm->name === 'General', 'default term name must survive native registration');
    taxonomyRuntimeExpect($defaultTerm->slug === 'general', 'default term slug must survive native registration');
    taxonomyRuntimeExpect($defaultTerm->description === 'Default library genre', 'default term description must survive native registration');

    $postId = wp_insert_post([
        'post_title' => 'Taxonomy default term runtime fixture',
        'post_status' => 'publish',
        'post_type' => 'post',
    ], true);
    taxonomyRuntimeExpect(is_int($postId) && $postId > 0, 'default term fixture post must be created');

    $alternate = wp_insert_term('Mystery', 'library_genre', ['slug' => 'mystery']);
    taxonomyRuntimeExpect(!is_wp_error($alternate) && is_array($alternate), 'alternate taxonomy term must be created');
    $alternateTermId = (int) ($alternate['term_id'] ?? 0);
    taxonomyRuntimeExpect($alternateTermId > 0, 'alternate taxonomy term must have an id');

    $assigned = wp_set_object_terms($postId, [$alternateTermId], 'library_genre', false);
    taxonomyRuntimeExpect(is_array($assigned), 'alternate taxonomy term must assign through WordPress core');
    taxonomyRuntimeExpect(
        wp_delete_term($alternateTermId, 'library_genre') === true,
        'deleting the sole non-default term must succeed through WordPress core',
    );
    $fallbackTerms = wp_get_object_terms($postId, 'library_genre', [
        'fields' => 'ids',
        'orderby' => 'none',
    ]);
    taxonomyRuntimeExpect(is_array($fallbackTerms), 'fallback object terms must be readable');
    taxonomyRuntimeExpect(
        array_map('intval', $fallbackTerms) === [$defaultTermId],
        'WordPress must reassign the configured custom taxonomy default when the sole assigned term is deleted',
    );

    taxonomyRuntimeExpect(
        wp_remove_object_terms($postId, $defaultTermId, 'library_genre') === true,
        'direct WordPress relationship removal must succeed for the assigned default term',
    );
    $termsAfterDirectRemoval = wp_get_object_terms($postId, 'library_genre', [
        'fields' => 'ids',
        'orderby' => 'none',
    ]);
    taxonomyRuntimeExpect(
        is_array($termsAfterDirectRemoval) && $termsAfterDirectRemoval === [],
        'direct relationship removal must preserve native semantics and may leave a custom taxonomy object term-less',
    );

    taxonomyRuntimeExpect(
        wp_delete_term($defaultTermId, 'library_genre') === 0,
        'WordPress must protect the configured custom taxonomy default term from direct deletion',
    );
    taxonomyRuntimeExpect(
        get_term($defaultTermId, 'library_genre') instanceof WP_Term,
        'protected default term must remain present after blocked deletion',
    );

    if ($evidencePath !== '') {
        $directory = dirname($evidencePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($evidencePath, json_encode([
            'schema' => 'wpessential-taxonomy-runtime-evidence-v2',
            'result' => 'PARTIAL',
            'default_term_id' => $defaultTermId,
            'default_term_created' => true,
            'default_term_option_tracks_id' => true,
            'native_delete_fallback_reassigned_default' => true,
            'direct_remove_can_leave_termless' => true,
            'default_term_delete_protected' => true,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    fwrite(STDOUT, "Taxonomy verify-active PASS\n");
    return;
}

if ($mode === 'seed-invalid') {
    taxonomyRuntimeExpect(taxonomy_exists('library_genre'), 'last good Taxonomy must be active while invalid fixture is seeded');
    $definitions->save(taxonomyRuntimeDefinition(
        $conflictId,
        'core-category-conflict',
        DefinitionStatus::Published,
        [
            'taxonomy_key' => 'category',
            'object_types' => ['post'],
            'name' => 'Conflicting Categories',
            'singular_name' => 'Conflicting Category',
        ],
    ));
    fwrite(STDOUT, "Taxonomy seed-invalid PASS\n");
    return;
}

if ($mode === 'verify-invalid-fail-closed') {
    taxonomyRuntimeExpect(!$status->passed() && $status->error() !== null, 'reserved active Taxonomy definition must fail compilation explicitly');
    taxonomyRuntimeExpect(taxonomy_exists('library_genre'), 'last-known-good compiled Taxonomy must remain active after failed compilation');
    taxonomyRuntimeExpect(taxonomy_exists('category'), 'WordPress core category taxonomy must remain registered');
    taxonomyRuntimeExpect(isset($runtime->forKind(RegistrationKind::Taxonomy)['library_genre']), 'failed compilation must not replace last-known-good manifest');
    fwrite(STDOUT, "Taxonomy verify-invalid-fail-closed PASS\n");
    return;
}

if ($mode === 'disable-invalid') {
    $existing = $definitions->get($conflictId);
    taxonomyRuntimeExpect($existing instanceof Definition, 'invalid conflict definition must exist for disable transition');
    $definitions->save(taxonomyRuntimeDefinition(
        $conflictId,
        $existing->slug,
        DefinitionStatus::Disabled,
        $existing->payload,
        2,
    ));
    fwrite(STDOUT, "Taxonomy disable-invalid PASS\n");
    return;
}

if ($mode === 'disable-active') {
    taxonomyRuntimeExpect($status->passed(), 'compilation must recover after invalid Taxonomy definition is disabled');
    $existing = $definitions->get($genreId);
    taxonomyRuntimeExpect($existing instanceof Definition, 'Taxonomy definition must exist for disable transition');
    $definitions->save(taxonomyRuntimeDefinition(
        $genreId,
        $existing->slug,
        DefinitionStatus::Disabled,
        $existing->payload,
        2,
    ));
    fwrite(STDOUT, "Taxonomy disable-active PASS\n");
    return;
}

if ($mode === 'verify-disabled') {
    taxonomyRuntimeExpect($status->passed(), 'disabled-only Taxonomy definition set must compile successfully');
    taxonomyRuntimeExpect(!taxonomy_exists('library_genre'), 'disabled Taxonomy must not register on the next request');
    $retained = $definitions->get($genreId);
    taxonomyRuntimeExpect($retained instanceof Definition && $retained->status === DefinitionStatus::Disabled, 'disabled Taxonomy definition must remain persisted');
    taxonomyRuntimeExpect($retained->payload === $genrePayload, 'disable must retain canonical Taxonomy configuration payload');
    taxonomyRuntimeExpect($runtime->forKind(RegistrationKind::Taxonomy) === [], 'compiled Taxonomy manifest must be empty after disable');

    $priorEvidence = [];
    if ($evidencePath !== '' && is_file($evidencePath)) {
        $rawEvidence = file_get_contents($evidencePath);
        if (is_string($rawEvidence)) {
            $decodedEvidence = json_decode($rawEvidence, true);
            if (is_array($decodedEvidence)) {
                $priorEvidence = $decodedEvidence;
            }
        }
    }
    taxonomyRuntimeExpect(($priorEvidence['default_term_created'] ?? false) === true, 'default term creation evidence must survive lifecycle requests');
    taxonomyRuntimeExpect(($priorEvidence['default_term_option_tracks_id'] ?? false) === true, 'default term option evidence must survive lifecycle requests');
    taxonomyRuntimeExpect(($priorEvidence['native_delete_fallback_reassigned_default'] ?? false) === true, 'native delete fallback evidence must survive lifecycle requests');
    taxonomyRuntimeExpect(($priorEvidence['direct_remove_can_leave_termless'] ?? false) === true, 'native direct-removal evidence must survive lifecycle requests');
    taxonomyRuntimeExpect(($priorEvidence['default_term_delete_protected'] ?? false) === true, 'default term protection evidence must survive lifecycle requests');

    $defaultTermId = (int) ($priorEvidence['default_term_id'] ?? 0);
    taxonomyRuntimeExpect($defaultTermId > 0, 'default term evidence must retain the native term id');
    taxonomyRuntimeExpect(
        (int) get_option('default_term_library_genre', 0) === $defaultTermId,
        'disabling WPE runtime registration must not destructively remove the native default-term option',
    );

    global $wpdb;
    $defaultTermRows = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE term_id = %d AND taxonomy = %s",
        $defaultTermId,
        'library_genre',
    ));
    taxonomyRuntimeExpect(
        $defaultTermRows === 1,
        'disabling WPE runtime registration must not destructively remove the native default term row',
    );

    if ($evidencePath !== '') {
        $directory = dirname($evidencePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $evidence = array_merge($priorEvidence, [
            'schema' => 'wpessential-taxonomy-runtime-evidence-v2',
            'result' => 'PASS',
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'module_state' => $kernel->modules()->state('taxonomies')?->value,
            'definition_type' => TaxonomyDefinitionProjector::DEFINITION_TYPE,
            'owner_surface_id' => TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            'taxonomy_key' => 'library_genre',
            'disabled_definition_retained' => true,
            'compiled_taxonomy_count' => count($runtime->forKind(RegistrationKind::Taxonomy)),
            'disabled_default_term_row_retained' => true,
            'disabled_default_term_option_retained' => true,
        ]);
        file_put_contents($evidencePath, json_encode(
            $evidence,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        ));
    }
    fwrite(STDOUT, "Taxonomy verify-disabled PASS\n");
    return;
}

fwrite(STDERR, "FAIL: unknown WPE_TAXONOMY_TEST_MODE {$mode}\n");
exit(1);
