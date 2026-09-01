<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);

/** @return array<string,mixed> */
function oc_json(string $path): array
{
    if (!is_file($path)) {
        throw new RuntimeException("Missing JSON: {$path}");
    }
    try {
        $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        throw new RuntimeException("Invalid JSON {$path}: {$e->getMessage()}", 0, $e);
    }
    if (!is_array($data)) {
        throw new RuntimeException("JSON root must be object: {$path}");
    }
    return $data;
}

/** @param mixed $v */
function oc_string($v, string $message): string
{
    if (!is_string($v) || trim($v) === '') {
        throw new RuntimeException($message);
    }
    return $v;
}

/** @param mixed $v */
function oc_int($v, string $message): int
{
    if (!is_int($v)) {
        throw new RuntimeException($message);
    }
    return $v;
}

/** @param mixed $v */
function oc_array($v, string $message): array
{
    if (!is_array($v)) {
        throw new RuntimeException($message);
    }
    return $v;
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
$projectionDispositions = [
    'AUTHORED_ATOMIC', 'USER_PREFERENCE_ATOMIC', 'INTEGRATION_ATOMIC',
    'EFFECTIVE_OR_DIAGNOSTIC', 'RUNTIME_IMPLEMENTATION_EVIDENCE',
    'OUT_OF_SURFACE_REFERENCE', 'COMPATIBILITY_PROVIDER_MAPPING',
    'DEFERRED', 'REJECTED_UNSAFE', 'WPE_EXCEED',
];

$schema = oc_json($root . '/config/product/option-contract.schema.json');
if (($schema['$id'] ?? null) !== 'https://wpessential.local/schema/product-option-contract.json') {
    throw new RuntimeException('Unexpected Atomic Option schema id.');
}
$schemaStatuses = oc_array($schema['properties']['status']['enum'] ?? null, 'Schema status enum missing.');
foreach (array_keys($lifecycle) as $status) {
    if (!in_array($status, $schemaStatuses, true)) {
        throw new RuntimeException("Schema lifecycle missing {$status}.");
    }
}
if (!isset($schema['properties']['source_projection'], $schema['$defs']['sourceProjection'], $schema['$defs']['sourceProjectionEntry'])) {
    throw new RuntimeException('Schema source_projection contract missing.');
}

$registry = oc_json($root . '/config/product/competitor-parity-surfaces.json');
$surfaces = oc_array($registry['surfaces'] ?? null, 'Surface registry missing.');
if (count($surfaces) !== 56) {
    throw new RuntimeException('Surface registry must contain 56 rows.');
}
$byId = [];
$byKey = [];
foreach ($surfaces as $row) {
    if (!is_array($row)) {
        throw new RuntimeException('Invalid surface row.');
    }
    $id = oc_int($row['id'] ?? null, 'Surface id missing.');
    $key = oc_string($row['key'] ?? null, 'Surface key missing.');
    if (isset($byId[$id]) || isset($byKey[$key])) {
        throw new RuntimeException("Duplicate surface {$id}/{$key}.");
    }
    $byId[$id] = $key;
    $byKey[$key] = $id;
}

$progress = oc_json($root . '/config/product/atomic-option-contract-progress.json');
$rows = oc_array($progress['surface_status'] ?? null, 'Atomic progress rows missing.');
if (count($rows) !== 56) {
    throw new RuntimeException('Atomic progress must contain 56 rows.');
}
$progressByKey = [];
$counts = [
    'atomic_inventory_surfaces' => 0,
    'option_contract_complete_surfaces' => 0,
    'ux_contract_complete_surfaces' => 0,
    'runtime_certified_for_full_parity_contract' => 0,
    'product_parity_certified_surfaces' => 0,
];
foreach ($rows as $row) {
    if (!is_array($row)) {
        throw new RuntimeException('Invalid Atomic progress row.');
    }
    $id = oc_int($row['id'] ?? null, 'Progress id missing.');
    $key = oc_string($row['key'] ?? null, 'Progress key missing.');
    $status = oc_string($row['status'] ?? null, 'Progress status missing.');
    if (($byId[$id] ?? null) !== $key || ($byKey[$key] ?? null) !== $id || !isset($lifecycle[$status]) || isset($progressByKey[$key])) {
        throw new RuntimeException("Invalid Atomic progress identity/lifecycle for {$id}/{$key}.");
    }
    $progressByKey[$key] = $status;
    $rank = $lifecycle[$status];
    $counts['atomic_inventory_surfaces'] += (int) ($rank >= 30);
    $counts['option_contract_complete_surfaces'] += (int) ($rank >= 40);
    $counts['ux_contract_complete_surfaces'] += (int) ($rank >= 50);
    $counts['runtime_certified_for_full_parity_contract'] += (int) ($rank >= 80);
    $counts['product_parity_certified_surfaces'] += (int) ($rank >= 90);
}
$truth = oc_array($progress['truth'] ?? null, 'Atomic progress truth missing.');
foreach ($counts as $field => $expected) {
    if (oc_int($truth[$field] ?? null, "truth.{$field} missing") !== $expected) {
        throw new RuntimeException("truth.{$field} does not match derived state.");
    }
}
if (oc_int($truth['capability_matrix_surfaces'] ?? null, 'capability matrix count missing') !== 56) {
    throw new RuntimeException('Capability matrix count must be 56.');
}

$dir = $root . '/config/product/option-contracts';
$files = is_dir($dir) ? glob($dir . '/*.json') : [];
if ($files === false) {
    throw new RuntimeException('Unable to enumerate Atomic Option instances.');
}
sort($files, SORT_STRING);
$seenSurfaces = [];
foreach ($files as $file) {
    $instance = oc_json($file);
    $id = oc_int($instance['surface_id'] ?? null, "{$file} surface_id missing");
    $key = oc_string($instance['surface_key'] ?? null, "{$file} surface_key missing");
    $status = oc_string($instance['status'] ?? null, "{$file} status missing");
    if (($byId[$id] ?? null) !== $key || pathinfo($file, PATHINFO_FILENAME) !== $key || isset($seenSurfaces[$key]) || !isset($lifecycle[$status])) {
        throw new RuntimeException("Invalid Atomic Option instance identity/status: {$file}");
    }
    $seenSurfaces[$key] = true;
    if ($lifecycle[$progressByKey[$key]] > $lifecycle[$status]) {
        throw new RuntimeException("Progress outruns {$key} instance status.");
    }
    $snapshot = oc_array($instance['benchmark_snapshot'] ?? null, "{$file} benchmark snapshot missing");
    oc_string($snapshot['date'] ?? null, "{$file} benchmark date missing");
    if (oc_array($snapshot['products'] ?? null, "{$file} benchmark products missing") === []) {
        throw new RuntimeException("{$file} benchmark products empty.");
    }

    $groups = oc_array($instance['feature_groups'] ?? null, "{$file} feature groups missing");
    if ($groups === []) {
        throw new RuntimeException("{$file} feature groups empty.");
    }
    $groupIds = [];
    $atomicIds = [];
    $derived = ['parity' => 0, 'exceeds' => 0, 'deferred' => 0, 'rejected_unsafe' => 0, 'missing' => 0];
    foreach ($groups as $group) {
        if (!is_array($group)) {
            throw new RuntimeException("{$file} invalid feature group.");
        }
        $groupId = oc_string($group['id'] ?? null, "{$file} feature group id missing");
        if (isset($groupIds[$groupId])) {
            throw new RuntimeException("{$file} duplicate feature group {$groupId}.");
        }
        $groupIds[$groupId] = true;
        oc_string($group['label'] ?? null, "{$file} feature group label missing");
        foreach (oc_array($group['options'] ?? null, "{$file} group options missing") as $option) {
            if (!is_array($option)) {
                throw new RuntimeException("{$file} invalid Atomic Option.");
            }
            $atomicId = oc_string($option['id'] ?? null, "{$file} option id missing");
            if (isset($atomicIds[$atomicId])) {
                throw new RuntimeException("{$file} duplicate Atomic Option {$atomicId}.");
            }
            $atomicIds[$atomicId] = true;
            foreach (['label', 'kind', 'parity_status', 'requiredness', 'value_type'] as $required) {
                oc_string($option[$required] ?? null, "{$file} {$atomicId} missing {$required}");
            }
            if (($option['validation']['server_authoritative'] ?? null) !== true) {
                throw new RuntimeException("{$file} {$atomicId} must be server-authoritative.");
            }
            $evidence = oc_array($option['testing']['required_evidence'] ?? null, "{$file} {$atomicId} evidence missing");
            if ($evidence === []) {
                throw new RuntimeException("{$file} {$atomicId} evidence empty.");
            }
            $parity = $option['parity_status'];
            $map = ['PARITY' => 'parity', 'EXCEEDS' => 'exceeds', 'DEFERRED_WITH_REASON' => 'deferred', 'REJECTED_UNSAFE' => 'rejected_unsafe', 'MISSING' => 'missing'];
            if (isset($map[$parity])) {
                ++$derived[$map[$parity]];
            }
        }
    }

    $coverage = oc_array($instance['coverage_summary'] ?? null, "{$file} coverage missing");
    if (oc_int($coverage['atomic_options'] ?? null, "{$file} atomic_options missing") !== count($atomicIds)) {
        throw new RuntimeException("{$file} atomic option count mismatch.");
    }
    foreach ($derived as $field => $expected) {
        if (oc_int($coverage[$field] ?? null, "{$file} {$field} missing") !== $expected) {
            throw new RuntimeException("{$file} coverage {$field} mismatch.");
        }
    }
    $unclassified = oc_int($coverage['unclassified'] ?? null, "{$file} unclassified missing");
    if ($lifecycle[$status] >= 40 && ($derived['missing'] !== 0 || $unclassified !== 0)) {
        throw new RuntimeException("{$file} cannot be OPTION_CONTRACT_COMPLETE with missing/unclassified state.");
    }

    if (isset($instance['source_projection'])) {
        $projection = oc_array($instance['source_projection'], "{$file} source_projection invalid");
        if (($projection['source_type'] ?? null) !== 'options_bank' || ($projection['source_surface_key'] ?? null) !== $key) {
            throw new RuntimeException("{$file} source projection identity mismatch.");
        }
        oc_string($projection['source_review_version'] ?? null, "{$file} source review version missing");
        $sourceCount = oc_int($projection['source_record_count'] ?? null, "{$file} source count missing");
        $entries = oc_array($projection['entries'] ?? null, "{$file} source entries missing");
        if (count($entries) !== $sourceCount) {
            throw new RuntimeException("{$file} source projection count mismatch.");
        }
        $sourceIds = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                throw new RuntimeException("{$file} invalid projection entry.");
            }
            $sourceId = oc_string($entry['source_id'] ?? null, "{$file} projection source_id missing");
            if (isset($sourceIds[$sourceId])) {
                throw new RuntimeException("{$file} duplicate projection source {$sourceId}.");
            }
            $sourceIds[$sourceId] = true;
            $disposition = oc_string($entry['disposition'] ?? null, "{$file} projection disposition missing");
            if (!in_array($disposition, $projectionDispositions, true)) {
                throw new RuntimeException("{$file} invalid projection disposition {$disposition}.");
            }
            oc_string($entry['source_kind'] ?? null, "{$file} projection source_kind missing");
            oc_string($entry['reason'] ?? null, "{$file} projection reason missing");
            oc_array($entry['evidence_refs'] ?? null, "{$file} projection evidence_refs missing");
            $mapped = oc_array($entry['atomic_ids'] ?? null, "{$file} projection atomic_ids missing");
            if (count($mapped) !== count(array_unique($mapped))) {
                throw new RuntimeException("{$file} duplicate projection atomic_ids for {$sourceId}.");
            }
            foreach ($mapped as $mappedId) {
                $mappedId = oc_string($mappedId, "{$file} invalid mapped Atomic Option id");
                if (!isset($atomicIds[$mappedId])) {
                    throw new RuntimeException("{$file} projection references missing {$mappedId}.");
                }
            }
        }
    }
}

fwrite(STDOUT, sprintf(
    "Atomic Option contracts: PASS (%d instance(s), %d inventory, %d option-contract, %d UX-complete).\n",
    count($files),
    $counts['atomic_inventory_surfaces'],
    $counts['option_contract_complete_surfaces'],
    $counts['ux_contract_complete_surfaces'],
));
