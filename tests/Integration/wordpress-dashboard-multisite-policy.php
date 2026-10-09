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
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityFreshnessService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetImportPreflightService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDraftImportService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDraftPublishReviewService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetMappedDraftImportService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityMappingPreviewService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetReadService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetResolver;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Modules\DashboardWidgets\WordPressDashboardWidgetRoleMembershipProvider;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Contracts\DefinitionCreateOnlyRepositoryInterface;
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

// RB-0104: exercise fingerprint *freshness*, not authenticity/import,
// across real two-site WordPress contexts with unchanged native preferences.
$freshnessReader = new DashboardWidgetPresetPortabilityFreshnessService($portableSnapshotReader);
$networkDefinitionBeforeFreshness = $repo->get($presetId);
$localDefinitionBeforeFreshness = $repo->get($localPresetId);
foreach ([$siteA, $siteB] as $fixtureSiteId) {
    switch_to_blog($fixtureSiteId);
    wp_set_current_user($adminId);
    $beforeSite = get_current_blog_id();
    $beforeUser = get_current_user_id();
    $beforeNativePreferences = $preferences($adminId);
    dashboardMultisiteExpect(
        $freshnessReader->check($presetId, $networkSnapshot['sha256']) === ['status' => 'match', 'current' => true],
        'Published network preset fingerprint must match in real site context',
    );
    dashboardMultisiteExpect(
        $freshnessReader->check($localPresetId, $siteSnapshot['sha256']) === ['status' => 'stale', 'current' => false],
        'Prior local preset digest must be stale after fixture revision/order changes',
    );
    dashboardMultisiteExpect(
        $freshnessReader->check($localPresetId, $orderSnapshot['sha256']) === ['status' => 'match', 'current' => true],
        'New local snapshot fingerprint must match exactly',
    );
    dashboardMultisiteExpect($preferences($adminId) === $beforeNativePreferences, 'freshness reads may not write native dashboard prefs');
    dashboardMultisiteExpect(get_current_blog_id() === $beforeSite, 'freshness check may not change current blog');
    dashboardMultisiteExpect(get_current_user_id() === $beforeUser, 'freshness check may not change current WP user');
    dashboardMultisiteExpect((int) get_current_network_id() === $networkId, 'freshness check may not switch WP network');
    restore_current_blog();
}
dashboardMultisiteExpect($repo->get($presetId) === $networkDefinitionBeforeFreshness, 'network preset Definition untouched by freshness');
dashboardMultisiteExpect($repo->get($localPresetId) === $localDefinitionBeforeFreshness, 'local preset Definition untouched by freshness');
dashboardMultisiteExpect(
    $freshnessReader->check($draftPresetId, $siteSnapshot['sha256']) === ['status' => 'unavailable', 'current' => false],
    'Draft preset may not satisfy freshness',
);
dashboardMultisiteExpect(
    $freshnessReader->check($wrongOwnerPresetId, $siteSnapshot['sha256']) === ['status' => 'unavailable', 'current' => false],
    'Foreign owner preset may not satisfy freshness',
);
dashboardMultisiteExpect(
    $freshnessReader->check($widgetId, $siteSnapshot['sha256']) === ['status' => 'unavailable', 'current' => false],
    'Widget type cannot be treated as a preset',
);
dashboardMultisiteExpect(
    $freshnessReader->check('15151515-1515-4515-8515-151515151515', $siteSnapshot['sha256']) === ['status' => 'unavailable', 'current' => false],
    'Unknown preset must remain unavailable',
);
foreach ([
    ['not-a-uuid', $siteSnapshot['sha256']],
    [$localPresetId, strtoupper($siteSnapshot['sha256'])],
    [$localPresetId, 'invalid'],
    [strtoupper('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $siteSnapshot['sha256']],
] as [$id, $fingerprint]) {
    try {
        $freshnessReader->check($id, $fingerprint);
        dashboardMultisiteExpect(false, 'Invalid fingerprint input must not be accepted');
    } catch (InvalidArgumentException) {
        // Expected typed boundary rejection; no native WordPress state changed.
    }
}
$invalidFreshnessPresetId = '16161616-1616-4616-8616-161616161616';
$repo->save($portableDefinition(
    $invalidFreshnessPresetId, DefinitionStatus::Published, 10, 1,
    ['17171717-1717-4717-8717-171717171717'],
));
dashboardMultisiteExpect(
    $freshnessReader->check($invalidFreshnessPresetId, $siteSnapshot['sha256']) === ['status' => 'invalid_catalog', 'current' => false],
    'Malformed Published widget references must fail closed, without digest disclosure',
);
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteA], 'native preferences unchanged after real WP freshness validation');

