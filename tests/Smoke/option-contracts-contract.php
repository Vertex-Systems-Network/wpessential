<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);

/** @return array<string,mixed> */
function optionContractJson(string $path): array
{
    if (!is_file($path)) {
        throw new RuntimeException("Missing JSON: {$path}");
    }

    try {
        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new RuntimeException("Invalid JSON {$path}: {$exception->getMessage()}", 0, $exception);
    }

    if (!is_array($decoded)) {
        throw new RuntimeException("JSON root must be an object: {$path}");
    }

    return $decoded;
}

function optionContractString(mixed $value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }

    return $value;
}

function optionContractInt(mixed $value, string $message): int
{
    if (!is_int($value)) {
        throw new RuntimeException($message);
    }

    return $value;
}

/** @return array<mixed> */
function optionContractArray(mixed $value, string $message): array
{
    if (!is_array($value)) {
        throw new RuntimeException($message);
    }

    return $value;
}

/** @param list<string> $allowed */
function optionContractEnum(mixed $value, array $allowed, string $message): string
{
    $value = optionContractString($value, $message);
    if (!in_array($value, $allowed, true)) {
        throw new RuntimeException("{$message} Got {$value}.");
    }

    return $value;
}

function optionContractIdentifier(string $value, string $message): string
{
    if (preg_match('/^[a-z0-9][a-z0-9._-]*$/', $value) !== 1) {
        throw new RuntimeException($message);
    }

    return $value;
}

$lifecycle = [
    'BENCHMARKING' => 10,
    'CAPABILITY_INVENTORY_COMPLETE' => 20,
    'ATOMIC_INVENTORY_COMPLETE' => 30,
    'OPTION_CONTRACT_COMPLETE' => 40,
    'UX_CONTRACT_COMPLETE' => 50,
    'PRODUCT_PLANNED' => 60,
    'IMPLEMENTING' => 70,
    'RUNTIME_CERTIFIED' => 80,
    'PRODUCT_PARITY_CERTIFIED' => 90,
];

$schema = optionContractJson($root . '/config/product/option-contract.schema.json');
if (($schema['$id'] ?? null) !== 'https://wpessential.local/schema/product-option-contract.json') {
    throw new RuntimeException('Unexpected Atomic Option schema id.');
}
$schemaStatuses = optionContractArray($schema['properties']['status']['enum'] ?? null, 'Schema status enum missing.');
foreach (array_keys($lifecycle) as $status) {
    if (!in_array($status, $schemaStatuses, true)) {
        throw new RuntimeException("Schema lifecycle missing {$status}.");
    }
}
if (!isset($schema['properties']['source_projection'], $schema['$defs']['sourceProjection'], $schema['$defs']['sourceProjectionEntry'])) {
    throw new RuntimeException('Schema source_projection contract missing.');
}

$registry = optionContractJson($root . '/config/product/competitor-parity-surfaces.json');
$surfaceRows = optionContractArray($registry['surfaces'] ?? null, 'Surface registry missing.');
if (count($surfaceRows) !== 56) {
    throw new RuntimeException('Surface registry must contain 56 rows.');
}
$byId = [];
$byKey = [];
foreach ($surfaceRows as $row) {
    if (!is_array($row)) {
        throw new RuntimeException('Invalid surface registry row.');
    }
    $id = optionContractInt($row['id'] ?? null, 'Surface id missing.');
    $key = optionContractString($row['key'] ?? null, 'Surface key missing.');
    if (isset($byId[$id]) || isset($byKey[$key])) {
        throw new RuntimeException("Duplicate surface {$id}/{$key}.");
    }
    $byId[$id] = $key;
    $byKey[$key] = $id;
}

