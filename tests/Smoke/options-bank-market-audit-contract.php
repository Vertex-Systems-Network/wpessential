<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$auditPath = $root . '/config/product/options-bank-audits/fields-market-ecosystem.json';
$schemaPath = $root . '/config/product/options-bank-market-audit.schema.json';
$surfaceRegistryPath = $root . '/config/product/competitor-parity-surfaces.json';
$progressPath = $root . '/config/product/options-bank-progress.json';
$bankDirectory = $root . '/config/product/options-bank';

/** @return array<string, mixed> */
function readMarketAuditJson(string $path): array
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
function requireMarketAuditString($value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }

    return $value;
}

/** @param mixed $value */
function requireMarketAuditList($value, string $message): array
{
    if (!is_array($value)) {
        throw new RuntimeException($message);
    }

    return $value;
}

/** @param array<int, string> $urls */
function validateMarketEvidenceUrls(array $urls, string $context): void
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

// Dependency-free smoke contract: schema must at least remain parseable JSON.
readMarketAuditJson($schemaPath);

$registry = readMarketAuditJson($surfaceRegistryPath);
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

/** @var array<string, true> $fieldsBankIds */
$fieldsBankIds = [];
$bankFiles = glob($bankDirectory . '/*.json');
if ($bankFiles === false) {
    throw new RuntimeException('Unable to enumerate Options Bank shards.');
}
foreach ($bankFiles as $file) {
    $bank = readMarketAuditJson($file);
    $surface = $bank['surface'] ?? null;
    $records = $bank['records'] ?? null;
    if (!is_array($surface) || !is_string($surface['key'] ?? null) || !is_array($records)) {
        throw new RuntimeException(sprintf('Invalid Options Bank shard: %s', $file));
    }
    if ($surface['key'] !== 'fields') {
        continue;
    }
    foreach ($records as $record) {
        if (!is_array($record) || !is_string($record['id'] ?? null)) {
            throw new RuntimeException(sprintf('Invalid Fields Bank record in %s.', $file));
        }
        $fieldsBankIds[$record['id']] = true;
    }
}

$audit = readMarketAuditJson($auditPath);
if (($audit['schema_version'] ?? null) !== 1 || ($audit['bank_version'] ?? null) !== 'v1') {
    throw new RuntimeException('Fields market audit has an unsupported version.');
}

$surface = $audit['surface'] ?? null;
if (!is_array($surface) || ($surface['id'] ?? null) !== 3 || ($surface['key'] ?? null) !== 'fields') {
    throw new RuntimeException('Fields market audit must target canonical Surface 3 / fields.');
}

$snapshot = $audit['snapshot'] ?? null;
if (!is_array($snapshot)) {
    throw new RuntimeException('Fields market audit is missing snapshot metadata.');
}
requireMarketAuditString($snapshot['date'] ?? null, 'Fields market audit is missing snapshot.date.');
requireMarketAuditString($snapshot['notes'] ?? null, 'Fields market audit is missing snapshot.notes.');

$status = requireMarketAuditString($audit['status'] ?? null, 'Fields market audit has no status.');
if (!in_array($status, ['MARKET_AUDIT_IN_PROGRESS', 'MARKET_AUDITED'], true)) {
    throw new RuntimeException(sprintf('Fields market audit has invalid status %s.', $status));
}

$requiredFamilies = requireMarketAuditList(
    $audit['required_families'] ?? null,
    'Fields market audit is missing required_families.',
);
$expectedFamilies = [
    'field_definition',
    'composition_behavior',
    'data_model',
    'api_integration',
    'governance_editor',
    'portability_extensibility',
];
if ($requiredFamilies !== $expectedFamilies) {
    throw new RuntimeException('Fields market audit required_families must match the canonical six-family matrix.');
}
$familyLookup = array_fill_keys($requiredFamilies, true);

