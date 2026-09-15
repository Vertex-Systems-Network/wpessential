<?php

declare(strict_types=1);

const P006D_CONTEXTS = ['frontend', 'admin', 'rest', 'cron', 'cli'];
const P006D_PRO_MODULES = [
    'roles', 'admin-menu', 'settings', 'dashboard', 'profiles', 'membership',
    'builder-widgets', 'forms-workflows', 'cron', 'notifications', 'emails', 'chat',
];

function p006dFail(string $message): never { throw new RuntimeException($message); }
function p006dAssert(bool $condition, string $message): void { if (!$condition) { p006dFail($message); } }
function p006dEnv(string $name): string
{
    $value = trim((string) getenv($name));
    if ($value === '') { p006dFail("Required environment variable is missing: {$name}"); }
    return $value;
}

/** @return array<string,mixed> */
function p006dInput(string $mode): array
{
    p006dAssert(in_array($mode, ['prepare', 'verify', 'aggregate'], true), 'Usage: php p006-wave1d-compatible-pair-runtime-evidence.php <prepare|verify|aggregate>');
    $fixture = p006dEnv('WPE_P006_FIXTURE_ID');
    p006dAssert(in_array($fixture, ['FP-15', 'FP-16'], true), 'Wave 1D authorizes only FP-15 and FP-16.');
    $expectedWp = p006dEnv('WPE_P006_EXPECTED_WP');
    $expectedPhp = p006dEnv('WPE_P006_EXPECTED_PHP');
    $expectedMysql = p006dEnv('WPE_P006_EXPECTED_MYSQL');
    $allowedCells = [
        ['wordpress' => '6.9', 'php' => '8.2', 'mysql' => '8.4', 'id' => 'minimum'],
        ['wordpress' => '7.1', 'php' => '8.5', 'mysql' => '8.4', 'id' => 'reference'],
    ];
    $cellId = null;
    foreach ($allowedCells as $cell) {
        if ($expectedWp === $cell['wordpress'] && $expectedPhp === $cell['php'] && $expectedMysql === $cell['mysql']) { $cellId = $cell['id']; break; }
    }
    p006dAssert(is_string($cellId), 'Requested environment is outside GOV-OWNER-CONSENT-P001-TEMP-P006-002.');
    $sourceSha = p006dEnv('WPE_P006_SOURCE_SHA');
    $freeHash = p006dEnv('WPE_P006_FREE_SHA256');
    $proHash = p006dEnv('WPE_P006_PRO_SHA256');
    $pairId = p006dEnv('WPE_P006_PAIR_ID');
    p006dAssert(preg_match('/^[0-9a-f]{40}$/', $sourceSha) === 1, 'Source SHA must be exact lowercase hex.');
    foreach (['Free SHA-256' => $freeHash, 'Pro SHA-256' => $proHash, 'pair ID' => $pairId] as $label => $hash) {
        p006dAssert(preg_match('/^[0-9a-f]{64}$/', $hash) === 1, "{$label} must be exact lowercase hex.");
    }
    $wpDir = rtrim(p006dEnv('WPE_TEST_WORDPRESS_DIR'), '/\\');
    p006dAssert(is_file($wpDir . '/wp-load.php'), 'Real WordPress fixture is unavailable.');
    $evidenceDir = rtrim(p006dEnv('WPE_P006_EVIDENCE_DIR'), '/\\');
    if (!is_dir($evidenceDir) && !mkdir($evidenceDir, 0775, true) && !is_dir($evidenceDir)) { p006dFail("Unable to create evidence directory: {$evidenceDir}"); }
    $context = null;
    if ($mode === 'verify') {
        $context = p006dEnv('WPE_P006_REQUEST_CONTEXT');
        p006dAssert(in_array($context, P006D_CONTEXTS, true), 'Unsupported request context.');
    }
    return [
        'mode' => $mode, 'fixture_id' => $fixture, 'cell_id' => $cellId,
        'expected_wordpress' => $expectedWp, 'expected_php' => $expectedPhp, 'expected_mysql' => $expectedMysql,
        'source_sha' => $sourceSha, 'free_sha256' => $freeHash, 'pro_sha256' => $proHash, 'pair_id_sha256' => $pairId,
        'wordpress_dir' => $wpDir, 'free_zip' => p006dEnv('WPE_P006_FREE_ZIP_PATH'), 'pro_zip' => p006dEnv('WPE_P006_PRO_ZIP_PATH'),
        'evidence_dir' => $evidenceDir, 'network_log' => p006dEnv('WPE_P006_NETWORK_LOG'), 'timing_log' => p006dEnv('WPE_P006_TIMING_LOG'),
        'request_context' => $context,
    ];
}

