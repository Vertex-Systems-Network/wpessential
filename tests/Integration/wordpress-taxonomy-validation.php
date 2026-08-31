<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}
if (!function_exists('get_bloginfo')) {
    fwrite(STDERR, "FAIL: WordPress must be loaded before the Taxonomy validation integration test.\n");
    exit(1);
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;

function taxonomyValidationExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @param mixed $data @return list<string> */
function taxonomyValidationIssueIds(mixed $data): array
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
taxonomyValidationExpect($kernel instanceof Kernel && $kernel->isBooted(), 'production plugin kernel must be booted');
$services = $kernel->services();
$definitions = $services->get('platform.definitions');
$ajax = $services->get('platform.ajax.dispatcher');
taxonomyValidationExpect($definitions instanceof DefinitionRepositoryInterface, 'shared Definition Repository must be available');
taxonomyValidationExpect($ajax instanceof AjaxDispatcher, 'shared AJAX dispatcher must be available');

$admin = get_user_by('login', 'wpessential_admin');
taxonomyValidationExpect($admin instanceof WP_User, 'WordPress fixture must provide the administrator user');
wp_set_current_user((int) $admin->ID);
taxonomyValidationExpect(current_user_can('manage_options'), 'validation principal must have manage_options');

$before = count($definitions->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE));
$validationNonce = $ajax->createNonce('taxonomy.validate');

$invalid = $ajax->dispatch([
    'type' => 'taxonomy.validate',
    'nonce' => $validationNonce,
    'payload' => [
        'payload' => [
            'taxonomy_key' => 'category',
            'object_types' => ['post'],
            'name' => 'Categories',
            'singular_name' => 'Category',
        ],
    ],
], true);
taxonomyValidationExpect($invalid->success, 'validation route must return a structured report rather than transport failure');
taxonomyValidationExpect(is_array($invalid->data) && ($invalid->data['valid'] ?? true) === false, 'reserved core taxonomy key must be blocked');
taxonomyValidationExpect(in_array('registration_schema_invalid', taxonomyValidationIssueIds($invalid->data), true), 'reserved key report must include registration schema issue');

register_taxonomy('external_genre', ['post'], ['public' => true]);
taxonomyValidationExpect(taxonomy_exists('external_genre'), 'external taxonomy collision fixture must be active');
$collision = $ajax->dispatch([
    'type' => 'taxonomy.validate',
    'nonce' => $validationNonce,
    'payload' => [
        'payload' => [
            'taxonomy_key' => 'external_genre',
            'object_types' => ['post'],
            'name' => 'External Genres',
            'singular_name' => 'External Genre',
        ],
    ],
], true);
taxonomyValidationExpect($collision->success, 'runtime collision must remain a validation report');
taxonomyValidationExpect(is_array($collision->data) && ($collision->data['valid'] ?? true) === false, 'unknown runtime taxonomy owner must block candidate registration');
taxonomyValidationExpect(in_array('runtime_registration_collision', taxonomyValidationIssueIds($collision->data), true), 'runtime collision report must identify collision');

$warning = $ajax->dispatch([
    'type' => 'taxonomy.validate',
    'nonce' => $validationNonce,
    'payload' => [
        'payload' => [
            'taxonomy_key' => 'catalog_genre',
            'object_types' => ['missing_catalog_type'],
            'name' => 'Catalog Genres',
            'singular_name' => 'Catalog Genre',
        ],
    ],
], true);
taxonomyValidationExpect($warning->success, 'missing object type must remain a validation report');
taxonomyValidationExpect(is_array($warning->data) && ($warning->data['valid'] ?? false) === true, 'missing object type must degrade rather than block canonical Taxonomy definition');
taxonomyValidationExpect(in_array('missing_object_type', taxonomyValidationIssueIds($warning->data), true), 'missing object type report must identify degraded dependency');

$afterValidation = count($definitions->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE));
taxonomyValidationExpect($afterValidation === $before, 'validation route must not persist or mutate Taxonomy definitions');

$saveNonce = $ajax->createNonce('taxonomy.save');
$blockedSave = $ajax->dispatch([
    'type' => 'taxonomy.save',
    'nonce' => $saveNonce,
    'payload' => [
        'payload' => [
            'taxonomy_key' => 'external_genre',
            'object_types' => ['post'],
            'name' => 'External Genres',
            'singular_name' => 'External Genre',
        ],
        'status' => 'draft',
    ],
], true);
taxonomyValidationExpect(!$blockedSave->success && $blockedSave->errorCode === 'handler_failure', 'direct save must not bypass blocking runtime taxonomy collision validation');
taxonomyValidationExpect(count($definitions->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE)) === $before, 'blocked direct save must not persist a Taxonomy definition');

$draftSave = $ajax->dispatch([
    'type' => 'taxonomy.save',
    'nonce' => $saveNonce,
    'payload' => [
        'payload' => [
            'taxonomy_key' => 'publish_probe_tax',
            'object_types' => ['post'],
            'name' => 'Publish Probe Taxonomies',
            'singular_name' => 'Publish Probe Taxonomy',
        ],
        'status' => 'draft',
    ],
], true);
taxonomyValidationExpect($draftSave->success && is_array($draftSave->data), 'non-colliding draft fixture must save through the canonical mutation route');
$draftData = $draftSave->data['definition'] ?? null;
taxonomyValidationExpect(is_array($draftData) && is_string($draftData['id'] ?? null), 'draft save must return its canonical definition id');
$draftId = (string) $draftData['id'];

register_taxonomy('publish_probe_tax', ['post'], ['public' => true]);
taxonomyValidationExpect(taxonomy_exists('publish_probe_tax'), 'publish collision fixture must be active');
$statusNonce = $ajax->createNonce('taxonomy.status');
$blockedPublish = $ajax->dispatch([
    'type' => 'taxonomy.status',
    'nonce' => $statusNonce,
    'payload' => [
        'id' => $draftId,
        'expected_revision' => 1,
        'status' => 'published',
    ],
], true);
taxonomyValidationExpect(!$blockedPublish->success && $blockedPublish->errorCode === 'handler_failure', 'Draft to Published transition must not bypass runtime taxonomy collision validation');
$retainedDraft = $definitions->get($draftId);
taxonomyValidationExpect($retainedDraft instanceof Definition, 'blocked publish fixture must remain persisted');
taxonomyValidationExpect($retainedDraft->status === DefinitionStatus::Draft && $retainedDraft->revision === 1, 'blocked publish must retain the previous draft revision and lifecycle state');

$evidencePath = getenv('WPE_TAXONOMY_VALIDATION_EVIDENCE_PATH') ?: '';
if ($evidencePath !== '') {
    $directory = dirname($evidencePath);
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
    file_put_contents($evidencePath, json_encode([
        'schema' => 'wpessential-taxonomy-validation-evidence-v1',
        'result' => 'PASS',
        'wordpress_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
        'reserved_key_blocked' => true,
        'unknown_runtime_owner_blocked' => true,
        'missing_object_type_degraded' => true,
        'validation_non_mutating' => $afterValidation === $before,
        'direct_save_collision_blocked' => true,
        'publish_transition_collision_blocked' => true,
        'definition_count_before' => $before,
        'definition_count_after_validation' => $afterValidation,
        'publish_probe_status' => $retainedDraft->status->value,
        'publish_probe_revision' => $retainedDraft->revision,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
}

fwrite(STDOUT, "WPEssential real WordPress Taxonomy validation and mutation guard PASS\n");