$requiredPrimary = [
    'acf',
    'secure_custom_fields',
    'meta_box',
    'pods',
    'cmb2',
    'carbon_fields',
    'fieldmanager',
    'jetengine',
    'redux',
    'acf_extended',
    'toolset_types',
    'acpt',
];
$requiredSpecialists = [
    'smart_custom_fields',
    'wck',
    'atshift_fields',
    'modern_fields',
    'native_custom_fields',
    'open_fields',
    'massoftind_field_builder',
    'cptify',
    'onemeta',
    'ozy_custom_fields',
    'field_forge',
    'yaml_custom_fields',
    'meta_field_block',
    'piklist',
    'cmb2_extensions',
];

$primaryProviders = requireMarketAuditList(
    $audit['primary_providers'] ?? null,
    'Fields market audit has no primary_providers.',
);
$specialistProviders = requireMarketAuditList(
    $audit['specialist_providers'] ?? null,
    'Fields market audit has no specialist_providers.',
);

/** @var array<string, true> $providerIds */
$providerIds = [];
/** @var array<string, array<string, array<int, string>>> $primaryMaps */
$primaryMaps = [];
$familyMappings = 0;
$nonApplicableFamilyCells = 0;
$bankRecordReferences = 0;

foreach ($primaryProviders as $index => $provider) {
    if (!is_array($provider)) {
        throw new RuntimeException(sprintf('Primary provider %d is invalid.', $index));
    }

    $id = requireMarketAuditString($provider['id'] ?? null, sprintf('Primary provider %d has no id.', $index));
    requireMarketAuditString($provider['name'] ?? null, sprintf('Primary provider %s has no name.', $id));
    if (isset($providerIds[$id])) {
        throw new RuntimeException(sprintf('Fields market audit duplicates provider id %s.', $id));
    }
    $providerIds[$id] = true;

    $evidence = requireMarketAuditList($provider['evidence'] ?? null, sprintf('Primary provider %s has no evidence.', $id));
    validateMarketEvidenceUrls($evidence, sprintf('Primary provider %s', $id));

    $familyMap = requireMarketAuditList($provider['family_map'] ?? null, sprintf('Primary provider %s has no family_map.', $id));
    $nonApplicable = requireMarketAuditList(
        $provider['non_applicable_families'] ?? null,
        sprintf('Primary provider %s has no non_applicable_families.', $id),
    );

    $mapped = [];
    foreach ($familyMap as $family => $recordIds) {
        if (!is_string($family) || !isset($familyLookup[$family])) {
            throw new RuntimeException(sprintf('Primary provider %s maps unknown family %s.', $id, (string) $family));
        }
        if (!is_array($recordIds) || $recordIds === []) {
            throw new RuntimeException(sprintf('Primary provider %s family %s must map to Bank records.', $id, $family));
        }

        $mapped[$family] = true;
        ++$familyMappings;
        $primaryMaps[$id][$family] = [];

        foreach ($recordIds as $recordId) {
            if (!is_string($recordId) || !isset($fieldsBankIds[$recordId])) {
                throw new RuntimeException(sprintf('Primary provider %s family %s references missing Fields Bank record %s.', $id, $family, (string) $recordId));
            }
            $primaryMaps[$id][$family][] = $recordId;
            ++$bankRecordReferences;
        }
    }

    $notApplicable = [];
    foreach ($nonApplicable as $family) {
        if (!is_string($family) || !isset($familyLookup[$family])) {
            throw new RuntimeException(sprintf('Primary provider %s has unknown non-applicable family %s.', $id, (string) $family));
        }
        if (isset($mapped[$family]) || isset($notApplicable[$family])) {
            throw new RuntimeException(sprintf('Primary provider %s duplicates family disposition %s.', $id, $family));
        }
        $notApplicable[$family] = true;
        ++$nonApplicableFamilyCells;
    }

    if (count($mapped) + count($notApplicable) !== count($requiredFamilies)) {
        throw new RuntimeException(sprintf('Primary provider %s must disposition every required capability family.', $id));
    }
}