// RB-0106: a read-only compatibility preflight in the real pinned two-site
// WordPress fixture. Neither a 'valid_candidate' nor any other result permits
// importing, altering native preferences, or creating a Definition.
$preflight = new DashboardWidgetPresetImportPreflightService($repo, $portablePresetCompiler);
$candidateId = '18181818-1818-4818-8818-181818181818';
$candidateSnapshot = $orderSnapshot;
$candidateSnapshot['payload']['definition_id'] = $candidateId;
$candidateSnapshot['sha256'] = hash('sha256', json_encode(
    $candidateSnapshot['payload'],
    JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
));
$preflightExistingBefore = $repo->get($localPresetId);
$preflightWidgetBefore = $repo->get($widgetId);
$preflightLocalWidgetBefore = $repo->get($localWidgetId);
foreach ([$siteA, $siteB] as $siteForPreflight) {
    switch_to_blog($siteForPreflight);
    wp_set_current_user($adminId);
    $beforeBlog = get_current_blog_id();
    $beforeUser = get_current_user_id();
    $beforeNative = $preferences($adminId);
    dashboardMultisiteExpect(
        $preflight->preflight($candidateSnapshot) === ['status' => 'valid_candidate', 'applicable' => false],
        'valid snapshot with a new id is an advisory candidate only, never auto-applicable',
    );
    dashboardMultisiteExpect(
        $preflight->preflight($orderSnapshot) === ['status' => 'id_conflict', 'applicable' => false],
        'already-owned preset id must be advisory conflict, never overwritable',
    );
    $tampered = $candidateSnapshot;
    $tampered['sha256'] = str_repeat('0', 64);
    dashboardMultisiteExpect(
        $preflight->preflight($tampered) === ['status' => 'integrity_mismatch', 'applicable' => false],
        'modified fingerprint must not pass content-integrity assessment',
    );
    $unknownReference = $candidateSnapshot;
    $unknownReference['payload']['widget_definition_ids'] = ['19191919-1919-4919-8919-191919191919'];
    $unknownReference['sha256'] = hash('sha256', json_encode(
        $unknownReference['payload'],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    ));
    dashboardMultisiteExpect(
        $preflight->preflight($unknownReference) === ['status' => 'invalid_snapshot', 'applicable' => false],
        'missing Published widget reference must fail compatibility assessment',
    );
    $wrongTypeReference = $candidateSnapshot;
    $wrongTypeReference['payload']['widget_definition_ids'] = [$draftPresetId];
    $wrongTypeReference['sha256'] = hash('sha256', json_encode(
        $wrongTypeReference['payload'],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    ));
    dashboardMultisiteExpect(
        $preflight->preflight($wrongTypeReference) === ['status' => 'invalid_snapshot', 'applicable' => false],
        'Draft preset cannot be used as a Published widget reference',
    );
    $injected = $candidateSnapshot;
    $injected['payload']['source_url'] = 'https://untrusted.example';
    dashboardMultisiteExpect(
        $preflight->preflight($injected) === ['status' => 'invalid_snapshot', 'applicable' => false],
        'unrecognized portability payload fields must be rejected before use',
    );
    $wrongOrder = [
        'payload' => $candidateSnapshot['payload'],
        'format' => $candidateSnapshot['format'],
        'version' => $candidateSnapshot['version'],
        'sha256' => $candidateSnapshot['sha256'],
    ];
    dashboardMultisiteExpect(
        $preflight->preflight($wrongOrder) === ['status' => 'invalid_snapshot', 'applicable' => false],
        'non-canonical envelope ordering cannot be silently accepted',
    );
    dashboardMultisiteExpect($preferences($adminId) === $beforeNative, 'preflight must preserve native Dashboard preferences');
    dashboardMultisiteExpect(get_current_blog_id() === $beforeBlog, 'preflight must preserve current WordPress blog identity');
    dashboardMultisiteExpect(get_current_user_id() === $beforeUser, 'preflight must preserve WordPress current user');
    dashboardMultisiteExpect((int) get_current_network_id() === $networkId, 'preflight must preserve real WordPress network');
    dashboardMultisiteExpect($repo->get($candidateId) === null, 'preflight must never save candidate Definition');
    restore_current_blog();
}
dashboardMultisiteExpect($repo->get($localPresetId) === $preflightExistingBefore, 'existing preset untouched by preflight');
dashboardMultisiteExpect($repo->get($widgetId) === $preflightWidgetBefore, 'network widget untouched by preflight');
dashboardMultisiteExpect($repo->get($localWidgetId) === $preflightLocalWidgetBefore, 'local widget untouched by preflight');
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteA], 'preflight must preserve network admin native layout');