/** @param array<string,mixed> $payload */
function p006dWriteJson(string $path, array $payload): void
{
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) { p006dFail("Unable to create JSON output directory: {$directory}"); }
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
    p006dAssert(file_put_contents($path, $json) !== false, "Unable to write evidence JSON: {$path}");
}

/** @return array<string,mixed> */
function p006dReadJson(string $path): array
{
    $content = file_get_contents($path);
    p006dAssert($content !== false, "Unable to read evidence JSON: {$path}");
    $decoded = json_decode((string) $content, true, flags: JSON_THROW_ON_ERROR);
    p006dAssert(is_array($decoded), "Expected JSON object at {$path}");
    return $decoded;
}

function p006dHashFile(string $path): string
{
    p006dAssert(is_file($path) && filesize($path) > 0, "Required artifact is missing or empty: {$path}");
    $hash = hash_file('sha256', $path);
    p006dAssert(is_string($hash), "Unable to hash artifact: {$path}");
    return $hash;
}

/** @return array{file_count:int,mismatches:list<string>} */
function p006dVerifyInstalledZip(string $zipPath, string $zipPrefix, string $installedRoot): array
{
    p006dAssert(class_exists(ZipArchive::class), 'ZipArchive is required for installed-byte verification.');
    p006dAssert(is_dir($installedRoot), "Installed plugin root is missing: {$installedRoot}");
    $zip = new ZipArchive();
    p006dAssert($zip->open($zipPath) === true, "Unable to open immutable candidate ZIP: {$zipPath}");
    $expected = []; $mismatches = [];
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $name = $zip->getNameIndex($index);
        if (!is_string($name) || !str_starts_with($name, $zipPrefix)) { $mismatches[] = is_string($name) ? "unexpected-prefix:{$name}" : "index:{$index}"; continue; }
        if (str_ends_with($name, '/')) { continue; }
        $relative = str_replace('\\', '/', substr($name, strlen($zipPrefix)));
        if ($relative === '') { continue; }
        $expected[] = $relative;
        $installed = $installedRoot . '/' . $relative;
        if (!is_file($installed)) { $mismatches[] = "missing:{$relative}"; continue; }
        $bytes = $zip->getFromIndex($index); $installedHash = hash_file('sha256', $installed);
        if (!is_string($bytes) || !is_string($installedHash) || !hash_equals(hash('sha256', $bytes), $installedHash)) { $mismatches[] = "bytes:{$relative}"; }
    }
    $zip->close();
    $actual = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($installedRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) { if ($file instanceof SplFileInfo && $file->isFile()) { $actual[] = str_replace('\\', '/', substr($file->getPathname(), strlen($installedRoot) + 1)); } }
    sort($expected, SORT_STRING); sort($actual, SORT_STRING);
    p006dAssert($mismatches === [], 'Installed candidate differs from immutable ZIP: ' . implode(', ', $mismatches));
    p006dAssert($expected === $actual, 'Installed candidate file set differs from immutable ZIP.');
    return ['file_count' => count($actual), 'mismatches' => []];
}

/** @param array<string,mixed> $input @return array<string,mixed> */
function p006dArtifactIdentity(array $input): array
{
    $freeHash = p006dHashFile((string) $input['free_zip']); $proHash = p006dHashFile((string) $input['pro_zip']);
    p006dAssert(hash_equals((string) $input['free_sha256'], $freeHash), 'Free candidate hash drift detected.');
    p006dAssert(hash_equals((string) $input['pro_sha256'], $proHash), 'Pro candidate hash drift detected.');
    $pairId = hash('sha256', "free:{$freeHash}\npro:{$proHash}\n");
    p006dAssert(hash_equals((string) $input['pair_id_sha256'], $pairId), 'Free/Pro pair identity mismatch.');
    $freeRoot = (string) $input['wordpress_dir'] . '/wp-content/plugins/wpessential';
    $proRoot = (string) $input['wordpress_dir'] . '/wp-content/plugins/wpessential-pro';
    p006dAssert(is_file($freeRoot . '/wpessential.php'), 'Packaged Free plugin is not installed canonically.');
    p006dAssert(is_file($proRoot . '/wpessential-pro.php'), 'Packaged Pro plugin is not installed canonically.');
    $free = p006dVerifyInstalledZip((string) $input['free_zip'], 'wpessential/', $freeRoot);
    $pro = p006dVerifyInstalledZip((string) $input['pro_zip'], 'wpessential-pro/', $proRoot);
    return [
        'source_sha' => $input['source_sha'], 'free_sha256' => $freeHash, 'pro_sha256' => $proHash, 'pair_id_sha256' => $pairId,
        'installed_free_matches_zip' => true, 'installed_pro_matches_zip' => true,
        'installed_free_file_count' => $free['file_count'], 'installed_pro_file_count' => $pro['file_count'],
    ];
}