$primaryIds = array_keys($providerIds);
sort($primaryIds);
$expectedPrimary = $requiredPrimary;
sort($expectedPrimary);
if ($primaryIds !== $expectedPrimary) {
    throw new RuntimeException('Fields market audit primary provider roster does not match the required benchmark set.');
}

$specialistSeen = [];
foreach ($specialistProviders as $index => $provider) {
    if (!is_array($provider)) {
        throw new RuntimeException(sprintf('Specialist provider %d is invalid.', $index));
    }

    $id = requireMarketAuditString($provider['id'] ?? null, sprintf('Specialist provider %d has no id.', $index));
    requireMarketAuditString($provider['name'] ?? null, sprintf('Specialist provider %s has no name.', $id));
    if (isset($providerIds[$id]) || isset($specialistSeen[$id])) {
        throw new RuntimeException(sprintf('Fields market audit duplicates specialist provider id %s.', $id));
    }
    $specialistSeen[$id] = true;

    $evidence = requireMarketAuditList($provider['evidence'] ?? null, sprintf('Specialist provider %s has no evidence.', $id));
    validateMarketEvidenceUrls($evidence, sprintf('Specialist provider %s', $id));

    $recordIds = requireMarketAuditList(
        $provider['bank_record_ids'] ?? null,
        sprintf('Specialist provider %s has no Bank coverage.', $id),
    );
    if ($recordIds === []) {
        throw new RuntimeException(sprintf('Specialist provider %s must map to at least one Bank record.', $id));
    }
    foreach ($recordIds as $recordId) {
        if (!is_string($recordId) || !isset($fieldsBankIds[$recordId])) {
            throw new RuntimeException(sprintf('Specialist provider %s references missing Fields Bank record %s.', $id, (string) $recordId));
        }
        ++$bankRecordReferences;
    }
}

$specialistIds = array_keys($specialistSeen);
sort($specialistIds);
$expectedSpecialists = $requiredSpecialists;
sort($expectedSpecialists);
if ($specialistIds !== $expectedSpecialists) {
    throw new RuntimeException('Fields market audit specialist provider roster does not match the required long-tail set.');
}

$requiredGapMappings = [
    ['acf', 'api_integration', 'fields.marketaudit.schema_org_property'],
    ['acf', 'api_integration', 'fields.marketaudit.schema_org_output_format'],
    ['acf', 'api_integration', 'fields.marketaudit.group_ai_access'],
    ['acf', 'api_integration', 'fields.marketaudit.group_ai_description'],
    ['pods', 'api_integration', 'fields.marketaudit.rest_readable'],
    ['pods', 'api_integration', 'fields.marketaudit.rest_writable'],
    ['pods', 'api_integration', 'fields.marketaudit.relation_rest_response_type'],
    ['pods', 'api_integration', 'fields.marketaudit.relation_rest_depth'],
    ['acpt', 'data_model', 'fields.marketaudit.custom_table_foreign_keys'],
];
foreach ($requiredGapMappings as [$providerId, $family, $recordId]) {
    if (!in_array($recordId, $primaryMaps[$providerId][$family] ?? [], true)) {
        throw new RuntimeException(sprintf('Current market gap %s must remain mapped under %s/%s.', $recordId, $providerId, $family));
    }
}