// RB-0108: read-only cross-site UUID translation on pinned real WordPress.
// A different source site's widget IDs map to existing Published target widgets;
// no importer, WordPress preference update, or Definition write is permitted.
$mappingReader = new DashboardWidgetPresetPortabilityMappingPreviewService($preflight);
$portableSourceSnapshot = $orderSnapshot;
$portableSourceSnapshot['payload']['definition_id'] = 'abababab-abab-4bab-8bab-abababababab';
$portableSourceSnapshot['payload']['widget_definition_ids'] = [
    'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
    'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
];
$portableSourceSnapshot['sha256'] = hash('sha256', json_encode(
    $portableSourceSnapshot['payload'],
    JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
));
$mappedCandidateId = 'cdcdcdcd-cdcd-4dcd-8dcd-cdcdcdcdcdcd';
$widgetIdMapping = [
    'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' => $widgetId,
    'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb' => $localWidgetId,
];
$mappingExpected = [$widgetId, $localWidgetId];
$mappingRepoPresetBefore = $repo->get($localPresetId);
$mappingRepoWidgetBefore = $repo->get($widgetId);
$mappingRepoLocalWidgetBefore = $repo->get($localWidgetId);
foreach ([$siteA, $siteB] as $mappingSiteId) {
    switch_to_blog($mappingSiteId);
    wp_set_current_user($adminId);
    $mappingBlogBefore = get_current_blog_id();
    $mappingUserBefore = get_current_user_id();
    $mappingPrefsBefore = $preferences($adminId);

    $mappingResult = $mappingReader->preview($portableSourceSnapshot, $mappedCandidateId, $widgetIdMapping);
    dashboardMultisiteExpect(
        $mappingResult['status'] === 'valid_candidate' && $mappingResult['applicable'] === false,
        'cross-site mapped snapshot is an advisory non-applicable candidate only',
    );
    $candidate = $mappingResult['candidate_snapshot'];
    dashboardMultisiteExpect(is_array($candidate), 'only validated mapping may expose its candidate envelope');
    dashboardMultisiteExpect($candidate['payload']['definition_id'] === $mappedCandidateId, 'mapped candidate ID must be exact');
    dashboardMultisiteExpect($candidate['payload']['widget_definition_ids'] === $mappingExpected, 'mapped references must preserve source order');
    dashboardMultisiteExpect($candidate['sha256'] === hash('sha256', json_encode(
        $candidate['payload'],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    )), 'mapped canonical candidate fingerprint must match deterministic JSON');
    dashboardMultisiteExpect(
        $mappingReader->preview($portableSourceSnapshot, $mappedCandidateId, $widgetIdMapping) === $mappingResult,
        'cross-site mapping preview must remain deterministic across reads',
    );
    dashboardMultisiteExpect(
        $preflight->preflight($candidate) === ['status' => 'valid_candidate', 'applicable' => false],
        'mapped envelope must pass canonical Published target-site preflight',
    );

    $badDigest = $portableSourceSnapshot;
    $badDigest['sha256'] = str_repeat('0', 64);
    dashboardMultisiteExpect(
        $mappingReader->preview($badDigest, $mappedCandidateId, $widgetIdMapping) ===
        ['status' => 'integrity_mismatch', 'applicable' => false, 'candidate_snapshot' => null],
        'modified source fingerprint must fail closed with no candidate',
    );
    foreach ([
        ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' => $widgetId],
        [
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' => $widgetId,
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb' => $widgetId,
        ],
        [
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' => $widgetId,
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        ],
        [
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' => $widgetId,
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb' => $draftPresetId,
        ],
        [
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' => $widgetId,
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb' => $wrongOwnerPresetId,
        ],
    ] as $badMap) {
        dashboardMultisiteExpect(
            $mappingReader->preview($portableSourceSnapshot, $mappedCandidateId, $badMap) ===
            ['status' => 'invalid_snapshot', 'applicable' => false, 'candidate_snapshot' => null],
            'incomplete, duplicate, unknown, foreign or wrong-type target mapping must fail closed',
        );
    }
    dashboardMultisiteExpect(
        $mappingReader->preview($portableSourceSnapshot, $localPresetId, $widgetIdMapping) ===
        ['status' => 'id_conflict', 'applicable' => false, 'candidate_snapshot' => null],
        'existing target preset cannot be overwritten through mapping preview',
    );
    dashboardMultisiteExpect($preferences($adminId) === $mappingPrefsBefore, 'mapping cannot mutate native Dashboard preferences');
    dashboardMultisiteExpect(get_current_blog_id() === $mappingBlogBefore, 'mapping cannot switch current WP blog');
    dashboardMultisiteExpect(get_current_user_id() === $mappingUserBefore, 'mapping cannot alter current WP user');
    dashboardMultisiteExpect((int) get_current_network_id() === $networkId, 'mapping cannot switch current WP network');
    dashboardMultisiteExpect($repo->get($mappedCandidateId) === null, 'mapping cannot persist mapped preset');
    restore_current_blog();
}
dashboardMultisiteExpect($repo->get($localPresetId) === $mappingRepoPresetBefore, 'mapping must not modify existing preset');
dashboardMultisiteExpect($repo->get($widgetId) === $mappingRepoWidgetBefore, 'mapping must not modify network widget');
dashboardMultisiteExpect($repo->get($localWidgetId) === $mappingRepoLocalWidgetBefore, 'mapping must not modify local widget');
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteA], 'mapping must not touch network admin native preferences');

