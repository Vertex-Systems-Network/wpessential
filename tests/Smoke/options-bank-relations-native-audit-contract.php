<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$auditPath = $root . '/config/product/options-bank-audits/relations-native-wordpress.json';
$auditSchemaPath = $root . '/config/product/options-bank-native-audit.schema.json';
$surfaceRegistryPath = $root . '/config/product/competitor-parity-surfaces.json';
$progressPath = $root . '/config/product/options-bank-progress.json';
$bankDirectory = $root . '/config/product/options-bank';

/** @return array<string, mixed> */
function readRelationsNativeAuditJson(string $path): array
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
function requireRelationsNativeAuditString($value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }

    return $value;
}

readRelationsNativeAuditJson($auditSchemaPath);

$registry = readRelationsNativeAuditJson($surfaceRegistryPath);
$surfaces = $registry['surfaces'] ?? null;
if (!is_array($surfaces)) {
    throw new RuntimeException('Canonical surface registry is malformed.');
}

$surfaceIds = [];
foreach ($surfaces as $surface) {
    if (!is_array($surface) || !is_int($surface['id'] ?? null) || !is_string($surface['key'] ?? null)) {
        throw new RuntimeException('Canonical surface registry contains an invalid row.');
    }
    $surfaceIds[$surface['key']] = $surface['id'];
}

$relationRecordIds = [];
$relationRecords = [];
$bankFiles = glob($bankDirectory . '/relations*.json');
if ($bankFiles === false || $bankFiles === []) {
    throw new RuntimeException('Unable to enumerate Relations Bank shards.');
}
sort($bankFiles, SORT_STRING);
foreach ($bankFiles as $file) {
    $bank = readRelationsNativeAuditJson($file);
    $surface = $bank['surface'] ?? null;
    $records = $bank['records'] ?? null;
    if (!is_array($surface) || ($surface['key'] ?? null) !== 'relations' || !is_array($records)) {
        throw new RuntimeException(sprintf('Invalid Relations Bank shard: %s', $file));
    }
    foreach ($records as $record) {
        if (!is_array($record) || !is_string($record['id'] ?? null)) {
            throw new RuntimeException(sprintf('Invalid Relations Bank record in %s.', $file));
        }
        $relationRecordIds[$record['id']] = true;
        $relationRecords[$record['id']] = $record;
    }
}

$audit = readRelationsNativeAuditJson($auditPath);
if (($audit['schema_version'] ?? null) !== 1 || ($audit['bank_version'] ?? null) !== 'v1') {
    throw new RuntimeException('Relations native audit has an unsupported version.');
}
$surface = $audit['surface'] ?? null;
if (!is_array($surface) || ($surface['id'] ?? null) !== 4 || ($surface['key'] ?? null) !== 'relations') {
    throw new RuntimeException('Relations native audit must target canonical Surface 4 / relations.');
}

$snapshot = $audit['snapshot'] ?? null;
if (!is_array($snapshot)) {
    throw new RuntimeException('Relations native audit is missing snapshot metadata.');
}
requireRelationsNativeAuditString($snapshot['date'] ?? null, 'Relations native audit is missing snapshot.date.');
requireRelationsNativeAuditString($snapshot['wordpress_target'] ?? null, 'Relations native audit is missing snapshot.wordpress_target.');
$sources = $snapshot['sources'] ?? null;
if (!is_array($sources) || $sources === []) {
    throw new RuntimeException('Relations native audit must identify primary sources.');
}
foreach ($sources as $source) {
    if (!is_array($source)) {
        throw new RuntimeException('Relations native audit contains an invalid source.');
    }
    $url = requireRelationsNativeAuditString($source['url'] ?? null, 'Relations native audit source has no URL.');
    if (!str_starts_with($url, 'https://developer.wordpress.org/')) {
        throw new RuntimeException(sprintf('Relations native audit source must be Developer.WordPress.org: %s', $url));
    }
}

if (($audit['status'] ?? null) !== 'NATIVE_AUDITED') {
    throw new RuntimeException('Relations native audit must be NATIVE_AUDITED on a certified candidate head.');
}

$allowedDispositions = [
    'BANK_RECORD', 'PROVIDER_MAPPING', 'SYSTEM_RUNTIME', 'OUT_OF_SURFACE',
    'LEGACY_COMPATIBILITY', 'CORE_INTERNAL', 'UNRESOLVED',
];
$items = $audit['items'] ?? null;
if (!is_array($items) || $items === []) {
    throw new RuntimeException('Relations native audit has no disposition items.');
}

