<?php

declare(strict_types=1);

const P006L_GRANT = 'GOV-P001-CF-TEMP-010';
const P006L_MTIME = 946684800;

const P006L_FREE_BASE_VERSION = '0.1.0-dev';
const P006L_FREE_OVERLAP_VERSION = '0.1.1-test-overlap';
const P006L_FREE_BREAKING_VERSION = '0.2.0-test-breaking';

const P006L_PRO_BASE_VERSION = '0.1.0-dev';
const P006L_PRO_BREAKING_VERSION = '0.2.0-test-breaking';

const P006L_API_BASE = '0.1.0';
const P006L_API_BREAKING = '0.2.0';
const P006L_SCHEMA = 1;

function lFail(string $message): never
{
    throw new RuntimeException($message);
}

function lAssert(bool $condition, string $message): void
{
    if (!$condition) {
        lFail($message);
    }
}

function lEnv(string $key): string
{
    $value = trim((string) getenv($key));
    if ($value === '') {
        lFail("Missing env: {$key}");
    }

    return $value;
}

function lHashFile(string $path): string
{
    lAssert(is_file($path) && filesize($path) > 0, "Missing artifact: {$path}");
    $hash = hash_file('sha256', $path);
    lAssert(is_string($hash), "Unable to hash artifact: {$path}");

    return $hash;
}

function lPair(string $freeHash, string $proHash): string
{
    return hash('sha256', $freeHash . ':' . $proHash);
}

/** @return array<string,string> */
function lReadZipEntries(string $path): array
{
    lAssert(class_exists(ZipArchive::class), 'ZipArchive unavailable');

    $zip = new ZipArchive();
    $opened = $zip->open($path);
    lAssert($opened === true, "Unable to open ZIP: {$path}");

    $entries = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        lAssert(is_array($stat) && isset($stat['name']) && is_string($stat['name']), 'Unable to inspect ZIP entry');

        $name = $stat['name'];
        lAssert($name !== '' && !str_contains($name, '../') && !str_starts_with($name, '/'), "Unsafe ZIP entry: {$name}");
        if (str_ends_with($name, '/')) {
            continue;
        }

        $value = $zip->getFromIndex($i);
        lAssert(is_string($value), "Unable to read ZIP entry: {$name}");
        lAssert(!array_key_exists($name, $entries), "Duplicate ZIP entry: {$name}");

        $entries[$name] = $value;
    }

    $zip->close();
    ksort($entries, SORT_STRING);
    lAssert($entries !== [], "ZIP has no file entries: {$path}");

    return $entries;
}

/** @param array<string,string> $entries */
function lWriteZip(string $path, array $entries): void
{
    $directory = dirname($path);
    if (!is_dir($directory)) {
        lAssert(mkdir($directory, 0775, true) || is_dir($directory), "Unable to create directory: {$directory}");
    }

    @unlink($path);

    $zip = new ZipArchive();
    $opened = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    lAssert($opened === true, "Unable to create ZIP: {$path}");

    ksort($entries, SORT_STRING);
    foreach ($entries as $name => $value) {
        lAssert($zip->addFromString($name, $value), "Unable to add ZIP entry: {$name}");
        lAssert($zip->setMtimeName($name, P006L_MTIME), "Unable to normalize ZIP mtime: {$name}");
        lAssert(
            $zip->setExternalAttributesName($name, ZipArchive::OPSYS_UNIX, 0100644 << 16),
            "Unable to normalize ZIP permissions: {$name}",
        );
        lAssert($zip->setCompressionName($name, ZipArchive::CM_DEFLATE, 9), "Unable to normalize ZIP compression: {$name}");
    }

    lAssert($zip->close(), "Unable to close ZIP: {$path}");
    lAssert(is_file($path) && filesize($path) > 0, "ZIP write produced no artifact: {$path}");
}

function lReplaceOne(string $value, string $from, string $to, string $label): string
{
    $count = substr_count($value, $from);
    lAssert($count === 1, "{$label}: expected exactly one replacement anchor, found {$count}");

    return str_replace($from, $to, $value);
}

