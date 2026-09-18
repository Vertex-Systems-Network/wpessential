<?php

declare(strict_types=1);

const P006K_GRANT = 'GOV-P001-CF-TEMP-009';
const P006K_MTIME = 946684800;
const P006K_FREE_BASE_VERSION = '0.1.0-dev';
const P006K_FREE_OVERLAP_VERSION = '0.1.1-test-overlap';
const P006K_PRO_BASE_VERSION = '0.1.0-dev';
const P006K_PRO_OVERLAP_VERSION = '0.1.1-test-overlap';
const P006K_API = '0.1.0';
const P006K_SCHEMA = 1;

function kFail(string $message): never
{
    throw new RuntimeException($message);
}

function kAssert(bool $condition, string $message): void
{
    if (!$condition) {
        kFail($message);
    }
}

function kEnv(string $key): string
{
    $value = trim((string) getenv($key));
    if ($value === '') {
        kFail("Missing env: {$key}");
    }

    return $value;
}

function kHashFile(string $path): string
{
    kAssert(is_file($path) && filesize($path) > 0, "Missing artifact: {$path}");
    $hash = hash_file('sha256', $path);
    kAssert(is_string($hash), "Unable to hash artifact: {$path}");

    return $hash;
}

function kPair(string $freeHash, string $proHash): string
{
    return hash('sha256', $freeHash . ':' . $proHash);
}

/** @return array<string,string> */
function kReadZipEntries(string $path): array
{
    kAssert(class_exists(ZipArchive::class), 'ZipArchive unavailable');

    $zip = new ZipArchive();
    $opened = $zip->open($path);
    kAssert($opened === true, "Unable to open ZIP: {$path}");

    $entries = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        kAssert(is_array($stat) && isset($stat['name']) && is_string($stat['name']), 'Unable to inspect ZIP entry');
        $name = $stat['name'];
        kAssert($name !== '' && !str_contains($name, '../') && !str_starts_with($name, '/'), "Unsafe ZIP entry: {$name}");
        if (str_ends_with($name, '/')) {
            continue;
        }

        $value = $zip->getFromIndex($i);
        kAssert(is_string($value), "Unable to read ZIP entry: {$name}");
        kAssert(!array_key_exists($name, $entries), "Duplicate ZIP entry: {$name}");
        $entries[$name] = $value;
    }

    $zip->close();
    ksort($entries, SORT_STRING);
    kAssert($entries !== [], "ZIP has no file entries: {$path}");

    return $entries;
}

/** @param array<string,string> $entries */
function kWriteZip(string $path, array $entries): void
{
    if (!is_dir(dirname($path))) {
        kAssert(mkdir(dirname($path), 0775, true) || is_dir(dirname($path)), "Unable to create directory: " . dirname($path));
    }

    @unlink($path);

    $zip = new ZipArchive();
    $opened = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    kAssert($opened === true, "Unable to create ZIP: {$path}");

    ksort($entries, SORT_STRING);
    foreach ($entries as $name => $value) {
        kAssert($zip->addFromString($name, $value), "Unable to add ZIP entry: {$name}");
        kAssert($zip->setMtimeName($name, P006K_MTIME), "Unable to normalize ZIP mtime: {$name}");
        kAssert(
            $zip->setExternalAttributesName($name, ZipArchive::OPSYS_UNIX, 0100644 << 16),
            "Unable to normalize ZIP permissions: {$name}",
        );
        kAssert($zip->setCompressionName($name, ZipArchive::CM_DEFLATE, 9), "Unable to normalize ZIP compression: {$name}");
    }

    kAssert($zip->close(), "Unable to close ZIP: {$path}");
    kAssert(is_file($path) && filesize($path) > 0, "ZIP write produced no artifact: {$path}");
}

function kReplaceOne(string $value, string $from, string $to, string $label): string
{
    $count = substr_count($value, $from);
    kAssert($count === 1, "{$label}: expected exactly one replacement anchor, found {$count}");

    return str_replace($from, $to, $value);
}

/** @param array<string,string> $entries @return array<string,string> */
function kBuildF1(array $entries): array
{
    $main = 'wpessential/wpessential.php';
    kAssert(isset($entries[$main]), 'Canonical Free main plugin entry missing');

    $value = $entries[$main];
    $value = kReplaceOne(
        $value,
        ' * Version: ' . P006K_FREE_BASE_VERSION,
        ' * Version: ' . P006K_FREE_OVERLAP_VERSION,
        'F1 header version',
    );
    $value = kReplaceOne(
        $value,
        "define('WPE_VERSION', '" . P006K_FREE_BASE_VERSION . "');",
        "define('WPE_VERSION', '" . P006K_FREE_OVERLAP_VERSION . "');",
        'F1 WPE_VERSION',
    );
    $entries[$main] = $value;

    return $entries;
}

