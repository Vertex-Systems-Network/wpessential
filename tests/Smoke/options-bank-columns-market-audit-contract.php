<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$auditPath = $root . '/config/product/options-bank-audits/columns-market-ecosystem.json';
$schemaPath = $root . '/config/product/options-bank-market-audit.schema.json';
$surfaceRegistryPath = $root . '/config/product/competitor-parity-surfaces.json';
$bankDirectory = $root . '/config/product/options-bank';

/** @return array<string, mixed> */
function readColumnsMarketJson(string $path): array
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
function requireColumnsMarketString($value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }
    return $value;
}

/** @param mixed $value
 * @return array<mixed>
 */
function requireColumnsMarketArray($value, string $message): array
{
    if (!is_array($value)) {
        throw new RuntimeException($message);
    }
    return $value;
}

/** @param array<mixed> $urls */
function validateColumnsMarketEvidence(array $urls, string $context): void
{
    if ($urls === []) {
        throw new RuntimeException(sprintf('%s must provide at least one evidence URL.', $context));
    }
    foreach ($urls as $url) {
        if (!is_string($url) || !str_starts_with($url, 'https://')) {
            throw new RuntimeException(sprintf('%s contains an invalid evidence URL.', $context));
        }
    }
}

readColumnsMarketJson($schemaPath);
$registry = readColumnsMarketJson($surfaceRegistryPath);
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
    $bank = readColumnsMarketJson($file);
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
    throw new RuntimeException(sprintf('Admin Columns market audit expects 214 unique Bank records; found %d.', count($bankRecordIds)));
}

$audit = readColumnsMarketJson($auditPath);
if (($audit['schema_version'] ?? null) !== 1 || ($audit['bank_version'] ?? null) !== 'v1') {
    throw new RuntimeException('Admin Columns market audit has an unsupported version.');
}
if (($audit['surface']['id'] ?? null) !== 8 || ($audit['surface']['key'] ?? null) !== 'columns') {
    throw new RuntimeException('Admin Columns market audit must target canonical Surface 8 / columns.');
}
$status = requireColumnsMarketString($audit['status'] ?? null, 'Admin Columns market audit has no status.');
if (!in_array($status, ['MARKET_AUDIT_IN_PROGRESS', 'MARKET_AUDITED'], true)) {
    throw new RuntimeException(sprintf('Admin Columns market audit has invalid status %s.', $status));
}

$snapshot = $audit['snapshot'] ?? null;
if (!is_array($snapshot)) {
    throw new RuntimeException('Admin Columns market audit is missing snapshot metadata.');
}
requireColumnsMarketString($snapshot['date'] ?? null, 'Admin Columns market audit is missing snapshot.date.');
requireColumnsMarketString($snapshot['notes'] ?? null, 'Admin Columns market audit is missing snapshot.notes.');

$requiredFamilies = requireColumnsMarketArray($audit['required_families'] ?? null, 'Admin Columns market audit is missing required_families.');
$expectedFamilies = [
    'column_views_layout',
    'source_formatting',
    'sorting_filtering',
    'editing_workflows',
    'export_portability',
    'ecosystem_integrations',
];
if ($requiredFamilies !== $expectedFamilies) {
    throw new RuntimeException('Admin Columns market audit required_families must match the canonical six-family matrix.');
}
$familyLookup = array_fill_keys($requiredFamilies, true);

$primaryProviders = requireColumnsMarketArray($audit['primary_providers'] ?? null, 'Admin Columns market audit has no primary_providers.');
$specialistProviders = requireColumnsMarketArray($audit['specialist_providers'] ?? null, 'Admin Columns market audit has no specialist_providers.');

$expectedPrimary = ['admin_columns_pro', 'jetengine', 'meta_box'];
$expectedSpecialists = ['admin_columns_acf'];
$providerIds = [];
$familyMappings = 0;
$nonApplicableFamilyCells = 0;
$bankRecordReferences = 0;