$extraDispositions = requireMarketAuditList(
    $audit['extra_dispositions'] ?? null,
    'Fields market audit is missing extra_dispositions.',
);
$allowedExtraDispositions = [
    'OUT_OF_SURFACE',
    'IMPLEMENTATION_PATTERN',
    'DEFERRED_WITH_REASON',
    'REJECTED_UNSAFE',
    'UNRESOLVED',
];
$extraSeen = [];
$unresolved = 0;
foreach ($extraDispositions as $index => $item) {
    if (!is_array($item)) {
        throw new RuntimeException(sprintf('Extra market disposition %d is invalid.', $index));
    }

    $id = requireMarketAuditString($item['id'] ?? null, sprintf('Extra market disposition %d has no id.', $index));
    $provider = requireMarketAuditString($item['provider'] ?? null, sprintf('Extra market disposition %s has no provider.', $id));
    $disposition = requireMarketAuditString($item['disposition'] ?? null, sprintf('Extra market disposition %s has no disposition.', $id));
    $evidenceUrl = requireMarketAuditString($item['evidence_url'] ?? null, sprintf('Extra market disposition %s has no evidence_url.', $id));
    requireMarketAuditString($item['notes'] ?? null, sprintf('Extra market disposition %s has no notes.', $id));

    if (isset($extraSeen[$id])) {
        throw new RuntimeException(sprintf('Fields market audit duplicates extra disposition id %s.', $id));
    }
    $extraSeen[$id] = true;

    if ($provider !== 'ecosystem' && !isset($providerIds[$provider]) && !isset($specialistSeen[$provider])) {
        throw new RuntimeException(sprintf('Extra market disposition %s references unknown provider %s.', $id, $provider));
    }
    if (!in_array($disposition, $allowedExtraDispositions, true)) {
        throw new RuntimeException(sprintf('Extra market disposition %s has invalid disposition %s.', $id, $disposition));
    }
    if (!str_starts_with($evidenceUrl, 'https://')) {
        throw new RuntimeException(sprintf('Extra market disposition %s has an invalid evidence URL.', $id));
    }

    if ($disposition === 'OUT_OF_SURFACE') {
        $ownerSurface = $item['owner_surface'] ?? null;
        if (!is_string($ownerSurface) || !isset($surfaceIds[$ownerSurface]) || $ownerSurface === 'fields') {
            throw new RuntimeException(sprintf('Extra market disposition %s must name a different canonical owner_surface.', $id));
        }
    }

    if ($disposition === 'UNRESOLVED') {
        ++$unresolved;
    }
}

$coverage = $audit['coverage'] ?? null;
if (!is_array($coverage)) {
    throw new RuntimeException('Fields market audit is missing coverage counters.');
}
$expectedCoverage = [
    'primary_providers' => count($primaryProviders),
    'specialist_providers' => count($specialistProviders),
    'family_mappings' => $familyMappings,
    'non_applicable_family_cells' => $nonApplicableFamilyCells,
    'bank_record_references' => $bankRecordReferences,
    'extra_dispositions' => count($extraDispositions),
    'unresolved' => $unresolved,
];
foreach ($expectedCoverage as $key => $value) {
    if (($coverage[$key] ?? null) !== $value) {
        throw new RuntimeException(sprintf('Fields market audit coverage.%s must be %d.', $key, $value));
    }
}

$progress = readMarketAuditJson($progressPath);
$progressRows = $progress['surface_status'] ?? null;
if (!is_array($progressRows)) {
    throw new RuntimeException('Options Bank progress is malformed.');
}
$fieldsProgressStatus = null;
foreach ($progressRows as $row) {
    if (is_array($row) && ($row['key'] ?? null) === 'fields') {
        $fieldsProgressStatus = $row['status'] ?? null;
        break;
    }
}

if ($status === 'MARKET_AUDITED') {
    if ($unresolved !== 0) {
        throw new RuntimeException('MARKET_AUDITED requires zero unresolved market dispositions.');
    }
    if ($fieldsProgressStatus !== 'MARKET_AUDITED' && $fieldsProgressStatus !== 'BANK_REVIEWED') {
        throw new RuntimeException('MARKET_AUDITED audit requires Fields progress to be MARKET_AUDITED or later.');
    }
}

fwrite(
    STDOUT,
    sprintf(
        "Fields market audit contract: PASS (%d primary, %d specialist, %d family mappings, %d Bank references, 0 unresolved).\n",
        count($primaryProviders),
        count($specialistProviders),
        $familyMappings,
        $bankRecordReferences,
    ),
);
