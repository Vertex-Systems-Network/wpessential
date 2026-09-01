<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$auditPath = $root . '/config/product/options-bank-audits/columns-native-wordpress.json';
$schemaPath = $root . '/config/product/options-bank-native-audit.schema.json';
$surfaceRegistryPath = $root . '/config/product/competitor-parity-surfaces.json';
$bankDirectory = $root . '/config/product/options-bank';

/** @return array<string, mixed> */
function readColumnsNativeJson(string $path): array
{
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException(sprintf('Unable to read %s.', $path));
    }

    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        throw new RuntimeException(sprintf('%s must contain a JSON object.', $path));
    }

    return $data;
}

/** @param mixed $value */
function requireColumnsNativeString($value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }

    return $value;
}

readColumnsNativeJson($schemaPath);
$registry = readColumnsNativeJson($surfaceRegistryPath);
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
if (($surfaceIds['columns'] ?? null) !== 8) {
    throw new RuntimeException('Canonical Surface 8 must remain columns.');
}

/** @var array<string, true> $bankRecordIds */
$bankRecordIds = [];
$bankFiles = glob($bankDirectory . '/columns*.json');
if ($bankFiles === false || $bankFiles === []) {
    throw new RuntimeException('Unable to enumerate Admin Columns Options Bank shards.');
}
foreach ($bankFiles as $file) {
    $bank = readColumnsNativeJson($file);
    if (($bank['surface']['id'] ?? null) !== 8 || ($bank['surface']['key'] ?? null) !== 'columns' || !is_array($bank['records'] ?? null)) {
        throw new RuntimeException(sprintf('Invalid Admin Columns Options Bank shard: %s.', $file));
    }
    foreach ($bank['records'] as $record) {
        if (!is_array($record) || !is_string($record['id'] ?? null) || isset($bankRecordIds[$record['id']])) {
            throw new RuntimeException(sprintf('Admin Columns Bank record IDs must be valid and unique in %s.', $file));
        }
        $bankRecordIds[$record['id']] = true;
    }
}
if (count($bankRecordIds) !== 214) {
    throw new RuntimeException(sprintf('Admin Columns native audit expects 214 unique Bank records; found %d.', count($bankRecordIds)));
}

$audit = readColumnsNativeJson($auditPath);
if (($audit['schema_version'] ?? null) !== 1 || ($audit['bank_version'] ?? null) !== 'v1') {
    throw new RuntimeException('Admin Columns native audit has an unsupported version.');
}
if (($audit['surface']['id'] ?? null) !== 8 || ($audit['surface']['key'] ?? null) !== 'columns') {
    throw new RuntimeException('Admin Columns native audit must target canonical Surface 8 / columns.');
}

$status = requireColumnsNativeString($audit['status'] ?? null, 'Admin Columns native audit has no status.');
if (!in_array($status, ['NATIVE_AUDIT_IN_PROGRESS', 'NATIVE_AUDITED'], true)) {
    throw new RuntimeException(sprintf('Admin Columns native audit has invalid status %s.', $status));
}