// RB-0112 — internal-only Draft import, exercised against real pinned
// two-site WordPress identity/capability state and disposable Definition
// storage. Never publishes, exposes REST/UI or changes native preferences.
$draftImporter = new DashboardWidgetPresetDraftImportService(
    $repo,
    $preflight,
    new WordPressCapabilityChecker(new NativeWordPressAbilityEnvironment()),
);
$existingPresetBeforeDraftImport = $repo->get($localPresetId);
$existingNetworkBeforeDraftImport = $repo->get($presetId);
$draftCandidateIds = [
    $siteA => '21212121-2121-4212-8212-212121212121',
    $siteB => '23232323-2323-4232-8232-232323232323',
];
$draftImportSnapshot = static function (array $original, string $target): array {
    $copy = $original;
    $copy['payload']['definition_id'] = $target;
    $copy['sha256'] = hash('sha256', json_encode(
        $copy['payload'],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    ));
    return $copy;
};
foreach ([$siteA, $siteB] as $importSiteId) {
    switch_to_blog($importSiteId);
    wp_set_current_user($adminId);
    $beforeBlog = (int) get_current_blog_id();
    $beforeUser = (int) get_current_user_id();
    $beforeNetwork = (int) get_current_network_id();
    $beforeNative = $preferences($adminId);
    $candidateId = $draftCandidateIds[$importSiteId];
    $snapshotToImport = $draftImportSnapshot($orderSnapshot, $candidateId);
    $ctx = new ExecutionContext(new Principal($adminId), $importSiteId, networkId: $networkId);
    $slug = 'draft-multisite-import-' . $importSiteId;

    dashboardMultisiteExpect(
        $draftImporter->importDraft(
            new ExecutionContext(new Principal($adminId), $importSiteId, ExecutionChannel::Rest, $networkId),
            $snapshotToImport, $slug,
        ) === ['status' => 'forbidden'],
        'REST callers cannot invoke internal-only Draft importer',
    );
    dashboardMultisiteExpect(
        $draftImporter->importDraft(
            new ExecutionContext(new Principal($adminId + 100), $importSiteId, networkId: $networkId),
            $snapshotToImport, $slug,
        ) === ['status' => 'forbidden'],
        'forged WordPress principal cannot import',
    );
    dashboardMultisiteExpect(
        $draftImporter->importDraft(
            new ExecutionContext(new Principal($adminId), $importSiteId === $siteA ? $siteB : $siteA, networkId: $networkId),
            $snapshotToImport, $slug,
        ) === ['status' => 'forbidden'],
        'cross-site identity mismatch cannot import',
    );
    dashboardMultisiteExpect(
        $draftImporter->importDraft(
            new ExecutionContext(new Principal($adminId), $importSiteId, networkId: $networkId + 1),
            $snapshotToImport, $slug,
        ) === ['status' => 'forbidden'],
        'cross-network identity mismatch cannot import',
    );
    dashboardMultisiteExpect(
        $draftImporter->importDraft(
            new ExecutionContext(new Principal(null), $importSiteId, networkId: $networkId),
            $snapshotToImport, $slug,
        ) === ['status' => 'forbidden'],
        'guest cannot import Draft',
    );
    $badDigest = $snapshotToImport;
    $badDigest['sha256'] = str_repeat('0', 64);
    dashboardMultisiteExpect(
        $draftImporter->importDraft($ctx, $badDigest, $slug) === ['status' => 'invalid_snapshot'],
        'failed portability integrity cannot create Draft',
    );
    $networkDefault = $snapshotToImport;
    $networkDefault['payload']['assignment'] = ['roles' => [], 'network_default' => true];
    $networkDefault['sha256'] = hash('sha256', json_encode(
        $networkDefault['payload'],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    ));
    dashboardMultisiteExpect(
        $draftImporter->importDraft($ctx, $networkDefault, $slug) === ['status' => 'forbidden'],
        'site manage_options cannot authorize network-default import',
    );
    dashboardMultisiteExpect($repo->get($candidateId) === null, 'denied Draft operations must not save a Definition');

    dashboardMultisiteExpect(
        $draftImporter->importDraft($ctx, $snapshotToImport, $slug) ===
        ['status' => 'created_draft', 'definition_id' => $candidateId],
        'actual WordPress admin must create exactly one scoped Draft',
    );
    $savedDraft = $repo->get($candidateId);
    dashboardMultisiteExpect($savedDraft instanceof Definition, 'created Draft must exist in disposable repository');
    dashboardMultisiteExpect($savedDraft->status === DefinitionStatus::Draft, 'created imported preset must never be Published');
    dashboardMultisiteExpect($savedDraft->revision === 1, 'import revision starts at destination revision one');
    dashboardMultisiteExpect($savedDraft->payload['preset']['label'] === $snapshotToImport['payload']['label'], 'Draft label must be preserved');
    dashboardMultisiteExpect($savedDraft->payload['preset']['widget_definition_ids'] === $snapshotToImport['payload']['widget_definition_ids'], 'Draft widget order preserved');
    dashboardMultisiteExpect($savedDraft->payload['preset']['assignment'] === $snapshotToImport['payload']['assignment'], 'Draft assignment preserved');
    dashboardMultisiteExpect(
        $draftImporter->importDraft($ctx, $snapshotToImport, 'should-never-overwrite') === ['status' => 'id_conflict'],
        'same UUID must remain insert-only and cannot overwrite',
    );
    dashboardMultisiteExpect($repo->get($candidateId) === $savedDraft, 'id collision cannot update Draft');
    dashboardMultisiteExpect($preferences($adminId) === $beforeNative, 'Draft import must not alter native dashboard preferences');
    dashboardMultisiteExpect((int) get_current_blog_id() === $beforeBlog, 'Draft import must not switch WordPress blog');
    dashboardMultisiteExpect((int) get_current_user_id() === $beforeUser, 'Draft import must not switch WordPress user');
    dashboardMultisiteExpect((int) get_current_network_id() === $beforeNetwork, 'Draft import must not switch WordPress network');
    restore_current_blog();
}
dashboardMultisiteExpect($repo->get($localPresetId) === $existingPresetBeforeDraftImport, 'Published local preset cannot be overwritten by Draft import');
dashboardMultisiteExpect($repo->get($presetId) === $existingNetworkBeforeDraftImport, 'Published network preset cannot be overwritten by Draft import');
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteA], 'native Dashboard preferences unchanged across both Draft operations');