/** @param array<string,string> $entries @return array<string,string> */
function kBuildP0(array $entries): array
{
    $main = 'wpessential-pro/wpessential-pro.php';
    kAssert(isset($entries[$main]), 'Canonical Pro main plugin entry missing');

    $value = $entries[$main];
    $value = kReplaceOne(
        $value,
        "define('WPE_PRO_MAX_FREE_VERSION', '" . P006K_FREE_BASE_VERSION . "');",
        "define('WPE_PRO_MAX_FREE_VERSION', '" . P006K_FREE_OVERLAP_VERSION . "');",
        'P0 max Free overlap',
    );
    $entries[$main] = $value;

    return $entries;
}

/** @param array<string,string> $entries @return array<string,string> */
function kBuildP1(array $entries): array
{
    $main = 'wpessential-pro/wpessential-pro.php';
    kAssert(isset($entries[$main]), 'Canonical Pro main plugin entry missing');

    $value = $entries[$main];
    $value = kReplaceOne(
        $value,
        ' * Version: ' . P006K_PRO_BASE_VERSION,
        ' * Version: ' . P006K_PRO_OVERLAP_VERSION,
        'P1 header version',
    );
    $value = kReplaceOne(
        $value,
        "define('WPE_PRO_VERSION', '" . P006K_PRO_BASE_VERSION . "');",
        "define('WPE_PRO_VERSION', '" . P006K_PRO_OVERLAP_VERSION . "');",
        'P1 WPE_PRO_VERSION',
    );
    $value = kReplaceOne(
        $value,
        "define('WPE_PRO_MAX_FREE_VERSION', '" . P006K_FREE_BASE_VERSION . "');",
        "define('WPE_PRO_MAX_FREE_VERSION', '" . P006K_FREE_OVERLAP_VERSION . "');",
        'P1 max Free overlap',
    );
    $entries[$main] = $value;

    return $entries;
}

/** @param array<string,string> $a @param array<string,string> $b @return list<string> */
function kChangedEntries(array $a, array $b): array
{
    kAssert(array_keys($a) === array_keys($b), 'Variant ZIP entry-set drift');

    $changed = [];
    foreach ($a as $name => $value) {
        if (!hash_equals(hash('sha256', $value), hash('sha256', $b[$name]))) {
            $changed[] = $name;
        }
    }

    sort($changed, SORT_STRING);
    return $changed;
}

/** @param array<string,string> $entries */
function kTreeDigest(array $entries): string
{
    ksort($entries, SORT_STRING);
    $ctx = hash_init('sha256');
    foreach ($entries as $name => $value) {
        hash_update($ctx, $name . "\0" . hash('sha256', $value) . "\n");
    }

    return hash_final($ctx);
}

function kRegex(string $pattern, string $value, string $label): string
{
    $matched = preg_match($pattern, $value, $m);
    kAssert($matched === 1 && isset($m[1]) && is_string($m[1]), "Unable to parse {$label}");

    return $m[1];
}