/** @param array<string,mixed> $input */
function p006dEnsureRuntimeProbes(array $input): void
{
    $dir = (string) $input['wordpress_dir'] . '/wp-content/mu-plugins';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) { p006dFail('Unable to create disposable MU-plugin directory.'); }
    $network = <<<'PHP'
<?php
if (!defined('WPE_P006D_NETWORK_DENY_PROBE_ACTIVE')) { define('WPE_P006D_NETWORK_DENY_PROBE_ACTIVE', true); }
add_filter('pre_http_request', static function ($preempt, $parsedArgs, $url) {
    $log = trim((string) getenv('WPE_P006_NETWORK_LOG'));
    if ($log !== '') { file_put_contents($log, (string) $url . PHP_EOL, FILE_APPEND | LOCK_EX); }
    return new WP_Error('p006_wave1d_network_blocked', 'Outbound HTTP is denied during P-006 Wave 1D runtime evidence.');
}, PHP_INT_MIN, 3);
PHP;
    p006dAssert(file_put_contents($dir . '/p006-wave1d-network-deny.php', $network . PHP_EOL) !== false, 'Unable to install Wave 1D network-deny probe.');
    $timing = <<<'PHP'
<?php
if (!defined('WPE_P006D_TIMING_PROBE_ACTIVE')) { define('WPE_P006D_TIMING_PROBE_ACTIVE', true); }
if (!function_exists('p006_wave1d_timing_snapshot')) {
    function p006_wave1d_timing_snapshot(string $stage): void {
        $compatibility = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
        $kernel = null;
        if (class_exists(\WPEssential\Bootstrap\Plugin::class)) { $kernel = \WPEssential\Bootstrap\Plugin::kernel(); }
        $present = [];
        foreach (['roles','admin-menu','settings','dashboard','profiles','membership','builder-widgets','forms-workflows','cron','notifications','emails','chat'] as $module) {
            if ($kernel instanceof \WPEssential\Kernel\Kernel && $kernel->modules()->has($module)) { $present[] = $module; }
        }
        $record = [
            'stage' => $stage,
            'compatibility_state' => is_array($compatibility) ? ($compatibility['state'] ?? null) : null,
            'premium_boot_allowed' => is_array($compatibility) ? (($compatibility['premium_boot_allowed'] ?? false) === true) : false,
            'premium_migrations_allowed' => is_array($compatibility) ? (($compatibility['premium_migrations_allowed'] ?? false) === true) : false,
            'kernel_initialized' => $kernel instanceof \WPEssential\Kernel\Kernel,
            'kernel_booted' => $kernel instanceof \WPEssential\Kernel\Kernel ? $kernel->isBooted() : false,
            'pro_modules_present' => $present,
        ];
        $log = trim((string) getenv('WPE_P006_TIMING_LOG'));
        if ($log !== '') { file_put_contents($log, json_encode($record, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX); }
    }
}
add_action('plugins_loaded', static function (): void { p006_wave1d_timing_snapshot('before_pro_preflight'); }, -250);
add_action('plugins_loaded', static function (): void { p006_wave1d_timing_snapshot('after_pro_preflight_before_free_boot'); }, -150);
add_action('plugins_loaded', static function (): void { p006_wave1d_timing_snapshot('after_free_boot'); }, -50);
PHP;
    p006dAssert(file_put_contents($dir . '/p006-wave1d-timing.php', $timing . PHP_EOL) !== false, 'Unable to install Wave 1D timing probe.');
}

function p006dResetFile(string $path): void
{
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) { p006dFail("Unable to create log directory: {$directory}"); }
    p006dAssert(file_put_contents($path, '') !== false, "Unable to reset log: {$path}");
}

/** @return list<string> */
function p006dNetworkAttempts(array $input): array
{
    $path = (string) $input['network_log']; if (!is_file($path)) { return []; }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return is_array($lines) ? array_values(array_map('strval', $lines)) : [];
}

/** @return list<array<string,mixed>> */
function p006dTimingRecords(array $input): array
{
    $path = (string) $input['timing_log']; if (!is_file($path)) { return []; }
    $records = []; $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach (is_array($lines) ? $lines : [] as $line) {
        $decoded = json_decode((string) $line, true, flags: JSON_THROW_ON_ERROR);
        p006dAssert(is_array($decoded), 'Timing evidence record is not an object.'); $records[] = $decoded;
    }
    return $records;
}