$bankProgress = optionContractJson($root . '/config/product/options-bank-progress.json');
$bankByKey = [];
foreach (optionContractArray($bankProgress['surface_status'] ?? null, 'Options Bank progress rows missing.') as $row) {
    if (!is_array($row)) {
        throw new RuntimeException('Invalid Options Bank progress row.');
    }
    $key = optionContractString($row['key'] ?? null, 'Options Bank progress key missing.');
    if (!isset($byKey[$key])) {
        throw new RuntimeException("Options Bank progress references unknown surface {$key}.");
    }
    $bankByKey[$key] = [
        'status' => optionContractString($row['status'] ?? null, "Options Bank progress status missing for {$key}."),
        'records' => optionContractInt($row['records'] ?? null, "Options Bank progress records invalid for {$key}."),
    ];
}

$progress = optionContractJson($root . '/config/product/atomic-option-contract-progress.json');
$progressRows = optionContractArray($progress['surface_status'] ?? null, 'Atomic progress rows missing.');
if (count($progressRows) !== 56) {
    throw new RuntimeException('Atomic progress must contain 56 rows.');
}
$progressByKey = [];
$derivedTruth = [
    'atomic_inventory_surfaces' => 0,
    'option_contract_complete_surfaces' => 0,
    'ux_contract_complete_surfaces' => 0,
    'runtime_certified_for_full_parity_contract' => 0,
    'product_parity_certified_surfaces' => 0,
];
foreach ($progressRows as $row) {
    if (!is_array($row)) {
        throw new RuntimeException('Invalid Atomic progress row.');
    }
    $id = optionContractInt($row['id'] ?? null, 'Atomic progress id missing.');
    $key = optionContractString($row['key'] ?? null, 'Atomic progress key missing.');
    $status = optionContractString($row['status'] ?? null, 'Atomic progress status missing.');
    if (($byId[$id] ?? null) !== $key || !isset($lifecycle[$status]) || isset($progressByKey[$key])) {
        throw new RuntimeException("Invalid Atomic progress identity/lifecycle for {$id}/{$key}.");
    }
    $progressByKey[$key] = $status;
    $rank = $lifecycle[$status];
    $derivedTruth['atomic_inventory_surfaces'] += (int) ($rank >= 30);
    $derivedTruth['option_contract_complete_surfaces'] += (int) ($rank >= 40);
    $derivedTruth['ux_contract_complete_surfaces'] += (int) ($rank >= 50);
    $derivedTruth['runtime_certified_for_full_parity_contract'] += (int) ($rank >= 80);
    $derivedTruth['product_parity_certified_surfaces'] += (int) ($rank >= 90);
}
$truth = optionContractArray($progress['truth'] ?? null, 'Atomic progress truth missing.');
if (optionContractInt($truth['capability_matrix_surfaces'] ?? null, 'Capability matrix truth missing.') !== 56) {
    throw new RuntimeException('Capability matrix count must be 56.');
}
foreach ($derivedTruth as $field => $expected) {
    if (optionContractInt($truth[$field] ?? null, "Atomic truth {$field} missing.") !== $expected) {
        throw new RuntimeException("Atomic truth {$field} does not match derived lifecycle state.");
    }
}

