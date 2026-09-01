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
    } catch (JsonException $exception) {
        throw new RuntimeException("Invalid JSON {$path}: {$exception->getMessage()}", 0, $exception);
    }

    if (!is_array($data)) {
        throw new RuntimeException("JSON root must be object: {$path}");
    }

    return $data;
}

/** @param mixed $value */
function oc_string($value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }

    return $value;
}

/** @param mixed $value */
function oc_int($value, string $message): int
{
    if (!is_int($value)) {
        throw new RuntimeException($message);
    }

    return $value;
}

/** @param mixed $value */
function oc_array($value, string $message): array
{
    if (!is_array($value)) {
        throw new RuntimeException($message);
    }

    return $value;
}

/** @param mixed $value */
function oc_bool($value, string $message): bool
{
    if (!is_bool($value)) {
        throw new RuntimeException($message);
    }

    return $value;
}

/** @param list<string> $allowed */
function oc_enum($value, array $allowed, string $message): string
{
    $value = oc_string($value, $message);
    if (!in_array($value, $allowed, true)) {
        throw new RuntimeException("{$message} Got {$value}.");
    }

    return $value;
}

function oc_identifier(string $value, string $message): string
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
$projectionKinds = [
    'authored_option', 'user_preference', 'integration', 'native_runtime',
    'effective_state', 'diagnostic', 'out_of_surface', 'compatibility_provider',
    'deferred', 'rejected_unsafe', 'wpe_exceed',
];
$projectionDispositions = [
    'AUTHORED_ATOMIC', 'USER_PREFERENCE_ATOMIC', 'INTEGRATION_ATOMIC',
    'EFFECTIVE_OR_DIAGNOSTIC', 'RUNTIME_IMPLEMENTATION_EVIDENCE',
    'OUT_OF_SURFACE_REFERENCE', 'COMPATIBILITY_PROVIDER_MAPPING',
    'DEFERRED', 'REJECTED_UNSAFE', 'WPE_EXCEED',
];
$optionKinds = [
    'native_option', 'wpe_option', 'capability', 'workflow', 'integration',
    'ux_capability', 'data_behavior', 'developer_extension',
];
$parityStatuses = [
    'MISSING', 'PLANNED_BASELINE', 'PARITY', 'EXCEEDS', 'NOT_APPLICABLE',
    'DEFERRED_WITH_REASON', 'REJECTED_UNSAFE',
];
$requiredness = ['required_create', 'required_publish', 'optional', 'computed', 'provider_required'];
$defaultModes = ['wordpress_default', 'inherited', 'wpe_default', 'computed', 'none'];
$uiTiers = ['Essential', 'Advanced', 'Expert', 'System'];
$mutationClasses = ['reversible', 'high_impact', 'migration_required', 'immutable', 'destructive', 'read_only'];
$performanceClasses = ['P0', 'P1', 'P2', 'P3', 'P4', 'not_applicable'];
$securityClasses = ['normal', 'privileged', 'expert', 'provider_only', 'internal', 'prohibited'];
$evidenceTypes = [
    'unit', 'integration', 'wordpress_runtime', 'rest', 'browser', 'accessibility',
    'migration', 'performance', 'security', 'compatibility', 'portability',
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

$bankProgress = oc_json($root . '/config/product/options-bank-progress.json');
$bankProgressByKey = [];
foreach (oc_array($bankProgress['surface_status'] ?? null, 'Options Bank progress rows missing.') as $row) {
    if (!is_array($row)) {
        throw new RuntimeException('Invalid Options Bank progress row.');
    }
    $key = oc_string($row['key'] ?? null, 'Options Bank progress key missing.');
    if (!isset($byKey[$key])) {
        throw new RuntimeException("Options Bank progress references unknown surface {$key}.");
    }
    $bankProgressByKey[$key] = [
        'status' => oc_string($row['status'] ?? null, "Options Bank progress status missing for {$key}."),
        'records' => oc_int($row['records'] ?? null, "Options Bank progress records invalid for {$key}."),
    ];
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
$totalOptions = 0;
$totalProjections = 0;
foreach ($files as $file) {
    $instance = oc_json($file);
    if (($instance['schema_version'] ?? null) !== 1) {
        throw new RuntimeException("{$file} has unsupported schema_version.");
    }

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
    $snapshotDate = oc_string($snapshot['date'] ?? null, "{$file} benchmark date missing");
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $snapshotDate) !== 1) {
        throw new RuntimeException("{$file} benchmark date has invalid shape.");
    }
    $products = oc_array($snapshot['products'] ?? null, "{$file} benchmark products missing");
    if ($products === []) {
        throw new RuntimeException("{$file} benchmark products empty.");
    }
    foreach ($products as $product) {
        if (!is_array($product)) {
            throw new RuntimeException("{$file} has invalid benchmark product.");
        }
        oc_string($product['name'] ?? null, "{$file} benchmark product name missing.");
        oc_enum($product['role'] ?? null, ['primary', 'secondary', 'specialist'], "{$file} benchmark role invalid.");
        $officialSources = oc_array($product['official_sources'] ?? null, "{$file} benchmark sources missing.");
        if ($officialSources === []) {
            throw new RuntimeException("{$file} benchmark sources empty.");
        }
        foreach ($officialSources as $source) {
            oc_string($source, "{$file} benchmark source invalid.");
        }
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
        $groupId = oc_identifier(
            oc_string($group['id'] ?? null, "{$file} feature group id missing"),
            "{$file} feature group id invalid",
        );
        if (isset($groupIds[$groupId])) {
            throw new RuntimeException("{$file} duplicate feature group {$groupId}.");
        }
        $groupIds[$groupId] = true;
        oc_string($group['label'] ?? null, "{$file} feature group label missing");

        foreach (oc_array($group['options'] ?? null, "{$file} group options missing") as $option) {
            if (!is_array($option)) {
                throw new RuntimeException("{$file} invalid Atomic Option.");
            }
            $atomicId = oc_identifier(
                oc_string($option['id'] ?? null, "{$file} option id missing"),
                "{$file} Atomic Option id invalid",
            );
            if (isset($atomicIds[$atomicId])) {
                throw new RuntimeException("{$file} duplicate Atomic Option {$atomicId}.");
            }
            $atomicIds[$atomicId] = true;
            ++$totalOptions;

            oc_string($option['label'] ?? null, "{$file} {$atomicId} missing label");
            oc_enum($option['kind'] ?? null, $optionKinds, "{$file} {$atomicId} has invalid kind.");
            $parity = oc_enum($option['parity_status'] ?? null, $parityStatuses, "{$file} {$atomicId} has invalid parity status.");
            oc_enum($option['requiredness'] ?? null, $requiredness, "{$file} {$atomicId} has invalid requiredness.");
            oc_string($option['value_type'] ?? null, "{$file} {$atomicId} missing value_type");

            $defaultBehavior = oc_array($option['default_behavior'] ?? null, "{$file} {$atomicId} default_behavior missing");
            oc_enum($defaultBehavior['mode'] ?? null, $defaultModes, "{$file} {$atomicId} default mode invalid.");

            $ui = oc_array($option['ui'] ?? null, "{$file} {$atomicId} ui missing");
            oc_enum($ui['tier'] ?? null, $uiTiers, "{$file} {$atomicId} UI tier invalid.");
            oc_string($ui['group'] ?? null, "{$file} {$atomicId} UI group missing");
            oc_string($ui['control'] ?? null, "{$file} {$atomicId} UI control missing");

            $validation = oc_array($option['validation'] ?? null, "{$file} {$atomicId} validation missing");
            if (($validation['server_authoritative'] ?? null) !== true) {
                throw new RuntimeException("{$file} {$atomicId} must be server-authoritative.");
            }

            $storage = oc_array($option['storage'] ?? null, "{$file} {$atomicId} storage missing");
            oc_string($storage['owner'] ?? null, "{$file} {$atomicId} storage owner missing");
            oc_string($storage['mode'] ?? null, "{$file} {$atomicId} storage mode missing");

            $runtime = oc_array($option['runtime'] ?? null, "{$file} {$atomicId} runtime missing");
            oc_string($runtime['effect'] ?? null, "{$file} {$atomicId} runtime effect missing");
            oc_enum($runtime['mutation_class'] ?? null, $mutationClasses, "{$file} {$atomicId} mutation class invalid.");
            if (array_key_exists('performance_class', $runtime)) {
                oc_enum($runtime['performance_class'], $performanceClasses, "{$file} {$atomicId} performance class invalid.");
            }

            $security = oc_array($option['security'] ?? null, "{$file} {$atomicId} security missing");
            $securityClass = oc_enum($security['class'] ?? null, $securityClasses, "{$file} {$atomicId} security class invalid.");
            if (!array_key_exists('capability', $security) || (!is_string($security['capability']) && $security['capability'] !== null)) {
                throw new RuntimeException("{$file} {$atomicId} security capability invalid.");
            }

            $portability = oc_array($option['portability'] ?? null, "{$file} {$atomicId} portability missing");
            oc_bool($portability['export'] ?? null, "{$file} {$atomicId} portability.export invalid");
            oc_bool($portability['import'] ?? null, "{$file} {$atomicId} portability.import invalid");

            $testing = oc_array($option['testing'] ?? null, "{$file} {$atomicId} testing missing");
            $evidence = oc_array($testing['required_evidence'] ?? null, "{$file} {$atomicId} evidence missing");
            if ($evidence === []) {
                throw new RuntimeException("{$file} {$atomicId} evidence empty.");
            }
            foreach ($evidence as $evidenceType) {
                oc_enum($evidenceType, $evidenceTypes, "{$file} {$atomicId} evidence type invalid.");
            }

            oc_array($option['competitor_evidence'] ?? null, "{$file} {$atomicId} competitor_evidence missing");

            if ($parity === 'REJECTED_UNSAFE' && $securityClass !== 'prohibited') {
                throw new RuntimeException("{$file} {$atomicId} REJECTED_UNSAFE must use prohibited security class.");
            }
            if ($parity === 'EXCEEDS') {
                $exceed = oc_array($option['wpe_exceed'] ?? null, "{$file} {$atomicId} EXCEEDS requires wpe_exceed");
                oc_string($exceed['reason'] ?? null, "{$file} {$atomicId} wpe_exceed reason missing");
            }

            $map = [
                'PARITY' => 'parity',
                'EXCEEDS' => 'exceeds',
                'DEFERRED_WITH_REASON' => 'deferred',
                'REJECTED_UNSAFE' => 'rejected_unsafe',
                'MISSING' => 'missing',
            ];
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

    $projection = $instance['source_projection'] ?? null;
    if ($projection !== null) {
        $projection = oc_array($projection, "{$file} source_projection invalid");
        if (($projection['source_type'] ?? null) !== 'options_bank' || ($projection['source_surface_key'] ?? null) !== $key) {
            throw new RuntimeException("{$file} source projection identity mismatch.");
        }
        oc_string($projection['source_review_version'] ?? null, "{$file} source review version missing");
        $sourceCount = oc_int($projection['source_record_count'] ?? null, "{$file} source count missing");
        $entries = oc_array($projection['entries'] ?? null, "{$file} source entries missing");
        if (count($entries) !== $sourceCount) {
            throw new RuntimeException("{$file} source projection count mismatch.");
        }
        if (($bankProgressByKey[$key]['status'] ?? null) === 'BANK_REVIEWED' && ($bankProgressByKey[$key]['records'] ?? null) !== $sourceCount) {
            throw new RuntimeException("{$file} BANK_REVIEWED source count does not match canonical Options Bank progress.");
        }

        $sourceIds = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                throw new RuntimeException("{$file} invalid projection entry.");
            }
            $sourceId = oc_identifier(
                oc_string($entry['source_id'] ?? null, "{$file} projection source_id missing"),
                "{$file} projection source_id invalid",
            );
            if (isset($sourceIds[$sourceId])) {
                throw new RuntimeException("{$file} duplicate projection source {$sourceId}.");
            }
            $sourceIds[$sourceId] = true;

            $sourceKind = oc_enum($entry['source_kind'] ?? null, $projectionKinds, "{$file} projection source_kind invalid.");
            $disposition = oc_enum($entry['disposition'] ?? null, $projectionDispositions, "{$file} projection disposition invalid.");
            if (!array_key_exists('owner_surface', $entry) || (!is_string($entry['owner_surface']) && $entry['owner_surface'] !== null)) {
                throw new RuntimeException("{$file} projection owner_surface invalid for {$sourceId}.");
            }
            if ($disposition === 'OUT_OF_SURFACE_REFERENCE' && (!is_string($entry['owner_surface']) || trim($entry['owner_surface']) === '')) {
                throw new RuntimeException("{$file} out-of-surface projection {$sourceId} requires owner_surface.");
            }
            if ($sourceKind === 'out_of_surface' && $disposition !== 'OUT_OF_SURFACE_REFERENCE') {
                throw new RuntimeException("{$file} out_of_surface source {$sourceId} must use OUT_OF_SURFACE_REFERENCE.");
            }

            oc_string($entry['reason'] ?? null, "{$file} projection reason missing for {$sourceId}");
            oc_array($entry['evidence_refs'] ?? null, "{$file} projection evidence_refs missing for {$sourceId}");
            $mapped = oc_array($entry['atomic_ids'] ?? null, "{$file} projection atomic_ids missing for {$sourceId}");
            if (count($mapped) !== count(array_unique($mapped))) {
                throw new RuntimeException("{$file} duplicate projection atomic_ids for {$sourceId}.");
            }
            foreach ($mapped as $mappedId) {
                $mappedId = oc_identifier(
                    oc_string($mappedId, "{$file} invalid mapped Atomic Option id"),
                    "{$file} invalid mapped Atomic Option identifier",
                );
                if (!isset($atomicIds[$mappedId])) {
                    throw new RuntimeException("{$file} projection references missing {$mappedId}.");
                }
            }
        }
        ++$totalProjections;
    }

    if ($lifecycle[$status] >= 40 && ($bankProgressByKey[$key]['status'] ?? null) === 'BANK_REVIEWED' && $projection === null) {
        throw new RuntimeException("{$file} BANK_REVIEWED source requires source_projection before OPTION_CONTRACT_COMPLETE.");
    }
}

fwrite(
    STDOUT,
    sprintf(
        "Atomic Option contracts: PASS (%d instance(s), %d option(s), %d projection(s); %d inventory, %d option-contract, %d UX-complete).\n",
        count($files),
        $totalOptions,
        $totalProjections,
        $counts['atomic_inventory_surfaces'],
        $counts['option_contract_complete_surfaces'],
        $counts['ux_contract_complete_surfaces'],
    ),
);
