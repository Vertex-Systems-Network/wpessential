<?php

declare(strict_types=1);

const P006B6A_AUTH = 'GOV-P006-B6A-FP89-PLATFORM-API-CANDIDATE-001';
const P006B6A_MTIME = 946684800;
const P006B6A_FREE_VERSION = '0.1.0-dev';
const P006B6A_PRO_VERSION = '0.1.0-dev';
const P006B6A_API_CURRENT = '0.1.0';
const P006B6A_API_REQUIRED = '0.2.0';
const P006B6A_SCHEMA = 1;

function b6aFail(string $message): never
{
    throw new RuntimeException($message);
}

function b6aAssert(bool $condition, string $message): void
{
    if (!$condition) {
        b6aFail($message);
    }
}

function b6aEnv(string $key): string
{
    $value = trim((string) getenv($key));
    if ($value === '') {
        b6aFail("Missing env: {$key}");
    }
    return $value;
}

function b6aHashFile(string $path): string
{
    b6aAssert(is_file($path) && filesize($path) > 0, "Missing artifact: {$path}");
    $hash = hash_file('sha256', $path);
    b6aAssert(is_string($hash), "Unable to hash: {$path}");
    return $hash;
}

function b6aPair(string $freeHash, string $proHash): string
{
    return hash('sha256', $freeHash . ':' . $proHash);
}

/** @return array<string,string> */
function b6aReadZip(string $path): array
{
    b6aAssert(class_exists(ZipArchive::class), 'ZipArchive unavailable');
    $zip = new ZipArchive();
    b6aAssert($zip->open($path) === true, "Unable to open ZIP: {$path}");

    $entries = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        b6aAssert(is_array($stat) && isset($stat['name']) && is_string($stat['name']), 'Unable to inspect ZIP entry');
        $name = $stat['name'];
        b6aAssert($name !== '' && !str_contains($name, '../') && !str_starts_with($name, '/'), "Unsafe ZIP entry: {$name}");
        if (str_ends_with($name, '/')) {
            continue;
        }
        $value = $zip->getFromIndex($i);
        b6aAssert(is_string($value), "Unable to read ZIP entry: {$name}");
        b6aAssert(!isset($entries[$name]), "Duplicate ZIP entry: {$name}");
        $entries[$name] = $value;
    }
    $zip->close();
    ksort($entries, SORT_STRING);
    b6aAssert($entries !== [], "ZIP contains no files: {$path}");
    return $entries;
}

/** @param array<string,string> $entries */
function b6aWriteZip(string $path, array $entries): void
{
    if (!is_dir(dirname($path))) {
        b6aAssert(mkdir(dirname($path), 0775, true) || is_dir(dirname($path)), 'Unable to create output directory');
    }
    @unlink($path);
    $zip = new ZipArchive();
    b6aAssert($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, "Unable to create ZIP: {$path}");
    ksort($entries, SORT_STRING);
    foreach ($entries as $name => $value) {
        b6aAssert($zip->addFromString($name, $value), "Unable to add ZIP entry: {$name}");
        b6aAssert($zip->setMtimeName($name, P006B6A_MTIME), "Unable to normalize mtime: {$name}");
        b6aAssert($zip->setExternalAttributesName($name, ZipArchive::OPSYS_UNIX, 0100644 << 16), "Unable to normalize permissions: {$name}");
        b6aAssert($zip->setCompressionName($name, ZipArchive::CM_DEFLATE, 9), "Unable to normalize compression: {$name}");
    }
    b6aAssert($zip->close(), "Unable to finalize ZIP: {$path}");
}

function b6aReplaceOne(string $value, string $from, string $to, string $label): string
{
    $count = substr_count($value, $from);
    b6aAssert($count === 1, "{$label}: expected one replacement anchor, found {$count}");
    return str_replace($from, $to, $value);
}

