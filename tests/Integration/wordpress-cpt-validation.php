<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}
if (!function_exists('get_bloginfo')) {
    fwrite(STDERR, "FAIL: WordPress must be loaded before the CPT validation integration test.\n");
    exit(1);
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeDefinitionProjector;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;

function cptValidationExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @param mixed $data */
function cptValidationIssueIds(mixed $data): array
{
    if (!is_array($data) || !is_array($data['issues'] ?? null)) {
        return [];
    }

    $ids = [];
    foreach ($data['issues'] as $issue) {
        if (is_array($issue) && is_string($issue['id'] ?? null)) {
            $ids[] = $issue['id'];
        }
    }
    return $ids;
}

$kernel = \WPEssential\Bootstrap\Plugin::kernel();
cptValidationExpect($kernel instanceof Kernel && $kernel->isBooted(), 'production plugin kernel must be booted');
$services = $kernel->services();
$definitions = $services->get('platform.definitions');
$ajax = $services->get('platform.ajax.dispatcher');
cptValidationExpect($definitions instanceof DefinitionRepositoryInterface, 'shared Definition Repository must be available');
cptValidationExpect($ajax instanceof AjaxDispatcher, 'shared AJAX dispatcher must be available');

$admin = get_user_by('login', 'wpessential_admin');
cptValidationExpect($admin instanceof WP_User, 'WordPress fixture must provide the administrator user');
wp_set_current_user((int) $admin->ID);
cptValidationExpect(current_user_can('manage_options'), 'validation principal must have manage_options');

$before = count($definitions->byType(CustomPostTypeDefinitionProjector::DEFINITION_TYPE));
$validationNonce = $ajax->createNonce('cpt.validate');

$invalid = $ajax->dispatch([
    'type' => 'cpt.validate',
    'nonce' => $validationNonce,
    'payload' => [
        'payload' => [
            'post_type_key' => 'post',
            'name' => 'Posts',
            'singular_name' => 'Post',
        ],
    ],
], true);
cptValidationExpect($invalid->success, 'validation route must return a structured report rather than transport failure');
cptValidationExpect(is_array($invalid->data) && ($invalid->data['valid'] ?? true) === false, 'reserved core key must be blocked by validation report');
cptValidationExpect(in_array('registration_schema_invalid', cptValidationIssueIds($invalid->data), true), 'reserved key report must include registration schema issue');

register_post_type('external_book', ['public' => true]);
cptValidationExpect(post_type_exists('external_book'), 'external collision fixture must be active');
$collision = $ajax->dispatch([
    'type' => 'cpt.validate',
    'nonce' => $validationNonce,
    'payload' => [
        'payload' => [
            'post_type_key' => 'external_book',
            'name' => 'External Books',
            'singular_name' => 'External Book',
        ],
    ],
], true);
cptValidationExpect($collision->success, 'runtime collision must remain a validation report');
cptValidationExpect(is_array($collision->data) && ($collision->data['valid'] ?? true) === false, 'unknown runtime owner must block candidate registration');
cptValidationExpect(in_array('runtime_registration_collision', cptValidationIssueIds($collision->data), true), 'runtime collision report must identify collision');

$warning = $ajax->dispatch([
    'type' => 'cpt.validate',
    'nonce' => $validationNonce,
    'payload' => [
        'payload' => [
            'post_type_key' => 'catalog_item',
            'name' => 'Catalog Items',
            'singular_name' => 'Catalog Item',
            'taxonomies' => ['missing_catalog_tax'],
        ],
    ],
], true);
cptValidationExpect($warning->success, 'missing taxonomy must remain a validation report');
cptValidationExpect(is_array($warning->data) && ($warning->data['valid'] ?? false) === true, 'missing taxonomy must degrade rather than block canonical CPT definition');
cptValidationExpect(in_array('missing_taxonomy', cptValidationIssueIds($warning->data), true), 'missing taxonomy report must identify degraded dependency');

$afterValidation = count($definitions->byType(CustomPostTypeDefinitionProjector::DEFINITION_TYPE));
cptValidationExpect($afterValidation === $before, 'validation route must not persist or mutate CPT definitions');

$saveNonce = $ajax->createNonce('cpt.save');
$blockedSave = $ajax->dispatch([
    'type' => 'cpt.save',
    'nonce' => $saveNonce,
    'payload' => [
        'payload' => [
            'post_type_key' => 'external_book',
            'name' => 'External Books',
            'singular_name' => 'External Book',
        ],
        'status' => 'draft',
    ],
], true);
cptValidationExpect(!$blockedSave->success && $blockedSave->errorCode === 'handler_failure', 'direct save must not bypass blocking runtime collision validation');
cptValidationExpect(count($definitions->byType(CustomPostTypeDefinitionProjector::DEFINITION_TYPE)) === $before, 'blocked direct save must not persist a definition');

$draftSave = $ajax->dispatch([
    'type' => 'cpt.save',
    'nonce' => $saveNonce,
    'payload' => [
        'payload' => [
            'post_type_key' => 'publish_probe',
            'name' => 'Publish Probes',
            'singular_name' => 'Publish Probe',
        ],
        'status' => 'draft',
    ],
], true);
cptValidationExpect($draftSave->success && is_array($draftSave->data), 'non-colliding draft fixture must save through the canonical mutation route');
$draftData = $draftSave->data['definition'] ?? null;
cptValidationExpect(is_array($draftData) && is_string($draftData['id'] ?? null), 'draft save must return its canonical definition id');
$draftId = (string) $draftData['id'];

register_post_type('publish_probe', ['public' => true]);
cptValidationExpect(post_type_exists('publish_probe'), 'publish collision fixture must be active');
$statusNonce = $ajax->createNonce('cpt.status');
$blockedPublish = $ajax->dispatch([
    'type' => 'cpt.status',
    'nonce' => $statusNonce,
    'payload' => [
        'id' => $draftId,
        'expected_revision' => 1,
        'status' => 'published',
    ],
], true);
cptValidationExpect(!$blockedPublish->success && $blockedPublish->errorCode === 'handler_failure', 'Draft to Published transition must not bypass runtime collision validation');
$retainedDraft = $definitions->get($draftId);
cptValidationExpect($retainedDraft instanceof Definition, 'blocked publish fixture must remain persisted');
cptValidationExpect($retainedDraft->status === DefinitionStatus::Draft && $retainedDraft->revision === 1, 'blocked publish must retain the previous draft revision and lifecycle state');

$evidencePath = getenv('WPE_CPT_VALIDATION_EVIDENCE_PATH') ?: '';
if ($evidencePath !== '') {
    $directory = dirname($evidencePath);
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
    file_put_contents($evidencePath, json_encode([
        'schema' => 'wpessential-cpt-validation-evidence-v1',
        'result' => 'PASS',
        'wordpress_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
        'reserved_key_blocked' => true,
        'unknown_runtime_owner_blocked' => true,
        'missing_taxonomy_degraded' => true,
        'validation_non_mutating' => $afterValidation === $before,
        'direct_save_collision_blocked' => true,
        'publish_transition_collision_blocked' => true,
        'definition_count_before' => $before,
        'definition_count_after_validation' => $afterValidation,
        'publish_probe_status' => $retainedDraft->status->value,
        'publish_probe_revision' => $retainedDraft->revision,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
}

fwrite(STDOUT, "WPEssential real WordPress CPT validation and mutation guard PASS\n");