// RB-0115: exercise the mapped Draft-only *composition* against actual pinned
// two-site WordPress identity, permissions and dashboard usermeta. The source
// UUIDs refer to another site's export and are never persisted directly.
$mappedDraftImporter = new DashboardWidgetPresetMappedDraftImportService($mappingReader, $draftImporter);
$mappedDraftCandidates = [
    $siteA => '31313131-3131-4313-8313-313131313131',
    $siteB => '32323232-3232-4323-8323-323232323232',
];
$publishedPresetBeforeMappedImport = $repo->get($localPresetId);
$networkPresetBeforeMappedImport = $repo->get($presetId);
$targetWidgetBeforeMappedImport = [$repo->get($widgetId), $repo->get($localWidgetId)];
foreach ([$siteA, $siteB] as $siteForMappedDraft) {
    switch_to_blog($siteForMappedDraft);
    wp_set_current_user($adminId);
    $savedBlog = (int) get_current_blog_id();
    $savedUser = (int) get_current_user_id();
    $savedNetwork = (int) get_current_network_id();
    $savedPrefs = $preferences($adminId);
    $mappedDraftId = $mappedDraftCandidates[$siteForMappedDraft];
    $mappedDraftSlug = 'mapped-draft-site-' . $siteForMappedDraft;
    $ctx = new ExecutionContext(new Principal($adminId), $siteForMappedDraft, networkId: $networkId);

    foreach ([
        new ExecutionContext(new Principal($adminId), $siteForMappedDraft, ExecutionChannel::Rest, $networkId),
        new ExecutionContext(new Principal($adminId), $siteForMappedDraft, ExecutionChannel::Ui, $networkId),
        new ExecutionContext(new Principal($adminId), $siteForMappedDraft, ExecutionChannel::Ai, $networkId),
        new ExecutionContext(new Principal(null), $siteForMappedDraft, networkId: $networkId),
        new ExecutionContext(new Principal($adminId + 100), $siteForMappedDraft, networkId: $networkId),
        new ExecutionContext(new Principal($adminId), $siteForMappedDraft === $siteA ? $siteB : $siteA, networkId: $networkId),
        new ExecutionContext(new Principal($adminId), $siteForMappedDraft, networkId: $networkId + 1),
    ] as $unauthorizedContext) {
        dashboardMultisiteExpect(
            $mappedDraftImporter->importMappedDraft(
                $unauthorizedContext, ['untrusted' => true], 'not-a-uuid', ['untrusted' => []], 'invalid',
            ) === ['status' => 'forbidden'],
            'mapped Draft must deny non-Internal, guest, forged or wrong-site/network callers BEFORE reading source inputs',
        );
    }

    $badSource = $portableSourceSnapshot;
    $badSource['sha256'] = str_repeat('0', 64);
    dashboardMultisiteExpect(
        $mappedDraftImporter->importMappedDraft(
            $ctx, $badSource, $mappedDraftId, $widgetIdMapping, $mappedDraftSlug,
        ) === ['status' => 'invalid_snapshot'],
        'untrusted source checksum cannot create mapped Draft',
    );
    foreach ([
        ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' => $widgetId],
        [
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' => $widgetId,
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb' => $widgetId,
        ],
        [
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' => $widgetId,
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb' => $draftPresetId,
        ],
        [
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa' => $widgetId,
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb' => $wrongOwnerPresetId,
        ],
    ] as $badMap) {
        dashboardMultisiteExpect(
            $mappedDraftImporter->importMappedDraft($ctx, $portableSourceSnapshot, $mappedDraftId, $badMap, $mappedDraftSlug) ===
            ['status' => 'invalid_snapshot'],
            'incomplete, duplicate, Draft or wrong-owner mapped refs fail closed',
        );
    }
    $networkDefaultSource = $portableSourceSnapshot;
    $networkDefaultSource['payload']['assignment'] = ['roles' => [], 'network_default' => true];
    $networkDefaultSource['sha256'] = hash('sha256', json_encode(
        $networkDefaultSource['payload'],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    ));
    dashboardMultisiteExpect(
        $mappedDraftImporter->importMappedDraft(
            $ctx, $networkDefaultSource, $mappedDraftId, $widgetIdMapping, $mappedDraftSlug,
        ) === ['status' => 'forbidden'],
        'site manage_options cannot import network-default via mapped Draft',
    );
    dashboardMultisiteExpect($repo->get($mappedDraftId) === null, 'all denied mapped Draft calls must not write');

    dashboardMultisiteExpect(
        $mappedDraftImporter->importMappedDraft(
            $ctx, $portableSourceSnapshot, $mappedDraftId, $widgetIdMapping, $mappedDraftSlug,
        ) === ['status' => 'created_draft', 'definition_id' => $mappedDraftId],
        'real WordPress administrator must create only one mapped Draft under local target id',
    );
    $mappedDraft = $repo->get($mappedDraftId);
    dashboardMultisiteExpect($mappedDraft instanceof Definition, 'mapped Draft must exist in disposable store');
    dashboardMultisiteExpect($mappedDraft->status === DefinitionStatus::Draft, 'mapped import never publishes');
    dashboardMultisiteExpect($mappedDraft->revision === 1, 'destination revision resets to one');
    dashboardMultisiteExpect($mappedDraft->slug === $mappedDraftSlug, 'destination slug preserved');
    dashboardMultisiteExpect(
        $mappedDraft->payload['preset']['widget_definition_ids'] === $mappingExpected,
        'mapped Draft must preserve ordered Published target widget IDs',
    );
    dashboardMultisiteExpect(
        $mappedDraft->payload['preset']['label'] === $portableSourceSnapshot['payload']['label'],
        'mapped Draft must preserve validated source label',
    );
    dashboardMultisiteExpect(
        $mappedDraft->payload['preset']['assignment'] === $portableSourceSnapshot['payload']['assignment'],
        'mapped Draft must preserve site-safe roles and assignment',
    );
    dashboardMultisiteExpect(
        $mappedDraftImporter->importMappedDraft(
            $ctx, $portableSourceSnapshot, $mappedDraftId, $widgetIdMapping, 'must-not-overwrite',
        ) === ['status' => 'id_conflict'],
        'repeat mapped Draft creation must not overwrite',
    );
    dashboardMultisiteExpect($repo->get($mappedDraftId) === $mappedDraft, 'repeated source mapping cannot mutate saved Draft');
    dashboardMultisiteExpect($preferences($adminId) === $savedPrefs, 'mapped Draft must not update native preferences');
    dashboardMultisiteExpect((int) get_current_blog_id() === $savedBlog, 'mapped Draft must not switch blog');
    dashboardMultisiteExpect((int) get_current_user_id() === $savedUser, 'mapped Draft must not switch user');
    dashboardMultisiteExpect((int) get_current_network_id() === $savedNetwork, 'mapped Draft must not switch network');
    restore_current_blog();
}
dashboardMultisiteExpect($repo->get($localPresetId) === $publishedPresetBeforeMappedImport, 'Published local preset unchanged by mapped Draft');
dashboardMultisiteExpect($repo->get($presetId) === $networkPresetBeforeMappedImport, 'Published network preset unchanged by mapped Draft');
dashboardMultisiteExpect(
    [$repo->get($widgetId), $repo->get($localWidgetId)] === $targetWidgetBeforeMappedImport,
    'Published target widgets unchanged by mapped Draft',
);
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteA], 'all native WP preferences unchanged by real mapped Draft tests');

