<?php

declare(strict_types=1);

const P006I_GRANT = 'GOV-P001-CF-TEMP-007';
const P006I_PRO_MODULES = [
    'roles',
    'admin-menu',
    'settings',
    'dashboard',
    'profiles',
    'membership',
    'builder-widgets',
    'forms-workflows',
    'cron',
    'notifications',
    'emails',
    'chat',
];
const P006I_WARMUPS = 20;
const P006I_SAMPLES = 100;
const P006I_MEDIAN_LIMIT_MS = 5.0;
const P006I_P95_LIMIT_MS = 20.0;

function iFail(string $message): never
{
    throw new RuntimeException($message);
}

function iAssert(bool $condition, string $message): void
{
    if (!$condition) {
        iFail($message);
    }
}

function iEnv(string $key): string
{
    $value = trim((string) getenv($key));
    if ($value === '') {
        iFail("Missing env: {$key}");
    }

    return $value;
}

function iHash(string $path): string
{
    iAssert(is_file($path) && filesize($path) > 0, "Missing artifact: {$path}");
    $hash = hash_file('sha256', $path);
    iAssert(is_string($hash), "Unable to hash artifact: {$path}");

    return $hash;
}

function iPair(string $freeHash, string $proHash): string
{
    return hash('sha256', "free:{$freeHash}\npro:{$proHash}\n");
}

/** @param array<string,mixed> $value */
function iWrite(string $path, array $value): void
{
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }

    $encoded = json_encode(
        $value,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
    );

    iAssert(file_put_contents($path, $encoded . PHP_EOL) !== false, "Unable to write evidence: {$path}");
}

/** @return array<string,mixed> */
function iRead(string $path): array
{
    iAssert(is_file($path), "Missing evidence file: {$path}");
    $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    iAssert(is_array($decoded), "Invalid JSON evidence: {$path}");

    return $decoded;
}

/** @return array<string,mixed> */
function iIdentity(): array
{
    $sourceSha = iEnv('WPE_P006_SOURCE_SHA');
    iAssert(preg_match('/^[0-9a-f]{40}$/', $sourceSha) === 1, 'Invalid source SHA');

    $freeHash = iHash(iEnv('WPE_P006_FREE_ZIP_PATH'));
    $proHash = iHash(iEnv('WPE_P006_PRO_ZIP_PATH'));
    $pairId = iPair($freeHash, $proHash);

    return [
        'protocol' => 'P-006',
        'wave' => '1I-real-wordpress-runtime',
        'source_sha' => $sourceSha,
        'temporary_approval_id' => P006I_GRANT,
        'classification' => 'NON-RELEASE / TEST-ONLY EVIDENCE',
        'candidate_identity' => [
            'free_sha256' => $freeHash,
            'pro_sha256' => $proHash,
            'pair_id_sha256' => $pairId,
        ],
        'authorized_fixtures' => ['FP-34', 'FP-44'],
        'authorized_runtime_cells' => [
            'minimum' => ['wordpress' => '6.9', 'php' => '8.2', 'mysql' => '8.4'],
            'reference' => ['wordpress' => '7.1', 'php' => '8.5', 'mysql' => '8.4'],
        ],
        'fp44_method' => [
            'clock' => 'hrtime(true)',
            'warmups' => P006I_WARMUPS,
            'samples' => P006I_SAMPLES,
            'median_limit_ms' => P006I_MEDIAN_LIMIT_MS,
            'p95_limit_ms' => P006I_P95_LIMIT_MS,
            'outbound_http_attempt_limit' => 0,
            'scope' => 'LocalCompatibilityPreflight::evaluateRuntime() inside a booted real WordPress process',
        ],
        'timeout_safe_ci' => [
            'candidate_build_separate_job' => true,
            'fp34_matrix_separate_job' => true,
            'fp44_matrix_separate_job' => true,
            'stale_run_cancellation' => true,
        ],
        'certification_boundary' => [
            'permanent_p001_cf_certified' => false,
            'free_pro_pair_certified' => false,
            'runtime_certified' => false,
            'adr_0010' => 'Proposed',
            'ga_or_release_authorized' => false,
        ],
    ];
}

