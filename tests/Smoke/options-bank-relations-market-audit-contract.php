<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);

/** @return array<string,mixed> */
function rmaJson(string $path): array
{
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException("Unable to read {$path}.");
    }
    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        throw new RuntimeException("Invalid JSON object: {$path}.");
    }
    return $data;
}

$schema = rmaJson($root . '/config/product/options-bank-market-audit.schema.json');
$surfaceSchema = $schema['properties']['surface']['properties'] ?? null;
if (!is_array($surfaceSchema)
    || ($surfaceSchema['id']['minimum'] ?? null) !== 1
    || ($surfaceSchema['id']['maximum'] ?? null) !== 56
    || array_key_exists('const', $surfaceSchema['id'])
    || ($surfaceSchema['key']['pattern'] ?? null) !== '^[a-z0-9][a-z0-9-]*$'
    || array_key_exists('const', $surfaceSchema['key'])) {
    throw new RuntimeException('Market-audit schema must be generic across canonical surfaces 1-56.');
}

$registry = rmaJson($root . '/config/product/competitor-parity-surfaces.json');
$canonicalKeys = [];
foreach (($registry['surfaces'] ?? []) as $row) {
    if (is_array($row) && is_int($row['id'] ?? null) && is_string($row['key'] ?? null)) {
        $canonicalKeys[$row['key']] = $row['id'];
    }
}
if (($canonicalKeys['relations'] ?? null) !== 4) {
    throw new RuntimeException('Canonical Surface 4 must remain relations.');
}

$relationRecords = [];
foreach (glob($root . '/config/product/options-bank/*.json') ?: [] as $file) {
    $bank = rmaJson($file);
    if (($bank['surface']['key'] ?? null) !== 'relations') {
        continue;
    }
    foreach (($bank['records'] ?? []) as $record) {
        $id = $record['id'] ?? null;
        if (!is_string($id) || isset($relationRecords[$id])) {
            throw new RuntimeException('Relations Bank record IDs must be valid and unique.');
        }
        $relationRecords[$id] = $record;
    }
}
if (count($relationRecords) !== 144) {
    throw new RuntimeException(sprintf('Relations market audit requires exactly 144 local Bank records; found %d.', count($relationRecords)));
}

$audit = rmaJson($root . '/config/product/options-bank-audits/relations-market-ecosystem.json');
if (($audit['schema_version'] ?? null) !== 1
    || ($audit['bank_version'] ?? null) !== 'v1'
    || ($audit['surface']['id'] ?? null) !== 4
    || ($audit['surface']['key'] ?? null) !== 'relations'
    || ($audit['status'] ?? null) !== 'MARKET_AUDITED') {
    throw new RuntimeException('Relations market certificate identity/status is invalid.');
}

$families = [
    'definition_cardinality', 'endpoint_direction', 'storage_pivot', 'editor_permissions',
    'query_traversal', 'api_integration', 'lifecycle_integrity', 'portability_ecosystem',
];
if (($audit['required_families'] ?? null) !== $families) {
    throw new RuntimeException('Relations market audit must use the canonical eight-family matrix.');
}
$familySet = array_fill_keys($families, true);
$requiredPrimary = ['acf', 'acpt', 'jetengine', 'meta_box', 'pods', 'toolset'];
$providerMaps = [];
$providerIds = [];
$familyMappings = 0;
$nonApplicable = 0;
$bankRefs = 0;

foreach (($audit['primary_providers'] ?? []) as $provider) {
    if (!is_array($provider) || !is_string($provider['id'] ?? null)) {
        throw new RuntimeException('Invalid Relations primary provider.');
    }
    $id = $provider['id'];
    if (isset($providerIds[$id])) {
        throw new RuntimeException("Duplicate Relations provider {$id}.");
    }
    $providerIds[$id] = true;
    foreach (($provider['evidence'] ?? []) as $url) {
        if (!is_string($url) || !str_starts_with($url, 'https://')) {
            throw new RuntimeException("Provider {$id} has invalid evidence.");
        }
    }
    $mapped = [];
    foreach (($provider['family_map'] ?? []) as $family => $ids) {
        if (!isset($familySet[$family]) || !is_array($ids) || $ids === []) {
            throw new RuntimeException("Provider {$id} has invalid family map.");
        }
        $mapped[$family] = true;
        ++$familyMappings;
        foreach ($ids as $recordId) {
            if (!is_string($recordId) || !isset($relationRecords[$recordId])) {
                throw new RuntimeException("Provider {$id} references missing Relations Bank record.");
            }
            $providerMaps[$id][$family][] = $recordId;
            ++$bankRefs;
        }
    }
    $na = [];
    foreach (($provider['non_applicable_families'] ?? []) as $family) {
        if (!is_string($family) || !isset($familySet[$family]) || isset($mapped[$family]) || isset($na[$family])) {
            throw new RuntimeException("Provider {$id} has invalid non-applicable family.");
        }
        $na[$family] = true;
        ++$nonApplicable;
    }
    if (count($mapped) + count($na) !== 8) {
        throw new RuntimeException("Provider {$id} must disposition all eight families.");
    }
}
$ids = array_keys($providerIds);
sort($ids);
if ($ids !== $requiredPrimary) {
    throw new RuntimeException('Relations market audit primary roster must be ACF, ACPT, JetEngine, Meta Box, Pods and Toolset.');
}