function p006dServerEnvelope(string $context): void
{
    $uris = ['activation' => '/wp-admin/plugins.php', 'frontend' => '/', 'admin' => '/wp-admin/admin.php?page=wpessential', 'rest' => '/wp-json/wp/v2/types', 'cron' => '/wp-cron.php', 'cli' => '/'];
    $_SERVER['HTTP_HOST'] = 'p006.test'; $_SERVER['SERVER_NAME'] = 'p006.test'; $_SERVER['SERVER_PORT'] = '80'; $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['REQUEST_METHOD'] = 'GET'; $_SERVER['REMOTE_ADDR'] = '127.0.0.1'; $_SERVER['HTTPS'] = 'off'; $_SERVER['REQUEST_URI'] = $uris[$context] ?? '/';
    $_SERVER['SCRIPT_NAME'] = $context === 'admin' || $context === 'activation' ? '/wp-admin/plugins.php' : '/index.php'; $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
}

function p006dApplyContext(string $context): void
{
    if ($context === 'admin' && !defined('WP_ADMIN')) { define('WP_ADMIN', true); }
    if ($context === 'rest' && !defined('REST_REQUEST')) { define('REST_REQUEST', true); }
    if ($context === 'cron' && !defined('DOING_CRON')) { define('DOING_CRON', true); }
    if ($context === 'cli' && !defined('WP_CLI')) { define('WP_CLI', true); }
}

/** @return array<string,mixed> */
function p006dEnvironment(array $input): array
{
    global $wp_version, $wpdb;
    p006dAssert(isset($wp_version) && is_string($wp_version), 'WordPress version is unavailable after core boot.');
    p006dAssert(isset($wpdb) && $wpdb instanceof wpdb, 'WordPress database adapter is unavailable.');
    $php = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION; $mysql = method_exists($wpdb, 'db_version') ? (string) $wpdb->db_version() : '';
    p006dAssert($wp_version === $input['expected_wordpress'], "WordPress cell mismatch: {$wp_version}.");
    p006dAssert($php === $input['expected_php'], "PHP cell mismatch: {$php}.");
    p006dAssert(str_starts_with($mysql, (string) $input['expected_mysql']), "MySQL cell mismatch: {$mysql}.");
    return ['wordpress' => $wp_version, 'php' => PHP_VERSION, 'php_major_minor' => $php, 'mysql' => $mysql, 'sapi' => PHP_SAPI];
}

/** @return list<string> */
function p006dActivePlugins(): array
{
    $plugins = get_option('active_plugins', []);
    return is_array($plugins) ? array_values(array_map('strval', $plugins)) : [];
}

/** @return array<string,mixed> */
function p006dPreRuntimeSafety(): array
{
    $compatibility = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    $kernel = class_exists(\WPEssential\Bootstrap\Plugin::class, false) ? \WPEssential\Bootstrap\Plugin::kernel() : null;
    $membershipResolvable = class_exists(\WPEssential\Modules\Membership\MembershipModule::class);
    p006dAssert(!is_array($compatibility), 'Compatibility decision appeared before the next real plugins_loaded runtime.');
    p006dAssert(!$kernel instanceof \WPEssential\Kernel\Kernel, 'Kernel unexpectedly booted during activation API sequencing.');
    p006dAssert($membershipResolvable === false, 'Premium implementation resolved before canonical compatibility PASS.');
    return ['compatibility_published' => false, 'kernel_initialized' => false, 'premium_membership_resolvable' => false];
}