/** @param array<string,string> $entries @return array<string,string> */
function lBuildF1(array $entries): array
{
    $main = 'wpessential/wpessential.php';
    lAssert(isset($entries[$main]), 'Canonical Free main plugin entry missing');

    $value = $entries[$main];
    $value = lReplaceOne(
        $value,
        ' * Version: ' . P006L_FREE_BASE_VERSION,
        ' * Version: ' . P006L_FREE_OVERLAP_VERSION,
        'F1 header version',
    );
    $value = lReplaceOne(
        $value,
        "define('WPE_VERSION', '" . P006L_FREE_BASE_VERSION . "');",
        "define('WPE_VERSION', '" . P006L_FREE_OVERLAP_VERSION . "');",
        'F1 WPE_VERSION',
    );

    $entries[$main] = $value;
    return $entries;
}

/** @param array<string,string> $entries @return array<string,string> */
function lBuildF2(array $entries): array
{
    $main = 'wpessential/wpessential.php';
    lAssert(isset($entries[$main]), 'Canonical Free main plugin entry missing');

    $value = $entries[$main];
    $value = lReplaceOne(
        $value,
        ' * Version: ' . P006L_FREE_BASE_VERSION,
        ' * Version: ' . P006L_FREE_BREAKING_VERSION,
        'F2 header version',
    );
    $value = lReplaceOne(
        $value,
        "define('WPE_VERSION', '" . P006L_FREE_BASE_VERSION . "');",
        "define('WPE_VERSION', '" . P006L_FREE_BREAKING_VERSION . "');",
        'F2 WPE_VERSION',
    );
    $value = lReplaceOne(
        $value,
        "define('WPE_PLATFORM_API_VERSION', '" . P006L_API_BASE . "');",
        "define('WPE_PLATFORM_API_VERSION', '" . P006L_API_BREAKING . "');",
        'F2 WPE_PLATFORM_API_VERSION',
    );

    $entries[$main] = $value;
    return $entries;
}

/** @param array<string,string> $entries @return array<string,string> */
function lBuildP0(array $entries): array
{
    $main = 'wpessential-pro/wpessential-pro.php';
    lAssert(isset($entries[$main]), 'Canonical Pro main plugin entry missing');

    $value = $entries[$main];
    $value = lReplaceOne(
        $value,
        "define('WPE_PRO_MAX_FREE_VERSION', '" . P006L_FREE_BASE_VERSION . "');",
        "define('WPE_PRO_MAX_FREE_VERSION', '" . P006L_FREE_OVERLAP_VERSION . "');",
        'P0 max Free overlap',
    );

    $entries[$main] = $value;
    return $entries;
}

/** @param array<string,string> $entries @return array<string,string> */
function lBuildP2(array $entries): array
{
    $main = 'wpessential-pro/wpessential-pro.php';
    lAssert(isset($entries[$main]), 'Canonical Pro main plugin entry missing');

    $value = $entries[$main];

    $value = lReplaceOne(
        $value,
        ' * Version: ' . P006L_PRO_BASE_VERSION,
        ' * Version: ' . P006L_PRO_BREAKING_VERSION,
        'P2 header version',
    );
    $value = lReplaceOne(
        $value,
        "define('WPE_PRO_VERSION', '" . P006L_PRO_BASE_VERSION . "');",
        "define('WPE_PRO_VERSION', '" . P006L_PRO_BREAKING_VERSION . "');",
        'P2 WPE_PRO_VERSION',
    );
    $value = lReplaceOne(
        $value,
        "define('WPE_PRO_MIN_FREE_VERSION', '" . P006L_FREE_BASE_VERSION . "');",
        "define('WPE_PRO_MIN_FREE_VERSION', '" . P006L_FREE_BREAKING_VERSION . "');",
        'P2 min Free',
    );
    $value = lReplaceOne(
        $value,
        "define('WPE_PRO_MAX_FREE_VERSION', '" . P006L_FREE_BASE_VERSION . "');",
        "define('WPE_PRO_MAX_FREE_VERSION', '" . P006L_FREE_BREAKING_VERSION . "');",
        'P2 max Free',
    );
    $value = lReplaceOne(
        $value,
        "define('WPE_PRO_MIN_PLATFORM_API_VERSION', '" . P006L_API_BASE . "');",
        "define('WPE_PRO_MIN_PLATFORM_API_VERSION', '" . P006L_API_BREAKING . "');",
        'P2 min Platform API',
    );
    $value = lReplaceOne(
        $value,
        "define('WPE_PRO_MAX_PLATFORM_API_VERSION', '" . P006L_API_BASE . "');",
        "define('WPE_PRO_MAX_PLATFORM_API_VERSION', '" . P006L_API_BREAKING . "');",
        'P2 max Platform API',
    );

    $entries[$main] = $value;
    return $entries;
}

