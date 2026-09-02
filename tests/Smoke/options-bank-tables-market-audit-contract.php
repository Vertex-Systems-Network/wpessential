<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);

/** @return array<string, mixed> */
function readTablesMarketJson(string $path): array
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

$records = [];
foreach (glob($root . '/config/product/options-bank/tables--*.json') ?: [] as $file) {
    $bank = readTablesMarketJson($file);
    foreach (($bank['records'] ?? []) as $record) {
        if (is_array($record) && is_string($record['id'] ?? null)) {
            $records[$record['id']] = true;
        }
    }
}
if (count($records) !== 165) {
    throw new RuntimeException('Surface 7 market audit requires the complete 165-record Bank candidate.');
}

$audit = readTablesMarketJson($root . '/config/product/options-bank-audits/tables-market-ecosystem.json');
if (($audit['schema_version'] ?? null) !== 1
    || ($audit['bank_version'] ?? null) !== 'v1'
    || ($audit['surface']['id'] ?? null) !== 7
    || ($audit['surface']['key'] ?? null) !== 'tables'
    || ($audit['status'] ?? null) !== 'MARKET_AUDITED') {
    throw new RuntimeException('Surface 7 market audit identity/status is invalid.');
}

$requiredFamilies = $audit['required_families'] ?? null;
if (!is_array($requiredFamilies) || count($requiredFamilies) !== 7) {
    throw new RuntimeException('Surface 7 market audit must define exactly seven capability families.');
}
$requiredFamilySet = array_fill_keys($requiredFamilies, true);
$providers = $audit['primary_providers'] ?? null;
if (!is_array($providers) || count($providers) !== 4) {
    throw new RuntimeException('Surface 7 market audit must contain exactly four primary providers.');
}

$providerIds = [];
$familyMappings = 0;
$nonApplicable = 0;
$bankReferences = 0;
foreach ($providers as $provider) {
    if (!is_array($provider) || !is_string($provider['id'] ?? null) || isset($providerIds[$provider['id']])) {
        throw new RuntimeException('Surface 7 primary provider IDs must be unique strings.');
    }
    $providerIds[$provider['id']] = true;
    $mapped = $provider['family_map'] ?? null;
    $na = $provider['non_applicable_families'] ?? null;
    if (!is_array($mapped) || !is_array($na)) {
        throw new RuntimeException(sprintf('Provider %s is missing family disposition metadata.', $provider['id']));
    }
    $covered = [];
    foreach ($mapped as $family => $recordIds) {
        if (!isset($requiredFamilySet[$family]) || !is_array($recordIds) || $recordIds === []) {
            throw new RuntimeException(sprintf('Provider %s has an invalid family mapping.', $provider['id']));
        }
        ++$familyMappings;
        $covered[$family] = true;
        foreach ($recordIds as $recordId) {
            if (!is_string($recordId) || !isset($records[$recordId])) {
                throw new RuntimeException(sprintf('Provider %s references a missing Bank record.', $provider['id']));
            }
            ++$bankReferences;
        }
    }
    foreach ($na as $family) {
        if (!is_string($family) || !isset($requiredFamilySet[$family]) || isset($covered[$family])) {
            throw new RuntimeException(sprintf('Provider %s has an invalid non-applicable family.', $provider['id']));
        }
        ++$nonApplicable;
        $covered[$family] = true;
    }
    if (count($covered) !== count($requiredFamilySet)) {
        throw new RuntimeException(sprintf('Provider %s does not disposition every required market family.', $provider['id']));
    }
}

$specialists = $audit['specialist_providers'] ?? null;
if (!is_array($specialists) || count($specialists) !== 1) {
    throw new RuntimeException('Surface 7 market audit must contain exactly one specialist provider.');
}
foreach ($specialists as $provider) {
    foreach (($provider['bank_record_ids'] ?? []) as $recordId) {
        if (!is_string($recordId) || !isset($records[$recordId])) {
            throw new RuntimeException('Specialist provider references a missing Bank record.');
        }
        ++$bankReferences;
    }
}

$extras = $audit['extra_dispositions'] ?? null;
if (!is_array($extras) || count($extras) !== 6) {
    throw new RuntimeException('Surface 7 market audit must preserve six explicit extra dispositions.');
}
$rejectedDba = false;
foreach ($extras as $extra) {
    if (($extra['id'] ?? null) === 'wp_data_access.generic_dba' && ($extra['disposition'] ?? null) === 'REJECTED_UNSAFE') {
        $rejectedDba = true;
    }
}
if (!$rejectedDba) {
    throw new RuntimeException('Surface 7 market audit must reject generic DBA console parity.');
}

$coverage = $audit['coverage'] ?? [];
$expected = ['primary_providers'=>4,'specialist_providers'=>1,'family_mappings'=>19,'non_applicable_family_cells'=>9,'bank_record_references'=>80,'extra_dispositions'=>6,'unresolved'=>0];
foreach ($expected as $key => $value) {
    if (($coverage[$key] ?? null) !== $value) {
        throw new RuntimeException(sprintf('Surface 7 market audit coverage %s disagrees with certified truth.', $key));
    }
}
if ($familyMappings !== 19 || $nonApplicable !== 9 || $bankReferences !== 80) {
    throw new RuntimeException(sprintf('Surface 7 market map counters drifted (families=%d, n/a=%d, refs=%d).', $familyMappings, $nonApplicable, $bankReferences));
}

printf("Surface 7 market audit contract: PASS (4 primary + 1 specialist; 19 family mappings; 80 Bank refs; 0 unresolved).\n");
