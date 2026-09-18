<?php

declare(strict_types=1);

const P006M_GRANT = 'GOV-P001-CF-TEMP-011';
const P006M_MTIME = 946684800;
const P006M_FREE_BASE_VERSION = '0.1.0-dev';
const P006M_FREE_OVERLAP_VERSION = '0.1.1-test-overlap';
const P006M_FREE_BREAKING_VERSION = '0.2.0-test-breaking';
const P006M_PRO_BASE_VERSION = '0.1.0-dev';
const P006M_PRO_OVERLAP_VERSION = '0.1.1-test-overlap';
const P006M_PRO_BREAKING_VERSION = '0.2.0-test-breaking';
const P006M_API_BASE = '0.1.0';
const P006M_API_BREAKING = '0.2.0';
const P006M_SCHEMA = 1;

function mFail(string $message): never
{
    throw new RuntimeException($message);
}

function mAssert(bool $condition, string $message): void
{
    if (!$condition) {
        mFail($message);
    }
}

function mEnv(string $key): string
{
    $value = trim((string) getenv($key));
    if ($value === '') {
        mFail("Missing env: {$key}");
    }
    return $value;
}

function mHashFile(string $path): string
{
    mAssert(is_file($path) && filesize($path) > 0, "Missing artifact: {$path}");
    $hash = hash_file('sha256', $path);
    mAssert(is_string($hash), "Unable to hash artifact: {$path}");
    return $hash;
}

function mPair(string $freeHash, string $proHash): string
{
    return hash('sha256', $freeHash . ':' . $proHash);
}

/** @return array<string,string> */
function mReadZipEntries(string $path): array
{
    mAssert(class_exists(ZipArchive::class), 'ZipArchive unavailable');
    $zip = new ZipArchive();
    mAssert($zip->open($path) === true, "Unable to open ZIP: {$path}");

    $entries = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        mAssert(is_array($stat) && isset($stat['name']) && is_string($stat['name']), 'Unable to inspect ZIP entry');
        $name = $stat['name'];
        mAssert($name !== '' && !str_contains($name, '../') && !str_starts_with($name, '/'), "Unsafe ZIP entry: {$name}");
        if (str_ends_with($name, '/')) {
            continue;
        }
        $value = $zip->getFromIndex($i);
        mAssert(is_string($value), "Unable to read ZIP entry: {$name}");
        mAssert(!array_key_exists($name, $entries), "Duplicate ZIP entry: {$name}");
        $entries[$name] = $value;
    }
    $zip->close();

    ksort($entries, SORT_STRING);
    mAssert($entries !== [], "ZIP has no file entries: {$path}");
    return $entries;
}

/** @param array<string,string> $entries */
function mWriteZip(string $path, array $entries): void
{
    if (!is_dir(dirname($path))) {
        mAssert(mkdir(dirname($path), 0775, true) || is_dir(dirname($path)), "Unable to create output directory");
    }

    @unlink($path);
    $zip = new ZipArchive();
    mAssert($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, "Unable to create ZIP: {$path}");

    ksort($entries, SORT_STRING);
    foreach ($entries as $name => $value) {
        mAssert($zip->addFromString($name, $value), "Unable to add ZIP entry: {$name}");
        mAssert($zip->setMtimeName($name, P006M_MTIME), "Unable to normalize ZIP mtime: {$name}");
        mAssert(
            $zip->setExternalAttributesName($name, ZipArchive::OPSYS_UNIX, 0100644 << 16),
            "Unable to normalize ZIP permissions: {$name}",
        );
        mAssert($zip->setCompressionName($name, ZipArchive::CM_DEFLATE, 9), "Unable to normalize compression: {$name}");
    }

    mAssert($zip->close(), "Unable to close ZIP: {$path}");
    mAssert(is_file($path) && filesize($path) > 0, "ZIP write produced no artifact: {$path}");
}

function mReplaceOne(string $value, string $from, string $to, string $label): string
{
    $count = substr_count($value, $from);
    mAssert($count === 1, "{$label}: expected exactly one replacement anchor, found {$count}");
    return str_replace($from, $to, $value);
}

/** @param array<string,string> $entries @return array<string,string> */
function mBuildF1(array $entries): array
{
    $main = 'wpessential/wpessential.php';
    $value = $entries[$main] ?? null;
    mAssert(is_string($value), 'Canonical Free main entry missing');
    $value = mReplaceOne($value, ' * Version: ' . P006M_FREE_BASE_VERSION, ' * Version: ' . P006M_FREE_OVERLAP_VERSION, 'F1 header');
    $value = mReplaceOne($value, "define('WPE_VERSION', '" . P006M_FREE_BASE_VERSION . "');", "define('WPE_VERSION', '" . P006M_FREE_OVERLAP_VERSION . "');", 'F1 version constant');
    $entries[$main] = $value;
    return $entries;
}

