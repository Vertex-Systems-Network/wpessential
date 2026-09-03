<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$contractPath = $root . '/config/product/option-contracts/columns.json';
$bankPaths = [
    $root . '/config/product/options-bank/columns.json',
    $root . '/config/product/options-bank/columns--sources-formatting.json',
    $root . '/config/product/options-bank/columns--sorting-filtering-editing.json',
    $root . '/config/product/options-bank/columns--export-performance-portability.json',
    $root . '/config/product/options-bank/columns--wpe-exceed-market-v1.json',
];

/** @return array<string, mixed> */
function columns_contract_json(string $path): array
{
    if (!is_file($path)) {
        throw new RuntimeException(sprintf('Required JSON file is missing: %s', $path));
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        throw new RuntimeException(sprintf('Unable to read JSON file: %s', $path));
    }

    try {
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new RuntimeException(
            sprintf('Invalid JSON in %s: %s', $path, $exception->getMessage()),
            0,
            $exception,
        );
    }

    if (!is_array($decoded)) {
        throw new RuntimeException(sprintf('JSON root must be an object: %s', $path));
    }

    return $decoded;
}

/** @param mixed $value */
function columns_contract_string($value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }

    return $value;
}

/** @param mixed $value */
function columns_contract_array($value, string $message): array
{
    if (!is_array($value)) {
        throw new RuntimeException($message);
    }

    return $value;
}

/**
 * @param array<string, array<string, mixed>> $projectionBySource
 * @param list<string>                       $atomicIds
 */
function columns_contract_require_projection(
    array $projectionBySource,
    string $sourceId,
    string $sourceKind,
    string $disposition,
    array $atomicIds,
    ?string $ownerSurface = null,
): void {
    $entry = $projectionBySource[$sourceId] ?? null;
    if (!is_array($entry)) {
        throw new RuntimeException(sprintf('Required source projection is missing: %s', $sourceId));
    }

    if (($entry['source_kind'] ?? null) !== $sourceKind) {
        throw new RuntimeException(sprintf('%s has incorrect source_kind.', $sourceId));
    }
    if (($entry['disposition'] ?? null) !== $disposition) {
        throw new RuntimeException(sprintf('%s has incorrect disposition.', $sourceId));
    }
    if (($entry['owner_surface'] ?? null) !== $ownerSurface) {
        throw new RuntimeException(sprintf('%s has incorrect owner_surface.', $sourceId));
    }

    $actualAtomicIds = columns_contract_array(
        $entry['atomic_ids'] ?? null,
        sprintf('%s has invalid atomic_ids.', $sourceId),
    );
    sort($actualAtomicIds, SORT_STRING);
    sort($atomicIds, SORT_STRING);
    if ($actualAtomicIds !== $atomicIds) {
        throw new RuntimeException(sprintf('%s has incorrect Atomic Option mapping.', $sourceId));
    }
}

$contract = columns_contract_json($contractPath);
$contractStatus = $contract['status'] ?? null;
if (($contract['schema_version'] ?? null) !== 1
    || ($contract['surface_id'] ?? null) !== 8
    || ($contract['surface_key'] ?? null) !== 'columns'
    || !in_array($contractStatus, ['OPTION_CONTRACT_COMPLETE', 'UX_CONTRACT_COMPLETE'], true)
) {
    throw new RuntimeException('Surface 8 Atomic Option contract identity/lifecycle is invalid.');
}

/** @var array<string, array<string, mixed>> $bankRecordsById */
$bankRecordsById = [];
$bankRecordCount = 0;
foreach ($bankPaths as $bankPath) {
    $bank = columns_contract_json($bankPath);
    $surface = columns_contract_array($bank['surface'] ?? null, sprintf('%s has no surface.', $bankPath));
    if (($surface['id'] ?? null) !== 8 || ($surface['key'] ?? null) !== 'columns') {
        throw new RuntimeException(sprintf('%s is not a Surface 8 Bank shard.', $bankPath));
    }

    $records = columns_contract_array($bank['records'] ?? null, sprintf('%s has no records.', $bankPath));
    $coverage = columns_contract_array($bank['coverage'] ?? null, sprintf('%s has no coverage.', $bankPath));
    if (($coverage['records'] ?? null) !== count($records)) {
        throw new RuntimeException(sprintf('%s Bank coverage count is inconsistent.', $bankPath));
    }

    foreach ($records as $record) {
        if (!is_array($record)) {
            throw new RuntimeException(sprintf('%s contains an invalid record.', $bankPath));
        }

        $recordId = columns_contract_string($record['id'] ?? null, sprintf('%s record id missing.', $bankPath));
        if (isset($bankRecordsById[$recordId])) {
            throw new RuntimeException(sprintf('Duplicate Surface 8 Bank record: %s', $recordId));
        }
        $bankRecordsById[$recordId] = $record;
        ++$bankRecordCount;
    }
}