$snapshot = $audit['snapshot'] ?? null;
if (!is_array($snapshot)) {
    throw new RuntimeException('Admin Columns native audit is missing snapshot metadata.');
}
requireColumnsNativeString($snapshot['date'] ?? null, 'Admin Columns native audit is missing snapshot.date.');
requireColumnsNativeString($snapshot['wordpress_target'] ?? null, 'Admin Columns native audit is missing snapshot.wordpress_target.');
$sources = $snapshot['sources'] ?? null;
if (!is_array($sources) || $sources === []) {
    throw new RuntimeException('Admin Columns native audit must identify primary WordPress sources.');
}
foreach ($sources as $source) {
    if (!is_array($source)) {
        throw new RuntimeException('Admin Columns native audit contains an invalid source row.');
    }
    requireColumnsNativeString($source['name'] ?? null, 'Admin Columns native source is missing name.');
    $url = requireColumnsNativeString($source['url'] ?? null, 'Admin Columns native source is missing url.');
    if (!str_starts_with($url, 'https://developer.wordpress.org/')) {
        throw new RuntimeException(sprintf('Admin Columns native source must cite Developer.WordPress.org: %s.', $url));
    }
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
if (!is_array($items) || count($items) !== 29) {
    throw new RuntimeException('Admin Columns native audit must contain exactly 29 reviewed disposition items.');
}

$seenIds = [];
$counts = array_fill_keys($allowedDispositions, 0);
foreach ($items as $index => $item) {
    if (!is_array($item)) {
        throw new RuntimeException(sprintf('Admin Columns native audit item %d is invalid.', $index));
    }

    $id = requireColumnsNativeString($item['id'] ?? null, sprintf('Admin Columns native audit item %d has no id.', $index));
    requireColumnsNativeString($item['api'] ?? null, sprintf('Admin Columns native audit item %s has no api.', $id));
    requireColumnsNativeString($item['member'] ?? null, sprintf('Admin Columns native audit item %s has no member.', $id));
    $disposition = requireColumnsNativeString($item['disposition'] ?? null, sprintf('Admin Columns native audit item %s has no disposition.', $id));
    $evidenceUrl = requireColumnsNativeString($item['evidence_url'] ?? null, sprintf('Admin Columns native audit item %s has no evidence_url.', $id));
    requireColumnsNativeString($item['notes'] ?? null, sprintf('Admin Columns native audit item %s has no notes.', $id));

    if (!str_starts_with($id, 'columns.native.') || isset($seenIds[$id])) {
        throw new RuntimeException(sprintf('Admin Columns native audit item ID is invalid or duplicated: %s.', $id));
    }
    $seenIds[$id] = true;

    if (!in_array($disposition, $allowedDispositions, true)) {
        throw new RuntimeException(sprintf('Admin Columns native audit item %s has invalid disposition %s.', $id, $disposition));
    }
    ++$counts[$disposition];

    if (!str_starts_with($evidenceUrl, 'https://developer.wordpress.org/')) {
        throw new RuntimeException(sprintf('Admin Columns native audit item %s must cite Developer.WordPress.org.', $id));
    }

    $recordIds = $item['bank_record_ids'] ?? [];
    if (!is_array($recordIds)) {
        throw new RuntimeException(sprintf('Admin Columns native audit item %s has invalid bank_record_ids.', $id));
    }
    if (in_array($disposition, ['BANK_RECORD', 'PROVIDER_MAPPING'], true) && $recordIds === []) {
        throw new RuntimeException(sprintf('Admin Columns native audit item %s must map to at least one Bank record.', $id));
    }
    foreach ($recordIds as $recordId) {
        if (!is_string($recordId) || !isset($bankRecordIds[$recordId])) {
            throw new RuntimeException(sprintf('Admin Columns native audit item %s references missing Bank record %s.', $id, (string) $recordId));
        }
    }

    if ($disposition === 'OUT_OF_SURFACE') {
        $ownerSurface = $item['owner_surface'] ?? null;
        if (!is_string($ownerSurface) || !isset($surfaceIds[$ownerSurface]) || $ownerSurface === 'columns') {
            throw new RuntimeException(sprintf('Admin Columns native audit item %s must name a different canonical owner_surface.', $id));
        }
    }
}

$expectedCounts = [
    'BANK_RECORD' => 16,
    'PROVIDER_MAPPING' => 4,
    'SYSTEM_RUNTIME' => 4,
    'OUT_OF_SURFACE' => 4,
    'LEGACY_COMPATIBILITY' => 0,
    'CORE_INTERNAL' => 1,
    'UNRESOLVED' => 0,
];
foreach ($expectedCounts as $disposition => $expected) {
    if ($counts[$disposition] !== $expected) {
        throw new RuntimeException(sprintf('Admin Columns native audit %s count must be %d; found %d.', $disposition, $expected, $counts[$disposition]));
    }
}

$coverage = $audit['coverage'] ?? null;
$expectedCoverage = [
    'items' => 29,
    'bank_record' => 16,
    'provider_mapping' => 4,
    'system_runtime' => 4,
    'out_of_surface' => 4,
    'legacy_compatibility' => 0,
    'core_internal' => 1,
    'unresolved' => 0,
];
if (!is_array($coverage)) {
    throw new RuntimeException('Admin Columns native audit is missing coverage counters.');
}
foreach ($expectedCoverage as $key => $expected) {
    if (($coverage[$key] ?? null) !== $expected) {
        throw new RuntimeException(sprintf('Admin Columns native audit coverage.%s must be %d.', $key, $expected));
    }
}

printf("Admin Columns native audit contract: PASS (%s; 29 dispositions, 214 Bank records, 0 unresolved).\n", $status);