/** @return array<string,mixed> */
function p006dPrepare(array $input): array
{
    $artifact = p006dArtifactIdentity($input);
    p006dAssert(is_file((string) $input['wordpress_dir'] . '/wp-config.php'), 'Disposable wp-config.php is missing.');
    p006dEnsureRuntimeProbes($input); p006dServerEnvelope('activation');
    require (string) $input['wordpress_dir'] . '/wp-load.php'; require_once ABSPATH . 'wp-admin/includes/plugin.php';
    p006dAssert(is_blog_installed(), 'Disposable WordPress must be installed before Wave 1D activation evidence.');
    $environment = p006dEnvironment($input);
    $freePlugin = 'wpessential/wpessential.php'; $proPlugin = 'wpessential-pro/wpessential-pro.php';
    if (is_plugin_active($proPlugin)) { deactivate_plugins($proPlugin, true, false); }
    if (is_plugin_active($freePlugin)) { deactivate_plugins($freePlugin, true, false); }
    p006dAssert(!is_plugin_active($freePlugin) && !is_plugin_active($proPlugin), 'Wave 1D must begin with both candidates inactive.');
    p006dResetFile((string) $input['network_log']); p006dResetFile((string) $input['timing_log']);
    $sequence = []; $proFirstBehavior = null;
    if ($input['fixture_id'] === 'FP-15') {
        $freeActivation = activate_plugin($freePlugin, '', false, true);
        p006dAssert(!is_wp_error($freeActivation) && is_plugin_active($freePlugin), 'WordPress activation API rejected packaged Free candidate in FP-15.');
        $sequence[] = ['step' => 'activate_free', 'result' => 'success']; $beforePro = p006dPreRuntimeSafety();
        $proActivation = activate_plugin($proPlugin, '', false, true);
        p006dAssert(!is_wp_error($proActivation) && is_plugin_active($proPlugin), 'WordPress activation API rejected packaged Pro candidate after Free in FP-15.');
        $sequence[] = ['step' => 'activate_pro', 'result' => 'success']; $afterProActivation = p006dPreRuntimeSafety();
    } else {
        $firstProActivation = activate_plugin($proPlugin, '', false, true);
        if (is_wp_error($firstProActivation)) {
            p006dAssert(!is_plugin_active($proPlugin), 'WordPress reported Pro-first activation error but Pro became active.');
            $proFirstBehavior = ['mode' => 'blocked_by_wordpress_dependency_validation', 'wp_error_code' => $firstProActivation->get_error_code(), 'wp_error_message' => $firstProActivation->get_error_message()];
            $sequence[] = ['step' => 'activate_pro_before_free', 'result' => 'blocked', 'error_code' => $firstProActivation->get_error_code()];
            $preFreeSafety = p006dPreRuntimeSafety();
            $freeActivation = activate_plugin($freePlugin, '', false, true);
            p006dAssert(!is_wp_error($freeActivation) && is_plugin_active($freePlugin), 'Free activation failed while recovering from blocked Pro-first attempt.');
            $sequence[] = ['step' => 'activate_free_after_block', 'result' => 'success'];
            $retryPro = activate_plugin($proPlugin, '', false, true);
            p006dAssert(!is_wp_error($retryPro) && is_plugin_active($proPlugin), 'Pro activation retry failed after Free became available.');
            $sequence[] = ['step' => 'retry_pro_after_free', 'result' => 'success'];
        } else {
            p006dAssert(is_plugin_active($proPlugin) && !is_plugin_active($freePlugin), 'Reachable Pro-first activation did not remain Pro-only.');
            $proFirstBehavior = ['mode' => 'pro_first_activation_reachable_and_inert']; $sequence[] = ['step' => 'activate_pro_before_free', 'result' => 'success'];
            $preFreeSafety = p006dPreRuntimeSafety();
            $freeActivation = activate_plugin($freePlugin, '', false, true);
            p006dAssert(!is_wp_error($freeActivation) && is_plugin_active($freePlugin), 'Free activation failed after reachable inert Pro-first activation.');
            $sequence[] = ['step' => 'activate_free_after_pro', 'result' => 'success'];
        }
        $beforePro = $preFreeSafety; $afterProActivation = p006dPreRuntimeSafety();
    }
    p006dAssert(is_plugin_active($freePlugin) && is_plugin_active($proPlugin), 'Both packaged candidates must be active before fresh runtime verification.');
    $attempts = p006dNetworkAttempts($input);
    p006dAssert($attempts === [], 'Unexpected outbound WordPress HTTP during Wave 1D activation sequencing: ' . implode(', ', $attempts));
    return [
        'status' => 'PASS', 'phase' => 'activation_sequence', 'fixture_id' => $input['fixture_id'], 'cell_id' => $input['cell_id'],
        'expected_environment' => ['wordpress' => $input['expected_wordpress'], 'php' => $input['expected_php'], 'mysql' => $input['expected_mysql']],
        'actual_environment' => $environment, 'artifact_identity' => $artifact, 'activation_sequence' => $sequence, 'pro_first_behavior' => $proFirstBehavior,
        'safety_before_free_or_pro_completion' => $beforePro, 'safety_after_activation_before_fresh_plugins_loaded' => $afterProActivation,
        'active_plugins_after_sequence' => p006dActivePlugins(), 'both_plugins_active_for_fresh_request' => true, 'runtime_network_attempts' => $attempts,
    ];
}