if ($bankRecordCount !== 214) {
    throw new RuntimeException(sprintf('Surface 8 must contain exactly 214 Bank records; found %d.', $bankRecordCount));
}

$atomicIds = [];
$atomicById = [];
foreach (columns_contract_array($contract['feature_groups'] ?? null, 'Surface 8 feature_groups missing.') as $group) {
    if (!is_array($group)) {
        throw new RuntimeException('Surface 8 contains an invalid feature group.');
    }

    foreach (columns_contract_array($group['options'] ?? null, 'Surface 8 feature group options missing.') as $option) {
        if (!is_array($option)) {
            throw new RuntimeException('Surface 8 contains an invalid Atomic Option.');
        }
        $atomicId = columns_contract_string($option['id'] ?? null, 'Surface 8 Atomic Option id missing.');
        if (isset($atomicIds[$atomicId])) {
            throw new RuntimeException(sprintf('Duplicate Surface 8 Atomic Option id: %s', $atomicId));
        }
        $atomicIds[$atomicId] = true;
        $atomicById[$atomicId] = $option;
    }
}

if (count($atomicIds) !== 41) {
    throw new RuntimeException(sprintf('Surface 8 contract must emit 41 Atomic Options; found %d.', count($atomicIds)));
}

$projection = columns_contract_array($contract['source_projection'] ?? null, 'Surface 8 source_projection missing.');
if (($projection['source_type'] ?? null) !== 'options_bank'
    || ($projection['source_surface_key'] ?? null) !== 'columns'
    || ($projection['source_record_count'] ?? null) !== 214
) {
    throw new RuntimeException('Surface 8 source projection identity/count is invalid.');
}

/** @var array<string, array<string, mixed>> $projectionBySource */
$projectionBySource = [];
foreach (columns_contract_array($projection['entries'] ?? null, 'Surface 8 projection entries missing.') as $entry) {
    if (!is_array($entry)) {
        throw new RuntimeException('Surface 8 contains an invalid projection entry.');
    }

    $sourceId = columns_contract_string($entry['source_id'] ?? null, 'Projection source_id missing.');
    if (isset($projectionBySource[$sourceId])) {
        throw new RuntimeException(sprintf('Projection maps source %s more than once.', $sourceId));
    }
    if (!isset($bankRecordsById[$sourceId])) {
        throw new RuntimeException(sprintf('Projection references non-Bank source %s.', $sourceId));
    }

    foreach (columns_contract_array($entry['atomic_ids'] ?? null, sprintf('%s atomic_ids invalid.', $sourceId)) as $atomicId) {
        if (!is_string($atomicId) || !isset($atomicIds[$atomicId])) {
            throw new RuntimeException(sprintf('%s references an unknown Atomic Option.', $sourceId));
        }
    }

    $projectionBySource[$sourceId] = $entry;
}

if (count($projectionBySource) !== 214) {
    throw new RuntimeException(sprintf('Surface 8 must project all 214 Bank records; found %d.', count($projectionBySource)));
}

$unmapped = array_diff_key($bankRecordsById, $projectionBySource);
if ($unmapped !== []) {
    throw new RuntimeException(sprintf('Surface 8 has %d unmapped Bank record(s).', count($unmapped)));
}

// Native WordPress list-table mechanics are runtime obligations, not settings.
foreach (array_keys($bankRecordsById) as $recordId) {
    if (!str_starts_with($recordId, 'columns.native.')) {
        continue;
    }
    columns_contract_require_projection(
        $projectionBySource,
        $recordId,
        'native_runtime',
        'RUNTIME_IMPLEMENTATION_EVIDENCE',
        [],
    );
}

// Diagnostics/effective state remain read-only runtime evidence.
foreach (array_keys($bankRecordsById) as $recordId) {
    if (!str_starts_with($recordId, 'columns.diagnostic.')) {
        continue;
    }
    columns_contract_require_projection(
        $projectionBySource,
        $recordId,
        'diagnostic',
        'EFFECTIVE_OR_DIAGNOSTIC',
        [],
    );
}

