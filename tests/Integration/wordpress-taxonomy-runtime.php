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
taxonomyRuntimeExpect($definitions instanceof DefinitionRepositoryInterface, 'shared Definition Repository must be available');
taxonomyRuntimeExpect($status instanceof RegistrationCompilationStatus, 'registration compilation status must be available');
taxonomyRuntimeExpect($runtime instanceof RegistrationRuntimeLoader, 'compiled registration runtime must be available');

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
];
$mode = getenv('WPE_TAXONOMY_TEST_MODE') ?: '';

if ($mode === 'seed-active') {
    taxonomyRuntimeExpect($definitions->get($genreId) === null, 'taxonomy fixture must start absent');
    $definitions->save(taxonomyRuntimeDefinition(
        $genreId,
        'library-genre-definition',
        DefinitionStatus::Published,
        $genrePayload,
    ));
    fwrite(STDOUT, "Taxonomy seed-active PASS\n");
    return;
}

if ($mode === 'verify-active') {
    taxonomyRuntimeExpect($status->passed(), 'active Taxonomy definition compilation must pass');
    taxonomyRuntimeExpect(taxonomy_exists('library_genre'), 'published Taxonomy must register on the next real WordPress request');
    $object = get_taxonomy('library_genre');
    taxonomyRuntimeExpect($object instanceof WP_Taxonomy, 'registered Taxonomy object must be available');
    taxonomyRuntimeExpect($object->public === true && $object->show_in_rest === true, 'public + REST semantics must survive projection');
    taxonomyRuntimeExpect($object->hierarchical === true, 'hierarchical semantics must survive projection');
    taxonomyRuntimeExpect(in_array('post', $object->object_type, true), 'Taxonomy object-type association must survive projection');
    taxonomyRuntimeExpect(is_array($object->rewrite) && ($object->rewrite['slug'] ?? null) === 'library/genres', 'Taxonomy rewrite slug must survive projection');
    taxonomyRuntimeExpect(isset($runtime->forKind(RegistrationKind::Taxonomy)['library_genre']), 'compiled manifest must contain the Taxonomy');
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

    $evidencePath = getenv('WPE_TAXONOMY_EVIDENCE_PATH') ?: '';
    if ($evidencePath !== '') {
        $directory = dirname($evidencePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $evidence = [
            'schema' => 'wpessential-taxonomy-runtime-evidence-v1',
            'result' => 'PASS',
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'module_state' => $kernel->modules()->state('taxonomies')?->value,
            'definition_type' => TaxonomyDefinitionProjector::DEFINITION_TYPE,
            'owner_surface_id' => TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            'taxonomy_key' => 'library_genre',
            'disabled_definition_retained' => true,
            'compiled_taxonomy_count' => count($runtime->forKind(RegistrationKind::Taxonomy)),
        ];
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