/** @return array<string,mixed> */
function kFreeMetadata(string $main): array
{
    $header = kRegex('/^ \* Version:\s*([^\s]+)\s*$/m', $main, 'Free plugin header version');
    $constant = kRegex("/define\('WPE_VERSION',\s*'([^']+)'\);/", $main, 'WPE_VERSION');
    $api = kRegex("/define\('WPE_PLATFORM_API_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PLATFORM_API_VERSION');
    $schema = (int) kRegex("/define\('WPE_PLATFORM_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PLATFORM_SCHEMA_GENERATION');

    kAssert($header === $constant, 'Free header/constant marketing-version mismatch');

    return [
        'version' => $constant,
        'platform_api' => $api,
        'platform_schema' => $schema,
    ];
}

/** @return array<string,mixed> */
function kProMetadata(string $main): array
{
    $header = kRegex('/^ \* Version:\s*([^\s]+)\s*$/m', $main, 'Pro plugin header version');
    $version = kRegex("/define\('WPE_PRO_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_VERSION');
    $minFree = kRegex("/define\('WPE_PRO_MIN_FREE_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MIN_FREE_VERSION');
    $maxFree = kRegex("/define\('WPE_PRO_MAX_FREE_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MAX_FREE_VERSION');
    $minApi = kRegex("/define\('WPE_PRO_MIN_PLATFORM_API_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MIN_PLATFORM_API_VERSION');
    $maxApi = kRegex("/define\('WPE_PRO_MAX_PLATFORM_API_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MAX_PLATFORM_API_VERSION');
    $minSchema = (int) kRegex("/define\('WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION');
    $maxSchema = (int) kRegex("/define\('WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION');
    $schema = (int) kRegex("/define\('WPE_PRO_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PRO_SCHEMA_GENERATION');

    kAssert($header === $version, 'Pro header/constant marketing-version mismatch');

    return [
        'version' => $version,
        'min_free_version' => $minFree,
        'max_free_version' => $maxFree,
        'min_platform_api' => $minApi,
        'max_platform_api' => $maxApi,
        'min_platform_schema' => $minSchema,
        'max_platform_schema' => $maxSchema,
        'schema' => $schema,
    ];
}

/** @param array<string,string> $entries @param array<string,mixed> $metadata @return array<string,mixed> */
function kNode(string $logical, string $fileName, string $zipPath, array $entries, string $mainEntry, array $metadata): array
{
    kAssert(isset($entries[$mainEntry]), "{$logical}: main entry missing");

    return [
        'logical_node' => $logical,
        'artifact' => $fileName,
        'sha256' => kHashFile($zipPath),
        'payload_tree_sha256' => kTreeDigest($entries),
        'entry_count' => count($entries),
        'main_entry' => $mainEntry,
        'main_entry_sha256' => hash('sha256', $entries[$mainEntry]),
        'metadata' => $metadata,
    ];
}

/** @param array<string,mixed> $value */
function kWriteJson(string $path, array $value): void
{
    $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    kAssert(file_put_contents($path, $encoded . PHP_EOL) !== false, "Unable to write JSON: {$path}");
}

try {
    $sourceSha = kEnv('WPE_P006_SOURCE_SHA');
    kAssert(preg_match('/^[0-9a-f]{40}$/', $sourceSha) === 1, 'Invalid source SHA');

    $canonicalFree = kEnv('WPE_P006_CANONICAL_FREE_ZIP');
    $canonicalPro = kEnv('WPE_P006_CANONICAL_PRO_ZIP');
    $outputDir = rtrim(kEnv('WPE_P006_OUTPUT_DIR'), '/\\');

    if (!is_dir($outputDir)) {
        kAssert(mkdir($outputDir, 0775, true) || is_dir($outputDir), "Unable to create output directory: {$outputDir}");
    }

    $freeBaseEntries = kReadZipEntries($canonicalFree);
    $proBaseEntries = kReadZipEntries($canonicalPro);

    $freeMain = 'wpessential/wpessential.php';
    $proMain = 'wpessential-pro/wpessential-pro.php';
    kAssert(isset($freeBaseEntries[$freeMain]), 'Canonical Free plugin entry missing');
    kAssert(isset($proBaseEntries[$proMain]), 'Canonical Pro plugin entry missing');

    $canonicalFreeMetadata = kFreeMetadata($freeBaseEntries[$freeMain]);
    $canonicalProMetadata = kProMetadata($proBaseEntries[$proMain]);

    kAssert($canonicalFreeMetadata === [
        'version' => P006K_FREE_BASE_VERSION,
        'platform_api' => P006K_API,
        'platform_schema' => P006K_SCHEMA,
    ], 'Canonical Free metadata drifted outside Wave 1K authorization');

    kAssert($canonicalProMetadata === [
        'version' => P006K_PRO_BASE_VERSION,
        'min_free_version' => P006K_FREE_BASE_VERSION,
        'max_free_version' => P006K_FREE_BASE_VERSION,
        'min_platform_api' => P006K_API,
        'max_platform_api' => P006K_API,
        'min_platform_schema' => P006K_SCHEMA,
        'max_platform_schema' => P006K_SCHEMA,
        'schema' => P006K_SCHEMA,
    ], 'Canonical Pro metadata drifted outside Wave 1K authorization');

    $f0Entries = $freeBaseEntries;
    $f1Entries = kBuildF1($freeBaseEntries);
    $p0Entries = kBuildP0($proBaseEntries);
    $p1Entries = kBuildP1($proBaseEntries);

    kAssert(kChangedEntries($f0Entries, $f1Entries) === [$freeMain], 'F1 changed entries outside the authorized Free plugin entry');
    kAssert(kChangedEntries($proBaseEntries, $p0Entries) === [$proMain], 'P0 changed entries outside the authorized Pro plugin entry');
    kAssert(kChangedEntries($proBaseEntries, $p1Entries) === [$proMain], 'P1 changed entries outside the authorized Pro plugin entry');

    $paths = [
        'F0' => $outputDir . '/f0.zip',
        'F1' => $outputDir . '/f1.zip',
        'P0' => $outputDir . '/p0.zip',
        'P1' => $outputDir . '/p1.zip',
    ];

    @unlink($paths['F0']);
    kAssert(copy($canonicalFree, $paths['F0']), 'Unable to copy canonical Free ZIP as F0');
    kAssert(hash_equals(kHashFile($canonicalFree), kHashFile($paths['F0'])), 'F0 is not byte-identical to canonical Free ZIP');

    kWriteZip($paths['F1'], $f1Entries);
    kWriteZip($paths['P0'], $p0Entries);
    kWriteZip($paths['P1'], $p1Entries);

    $f0Meta = kFreeMetadata($f0Entries[$freeMain]);
    $f1Meta = kFreeMetadata($f1Entries[$freeMain]);
    $p0Meta = kProMetadata($p0Entries[$proMain]);
    $p1Meta = kProMetadata($p1Entries[$proMain]);

    kAssert($f1Meta === [
        'version' => P006K_FREE_OVERLAP_VERSION,
        'platform_api' => P006K_API,
        'platform_schema' => P006K_SCHEMA,
    ], 'F1 metadata mismatch');

    foreach ([$p0Meta, $p1Meta] as $index => $proMeta) {
        $expectedVersion = $index === 0 ? P006K_PRO_BASE_VERSION : P006K_PRO_OVERLAP_VERSION;
        kAssert($proMeta === [
            'version' => $expectedVersion,
            'min_free_version' => P006K_FREE_BASE_VERSION,
            'max_free_version' => P006K_FREE_OVERLAP_VERSION,
            'min_platform_api' => P006K_API,
            'max_platform_api' => P006K_API,
            'min_platform_schema' => P006K_SCHEMA,
            'max_platform_schema' => P006K_SCHEMA,
            'schema' => P006K_SCHEMA,
        ], ($index === 0 ? 'P0' : 'P1') . ' metadata mismatch');
    }

    $nodes = [
        'F0' => kNode('F0', 'f0.zip', $paths['F0'], $f0Entries, $freeMain, $f0Meta),
        'F1' => kNode('F1', 'f1.zip', $paths['F1'], $f1Entries, $freeMain, $f1Meta),
        'P0' => kNode('P0', 'p0.zip', $paths['P0'], $p0Entries, $proMain, $p0Meta),
        'P1' => kNode('P1', 'p1.zip', $paths['P1'], $p1Entries, $proMain, $p1Meta),
    ];

    $identity = [
        'protocol' => 'P-006',
        'wave' => '1K-lane-b2-compatible-update-order',
        'source_sha' => $sourceSha,
        'temporary_approval_id' => P006K_GRANT,
        'classification' => 'NON-RELEASE / TEST-ONLY EVIDENCE',
        'nodes' => $nodes,
        'pairs' => [
            'F0_P0' => [
                'free' => 'F0',
                'pro' => 'P0',
                'pair_id_sha256' => kPair($nodes['F0']['sha256'], $nodes['P0']['sha256']),
            ],
            'F1_P0' => [
                'free' => 'F1',
                'pro' => 'P0',
                'pair_id_sha256' => kPair($nodes['F1']['sha256'], $nodes['P0']['sha256']),
            ],
            'F0_P1' => [
                'free' => 'F0',
                'pro' => 'P1',
                'pair_id_sha256' => kPair($nodes['F0']['sha256'], $nodes['P1']['sha256']),
            ],
        ],
        'variant_boundary' => [
            'F1_changed_entries' => [$freeMain],
            'P0_changed_entries' => [$proMain],
            'P1_changed_entries' => [$proMain],
            'tracked_product_source_modified' => false,
            'canonical_release_artifacts_replaced' => false,
        ],
        'authorized_fixtures' => ['FP-45', 'FP-46', 'FP-53', 'FP-60'],
        'authorized_runtime_cells' => [
            'minimum' => ['wordpress' => '6.9', 'php' => '8.2', 'mysql' => '8.4'],
            'reference' => ['wordpress' => '7.1', 'php' => '8.5', 'mysql' => '8.4'],
        ],
        'certification_boundary' => [
            'permanent_p001_cf_certified' => false,
            'free_pro_pair_certified' => false,
            'runtime_certified' => false,
            'updater_or_tuf_certified' => false,
            'manual_upload_certified' => false,
            'adr_0010' => 'Proposed',
            'ga_or_release_authorized' => false,
        ],
    ];

    kWriteJson($outputDir . '/candidate-identity.json', $identity);
    fwrite(STDOUT, json_encode($identity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 Wave 1K artifact builder] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
