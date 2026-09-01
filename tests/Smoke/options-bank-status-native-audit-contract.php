<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$auditPath = $root . '/config/product/options-bank-audits/status-native-wordpress.json';
$auditSchemaPath = $root . '/config/product/options-bank-native-audit.schema.json';
$surfaceRegistryPath = $root . '/config/product/competitor-parity-surfaces.json';
$progressPath = $root . '/config/product/options-bank-progress.json';
$bankDirectory = $root . '/config/product/options-bank';

/** @return array<string, mixed> */
function readStatusNativeAuditJson(string $path): array
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
function requireStatusNativeAuditString($value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }

    return $value;
}

$schema = readStatusNativeAuditJson($auditSchemaPath);
$surfaceSchema = $schema['properties']['surface']['properties'] ?? null;
if (!is_array($surfaceSchema)
    || ($surfaceSchema['id']['minimum'] ?? null) !== 1
    || ($surfaceSchema['id']['maximum'] ?? null) !== 56
    || array_key_exists('const', $surfaceSchema['id'])
    || ($surfaceSchema['key']['pattern'] ?? null) !== '^[a-z0-9][a-z0-9-]*$'
    || array_key_exists('const', $surfaceSchema['key'])) {
    throw new RuntimeException('Native-audit schema must remain generic across canonical surfaces 1-56.');
}

$registry = readStatusNativeAuditJson($surfaceRegistryPath);
$surfaceIds = [];
foreach (($registry['surfaces'] ?? []) as $surface) {
    if (!is_array($surface) || !is_int($surface['id'] ?? null) || !is_string($surface['key'] ?? null)) {
        throw new RuntimeException('Canonical surface registry contains an invalid row.');
    }
    $surfaceIds[$surface['key']] = $surface['id'];
}
if (($surfaceIds['status'] ?? null) !== 5) {
    throw new RuntimeException('Canonical Surface 5 must remain status.');
}

$statusRecords = [];
$optionPaths = [];
$bankFiles = glob($bankDirectory . '/status*.json');
if ($bankFiles === false || $bankFiles === []) {
    throw new RuntimeException('Unable to enumerate Status Bank shards.');
}
sort($bankFiles, SORT_STRING);
foreach ($bankFiles as $file) {
    $bank = readStatusNativeAuditJson($file);
    if (($bank['surface']['id'] ?? null) !== 5
        || ($bank['surface']['key'] ?? null) !== 'status'
        || ($bank['status'] ?? null) !== 'BANK_SURFACE_SEEDED'
        || !is_array($bank['records'] ?? null)) {
        throw new RuntimeException(sprintf('Invalid Status Bank shard: %s', $file));
    }

    foreach ($bank['records'] as $record) {
        if (!is_array($record)
            || !is_string($record['id'] ?? null)
            || !is_string($record['option_path'] ?? null)) {
            throw new RuntimeException(sprintf('Invalid Status Bank record in %s.', $file));
        }

        $id = $record['id'];
        $optionPath = $record['option_path'];
        if (isset($statusRecords[$id])) {
            throw new RuntimeException(sprintf('Duplicate Status Bank record id %s.', $id));
        }
        if (isset($optionPaths[$optionPath])) {
            throw new RuntimeException(sprintf('Duplicate Status option_path %s.', $optionPath));
        }

        $statusRecords[$id] = $record;
        $optionPaths[$optionPath] = true;
    }
}
if (count($statusRecords) !== 129) {
    throw new RuntimeException(sprintf('Status native audit candidate requires exactly 129 local Bank records; found %d.', count($statusRecords)));
}

$audit = readStatusNativeAuditJson($auditPath);
if (($audit['schema_version'] ?? null) !== 1 || ($audit['bank_version'] ?? null) !== 'v1') {
    throw new RuntimeException('Status native audit has an unsupported version.');
}
if (($audit['surface']['id'] ?? null) !== 5 || ($audit['surface']['key'] ?? null) !== 'status') {
    throw new RuntimeException('Status native audit must target canonical Surface 5 / status.');
}