// RB-0120: no-route, internal-only Draft *review* in actual pinned two-site
// WordPress identity/capability contexts. Inspect NEVER publishes or writes.
$draftPublishReview = new DashboardWidgetPresetDraftPublishReviewService(
    $repo,
    $portablePresetCompiler,
    $draftImporter,
);
$draftsBeforeReview = [];
foreach ([$siteA, $siteB] as $siteForDraftReview) {
    $draftsBeforeReview[$siteForDraftReview] = [
        $repo->get($draftCandidateIds[$siteForDraftReview]),
        $repo->get($mappedDraftCandidates[$siteForDraftReview]),
    ];
}
$publishedBeforeReview = [$repo->get($localPresetId), $repo->get($presetId)];
$widgetsBeforeReview = [$repo->get($widgetId), $repo->get($localWidgetId)];
$siteReviewStatus = ['status' => 'review_candidate', 'publish_authorized' => false];
$reviewForbidden = ['status' => 'forbidden', 'publish_authorized' => false];
foreach ([$siteA, $siteB] as $siteForDraftReview) {
    switch_to_blog($siteForDraftReview);
    wp_set_current_user($adminId);
    $beforeBlog = (int) get_current_blog_id();
    $beforeUser = (int) get_current_user_id();
    $beforeNetwork = (int) get_current_network_id();
    $beforePrefs = $preferences($adminId);
    $ctx = new ExecutionContext(new Principal($adminId), $siteForDraftReview, networkId: $networkId);
    foreach ([$draftCandidateIds[$siteForDraftReview], $mappedDraftCandidates[$siteForDraftReview]] as $candidate) {
        $draft = $repo->get($candidate);
        dashboardMultisiteExpect($draft instanceof Definition, 'real WP Draft review fixture requires persisted disposable Draft');
        dashboardMultisiteExpect($draft->status === DefinitionStatus::Draft, 'Draft review requires existing Draft state');
        $checksum = $draft->computedChecksum();
        dashboardMultisiteExpect(
            $draftPublishReview->inspect($ctx, $candidate, $checksum) === $siteReviewStatus,
            'same-site authorized WP admin may inspect a Draft as review candidate but never authorize publication',
        );
        dashboardMultisiteExpect(
            $draftPublishReview->inspect($ctx, $candidate, $checksum) === $siteReviewStatus,
            'review read must be deterministic and idempotent without writes',
        );
        dashboardMultisiteExpect(
            $draftPublishReview->inspect($ctx, $candidate, str_repeat('0', 64)) ===
            ['status' => 'stale_draft', 'publish_authorized' => false],
            'stale Draft content checksum must reject review',
        );
        foreach ([
            new ExecutionContext(new Principal($adminId), $siteForDraftReview, ExecutionChannel::Rest, $networkId),
            new ExecutionContext(new Principal($adminId), $siteForDraftReview, ExecutionChannel::Ui, $networkId),
            new ExecutionContext(new Principal($adminId), $siteForDraftReview, ExecutionChannel::Ai, $networkId),
            new ExecutionContext(new Principal(null), $siteForDraftReview, networkId: $networkId),
            new ExecutionContext(new Principal($adminId + 100), $siteForDraftReview, networkId: $networkId),
            new ExecutionContext(new Principal($adminId), $siteForDraftReview === $siteA ? $siteB : $siteA, networkId: $networkId),
            new ExecutionContext(new Principal($adminId), $siteForDraftReview, networkId: $networkId + 1),
        ] as $badContext) {
            dashboardMultisiteExpect(
                $draftPublishReview->inspect($badContext, 'invalid-uuid', 'invalid-hash') === $reviewForbidden,
                'unauthorized channel, user, blog and network must be rejected BEFORE input processing',
            );
        }
        dashboardMultisiteExpect(
            $draftPublishReview->inspect($ctx, $candidate, 'bad-hash') ===
            ['status' => 'invalid_input', 'publish_authorized' => false],
            'invalid expected content hash must not approve Draft review',
        );
        dashboardMultisiteExpect($repo->get($candidate) === $draft, 'Draft review cannot change existing stored Draft');
    }
    dashboardMultisiteExpect(
        $draftPublishReview->inspect($ctx, $localPresetId, str_repeat('a', 64)) ===
        ['status' => 'unavailable', 'publish_authorized' => false],
        'Published preset cannot be reviewed as an imported Draft',
    );
    dashboardMultisiteExpect($preferences($adminId) === $beforePrefs, 'Draft review never changes native Dashboard user preferences');
    dashboardMultisiteExpect((int) get_current_blog_id() === $beforeBlog, 'Draft review must never switch blog');
    dashboardMultisiteExpect((int) get_current_user_id() === $beforeUser, 'Draft review must never switch user');
    dashboardMultisiteExpect((int) get_current_network_id() === $beforeNetwork, 'Draft review must never switch network');
    restore_current_blog();
}
foreach ($draftsBeforeReview as $siteId => [$directDraft, $mappedDraft]) {
    dashboardMultisiteExpect($repo->get($draftCandidateIds[$siteId]) === $directDraft, 'direct imported Draft unchanged by review');
    dashboardMultisiteExpect($repo->get($mappedDraftCandidates[$siteId]) === $mappedDraft, 'mapped imported Draft unchanged by review');
}
dashboardMultisiteExpect(
    [$repo->get($localPresetId), $repo->get($presetId)] === $publishedBeforeReview,
    'review cannot alter existing Published presets',
);
dashboardMultisiteExpect(
    [$repo->get($widgetId), $repo->get($localWidgetId)] === $widgetsBeforeReview,
    'review cannot alter existing Published widgets',
);
dashboardMultisiteExpect($preferences($adminId) === $fixtureBefore[$siteA], 'native WP dashboard settings still unchanged');

