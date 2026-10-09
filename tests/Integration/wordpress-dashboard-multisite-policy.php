<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDERR, "FAIL: Real WordPress Multisite fixture is unavailable\n");
    exit(1);
}
if (!defined('ABSPATH')) {
    define('ABSPATH', $wpDir . '/');
}
$host = trim((string) getenv('WPE_TEST_WP_HOST'));
$host = $host !== '' ? $host : 'wpessential.test';
$_SERVER['HTTP_HOST'] = $host;
$_SERVER['SERVER_NAME'] = $host;
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
require $wpDir . '/wp-load.php';

$root = dirname(__DIR__, 2);
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = $root . '/frameworks/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisiteEffectivePresetResolver;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetSubsitePresetOverrideCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetSubsitePresetOverrideDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetMultisitePolicyResolver;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityReadService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetReadService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetResolver;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Modules\DashboardWidgets\WordPressDashboardWidgetRoleMembershipProvider;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\NativeWordPressAbilityEnvironment;
use WPEssential\Platform\WordPress\Abilities\WordPressCapabilityChecker;

function dashboardMultisiteExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

dashboardMultisiteExpect(is_multisite(), 'fixture must be real WordPress Multisite');
$sites = get_sites(['number' => 10, 'orderby' => 'id', 'order' => 'ASC']);
dashboardMultisiteExpect(count($sites) >= 2, 'fixture must contain two sites');
$siteA = (int) $sites[0]->blog_id;
$siteB = (int) $sites[1]->blog_id;
dashboardMultisiteExpect($siteA > 0 && $siteB > 0 && $siteA !== $siteB, 'site ids must be distinct');

$admin = get_user_by('login', 'wpessential_admin');
dashboardMultisiteExpect($admin instanceof WP_User, 'fixture admin must exist');
$adminId = (int) $admin->ID;
wp_set_current_user($adminId);
dashboardMultisiteExpect(is_super_admin($adminId), 'fixture user must be the network admin');
$networkId = (int) get_current_network_id();
dashboardMultisiteExpect($networkId > 0, 'WordPress network id must be positive');

$widgetId = '11111111-1111-4111-8111-111111111111';
$presetId = '22222222-2222-4222-8222-222222222222';
$policyId = '33333333-3333-4333-8333-333333333333';
$repo = new InMemoryDefinitionRepository();
$repo->save(new Definition(
    id: $widgetId,
    slug: 'widget',
    type: DashboardWidgetDefinition::TYPE,
    schemaVersion: 1,
    ownerSurfaceId: 10,
    status: DefinitionStatus::Published,
    payload: [],
));
$repo->save(new Definition(
    id: $presetId,
    slug: 'network-preset',
    type: DashboardWidgetPresetDefinition::TYPE,
    schemaVersion: 1,
    ownerSurfaceId: 10,
    status: DefinitionStatus::Published,
    payload: ['preset' => [
        'label' => 'Network fixture preset',
        'widget_definition_ids' => [$widgetId],
        'assignment' => ['network_default' => true],
    ]],
    revision: 2,
));
$data = [
    'blueprint_site_id' => $siteA,
    'network_preset_id' => $presetId,
    'exclude_site_ids' => [$siteB],
    'subsite_override' => true,
    'settings_capability' => 'manage_options',
];
$makePolicy = static function (array $payload, int $revision = 3, string $id = '33333333-3333-4333-8333-333333333333'): Definition {
    return new Definition(
        id: $id,
        slug: $id === '33333333-3333-4333-8333-333333333333' ? 'policy' : 'alternate-policy',
        type: DashboardWidgetMultisitePolicyDefinition::TYPE,
        schemaVersion: 1,
        ownerSurfaceId: 10,
        status: DefinitionStatus::Published,
        payload: ['multisite' => $payload],
        revision: $revision,
    );
};
$repo->save($makePolicy($data));
$compiler = new DashboardWidgetMultisitePolicyCompiler($repo, new DashboardWidgetPresetCompiler($repo));
$resolver = new DashboardWidgetMultisitePolicyResolver(
    $repo,
    $compiler,
    new WordPressDashboardWidgetRoleMembershipProvider(),
    new WordPressCapabilityChecker(new NativeWordPressAbilityEnvironment()),
);

$preferences = static fn (int $id): array => [
    get_user_option('meta-box-order_dashboard', $id),
    get_user_option('closedpostboxes_dashboard', $id),
    get_user_option('metaboxhidden_dashboard', $id),
];
$fixtureBefore = [];