$contractDir = $root . '/config/product/option-contracts';
$files = is_dir($contractDir) ? glob($contractDir . '/*.json') : [];
if ($files === false) {
    throw new RuntimeException('Unable to enumerate Atomic Option instances.');
}
sort($files, SORT_STRING);
$seenSurfaces = [];
$totalOptions = 0;
$totalProjections = 0;
foreach ($files as $file) {
    $instance = optionContractJson($file);
    $id = optionContractInt($instance['surface_id'] ?? null, "{$file} surface_id missing.");
    $key = optionContractString($instance['surface_key'] ?? null, "{$file} surface_key missing.");
    $status = optionContractString($instance['status'] ?? null, "{$file} status missing.");
    if (($instance['schema_version'] ?? null) !== 1
        || ($byId[$id] ?? null) !== $key
        || pathinfo($file, PATHINFO_FILENAME) !== $key
        || isset($seenSurfaces[$key])
        || !isset($lifecycle[$status])
    ) {
        throw new RuntimeException("Invalid Atomic Option instance identity/status: {$file}");
    }
    $seenSurfaces[$key] = true;
    if ($lifecycle[$progressByKey[$key]] > $lifecycle[$status]) {
        throw new RuntimeException("Progress outruns {$key} instance status.");
    }

    $groups = optionContractArray($instance['feature_groups'] ?? null, "{$file} feature groups missing.");
    if ($groups === []) {
        throw new RuntimeException("{$file} feature groups must not be empty.");
    }
    $atomicIds = [];
    $derivedCoverage = [
        'parity' => 0,
        'exceeds' => 0,
        'deferred' => 0,
        'rejected_unsafe' => 0,
        'missing' => 0,
    ];
    foreach ($groups as $group) {
        if (!is_array($group)) {
            throw new RuntimeException("{$file} contains an invalid feature group.");
        }
        optionContractIdentifier(optionContractString($group['id'] ?? null, "{$file} feature group id missing."), "{$file} feature group id invalid.");
        optionContractString($group['label'] ?? null, "{$file} feature group label missing.");
        foreach (optionContractArray($group['options'] ?? null, "{$file} feature group options missing.") as $option) {
            if (!is_array($option)) {
                throw new RuntimeException("{$file} contains an invalid Atomic Option.");
            }
            $atomicId = optionContractIdentifier(optionContractString($option['id'] ?? null, "{$file} option id missing."), "{$file} option id invalid.");
            if (isset($atomicIds[$atomicId])) {
                throw new RuntimeException("{$file} duplicates Atomic Option {$atomicId}.");
            }
            $atomicIds[$atomicId] = true;
            ++$totalOptions;
            optionContractString($option['label'] ?? null, "{$file} {$atomicId} label missing.");
            $parity = optionContractEnum(
                $option['parity_status'] ?? null,
                ['MISSING', 'PLANNED_BASELINE', 'PARITY', 'EXCEEDS', 'NOT_APPLICABLE', 'DEFERRED_WITH_REASON', 'REJECTED_UNSAFE'],
                "{$file} {$atomicId} parity status invalid.",
            );
            optionContractString($option['value_type'] ?? null, "{$file} {$atomicId} value_type missing.");
            $validation = optionContractArray($option['validation'] ?? null, "{$file} {$atomicId} validation missing.");
            if (($validation['server_authoritative'] ?? null) !== true) {
                throw new RuntimeException("{$file} {$atomicId} must be server-authoritative.");
            }
            $storage = optionContractArray($option['storage'] ?? null, "{$file} {$atomicId} storage missing.");
            optionContractString($storage['owner'] ?? null, "{$file} {$atomicId} storage owner missing.");
            optionContractString($storage['mode'] ?? null, "{$file} {$atomicId} storage mode missing.");
            optionContractArray($option['runtime'] ?? null, "{$file} {$atomicId} runtime missing.");
            optionContractArray($option['security'] ?? null, "{$file} {$atomicId} security missing.");
            optionContractArray($option['portability'] ?? null, "{$file} {$atomicId} portability missing.");
            optionContractArray($option['testing'] ?? null, "{$file} {$atomicId} testing missing.");
            optionContractArray($option['competitor_evidence'] ?? null, "{$file} {$atomicId} competitor evidence missing.");

            $coverageMap = [
                'PARITY' => 'parity',
                'EXCEEDS' => 'exceeds',
                'DEFERRED_WITH_REASON' => 'deferred',
                'REJECTED_UNSAFE' => 'rejected_unsafe',
                'MISSING' => 'missing',
            ];
            if (isset($coverageMap[$parity])) {
                ++$derivedCoverage[$coverageMap[$parity]];
            }
        }
    }

    $coverage = optionContractArray($instance['coverage_summary'] ?? null, "{$file} coverage missing.");
    if (optionContractInt($coverage['atomic_options'] ?? null, "{$file} atomic_options missing.") !== count($atomicIds)) {
        throw new RuntimeException("{$file} Atomic Option count mismatch.");
    }
    foreach ($derivedCoverage as $field => $expected) {
        if (optionContractInt($coverage[$field] ?? null, "{$file} {$field} coverage missing.") !== $expected) {
            throw new RuntimeException("{$file} coverage {$field} mismatch.");
        }
    }
    $unclassified = optionContractInt($coverage['unclassified'] ?? null, "{$file} unclassified coverage missing.");
    if ($lifecycle[$status] >= 40 && ($derivedCoverage['missing'] !== 0 || $unclassified !== 0)) {
        throw new RuntimeException("{$file} cannot be OPTION_CONTRACT_COMPLETE with missing/unclassified state.");
    }

    $projection = $instance['source_projection'] ?? null;
    if ($projection !== null) {
        $projection = optionContractArray($projection, "{$file} source_projection invalid.");
        if (($projection['source_type'] ?? null) !== 'options_bank' || ($projection['source_surface_key'] ?? null) !== $key) {
            throw new RuntimeException("{$file} source projection identity mismatch.");
        }
        optionContractString($projection['source_review_version'] ?? null, "{$file} source review version missing.");
        $sourceCount = optionContractInt($projection['source_record_count'] ?? null, "{$file} source record count missing.");
        $entries = optionContractArray($projection['entries'] ?? null, "{$file} source projection entries missing.");
        if (count($entries) !== $sourceCount) {
            throw new RuntimeException("{$file} source projection count mismatch.");
        }
        if (($bankByKey[$key]['status'] ?? null) === 'BANK_REVIEWED' && ($bankByKey[$key]['records'] ?? null) !== $sourceCount) {
            throw new RuntimeException("{$file} source projection count differs from BANK_REVIEWED progress.");
        }

        $seenSourceIds = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                throw new RuntimeException("{$file} contains an invalid source projection entry.");
            }
            $sourceId = optionContractIdentifier(optionContractString($entry['source_id'] ?? null, "{$file} source_id missing."), "{$file} source_id invalid.");
            if (isset($seenSourceIds[$sourceId])) {
                throw new RuntimeException("{$file} duplicates source projection {$sourceId}.");
            }
            $seenSourceIds[$sourceId] = true;
            $disposition = optionContractString($entry['disposition'] ?? null, "{$file} {$sourceId} disposition missing.");
            $owner = $entry['owner_surface'] ?? null;
            if ($disposition === 'OUT_OF_SURFACE_REFERENCE' && (!is_string($owner) || trim($owner) === '')) {
                throw new RuntimeException("{$file} {$sourceId} out-of-surface reference requires owner_surface.");
            }
            optionContractString($entry['reason'] ?? null, "{$file} {$sourceId} reason missing.");
            optionContractArray($entry['evidence_refs'] ?? null, "{$file} {$sourceId} evidence_refs missing.");
            $mappedIds = optionContractArray($entry['atomic_ids'] ?? null, "{$file} {$sourceId} atomic_ids missing.");
            foreach ($mappedIds as $mappedId) {
                if (!is_string($mappedId) || !isset($atomicIds[$mappedId])) {
                    throw new RuntimeException("{$file} {$sourceId} references missing Atomic Option.");
                }
            }
        }
        ++$totalProjections;
    }

    if ($lifecycle[$status] >= 40 && ($bankByKey[$key]['status'] ?? null) === 'BANK_REVIEWED' && $projection === null) {
        throw new RuntimeException("{$file} BANK_REVIEWED source requires source_projection before OPTION_CONTRACT_COMPLETE.");
    }
}

fwrite(
    STDOUT,
    sprintf(
        "Atomic Option contracts: PASS (%d instance(s), %d option(s), %d projection(s); %d option-contract-complete surface(s)).\n",
        count($files),
        $totalOptions,
        $totalProjections,
        $derivedTruth['option_contract_complete_surfaces'],
    ),
);
