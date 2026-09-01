<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);

/** @return array<string,mixed> */
function relations_contract_json(string $path): array
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

/** @return list<mixed> */
function relations_contract_list(mixed $value, string $message): array
{
    if (!is_array($value) || !array_is_list($value)) {
        throw new RuntimeException($message);
    }

    return $value;
}

function relations_contract_string(mixed $value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }

    return $value;
}

$contractPath = $root . '/config/product/option-contracts/relations.json';
$contract = relations_contract_json($contractPath);

if (($contract['schema_version'] ?? null) !== 1
    || ($contract['surface_id'] ?? null) !== 4
    || ($contract['surface_key'] ?? null) !== 'relations'
    || ($contract['status'] ?? null) !== 'OPTION_CONTRACT_COMPLETE'
) {
    throw new RuntimeException('Relations Atomic Option contract identity/status mismatch.');
}

$coverage = $contract['coverage_summary'] ?? null;
if (!is_array($coverage)
    || ($coverage['atomic_options'] ?? null) !== 18
    || ($coverage['parity'] ?? null) !== 18
    || ($coverage['exceeds'] ?? null) !== 0
    || ($coverage['deferred'] ?? null) !== 0
    || ($coverage['rejected_unsafe'] ?? null) !== 0
    || ($coverage['missing'] ?? null) !== 0
    || ($coverage['unclassified'] ?? null) !== 0
) {
    throw new RuntimeException('Relations coverage summary must certify exactly 18 parity Atomic Options with zero missing/unclassified state.');
}

$projection = $contract['source_projection'] ?? null;
if (!is_array($projection)
    || ($projection['source_type'] ?? null) !== 'options_bank'
    || ($projection['source_surface_key'] ?? null) !== 'relations'
    || ($projection['source_record_count'] ?? null) !== 144
) {
    throw new RuntimeException('Relations source projection identity/count mismatch.');
}

$expectedProjectionFiles = [
    'config/product/option-contract-projections/relations/core.json',
    'config/product/option-contract-projections/relations/lifecycle-bulk.json',
    'config/product/option-contract-projections/relations/editor-permissions.json',
    'config/product/option-contract-projections/relations/query-api.json',
    'config/product/option-contract-projections/relations/portability-integrity.json',
    'config/product/option-contract-projections/relations/native-audit-v1.json',
    'config/product/option-contract-projections/relations/market-audit-v1.json',
];
$projectionFiles = relations_contract_list($projection['entry_files'] ?? null, 'Relations projection entry_files missing.');
$projectionFiles = array_map(
    static fn (mixed $value): string => relations_contract_string($value, 'Invalid Relations projection file path.'),
    $projectionFiles,
);
$sortedProjectionFiles = $projectionFiles;
$sortedExpectedProjectionFiles = $expectedProjectionFiles;
sort($sortedProjectionFiles, SORT_STRING);
sort($sortedExpectedProjectionFiles, SORT_STRING);
if ($sortedProjectionFiles !== $sortedExpectedProjectionFiles) {
    throw new RuntimeException('Relations contract must reference exactly the seven canonical projection shards.');
}

$expectedAtomicIds = [
    'relations.definition.identity',
    'relations.definition.cardinality',
    'relations.definition.directionality',
    'relations.definition.from_endpoint',
    'relations.definition.to_endpoint',
    'relations.definition.connection_bounds',
    'relations.definition.edge_uniqueness',
    'relations.definition.edge_ordering',
    'relations.definition.storage_mode',
    'relations.definition.storage_config',
    'relations.definition.pivot_enabled',
    'relations.definition.pivot_policy',
    'relations.definition.deletion_policy',
    'relations.definition.editor_policy',
    'relations.definition.permissions_policy',
    'relations.definition.rest_policy',
    'relations.definition.multisite_scope',
    'relations.definition.portability',
];

$atomicIds = [];
$storageModeAllowedValues = null;
foreach (relations_contract_list($contract['feature_groups'] ?? null, 'Relations feature_groups missing.') as $group) {
    if (!is_array($group)) {
        throw new RuntimeException('Invalid Relations feature group.');
    }

    foreach (relations_contract_list($group['options'] ?? null, 'Relations feature group options missing.') as $option) {
        if (!is_array($option)) {
            throw new RuntimeException('Invalid Relations Atomic Option.');
        }

        $id = relations_contract_string($option['id'] ?? null, 'Relations Atomic Option id missing.');
        if (isset($atomicIds[$id])) {
            throw new RuntimeException("Duplicate Relations Atomic Option: {$id}");
        }
        $atomicIds[$id] = true;

        $validation = $option['validation'] ?? null;
        $storage = $option['storage'] ?? null;
        if (!is_array($validation) || ($validation['server_authoritative'] ?? null) !== true) {
            throw new RuntimeException("Relations Atomic Option {$id} must be server-authoritative.");
        }
        if (!is_array($storage) || ($storage['owner'] ?? null) !== 'relations') {
            throw new RuntimeException("Relations Atomic Option {$id} must remain Relations-owned.");
        }

        if ($id === 'relations.definition.storage_mode') {
            $storageModeAllowedValues = relations_contract_list($option['allowed_values'] ?? null, 'Relations storage-mode allowed values missing.');
        }
    }
}

$actualAtomicIds = array_keys($atomicIds);
sort($actualAtomicIds, SORT_STRING);
$sortedExpectedAtomicIds = $expectedAtomicIds;
sort($sortedExpectedAtomicIds, SORT_STRING);
if ($actualAtomicIds !== $sortedExpectedAtomicIds) {
    throw new RuntimeException('Relations Atomic Option identity set does not match the canonical 18-option V1 contract.');
}