switch_to_blog($siteA);
wp_set_current_user($adminId);
$fixtureBefore[$siteA] = $preferences($adminId);
$contextA = new ExecutionContext(new Principal($adminId), $siteA, networkId: $networkId);
$resolvedA = $resolver->resolve($contextA)->toArray();
dashboardMultisiteExpect($resolvedA['status'] === 'resolved', 'blueprint site must resolve policy');
dashboardMultisiteExpect($resolvedA['policy_id'] === $policyId, 'policy id must be canonical');
dashboardMultisiteExpect($resolvedA['network_preset_id'] === $presetId, 'Published network-default preset must resolve');
dashboardMultisiteExpect($resolvedA['network_preset_revision'] === 2, 'preset revision must be preserved');
dashboardMultisiteExpect($resolvedA['widget_definition_ids'] === [$widgetId], 'preset widget order must be read-only');
dashboardMultisiteExpect($resolvedA['can_manage_override'] === true, 'network admin should pass settings capability');
dashboardMultisiteExpect(
    $resolver->resolve(new ExecutionContext(new Principal($adminId), $siteA, networkId: $networkId + 1))->status === 'unavailable',
    'wrong network identity must fail closed',
);
dashboardMultisiteExpect(
    $resolver->resolve(new ExecutionContext(new Principal($adminId + 100), $siteA, networkId: $networkId))->status === 'unavailable',
    'wrong user identity must fail closed',
);
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteA], 'site A native preferences must not mutate');
restore_current_blog();

switch_to_blog($siteB);
wp_set_current_user($adminId);
$fixtureBefore[$siteB] = $preferences($adminId);
$contextB = new ExecutionContext(new Principal($adminId), $siteB, networkId: $networkId);
$excludedB = $resolver->resolve($contextB)->toArray();
dashboardMultisiteExpect($excludedB['status'] === 'excluded', 'excluded site must reject network policy');
dashboardMultisiteExpect($excludedB['network_preset_id'] === null, 'excluded site must not expose applied preset evidence');
dashboardMultisiteExpect($excludedB['widget_definition_ids'] === [], 'excluded site must not get widget order');
dashboardMultisiteExpect($excludedB['can_manage_override'] === false, 'excluded site must not allow policy override');
dashboardMultisiteExpect(
    $resolver->resolve(new ExecutionContext(new Principal($adminId), $siteA, networkId: $networkId))->status === 'unavailable',
    'site spoofing must be unavailable after switching blogs',
);
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteB], 'site B native preferences must not mutate');
restore_current_blog();

// RB-0100: exercise explicitly scoped site preset inheritance in the real WordPress fixture.
$localWidgetId = '88888888-8888-4888-8888-888888888888';
$localPresetId = '99999999-9999-4999-8999-999999999999';
$overrideId = '55555555-5555-4555-8555-555555555555';
$repo->save(new Definition(
    id: $localWidgetId,
    slug: 'site-local-widget',
    type: DashboardWidgetDefinition::TYPE,
    schemaVersion: 1,
    ownerSurfaceId: 10,
    status: DefinitionStatus::Published,
    payload: [],
));
$repo->save(new Definition(
    id: $localPresetId,
    slug: 'site-local-preset',
    type: DashboardWidgetPresetDefinition::TYPE,
    schemaVersion: 1,
    ownerSurfaceId: 10,
    status: DefinitionStatus::Published,
    payload: ['preset' => [
        'label' => 'Explicit scoped site preset',
        'widget_definition_ids' => [$localWidgetId, $widgetId],
        'assignment' => ['network_default' => false, 'roles' => []],
    ]],
    revision: 2,
));
$overridePayload = [
    'network_id' => $networkId,
    'site_id' => $siteB,
    'policy_id' => $policyId,
    'preset_id' => $localPresetId,
];
$makeOverride = static function (
    array $payload,
    string $id = '55555555-5555-4555-8555-555555555555',
    DefinitionStatus $status = DefinitionStatus::Published,
    int $revision = 1,
): Definition {
    return new Definition(
        id: $id,
        slug: $id === '55555555-5555-4555-8555-555555555555' ? 'site-local-override' : 'site-other-override',
        type: DashboardWidgetSubsitePresetOverrideDefinition::TYPE,
        schemaVersion: 1,
        ownerSurfaceId: 10,
        status: $status,
        payload: ['subsite_preset_override' => $payload],
        revision: $revision,
    );
};
$repo->save($makeOverride($overridePayload));
$effectivePreset = new DashboardWidgetMultisiteEffectivePresetResolver(
    $resolver,
    $repo,
    new DashboardWidgetSubsitePresetOverrideCompiler($repo, new DashboardWidgetPresetCompiler($repo)),
);

