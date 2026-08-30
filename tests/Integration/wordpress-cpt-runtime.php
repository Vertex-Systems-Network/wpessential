<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    fwrite(STDERR, "FAIL: WordPress must be loaded before the CPT runtime integration test.\n");
    exit(1);
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeDefinitionProjector;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Modules\ModuleState;
use WPEssential\Platform\WordPress\Registrations\RegistrationCompilationStatus;
use WPEssential\Platform\WordPress\Registrations\RegistrationKind;
use WPEssential\Platform\WordPress\Registrations\RegistrationRuntimeLoader;

function cptRuntimeExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function cptRuntimeDefinition(string $id, string $slug, DefinitionStatus $status, array $payload, int $revision = 1): Definition
{
    return new Definition(
        id: $id,
        slug: $slug,
        type: CustomPostTypeDefinitionProjector::DEFINITION_TYPE,
        schemaVersion: 1,
        ownerSurfaceId: 1,
        status: $status,
        payload: $payload,
        revision: $revision,
    );
}

$kernel = \WPEssential\Bootstrap\Plugin::kernel();
cptRuntimeExpect($kernel instanceof Kernel && $kernel->isBooted(), 'production plugin kernel must be booted');
cptRuntimeExpect($kernel->modules()->state('custom-post-types') === ModuleState::Booted, 'CPT free module must be booted');
$services = $kernel->services();
$definitions = $services->get('platform.definitions');
$status = $services->get('platform.registrations.compilation-status');
$runtime = $services->get('platform.registrations.runtime');
cptRuntimeExpect($definitions instanceof DefinitionRepositoryInterface, 'shared Definition Repository must be available');
cptRuntimeExpect($status instanceof RegistrationCompilationStatus, 'registration compilation status must be available');
cptRuntimeExpect($runtime instanceof RegistrationRuntimeLoader, 'compiled registration runtime must be available');

$bookId = '11111111-1111-4111-8111-111111111111';
$conflictId = '22222222-2222-4222-8222-222222222222';
$bookPayload = [
    'post_type_key' => 'library_book',
    'name' => 'Library Books',
    'singular_name' => 'Library Book',
    'public' => true,
    'show_in_rest' => true,
    'supports' => ['title', 'editor', 'thumbnail'],
    'has_archive' => 'library-books',
    'rewrite' => ['slug' => 'library/books', 'with_front' => false],
    'query_var' => 'library_book',
    'can_export' => true,
];
$mode = getenv('WPE_CPT_TEST_MODE') ?: '';

if ($mode === 'seed-active') {
    cptRuntimeExpect($definitions->get($bookId) === null, 'book fixture must start absent');
    $definitions->save(cptRuntimeDefinition($bookId, 'library-book-definition', DefinitionStatus::Published, $bookPayload));
    fwrite(STDOUT, "CPT seed-active PASS\n");
    return;
}

if ($mode === 'verify-active') {
    cptRuntimeExpect($status->passed(), 'active definition compilation must pass');
    cptRuntimeExpect(post_type_exists('library_book'), 'published CPT must register on the next real WordPress request');
    $object = get_post_type_object('library_book');
    cptRuntimeExpect($object instanceof WP_Post_Type, 'registered CPT object must be available');
    cptRuntimeExpect($object->public === true && $object->show_in_rest === true, 'public + REST semantics must survive projection');
    cptRuntimeExpect($object->has_archive === 'library-books', 'archive slug must survive projection');
    cptRuntimeExpect(post_type_supports('library_book', 'title') && post_type_supports('library_book', 'editor'), 'editor supports must register');
    cptRuntimeExpect(isset($runtime->forKind(RegistrationKind::PostType)['library_book']), 'compiled manifest must contain the CPT');
    fwrite(STDOUT, "CPT verify-active PASS\n");
    return;
}

if ($mode === 'seed-invalid') {
    cptRuntimeExpect(post_type_exists('library_book'), 'last good CPT must be active while invalid fixture is seeded');
    $definitions->save(cptRuntimeDefinition($conflictId, 'core-post-conflict', DefinitionStatus::Published, [
        'post_type_key' => 'post',
        'name' => 'Conflicting Posts',
        'singular_name' => 'Conflicting Post',
    ]));
    fwrite(STDOUT, "CPT seed-invalid PASS\n");
    return;
}

if ($mode === 'verify-invalid-fail-closed') {
    cptRuntimeExpect(!$status->passed() && $status->error() !== null, 'reserved active definition must fail compilation explicitly');
    cptRuntimeExpect(post_type_exists('library_book'), 'last-known-good compiled CPT must remain active after failed compilation');
    cptRuntimeExpect(post_type_exists('post'), 'WordPress core post type must remain registered');
    cptRuntimeExpect(isset($runtime->forKind(RegistrationKind::PostType)['library_book']), 'failed compilation must not replace last-known-good manifest');
    fwrite(STDOUT, "CPT verify-invalid-fail-closed PASS\n");
    return;
}

if ($mode === 'disable-invalid') {
    $existing = $definitions->get($conflictId);
    cptRuntimeExpect($existing instanceof Definition, 'invalid conflict definition must exist for disable transition');
    $definitions->save(cptRuntimeDefinition($conflictId, $existing->slug, DefinitionStatus::Disabled, $existing->payload, 2));
    fwrite(STDOUT, "CPT disable-invalid PASS\n");
    return;
}

if ($mode === 'disable-active') {
    cptRuntimeExpect($status->passed(), 'compilation must recover after invalid definition is disabled');
    $existing = $definitions->get($bookId);
    cptRuntimeExpect($existing instanceof Definition, 'book definition must exist for disable transition');
    $definitions->save(cptRuntimeDefinition($bookId, $existing->slug, DefinitionStatus::Disabled, $existing->payload, 2));
    fwrite(STDOUT, "CPT disable-active PASS\n");
    return;
}

if ($mode === 'verify-disabled') {
    cptRuntimeExpect($status->passed(), 'disabled-only definition set must compile successfully');
    cptRuntimeExpect(!post_type_exists('library_book'), 'disabled CPT must not register on the next request');
    $retained = $definitions->get($bookId);
    cptRuntimeExpect($retained instanceof Definition && $retained->status === DefinitionStatus::Disabled, 'disabled definition must remain persisted');
    cptRuntimeExpect($retained->payload === $bookPayload, 'disable must retain canonical CPT configuration payload');
    cptRuntimeExpect($runtime->forKind(RegistrationKind::PostType) === [], 'compiled post type manifest must be empty after disable');

    $evidencePath = getenv('WPE_CPT_EVIDENCE_PATH') ?: '';
    if ($evidencePath !== '') {
        $directory = dirname($evidencePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $evidence = [
            'schema' => 'wpessential-cpt-runtime-evidence-v1',
            'result' => 'PASS',
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'module_state' => $kernel->modules()->state('custom-post-types')?->value,
            'definition_type' => CustomPostTypeDefinitionProjector::DEFINITION_TYPE,
            'owner_surface_id' => CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
            'post_type_key' => 'library_book',
            'disabled_definition_retained' => true,
            'compiled_post_type_count' => count($runtime->forKind(RegistrationKind::PostType)),
        ];
        file_put_contents($evidencePath, json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
    fwrite(STDOUT, "CPT verify-disabled PASS\n");
    return;
}

fwrite(STDERR, "FAIL: unknown WPE_CPT_TEST_MODE {$mode}\n");
exit(1);
