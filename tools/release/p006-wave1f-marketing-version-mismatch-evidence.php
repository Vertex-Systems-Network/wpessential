<?php

declare(strict_types=1);

const P006F_ORIGINAL_FREE_SHA256 = '2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80';
const P006F_PRO_SHA256 = '771bf516e9c1d0707a7d036309b14520facf5daa59742b337b0a0fdf622c0363';
const P006F_ORIGINAL_PAIR_ID = '39a31275225f1b560a22096fcbd66aef8878bc23554c5b32c6861a6a446215ea';
const P006F_CONTEXTS = ['frontend', 'admin', 'rest', 'cron', 'cli'];
const P006F_PRO_MODULES = [
    'roles', 'admin-menu', 'settings', 'dashboard', 'profiles', 'membership',
    'builder-widgets', 'forms-workflows', 'cron', 'notifications', 'emails', 'chat',
];
const P006F_VARIANTS = [
    'FP-19' => [
        'version' => '0.0.9',
        'filename' => 'wpessential-fp19-0.0.9.zip',
        'state' => 'free_version_too_old',
        'reason' => 'free_version_below_supported_minimum',
        'remediation' => 'update_free',
    ],
    'FP-20' => [
        'version' => '0.1.1',
        'filename' => 'wpessential-fp20-0.1.1.zip',
        'state' => 'free_version_too_new',
        'reason' => 'free_version_above_supported_maximum',
        'remediation' => 'update_pro',
    ],
];

function p006fFail(string $message): never
{
    throw new RuntimeException($message);
}

function p006fAssert(bool $condition, string $message): void
{
    if (!$condition) {
        p006fFail($message);
    }
}

function p006fEnv(string $name): string
{
    $value = trim((string) getenv($name));
    if ($value === '') {
        p006fFail("Required environment variable is missing: {$name}");
    }
    return $value;
}

function p006fHashFile(string $path): string
{
    p006fAssert(is_file($path) && filesize($path) > 0, "Required artifact is missing or empty: {$path}");
    $hash = hash_file('sha256', $path);
    p006fAssert(is_string($hash), "Unable to hash artifact: {$path}");
    return $hash;
}

/** @param array<string,mixed> $payload */
function p006fWriteJson(string $path, array $payload): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        p006fFail("Unable to create evidence directory: {$dir}");
    }
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
    p006fAssert(file_put_contents($path, $json) !== false, "Unable to write evidence JSON: {$path}");
}

/** @return array<string,mixed> */
function p006fReadJson(string $path): array
{
    $bytes = file_get_contents($path);
    p006fAssert(is_string($bytes), "Unable to read JSON: {$path}");
    $data = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    p006fAssert(is_array($data), "Expected JSON object: {$path}");
    return $data;
}

/** @return array<string,string> */
function p006fZipEntryHashes(string $zipPath): array
{
    p006fAssert(class_exists(ZipArchive::class), 'ZipArchive is required.');
    $zip = new ZipArchive();
    p006fAssert($zip->open($zipPath) === true, "Unable to open ZIP: {$zipPath}");
    $hashes = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (!is_string($name) || str_ends_with($name, '/')) {
            continue;
        }
        $bytes = $zip->getFromIndex($i);
        p006fAssert(is_string($bytes), "Unable to read ZIP entry: {$name}");
        $hashes[$name] = hash('sha256', $bytes);
    }
    $zip->close();
    ksort($hashes, SORT_STRING);
    return $hashes;
}