switch_to_blog($siteB);
wp_set_current_user($adminId);
$excludedSelection = $effectivePreset->resolve($contextB);
dashboardMultisiteExpect($excludedSelection['status'] === 'excluded', 'excluded site must not receive even its local preset');
dashboardMultisiteExpect($excludedSelection['preset_id'] === null, 'excluded site must not leak local override preset reference');
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteB], 'excluded local preset must not mutate native preferences');
restore_current_blog();

// The subsite is now explicitly included, and its immutable local preset should win.
$repo->save($makePolicy(array_replace($data, ['exclude_site_ids' => []]), 4));
switch_to_blog($siteB);
wp_set_current_user($adminId);
$localSelection = $effectivePreset->resolve($contextB);
dashboardMultisiteExpect($localSelection['status'] === 'resolved', 'included subsite must resolve');
dashboardMultisiteExpect($localSelection['source'] === 'subsite_override', 'explicit scoped site preset must win inheritance');
dashboardMultisiteExpect($localSelection['preset_id'] === $localPresetId, 'local preset ref must be exact');
dashboardMultisiteExpect($localSelection['widget_definition_ids'] === [$localWidgetId, $widgetId], 'local preset order must stay typed');
dashboardMultisiteExpect($localSelection['can_manage_override'] === true, 'network admin capability must remain separately checked');
dashboardMultisiteExpect(
    $effectivePreset->resolve(new ExecutionContext(new Principal($adminId), $siteB, networkId: $networkId + 1))['status'] === 'unavailable',
    'spoofed network may not inherit local preset',
);
dashboardMultisiteExpect(
    $effectivePreset->resolve(new ExecutionContext(new Principal($adminId + 1), $siteB, networkId: $networkId))['status'] === 'unavailable',
    'spoofed user may not inherit local preset',
);
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteB], 'local preset reads must preserve WordPress preferences');
restore_current_blog();

switch_to_blog($siteA);
wp_set_current_user($adminId);
$inheritedBlueprint = $effectivePreset->resolve($contextA);
dashboardMultisiteExpect($inheritedBlueprint['source'] === 'network_inherited', 'blueprint site must keep network preset');
dashboardMultisiteExpect($inheritedBlueprint['preset_id'] === $presetId, 'blueprint must preserve selected network preset');
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteA], 'blueprint policy read must not mutate preferences');
restore_current_blog();

// Foreign network/site/policy payloads must never displace the matching local override.
$repo->save($makeOverride(
    array_replace($overridePayload, ['network_id' => $networkId + 10]),
    'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
));
$repo->save($makeOverride(
    array_replace($overridePayload, ['site_id' => $siteA]),
    'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
));
$repo->save($makeOverride(
    array_replace($overridePayload, ['policy_id' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd']),
    'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
));

switch_to_blog($siteB);
wp_set_current_user($adminId);
dashboardMultisiteExpect($effectivePreset->resolve($contextB)['preset_id'] === $localPresetId, 'foreign overrides must be ignored');
$duplicateId = 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee';
$repo->save($makeOverride($overridePayload, $duplicateId));
$conflictSelection = $effectivePreset->resolve($contextB);
dashboardMultisiteExpect($conflictSelection['status'] === 'conflict', 'duplicate exact site override must fail closed');
dashboardMultisiteExpect($conflictSelection['preset_id'] === null, 'conflict must not leak preset details');
$repo->save($makeOverride($overridePayload, $duplicateId, DefinitionStatus::Draft, 2));

$invalidId = 'ffffffff-ffff-4fff-8fff-ffffffffffff';
$repo->save($makeOverride(['site_id' => $siteB], $invalidId));
$badSelection = $effectivePreset->resolve($contextB);
dashboardMultisiteExpect($badSelection['status'] === 'invalid_catalog', 'malformed Published override must fail closed');
dashboardMultisiteExpect($badSelection['widget_definition_ids'] === [], 'malformed catalog must reveal no widget ids');
$repo->save($makeOverride(['site_id' => $siteB], $invalidId, DefinitionStatus::Draft, 2));
dashboardMultisiteExpect($effectivePreset->resolve($contextB)['source'] === 'subsite_override', 'Draft records must not affect resolution');

$repo->save($makePolicy(array_replace($data, ['exclude_site_ids' => [], 'subsite_override' => false]), 5));
$disabledLocal = $effectivePreset->resolve($contextB);
dashboardMultisiteExpect($disabledLocal['source'] === 'network_inherited', 'disabled site overrides must inherit network preset');
dashboardMultisiteExpect($disabledLocal['preset_id'] === $presetId, 'disabled site override must not select local preset');
dashboardMultisiteExpect($disabledLocal['can_manage_override'] === false, 'disabled override may not elevate capability');
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteB], 'all site override reads preserve native preferences');
restore_current_blog();