// User preferences remain user-scoped and do not mutate the shared View definition.
foreach ([
    'columns.preference.chosen_view',
    'columns.preference.temporary_sort',
    'columns.preference.temporary_filters',
    'columns.preference.column_visibility',
    'columns.preference.density',
] as $recordId) {
    columns_contract_require_projection(
        $projectionBySource,
        $recordId,
        'user_preference',
        'USER_PREFERENCE_ATOMIC',
        ['columns.preference.runtime'],
    );
}
if (($atomicById['columns.preference.runtime']['storage']['mode'] ?? null) !== 'user_preference'
    || ($atomicById['columns.preference.runtime']['multisite']['scope'] ?? null) !== 'user'
) {
    throw new RuntimeException('Personal runtime preferences must remain user-scoped preference state.');
}

// Canonical peer ownership must remain references rather than shadow engines.
$ownerAssertions = [
    'columns.target.wpe_tables' => 'tables',
    'columns.source.field' => 'fields',
    'columns.source.taxonomy' => 'taxonomy',
    'columns.source.relation' => 'relations',
    'columns.source.media' => 'media',
    'columns.source.status' => 'status',
    'columns.source.query_aggregate' => 'query',
    'columns.filter.relation_ops' => 'relations',
    'columns.portability.map_field' => 'fields',
    'columns.portability.map_query' => 'query',
    'columns.portability.map_relation' => 'relations',
];
foreach ($ownerAssertions as $recordId => $ownerSurface) {
    columns_contract_require_projection(
        $projectionBySource,
        $recordId,
        'out_of_surface',
        'OUT_OF_SURFACE_REFERENCE',
        [],
        $ownerSurface,
    );
}

foreach (['columns.sort.enabled', 'columns.filter.enabled', 'columns.search.inclusion'] as $recordId) {
    $entry = $projectionBySource[$recordId] ?? null;
    if (!is_array($entry) || ($entry['owner_surface'] ?? null) !== 'query') {
        throw new RuntimeException(sprintf('%s must preserve Query ownership.', $recordId));
    }
}

foreach (['columns.edit.inline_enabled', 'columns.edit.validation', 'columns.bulk.enabled', 'columns.quick_add.form'] as $recordId) {
    $entry = $projectionBySource[$recordId] ?? null;
    if (!is_array($entry) || ($entry['owner_surface'] ?? null) !== 'fields') {
        throw new RuntimeException(sprintf('%s must preserve source/Fields ownership.', $recordId));
    }
}

// Unsafe executable configuration must remain prohibited.
columns_contract_require_projection(
    $projectionBySource,
    'columns.source.arbitrary_php',
    'rejected_unsafe',
    'REJECTED_UNSAFE',
    ['columns.safety.arbitrary_code'],
);
$unsafe = $atomicById['columns.safety.arbitrary_code'] ?? null;
if (!is_array($unsafe)
    || ($unsafe['parity_status'] ?? null) !== 'REJECTED_UNSAFE'
    || ($unsafe['security']['class'] ?? null) !== 'prohibited'
) {
    throw new RuntimeException('Arbitrary executable source policy must remain REJECTED_UNSAFE/prohibited.');
}

// Visibility never becomes authorization.
columns_contract_require_projection(
    $projectionBySource,
    'columns.visibility.not_authorization',
    'wpe_exceed',
    'WPE_EXCEED',
    ['columns.safety.accessibility_authorization'],
);
$visibility = $atomicById['columns.visibility.policy'] ?? null;
if (!is_array($visibility)
    || !str_contains((string) ($visibility['description'] ?? ''), 'never authorization')
) {
    throw new RuntimeException('Surface 8 visibility contract must explicitly remain presentation-only.');
}

$shortcode = $projectionBySource['columns.source.shortcode'] ?? null;
if (!is_array($shortcode)
    || ($shortcode['source_kind'] ?? null) !== 'deferred'
    || ($shortcode['disposition'] ?? null) !== 'DEFERRED'
    || ($shortcode['atomic_ids'] ?? null) !== []
) {
    throw new RuntimeException('Expert shortcode source must remain deferred and non-authored.');
}

$coverage = columns_contract_array($contract['coverage_summary'] ?? null, 'Surface 8 coverage_summary missing.');
$expectedCoverage = [
    'atomic_options' => 41,
    'parity' => 31,
    'exceeds' => 8,
    'deferred' => 0,
    'rejected_unsafe' => 1,
    'missing' => 0,
    'unclassified' => 0,
];
foreach ($expectedCoverage as $field => $expected) {
    if (($coverage[$field] ?? null) !== $expected) {
        throw new RuntimeException(sprintf('Surface 8 coverage %s must be %d.', $field, $expected));
    }
}

fwrite(
    STDOUT,
    sprintf(
        "Admin Columns Atomic Option contract: PASS (%d Bank records -> %d Atomic Options; missing 0, unclassified 0).\n",
        $bankRecordCount,
        count($atomicIds),
    ),
);