/** @return array{sha256:string,pair_id_sha256:string,version:string,filename:string,changed_entries:list<string>,entry_count:int} */
function p006fDeriveVariant(string $originalFreeZip, string $proHash, string $fixture, string $outputDir): array
{
    $spec = P006F_VARIANTS[$fixture] ?? null;
    p006fAssert(is_array($spec), "Unsupported Wave 1F fixture: {$fixture}");
    if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
        p006fFail("Unable to create variant directory: {$outputDir}");
    }

    $source = new ZipArchive();
    p006fAssert($source->open($originalFreeZip) === true, 'Unable to open original immutable Free ZIP.');
    $destinationPath = rtrim($outputDir, '/\\') . '/' . (string) $spec['filename'];
    @unlink($destinationPath);
    $destination = new ZipArchive();
    p006fAssert($destination->open($destinationPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 'Unable to create TEST-ONLY Free ZIP.');

    $entrypoint = 'wpessential/wpessential.php';
    $changedEntries = [];
    $entryCount = 0;
    for ($i = 0; $i < $source->numFiles; $i++) {
        $name = $source->getNameIndex($i);
        p006fAssert(is_string($name) && $name !== '', "Invalid original ZIP entry at index {$i}.");
        if (str_ends_with($name, '/')) {
            if (!$destination->addEmptyDir(rtrim($name, '/'))) {
                p006fFail("Unable to copy directory ZIP entry: {$name}");
            }
            continue;
        }
        $bytes = $source->getFromIndex($i);
        p006fAssert(is_string($bytes), "Unable to read original ZIP entry: {$name}");
        if ($name === $entrypoint) {
            $original = $bytes;
            $countHeader = 0;
            $countConstant = 0;
            $bytes = str_replace('Version: 0.1.0-dev', 'Version: ' . $spec['version'], $bytes, $countHeader);
            $bytes = str_replace("define('WPE_VERSION', '0.1.0-dev');", "define('WPE_VERSION', '" . $spec['version'] . "');", $bytes, $countConstant);
            p006fAssert($countHeader === 1, 'Expected exactly one Free plugin Version header replacement.');
            p006fAssert($countConstant === 1, 'Expected exactly one WPE_VERSION replacement.');
            $normalized = str_replace('Version: ' . $spec['version'], 'Version: 0.1.0-dev', $bytes, $reverseHeader);
            $normalized = str_replace("define('WPE_VERSION', '" . $spec['version'] . "');", "define('WPE_VERSION', '0.1.0-dev');", $normalized, $reverseConstant);
            p006fAssert($reverseHeader === 1 && $reverseConstant === 1 && hash_equals(hash('sha256', $original), hash('sha256', $normalized)), 'TEST-ONLY entrypoint differs beyond the two authorized marketing-version declarations.');
            $changedEntries[] = $name;
        }
        p006fAssert($destination->addFromString($name, $bytes), "Unable to add TEST-ONLY ZIP entry: {$name}");
        $destination->setMtimeName($name, 946684800);
        $destination->setExternalAttributesName($name, ZipArchive::OPSYS_UNIX, 0100644 << 16);
        $destination->setCompressionName($name, ZipArchive::CM_DEFLATE, 9);
        $entryCount++;
    }
    $source->close();
    p006fAssert($destination->close(), 'Unable to close TEST-ONLY Free ZIP.');
    p006fAssert($changedEntries === [$entrypoint], 'TEST-ONLY derivation changed an unexpected ZIP entry set.');

    $originalEntries = p006fZipEntryHashes($originalFreeZip);
    $derivedEntries = p006fZipEntryHashes($destinationPath);
    p006fAssert(array_keys($originalEntries) === array_keys($derivedEntries), 'Derived Free ZIP file set differs from immutable Free ZIP.');
    $logicalChanges = [];
    foreach ($originalEntries as $name => $hash) {
        if (!hash_equals($hash, $derivedEntries[$name])) {
            $logicalChanges[] = $name;
        }
    }
    p006fAssert($logicalChanges === [$entrypoint], 'Derived Free ZIP contains logical byte changes outside wpessential.php.');

    $derivedHash = p006fHashFile($destinationPath);
    p006fAssert(!hash_equals(P006F_ORIGINAL_FREE_SHA256, $derivedHash), 'Derived Free ZIP unexpectedly equals the immutable Free artifact hash.');
    $pairId = hash('sha256', "free:{$derivedHash}\npro:{$proHash}\n");
    return [
        'sha256' => $derivedHash,
        'pair_id_sha256' => $pairId,
        'version' => (string) $spec['version'],
        'filename' => (string) $spec['filename'],
        'changed_entries' => $logicalChanges,
        'entry_count' => $entryCount,
    ];
}