/** @param list<array<string,mixed>> $records */
function p006dAssertTiming(array $records): void
{
    p006dAssert(count($records) === 3, 'Expected exactly three plugins_loaded timing snapshots.'); $byStage = [];
    foreach ($records as $record) { $byStage[(string) ($record['stage'] ?? '')] = $record; }
    foreach (['before_pro_preflight','after_pro_preflight_before_free_boot','after_free_boot'] as $stage) { p006dAssert(isset($byStage[$stage]), "Missing timing snapshot: {$stage}"); }
    $before = $byStage['before_pro_preflight'];
    p006dAssert(($before['compatibility_state'] ?? null) === null, 'Compatibility was published before Pro preflight callback.');
    p006dAssert(($before['kernel_booted'] ?? true) === false, 'Kernel booted before Pro preflight callback.');
    p006dAssert(($before['pro_modules_present'] ?? ['unexpected']) === [], 'Premium modules existed before compatibility preflight.');
    $afterPreflight = $byStage['after_pro_preflight_before_free_boot'];
    p006dAssert(($afterPreflight['compatibility_state'] ?? null) === 'compatible', 'Pro preflight did not publish compatible before Free boot.');
    p006dAssert(($afterPreflight['premium_boot_allowed'] ?? false) === true, 'Compatible preflight did not authorize premium boot.');
    p006dAssert(($afterPreflight['premium_migrations_allowed'] ?? false) === true, 'Compatible preflight did not publish migration admission.');
    p006dAssert(($afterPreflight['kernel_booted'] ?? true) === false, 'Kernel booted before Free boot callback.');
    p006dAssert(($afterPreflight['pro_modules_present'] ?? ['unexpected']) === [], 'Premium modules appeared before Free kernel boot.');
    $afterBoot = $byStage['after_free_boot'];
    p006dAssert(($afterBoot['compatibility_state'] ?? null) === 'compatible' && ($afterBoot['kernel_booted'] ?? false) === true, 'Compatible state/kernel boot missing after Free boot.');
    $present = $afterBoot['pro_modules_present'] ?? []; p006dAssert(is_array($present), 'Timing snapshot Pro module list is invalid.'); sort($present, SORT_STRING);
    $expected = P006D_PRO_MODULES; sort($expected, SORT_STRING); p006dAssert($present === $expected, 'Implemented premium module set was not present after compatible Free boot.');
}