/** @param array<string,string> $entries @return array<string,string> */
function b6aBuildPApi(array $entries): array
{
    $main = 'wpessential-pro/wpessential-pro.php';
    b6aAssert(isset($entries[$main]), 'Canonical Pro main entry missing');
    $value = $entries[$main];
    $value = b6aReplaceOne(
        $value,
        "define('WPE_PRO_MIN_PLATFORM_API_VERSION', '" . P006B6A_API_CURRENT . "');",
        "define('WPE_PRO_MIN_PLATFORM_API_VERSION', '" . P006B6A_API_REQUIRED . "');",
        'P-API minimum Platform API',
    );
    $value = b6aReplaceOne(
        $value,
        "define('WPE_PRO_MAX_PLATFORM_API_VERSION', '" . P006B6A_API_CURRENT . "');",
        "define('WPE_PRO_MAX_PLATFORM_API_VERSION', '" . P006B6A_API_REQUIRED . "');",
        'P-API maximum Platform API',
    );
    $entries[$main] = $value;
    return $entries;
}

/** @param array<string,string> $a @param array<string,string> $b @return list<string> */
function b6aChangedEntries(array $a, array $b): array
{
    b6aAssert(array_keys($a) === array_keys($b), 'Variant ZIP entry-set drift');
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
function b6aTreeDigest(array $entries): string
{
    ksort($entries, SORT_STRING);
    $ctx = hash_init('sha256');
    foreach ($entries as $name => $value) {
        hash_update($ctx, $name . "\0" . hash('sha256', $value) . "\n");
    }
    return hash_final($ctx);
}

function b6aRegex(string $pattern, string $value, string $label): string
{
    $matched = preg_match($pattern, $value, $m);
    b6aAssert($matched === 1 && isset($m[1]) && is_string($m[1]), "Unable to parse {$label}");
    return $m[1];
}

/** @return array<string,mixed> */
function b6aFreeMetadata(string $main): array
{
    $header = b6aRegex('/^ \* Version:\s*([^\s]+)\s*$/m', $main, 'Free header version');
    $version = b6aRegex("/define\('WPE_VERSION',\s*'([^']+)'\);/", $main, 'WPE_VERSION');
    $api = b6aRegex("/define\('WPE_PLATFORM_API_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PLATFORM_API_VERSION');
    $schema = (int) b6aRegex("/define\('WPE_PLATFORM_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PLATFORM_SCHEMA_GENERATION');
    b6aAssert($header === $version, 'Free header/version mismatch');
    return ['version' => $version, 'platform_api' => $api, 'platform_schema' => $schema];
}

/** @return array<string,mixed> */
function b6aProMetadata(string $main): array
{
    $header = b6aRegex('/^ \* Version:\s*([^\s]+)\s*$/m', $main, 'Pro header version');
    $version = b6aRegex("/define\('WPE_PRO_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_VERSION');
    $minFree = b6aRegex("/define\('WPE_PRO_MIN_FREE_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MIN_FREE_VERSION');
    $maxFree = b6aRegex("/define\('WPE_PRO_MAX_FREE_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MAX_FREE_VERSION');
    $minApi = b6aRegex("/define\('WPE_PRO_MIN_PLATFORM_API_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MIN_PLATFORM_API_VERSION');
    $maxApi = b6aRegex("/define\('WPE_PRO_MAX_PLATFORM_API_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MAX_PLATFORM_API_VERSION');
    $minSchema = (int) b6aRegex("/define\('WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION');
    $maxSchema = (int) b6aRegex("/define\('WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION');
    $schema = (int) b6aRegex("/define\('WPE_PRO_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PRO_SCHEMA_GENERATION');
    b6aAssert($header === $version, 'Pro header/version mismatch');
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
function b6aNode(string $logical, string $artifact, string $path, array $entries, string $main, array $metadata): array
{
    b6aAssert(isset($entries[$main]), "{$logical}: main entry missing");
    return [
        'logical_node' => $logical,
        'artifact' => $artifact,
        'sha256' => b6aHashFile($path),
        'payload_tree_sha256' => b6aTreeDigest($entries),
        'entry_count' => count($entries),
        'main_entry' => $main,
        'main_entry_sha256' => hash('sha256', $entries[$main]),
        'metadata' => $metadata,
    ];
}

/** @param array<string,mixed> $value */
function b6aWriteJson(string $path, array $value): void
{
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    b6aAssert(file_put_contents($path, $json . PHP_EOL) !== false, "Unable to write JSON: {$path}");
}

try {
    $sourceSha = b6aEnv('WPE_P006_SOURCE_SHA');
    b6aAssert(preg_match('/^[0-9a-f]{40}$/', $sourceSha) === 1, 'Invalid source SHA');
    $canonicalFree = b6aEnv('WPE_P006_CANONICAL_FREE_ZIP');
    $canonicalPro = b6aEnv('WPE_P006_CANONICAL_PRO_ZIP');
    $outputDir = rtrim(b6aEnv('WPE_P006_OUTPUT_DIR'), '/\\');

    if (!is_dir($outputDir)) {
        b6aAssert(mkdir($outputDir, 0775, true) || is_dir($outputDir), 'Unable to create output directory');
    }

    $freeEntries = b6aReadZip($canonicalFree);
    $proEntries = b6aReadZip($canonicalPro);
    $pApiEntries = b6aBuildPApi($proEntries);
    $freeMain = 'wpessential/wpessential.php';
    $proMain = 'wpessential-pro/wpessential-pro.php';

    b6aAssert(b6aChangedEntries($proEntries, $pApiEntries) === [$proMain], 'P-API changed entries outside Pro main entry');

    $freeMeta = b6aFreeMetadata($freeEntries[$freeMain] ?? '');
    $proMeta = b6aProMetadata($proEntries[$proMain] ?? '');
    $pApiMeta = b6aProMetadata($pApiEntries[$proMain] ?? '');

    $expectedFree = ['version' => P006B6A_FREE_VERSION, 'platform_api' => P006B6A_API_CURRENT, 'platform_schema' => P006B6A_SCHEMA];
    $expectedP0 = [
        'version' => P006B6A_PRO_VERSION,
        'min_free_version' => P006B6A_FREE_VERSION,
        'max_free_version' => P006B6A_FREE_VERSION,
        'min_platform_api' => P006B6A_API_CURRENT,
        'max_platform_api' => P006B6A_API_CURRENT,
        'min_platform_schema' => P006B6A_SCHEMA,
        'max_platform_schema' => P006B6A_SCHEMA,
        'schema' => P006B6A_SCHEMA,
    ];
    $expectedPApi = $expectedP0;
    $expectedPApi['min_platform_api'] = P006B6A_API_REQUIRED;
    $expectedPApi['max_platform_api'] = P006B6A_API_REQUIRED;

    b6aAssert($freeMeta === $expectedFree, 'Canonical Free metadata drift');
    b6aAssert($proMeta === $expectedP0, 'Canonical Pro metadata drift');
    b6aAssert($pApiMeta === $expectedPApi, 'P-API metadata drift');

    $paths = [
        'F0' => $outputDir . '/f0.zip',
        'P0' => $outputDir . '/p0.zip',
        'P_API' => $outputDir . '/p-api.zip',
    ];
    b6aAssert(copy($canonicalFree, $paths['F0']), 'Unable to copy canonical Free');
    b6aAssert(copy($canonicalPro, $paths['P0']), 'Unable to copy canonical Pro');
    b6aAssert(hash_equals(b6aHashFile($canonicalFree), b6aHashFile($paths['F0'])), 'F0 not byte-identical to canonical Free');
    b6aAssert(hash_equals(b6aHashFile($canonicalPro), b6aHashFile($paths['P0'])), 'P0 not byte-identical to canonical Pro');
    b6aWriteZip($paths['P_API'], $pApiEntries);

    if (!defined('ABSPATH')) {
        define('ABSPATH', dirname(__DIR__, 2) . '/');
    }
    require_once dirname(__DIR__, 2) . '/frameworks/Modules/Compatibility/LocalCompatibilityPreflight.php';

    $freeInput = [
        'present' => true,
        'bootstrap_complete' => true,
        'version' => $freeMeta['version'],
        'platform_api' => $freeMeta['platform_api'],
        'platform_schema' => $freeMeta['platform_schema'],
    ];
    $p0Input = ['package_complete' => true] + $proMeta;
    $pApiInput = ['package_complete' => true] + $pApiMeta;

    $control = \WPEssential\Modules\Compatibility\LocalCompatibilityPreflight::evaluate($freeInput, $p0Input);
    $mismatch = \WPEssential\Modules\Compatibility\LocalCompatibilityPreflight::evaluate($freeInput, $pApiInput);

    b6aAssert(($control['state'] ?? null) === 'compatible', 'Canonical F0/P0 control no longer compatible');
    b6aAssert(($control['premium_migrations_allowed'] ?? null) === true, 'Canonical F0/P0 control denies premium migrations');
    b6aAssert(($mismatch['state'] ?? null) === 'platform_api_too_old', 'P-API preflight state drift');
    b6aAssert(($mismatch['dimension'] ?? null) === 'platform_api', 'P-API preflight dimension drift');
    b6aAssert(($mismatch['premium_boot_allowed'] ?? null) === false, 'P-API unexpectedly allows premium boot');
    b6aAssert(($mismatch['premium_migrations_allowed'] ?? null) === false, 'P-API unexpectedly allows premium migrations');

    $nodes = [
        'F0' => b6aNode('F0', 'f0.zip', $paths['F0'], $freeEntries, $freeMain, $freeMeta),
        'P0' => b6aNode('P0', 'p0.zip', $paths['P0'], $proEntries, $proMain, $proMeta),
        'P_API' => b6aNode('P_API', 'p-api.zip', $paths['P_API'], $pApiEntries, $proMain, $pApiMeta),
    ];

    $identity = [
        'protocol' => 'P-006',
        'prerequisite' => 'B6a-FP89-platform-api-candidate',
        'source_sha' => $sourceSha,
        'authorization' => P006B6A_AUTH,
        'classification' => 'TEST-ONLY PREREQUISITE / FP-89 NOT FORMALLY EXECUTED / NON-CERTIFYING',
        'nodes' => $nodes,
        'pairs' => [
            'F0_P0' => [
                'free' => 'F0',
                'pro' => 'P0',
                'pair_id_sha256' => b6aPair($nodes['F0']['sha256'], $nodes['P0']['sha256']),
                'preflight' => $control,
            ],
            'F0_P_API' => [
                'free' => 'F0',
                'pro' => 'P_API',
                'pair_id_sha256' => b6aPair($nodes['F0']['sha256'], $nodes['P_API']['sha256']),
                'preflight' => $mismatch,
            ],
        ],
        'variant_boundary' => [
            'P_API_changed_entries' => [$proMain],
            'changed_constants' => ['WPE_PRO_MIN_PLATFORM_API_VERSION', 'WPE_PRO_MAX_PLATFORM_API_VERSION'],
            'canonical_free_byte_identical' => true,
            'canonical_pro_control_byte_identical' => true,
            'tracked_product_source_modified' => false,
        ],
        'formal_fixture_executed' => false,
        'accounting' => ['documented' => 144, 'executed' => 57, 'pass' => 57, 'fail' => 0, 'inconclusive' => 0],
        'boundaries' => [
            'wordpress_or_mysql_runtime_used' => false,
            'migration_or_db_mutation' => false,
            'runtime_grant_created' => false,
            'pair_certified' => false,
            'runtime_certified' => false,
            'migration_certified' => false,
            'permanent_p001_cf_certified' => false,
            'adr_0010' => 'Proposed',
            'ga_or_release_authorized' => false,
        ],
    ];

    b6aWriteJson($outputDir . '/candidate-identity.json', $identity);
    fwrite(STDOUT, json_encode($identity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 B6a FP-89 candidate] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