/** @return array<string,mixed> */
function p006fDerive(): array
{
    $sourceSha = p006fEnv('WPE_P006_SOURCE_SHA');
    p006fAssert(preg_match('/^[0-9a-f]{40}$/', $sourceSha) === 1, 'Source SHA must be exact lowercase hex.');
    $originalFree = p006fEnv('WPE_P006_ORIGINAL_FREE_ZIP_PATH');
    $pro = p006fEnv('WPE_P006_PRO_ZIP_PATH');
    $outputDir = p006fEnv('WPE_P006_VARIANT_DIR');
    $originalFreeHash = p006fHashFile($originalFree);
    $proHash = p006fHashFile($pro);
    p006fAssert(hash_equals(P006F_ORIGINAL_FREE_SHA256, $originalFreeHash), 'Original immutable Free hash drift detected; STOP Wave 1F.');
    p006fAssert(hash_equals(P006F_PRO_SHA256, $proHash), 'Original immutable Pro hash drift detected; STOP Wave 1F.');
    $originalPair = hash('sha256', "free:{$originalFreeHash}\npro:{$proHash}\n");
    p006fAssert(hash_equals(P006F_ORIGINAL_PAIR_ID, $originalPair), 'Original immutable pair identity drift detected; STOP Wave 1F.');

    $fp19 = p006fDeriveVariant($originalFree, $proHash, 'FP-19', $outputDir);
    $fp20 = p006fDeriveVariant($originalFree, $proHash, 'FP-20', $outputDir);
    p006fAssert(!hash_equals($fp19['sha256'], $fp20['sha256']), 'FP-19 and FP-20 derived artifact hashes must differ.');
    p006fAssert(!hash_equals($fp19['pair_id_sha256'], $fp20['pair_id_sha256']), 'FP-19 and FP-20 derived pair IDs must differ.');

    return [
        'protocol' => 'P-006',
        'wave' => '1F-marketing-version-mismatch-runtime',
        'source_sha' => $sourceSha,
        'temporary_approval_id' => 'GOV-OWNER-CONSENT-P001-TEMP-P006-004',
        'classification' => 'NON-RELEASE / TEST-ONLY',
        'original' => [
            'free_sha256' => $originalFreeHash,
            'pro_sha256' => $proHash,
            'pair_id_sha256' => $originalPair,
        ],
        'variants' => ['FP-19' => $fp19, 'FP-20' => $fp20],
        'authorized_mutation' => [
            'entry' => 'wpessential/wpessential.php',
            'fields' => ['Plugin header Version', 'WPE_VERSION'],
            'platform_api_unchanged' => '0.1.0',
            'platform_schema_unchanged' => 1,
            'pro_artifact_unchanged' => true,
            'release_artifact' => false,
        ],
        'certification_boundary' => [
            'permanent_p001_certified' => false,
            'free_pro_pair_certified' => false,
            'runtime_certified' => false,
            'adr_0010' => 'Proposed',
            'fp21_plus_executed' => false,
        ],
    ];
}

/** @return array<string,mixed> */
function p006fRuntimeInput(string $mode): array
{
    $fixture = p006fEnv('WPE_P006_FIXTURE_ID');
    p006fAssert(isset(P006F_VARIANTS[$fixture]), 'Wave 1F authorizes only FP-19 and FP-20.');
    $wp = p006fEnv('WPE_P006_EXPECTED_WP');
    $php = p006fEnv('WPE_P006_EXPECTED_PHP');
    $mysql = p006fEnv('WPE_P006_EXPECTED_MYSQL');
    $cells = [
        'minimum' => ['wordpress' => '6.9', 'php' => '8.2', 'mysql' => '8.4'],
        'reference' => ['wordpress' => '7.1', 'php' => '8.5', 'mysql' => '8.4'],
    ];
    $cellId = null;
    foreach ($cells as $id => $cell) {
        if ($wp === $cell['wordpress'] && $php === $cell['php'] && $mysql === $cell['mysql']) {
            $cellId = $id;
            break;
        }
    }
    p006fAssert(is_string($cellId), 'Requested environment is outside GOV-OWNER-CONSENT-P001-TEMP-P006-004.');
    $context = null;
    if ($mode === 'verify') {
        $context = p006fEnv('WPE_P006_REQUEST_CONTEXT');
        p006fAssert(in_array($context, P006F_CONTEXTS, true), 'Unsupported request context.');
    }
    return [
        'fixture_id' => $fixture,
        'spec' => P006F_VARIANTS[$fixture],
        'cell_id' => $cellId,
        'expected_wordpress' => $wp,
        'expected_php' => $php,
        'expected_mysql' => $mysql,
        'source_sha' => p006fEnv('WPE_P006_SOURCE_SHA'),
        'original_free_zip' => p006fEnv('WPE_P006_ORIGINAL_FREE_ZIP_PATH'),
        'free_zip' => p006fEnv('WPE_P006_FREE_ZIP_PATH'),
        'pro_zip' => p006fEnv('WPE_P006_PRO_ZIP_PATH'),
        'derived_free_sha256' => p006fEnv('WPE_P006_DERIVED_FREE_SHA256'),
        'derived_pair_id' => p006fEnv('WPE_P006_DERIVED_PAIR_ID'),
        'wordpress_dir' => rtrim(p006fEnv('WPE_TEST_WORDPRESS_DIR'), '/\\'),
        'evidence_dir' => rtrim(p006fEnv('WPE_P006_EVIDENCE_DIR'), '/\\'),
        'network_log' => p006fEnv('WPE_P006_NETWORK_LOG'),
        'request_context' => $context,
    ];
}

