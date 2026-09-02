<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

/** @return array<string, mixed> */
function readTablesNativeJson(string $path): array
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
    $bank = readTablesNativeJson($file);
    foreach (($bank['records'] ?? []) as $record) {
        if (is_array($record) && is_string($record['id'] ?? null)) {
            $records[$record['id']] = true;
        }
    }
}
if (count($records) !== 165) {
    throw new RuntimeException('Surface 7 native audit requires the complete 165-record Bank candidate.');
}

$audit = readTablesNativeJson($root . '/config/product/options-bank-audits/tables-native-wordpress.json');
if (($audit['schema_version'] ?? null) !== 1
    || ($audit['bank_version'] ?? null) !== 'v1'
    || ($audit['surface']['id'] ?? null) !== 7
    || ($audit['surface']['key'] ?? null) !== 'tables'
    || ($audit['status'] ?? null) !== 'NATIVE_AUDITED') {
    throw new RuntimeException('Surface 7 native audit identity/status is invalid.');
}

$items = $audit['items'] ?? null;
if (!is_array($items) || count($items) !== 20) {
    throw new RuntimeException('Surface 7 native audit must contain exactly 20 dispositions.');
}

$counts = [
    'BANK_RECORD' => 0,
    'PROVIDER_MAPPING' => 0,
    'SYSTEM_RUNTIME' => 0,
    'OUT_OF_SURFACE' => 0,
    'LEGACY_COMPATIBILITY' => 0,
    'CORE_INTERNAL' => 0,
];
$ids = [];
foreach ($items as $item) {
    if (!is_array($item) || !is_string($item['id'] ?? null) || isset($ids[$item['id']])) {
        throw new RuntimeException('Surface 7 native audit item IDs must be unique strings.');
    }
    $ids[$item['id']] = true;
    $disposition = $item['disposition'] ?? null;
    if (!is_string($disposition) || !array_key_exists($disposition, $counts)) {
        throw new RuntimeException(sprintf('Unknown Surface 7 native audit disposition for %s.', $item['id']));
    }
    ++$counts[$disposition];
    foreach (($item['bank_record_ids'] ?? []) as $recordId) {
        if (!is_string($recordId) || !isset($records[$recordId])) {
            throw new RuntimeException(sprintf('Native audit item %s references missing Bank record %s.', $item['id'], is_scalar($recordId) ? (string) $recordId : 'value'));
        }
    }
    if ($disposition === 'OUT_OF_SURFACE' && !is_string($item['owner_surface'] ?? null)) {
        throw new RuntimeException(sprintf('Out-of-surface native item %s must name its canonical owner.', $item['id']));
    }
}

$coverage = $audit['coverage'] ?? [];
$expected = [
    'items' => 20,
    'bank_record' => 10,
    'provider_mapping' => 1,
    'system_runtime' => 2,
    'out_of_surface' => 7,
    'legacy_compatibility' => 0,
    'core_internal' => 0,
    'unresolved' => 0,
];
foreach ($expected as $key => $value) {
    if (($coverage[$key] ?? null) !== $value) {
        throw new RuntimeException(sprintf('Surface 7 native audit coverage %s disagrees with certified truth.', $key));
    }
}
if ($counts['BANK_RECORD'] !== 10
    || $counts['PROVIDER_MAPPING'] !== 1
    || $counts['SYSTEM_RUNTIME'] !== 2
    || $counts['OUT_OF_SURFACE'] !== 7) {
    throw new RuntimeException('Surface 7 native audit disposition counters drifted.');
}

printf("Surface 7 native WordPress audit contract: PASS (20 dispositions, 0 unresolved).\n");