foreach ($primaryProviders as $index => $provider) {
    if (!is_array($provider)) {
        throw new RuntimeException(sprintf('Admin Columns primary provider %d is invalid.', $index));
    }
    $id = requireColumnsMarketString($provider['id'] ?? null, sprintf('Admin Columns primary provider %d has no id.', $index));
    requireColumnsMarketString($provider['name'] ?? null, sprintf('Admin Columns primary provider %s has no name.', $id));
    if (isset($providerIds[$id])) {
        throw new RuntimeException(sprintf('Admin Columns market audit duplicates provider id %s.', $id));
    }
    $providerIds[$id] = true;
    validateColumnsMarketEvidence(requireColumnsMarketArray($provider['evidence'] ?? null, sprintf('Admin Columns provider %s has no evidence.', $id)), sprintf('Admin Columns provider %s', $id));

    $familyMap = requireColumnsMarketArray($provider['family_map'] ?? null, sprintf('Admin Columns provider %s has no family_map.', $id));
    $nonApplicable = requireColumnsMarketArray($provider['non_applicable_families'] ?? null, sprintf('Admin Columns provider %s has no non_applicable_families.', $id));
    $mapped = [];
    foreach ($familyMap as $family => $recordIds) {
        if (!is_string($family) || !isset($familyLookup[$family]) || isset($mapped[$family]) || !is_array($recordIds) || $recordIds === []) {
            throw new RuntimeException(sprintf('Admin Columns provider %s has an invalid family mapping for %s.', $id, (string) $family));
        }
        $mapped[$family] = true;
        ++$familyMappings;
        foreach ($recordIds as $recordId) {
            if (!is_string($recordId) || !isset($bankRecordIds[$recordId])) {
                throw new RuntimeException(sprintf('Admin Columns provider %s family %s references missing Bank record %s.', $id, $family, (string) $recordId));
            }
            ++$bankRecordReferences;
        }
    }

    $notApplicable = [];
    foreach ($nonApplicable as $family) {
        if (!is_string($family) || !isset($familyLookup[$family]) || isset($mapped[$family]) || isset($notApplicable[$family])) {
            throw new RuntimeException(sprintf('Admin Columns provider %s has invalid non-applicable family %s.', $id, (string) $family));
        }
        $notApplicable[$family] = true;
        ++$nonApplicableFamilyCells;
    }
    if (count($mapped) + count($notApplicable) !== count($requiredFamilies)) {
        throw new RuntimeException(sprintf('Admin Columns provider %s must disposition every required capability family.', $id));
    }
}

$actualPrimary = array_keys($providerIds);
sort($actualPrimary);
sort($expectedPrimary);
if ($actualPrimary !== $expectedPrimary) {
    throw new RuntimeException('Admin Columns primary provider roster does not match the reviewed benchmark set.');
}

$specialistIds = [];
foreach ($specialistProviders as $index => $provider) {
    if (!is_array($provider)) {
        throw new RuntimeException(sprintf('Admin Columns specialist provider %d is invalid.', $index));
    }
    $id = requireColumnsMarketString($provider['id'] ?? null, sprintf('Admin Columns specialist provider %d has no id.', $index));
    requireColumnsMarketString($provider['name'] ?? null, sprintf('Admin Columns specialist provider %s has no name.', $id));
    if (isset($providerIds[$id]) || isset($specialistIds[$id])) {
        throw new RuntimeException(sprintf('Admin Columns market audit duplicates specialist provider id %s.', $id));
    }
    $specialistIds[$id] = true;
    validateColumnsMarketEvidence(requireColumnsMarketArray($provider['evidence'] ?? null, sprintf('Admin Columns specialist %s has no evidence.', $id)), sprintf('Admin Columns specialist %s', $id));
    $recordIds = requireColumnsMarketArray($provider['bank_record_ids'] ?? null, sprintf('Admin Columns specialist %s has no Bank coverage.', $id));
    if ($recordIds === []) {
        throw new RuntimeException(sprintf('Admin Columns specialist %s must map to Bank records.', $id));
    }
    foreach ($recordIds as $recordId) {
        if (!is_string($recordId) || !isset($bankRecordIds[$recordId])) {
            throw new RuntimeException(sprintf('Admin Columns specialist %s references missing Bank record %s.', $id, (string) $recordId));
        }
        ++$bankRecordReferences;
    }
}
$actualSpecialists = array_keys($specialistIds);
sort($actualSpecialists);
sort($expectedSpecialists);
if ($actualSpecialists !== $expectedSpecialists) {
    throw new RuntimeException('Admin Columns specialist provider roster does not match the reviewed benchmark set.');
}