// RB-0124: real pinned two-site WP identity drift *after* one atomic
// internal Draft insert. This is a disposable fixture and never publishes.
switch_to_blog($siteA);
wp_set_current_user($adminId);
$authRaceBeforeNative = $preferences($adminId);
$authRaceBeforeWidgets = [$repo->get($widgetId), $repo->get($localWidgetId)];
foreach ([
    ['mode' => 'user', 'id' => '8f8f8f8f-8f8f-4f8f-8f8f-8f8f8f8f8f8f'],
    ['mode' => 'site', 'id' => '9f9f9f9f-9f9f-4f9f-8f9f-9f9f9f9f9f9f'],
] as $authRaceCase) {
    $snapshot = $draftImportSnapshot($orderSnapshot, $authRaceCase['id']);
    $wrapper = new class($repo, $authRaceCase['mode'], $siteB) implements DefinitionCreateOnlyRepositoryInterface {
        public int $createCalls = 0;

        public function __construct(
            private readonly InMemoryDefinitionRepository $inner,
            private readonly string $mode,
            private readonly int $otherBlog,
        ) {}

        public function create(Definition $draft): void
        {
            ++$this->createCalls;
            $this->inner->create($draft);
            if ($this->mode === 'user') {
                wp_set_current_user(0);
            } else {
                switch_to_blog($this->otherBlog);
            }
        }

        public function save(Definition $definition): void
        {
            throw new RuntimeException('No retries, rollback or second writes allowed');
        }

        public function get(string $id): ?Definition { return $this->inner->get($id); }
        public function byType(string $type): array { return $this->inner->byType($type); }
        public function dependentsOf(string $id): array { return $this->inner->dependentsOf($id); }
    };
    $importer = new DashboardWidgetPresetDraftImportService(
        $wrapper,
        new DashboardWidgetPresetImportPreflightService(
            $wrapper, new DashboardWidgetPresetCompiler($wrapper),
        ),
        new WordPressCapabilityChecker(new NativeWordPressAbilityEnvironment()),
    );
    $result = $importer->importDraft(
        new ExecutionContext(new Principal($adminId), $siteA, networkId: $networkId),
        $snapshot, 'auth-race-' . $authRaceCase['mode'],
    );

    // Restore the disposable WP test session regardless of whether the guard
    // reported the expected generic failure.
    if ($authRaceCase['mode'] === 'user') {
        wp_set_current_user($adminId);
    } else {
        restore_current_blog();
    }
    dashboardMultisiteExpect(
        $result === ['status' => 'write_failed'],
        'post-insert WordPress user/blog drift cannot return created_draft: ' . $authRaceCase['mode'],
    );
    dashboardMultisiteExpect($wrapper->createCalls === 1, 'postcreate WP drift must not retry Draft insert');
    $saved = $repo->get($authRaceCase['id']);
    dashboardMultisiteExpect(
        $saved instanceof Definition && $saved->status === DefinitionStatus::Draft && $saved->revision === 1,
        'postcreate identity drift may leave only its one Draft, never a Published preset',
    );
    dashboardMultisiteExpect(get_current_blog_id() === $siteA, 'real WP test blog must be restored');
    dashboardMultisiteExpect(get_current_user_id() === $adminId, 'real WP test user must be restored');
    dashboardMultisiteExpect((int) get_current_network_id() === $networkId, 'postcreate check may not change network');
    dashboardMultisiteExpect($preferences($adminId) === $authRaceBeforeNative, 'postcreate auth drift may not mutate native Dashboard preferences');
}
dashboardMultisiteExpect(
    [$repo->get($widgetId), $repo->get($localWidgetId)] === $authRaceBeforeWidgets,
    'postcreate auth drift may not mutate referenced Published widget Definitions',
);
restore_current_blog();

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
    'rb0104_read_only_freshness_match_stale' => true,
    'rb0104_invalid_catalog_rejected' => true,
    'rb0104_wordpress_preferences_unchanged' => true,
    'rb0106_import_preflight_candidate_no_apply' => true,
    'rb0106_import_preflight_conflict_integrity_invalid' => true,
    'rb0106_import_preflight_no_native_mutation' => true,
    'rb0108_cross_site_mapping_candidate_verified' => true,
    'rb0108_mapping_invalid_and_conflict_rejected' => true,
    'rb0108_mapping_preserves_wordpress_state' => true,
    'rb0112_draft_import_auth_context_isolation' => true,
    'rb0112_draft_only_create_once' => true,
    'rb0112_native_preferences_unchanged' => true,
    'rb0115_internal_mapped_draft_real_wordpress_permission_gate' => true,
    'rb0115_mapped_order_and_draft_only_atomic_create' => true,
    'rb0115_network_isolation_and_native_preferences_unchanged' => true,
    'rb0120_draft_review_read_only_and_no_publication' => true,
    'rb0120_draft_review_real_wordpress_identity_isolation' => true,
    'rb0120_draft_review_native_dashboard_preferences_unchanged' => true,
    'rb0124_postcreate_identity_drift_fails_closed' => true,
    'rb0124_draft_insert_once_no_native_preferences' => true,
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