/** @return array<string,mixed> */
function iLoadIdentity(): array
{
    $identity = iRead(iEnv('WPE_P006_IDENTITY_PATH'));
    iAssert(($identity['temporary_approval_id'] ?? null) === P006I_GRANT, 'Temporary approval mismatch');
    iAssert(($identity['source_sha'] ?? null) === iEnv('WPE_P006_SOURCE_SHA'), 'Identity source SHA mismatch');

    $candidate = $identity['candidate_identity'] ?? null;
    iAssert(is_array($candidate), 'Candidate identity missing');

    $freeHash = iHash(iEnv('WPE_P006_FREE_ZIP_PATH'));
    $proHash = iHash(iEnv('WPE_P006_PRO_ZIP_PATH'));
    $pairId = iPair($freeHash, $proHash);

    iAssert(($candidate['free_sha256'] ?? null) === $freeHash, 'Free candidate hash drift; STOP');
    iAssert(($candidate['pro_sha256'] ?? null) === $proHash, 'Pro candidate hash drift; STOP');
    iAssert(($candidate['pair_id_sha256'] ?? null) === $pairId, 'Candidate pair identity drift; STOP');

    return $identity;
}

/** @return array{cell_id:string,expected_wp:string,expected_php:string,expected_mysql:string,wp_dir:string,evidence_dir:string,network_log:string} */
function iRuntimeInput(): array
{
    iLoadIdentity();

    $wp = iEnv('WPE_P006_EXPECTED_WP');
    $php = iEnv('WPE_P006_EXPECTED_PHP');
    $mysql = iEnv('WPE_P006_EXPECTED_MYSQL');

    $cellId = match (true) {
        $wp === '6.9' && $php === '8.2' && $mysql === '8.4' => 'minimum',
        $wp === '7.1' && $php === '8.5' && $mysql === '8.4' => 'reference',
        default => null,
    };
    iAssert(is_string($cellId), 'Runtime environment is outside GOV-P001-CF-TEMP-007');

    return [
        'cell_id' => $cellId,
        'expected_wp' => $wp,
        'expected_php' => $php,
        'expected_mysql' => $mysql,
        'wp_dir' => rtrim(iEnv('WPE_TEST_WORDPRESS_DIR'), '/\\'),
        'evidence_dir' => rtrim(iEnv('WPE_P006_EVIDENCE_DIR'), '/\\'),
        'network_log' => iEnv('WPE_P006_NETWORK_LOG'),
    ];
}