/** @param array<string,string> $entries @return array<string,string> */
function mBuildF2(array $entries): array
{
    $main = 'wpessential/wpessential.php';
    $value = $entries[$main] ?? null;
    mAssert(is_string($value), 'Canonical Free main entry missing');
    $value = mReplaceOne($value, ' * Version: ' . P006M_FREE_BASE_VERSION, ' * Version: ' . P006M_FREE_BREAKING_VERSION, 'F2 header');
    $value = mReplaceOne($value, "define('WPE_VERSION', '" . P006M_FREE_BASE_VERSION . "');", "define('WPE_VERSION', '" . P006M_FREE_BREAKING_VERSION . "');", 'F2 version constant');
    $value = mReplaceOne($value, "define('WPE_PLATFORM_API_VERSION', '" . P006M_API_BASE . "');", "define('WPE_PLATFORM_API_VERSION', '" . P006M_API_BREAKING . "');", 'F2 API constant');
    $entries[$main] = $value;
    return $entries;
}

/** @param array<string,string> $entries @return array<string,string> */
function mBuildP0(array $entries): array
{
    $main = 'wpessential-pro/wpessential-pro.php';
    $value = $entries[$main] ?? null;
    mAssert(is_string($value), 'Canonical Pro main entry missing');
    $value = mReplaceOne($value, "define('WPE_PRO_MAX_FREE_VERSION', '" . P006M_FREE_BASE_VERSION . "');", "define('WPE_PRO_MAX_FREE_VERSION', '" . P006M_FREE_OVERLAP_VERSION . "');", 'P0 max Free overlap');
    $entries[$main] = $value;
    return $entries;
}

/** @param array<string,string> $entries @return array<string,string> */
function mBuildP1(array $entries): array
{
    $main = 'wpessential-pro/wpessential-pro.php';
    $value = $entries[$main] ?? null;
    mAssert(is_string($value), 'Canonical Pro main entry missing');
    $value = mReplaceOne($value, ' * Version: ' . P006M_PRO_BASE_VERSION, ' * Version: ' . P006M_PRO_OVERLAP_VERSION, 'P1 header');
    $value = mReplaceOne($value, "define('WPE_PRO_VERSION', '" . P006M_PRO_BASE_VERSION . "');", "define('WPE_PRO_VERSION', '" . P006M_PRO_OVERLAP_VERSION . "');", 'P1 version constant');
    $value = mReplaceOne($value, "define('WPE_PRO_MAX_FREE_VERSION', '" . P006M_FREE_BASE_VERSION . "');", "define('WPE_PRO_MAX_FREE_VERSION', '" . P006M_FREE_OVERLAP_VERSION . "');", 'P1 max Free overlap');
    $entries[$main] = $value;
    return $entries;
}

/** @param array<string,string> $entries @return array<string,string> */
function mBuildP2(array $entries): array
{
    $main = 'wpessential-pro/wpessential-pro.php';
    $value = $entries[$main] ?? null;
    mAssert(is_string($value), 'Canonical Pro main entry missing');
    $value = mReplaceOne($value, ' * Version: ' . P006M_PRO_BASE_VERSION, ' * Version: ' . P006M_PRO_BREAKING_VERSION, 'P2 header');
    $value = mReplaceOne($value, "define('WPE_PRO_VERSION', '" . P006M_PRO_BASE_VERSION . "');", "define('WPE_PRO_VERSION', '" . P006M_PRO_BREAKING_VERSION . "');", 'P2 version constant');
    $value = mReplaceOne($value, "define('WPE_PRO_MIN_FREE_VERSION', '" . P006M_FREE_BASE_VERSION . "');", "define('WPE_PRO_MIN_FREE_VERSION', '" . P006M_FREE_BREAKING_VERSION . "');", 'P2 min Free');
    $value = mReplaceOne($value, "define('WPE_PRO_MAX_FREE_VERSION', '" . P006M_FREE_BASE_VERSION . "');", "define('WPE_PRO_MAX_FREE_VERSION', '" . P006M_FREE_BREAKING_VERSION . "');", 'P2 max Free');
    $value = mReplaceOne($value, "define('WPE_PRO_MIN_PLATFORM_API_VERSION', '" . P006M_API_BASE . "');", "define('WPE_PRO_MIN_PLATFORM_API_VERSION', '" . P006M_API_BREAKING . "');", 'P2 min API');
    $value = mReplaceOne($value, "define('WPE_PRO_MAX_PLATFORM_API_VERSION', '" . P006M_API_BASE . "');", "define('WPE_PRO_MAX_PLATFORM_API_VERSION', '" . P006M_API_BREAKING . "');", 'P2 max API');
    $entries[$main] = $value;
    return $entries;
}

