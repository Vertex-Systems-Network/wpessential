<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$auditPath = $root . '/config/product/options-bank-audits/dashboard-widgets-native-wordpress.json';
$auditSchemaPath = $root . '/config/product/options-bank-native-audit.schema.json';
$surfaceRegistryPath = $root . '/config/product/competitor-parity-surfaces.json';
$bankDirectory = $root . '/config/product/options-bank';

/** @return array<string, mixed> */
function readDashboardWidgetsNativeJson(string $path): array
{
    $contents = file_get_contents($path);
    if ($contents === false) {
        throw new RuntimeException(sprintf('Unable to read %s.', $path));
    }

    try {
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new RuntimeException(sprintf('Invalid JSON in %s: %s', $path, $exception->getMessage()), 0, $exception);
    }

    if (!is_array($decoded)) {
        throw new RuntimeException(sprintf('%s must contain a JSON object.', $path));
    }

    return $decoded;
}

/** @param mixed $value */
function requireDashboardWidgetsNativeString($value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }

    return $value;
}

// Dependency-free smoke contract: the shared schema must remain parseable JSON.
readDashboardWidgetsNativeJson($auditSchemaPath);

$registry = readDashboardWidgetsNativeJson($surfaceRegistryPath);
$surfaces = $registry['surfaces'] ?? null;
if (!is_array($surfaces)) {
    throw new RuntimeException('Canonical surface registry is malformed.');
}

/** @var array<string, int> $surfaceIds */
$surfaceIds = [];
foreach ($surfaces as $surface) {
    if (!is_array($surface) || !is_int($surface['id'] ?? null) || !is_string($surface['key'] ?? null)) {
        throw new RuntimeException('Canonical surface registry contains an invalid row.');
    }
    $surfaceIds[$surface['key']] = $surface['id'];
}
if (($surfaceIds['dashboard-widgets'] ?? null) !== 10) {
    throw new RuntimeException('Canonical Surface 10 must remain dashboard-widgets.');
}

/** @var array<string, true> $bankRecordIds */
$bankRecordIds = [];
$bankFiles = glob($bankDirectory . '/dashboard-widgets*.json');
if ($bankFiles === false || $bankFiles === []) {
    throw new RuntimeException('Unable to enumerate Dashboard Widgets Options Bank shards.');
}
foreach ($bankFiles as $file) {
    $bank = readDashboardWidgetsNativeJson($file);
    $surface = $bank['surface'] ?? null;
    $records = $bank['records'] ?? null;
    if (!is_array($surface) || ($surface['id'] ?? null) !== 10 || ($surface['key'] ?? null) !== 'dashboard-widgets' || !is_array($records)) {
        throw new RuntimeException(sprintf('Invalid Dashboard Widgets Options Bank shard: %s', $file));
    }
    foreach ($records as $record) {
        if (!is_array($record) || !is_string($record['id'] ?? null)) {
            throw new RuntimeException(sprintf('Invalid Dashboard Widgets Bank record in %s.', $file));
        }
        $bankRecordIds[$record['id']] = true;
    }
}
if (count($bankRecordIds) !== 123) {
    throw new RuntimeException(sprintf('Dashboard Widgets native audit expects 123 unique Bank records; found %d.', count($bankRecordIds)));
}

$audit = readDashboardWidgetsNativeJson($auditPath);
if (($audit['schema_version'] ?? null) !== 1 || ($audit['bank_version'] ?? null) !== 'v1') {
    throw new RuntimeException('Dashboard Widgets native audit has an unsupported version.');
}

$surface = $audit['surface'] ?? null;
if (!is_array($surface) || ($surface['id'] ?? null) !== 10 || ($surface['key'] ?? null) !== 'dashboard-widgets') {
    throw new RuntimeException('Dashboard Widgets native audit must target canonical Surface 10 / dashboard-widgets.');
}