$repo->save($makePolicy(array_replace($data, ['exclude_site_ids' => [], 'subsite_override' => false]), 6));
switch_to_blog($siteA);
wp_set_current_user($adminId);
$disabled = $resolver->resolve($contextA)->toArray();
dashboardMultisiteExpect($disabled['status'] === 'resolved', 'read-only policy remains resolved when overrides disabled');
dashboardMultisiteExpect(!$disabled['can_manage_override'], 'disabled override may not elevate rights');

$repo->save($makePolicy($data, 3, '44444444-4444-4444-8444-444444444444'));
dashboardMultisiteExpect($resolver->resolve($contextA)->status === 'conflict', 'multiple Published policies must fail closed');
$repo->save($makePolicy(['blueprint_site_id' => $siteA], 7));
dashboardMultisiteExpect($resolver->resolve($contextA)->status === 'invalid_catalog', 'malformed Published catalog must fail closed');
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteA], 'all read-only policy decisions must preserve WordPress preferences');
restore_current_blog();

// RB-0102: Verify deterministic bounded Published preset snapshot reads using
// actual WordPress two-site contexts. No WordPress preference or Definition writes
// are performed by the export service; fixture-only revisions below are explicit.
$portablePresetCompiler = new DashboardWidgetPresetCompiler($repo);
$portablePresetReader = new DashboardWidgetPresetReadService(
    $repo,
    $portablePresetCompiler,
    new DashboardWidgetPresetResolver(
        $repo,
        $portablePresetCompiler,
        new WordPressDashboardWidgetRoleMembershipProvider(),
    ),
);
$portableSnapshotReader = new DashboardWidgetPresetPortabilityReadService($portablePresetReader);
$networkBefore = $repo->get($presetId);
$siteBefore = $repo->get($localPresetId);
$networkSnapshot = $portableSnapshotReader->snapshot($presetId);
$siteSnapshot = $portableSnapshotReader->snapshot($localPresetId);
dashboardMultisiteExpect(is_array($networkSnapshot) && is_array($siteSnapshot), 'Published network and local presets must export');
dashboardMultisiteExpect($networkSnapshot['format'] === 'wpessential-dashboard-preset', 'portable snapshot format V1 must be explicit');
dashboardMultisiteExpect($networkSnapshot['version'] === 1, 'portable snapshot version must be exactly one');
dashboardMultisiteExpect($siteSnapshot['payload']['widget_definition_ids'] === [$localWidgetId, $widgetId], 'local snapshot retains Published widget order');
dashboardMultisiteExpect($siteSnapshot['payload']['assignment']['roles'] === [], 'unassigned local preset must have no role injection');
dashboardMultisiteExpect($networkSnapshot['payload']['assignment']['network_default'] === true, 'network preset exports validated network assignment');
dashboardMultisiteExpect($siteSnapshot['payload']['assignment']['network_default'] === false, 'local preset is not implicitly network-default');

foreach ([$siteA, $siteB] as $fixtureSiteId) {
    switch_to_blog($fixtureSiteId);
    wp_set_current_user($adminId);
    $preferencesBeforeSnapshot = $preferences($adminId);
    $activeBefore = get_current_blog_id();
    $userBefore = get_current_user_id();
    $contextBefore = new ExecutionContext(new Principal($adminId), $fixtureSiteId, networkId: $networkId);
    dashboardMultisiteExpect($contextBefore->siteId === $activeBefore, 'real site context must be bound');
    dashboardMultisiteExpect($portableSnapshotReader->snapshot($presetId) === $networkSnapshot, 'network snapshot must be deterministic per real WP site');
    dashboardMultisiteExpect($portableSnapshotReader->snapshot($localPresetId) === $siteSnapshot, 'local snapshot must remain deterministic per real WP site');
    dashboardMultisiteExpect($preferences($adminId) === $preferencesBeforeSnapshot, 'snapshot reads must not change WordPress dashboard preferences');
    dashboardMultisiteExpect(get_current_blog_id() === $activeBefore, 'snapshot must not switch WordPress site context');
    dashboardMultisiteExpect(get_current_user_id() === $userBefore, 'snapshot must not switch current WordPress user');
    dashboardMultisiteExpect((int) get_current_network_id() === $networkId, 'snapshot must not switch network');
    restore_current_blog();
}
dashboardMultisiteExpect($repo->get($presetId) === $networkBefore, 'network Definition remains unchanged by reads');
dashboardMultisiteExpect($repo->get($localPresetId) === $siteBefore, 'local Definition remains unchanged by reads');
dashboardMultisiteExpect(
    $siteSnapshot['sha256'] === hash('sha256', json_encode(
        $siteSnapshot['payload'],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    )),
    'portable SHA-256 fingerprint must match deterministic payload JSON',
);