$snapshot = $audit['snapshot'] ?? null;
if (!is_array($snapshot)) {
    throw new RuntimeException('Status native audit is missing snapshot metadata.');
}
requireStatusNativeAuditString($snapshot['date'] ?? null, 'Status native audit is missing snapshot.date.');
requireStatusNativeAuditString($snapshot['wordpress_target'] ?? null, 'Status native audit is missing snapshot.wordpress_target.');
$sources = $snapshot['sources'] ?? null;
if (!is_array($sources) || $sources === []) {
    throw new RuntimeException('Status native audit must identify primary WordPress sources.');
}
foreach ($sources as $source) {
    if (!is_array($source)) {
        throw new RuntimeException('Status native audit contains an invalid source.');
    }
    $url = requireStatusNativeAuditString($source['url'] ?? null, 'Status native audit source has no URL.');
    if (!str_starts_with($url, 'https://developer.wordpress.org/')) {
        throw new RuntimeException(sprintf('Status native audit source must be Developer.WordPress.org: %s', $url));
    }
}

$auditStatus = $audit['status'] ?? null;
if (!in_array($auditStatus, ['NATIVE_AUDIT_IN_PROGRESS', 'NATIVE_AUDITED'], true)) {
    throw new RuntimeException('Status native audit lifecycle state is invalid.');
}

$allowedDispositions = [
    'BANK_RECORD', 'PROVIDER_MAPPING', 'SYSTEM_RUNTIME', 'OUT_OF_SURFACE',
    'LEGACY_COMPATIBILITY', 'CORE_INTERNAL', 'UNRESOLVED',
];
$items = $audit['items'] ?? null;
if (!is_array($items) || $items === []) {
    throw new RuntimeException('Status native audit has no disposition items.');
}

$seenIds = [];
$counts = array_fill_keys($allowedDispositions, 0);
foreach ($items as $index => $item) {
    if (!is_array($item)) {
        throw new RuntimeException(sprintf('Status native audit item %d is invalid.', $index));
    }

    $id = requireStatusNativeAuditString($item['id'] ?? null, sprintf('Status native audit item %d has no id.', $index));
    requireStatusNativeAuditString($item['api'] ?? null, sprintf('%s has no api.', $id));
    requireStatusNativeAuditString($item['member'] ?? null, sprintf('%s has no member.', $id));
    $disposition = requireStatusNativeAuditString($item['disposition'] ?? null, sprintf('%s has no disposition.', $id));
    $evidenceUrl = requireStatusNativeAuditString($item['evidence_url'] ?? null, sprintf('%s has no evidence_url.', $id));
    requireStatusNativeAuditString($item['notes'] ?? null, sprintf('%s has no notes.', $id));

    if (!str_starts_with($id, 'status.native.') || isset($seenIds[$id])) {
        throw new RuntimeException(sprintf('Invalid or duplicate Status native audit id %s.', $id));
    }
    $seenIds[$id] = true;

    if (!in_array($disposition, $allowedDispositions, true)) {
        throw new RuntimeException(sprintf('%s has invalid disposition %s.', $id, $disposition));
    }
    ++$counts[$disposition];

    if (!str_starts_with($evidenceUrl, 'https://developer.wordpress.org/')) {
        throw new RuntimeException(sprintf('%s must cite Developer.WordPress.org primary evidence.', $id));
    }

    $recordIds = $item['bank_record_ids'] ?? [];
    if (!is_array($recordIds)) {
        throw new RuntimeException(sprintf('%s has invalid bank_record_ids.', $id));
    }
    if (in_array($disposition, ['BANK_RECORD', 'PROVIDER_MAPPING'], true) && $recordIds === []) {
        throw new RuntimeException(sprintf('%s must map to at least one Status Bank record.', $id));
    }
    foreach ($recordIds as $recordId) {
        if (!is_string($recordId) || !isset($statusRecords[$recordId])) {
            throw new RuntimeException(sprintf('%s references missing Status Bank record %s.', $id, (string) $recordId));
        }
    }

    if ($disposition === 'OUT_OF_SURFACE') {
        $ownerSurface = $item['owner_surface'] ?? null;
        if (!is_string($ownerSurface) || !isset($surfaceIds[$ownerSurface]) || $ownerSurface === 'status') {
            throw new RuntimeException(sprintf('%s must name a different canonical owner_surface.', $id));
        }
    }
}