$snapshot = $audit['snapshot'] ?? null;
if (!is_array($snapshot)) {
    throw new RuntimeException('Dashboard Widgets native audit is missing snapshot metadata.');
}
requireDashboardWidgetsNativeString($snapshot['date'] ?? null, 'Dashboard Widgets native audit is missing snapshot.date.');
requireDashboardWidgetsNativeString($snapshot['wordpress_target'] ?? null, 'Dashboard Widgets native audit is missing snapshot.wordpress_target.');
$sources = $snapshot['sources'] ?? null;
if (!is_array($sources) || $sources === []) {
    throw new RuntimeException('Dashboard Widgets native audit must identify primary WordPress sources.');
}
foreach ($sources as $source) {
    if (!is_array($source)) {
        throw new RuntimeException('Dashboard Widgets native audit contains an invalid source row.');
    }
    requireDashboardWidgetsNativeString($source['name'] ?? null, 'Dashboard Widgets native source is missing name.');
    $url = requireDashboardWidgetsNativeString($source['url'] ?? null, 'Dashboard Widgets native source is missing url.');
    if (!str_starts_with($url, 'https://developer.wordpress.org/')) {
        throw new RuntimeException(sprintf('Dashboard Widgets native source must cite Developer.WordPress.org: %s.', $url));
    }
}

$status = requireDashboardWidgetsNativeString($audit['status'] ?? null, 'Dashboard Widgets native audit has no status.');
if (!in_array($status, ['NATIVE_AUDIT_IN_PROGRESS', 'NATIVE_AUDITED'], true)) {
    throw new RuntimeException(sprintf('Dashboard Widgets native audit has invalid status %s.', $status));
}

$allowedDispositions = [
    'BANK_RECORD',
    'PROVIDER_MAPPING',
    'SYSTEM_RUNTIME',
    'OUT_OF_SURFACE',
    'LEGACY_COMPATIBILITY',
    'CORE_INTERNAL',
    'UNRESOLVED',
];
$items = $audit['items'] ?? null;
if (!is_array($items) || $items === []) {
    throw new RuntimeException('Dashboard Widgets native audit has no disposition items.');
}

$seenIds = [];
$counts = array_fill_keys($allowedDispositions, 0);
foreach ($items as $index => $item) {
    if (!is_array($item)) {
        throw new RuntimeException(sprintf('Dashboard Widgets native audit item %d is invalid.', $index));
    }

    $id = requireDashboardWidgetsNativeString($item['id'] ?? null, sprintf('Dashboard Widgets native audit item %d has no id.', $index));
    requireDashboardWidgetsNativeString($item['api'] ?? null, sprintf('Dashboard Widgets native audit item %s has no api.', $id));
    requireDashboardWidgetsNativeString($item['member'] ?? null, sprintf('Dashboard Widgets native audit item %s has no member.', $id));
    $disposition = requireDashboardWidgetsNativeString($item['disposition'] ?? null, sprintf('Dashboard Widgets native audit item %s has no disposition.', $id));
    $evidenceUrl = requireDashboardWidgetsNativeString($item['evidence_url'] ?? null, sprintf('Dashboard Widgets native audit item %s has no evidence_url.', $id));
    requireDashboardWidgetsNativeString($item['notes'] ?? null, sprintf('Dashboard Widgets native audit item %s has no notes.', $id));

    if (!str_starts_with($id, 'dashboard-widgets.native.')) {
        throw new RuntimeException(sprintf('Dashboard Widgets native audit item %s must use dashboard-widgets.native.* namespace.', $id));
    }
    if (isset($seenIds[$id])) {
        throw new RuntimeException(sprintf('Dashboard Widgets native audit duplicates item id %s.', $id));
    }
    $seenIds[$id] = true;

    if (!in_array($disposition, $allowedDispositions, true)) {
        throw new RuntimeException(sprintf('Dashboard Widgets native audit item %s has invalid disposition %s.', $id, $disposition));
    }
    ++$counts[$disposition];

    if (!str_starts_with($evidenceUrl, 'https://developer.wordpress.org/')) {
        throw new RuntimeException(sprintf('Dashboard Widgets native audit item %s must cite a primary Developer.WordPress.org URL.', $id));
    }

    $recordIds = $item['bank_record_ids'] ?? [];
    if (!is_array($recordIds)) {
        throw new RuntimeException(sprintf('Dashboard Widgets native audit item %s has invalid bank_record_ids.', $id));
    }
    if (in_array($disposition, ['BANK_RECORD', 'PROVIDER_MAPPING'], true) && $recordIds === []) {
        throw new RuntimeException(sprintf('Dashboard Widgets native audit item %s must map to at least one Bank record.', $id));
    }
    foreach ($recordIds as $recordId) {
        if (!is_string($recordId) || !isset($bankRecordIds[$recordId])) {
            throw new RuntimeException(sprintf('Dashboard Widgets native audit item %s references missing Bank record %s.', $id, (string) $recordId));
        }
    }

    if ($disposition === 'OUT_OF_SURFACE') {
        $ownerSurface = $item['owner_surface'] ?? null;
        if (!is_string($ownerSurface) || !isset($surfaceIds[$ownerSurface]) || $ownerSurface === 'dashboard-widgets') {
            throw new RuntimeException(sprintf('Dashboard Widgets native audit item %s must name a different canonical owner_surface.', $id));
        }
    }
}