/** @param array<string,mixed> $in */
function iInstallNetworkProbe(array $in): void
{
    $dir = $in['wp_dir'] . '/wp-content/mu-plugins';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $probe = <<<'PHP'
<?php
if (!defined('WPE_P006I_NETWORK_DENY_ACTIVE')) {
    define('WPE_P006I_NETWORK_DENY_ACTIVE', true);
}
add_filter('pre_http_request', static function ($preempt, $args, $url) {
    $log = trim((string) getenv('WPE_P006_NETWORK_LOG'));
    if ($log !== '') {
        file_put_contents($log, (string) $url . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    return new WP_Error('p006_wave1i_network_blocked', 'Outbound HTTP denied during P-006 Wave 1I evidence.');
}, PHP_INT_MIN, 3);
PHP;

    iAssert(
        file_put_contents($dir . '/p006-wave1i-network-deny.php', $probe . PHP_EOL) !== false,
        'Unable to install outbound HTTP deny probe',
    );
}

/** @param array<string,mixed> $in */
function iResetNetworkLog(array $in): void
{
    if (!is_dir(dirname($in['network_log']))) {
        mkdir(dirname($in['network_log']), 0775, true);
    }
    iAssert(file_put_contents($in['network_log'], '') !== false, 'Unable to reset network log');
}

/** @param array<string,mixed> $in @return list<string> */
function iNetworkAttempts(array $in): array
{
    if (!is_file($in['network_log'])) {
        return [];
    }

    $lines = file($in['network_log'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) {
        return [];
    }

    return array_values(array_map('strval', $lines));
}

function iEnvelope(string $uri = '/'): void
{
    $_SERVER['HTTP_HOST'] = 'p006.test';
    $_SERVER['SERVER_NAME'] = 'p006.test';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTPS'] = 'off';
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
}

/** @param array<string,mixed> $in */
function iBootWordPress(array $in): void
{
    iEnvelope('/');
    require $in['wp_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    iAssert(defined('WPE_P006I_NETWORK_DENY_ACTIVE'), 'Outbound HTTP deny probe is not active');
}

/** @param array<string,mixed> $in @return array<string,string> */
function iEnvironment(array $in): array
{
    global $wp_version, $wpdb;

    iAssert(isset($wp_version) && is_string($wp_version), 'WordPress version unavailable');
    iAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable');

    $php = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
    $mysql = (string) $wpdb->db_version();

    iAssert($wp_version === $in['expected_wp'], 'WordPress runtime cell mismatch');
    iAssert($php === $in['expected_php'], 'PHP runtime cell mismatch');
    iAssert(str_starts_with($mysql, $in['expected_mysql']), 'MySQL runtime cell mismatch');

    return [
        'wordpress' => $wp_version,
        'php' => PHP_VERSION,
        'mysql' => $mysql,
        'sapi' => PHP_SAPI,
    ];
}

/** @return list<string> */
function iPremiumModuleSet(): array
{
    if (!class_exists(\WPEssential\Bootstrap\Plugin::class)) {
        return [];
    }

    $kernel = \WPEssential\Bootstrap\Plugin::kernel();
    if (!$kernel instanceof \WPEssential\Kernel\Kernel || !$kernel->isBooted()) {
        return [];
    }

    $modules = $kernel->modules();
    $present = [];
    foreach (P006I_PRO_MODULES as $moduleId) {
        if ($modules->has($moduleId)) {
            $present[] = $moduleId;
        }
    }

    return $present;
}

/** @param array<string,mixed> $in @return list<string> */
function iIncludedProFiles(array $in): array
{
    $root = str_replace('\\', '/', $in['wp_dir'] . '/wp-content/plugins/wpessential-pro/');
    $files = [];
    foreach (get_included_files() as $file) {
        $normalized = str_replace('\\', '/', $file);
        if (str_starts_with($normalized, $root)) {
            $files[] = substr($normalized, strlen($root));
        }
    }
    sort($files);

    return array_values(array_unique($files));
}

/** @param array<string,mixed> $in */
function iPrepare(array $in): array
{
    iInstallNetworkProbe($in);
    iResetNetworkLog($in);
    iBootWordPress($in);

    iAssert(is_blog_installed(), 'Disposable WordPress is not installed');

    foreach (['wpessential-pro/wpessential-pro.php', 'wpessential/wpessential.php'] as $plugin) {
        if (is_plugin_active($plugin)) {
            deactivate_plugins($plugin, true, false);
        }
    }

    iResetNetworkLog($in);
    $result = activate_plugin('wpessential/wpessential.php', '', false, true);
    iAssert(!is_wp_error($result) && is_plugin_active('wpessential/wpessential.php'), 'Free activation failed');

    $result = activate_plugin('wpessential-pro/wpessential-pro.php', '', false, true);
    iAssert(!is_wp_error($result) && is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro activation failed');

    $attempts = iNetworkAttempts($in);
    iAssert($attempts === [], 'Unexpected outbound HTTP during activation');

    return [
        'status' => 'PASS',
        'phase' => 'prepare',
        'cell_id' => $in['cell_id'],
        'environment' => iEnvironment($in),
        'network_attempts' => [],
    ];
}

/** @param array<string,mixed> $in */
function iSetOrder(array $in, string $orderId): array
{
    iInstallNetworkProbe($in);
    iResetNetworkLog($in);
    iBootWordPress($in);

    $orders = [
        'free-pro' => ['wpessential/wpessential.php', 'wpessential-pro/wpessential-pro.php'],
        'pro-free' => ['wpessential-pro/wpessential-pro.php', 'wpessential/wpessential.php'],
    ];
    $order = $orders[$orderId] ?? null;
    iAssert(is_array($order), 'Unsupported FP-34 load-order id');

    iAssert(update_option('active_plugins', $order), 'Unable to set active_plugins order');
    $stored = get_option('active_plugins', []);
    iAssert($stored === $order, 'active_plugins order did not persist exactly');

    return [
        'status' => 'PASS',
        'phase' => 'set-order',
        'cell_id' => $in['cell_id'],
        'order_id' => $orderId,
        'stored_active_plugins' => $stored,
    ];
}

/** @param array<string,mixed> $in */
function iFp34(array $in, string $orderId): array
{
    $orders = [
        'free-pro' => ['wpessential/wpessential.php', 'wpessential-pro/wpessential-pro.php'],
        'pro-free' => ['wpessential-pro/wpessential-pro.php', 'wpessential/wpessential.php'],
    ];
    $expectedOrder = $orders[$orderId] ?? null;
    iAssert(is_array($expectedOrder), 'Unsupported FP-34 load-order id');

    iInstallNetworkProbe($in);
    iResetNetworkLog($in);
    iBootWordPress($in);

    $storedOrder = get_option('active_plugins', []);
    iAssert($storedOrder === $expectedOrder, 'Fresh WordPress process did not use requested active_plugins order');

    $compatibility = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    iAssert(is_array($compatibility), 'Compatibility result is absent');
    iAssert(($compatibility['state'] ?? null) === 'compatible', 'Authorized immutable pair is not compatible');
    iAssert(($compatibility['premium_boot_allowed'] ?? null) === true, 'Compatible pair did not allow premium boot');
    iAssert(($compatibility['premium_migrations_allowed'] ?? null) === true, 'Compatible pair did not allow premium migrations');

    $normalized = array_map(static fn (string $file): string => str_replace('\\', '/', $file), get_included_files());
    $freeMain = str_replace('\\', '/', $in['wp_dir'] . '/wp-content/plugins/wpessential/wpessential.php');
    $proMain = str_replace('\\', '/', $in['wp_dir'] . '/wp-content/plugins/wpessential-pro/wpessential-pro.php');
    $freeIndex = array_search($freeMain, $normalized, true);
    $proIndex = array_search($proMain, $normalized, true);
    iAssert(is_int($freeIndex) && is_int($proIndex), 'Unable to observe both plugin bootstrap files');

    $attempts = iNetworkAttempts($in);
    iAssert($attempts === [], 'Unexpected outbound HTTP during FP-34 fresh boot');

    return [
        'status' => 'PASS',
        'fixture_id' => 'FP-34',
        'cell_id' => $in['cell_id'],
        'order_id' => $orderId,
        'environment' => iEnvironment($in),
        'requested_active_plugins' => $expectedOrder,
        'stored_active_plugins' => $storedOrder,
        'observed_bootstrap_include_order' => [
            'free_index' => $freeIndex,
            'pro_index' => $proIndex,
            'free_before_pro' => $freeIndex < $proIndex,
        ],
        'compatibility' => $compatibility,
        'premium_module_set' => iPremiumModuleSet(),
        'included_pro_files' => iIncludedProFiles($in),
        'fatal_or_error' => null,
        'network_attempts' => [],
        'certification_boundary' => [
            'runtime_evidence_only' => true,
            'pair_certified' => false,
            'runtime_certified' => false,
            'adr_0010' => 'Proposed',
        ],
    ];
}

/** @param array<string,mixed> $in */
function iFp34Aggregate(array $in): array
{
    $a = iRead($in['evidence_dir'] . '/fp34-free-pro.json');
    $b = iRead($in['evidence_dir'] . '/fp34-pro-free.json');

    foreach ([$a, $b] as $result) {
        iAssert(($result['status'] ?? null) === 'PASS', 'FP-34 order result is not PASS');
        iAssert(($result['fixture_id'] ?? null) === 'FP-34', 'FP-34 fixture identity mismatch');
    }

    $keys = ['state', 'dimension', 'reason', 'remediation', 'premium_boot_allowed', 'premium_migrations_allowed'];
    $aCompat = $a['compatibility'] ?? [];
    $bCompat = $b['compatibility'] ?? [];
    foreach ($keys as $key) {
        iAssert(($aCompat[$key] ?? null) === ($bCompat[$key] ?? null), "FP-34 compatibility drift across load order: {$key}");
    }

    iAssert(($a['premium_module_set'] ?? null) === ($b['premium_module_set'] ?? null), 'FP-34 premium module set drift across load order');

    return [
        'status' => 'PASS',
        'fixture_id' => 'FP-34',
        'cell_id' => $in['cell_id'],
        'orders_tested' => ['free-pro', 'pro-free'],
        'effective_compatibility_identical' => true,
        'premium_module_set_identical' => true,
        'fatal_or_error' => null,
        'network_attempts' => [],
    ];
}

/** @param list<float> $samples */
function iPercentile(array $samples, float $p): float
{
    sort($samples, SORT_NUMERIC);
    $count = count($samples);
    iAssert($count > 0, 'No timing samples');
    $index = max(0, min($count - 1, (int) ceil($p * $count) - 1));

    return $samples[$index];
}

/** @param array<string,mixed> $in */
function iFp44(array $in): array
{
    iInstallNetworkProbe($in);
    iResetNetworkLog($in);
    iBootWordPress($in);

    $class = \WPEssential\Modules\Compatibility\LocalCompatibilityPreflight::class;
    iAssert(class_exists($class), 'LocalCompatibilityPreflight unavailable in real WordPress');

    $globalCompatibility = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    iAssert(is_array($globalCompatibility) && ($globalCompatibility['state'] ?? null) === 'compatible', 'Booted pair is not compatible');

    iResetNetworkLog($in);

    for ($i = 0; $i < P006I_WARMUPS; $i++) {
        $decision = $class::evaluateRuntime();
        iAssert(($decision['state'] ?? null) === 'compatible', 'Warm-up compatibility decision drift');
    }

    $samples = [];
    for ($i = 0; $i < P006I_SAMPLES; $i++) {
        $start = hrtime(true);
        $decision = $class::evaluateRuntime();
        $elapsed = hrtime(true) - $start;
        iAssert(($decision['state'] ?? null) === 'compatible', 'Measured compatibility decision drift');
        iAssert(($decision['premium_boot_allowed'] ?? null) === true, 'Measured compatible decision denied premium boot');
        $samples[] = $elapsed / 1_000_000;
    }

    $median = iPercentile($samples, 0.50);
    $p95 = iPercentile($samples, 0.95);
    $attempts = iNetworkAttempts($in);

    iAssert($median <= P006I_MEDIAN_LIMIT_MS, sprintf('FP-44 median %.6fms exceeds %.3fms bound', $median, P006I_MEDIAN_LIMIT_MS));
    iAssert($p95 <= P006I_P95_LIMIT_MS, sprintf('FP-44 p95 %.6fms exceeds %.3fms bound', $p95, P006I_P95_LIMIT_MS));
    iAssert($attempts === [], 'FP-44 observed outbound HTTP attempt(s): ' . implode(', ', $attempts));

    return [
        'status' => 'PASS',
        'fixture_id' => 'FP-44',
        'cell_id' => $in['cell_id'],
        'environment' => iEnvironment($in),
        'method' => [
            'clock' => 'hrtime(true)',
            'warmups' => P006I_WARMUPS,
            'samples' => P006I_SAMPLES,
            'scope' => 'LocalCompatibilityPreflight::evaluateRuntime() inside booted real WordPress',
        ],
        'bounds_ms' => [
            'median_max' => P006I_MEDIAN_LIMIT_MS,
            'p95_max' => P006I_P95_LIMIT_MS,
        ],
        'observed_ms' => [
            'median' => $median,
            'p95' => $p95,
            'min' => min($samples),
            'max' => max($samples),
            'samples' => $samples,
        ],
        'outbound_http_attempts' => [],
        'network_attempt_count' => 0,
        'compatibility_state' => 'compatible',
        'certification_boundary' => [
            'runtime_evidence_only' => true,
            'pair_certified' => false,
            'runtime_certified' => false,
            'adr_0010' => 'Proposed',
        ],
    ];
}

$mode = trim((string) getenv('WPE_P006_EXECUTION_MODE'));
if ($mode === '') {
    $mode = $argv[1] ?? '';
}

try {
    if ($mode === 'identity') {
        $identity = iIdentity();
        iWrite(iEnv('WPE_P006_IDENTITY_PATH'), $identity);
        fwrite(STDOUT, json_encode($identity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
        exit(0);
    }

    $input = iRuntimeInput();

    if ($mode === 'prepare') {
        $result = iPrepare($input);
        iWrite($input['evidence_dir'] . '/prepare.json', $result);
    } elseif ($mode === 'set-order') {
        $orderId = iEnv('WPE_P006_LOAD_ORDER');
        $result = iSetOrder($input, $orderId);
        iWrite($input['evidence_dir'] . '/set-order-' . $orderId . '.json', $result);
    } elseif ($mode === 'fp34') {
        $orderId = iEnv('WPE_P006_LOAD_ORDER');
        $result = iFp34($input, $orderId);
        iWrite($input['evidence_dir'] . '/fp34-' . $orderId . '.json', $result);
    } elseif ($mode === 'fp34-aggregate') {
        $result = iFp34Aggregate($input);
        iWrite($input['evidence_dir'] . '/fp34-summary.json', $result);
    } elseif ($mode === 'fp44') {
        $result = iFp44($input);
        iWrite($input['evidence_dir'] . '/fp44.json', $result);
    } else {
        iFail('Usage: <identity|prepare|set-order|fp34|fp34-aggregate|fp44>');
    }

    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 Wave 1I] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