$requiredItems = [
    'status.native.register.key',
    'status.native.register.label',
    'status.native.register.label_count',
    'status.native.register.public',
    'status.native.register.internal',
    'status.native.register.protected',
    'status.native.register.private',
    'status.native.register.publicly_queryable',
    'status.native.register.exclude_from_search',
    'status.native.register.show_in_admin_all_list',
    'status.native.register.show_in_admin_status_list',
    'status.native.register.date_floating',
    'status.native.register.defaults',
    'status.native.register.builtin',
    'status.native.register.timing',
    'status.native.storage.post_status_length',
    'status.native.list.get_post_stati',
    'status.native.object.get_post_status_object',
    'status.native.count.wp_count_posts',
    'status.native.transition.generic',
    'status.native.transition.dynamic_edge',
    'status.native.transition.dynamic_destination',
    'status.native.transition.private_helper',
    'status.native.mutation.wp_update_post',
    'status.native.rest.status_collection',
    'status.native.rest.status_permissions',
    'status.native.rest.status_mutation_permission',
    'status.native.rest.status_query',
    'status.native.builtins.initial',
    'status.native.future.publish',
    'status.native.future.date',
    'status.native.trash',
    'status.native.untrash',
    'status.native.untrash_filter',
    'status.native.attachment.inherit',
];
foreach ($requiredItems as $requiredId) {
    if (!isset($seenIds[$requiredId])) {
        throw new RuntimeException(sprintf('Status native audit is missing mandatory disposition %s.', $requiredId));
    }
}
if (count($items) !== count($requiredItems)) {
    throw new RuntimeException(sprintf('Status native audit must contain exactly %d dispositions; found %d.', count($requiredItems), count($items)));
}

$coverage = $audit['coverage'] ?? null;
if (!is_array($coverage)) {
    throw new RuntimeException('Status native audit is missing coverage counters.');
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
        throw new RuntimeException(sprintf('Status native audit coverage.%s must be %d.', $key, $value));
    }
}
if ($counts['UNRESOLVED'] !== 0) {
    throw new RuntimeException('Status native audit requires zero unresolved disposition items before certification.');
}

$expectedRecords = [
    'status.definition.key' => ['NATIVE_HARD', 'HARD', 'CURRENT_NATIVE', 'MUST_HAVE', 'P0_NATIVE'],
    'status.compatibility.key_length' => ['COMPATIBILITY', 'HARD', 'CURRENT_NATIVE', 'MUST_HAVE', 'P0_NATIVE'],
    'status.transition.same_state_behavior' => ['WPE_HARD', 'HARD', 'CURRENT_NATIVE', 'MUST_HAVE', 'P0_NATIVE'],
    'status.events.actual_transition_only' => ['WPE_HARD', 'HARD', 'CURRENT_NATIVE', 'MUST_HAVE', 'P0_NATIVE'],
    'status.registration.timing' => ['SOFT_NATIVE', 'HARD', 'CURRENT_NATIVE', 'MUST_HAVE', 'P0_NATIVE'],
    'status.security.reject_builtin_override' => ['REJECTED_UNSAFE', 'HARD', 'CURRENT_NATIVE', 'REJECT', 'NOT_SCHEDULED'],
];
foreach ($expectedRecords as $recordId => $expected) {
    $record = $statusRecords[$recordId] ?? null;
    $actual = is_array($record) ? [
        $record['classification'] ?? null,
        $record['hard_soft'] ?? null,
        $record['horizon'] ?? null,
        $record['adoption'] ?? null,
        $record['priority'] ?? null,
    ] : [];
    if ($actual !== $expected) {
        throw new RuntimeException(sprintf('Status native invariant record %s has drifted from its expected classification.', $recordId));
    }
}

$progress = readStatusNativeAuditJson($progressPath);
$statusProgress = null;
foreach (($progress['surface_status'] ?? []) as $row) {
    if (is_array($row) && ($row['id'] ?? null) === 5 && ($row['key'] ?? null) === 'status') {
        $statusProgress = $row;
        break;
    }
}
if (!is_array($statusProgress)) {
    throw new RuntimeException('Options Bank progress is missing canonical Surface 5 / status.');
}

if ($auditStatus === 'NATIVE_AUDITED') {
    if (!in_array($statusProgress['status'] ?? null, ['NATIVE_AUDITED', 'MARKET_AUDITED', 'BANK_REVIEWED'], true)
        || ($statusProgress['records'] ?? null) !== count($statusRecords)) {
        throw new RuntimeException('Certified Status native audit and canonical progress truth disagree.');
    }
} elseif (!in_array($statusProgress['status'] ?? null, ['UNSEEDED', 'BANK_SURFACE_SEEDED'], true)) {
    throw new RuntimeException('Status progress must not outrun an in-progress native audit.');
}

printf(
    "Status native audit contract: PASS (%s; %d dispositions, %d Bank, %d runtime, %d core-internal, 0 unresolved; %d current Status records).\n",
    $auditStatus,
    count($items),
    $counts['BANK_RECORD'],
    $counts['SYSTEM_RUNTIME'],
    $counts['CORE_INTERNAL'],
    count($statusRecords),
);