/** @param array<string,string> $left @param array<string,string> $right @return list<string> */
function mChangedEntries(array $left, array $right): array
{
    $names = array_unique(array_merge(array_keys($left), array_keys($right)));
    sort($names, SORT_STRING);
    $changed = [];
    foreach ($names as $name) {
        if (($left[$name] ?? null) !== ($right[$name] ?? null)) {
            $changed[] = $name;
        }
    }
    return $changed;
}

/** @param array<string,string> $entries */
function mTreeDigest(array $entries): string
{
    ksort($entries, SORT_STRING);
    $ctx = hash_init('sha256');
    foreach ($entries as $name => $value) {
        hash_update($ctx, $name . "\0" . hash('sha256', $value) . "\n");
    }
    return hash_final($ctx);
}

function mRegex(string $pattern, string $value, string $label): string
{
    $matched = preg_match($pattern, $value, $m);
    mAssert($matched === 1 && isset($m[1]) && is_string($m[1]), "Unable to parse {$label}");
    return $m[1];
}

/** @return array<string,mixed> */
function mFreeMetadata(string $main): array
{
    $header = mRegex('/^ \* Version:\s*([^\s]+)\s*$/m', $main, 'Free header version');
    $version = mRegex("/define\('WPE_VERSION',\s*'([^']+)'\);/", $main, 'WPE_VERSION');
    $api = mRegex("/define\('WPE_PLATFORM_API_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PLATFORM_API_VERSION');
    $schema = (int) mRegex("/define\('WPE_PLATFORM_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PLATFORM_SCHEMA_GENERATION');
    mAssert($header === $version, 'Free header/constant version mismatch');
    return ['version' => $version, 'platform_api' => $api, 'platform_schema' => $schema];
}

/** @return array<string,mixed> */
function mProMetadata(string $main): array
{
    $header = mRegex('/^ \* Version:\s*([^\s]+)\s*$/m', $main, 'Pro header version');
    $version = mRegex("/define\('WPE_PRO_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_VERSION');
    $minFree = mRegex("/define\('WPE_PRO_MIN_FREE_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MIN_FREE_VERSION');
    $maxFree = mRegex("/define\('WPE_PRO_MAX_FREE_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MAX_FREE_VERSION');
    $minApi = mRegex("/define\('WPE_PRO_MIN_PLATFORM_API_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MIN_PLATFORM_API_VERSION');
    $maxApi = mRegex("/define\('WPE_PRO_MAX_PLATFORM_API_VERSION',\s*'([^']+)'\);/", $main, 'WPE_PRO_MAX_PLATFORM_API_VERSION');
    $minSchema = (int) mRegex("/define\('WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION');
    $maxSchema = (int) mRegex("/define\('WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION');
    $schema = (int) mRegex("/define\('WPE_PRO_SCHEMA_GENERATION',\s*([0-9]+)\);/", $main, 'WPE_PRO_SCHEMA_GENERATION');
    mAssert($header === $version, 'Pro header/constant version mismatch');
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
function mNode(string $id, string $file, string $zip, array $entries, string $main, array $metadata): array
{
    return [
        'logical_node' => $id,
        'artifact' => $file,
        'sha256' => mHashFile($zip),
        'payload_tree_sha256' => mTreeDigest($entries),
        'entry_count' => count($entries),
        'main_entry' => $main,
        'main_entry_sha256' => hash('sha256', $entries[$main]),
        'metadata' => $metadata,
    ];
}

/** @param array<string,mixed> $value */
function mWriteJson(string $path, array $value): void
{
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    mAssert(file_put_contents($path, $json . PHP_EOL) !== false, "Unable to write JSON: {$path}");
}

try {
    $sourceSha = mEnv('WPE_P006_SOURCE_SHA');
    mAssert(preg_match('/^[0-9a-f]{40}$/', $sourceSha) === 1, 'Invalid source SHA');

    $canonicalFree = mEnv('WPE_P006_CANONICAL_FREE_ZIP');
    $canonicalPro = mEnv('WPE_P006_CANONICAL_PRO_ZIP');
    $outputDir = rtrim(mEnv('WPE_P006_OUTPUT_DIR'), '/\\');
    if (!is_dir($outputDir)) {
        mAssert(mkdir($outputDir, 0775, true) || is_dir($outputDir), 'Unable to create output directory');
    }

    $freeBase = mReadZipEntries($canonicalFree);
    $proBase = mReadZipEntries($canonicalPro);
    $freeMain = 'wpessential/wpessential.php';
    $proMain = 'wpessential-pro/wpessential-pro.php';

    mAssert(mFreeMetadata($freeBase[$freeMain]) === [
        'version' => P006M_FREE_BASE_VERSION,
        'platform_api' => P006M_API_BASE,
        'platform_schema' => P006M_SCHEMA,
    ], 'Canonical Free metadata drift');

    mAssert(mProMetadata($proBase[$proMain]) === [
        'version' => P006M_PRO_BASE_VERSION,
        'min_free_version' => P006M_FREE_BASE_VERSION,
        'max_free_version' => P006M_FREE_BASE_VERSION,
        'min_platform_api' => P006M_API_BASE,
        'max_platform_api' => P006M_API_BASE,
        'min_platform_schema' => P006M_SCHEMA,
        'max_platform_schema' => P006M_SCHEMA,
        'schema' => P006M_SCHEMA,
    ], 'Canonical Pro metadata drift');

    $entries = [
        'F0' => $freeBase,
        'F1' => mBuildF1($freeBase),
        'F2' => mBuildF2($freeBase),
        'P0' => mBuildP0($proBase),
        'P1' => mBuildP1($proBase),
        'P2' => mBuildP2($proBase),
    ];

    mAssert(mChangedEntries($freeBase, $entries['F1']) === [$freeMain], 'F1 changed unexpected entries');
    mAssert(mChangedEntries($freeBase, $entries['F2']) === [$freeMain], 'F2 changed unexpected entries');
    mAssert(mChangedEntries($proBase, $entries['P0']) === [$proMain], 'P0 changed unexpected entries');
    mAssert(mChangedEntries($proBase, $entries['P1']) === [$proMain], 'P1 changed unexpected entries');
    mAssert(mChangedEntries($proBase, $entries['P2']) === [$proMain], 'P2 changed unexpected entries');

    $paths = [];
    foreach (['F0','F1','F2','P0','P1','P2'] as $id) {
        $paths[$id] = $outputDir . '/' . strtolower($id) . '.zip';
    }

    @unlink($paths['F0']);
    mAssert(copy($canonicalFree, $paths['F0']), 'Unable to copy canonical Free as F0');
    mAssert(hash_equals(mHashFile($canonicalFree), mHashFile($paths['F0'])), 'F0 not byte-identical to canonical Free');
    mWriteZip($paths['F1'], $entries['F1']);
    mWriteZip($paths['F2'], $entries['F2']);
    mWriteZip($paths['P0'], $entries['P0']);
    mWriteZip($paths['P1'], $entries['P1']);
    mWriteZip($paths['P2'], $entries['P2']);

    $metadata = [
        'F0' => mFreeMetadata($entries['F0'][$freeMain]),
        'F1' => mFreeMetadata($entries['F1'][$freeMain]),
        'F2' => mFreeMetadata($entries['F2'][$freeMain]),
        'P0' => mProMetadata($entries['P0'][$proMain]),
        'P1' => mProMetadata($entries['P1'][$proMain]),
        'P2' => mProMetadata($entries['P2'][$proMain]),
    ];

    mAssert($metadata['F1'] === ['version'=>P006M_FREE_OVERLAP_VERSION,'platform_api'=>P006M_API_BASE,'platform_schema'=>P006M_SCHEMA], 'F1 metadata mismatch');
    mAssert($metadata['F2'] === ['version'=>P006M_FREE_BREAKING_VERSION,'platform_api'=>P006M_API_BREAKING,'platform_schema'=>P006M_SCHEMA], 'F2 metadata mismatch');

    $expectedOverlapPro = [
        'min_free_version'=>P006M_FREE_BASE_VERSION,
        'max_free_version'=>P006M_FREE_OVERLAP_VERSION,
        'min_platform_api'=>P006M_API_BASE,
        'max_platform_api'=>P006M_API_BASE,
        'min_platform_schema'=>P006M_SCHEMA,
        'max_platform_schema'=>P006M_SCHEMA,
        'schema'=>P006M_SCHEMA,
    ];
    mAssert($metadata['P0'] === ['version'=>P006M_PRO_BASE_VERSION] + $expectedOverlapPro, 'P0 metadata mismatch');
    mAssert($metadata['P1'] === ['version'=>P006M_PRO_OVERLAP_VERSION] + $expectedOverlapPro, 'P1 metadata mismatch');
    mAssert($metadata['P2'] === [
        'version'=>P006M_PRO_BREAKING_VERSION,
        'min_free_version'=>P006M_FREE_BREAKING_VERSION,
        'max_free_version'=>P006M_FREE_BREAKING_VERSION,
        'min_platform_api'=>P006M_API_BREAKING,
        'max_platform_api'=>P006M_API_BREAKING,
        'min_platform_schema'=>P006M_SCHEMA,
        'max_platform_schema'=>P006M_SCHEMA,
        'schema'=>P006M_SCHEMA,
    ], 'P2 metadata mismatch');

    $nodes = [
        'F0' => mNode('F0','f0.zip',$paths['F0'],$entries['F0'],$freeMain,$metadata['F0']),
        'F1' => mNode('F1','f1.zip',$paths['F1'],$entries['F1'],$freeMain,$metadata['F1']),
        'F2' => mNode('F2','f2.zip',$paths['F2'],$entries['F2'],$freeMain,$metadata['F2']),
        'P0' => mNode('P0','p0.zip',$paths['P0'],$entries['P0'],$proMain,$metadata['P0']),
        'P1' => mNode('P1','p1.zip',$paths['P1'],$entries['P1'],$proMain,$metadata['P1']),
        'P2' => mNode('P2','p2.zip',$paths['P2'],$entries['P2'],$proMain,$metadata['P2']),
    ];

    $pairs = [
        'F0_P0' => ['free'=>'F0','pro'=>'P0','expected_state'=>'compatible'],
        'F1_P0' => ['free'=>'F1','pro'=>'P0','expected_state'=>'compatible'],
        'F0_P1' => ['free'=>'F0','pro'=>'P1','expected_state'=>'compatible'],
        'F2_P0' => ['free'=>'F2','pro'=>'P0','expected_state'=>'free_version_too_new'],
        'F1_P2' => ['free'=>'F1','pro'=>'P2','expected_state'=>'free_version_too_old'],
    ];
    foreach ($pairs as &$pair) {
        $pair['pair_id_sha256'] = mPair($nodes[$pair['free']]['sha256'], $nodes[$pair['pro']]['sha256']);
    }
    unset($pair);

    $identity = [
        'protocol' => 'P-006',
        'wave' => '1M-fp58-wordpress-manual-replacement-order',
        'source_sha' => $sourceSha,
        'temporary_approval_id' => P006M_GRANT,
        'classification' => 'NON-RELEASE / TEST-ONLY FORMAL FP-58 EVIDENCE',
        'authorized_fixture' => 'FP-58',
        'nodes' => $nodes,
        'pairs' => $pairs,
        'variant_boundary' => [
            'F0_is_canonical_free' => true,
            'F1_changed_entries' => [$freeMain],
            'F2_changed_entries' => [$freeMain],
            'P0_changed_entries' => [$proMain],
            'P1_changed_entries' => [$proMain],
            'P2_changed_entries' => [$proMain],
            'tracked_product_source_modified' => false,
            'canonical_release_artifacts_replaced' => false,
        ],
        'authorized_runtime_cells' => [
            'minimum' => ['wordpress'=>'6.9','php'=>'8.2','mysql'=>'8.4'],
            'reference' => ['wordpress'=>'7.1','php'=>'8.5','mysql'=>'8.4'],
        ],
        'authorized_paths' => [
            'compatible-free' => ['before'=>'F0_P0','after'=>'F1_P0'],
            'compatible-pro' => ['before'=>'F0_P0','after'=>'F0_P1'],
            'breaking-free' => ['before'=>'F1_P0','after'=>'F2_P0'],
            'breaking-pro' => ['before'=>'F1_P0','after'=>'F1_P2'],
        ],
        'transport' => [
            'owner' => 'WordPress core',
            'api' => 'Plugin_Upgrader::install',
            'overwrite_package' => true,
            'filesystem_method' => 'direct',
            'live_plugin_roots_are_symlinks' => false,
        ],
        'certification_boundary' => [
            'pair_certified' => false,
            'runtime_certified' => false,
            'updater_or_tuf_certified' => false,
            'rollback_or_migration_certified' => false,
            'partial_or_interrupted_replacement_certified' => false,
            'adr_0010' => 'Proposed',
            'ga_or_release_authorized' => false,
        ],
    ];

    mWriteJson($outputDir . '/candidate-identity.json', $identity);
    fwrite(STDOUT, json_encode($identity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 Wave 1M builder] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
