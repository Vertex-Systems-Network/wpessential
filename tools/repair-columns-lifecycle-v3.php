<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$contractPath = $root . '/config/product/option-contracts/columns.json';
$smokePath = $root . '/tests/Smoke/option-contract-columns-contract.php';
$progressPath = $root . '/config/product/atomic-option-contract-progress.json';

$contractRaw = file_get_contents($contractPath);
if ($contractRaw === false) {
    throw new RuntimeException('Unable to read Columns contract.');
}
$contract = json_decode($contractRaw, true, 512, JSON_THROW_ON_ERROR);
if (!is_array($contract)
    || ($contract['surface_id'] ?? null) !== 8
    || ($contract['surface_key'] ?? null) !== 'columns'
    || ($contract['status'] ?? null) !== 'OPTION_CONTRACT_COMPLETE'
) {
    throw new RuntimeException('Unexpected Columns contract identity/lifecycle.');
}

$expectedRefs = [
    'columns.target.wpe_tables' => true,
    'columns.source.field' => true,
    'columns.source.taxonomy' => true,
    'columns.source.relation' => true,
    'columns.source.media' => true,
    'columns.source.status' => true,
    'columns.source.query_aggregate' => true,
    'columns.filter.relation_ops' => true,
    'columns.portability.map_field' => true,
    'columns.portability.map_query' => true,
    'columns.portability.map_relation' => true,
];
$entries = $contract['source_projection']['entries'] ?? null;
if (!is_array($entries) || count($entries) !== 214 || ($contract['source_projection']['source_record_count'] ?? null) !== 214) {
    throw new RuntimeException('Columns 214-record projection invariant drifted.');
}
$seenRefs = [];
foreach ($entries as $entry) {
    if (!is_array($entry) || ($entry['disposition'] ?? null) !== 'OUT_OF_SURFACE_REFERENCE') {
        continue;
    }
    $sourceId = $entry['source_id'] ?? null;
    if (!is_string($sourceId) || !isset($expectedRefs[$sourceId])) {
        throw new RuntimeException('Unexpected out-of-surface reference.');
    }
    if (($entry['atomic_ids'] ?? null) !== []) {
        throw new RuntimeException(sprintf('%s regained a local Atomic mapping.', $sourceId));
    }
    $owner = $entry['owner_surface'] ?? null;
    if (!is_string($owner) || $owner === '' || $owner === 'columns') {
        throw new RuntimeException(sprintf('%s has invalid canonical owner.', $sourceId));
    }
    $seenRefs[$sourceId] = true;
}
if (array_keys($seenRefs) !== array_keys($expectedRefs)) {
    ksort($seenRefs);
    $expectedSorted = $expectedRefs;
    ksort($expectedSorted);
    if (array_keys($seenRefs) !== array_keys($expectedSorted)) {
        throw new RuntimeException('Columns owner-reference set drifted.');
    }
}
$coverage = $contract['coverage_summary'] ?? null;
if (!is_array($coverage)
    || ($coverage['atomic_options'] ?? null) !== 41
    || ($coverage['missing'] ?? null) !== 0
    || ($coverage['unclassified'] ?? null) !== 0
) {
    throw new RuntimeException('Columns coverage invariant drifted.');
}

$contract['status'] = 'UX_CONTRACT_COMPLETE';
file_put_contents(
    $contractPath,
    json_encode($contract, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n",
);

$smoke = file_get_contents($smokePath);
if ($smoke === false) {
    throw new RuntimeException('Unable to read Columns smoke validator.');
}
$old = <<<'PHP'
$contract = columns_contract_json($contractPath);
if (($contract['schema_version'] ?? null) !== 1
    || ($contract['surface_id'] ?? null) !== 8
    || ($contract['surface_key'] ?? null) !== 'columns'
    || ($contract['status'] ?? null) !== 'OPTION_CONTRACT_COMPLETE'
) {
    throw new RuntimeException('Surface 8 Atomic Option contract identity/lifecycle is invalid.');
}
PHP;
$new = <<<'PHP'
$contract = columns_contract_json($contractPath);
$contractStatus = $contract['status'] ?? null;
if (($contract['schema_version'] ?? null) !== 1
    || ($contract['surface_id'] ?? null) !== 8
    || ($contract['surface_key'] ?? null) !== 'columns'
    || !in_array($contractStatus, ['OPTION_CONTRACT_COMPLETE', 'UX_CONTRACT_COMPLETE'], true)
) {
    throw new RuntimeException('Surface 8 Atomic Option contract identity/lifecycle is invalid.');
}
PHP;
if (substr_count($smoke, $old) !== 1) {
    throw new RuntimeException('Expected repaired lifecycle guard was not found exactly once.');
}
file_put_contents($smokePath, str_replace($old, $new, $smoke));

$progressRaw = file_get_contents($progressPath);
if ($progressRaw === false) {
    throw new RuntimeException('Unable to read lifecycle progress.');
}
$progress = json_decode($progressRaw, true, 512, JSON_THROW_ON_ERROR);
$statuses = [];
foreach (($progress['surface_status'] ?? []) as $row) {
    if (is_array($row) && isset($row['key'], $row['status'])) {
        $statuses[(string) $row['key']] = (string) $row['status'];
    }
}
if (($statuses['relations'] ?? null) !== 'OPTION_CONTRACT_COMPLETE'
    || ($statuses['columns'] ?? null) !== 'UX_CONTRACT_COMPLETE'
) {
    throw new RuntimeException('Lifecycle progress rows are inconsistent.');
}
$truth = $progress['truth'] ?? null;
if (!is_array($truth)
    || ($truth['option_contract_complete_surfaces'] ?? null) !== 2
    || ($truth['ux_contract_complete_surfaces'] ?? null) !== 1
    || ($truth['runtime_certified_for_full_parity_contract'] ?? null) !== 0
    || ($truth['product_parity_certified_surfaces'] ?? null) !== 0
) {
    throw new RuntimeException('Lifecycle progress counters are inconsistent.');
}

fwrite(STDOUT, "Columns repaired UX lifecycle transformation: PASS.\n");