/** @return array{file_count:int,mismatches:list<string>} */
function p006fVerifyInstalledZip(string $zipPath, string $prefix, string $installedRoot): array
{
    $zip = new ZipArchive();
    p006fAssert($zip->open($zipPath) === true, "Unable to open candidate ZIP: {$zipPath}");
    $expected = [];
    $mismatches = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (!is_string($name) || !str_starts_with($name, $prefix)) {
            $mismatches[] = is_string($name) ? "unexpected-prefix:{$name}" : "index:{$i}";
            continue;
        }
        if (str_ends_with($name, '/')) {
            continue;
        }
        $relative = substr($name, strlen($prefix));
        if ($relative === '') {
            continue;
        }
        $expected[] = $relative;
        $installed = $installedRoot . '/' . $relative;
        if (!is_file($installed)) {
            $mismatches[] = "missing:{$relative}";
            continue;
        }
        $bytes = $zip->getFromIndex($i);
        $installedHash = hash_file('sha256', $installed);
        if (!is_string($bytes) || !is_string($installedHash) || !hash_equals(hash('sha256', $bytes), $installedHash)) {
            $mismatches[] = "bytes:{$relative}";
        }
    }
    $zip->close();
    $actual = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($installedRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file instanceof SplFileInfo && $file->isFile()) {
            $actual[] = str_replace('\\', '/', substr($file->getPathname(), strlen($installedRoot) + 1));
        }
    }
    sort($expected, SORT_STRING);
    sort($actual, SORT_STRING);
    p006fAssert($mismatches === [], 'Installed candidate differs from ZIP: ' . implode(', ', $mismatches));
    p006fAssert($expected === $actual, 'Installed candidate file set differs from ZIP.');
    return ['file_count' => count($actual), 'mismatches' => []];
}

/** @return array<string,mixed> */
function p006fArtifactIdentity(array $input): array
{
    $originalFreeHash = p006fHashFile((string) $input['original_free_zip']);
    $derivedFreeHash = p006fHashFile((string) $input['free_zip']);
    $proHash = p006fHashFile((string) $input['pro_zip']);
    p006fAssert(hash_equals(P006F_ORIGINAL_FREE_SHA256, $originalFreeHash), 'Original Free hash drift during runtime evidence.');
    p006fAssert(hash_equals(P006F_PRO_SHA256, $proHash), 'Pro hash drift during runtime evidence.');
    p006fAssert(hash_equals((string) $input['derived_free_sha256'], $derivedFreeHash), 'Derived Free hash mismatch.');
    $pair = hash('sha256', "free:{$derivedFreeHash}\npro:{$proHash}\n");
    p006fAssert(hash_equals((string) $input['derived_pair_id'], $pair), 'Derived pair ID mismatch.');
    $freeRoot = (string) $input['wordpress_dir'] . '/wp-content/plugins/wpessential';
    $proRoot = (string) $input['wordpress_dir'] . '/wp-content/plugins/wpessential-pro';
    $free = p006fVerifyInstalledZip((string) $input['free_zip'], 'wpessential/', $freeRoot);
    $pro = p006fVerifyInstalledZip((string) $input['pro_zip'], 'wpessential-pro/', $proRoot);
    return [
        'source_sha' => $input['source_sha'],
        'classification' => 'NON-RELEASE / TEST-ONLY',
        'original_free_sha256' => $originalFreeHash,
        'derived_free_sha256' => $derivedFreeHash,
        'pro_sha256' => $proHash,
        'derived_pair_id_sha256' => $pair,
        'installed_free_matches_derived_zip' => true,
        'installed_pro_matches_immutable_zip' => true,
        'installed_free_file_count' => $free['file_count'],
        'installed_pro_file_count' => $pro['file_count'],
    ];
}

