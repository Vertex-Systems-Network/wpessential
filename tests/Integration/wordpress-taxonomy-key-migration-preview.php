<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}
if (!function_exists('get_bloginfo')) {
    fwrite(STDERR, "FAIL: WordPress must be loaded before the Taxonomy key-migration preview integration test.\n");
    exit(1);
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Modules\Taxonomies\TaxonomyKeyMigrationPreviewService;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

function taxonomyKeyMigrationPreviewExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @param array<string,mixed> $payload */
function taxonomyKeyMigrationPreviewDefinition(
    string $id,
    DefinitionStatus $status,
    array $payload,
    int $revision = 1,
): Definition {
    return new Definition(
        id: $id,
        slug: 'migration-probe-genre-definition',
        type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
        schemaVersion: 1,
        ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
        status: $status,
        payload: $payload,
        revision: $revision,
    );
}

$kernel = \WPEssential\Bootstrap\Plugin::kernel();
taxonomyKeyMigrationPreviewExpect($kernel instanceof Kernel && $kernel->isBooted(), 'production plugin kernel must be booted');
$services = $kernel->services();
$definitions = $services->get('platform.definitions');
$previewService = $services->get('module.taxonomies.key-migration-preview');
taxonomyKeyMigrationPreviewExpect($definitions instanceof DefinitionRepositoryInterface, 'shared Definition Repository must be available');
taxonomyKeyMigrationPreviewExpect($previewService instanceof TaxonomyKeyMigrationPreviewService, 'Taxonomy key-migration preview service must be registered');

$definitionId = '77777777-7777-4777-8777-777777777777';
$sourceTaxonomy = 'migration_probe_genre';
$targetTaxonomy = 'migration_probe_topic';
$payload = [
    'taxonomy_key' => $sourceTaxonomy,
    'object_types' => ['post'],
    'name' => 'Migration Probe Genres',
    'singular_name' => 'Migration Probe Genre',
    'public' => true,
    'show_in_rest' => true,
];
$mode = getenv('WPE_TAXONOMY_KEY_MIGRATION_PREVIEW_MODE') ?: '';

if ($mode === 'seed') {
    taxonomyKeyMigrationPreviewExpect($definitions->get($definitionId) === null, 'migration-preview fixture must start absent');
    taxonomyKeyMigrationPreviewExpect(!taxonomy_exists($sourceTaxonomy), 'source taxonomy must not be registered before the published Definition is compiled');
    $definitions->save(taxonomyKeyMigrationPreviewDefinition(
        $definitionId,
        DefinitionStatus::Published,
        $payload,
    ));

    fwrite(STDOUT, "Taxonomy key-migration preview seed PASS\n");
    return;
}

if ($mode === 'verify') {
    taxonomyKeyMigrationPreviewExpect(taxonomy_exists($sourceTaxonomy), 'source taxonomy must be registered from the published Definition');
    taxonomyKeyMigrationPreviewExpect(!taxonomy_exists($targetTaxonomy), 'target taxonomy must be absent before preview');

    $admin = get_user_by('login', 'wpessential_admin');
    taxonomyKeyMigrationPreviewExpect($admin instanceof WP_User, 'WordPress fixture must provide the administrator user');
    wp_set_current_user((int) $admin->ID);

    $postId = wp_insert_post([
        'post_type' => 'post',
        'post_status' => 'draft',
        'post_title' => 'Taxonomy Migration Preview Probe',
    ], true);
    taxonomyKeyMigrationPreviewExpect(!is_wp_error($postId) && is_int($postId) && $postId > 0, 'migration-preview probe post must be created');

    $term = wp_insert_term('Migration Preview Genre', $sourceTaxonomy, ['slug' => 'migration-preview-genre']);
    taxonomyKeyMigrationPreviewExpect(!is_wp_error($term), 'migration-preview probe term must be created');
    $termId = (int) $term['term_id'];
    $termTaxonomyId = (int) $term['term_taxonomy_id'];
    $assigned = wp_set_object_terms($postId, [$termId], $sourceTaxonomy, false);
    taxonomyKeyMigrationPreviewExpect(!is_wp_error($assigned), 'migration-preview probe relationship must be created');

    $beforeDefinition = $definitions->get($definitionId);
    taxonomyKeyMigrationPreviewExpect($beforeDefinition instanceof Definition, 'migration-preview source Definition must exist before preview');
    $beforeDefinitionSnapshot = [
        'payload' => $beforeDefinition->payload,
        'revision' => $beforeDefinition->revision,
        'status' => $beforeDefinition->status->value,
        'checksum' => $beforeDefinition->checksum,
    ];
    $beforeTerms = wp_get_object_terms($postId, $sourceTaxonomy, ['fields' => 'ids']);
    taxonomyKeyMigrationPreviewExpect(!is_wp_error($beforeTerms) && is_array($beforeTerms), 'source relationship snapshot must be readable');
    $beforeRewriteRules = get_option('rewrite_rules');

    $preview = $previewService->preview($definitionId, $targetTaxonomy);
    taxonomyKeyMigrationPreviewExpect(($preview['preview_only'] ?? false) === true, 'migration planning must identify itself as preview-only');
    taxonomyKeyMigrationPreviewExpect(($preview['execution_authorized'] ?? true) === false, 'migration execution must remain unauthorized');
    taxonomyKeyMigrationPreviewExpect(($preview['blocked'] ?? true) === false, 'safe probe target must not be blocked');
    taxonomyKeyMigrationPreviewExpect(($preview['source_key'] ?? null) === $sourceTaxonomy, 'preview must report canonical source key');
    taxonomyKeyMigrationPreviewExpect(($preview['target_key'] ?? null) === $targetTaxonomy, 'preview must report proposed target key');
    taxonomyKeyMigrationPreviewExpect(($preview['impacts']['term_urls']['changes'] ?? false) === true, 'default term URL path must report a key-change impact');
    taxonomyKeyMigrationPreviewExpect(($preview['impacts']['rest_route']['changes'] ?? false) === true, 'default REST route must report a key-change impact');
    taxonomyKeyMigrationPreviewExpect(($preview['impacts']['query_var']['changes'] ?? false) === true, 'default query var must report a key-change impact');
    taxonomyKeyMigrationPreviewExpect(($preview['impacts']['object_associations']['changes'] ?? true) === false, 'object associations must remain unchanged by key-only planning');
    taxonomyKeyMigrationPreviewExpect(($preview['recovery_plan']['execution_available'] ?? true) === false, 'recovery plan must not expose execution');

    $afterDefinition = $definitions->get($definitionId);
    taxonomyKeyMigrationPreviewExpect($afterDefinition instanceof Definition, 'source Definition must still exist after preview');
    $afterDefinitionSnapshot = [
        'payload' => $afterDefinition->payload,
        'revision' => $afterDefinition->revision,
        'status' => $afterDefinition->status->value,
        'checksum' => $afterDefinition->checksum,
    ];
    $definitionUnchanged = $beforeDefinitionSnapshot === $afterDefinitionSnapshot;
    taxonomyKeyMigrationPreviewExpect($definitionUnchanged, 'preview must not mutate source Definition state');
    taxonomyKeyMigrationPreviewExpect(taxonomy_exists($sourceTaxonomy), 'source taxonomy must remain registered after preview');
    taxonomyKeyMigrationPreviewExpect(!taxonomy_exists($targetTaxonomy), 'target taxonomy must remain unregistered after preview');

    $termAfter = get_term($termId, $sourceTaxonomy);
    $termUnchanged = $termAfter instanceof WP_Term
        && (int) $termAfter->term_id === $termId
        && (int) $termAfter->term_taxonomy_id === $termTaxonomyId
        && $termAfter->slug === 'migration-preview-genre';
    taxonomyKeyMigrationPreviewExpect($termUnchanged, 'preview must not mutate or move the source term');

    $afterTerms = wp_get_object_terms($postId, $sourceTaxonomy, ['fields' => 'ids']);
    taxonomyKeyMigrationPreviewExpect(!is_wp_error($afterTerms) && is_array($afterTerms), 'source relationship must remain readable after preview');
    $relationshipUnchanged = array_map('intval', $beforeTerms) === array_map('intval', $afterTerms)
        && in_array($termId, array_map('intval', $afterTerms), true);
    taxonomyKeyMigrationPreviewExpect($relationshipUnchanged, 'preview must not mutate source term relationships');

    $afterRewriteRules = get_option('rewrite_rules');
    $rewriteRulesUnchanged = $beforeRewriteRules === $afterRewriteRules;
    taxonomyKeyMigrationPreviewExpect($rewriteRulesUnchanged, 'preview must not flush or change persisted rewrite rules');

    wp_delete_post($postId, true);
    taxonomyKeyMigrationPreviewExpect(wp_delete_term($termId, $sourceTaxonomy) === true, 'migration-preview probe term must clean up');

    $existing = $definitions->get($definitionId);
    taxonomyKeyMigrationPreviewExpect($existing instanceof Definition, 'migration-preview Definition must remain available for cleanup');
    $definitions->save(taxonomyKeyMigrationPreviewDefinition(
        $definitionId,
        DefinitionStatus::Disabled,
        $existing->payload,
        $existing->revision + 1,
    ));
    $disabled = $definitions->get($definitionId);
    taxonomyKeyMigrationPreviewExpect($disabled instanceof Definition && $disabled->status === DefinitionStatus::Disabled, 'migration-preview fixture must be disabled after evidence');

    $evidencePath = getenv('WPE_TAXONOMY_KEY_MIGRATION_PREVIEW_EVIDENCE_PATH') ?: '';
    if ($evidencePath !== '') {
        $directory = dirname($evidencePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($evidencePath, json_encode([
            'schema' => 'wpessential-taxonomy-key-migration-preview-evidence-v1',
            'result' => 'PASS',
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'source_key' => $sourceTaxonomy,
            'target_key' => $targetTaxonomy,
            'preview_only' => true,
            'execution_authorized' => false,
            'definition_unchanged' => $definitionUnchanged,
            'source_taxonomy_present' => true,
            'target_taxonomy_absent' => true,
            'term_unchanged' => $termUnchanged,
            'relationship_unchanged' => $relationshipUnchanged,
            'rewrite_rules_unchanged' => $rewriteRulesUnchanged,
            'term_url_impact_reported' => true,
            'rest_route_impact_reported' => true,
            'query_var_impact_reported' => true,
            'object_associations_unchanged' => true,
            'fixture_definition_disabled' => true,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    fwrite(STDOUT, "Taxonomy key-migration read-only preview PASS\n");
    return;
}

fwrite(STDERR, "FAIL: unknown WPE_TAXONOMY_KEY_MIGRATION_PREVIEW_MODE {$mode}\n");
exit(1);
