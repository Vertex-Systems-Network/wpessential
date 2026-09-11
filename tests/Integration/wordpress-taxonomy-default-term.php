<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}
if (!function_exists('get_bloginfo')) {
    fwrite(STDERR, "FAIL: WordPress must be loaded before the Taxonomy default-term integration test.\n");
    exit(1);
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Registrations\RegistrationCompilationStatus;

function taxonomyDefaultTermExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @param array<string,mixed> $payload */
function taxonomyDefaultTermDefinition(
    string $id,
    DefinitionStatus $status,
    array $payload,
    int $revision = 1,
): Definition {
    return new Definition(
        id: $id,
        slug: 'library-default-genre-definition',
        type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
        schemaVersion: 1,
        ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
        status: $status,
        payload: $payload,
        revision: $revision,
    );
}

$kernel = \WPEssential\Bootstrap\Plugin::kernel();
taxonomyDefaultTermExpect($kernel instanceof Kernel && $kernel->isBooted(), 'production plugin kernel must be booted');
$services = $kernel->services();
$definitions = $services->get('platform.definitions');
$status = $services->get('platform.registrations.compilation-status');
taxonomyDefaultTermExpect($definitions instanceof DefinitionRepositoryInterface, 'shared Definition Repository must be available');
taxonomyDefaultTermExpect($status instanceof RegistrationCompilationStatus, 'registration compilation status must be available');

$definitionId = '55555555-5555-4555-8555-555555555555';
$taxonomy = 'library_default_genre';
$defaultName = 'General Library Genre';
$defaultSlug = 'general-library-genre';
$defaultDescription = 'Default library classification';
$payload = [
    'taxonomy_key' => $taxonomy,
    'object_types' => ['post'],
    'name' => 'Library Default Genres',
    'singular_name' => 'Library Default Genre',
    'public' => true,
    'show_in_rest' => true,
    'hierarchical' => true,
    'rewrite' => false,
    'default_term' => [
        'name' => $defaultName,
        'slug' => $defaultSlug,
        'description' => $defaultDescription,
    ],
];
$mode = getenv('WPE_TAXONOMY_DEFAULT_TERM_MODE') ?: '';

if ($mode === 'seed') {
    taxonomyDefaultTermExpect($definitions->get($definitionId) === null, 'default-term fixture must start absent');
    taxonomyDefaultTermExpect(!taxonomy_exists($taxonomy), 'default-term taxonomy must not be registered before its published Definition is compiled');

    $definitions->save(taxonomyDefaultTermDefinition(
        $definitionId,
        DefinitionStatus::Published,
        $payload,
    ));

    fwrite(STDOUT, "Taxonomy default-term seed PASS\n");
    return;
}

if ($mode === 'verify-created') {
    taxonomyDefaultTermExpect($status->passed(), 'published default-term Taxonomy definition compilation must pass');
    taxonomyDefaultTermExpect(taxonomy_exists($taxonomy), 'published default-term Taxonomy must register on the next real WordPress request');

    $taxonomyObject = get_taxonomy($taxonomy);
    taxonomyDefaultTermExpect($taxonomyObject instanceof WP_Taxonomy, 'registered default-term Taxonomy object must be available');
    taxonomyDefaultTermExpect(is_array($taxonomyObject->default_term), 'native Taxonomy object must retain the typed default_term map');
    taxonomyDefaultTermExpect(($taxonomyObject->default_term['name'] ?? null) === $defaultName, 'default term name must survive projection');
    taxonomyDefaultTermExpect(($taxonomyObject->default_term['slug'] ?? null) === $defaultSlug, 'default term slug must survive projection');
    taxonomyDefaultTermExpect(($taxonomyObject->default_term['description'] ?? null) === $defaultDescription, 'default term description must survive projection');

    $term = get_term_by('slug', $defaultSlug, $taxonomy);
    taxonomyDefaultTermExpect($term instanceof WP_Term, 'WordPress registration must create the configured default term');
    taxonomyDefaultTermExpect($term->name === $defaultName, 'created default term must preserve its configured name');
    taxonomyDefaultTermExpect($term->description === $defaultDescription, 'created default term must preserve its configured description');
    taxonomyDefaultTermExpect(
        (int) get_option('default_term_' . $taxonomy, 0) === (int) $term->term_id,
        'WordPress must track the created custom taxonomy default term ID in its native option',
    );

    fwrite(STDOUT, "Taxonomy default-term creation PASS\n");
    return;
}

if ($mode === 'verify-reuse-and-behavior') {
    taxonomyDefaultTermExpect($status->passed(), 'reloaded default-term Taxonomy definition compilation must pass');
    taxonomyDefaultTermExpect(taxonomy_exists($taxonomy), 'default-term Taxonomy must remain registered on a later request');

    $term = get_term_by('slug', $defaultSlug, $taxonomy);
    taxonomyDefaultTermExpect($term instanceof WP_Term, 'existing default term must be reused on later registration');
    $defaultTermId = (int) $term->term_id;
    taxonomyDefaultTermExpect(
        (int) get_option('default_term_' . $taxonomy, 0) === $defaultTermId,
        'native default-term option must continue to reference the reused term',
    );

    $sameSlugTerms = get_terms([
        'taxonomy' => $taxonomy,
        'slug' => $defaultSlug,
        'hide_empty' => false,
    ]);
    taxonomyDefaultTermExpect(!is_wp_error($sameSlugTerms), 'default-term reuse probe must return a native term list');
    taxonomyDefaultTermExpect(is_array($sameSlugTerms) && count($sameSlugTerms) === 1, 're-registration must not duplicate the configured default term');

    $admin = get_user_by('login', 'wpessential_admin');
    taxonomyDefaultTermExpect($admin instanceof WP_User, 'WordPress fixture must provide the administrator user');
    wp_set_current_user((int) $admin->ID);

    $postId = wp_insert_post([
        'post_type' => 'post',
        'post_status' => 'draft',
        'post_title' => 'Taxonomy Default Term Probe',
    ], true);
    taxonomyDefaultTermExpect(!is_wp_error($postId) && is_int($postId) && $postId > 0, 'default-term probe post must be created');

    $insertedTermIds = wp_get_object_terms($postId, $taxonomy, ['fields' => 'ids']);
    taxonomyDefaultTermExpect(!is_wp_error($insertedTermIds), 'inserted post default-term lookup must succeed');
    taxonomyDefaultTermExpect(
        is_array($insertedTermIds) && in_array($defaultTermId, array_map('intval', $insertedTermIds), true),
        'WordPress must assign the custom taxonomy default term when a post is inserted without an explicit term',
    );

    $removed = wp_set_object_terms($postId, [], $taxonomy, false);
    taxonomyDefaultTermExpect(!is_wp_error($removed), 'default-term relationship removal probe must succeed');
    $afterRemoval = wp_get_object_terms($postId, $taxonomy, ['fields' => 'ids']);
    taxonomyDefaultTermExpect(!is_wp_error($afterRemoval) && $afterRemoval === [], 'probe post must be term-less before publish restoration evidence');

    wp_publish_post($postId);
    $publishedTermIds = wp_get_object_terms($postId, $taxonomy, ['fields' => 'ids']);
    taxonomyDefaultTermExpect(!is_wp_error($publishedTermIds), 'published post default-term lookup must succeed');
    taxonomyDefaultTermExpect(
        is_array($publishedTermIds) && in_array($defaultTermId, array_map('intval', $publishedTermIds), true),
        'WordPress must restore the custom taxonomy default term when publishing a term-less post',
    );

    taxonomyDefaultTermExpect(
        wp_delete_term($defaultTermId, $taxonomy) === 0,
        'WordPress must block deletion of the configured custom taxonomy default term',
    );

    $existing = $definitions->get($definitionId);
    taxonomyDefaultTermExpect($existing instanceof Definition && $existing->status === DefinitionStatus::Published, 'default-term fixture must remain published until behavior evidence completes');
    $definitions->save(taxonomyDefaultTermDefinition(
        $definitionId,
        DefinitionStatus::Disabled,
        $existing->payload,
        2,
    ));
    $disabled = $definitions->get($definitionId);
    taxonomyDefaultTermExpect($disabled instanceof Definition && $disabled->status === DefinitionStatus::Disabled, 'default-term fixture must be disabled after evidence collection');
    taxonomyDefaultTermExpect($disabled->revision === 2, 'default-term fixture disable transition must advance its revision');

    wp_delete_post($postId, true);

    $evidencePath = getenv('WPE_TAXONOMY_DEFAULT_TERM_EVIDENCE_PATH') ?: '';
    if ($evidencePath !== '') {
        $directory = dirname($evidencePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($evidencePath, json_encode([
            'schema' => 'wpessential-taxonomy-default-term-evidence-v1',
            'result' => 'PASS',
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'taxonomy_key' => $taxonomy,
            'default_term_id' => $defaultTermId,
            'default_term_created' => true,
            'default_term_reused_without_duplicate' => true,
            'native_option_tracked' => true,
            'insert_default_assigned' => true,
            'publish_default_restored' => true,
            'default_term_deletion_blocked' => true,
            'fixture_definition_disabled' => true,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    fwrite(STDOUT, "Taxonomy native default-term lifecycle PASS\n");
    return;
}

fwrite(STDERR, "FAIL: unknown WPE_TAXONOMY_DEFAULT_TERM_MODE {$mode}\n");
exit(1);
