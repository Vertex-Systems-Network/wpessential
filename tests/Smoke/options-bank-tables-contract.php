<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);

/** @return array<string, mixed> */
function readTablesBankJson(string $path): array
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

readTablesBankJson($root . '/config/product/options-bank.schema.json');
$files = glob($root . '/config/product/options-bank/tables--*.json') ?: [];
if (count($files) !== 6) {
    throw new RuntimeException(sprintf('Surface 7 must have exactly 6 local Bank shards; found %d.', count($files)));
}

$records = [];
$paths = [];
$rejected = 0;
$deferred = 0;
$exceed = 0;
$requiredKeys = ['id','feature_group','option_path','label','classification','hard_soft','horizon','adoption','priority'];

foreach ($files as $file) {
    $bank = readTablesBankJson($file);
    if (($bank['schema_version'] ?? null) !== 1
        || ($bank['bank_version'] ?? null) !== 'v1'
        || ($bank['surface']['id'] ?? null) !== 7
        || ($bank['surface']['key'] ?? null) !== 'tables') {
        throw new RuntimeException(sprintf('Invalid Surface 7 Bank shard identity: %s.', $file));
    }
    if (($bank['status'] ?? null) !== 'BANK_SURFACE_SEEDED') {
        throw new RuntimeException(sprintf('Surface 7 Bank shard must remain discovery-seeded until canonical promotion: %s.', $file));
    }
    $localRecords = $bank['records'] ?? null;
    if (!is_array($localRecords) || $localRecords === []) {
        throw new RuntimeException(sprintf('Surface 7 Bank shard has no records: %s.', $file));
    }
    $coverage = $bank['coverage'] ?? [];
    if (($coverage['records'] ?? null) !== count($localRecords)
        || ($coverage['unreviewed'] ?? null) !== 0
        || ($coverage['adopted_or_classified'] ?? null) !== count($localRecords)) {
        throw new RuntimeException(sprintf('Surface 7 Bank shard coverage disagrees with records: %s.', $file));
    }
    foreach ($localRecords as $record) {
        if (!is_array($record)) {
            throw new RuntimeException('Surface 7 Bank records must be objects.');
        }
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $record)) {
                throw new RuntimeException(sprintf('Surface 7 record is missing required key %s.', $key));
            }
        }
        $id = $record['id'];
        $optionPath = $record['option_path'];
        if (!is_string($id) || !str_starts_with($id, 'tables.') || isset($records[$id])) {
            throw new RuntimeException('Surface 7 record IDs must be unique tables.* values.');
        }
        if (!is_string($optionPath) || $optionPath === '' || isset($paths[$optionPath])) {
            throw new RuntimeException('Surface 7 option_path values must be non-empty and unique.');
        }
        $records[$id] = $record;
        $paths[$optionPath] = true;
        if (($record['adoption'] ?? null) === 'UNREVIEWED') {
            throw new RuntimeException(sprintf('Surface 7 record %s remains UNREVIEWED.', $id));
        }
        if (($record['classification'] ?? null) === 'REJECTED_UNSAFE') {
            ++$rejected;
            if (($record['adoption'] ?? null) !== 'REJECT' || ($record['priority'] ?? null) !== 'NOT_SCHEDULED') {
                throw new RuntimeException(sprintf('Rejected Surface 7 record %s is policy-inconsistent.', $id));
            }
        }
        if (($record['classification'] ?? null) === 'DEFERRED') {
            ++$deferred;
            if (($record['adoption'] ?? null) !== 'LATER' || ($record['priority'] ?? null) !== 'P3_LATER' || ($record['horizon'] ?? null) !== 'WPE_FUTURE') {
                throw new RuntimeException(sprintf('Deferred Surface 7 record %s is policy-inconsistent.', $id));
            }
        }
        if (($record['classification'] ?? null) === 'WPE_EXCEED') {
            ++$exceed;
            if (($record['adoption'] ?? null) !== 'WPE_EXCEED' || ($record['priority'] ?? null) !== 'P1_EXCEED' || ($record['horizon'] ?? null) !== 'WPE_FUTURE') {
                throw new RuntimeException(sprintf('WPE_EXCEED Surface 7 record %s is policy-inconsistent.', $id));
            }
        }
    }
}

if (count($records) !== 165 || $rejected !== 14 || $deferred !== 5 || $exceed !== 10) {
    throw new RuntimeException(sprintf('Surface 7 Bank truth drifted (records=%d rejected=%d deferred=%d exceed=%d).', count($records), $rejected, $deferred, $exceed));
}

foreach ([
    'tables.security.generic_sql_console','tables.security.arbitrary_ddl','tables.security.drop_truncate_console',
    'tables.security.raw_identifier','tables.security.privilege_bypass','tables.security.cross_site_access',
    'tables.security.core_table_mutation','tables.security.unvalidated_mutation','tables.portability.unbounded_export',
    'tables.migration.raw_sql_transform','tables.migration.destructive_without_recovery','tables.ai.autonomous_destructive',
] as $id) {
    if (($records[$id]['classification'] ?? null) !== 'REJECTED_UNSAFE') {
        throw new RuntimeException(sprintf('Required Surface 7 unsafe rejection is missing: %s.', $id));
    }
}

printf("Surface 7 Options Bank contract: PASS (165 records; 14 rejected unsafe, 5 deferred, 10 WPE-exceed).\n");