$specialists = $audit['specialist_providers'] ?? [];
if (!is_array($specialists) || count($specialists) !== 1 || ($specialists[0]['id'] ?? null) !== 'jetformbuilder') {
    throw new RuntimeException('Relations specialist roster must contain JetFormBuilder exactly once.');
}
foreach (($specialists[0]['bank_record_ids'] ?? []) as $recordId) {
    if (!is_string($recordId) || !isset($relationRecords[$recordId])) {
        throw new RuntimeException('JetFormBuilder references missing Relations Bank coverage.');
    }
    ++$bankRefs;
}

foreach ([
    ['jetengine', 'editor_permissions', 'relations.marketaudit.admin_filter'],
    ['meta_box', 'editor_permissions', 'relations.marketaudit.admin_filter'],
    ['jetengine', 'api_integration', 'relations.marketaudit.rest_read_policy'],
    ['meta_box', 'api_integration', 'relations.marketaudit.rest_read_policy'],
] as [$provider, $family, $recordId]) {
    if (!in_array($recordId, $providerMaps[$provider][$family] ?? [], true)) {
        throw new RuntimeException("Required market gap {$recordId} is not mapped under {$provider}/{$family}.");
    }
}

$extras = $audit['extra_dispositions'] ?? [];
$unresolved = 0;
foreach ($extras as $extra) {
    if (!is_array($extra) || !is_string($extra['evidence_url'] ?? null) || !str_starts_with($extra['evidence_url'], 'https://')) {
        throw new RuntimeException('Invalid Relations extra market disposition.');
    }
    if (($extra['disposition'] ?? null) === 'OUT_OF_SURFACE') {
        $owner = $extra['owner_surface'] ?? null;
        if (!is_string($owner) || !isset($canonicalKeys[$owner]) || $owner === 'relations') {
            throw new RuntimeException('OUT_OF_SURFACE disposition must name another canonical owner.');
        }
    }
    if (($extra['disposition'] ?? null) === 'UNRESOLVED') {
        ++$unresolved;
    }
}
$coverage = $audit['coverage'] ?? [];
$expectedCoverage = [
    'primary_providers' => 6,
    'specialist_providers' => 1,
    'family_mappings' => $familyMappings,
    'non_applicable_family_cells' => $nonApplicable,
    'bank_record_references' => $bankRefs,
    'extra_dispositions' => is_array($extras) ? count($extras) : -1,
    'unresolved' => $unresolved,
];
foreach ($expectedCoverage as $key => $value) {
    if (($coverage[$key] ?? null) !== $value) {
        throw new RuntimeException("Relations market coverage {$key} does not match recomputed truth.");
    }
}
if ($unresolved !== 0) {
    throw new RuntimeException('Relations market audit must close with zero unresolved items.');
}

$expectedGaps = [
    'relations.marketaudit.admin_filter' => ['admin_filter.enabled', 'WPE_HARD', 'HARD', 'CURRENT_MARKET', 'COMPETITIVE', 'P2_COMPETITIVE'],
    'relations.marketaudit.rest_read_policy' => ['permissions.rest_read', 'WPE_HARD', 'HARD', 'CURRENT_MARKET', 'COMPETITIVE', 'P2_COMPETITIVE'],
];
foreach ($expectedGaps as $id => $expected) {
    $record = $relationRecords[$id] ?? null;
    $actual = is_array($record) ? [
        $record['option_path'] ?? null, $record['classification'] ?? null, $record['hard_soft'] ?? null,
        $record['horizon'] ?? null, $record['adoption'] ?? null, $record['priority'] ?? null,
    ] : [];
    if ($actual !== $expected) {
        throw new RuntimeException("Relations market gap {$id} has drifted from its certified classification.");
    }
}

$progress = rmaJson($root . '/config/product/options-bank-progress.json');
$relationsProgress = null;
foreach (($progress['surface_status'] ?? []) as $row) {
    if (($row['id'] ?? null) === 4 && ($row['key'] ?? null) === 'relations') {
        $relationsProgress = $row;
        break;
    }
}
$certifiedProgressStates = ['MARKET_AUDITED', 'BANK_REVIEWED'];
if (!is_array($relationsProgress)
    || !in_array($relationsProgress['status'] ?? null, $certifiedProgressStates, true)
    || ($relationsProgress['records'] ?? null) !== count($relationRecords)) {
    throw new RuntimeException('Progress registry must retain Relations market-or-later certification and match the current local record count.');
}

printf("Relations market audit contract: PASS (6 primary, 1 specialist, %d family mappings, %d Bank references, %d extras, 0 unresolved; %d current Relations records).\n", $familyMappings, $bankRefs, count($extras), count($relationRecords));