$requiredItems = [
    'dashboard-widgets.native.wp_add.widget_id',
    'dashboard-widgets.native.wp_add.widget_name',
    'dashboard-widgets.native.wp_add.callback',
    'dashboard-widgets.native.wp_add.control_callback',
    'dashboard-widgets.native.wp_add.callback_args',
    'dashboard-widgets.native.wp_add.context',
    'dashboard-widgets.native.wp_add.priority',
    'dashboard-widgets.native.wp_add.edit_dashboard',
    'dashboard-widgets.native.setup.site_hook',
    'dashboard-widgets.native.setup.network_hook',
    'dashboard-widgets.native.remove.widget',
    'dashboard-widgets.native.inventory.default_widgets',
    'dashboard-widgets.native.hidden.user_state',
    'dashboard-widgets.native.hidden.default_filter',
    'dashboard-widgets.native.hidden.effective_filter',
    'dashboard-widgets.native.screen.meta_box_prefs',
    'dashboard-widgets.native.screen.reorder',
    'dashboard-widgets.native.screen.collapse',
    'dashboard-widgets.native.dashboard.columns',
    'dashboard-widgets.native.dashboard.setup_nonce',
    'dashboard-widgets.native.dashboard.control_dispatch',
    'dashboard-widgets.native.api.user_override_order',
    'dashboard-widgets.native.api.user_screen_options',
    'dashboard-widgets.native.api.rss_caching',
];
foreach ($requiredItems as $requiredId) {
    if (!isset($seenIds[$requiredId])) {
        throw new RuntimeException(sprintf('Dashboard Widgets native audit is missing mandatory disposition %s.', $requiredId));
    }
}
if (count($seenIds) !== count($requiredItems)) {
    throw new RuntimeException(sprintf('Dashboard Widgets native audit expected %d dispositions; found %d.', count($requiredItems), count($seenIds)));
}

$coverage = $audit['coverage'] ?? null;
if (!is_array($coverage)) {
    throw new RuntimeException('Dashboard Widgets native audit is missing coverage counters.');
}
$expectedCoverage = [
    'items' => count($items),
    'bank_record' => $counts['BANK_RECORD'],
    'provider_mapping' => $counts['PROVIDER_MAPPING'],
    'system_runtime' => $counts['SYSTEM_RUNTIME'],
    'out_of_surface' => $counts['OUT_OF_SURFACE'],
    'legacy_compatibility' => $counts['LEGACY_COMPATIBILITY'],
    'core_internal' => $counts['CORE_INTERNAL'],
    'unresolved' => $counts['UNRESOLVED'],
];
foreach ($expectedCoverage as $key => $value) {
    if (($coverage[$key] ?? null) !== $value) {
        throw new RuntimeException(sprintf('Dashboard Widgets native audit coverage.%s must be %d.', $key, $value));
    }
}

if ($counts['UNRESOLVED'] !== 0) {
    throw new RuntimeException('Dashboard Widgets native audit candidate must have zero unresolved disposition items.');
}

fwrite(
    STDOUT,
    sprintf(
        "Dashboard Widgets native audit contract: PASS (%s; %d dispositions, %d Bank, %d provider, %d runtime, 0 unresolved).\n",
        $status,
        count($items),
        $counts['BANK_RECORD'],
        $counts['PROVIDER_MAPPING'],
        $counts['SYSTEM_RUNTIME'],
    ),
);
