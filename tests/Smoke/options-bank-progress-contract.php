<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$bankDirectory = $root . '/config/product/options-bank';
$progressPath = $root . '/config/product/options-bank-progress.json';
$surfaceRegistryPath = $root . '/config/product/competitor-parity-surfaces.json';

/** @return array<string, mixed> */
function readBankProgressJson(string $path): array
{
    $contents = file_get_contents($path);
    if ($contents === false) {
        throw new RuntimeException(sprintf('Unable to read %s.', $path));
    }

    try {
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new RuntimeException(sprintf('Invalid JSON in %s: %s', $path, $exception->getMessage()), 0, $exception);
    }

    if (!is_array($decoded)) {
        throw new RuntimeException(sprintf('%s must contain a JSON object.', $path));
    }

    return $decoded;
}

$registry = readBankProgressJson($surfaceRegistryPath);
$progress = readBankProgressJson($progressPath);
$surfaces = $registry['surfaces'] ?? null;
$rows = $progress['surface_status'] ?? null;

if (!is_array($surfaces) || !is_array($rows)) {
    throw new RuntimeException('Options Bank progress or canonical surface registry is malformed.');
}

/** @var array<int, string> $canonicalById */
$canonicalById = [];
foreach ($surfaces as $surface) {
    if (!is_array($surface) || !is_int($surface['id'] ?? null) || !is_string($surface['key'] ?? null)) {
        throw new RuntimeException('Canonical surface registry contains an invalid row.');
    }
    $canonicalById[$surface['id']] = $surface['key'];
}

if (count($canonicalById) !== 56 || ($progress['target_surface_count'] ?? null) !== 56 || count($rows) !== 56) {
    throw new RuntimeException('Options Bank progress must cover exactly 56 canonical surfaces.');
}

/** @var array<string, int> $actualRecords */
$actualRecords = [];
$files = glob($bankDirectory . '/*.json');
if ($files === false) {
    throw new RuntimeException('Unable to enumerate Options Bank shards.');
}

foreach ($files as $file) {
    $bank = readBankProgressJson($file);
    $surface = $bank['surface'] ?? null;
    $records = $bank['records'] ?? null;
    if (!is_array($surface) || !is_string($surface['key'] ?? null) || !is_array($records)) {
        throw new RuntimeException(sprintf('Invalid Options Bank shard: %s', $file));
    }
    $key = $surface['key'];
    $actualRecords[$key] = ($actualRecords[$key] ?? 0) + count($records);
}

$allowedStatuses = ['UNSEEDED', 'BANK_SURFACE_SEEDED', 'NATIVE_AUDITED', 'MARKET_AUDITED', 'BANK_REVIEWED'];
$seen = [];
$seeded = 0;
$nativeAudited = 0;
$marketAudited = 0;
$reviewed = 0;
$totalRecords = 0;

foreach ($rows as $row) {
    if (!is_array($row) || !is_int($row['id'] ?? null) || !is_string($row['key'] ?? null)) {
        throw new RuntimeException('Options Bank progress contains an invalid surface row.');
    }

    $id = $row['id'];
    $key = $row['key'];
    $status = $row['status'] ?? null;
    $declaredRecords = $row['records'] ?? null;

    if (($canonicalById[$id] ?? null) !== $key || isset($seen[$key])) {
        throw new RuntimeException(sprintf('Invalid or duplicate progress mapping for %d/%s.', $id, $key));
    }
    $seen[$key] = true;

    if (!is_string($status) || !in_array($status, $allowedStatuses, true) || !is_int($declaredRecords)) {
        throw new RuntimeException(sprintf('Surface %s has invalid status/record count.', $key));
    }

    $actual = $actualRecords[$key] ?? 0;
    if ($declaredRecords !== $actual) {
        throw new RuntimeException(sprintf('Surface %s declares %d Bank records but shards contain %d.', $key, $declaredRecords, $actual));
    }

    if ($status === 'UNSEEDED' && $actual !== 0) {
        throw new RuntimeException(sprintf('Surface %s is marked UNSEEDED but has Bank records.', $key));
    }
    if ($status !== 'UNSEEDED') {
        ++$seeded;
    }
    if (in_array($status, ['NATIVE_AUDITED', 'MARKET_AUDITED', 'BANK_REVIEWED'], true)) {
        ++$nativeAudited;
    }
    if (in_array($status, ['MARKET_AUDITED', 'BANK_REVIEWED'], true)) {
        ++$marketAudited;
    }
    if ($status === 'BANK_REVIEWED') {
        ++$reviewed;
    }

    $totalRecords += $actual;
}

$truth = $progress['truth'] ?? null;
if (!is_array($truth)) {
    throw new RuntimeException('Options Bank progress is missing truth counters.');
}

$expected = [
    'seeded_surfaces' => $seeded,
    'native_audited_surfaces' => $nativeAudited,
    'market_audited_surfaces' => $marketAudited,
    'bank_reviewed_surfaces' => $reviewed,
    'total_bank_records' => $totalRecords,
];

foreach ($expected as $key => $value) {
    if (($truth[$key] ?? null) !== $value) {
        throw new RuntimeException(sprintf('Options Bank truth.%s must be %d.', $key, $value));
    }
}

fwrite(STDOUT, sprintf("Options Bank progress contract: PASS (56 surfaces, %d seeded, %d records).\n", $seeded, $totalRecords));