function p006fEnsureNetworkProbe(array $input): void
{
    $dir = (string) $input['wordpress_dir'] . '/wp-content/mu-plugins';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        p006fFail('Unable to create disposable MU-plugin directory.');
    }
    $plugin = <<<'PHP'
<?php
if (!defined('WPE_P006F_NETWORK_DENY_PROBE_ACTIVE')) { define('WPE_P006F_NETWORK_DENY_PROBE_ACTIVE', true); }
add_filter('pre_http_request', static function ($preempt, $parsedArgs, $url) {
    $log = trim((string) getenv('WPE_P006_NETWORK_LOG'));
    if ($log !== '') { file_put_contents($log, (string) $url . PHP_EOL, FILE_APPEND | LOCK_EX); }
    return new WP_Error('p006_wave1f_network_blocked', 'Outbound HTTP is denied during P-006 Wave 1F runtime evidence.');
}, PHP_INT_MIN, 3);
PHP;
    p006fAssert(file_put_contents($dir . '/p006-wave1f-network-deny.php', $plugin . PHP_EOL) !== false, 'Unable to install Wave 1F network-deny probe.');
}

function p006fResetFile(string $path): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        p006fFail("Unable to create directory: {$dir}");
    }
    p006fAssert(file_put_contents($path, '') !== false, "Unable to reset file: {$path}");
}