$extraDispositions = requireColumnsMarketArray($audit['extra_dispositions'] ?? null, 'Admin Columns market audit is missing extra_dispositions.');
$allowedExtra = ['OUT_OF_SURFACE', 'IMPLEMENTATION_PATTERN', 'DEFERRED_WITH_REASON', 'REJECTED_UNSAFE', 'UNRESOLVED'];
$requiredExtraIds = [
    'admin_columns.performance_marketing',
    'jetengine.custom_callback_execution',
    'jetengine.relation_definition_semantics',
    'meta_box.field_definition_semantics',
];
$extraIds = [];
$unresolved = 0;
foreach ($extraDispositions as $index => $item) {
    if (!is_array($item)) {
        throw new RuntimeException(sprintf('Admin Columns extra disposition %d is invalid.', $index));
    }
    $id = requireColumnsMarketString($item['id'] ?? null, sprintf('Admin Columns extra disposition %d has no id.', $index));
    $provider = requireColumnsMarketString($item['provider'] ?? null, sprintf('Admin Columns extra disposition %s has no provider.', $id));
    $disposition = requireColumnsMarketString($item['disposition'] ?? null, sprintf('Admin Columns extra disposition %s has no disposition.', $id));
    $evidenceUrl = requireColumnsMarketString($item['evidence_url'] ?? null, sprintf('Admin Columns extra disposition %s has no evidence_url.', $id));
    requireColumnsMarketString($item['notes'] ?? null, sprintf('Admin Columns extra disposition %s has no notes.', $id));
    if (isset($extraIds[$id]) || (!isset($providerIds[$provider]) && !isset($specialistIds[$provider]) && $provider !== 'ecosystem')) {
        throw new RuntimeException(sprintf('Admin Columns extra disposition %s has invalid identity/provider.', $id));
    }
    $extraIds[$id] = true;
    if (!in_array($disposition, $allowedExtra, true) || !str_starts_with($evidenceUrl, 'https://')) {
        throw new RuntimeException(sprintf('Admin Columns extra disposition %s has invalid disposition/evidence.', $id));
    }
    if ($disposition === 'OUT_OF_SURFACE') {
        $owner = $item['owner_surface'] ?? null;
        if (!is_string($owner) || !isset($surfaceIds[$owner]) || $owner === 'columns') {
            throw new RuntimeException(sprintf('Admin Columns extra disposition %s must name a different canonical owner_surface.', $id));
        }
    }
    if ($disposition === 'UNRESOLVED') {
        ++$unresolved;
    }
}
$actualExtraIds = array_keys($extraIds);
sort($actualExtraIds);
sort($requiredExtraIds);
if ($actualExtraIds !== $requiredExtraIds || $unresolved !== 0) {
    throw new RuntimeException('Admin Columns market audit extra dispositions must preserve the reviewed zero-unresolved boundary set.');
}

$coverage = $audit['coverage'] ?? null;
$expectedCoverage = [
    'primary_providers' => 3,
    'specialist_providers' => 1,
    'family_mappings' => 15,
    'non_applicable_family_cells' => 3,
    'bank_record_references' => 57,
    'extra_dispositions' => 4,
    'unresolved' => 0,
];
if (!is_array($coverage)) {
    throw new RuntimeException('Admin Columns market audit is missing coverage counters.');
}
foreach ($expectedCoverage as $key => $expected) {
    if (($coverage[$key] ?? null) !== $expected) {
        throw new RuntimeException(sprintf('Admin Columns market audit coverage.%s must be %d.', $key, $expected));
    }
}
if ($familyMappings !== 15 || $nonApplicableFamilyCells !== 3 || $bankRecordReferences !== 57) {
    throw new RuntimeException('Admin Columns market audit derived coverage disagrees with reviewed expectations.');
}

printf("Admin Columns market audit contract: PASS (%s; 3 primary, 1 specialist, 57 Bank references, 0 unresolved).\n", $status);