/** @param array<string,string> $a @param array<string,string> $b @return list<string> */
function lChangedEntries(array $a, array $b): array
{
    lAssert(array_keys($a) === array_keys($b), 'Variant ZIP entry-set drift');

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
function lTreeDigest(array $entries): string
{
    ksort($entries, SORT_STRING);

    $ctx = hash_init('sha256');
    foreach ($entries as $name => $value) {
        hash_update($ctx, $name . "\0" . hash('sha256', $value) . "\n");
    }

    return hash_final($ctx);
}

function lRegex(string $pattern, string $value, string $label): string
{
    $matched = preg_match($pattern, $value, $m);
    lAssert($matched === 1 && isset($m[1]) && is_string($m[1]), "Unable to parse {$label}");

    return $m[1];
}

/** @return array<string,mixed> */
function lFreeMetadata(string $main): array
{
    $header = lRegex('/^ \* Version:\s*([^\s]+)\s*$/m', $main, 'Free plugin header version');
    $version = lRegex("/define\('WPE_VERSION',\s*'([^']+)'\);/", $main, 'WPE_VERSION');
    $api = lRegex("/define\('WPE_PLATFORM_API_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PLATFORM_API_VERSION');
    $schema = (int) lRegex("/define\('WPE_PLATFORM_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PLATFORM_SCHEMA_GENERATION');

    lAssert($header === $version, 'Free header/constant marketing-version mismatch');

    return [
        'version' => $version,
        'platform_api' => $api,
        'platform_schema' => $schema,
    ];
}

/** @return array<string,mixed> */
function lProMetadata(string $main): array
{
    $header = lRegex('/^ \* Version:\s*([^\s]+)\s*$/m', $main, 'Pro plugin header version');
    $version = lRegex("/define\('WPE_PRO_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_VERSION');
    $minFree = lRegex("/define\('WPE_PRO_MIN_FREE_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MIN_FREE_VERSION');
    $maxFree = lRegex("/define\('WPE_PRO_MAX_FREE_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MAX_FREE_VERSION');
    $minApi = lRegex("/define\('WPE_PRO_MIN_PLATFORM_API_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MIN_PLATFORM_API_VERSION');
    $maxApi = lRegex("/define\('WPE_PRO_MAX_PLATFORM_API_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MAX_PLATFORM_API_VERSION');
    $minSchema = (int) lRegex("/define\('WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION');
    $maxSchema = (int) lRegex("/define\('WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION');
    $schema = (int) lRegex("/define\('WPE_PRO_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PRO_SCHEMA_GENERATION');

    lAssert($header === $version, 'Pro header/constant marketing-version mismatch');

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
function lNode(string $logical, string $fileName, string $zipPath, array $entries, string $mainEntry, array $metadata): array
{
    lAssert(isset($entries[$mainEntry]), "{$logical}: main entry missing");

    return [
        'logical_node' => $logical,
        'artifact' => $fileName,
        'sha256' => lHashFile($zipPath),
        'payload_tree_sha256' => lTreeDigest($entries),
        'entry_count' => count($entries),
        'main_entry' => $mainEntry,
        'main_entry_sha256' => hash('sha256', $entries[$mainEntry]),
        'metadata' => $metadata,
    ];
}

/** @param array<string,mixed> $value */
function lWriteJson(string $path, array $value): void
{
    $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    lAssert(file_put_contents($path, $encoded . PHP_EOL) !== false, "Unable to write JSON: {$path}");
}

try {
    $sourceSha = lEnv('WPE_P006_SOURCE_SHA');
    lAssert(preg_match('/^[0-9a-f]{40}$/', $sourceSha) === 1, 'Invalid source SHA');

    $canonicalFree = lEnv('WPE_P006_CANONICAL_FREE_ZIP');
    $canonicalPro = lEnv('WPE_P006_CANONICAL_PRO_ZIP');
    $outputDir = rtrim(lEnv('WPE_P006_OUTPUT_DIR'), '/\\');

    if (!is_dir($outputDir)) {
        lAssert(mkdir($outputDir, 0775, true) || is_dir($outputDir), "Unable to create output directory: {$outputDir}");
    }

    $freeBaseEntries = lReadZipEntries($canonicalFree);
    $proBaseEntries = lReadZipEntries($canonicalPro);

    $freeMain = 'wpessential/wpessential.php';
    $proMain = 'wpessential-pro/wpessential-pro.php';

    lAssert(isset($freeBaseEntries[$freeMain]), 'Canonical Free plugin entry missing');
    lAssert(isset($proBaseEntries[$proMain]), 'Canonical Pro plugin entry missing');

    $canonicalFreeMetadata = lFreeMetadata($freeBaseEntries[$freeMain]);
    $canonicalProMetadata = lProMetadata($proBaseEntries[$proMain]);

    lAssert($canonicalFreeMetadata === [
        'version' => P006L_FREE_BASE_VERSION,
        'platform_api' => P006L_API_BASE,
        'platform_schema' => P006L_SCHEMA,
    ], 'Canonical Free metadata drifted outside Wave 1L authorization');

    lAssert($canonicalProMetadata === [
        'version' => P006L_PRO_BASE_VERSION,
        'min_free_version' => P006L_FREE_BASE_VERSION,
        'max_free_version' => P006L_FREE_BASE_VERSION,
        'min_platform_api' => P006L_API_BASE,
        'max_platform_api' => P006L_API_BASE,
        'min_platform_schema' => P006L_SCHEMA,
        'max_platform_schema' => P006L_SCHEMA,
        'schema' => P006L_SCHEMA,
    ], 'Canonical Pro metadata drifted outside Wave 1L authorization');

    $f1Entries = lBuildF1($freeBaseEntries);
    $f2Entries = lBuildF2($freeBaseEntries);
    $p0Entries = lBuildP0($proBaseEntries);
    $p2Entries = lBuildP2($proBaseEntries);

    lAssert(lChangedEntries($freeBaseEntries, $f1Entries) === [$freeMain], 'F1 changed entries outside Free plugin entry');
    lAssert(lChangedEntries($freeBaseEntries, $f2Entries) === [$freeMain], 'F2 changed entries outside Free plugin entry');
    lAssert(lChangedEntries($proBaseEntries, $p0Entries) === [$proMain], 'P0 changed entries outside Pro plugin entry');
    lAssert(lChangedEntries($proBaseEntries, $p2Entries) === [$proMain], 'P2 changed entries outside Pro plugin entry');

    $paths = [
        'F1' => $outputDir . '/f1.zip',
        'F2' => $outputDir . '/f2.zip',
        'P0' => $outputDir . '/p0.zip',
        'P2' => $outputDir . '/p2.zip',
    ];

    lWriteZip($paths['F1'], $f1Entries);
    lWriteZip($paths['F2'], $f2Entries);
    lWriteZip($paths['P0'], $p0Entries);
    lWriteZip($paths['P2'], $p2Entries);

    $f1Meta = lFreeMetadata($f1Entries[$freeMain]);
    $f2Meta = lFreeMetadata($f2Entries[$freeMain]);
    $p0Meta = lProMetadata($p0Entries[$proMain]);
    $p2Meta = lProMetadata($p2Entries[$proMain]);

    lAssert($f1Meta === [
        'version' => P006L_FREE_OVERLAP_VERSION,
        'platform_api' => P006L_API_BASE,
        'platform_schema' => P006L_SCHEMA,
    ], 'F1 metadata mismatch');

    lAssert($f2Meta === [
        'version' => P006L_FREE_BREAKING_VERSION,
        'platform_api' => P006L_API_BREAKING,
        'platform_schema' => P006L_SCHEMA,
    ], 'F2 metadata mismatch');

    lAssert($p0Meta === [
        'version' => P006L_PRO_BASE_VERSION,
        'min_free_version' => P006L_FREE_BASE_VERSION,
        'max_free_version' => P006L_FREE_OVERLAP_VERSION,
        'min_platform_api' => P006L_API_BASE,
        'max_platform_api' => P006L_API_BASE,
        'min_platform_schema' => P006L_SCHEMA,
        'max_platform_schema' => P006L_SCHEMA,
        'schema' => P006L_SCHEMA,
    ], 'P0 metadata mismatch');

    lAssert($p2Meta === [
        'version' => P006L_PRO_BREAKING_VERSION,
        'min_free_version' => P006L_FREE_BREAKING_VERSION,
        'max_free_version' => P006L_FREE_BREAKING_VERSION,
        'min_platform_api' => P006L_API_BREAKING,
        'max_platform_api' => P006L_API_BREAKING,
        'min_platform_schema' => P006L_SCHEMA,
        'max_platform_schema' => P006L_SCHEMA,
        'schema' => P006L_SCHEMA,
    ], 'P2 metadata mismatch');

    $nodes = [
        'F1' => lNode('F1', 'f1.zip', $paths['F1'], $f1Entries, $freeMain, $f1Meta),
        'F2' => lNode('F2', 'f2.zip', $paths['F2'], $f2Entries, $freeMain, $f2Meta),
        'P0' => lNode('P0', 'p0.zip', $paths['P0'], $p0Entries, $proMain, $p0Meta),
        'P2' => lNode('P2', 'p2.zip', $paths['P2'], $p2Entries, $proMain, $p2Meta),
    ];

    $identity = [
        'protocol' => 'P-006',
        'wave' => '1L-lane-b3a-complete-breaking-order',
        'source_sha' => $sourceSha,
        'temporary_approval_id' => P006L_GRANT,
        'classification' => 'NON-RELEASE / TEST-ONLY EVIDENCE',
        'nodes' => $nodes,
        'pairs' => [
            'F1_P0' => [
                'free' => 'F1',
                'pro' => 'P0',
                'expected_state' => 'compatible',
                'pair_id_sha256' => lPair($nodes['F1']['sha256'], $nodes['P0']['sha256']),
            ],
            'F2_P0' => [
                'free' => 'F2',
                'pro' => 'P0',
                'expected_state' => 'free_version_too_new',
                'pair_id_sha256' => lPair($nodes['F2']['sha256'], $nodes['P0']['sha256']),
            ],
            'F1_P2' => [
                'free' => 'F1',
                'pro' => 'P2',
                'expected_state' => 'free_version_too_old',
                'pair_id_sha256' => lPair($nodes['F1']['sha256'], $nodes['P2']['sha256']),
            ],
        ],
        'variant_boundary' => [
            'F1_changed_entries' => [$freeMain],
            'F2_changed_entries' => [$freeMain],
            'P0_changed_entries' => [$proMain],
            'P2_changed_entries' => [$proMain],
            'tracked_product_source_modified' => false,
            'canonical_release_artifacts_replaced' => false,
        ],
        'authorized_fixtures' => ['FP-47', 'FP-48'],
        'authorized_runtime_cells' => [
            'minimum' => ['wordpress' => '6.9', 'php' => '8.2', 'mysql' => '8.4'],
            'reference' => ['wordpress' => '7.1', 'php' => '8.5', 'mysql' => '8.4'],
        ],
        'certification_boundary' => [
            'permanent_p001_cf_certified' => false,
            'free_pro_pair_certified' => false,
            'runtime_certified' => false,
            'manual_upload_certified' => false,
            'updater_or_tuf_certified' => false,
            'interrupted_replacement_certified' => false,
            'rollback_certified' => false,
            'migration_certified' => false,
            'adr_0010' => 'Proposed',
            'ga_or_release_authorized' => false,
        ],
    ];

    lWriteJson($outputDir . '/candidate-identity.json', $identity);
    fwrite(STDOUT, json_encode($identity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 Wave 1L artifact builder] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