/** @return array<string,mixed> */
function p006dVerify(array $input): array
{
    $context = (string) $input['request_context']; $artifact = p006dArtifactIdentity($input);
    p006dAssert(is_file((string) $input['wordpress_dir'] . '/wp-config.php'), 'Disposable wp-config.php is missing.');
    p006dEnsureRuntimeProbes($input); p006dResetFile((string) $input['network_log']); p006dResetFile((string) $input['timing_log']);
    p006dServerEnvelope($context); p006dApplyContext($context); require (string) $input['wordpress_dir'] . '/wp-load.php'; require_once ABSPATH . 'wp-admin/includes/plugin.php';
    p006dAssert(defined('WPE_P006D_NETWORK_DENY_PROBE_ACTIVE') && defined('WPE_P006D_TIMING_PROBE_ACTIVE'), 'Wave 1D runtime probes did not load.');
    $environment = p006dEnvironment($input); $freePlugin = 'wpessential/wpessential.php'; $proPlugin = 'wpessential-pro/wpessential-pro.php';
    p006dAssert(is_plugin_active($freePlugin) && is_plugin_active($proPlugin), 'Packaged Free+Pro pair is not active in fresh request.');
    $compatibility = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    p006dAssert(is_array($compatibility) && ($compatibility['state'] ?? null) === 'compatible', 'Canonical compatibility result is not compatible.');
    p006dAssert(($compatibility['premium_boot_allowed'] ?? false) === true && ($compatibility['premium_migrations_allowed'] ?? false) === true, 'Compatible runtime did not publish boot/migration admission.');
    p006dAssert(defined('WPE_PRO_COMPATIBILITY_STATE') && WPE_PRO_COMPATIBILITY_STATE === 'compatible', 'Compatibility mirror state is not compatible.');
    p006dAssert(defined('WPE_PRO_COMPATIBILITY_BOOT_ALLOWED') && WPE_PRO_COMPATIBILITY_BOOT_ALLOWED === true, 'Compatibility mirror did not authorize premium boot.');
    p006dAssert(defined('WPE_PRO_COMPATIBILITY_MIGRATIONS_ALLOWED') && WPE_PRO_COMPATIBILITY_MIGRATIONS_ALLOWED === true, 'Compatibility mirror did not publish migration admission.');
    p006dAssert(class_exists(\WPEssential\Bootstrap\Plugin::class), 'Free Platform bootstrap class did not resolve.');
    $kernel = \WPEssential\Bootstrap\Plugin::kernel(); p006dAssert($kernel instanceof \WPEssential\Kernel\Kernel && $kernel->isBooted(), 'WPEssential kernel did not boot.');
    $modules = $kernel->modules(); p006dAssert($modules->has('custom-post-types') && $modules->has('taxonomies'), 'Free owner modules are missing.');
    $present = []; foreach (P006D_PRO_MODULES as $module) { if ($modules->has($module)) { $present[] = $module; } }
    $expectedModules = P006D_PRO_MODULES; sort($present, SORT_STRING); sort($expectedModules, SORT_STRING);
    p006dAssert($present === $expectedModules, 'Compatible runtime did not register the exact implemented premium module set.');
    p006dAssert(class_exists(\WPEssential\Modules\Membership\MembershipModule::class), 'Compatible runtime cannot resolve Pro Membership implementation.');
    $timing = p006dTimingRecords($input); p006dAssertTiming($timing); $attempts = p006dNetworkAttempts($input);
    p006dAssert($attempts === [], 'Unexpected outbound WordPress HTTP during compatible runtime boot: ' . implode(', ', $attempts));
    return [
        'status' => 'PASS', 'phase' => 'request_context', 'fixture_id' => $input['fixture_id'], 'cell_id' => $input['cell_id'], 'request_context' => $context,
        'context_constants' => ['WP_ADMIN' => defined('WP_ADMIN') ? (bool) constant('WP_ADMIN') : false, 'REST_REQUEST' => defined('REST_REQUEST') ? (bool) constant('REST_REQUEST') : false, 'DOING_CRON' => defined('DOING_CRON') ? (bool) constant('DOING_CRON') : false, 'WP_CLI' => defined('WP_CLI') ? (bool) constant('WP_CLI') : false],
        'expected_environment' => ['wordpress' => $input['expected_wordpress'], 'php' => $input['expected_php'], 'mysql' => $input['expected_mysql']],
        'actual_environment' => $environment, 'artifact_identity' => $artifact, 'compatibility' => $compatibility,
        'premium_runtime' => ['free_active' => true, 'pro_active' => true, 'kernel_booted' => true, 'free_custom_post_types_present' => true, 'free_taxonomies_present' => true, 'implemented_pro_modules' => $present, 'implemented_pro_module_count' => count($present), 'membership_class_resolvable' => true, 'migration_admission_observed' => true, 'migration_certification_claimed' => false],
        'plugins_loaded_timing' => $timing, 'runtime_network_attempts' => $attempts,
    ];
}

/** @return array<string,mixed> */
function p006dAggregate(array $input): array
{
    $paths = ['activation' => (string) $input['evidence_dir'] . '/activation.json']; foreach (P006D_CONTEXTS as $context) { $paths[$context] = (string) $input['evidence_dir'] . "/{$context}.json"; }
    $records = []; $missing = [];
    foreach ($paths as $name => $path) { if (!is_file($path)) { $missing[] = $name; continue; } $records[$name] = p006dReadJson($path); }
    $failed = []; $attempts = [];
    foreach ($records as $name => $record) {
        foreach (($record['runtime_network_attempts'] ?? []) as $url) { $attempts[] = (string) $url; }
        $identity = $record['artifact_identity'] ?? null;
        $identityMatches = is_array($identity) && ($identity['source_sha'] ?? null) === $input['source_sha'] && ($identity['free_sha256'] ?? null) === $input['free_sha256'] && ($identity['pro_sha256'] ?? null) === $input['pro_sha256'] && ($identity['pair_id_sha256'] ?? null) === $input['pair_id_sha256'];
        if (($record['status'] ?? null) !== 'PASS' || !$identityMatches) { $failed[] = $name; }
    }
    $status = $missing === [] && $failed === [] && count($records) === 6 ? 'PASS' : 'FAIL';
    return [
        'protocol' => 'P-006', 'wave' => '1D-compatible-free-pro-runtime-baseline', 'fixture_id' => $input['fixture_id'], 'cell_id' => $input['cell_id'], 'status' => $status,
        'expected_environment' => ['wordpress' => $input['expected_wordpress'], 'php' => $input['expected_php'], 'mysql' => $input['expected_mysql']],
        'actual_environment' => $records['activation']['actual_environment'] ?? null, 'source_sha' => $input['source_sha'],
        'artifacts' => ['free_sha256' => $input['free_sha256'], 'pro_sha256' => $input['pro_sha256'], 'pair_id_sha256' => $input['pair_id_sha256']],
        'activation' => $records['activation'] ?? null, 'request_contexts' => array_intersect_key($records, array_flip(P006D_CONTEXTS)), 'missing_records' => $missing, 'failed_records' => $failed,
        'summary' => ['matrix_cell_executed' => 1, 'matrix_cell_passed' => $status === 'PASS' ? 1 : 0, 'matrix_cell_failed' => $status === 'PASS' ? 0 : 1, 'all_required_contexts_terminal' => $missing === [], 'formal_fixture_counter_delta' => 'deferred_to_cross_cell_reconciliation'],
        'side_effect_profile' => ['real_wordpress_boot' => true, 'disposable_database_install_and_activation' => true, 'compatible_pro_runtime_observed' => true, 'migration_admission_observed' => true, 'migration_certification_promoted' => false, 'runtime_network_attempts_detected' => $attempts !== [], 'runtime_network_attempts' => array_values(array_unique($attempts)), 'live_provider_or_billing' => false, 'remote_entitlement_execution' => false, 'production_credentials_or_data' => false, 'destructive_or_irreversible_operations' => false],
        'certification_boundary' => ['temporary_p001_matrix_approval_id' => 'GOV-OWNER-CONSENT-P001-TEMP-P006-002', 'permanent_p001_certified' => false, 'free_pro_pair_certified' => false, 'runtime_certified' => false, 'adr_0010_status' => 'Proposed', 'adjacent_certifications_promoted' => false, 'fp17_or_later_executed' => false],
    ];
}