/** @return list<string> */
function p006fNetworkAttempts(array $input): array
{
    if (!is_file((string) $input['network_log'])) {
        return [];
    }
    $lines = file((string) $input['network_log'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return is_array($lines) ? array_values(array_map('strval', $lines)) : [];
}

function p006fServerEnvelope(string $context): void
{
    $uris = [
        'activation' => '/wp-admin/plugins.php',
        'frontend' => '/',
        'admin' => '/wp-admin/admin.php?page=wpessential',
        'rest' => '/wp-json/wp/v2/types',
        'cron' => '/wp-cron.php',
        'cli' => '/',
    ];
    $_SERVER['HTTP_HOST'] = 'p006.test';
    $_SERVER['SERVER_NAME'] = 'p006.test';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTPS'] = 'off';
    $_SERVER['REQUEST_URI'] = $uris[$context] ?? '/';
    $_SERVER['SCRIPT_NAME'] = ($context === 'admin' || $context === 'activation') ? '/wp-admin/plugins.php' : '/index.php';
    $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
}

function p006fApplyContext(string $context): void
{
    if ($context === 'admin' && !defined('WP_ADMIN')) {
        define('WP_ADMIN', true);
    }
    if ($context === 'rest' && !defined('REST_REQUEST')) {
        define('REST_REQUEST', true);
    }
    if ($context === 'cron' && !defined('DOING_CRON')) {
        define('DOING_CRON', true);
    }
    if ($context === 'cli' && !defined('WP_CLI')) {
        define('WP_CLI', true);
    }
}

/** @return array<string,mixed> */
function p006fEnvironment(array $input): array
{
    global $wp_version, $wpdb;
    p006fAssert(isset($wp_version) && is_string($wp_version), 'WordPress version unavailable.');
    p006fAssert(isset($wpdb) && $wpdb instanceof wpdb, 'WordPress DB adapter unavailable.');
    $php = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
    $mysql = method_exists($wpdb, 'db_version') ? (string) $wpdb->db_version() : '';
    p006fAssert($wp_version === $input['expected_wordpress'], "WordPress cell mismatch: {$wp_version}");
    p006fAssert($php === $input['expected_php'], "PHP cell mismatch: {$php}");
    p006fAssert(str_starts_with($mysql, (string) $input['expected_mysql']), "MySQL cell mismatch: {$mysql}");
    return ['wordpress' => $wp_version, 'php' => PHP_VERSION, 'php_major_minor' => $php, 'mysql' => $mysql, 'sapi' => PHP_SAPI];
}

/** @return list<string> */
function p006fIncludedProFiles(array $input): array
{
    $root = str_replace('\\', '/', (string) $input['wordpress_dir'] . '/wp-content/plugins/wpessential-pro/');
    $files = [];
    foreach (get_included_files() as $file) {
        $normalized = str_replace('\\', '/', $file);
        if (str_starts_with($normalized, $root)) {
            $files[] = substr($normalized, strlen($root));
        }
    }
    sort($files, SORT_STRING);
    return $files;
}

/** @return array<string,mixed> */
function p006fAssertMismatchRuntime(array $input): array
{
    $freePlugin = 'wpessential/wpessential.php';
    $proPlugin = 'wpessential-pro/wpessential-pro.php';
    p006fAssert(is_plugin_active($freePlugin), 'Derived Free candidate must remain active.');
    p006fAssert(is_plugin_active($proPlugin), 'Immutable Pro candidate must remain active for local mismatch observation.');
    p006fAssert(defined('WPE_VERSION') && (string) WPE_VERSION === $input['spec']['version'], 'Runtime Free marketing version does not match TEST-ONLY variant.');
    p006fAssert(defined('WPE_PLATFORM_API_VERSION') && (string) WPE_PLATFORM_API_VERSION === '0.1.0', 'Platform API changed in TEST-ONLY variant.');
    p006fAssert(defined('WPE_PLATFORM_SCHEMA_GENERATION') && (int) WPE_PLATFORM_SCHEMA_GENERATION === 1, 'Platform schema changed in TEST-ONLY variant.');
    p006fAssert(defined('WPE_PRO_PACKAGE_ACTIVE') && WPE_PRO_PACKAGE_ACTIVE === true, 'Active Pro package did not load for mismatch observation.');
    $compat = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    p006fAssert(is_array($compat), 'Pro did not publish canonical local compatibility result.');
    p006fAssert(($compat['state'] ?? null) === $input['spec']['state'], 'Unexpected compatibility state.');
    p006fAssert(($compat['dimension'] ?? null) === 'free_version', 'Unexpected compatibility dimension.');
    p006fAssert(($compat['reason'] ?? null) === $input['spec']['reason'], 'Unexpected compatibility reason.');
    p006fAssert(($compat['remediation'] ?? null) === $input['spec']['remediation'], 'Unexpected compatibility remediation.');
    p006fAssert(($compat['premium_boot_allowed'] ?? null) === false, 'Mismatch unexpectedly allows premium boot.');
    p006fAssert(($compat['premium_migrations_allowed'] ?? null) === false, 'Mismatch unexpectedly allows premium migrations.');
    p006fAssert(defined('WPE_PRO_COMPATIBILITY_STATE') && (string) WPE_PRO_COMPATIBILITY_STATE === $input['spec']['state'], 'Compatibility observability constant disagrees with canonical state.');
    p006fAssert(defined('WPE_PRO_COMPATIBILITY_BOOT_ALLOWED') && WPE_PRO_COMPATIBILITY_BOOT_ALLOWED === false, 'Compatibility boot constant must remain false.');
    p006fAssert(defined('WPE_PRO_COMPATIBILITY_MIGRATIONS_ALLOWED') && WPE_PRO_COMPATIBILITY_MIGRATIONS_ALLOWED === false, 'Compatibility migrations constant must remain false.');

    $includedPro = p006fIncludedProFiles($input);
    $unexpectedProFiles = array_values(array_filter($includedPro, static function (string $file): bool {
        return $file !== 'wpessential-pro.php' && !str_starts_with($file, 'frameworks/Modules/Compatibility/');
    }));
    p006fAssert($unexpectedProFiles === [], 'Mismatch loaded premium Pro implementation files: ' . implode(', ', $unexpectedProFiles));
    p006fAssert(!class_exists(\WPEssential\Modules\Membership\MembershipModule::class, false), 'Mismatch loaded premium Membership implementation.');
    p006fAssert(class_exists(\WPEssential\Bootstrap\Plugin::class), 'Free Platform bootstrap did not resolve.');
    $kernel = \WPEssential\Bootstrap\Plugin::kernel();
    p006fAssert($kernel instanceof \WPEssential\Kernel\Kernel && $kernel->isBooted(), 'Free kernel did not boot under mismatch.');
    $modules = $kernel->modules();
    p006fAssert($modules->has('custom-post-types') && $modules->has('taxonomies'), 'Free CPT/Taxonomy owners are missing under mismatch.');
    $unexpectedModules = [];
    foreach (P006F_PRO_MODULES as $module) {
        if ($modules->has($module)) {
            $unexpectedModules[] = $module;
        }
    }
    p006fAssert($unexpectedModules === [], 'Mismatch registered premium modules: ' . implode(', ', $unexpectedModules));

    return [
        'free_active' => true,
        'pro_active' => true,
        'free_version' => (string) WPE_VERSION,
        'platform_api' => (string) WPE_PLATFORM_API_VERSION,
        'platform_schema' => (int) WPE_PLATFORM_SCHEMA_GENERATION,
        'compatibility' => $compat,
        'kernel_booted' => true,
        'free_custom_post_types_present' => true,
        'free_taxonomies_present' => true,
        'unexpected_pro_modules' => [],
        'included_pro_files' => $includedPro,
        'unexpected_pro_implementation_files' => [],
        'migration_admission_observed' => false,
    ];
}

/** @return array<string,mixed> */
function p006fPrepare(array $input): array
{
    $artifact = p006fArtifactIdentity($input);
    p006fEnsureNetworkProbe($input);
    p006fResetFile((string) $input['network_log']);
    p006fServerEnvelope('activation');
    require (string) $input['wordpress_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    p006fAssert(defined('WPE_P006F_NETWORK_DENY_PROBE_ACTIVE'), 'Wave 1F network-deny probe did not load.');
    p006fAssert(is_blog_installed(), 'Disposable WordPress must be installed before activation evidence.');
    $environment = p006fEnvironment($input);
    $freePlugin = 'wpessential/wpessential.php';
    $proPlugin = 'wpessential-pro/wpessential-pro.php';
    if (is_plugin_active($proPlugin)) {
        deactivate_plugins($proPlugin, true, false);
    }
    if (is_plugin_active($freePlugin)) {
        deactivate_plugins($freePlugin, true, false);
    }
    p006fAssert(!is_plugin_active($freePlugin) && !is_plugin_active($proPlugin), 'Wave 1F must begin with both plugins inactive.');
    p006fResetFile((string) $input['network_log']);
    $freeActivation = activate_plugin($freePlugin, '', false, true);
    p006fAssert(!is_wp_error($freeActivation) && is_plugin_active($freePlugin), 'WordPress rejected derived TEST-ONLY Free activation.');
    $proActivation = activate_plugin($proPlugin, '', false, true);
    p006fAssert(!is_wp_error($proActivation) && is_plugin_active($proPlugin), 'WordPress rejected immutable Pro activation after Free activation.');
    $attempts = p006fNetworkAttempts($input);
    p006fAssert($attempts === [], 'Unexpected HTTP during Wave 1F activation: ' . implode(', ', $attempts));
    return [
        'status' => 'PASS',
        'phase' => 'activation_sequence',
        'fixture_id' => $input['fixture_id'],
        'cell_id' => $input['cell_id'],
        'expected_environment' => ['wordpress' => $input['expected_wordpress'], 'php' => $input['expected_php'], 'mysql' => $input['expected_mysql']],
        'actual_environment' => $environment,
        'artifact_identity' => $artifact,
        'activation_sequence' => [
            ['step' => 'activate_test_only_free', 'result' => 'success'],
            ['step' => 'activate_immutable_pro', 'result' => 'success'],
        ],
        'active_plugins_after_sequence' => array_values(array_map('strval', (array) get_option('active_plugins', []))),
        'runtime_network_attempts' => $attempts,
        'certification_boundary' => [
            'permanent_p001_certified' => false,
            'free_pro_pair_certified' => false,
            'runtime_certified' => false,
            'adr_0010' => 'Proposed',
            'fp21_plus_executed' => false,
        ],
    ];
}

/** @return array<string,mixed> */
function p006fVerify(array $input): array
{
    $artifact = p006fArtifactIdentity($input);
    $activation = p006fReadJson((string) $input['evidence_dir'] . '/activation.json');
    p006fAssert(($activation['status'] ?? null) === 'PASS', 'Activation evidence must PASS before context verification.');
    p006fEnsureNetworkProbe($input);
    p006fResetFile((string) $input['network_log']);
    $context = (string) $input['request_context'];
    p006fServerEnvelope($context);
    p006fApplyContext($context);
    require (string) $input['wordpress_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    p006fAssert(defined('WPE_P006F_NETWORK_DENY_PROBE_ACTIVE'), 'Wave 1F network-deny probe did not load.');
    $environment = p006fEnvironment($input);
    $runtime = p006fAssertMismatchRuntime($input);
    $attempts = p006fNetworkAttempts($input);
    p006fAssert($attempts === [], 'Unexpected runtime HTTP under mismatch: ' . implode(', ', $attempts));
    return [
        'status' => 'PASS',
        'phase' => 'fresh_request',
        'fixture_id' => $input['fixture_id'],
        'cell_id' => $input['cell_id'],
        'request_context' => $context,
        'actual_environment' => $environment,
        'artifact_identity' => $artifact,
        'runtime' => $runtime,
        'runtime_network_attempts' => $attempts,
        'certification_boundary' => [
            'permanent_p001_certified' => false,
            'free_pro_pair_certified' => false,
            'runtime_certified' => false,
            'adr_0010' => 'Proposed',
            'fp21_plus_executed' => false,
        ],
    ];
}

/** @return array<string,mixed> */
function p006fAggregate(array $input): array
{
    $activation = p006fReadJson((string) $input['evidence_dir'] . '/activation.json');
    p006fAssert(($activation['status'] ?? null) === 'PASS', 'Activation evidence is not PASS.');
    $contexts = [];
    foreach (P006F_CONTEXTS as $context) {
        $record = p006fReadJson((string) $input['evidence_dir'] . "/context-{$context}.json");
        p006fAssert(($record['status'] ?? null) === 'PASS', "Context {$context} is not PASS.");
        p006fAssert(($record['runtime']['compatibility']['state'] ?? null) === $input['spec']['state'], "Context {$context} compatibility state drift.");
        p006fAssert(($record['runtime_network_attempts'] ?? null) === [], "Context {$context} has runtime HTTP attempts.");
        $contexts[$context] = $record;
    }
    return [
        'status' => 'PASS',
        'fixture_id' => $input['fixture_id'],
        'cell_id' => $input['cell_id'],
        'expected_test_only_free_version' => $input['spec']['version'],
        'expected_compatibility_state' => $input['spec']['state'],
        'contexts_passed' => array_keys($contexts),
        'context_count' => count($contexts),
        'artifact_identity' => $activation['artifact_identity'],
        'certification_boundary' => [
            'permanent_p001_certified' => false,
            'free_pro_pair_certified' => false,
            'runtime_certified' => false,
            'adr_0010' => 'Proposed',
            'fp21_plus_executed' => false,
        ],
        'scope' => ['fixture_execution' => [$input['fixture_id']], 'test_only_variant' => true, 'product_release_artifact' => false],
    ];
}

$mode = $argv[1] ?? '';
try {
    if ($mode === 'derive') {
        $result = p006fDerive();
        p006fWriteJson(rtrim(p006fEnv('WPE_P006_VARIANT_DIR'), '/\\') . '/p006-wave1f-candidate-identity.json', $result);
        fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
        exit(0);
    }
    p006fAssert(in_array($mode, ['prepare', 'verify', 'aggregate'], true), 'Usage: php p006-wave1f-marketing-version-mismatch-evidence.php <derive|prepare|verify|aggregate>');
    $input = p006fRuntimeInput($mode);
    $result = $mode === 'prepare' ? p006fPrepare($input) : ($mode === 'verify' ? p006fVerify($input) : p006fAggregate($input));
    $path = (string) $input['evidence_dir'] . '/' . ($mode === 'prepare' ? 'activation.json' : ($mode === 'verify' ? 'context-' . $input['request_context'] . '.json' : 'summary.json'));
    p006fWriteJson($path, $result);
    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 Wave 1F] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