$expectedStorageModes = [
    'shared_relation_table',
    'dedicated_relation_table',
    'native_taxonomy_adapter',
    'native_post_parent_adapter',
    'provider',
];
if ($storageModeAllowedValues === null) {
    throw new RuntimeException('Relations storage-mode contract not found.');
}
$storageModeAllowedValues = array_map(
    static fn (mixed $value): string => relations_contract_string($value, 'Invalid Relations storage mode.'),
    $storageModeAllowedValues,
);
sort($storageModeAllowedValues, SORT_STRING);
sort($expectedStorageModes, SORT_STRING);
if ($storageModeAllowedValues !== $expectedStorageModes) {
    throw new RuntimeException('Relations storage modes must remain bounded to the canonical adapter set.');
}

$serializedContract = strtolower((string) json_encode($contract, JSON_UNESCAPED_SLASHES));
foreach (['php_callback', 'javascript_callback', 'raw_sql', 'direct_core_table'] as $prohibitedToken) {
    if (str_contains($serializedContract, $prohibitedToken)) {
        throw new RuntimeException("Relations contract contains prohibited executable/storage token: {$prohibitedToken}");
    }
}

$bankFiles = [
    'config/product/options-bank/relations.json',
    'config/product/options-bank/relations--lifecycle-bulk.json',
    'config/product/options-bank/relations--editor-permissions.json',
    'config/product/options-bank/relations--query-api.json',
    'config/product/options-bank/relations--portability-integrity.json',
    'config/product/options-bank/relations--native-audit-v1.json',
    'config/product/options-bank/relations--market-audit-v1.json',
];

$bankIds = [];
foreach ($bankFiles as $bankFile) {
    $bank = relations_contract_json($root . '/' . $bankFile);
    foreach (relations_contract_list($bank['records'] ?? null, "Bank records missing: {$bankFile}") as $record) {
        if (!is_array($record)) {
            throw new RuntimeException("Invalid Bank record: {$bankFile}");
        }
        $id = relations_contract_string($record['id'] ?? null, "Bank record id missing: {$bankFile}");
        if (isset($bankIds[$id])) {
            throw new RuntimeException("Duplicate Relations Bank source id: {$id}");
        }
        $bankIds[$id] = $bankFile;
    }
}
if (count($bankIds) !== 144) {
    throw new RuntimeException('Relations Bank must contain exactly 144 unique source IDs.');
}

$projectionIds = [];
$projectionEntries = [];
foreach ($projectionFiles as $projectionFile) {
    $shard = relations_contract_json($root . '/' . $projectionFile);
    foreach (relations_contract_list($shard['entries'] ?? null, "Projection entries missing: {$projectionFile}") as $entry) {
        if (!is_array($entry)) {
            throw new RuntimeException("Invalid projection entry: {$projectionFile}");
        }
        $id = relations_contract_string($entry['source_id'] ?? null, "Projection source_id missing: {$projectionFile}");
        if (isset($projectionIds[$id])) {
            throw new RuntimeException("Duplicate Relations projection source id: {$id}");
        }
        $projectionIds[$id] = $projectionFile;
        $projectionEntries[$id] = $entry;
    }
}
if (count($projectionIds) !== 144) {
    throw new RuntimeException('Relations source projection must contain exactly 144 unique source IDs.');
}

$sortedBankIds = array_keys($bankIds);
$sortedProjectionIds = array_keys($projectionIds);
sort($sortedBankIds, SORT_STRING);
sort($sortedProjectionIds, SORT_STRING);
if ($sortedBankIds !== $sortedProjectionIds) {
    $missing = array_values(array_diff($sortedBankIds, $sortedProjectionIds));
    $extra = array_values(array_diff($sortedProjectionIds, $sortedBankIds));
    throw new RuntimeException(
        'Relations Bank/projection source-ID sets differ. Missing=' . implode(',', $missing) . '; Extra=' . implode(',', $extra),
    );
}

$requiredOwners = [
    'relations.pivot.field_schema' => 'fields',
    'relations.admin_columns' => 'columns',
    'relations.frontend.listings' => 'listings',
    'relations.frontend.read' => 'listings',
    'relations.frontend.edit' => 'forms-workflows',
    'relations.frontend.form_connect' => 'forms-workflows',
    'relations.frontend.form_disconnect' => 'forms-workflows',
    'relations.import_export.edges' => 'import-export',
    'relations.import_export.pivot' => 'import-export',
    'relations.import_export.id_remap' => 'import-export',
    'relations.marketaudit.admin_filter' => 'query',
];
foreach ($requiredOwners as $sourceId => $owner) {
    $entry = $projectionEntries[$sourceId] ?? null;
    if (!is_array($entry)
        || ($entry['source_kind'] ?? null) !== 'out_of_surface'
        || ($entry['disposition'] ?? null) !== 'OUT_OF_SURFACE_REFERENCE'
        || ($entry['owner_surface'] ?? null) !== $owner
        || ($entry['atomic_ids'] ?? null) !== []
    ) {
        throw new RuntimeException("Relations peer-owner projection mismatch for {$sourceId}.");
    }
}

foreach ($projectionEntries as $sourceId => $entry) {
    if (str_starts_with($sourceId, 'relations.query.') && $sourceId !== 'relations.query.cache') {
        if (($entry['source_kind'] ?? null) === 'authored_option') {
            throw new RuntimeException("Query-owned source must not become a Relations-authored option: {$sourceId}");
        }
    }

    if (($entry['source_kind'] ?? null) === 'compatibility_provider' && ($entry['atomic_ids'] ?? null) !== []) {
        throw new RuntimeException("Provider mapping must not synthesize a local Relations Atomic Option: {$sourceId}");
    }
}

fwrite(STDOUT, "Relations Atomic Option contract: PASS (144/144 source records -> 18 canonical Atomic Options).\n");
