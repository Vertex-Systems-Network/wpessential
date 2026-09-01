<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Fields runtime reference core SKIP (WordPress fixture unavailable)\n");
    exit(0);
}

$fixturePath = (string) getenv('WPE_FIELDS_REFERENCE_FIXTURE');
$mode = (string) getenv('WPE_FIELDS_REFERENCE_MODE');
if ($fixturePath === '' || !is_file($fixturePath) || !in_array($mode, ['success', 'collision'], true)) {
    fwrite(STDERR, "FAIL: runtime reference child fixture is incomplete\n");
    exit(1);
}

require $wpDir . '/wp-load.php';

use WPEssential\Bootstrap\Plugin;
use WPEssential\Modules\Fields\FieldGroupRuntimeRegistrar;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Modules\ModuleState;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

function fieldsRuntimeCoreExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$fixture = json_decode((string) file_get_contents($fixturePath), true, flags: JSON_THROW_ON_ERROR);
fieldsRuntimeCoreExpect(is_array($fixture), 'reference fixture must decode to a map');

$kernel = Plugin::kernel();
fieldsRuntimeCoreExpect($kernel !== null, 'must-use contributor must boot WPEssential before WordPress init completes');
fieldsRuntimeCoreExpect($kernel->modules()->has('custom-fields'), 'concrete Fields module must be admitted through external policy');
fieldsRuntimeCoreExpect($kernel->modules()->state('custom-fields') === ModuleState::Booted, 'admitted Fields module must complete lifecycle boot');

$services = $kernel->services();
$runtime = $services->get('module.custom-fields.runtime.post-meta');
fieldsRuntimeCoreExpect($runtime instanceof FieldGroupRuntimeRegistrar, 'Fields module must expose its runtime registrar service');
fieldsRuntimeCoreExpect($runtime->processed(), 'Fields runtime registrar must execute automatically from init hook');

$postType = (string) ($fixture['post_type'] ?? '');
$groupId = (string) ($fixture['group_id'] ?? '');
$groupRevision = (int) ($fixture['group_revision'] ?? 0);
$headlineUuid = (string) ($fixture['headline_uuid'] ?? '');
$scoreUuid = (string) ($fixture['score_uuid'] ?? '');
$adminId = (int) ($fixture['admin_id'] ?? 0);
fieldsRuntimeCoreExpect($postType === 'wpe_ref_book', 'reference post type fixture must match expected key');
fieldsRuntimeCoreExpect($groupRevision > 0 && $adminId > 0, 'reference revision/admin fixture must be valid');
fieldsRuntimeCoreExpect(post_type_exists($postType), 'compiled CPT runtime must register target before Fields init priority 30');

$registered = get_registered_meta_keys('post', $postType);
if ($mode === 'collision') {
    fieldsRuntimeCoreExpect($runtime->bound() === [], 'combined collision must not report any Field Group as bound');
    $errors = $runtime->errors();
    fieldsRuntimeCoreExpect(isset($errors['runtime']), 'combined collision must surface inspectable runtime error state');
    fieldsRuntimeCoreExpect(str_contains($errors['runtime'], 'duplicate key "wpe_ref_collision"'), 'runtime error must identify duplicate storage key');
    fieldsRuntimeCoreExpect(!isset($registered['wpe_ref_collision']), 'colliding key must never reach native registration');
    fieldsRuntimeCoreExpect(!isset($registered['wpe_ref_headline']), 'combined plan collision must fail before the first otherwise-valid Field registration');
    fieldsRuntimeCoreExpect(!isset($registered['wpe_ref_score']), 'combined plan collision must prevent partial Field registration');
    fwrite(STDOUT, "WPEssential Fields runtime reference collision PASS\n");
    exit(0);
}

fieldsRuntimeCoreExpect($runtime->errors() === [], 'successful reference runtime must not report registration errors');
fieldsRuntimeCoreExpect($runtime->bound() === [$groupId], 'runtime must bind only the canonical Published Surface 3 group');
fieldsRuntimeCoreExpect(isset($registered['wpe_ref_headline']), 'Published headline Field must bind automatically to target subtype');
fieldsRuntimeCoreExpect(isset($registered['wpe_ref_score']), 'Published score Field must bind automatically to target subtype');
fieldsRuntimeCoreExpect(!isset($registered['wpe_ref_draft']), 'Draft Field Group must not bind runtime metadata');
fieldsRuntimeCoreExpect(!isset($registered['wpe_ref_foreign']), 'foreign-owner Field Group must not bind runtime metadata');
fieldsRuntimeCoreExpect(($registered['wpe_ref_headline']['type'] ?? null) === 'string', 'headline native registration type must remain string');
fieldsRuntimeCoreExpect(($registered['wpe_ref_headline']['single'] ?? null) === true, 'headline must retain single-row storage');
fieldsRuntimeCoreExpect(($registered['wpe_ref_headline']['revisions_enabled'] ?? null) === true, 'group revision policy must reach native registration');
fieldsRuntimeCoreExpect(($registered['wpe_ref_score']['type'] ?? null) === 'integer', 'integer number Field must retain native integer type');