$seenIds = [];
$counts = array_fill_keys($allowedDispositions, 0);
foreach ($items as $index => $item) {
    if (!is_array($item)) {
        throw new RuntimeException(sprintf('Relations native audit item %d is invalid.', $index));
    }
    $id = requireRelationsNativeAuditString($item['id'] ?? null, sprintf('Relations native audit item %d has no id.', $index));
    requireRelationsNativeAuditString($item['api'] ?? null, sprintf('%s has no api.', $id));
    requireRelationsNativeAuditString($item['member'] ?? null, sprintf('%s has no member.', $id));
    $disposition = requireRelationsNativeAuditString($item['disposition'] ?? null, sprintf('%s has no disposition.', $id));
    $evidenceUrl = requireRelationsNativeAuditString($item['evidence_url'] ?? null, sprintf('%s has no evidence_url.', $id));
    requireRelationsNativeAuditString($item['notes'] ?? null, sprintf('%s has no notes.', $id));

    if (!str_starts_with($id, 'relations.native.') || isset($seenIds[$id])) {
        throw new RuntimeException(sprintf('Invalid or duplicate Relations native audit id %s.', $id));
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
        throw new RuntimeException(sprintf('%s must map to at least one Relations Bank record.', $id));
    }
    foreach ($recordIds as $recordId) {
        if (!is_string($recordId) || !isset($relationRecordIds[$recordId])) {
            throw new RuntimeException(sprintf('%s references missing Relations Bank record %s.', $id, (string) $recordId));
        }
    }

    if (in_array($disposition, ['OUT_OF_SURFACE', 'CORE_INTERNAL'], true)) {
        $ownerSurface = $item['owner_surface'] ?? null;
        if (!is_string($ownerSurface) || !isset($surfaceIds[$ownerSurface]) || $ownerSurface === 'relations') {
            throw new RuntimeException(sprintf('%s must name a different canonical owner_surface.', $id));
        }
    }
}

$requiredItems = [
    'relations.native.taxonomy.set.taxonomy',
    'relations.native.taxonomy.set.append',
    'relations.native.taxonomy.set.term_order',
    'relations.native.taxonomy.set.tt_id_identity',
    'relations.native.taxonomy.set.object_type_validation',
    'relations.native.taxonomy.set.slug_can_create_term',
    'relations.native.taxonomy.remove',
    'relations.native.taxonomy.delete_all',
    'relations.native.taxonomy.read.object_terms',
    'relations.native.taxonomy.query.object_ids',
    'relations.native.taxonomy.query.reverse_ids',
    'relations.native.taxonomy.query.exists',
    'relations.native.taxonomy.term_delete.cleanup',
    'relations.native.post_parent.write',
    'relations.native.post_parent.hierarchy_requirement',
    'relations.native.post_parent.loop_filter',
    'relations.native.post_parent.read_id',
    'relations.native.post_parent.read_ancestors',
    'relations.native.post_parent.query.parent',
    'relations.native.post_parent.query.parent_in',
    'relations.native.post_parent.query.parent_not_in',
    'relations.native.delete.post_term_cleanup',
    'relations.native.delete.post_child_reparent',
    'relations.native.delete.attachment_reparent',
    'relations.native.storage.term_relationships_direct_sql',
];
foreach ($requiredItems as $requiredId) {
    if (!isset($seenIds[$requiredId])) {
        throw new RuntimeException(sprintf('Relations native audit is missing mandatory disposition %s.', $requiredId));
    }
}

$coverage = $audit['coverage'] ?? null;
if (!is_array($coverage)) {
    throw new RuntimeException('Relations native audit is missing coverage counters.');
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
        throw new RuntimeException(sprintf('Relations native audit coverage.%s must be %d.', $key, $value));
    }
}
if ($counts['UNRESOLVED'] !== 0) {
    throw new RuntimeException('Relations NATIVE_AUDITED requires zero unresolved disposition items.');
}

$requiredNativeRecords = [
    'relations.nativeaudit.taxonomy_term_taxonomy_identity',
    'relations.nativeaudit.taxonomy_sort_dependency',
    'relations.nativeaudit.taxonomy_object_type_validation',
    'relations.nativeaudit.post_parent_hierarchy_requirement',
    'relations.nativeaudit.post_parent_loop_guard',
    'relations.nativeaudit.post_parent_delete_reparent',
    'relations.nativeaudit.taxonomy_object_delete_cleanup',
];
foreach ($requiredNativeRecords as $recordId) {
    $record = $relationRecords[$recordId] ?? null;
    if (!is_array($record)
        || ($record['classification'] ?? null) !== 'NATIVE_HARD'
        || ($record['horizon'] ?? null) !== 'CURRENT_NATIVE'
        || ($record['adoption'] ?? null) !== 'MUST_HAVE'
        || ($record['priority'] ?? null) !== 'P0_NATIVE'
    ) {
        throw new RuntimeException(sprintf('Relations native gap record %s is missing or non-canonical.', $recordId));
    }
}

if (count($relationRecordIds) !== 142) {
    throw new RuntimeException(sprintf('Relations native audit expects 142 Bank records after gap closure; found %d.', count($relationRecordIds)));
}

$progress = readRelationsNativeAuditJson($progressPath);
$rows = $progress['surface_status'] ?? null;
if (!is_array($rows)) {
    throw new RuntimeException('Options Bank progress is malformed.');
}
$relationsProgress = null;
foreach ($rows as $row) {
    if (is_array($row) && ($row['key'] ?? null) === 'relations') {
        $relationsProgress = $row;
        break;
    }
}
if (!is_array($relationsProgress)
    || ($relationsProgress['status'] ?? null) !== 'NATIVE_AUDITED'
    || ($relationsProgress['records'] ?? null) !== 142
) {
    throw new RuntimeException('Relations native audit and canonical progress truth disagree.');
}

fwrite(
    STDOUT,
    sprintf(
        "Relations native audit contract: PASS (%d dispositions, %d Bank, %d runtime, %d out-of-surface, %d legacy/internal, 0 unresolved).\n",
        count($items),
        $counts['BANK_RECORD'],
        $counts['SYSTEM_RUNTIME'],
        $counts['OUT_OF_SURFACE'],
        $counts['LEGACY_COMPATIBILITY'] + $counts['CORE_INTERNAL'],
    ),
);