/** @return array<string,mixed> */
function p006dFailureRecord(array $input, string $phase, Throwable $exception): array
{
    return [
        'status' => 'FAIL', 'phase' => $phase, 'fixture_id' => $input['fixture_id'] ?? null, 'cell_id' => $input['cell_id'] ?? null, 'request_context' => $input['request_context'] ?? null,
        'expected_environment' => ['wordpress' => $input['expected_wordpress'] ?? null, 'php' => $input['expected_php'] ?? null, 'mysql' => $input['expected_mysql'] ?? null],
        'artifact_identity' => ['source_sha' => $input['source_sha'] ?? null, 'free_sha256' => $input['free_sha256'] ?? null, 'pro_sha256' => $input['pro_sha256'] ?? null, 'pair_id_sha256' => $input['pair_id_sha256'] ?? null],
        'failure' => ['type' => get_class($exception), 'message' => $exception->getMessage()], 'runtime_network_attempts' => isset($input['network_log']) ? p006dNetworkAttempts($input) : [],
    ];
}

if (PHP_SAPI !== 'cli') { fwrite(STDERR, "P-006 Wave 1D evidence harness is CLI-only.\n"); exit(1); }
$mode = $argv[1] ?? ''; $input = [];
try {
    $input = p006dInput($mode);
    if ($mode === 'prepare') { $record = p006dPrepare($input); p006dWriteJson((string) $input['evidence_dir'] . '/activation.json', $record); fwrite(STDOUT, "[p006-wave1d] {$input['fixture_id']} {$input['cell_id']} activation-sequence PASS\n"); exit(0); }
    if ($mode === 'verify') { $record = p006dVerify($input); $context = (string) $input['request_context']; p006dWriteJson((string) $input['evidence_dir'] . "/{$context}.json", $record); fwrite(STDOUT, "[p006-wave1d] {$input['fixture_id']} {$input['cell_id']} {$context} PASS\n"); exit(0); }
    $summary = p006dAggregate($input); p006dWriteJson((string) $input['evidence_dir'] . '/summary.json', $summary); fwrite(STDOUT, "[p006-wave1d] {$input['fixture_id']} {$input['cell_id']} aggregate {$summary['status']}\n"); exit($summary['status'] === 'PASS' ? 0 : 1);
} catch (Throwable $exception) {
    $phase = $mode === 'verify' ? 'request_context' : ($mode === 'aggregate' ? 'aggregate' : 'activation_sequence');
    if ($input !== [] && isset($input['evidence_dir'])) {
        $path = (string) $input['evidence_dir'] . '/failure.json'; if ($mode === 'prepare') { $path = (string) $input['evidence_dir'] . '/activation.json'; } elseif ($mode === 'verify' && isset($input['request_context'])) { $path = (string) $input['evidence_dir'] . '/' . $input['request_context'] . '.json'; }
        try { p006dWriteJson($path, p006dFailureRecord($input, $phase, $exception)); } catch (Throwable) { }
    }
    fwrite(STDERR, "[p006-wave1d] FAIL: {$exception->getMessage()}\n"); exit(1);
}