$restMeta = new WP_REST_Post_Meta_Fields($postType);
$restSchema = $restMeta->get_field_schema();
$restFields = $restSchema['properties'] ?? [];
fieldsRuntimeCoreExpect(is_array($restFields), 'real WordPress REST meta schema must expose properties');
fieldsRuntimeCoreExpect(isset($restFields['wpe_ref_headline']), 'automatically bound headline must be present in REST meta schema');
fieldsRuntimeCoreExpect(isset($restFields['wpe_ref_score']), 'automatically bound score must be present in REST meta schema');
fieldsRuntimeCoreExpect(!isset($restFields['wpe_ref_draft']), 'Draft group must remain absent from REST schema');
fieldsRuntimeCoreExpect(!isset($restFields['wpe_ref_foreign']), 'foreign-owner group must remain absent from REST schema');

wp_set_current_user($adminId);
$postId = wp_insert_post([
    'post_type' => $postType,
    'post_status' => 'publish',
    'post_title' => 'Fields Runtime Reference Target',
], true);
fieldsRuntimeCoreExpect(is_int($postId) && $postId > 0, 'real target post must be created');

fieldsRuntimeCoreExpect(function_exists('wp_get_ability'), 'native WordPress Ability lookup must be available');
$writeAbility = wp_get_ability('wpessential/fields-write-value');
$readAbility = wp_get_ability('wpessential/fields-read-value');
fieldsRuntimeCoreExpect($writeAbility instanceof WP_Ability, 'Fields write Ability must auto-register through early module boot');
fieldsRuntimeCoreExpect($readAbility instanceof WP_Ability, 'Fields read Ability must auto-register through early module boot');

$writeInput = [
    'group_id' => $groupId,
    'field_uuid' => $headlineUuid,
    'post_id' => $postId,
    'expected_group_revision' => $groupRevision,
    'value' => '  Runtime Reference Value  ',
];
fieldsRuntimeCoreExpect($writeAbility->check_permissions($writeInput) === true, 'administrator must pass real native write Ability permission');
$written = $writeAbility->execute($writeInput);
fieldsRuntimeCoreExpect(!is_wp_error($written), 'native write Ability must execute against automatically bound target');
fieldsRuntimeCoreExpect(is_array($written) && ($written['value'] ?? null) === 'Runtime Reference Value', 'native write Ability must return canonical value');
fieldsRuntimeCoreExpect(get_post_meta($postId, 'wpe_ref_headline', true) === 'Runtime Reference Value', 'native write Ability must persist to automatically registered meta');

$readInput = [
    'group_id' => $groupId,
    'field_uuid' => $headlineUuid,
    'post_id' => $postId,
];
$read = $readAbility->execute($readInput);
fieldsRuntimeCoreExpect(!is_wp_error($read), 'native read Ability must execute for target post');
fieldsRuntimeCoreExpect(is_array($read) && ($read['value'] ?? null) === 'Runtime Reference Value', 'native read Ability must return persisted canonical value');

$abilities = $services->get('platform.abilities');
$contexts = $services->get('platform.abilities.contexts');
fieldsRuntimeCoreExpect($abilities instanceof AbilityRegistry, 'shared internal Ability Registry must remain available');
fieldsRuntimeCoreExpect($contexts instanceof WordPressExecutionContextFactory, 'shared WordPress context factory must remain available');
$scoreWrite = $abilities->execute('wpessential/fields/write-value', [
    'group_id' => $groupId,
    'field_uuid' => $scoreUuid,
    'post_id' => $postId,
    'expected_group_revision' => $groupRevision,
    'value' => 9,
], $contexts->current());
fieldsRuntimeCoreExpect(is_array($scoreWrite) && ($scoreWrite['value'] ?? null) === 9, 'internal Ability path must write second automatically bound Field');
fieldsRuntimeCoreExpect(get_post_meta($postId, 'wpe_ref_score', true) === '9' || get_post_meta($postId, 'wpe_ref_score', true) === 9, 'WordPress must persist integer Field value');

$pageId = wp_insert_post([
    'post_type' => 'page',
    'post_status' => 'publish',
    'post_title' => 'Fields Runtime Non Target',
], true);
fieldsRuntimeCoreExpect(is_int($pageId) && $pageId > 0, 'real non-target post must be created');
$mismatchInput = $writeInput;
$mismatchInput['post_id'] = $pageId;
$mismatchInput['value'] = 'Must not persist';
$mismatch = $writeAbility->execute($mismatchInput);
fieldsRuntimeCoreExpect(is_wp_error($mismatch), 'native write Ability must fail closed for non-target post');
fieldsRuntimeCoreExpect(get_post_meta($pageId, 'wpe_ref_headline', true) === '', 'non-target Ability failure must not persist metadata');

fwrite(STDOUT, "WPEssential Fields runtime reference success PASS\n");