$draftPresetId = '12121212-1212-4212-8212-121212121212';
$wrongOwnerPresetId = '13131313-1313-4313-8313-131313131313';
$portableDefinition = static function (
    string $id, DefinitionStatus $status, int $owner, int $revision, array $widgets
): Definition {
    return new Definition(
        id: $id,
        slug: 'portable-fixture-' . $id,
        type: DashboardWidgetPresetDefinition::TYPE,
        schemaVersion: 1,
        ownerSurfaceId: $owner,
        status: $status,
        payload: ['preset' => [
            'label' => 'Portable Fixture',
            'widget_definition_ids' => $widgets,
            'assignment' => ['roles' => [], 'network_default' => false],
        ]],
        revision: $revision,
    );
};
$repo->save($portableDefinition($draftPresetId, DefinitionStatus::Draft, 10, 1, [$widgetId]));
$repo->save($portableDefinition($wrongOwnerPresetId, DefinitionStatus::Published, 11, 1, [$widgetId]));
dashboardMultisiteExpect($portableSnapshotReader->snapshot($draftPresetId) === null, 'Draft preset must never export');
dashboardMultisiteExpect($portableSnapshotReader->snapshot($wrongOwnerPresetId) === null, 'foreign-owner preset must not export');
dashboardMultisiteExpect($portableSnapshotReader->snapshot($widgetId) === null, 'widget Definition cannot be exported as preset');
dashboardMultisiteExpect($portableSnapshotReader->snapshot('14141414-1414-4414-8414-141414141414') === null, 'unknown preset must not export');

// Only test-fixture in-memory records change to prove revision/order checksum
// sensitivity; the portability service itself never writes any Definition.
$repo->save($portableDefinition($localPresetId, DefinitionStatus::Published, 10, 3, [$localWidgetId, $widgetId]));
$revisionSnapshot = $portableSnapshotReader->snapshot($localPresetId);
dashboardMultisiteExpect($revisionSnapshot['sha256'] !== $siteSnapshot['sha256'], 'revision change must affect fingerprint');
dashboardMultisiteExpect($revisionSnapshot['payload']['revision'] === 3, 'portable snapshot must expose current canonical revision');
$repo->save($portableDefinition($localPresetId, DefinitionStatus::Published, 10, 4, [$widgetId, $localWidgetId]));
$orderSnapshot = $portableSnapshotReader->snapshot($localPresetId);
dashboardMultisiteExpect($orderSnapshot['sha256'] !== $revisionSnapshot['sha256'], 'widget order change must affect fingerprint');
dashboardMultisiteExpect($orderSnapshot['payload']['widget_definition_ids'] === [$widgetId, $localWidgetId], 'current ordered Published widget IDs preserved');
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteA], 'network admin native preferences unchanged after snapshot verification');

$summary = [
    'contract' => 'RB-0098-real-wordpress-multisite-policy-isolation-v1',
    'wordpress' => get_bloginfo('version'),
    'php' => PHP_VERSION,
    'sites' => 2,
    'resolved_blueprint' => true,
    'excluded_subsite' => true,
    'identity_bound' => true,
    'capability_gated' => true,
    'invalid_catalog_fail_closed' => true,
    'native_preferences_unchanged' => true,
    'rb0100_subsite_override_precedence' => true,
    'rb0100_cross_site_network_isolation' => true,
    'rb0100_no_native_preference_mutation' => true,
    'rb0102_published_portability_snapshot' => true,
    'rb0102_cross_site_deterministic_fingerprint' => true,
    'rb0102_native_preferences_unchanged' => true,
];
$evidencePath = trim((string) getenv('WPE_DASHBOARD_MULTISITE_EVIDENCE_PATH'));
if ($evidencePath !== '') {
    $dir = dirname($evidencePath);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        fwrite(STDERR, "FAIL: Could not prepare Multisite policy evidence directory\n");
        exit(1);
    }
    file_put_contents($evidencePath, json_encode($summary, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
}
echo "PASS: Dashboard Widgets real WordPress Multisite policy isolation V1\n";
