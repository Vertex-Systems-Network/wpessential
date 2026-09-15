<?php

declare(strict_types=1);

const P006E_CONTEXTS = ['frontend', 'admin', 'rest', 'cron', 'cli'];
const P006E_PRO_MODULES = [
    'roles', 'admin-menu', 'settings', 'dashboard', 'profiles', 'membership',
    'builder-widgets', 'forms-workflows', 'cron', 'notifications', 'emails', 'chat',
];

function p006eFail(string $message): never
{
    throw new RuntimeException($message);
}

function p006eAssert(bool $condition, string $message): void
{
    if (!$condition) {
        p006eFail($message);
    }
}

function p006eEnv(string $name): string
{
    $value = trim((string) getenv($name));
    if ($value === '') {
        p006eFail("Required environment variable is missing: {$name}");
    }
    return $value;
}

/** @return array<string,mixed> */
function p006eInput(string $mode): array
{
    p006eAssert(
        in_array($mode, ['prepare', 'verify', 'aggregate'], true),
        'Usage: php p006-wave1e-inactive-dependency-runtime-evidence.php <prepare|verify|aggregate>'
    );

    $fixture = p006eEnv('WPE_P006_FIXTURE_ID');
    p006eAssert(in_array($fixture, ['FP-17', 'FP-18'], true), 'Wave 1E authorizes only FP-17 and FP-18.');

    $expectedWp = p006eEnv('WPE_P006_EXPECTED_WP');
    $expectedPhp = p006eEnv('WPE_P006_EXPECTED_PHP');
    $expectedMysql = p006eEnv('WPE_P006_EXPECTED_MYSQL');
    $allowedCells = [
        ['wordpress' => '6.9', 'php' => '8.2', 'mysql' => '8.4', 'id' => 'minimum'],
        ['wordpress' => '7.1', 'php' => '8.5', 'mysql' => '8.4', 'id' => 'reference'],
    ];
    $cellId = null;
    foreach ($allowedCells as $cell) {
        if (
            $expectedWp === $cell['wordpress']
            && $expectedPhp === $cell['php']
            && $expectedMysql === $cell['mysql']
        ) {
            $cellId = $cell['id'];
            break;
        }
    }
    p006eAssert(is_string($cellId), 'Requested environment is outside GOV-OWNER-CONSENT-P001-TEMP-P006-003.');

    $sourceSha = p006eEnv('WPE_P006_SOURCE_SHA');
    $freeHash = p006eEnv('WPE_P006_FREE_SHA256');
    $proHash = p006eEnv('WPE_P006_PRO_SHA256');
    $pairId = p006eEnv('WPE_P006_PAIR_ID');
    p006eAssert(preg_match('/^[0-9a-f]{40}$/', $sourceSha) === 1, 'Source SHA must be exact lowercase hex.');
    foreach (['Free SHA-256' => $freeHash, 'Pro SHA-256' => $proHash, 'pair ID' => $pairId] as $label => $hash) {
        p006eAssert(preg_match('/^[0-9a-f]{64}$/', $hash) === 1, "{$label} must be exact lowercase hex.");
    }

    $wpDir = rtrim(p006eEnv('WPE_TEST_WORDPRESS_DIR'), '/\\');
    p006eAssert(is_file($wpDir . '/wp-load.php'), 'Real WordPress fixture is unavailable.');
    $evidenceDir = rtrim(p006eEnv('WPE_P006_EVIDENCE_DIR'), '/\\');
    if (!is_dir($evidenceDir) && !mkdir($evidenceDir, 0775, true) && !is_dir($evidenceDir)) {
        p006eFail("Unable to create evidence directory: {$evidenceDir}");
    }

    $context = null;
    if ($mode === 'verify') {
        $context = p006eEnv('WPE_P006_REQUEST_CONTEXT');
        p006eAssert(in_array($context, P006E_CONTEXTS, true), 'Unsupported request context.');
    }

    return [
        'mode' => $mode,
        'fixture_id' => $fixture,
        'cell_id' => $cellId,
        'expected_wordpress' => $expectedWp,
        'expected_php' => $expectedPhp,
        'expected_mysql' => $expectedMysql,
        'source_sha' => $sourceSha,
        'free_sha256' => $freeHash,
        'pro_sha256' => $proHash,
        'pair_id_sha256' => $pairId,
        'wordpress_dir' => $wpDir,
        'free_zip' => p006eEnv('WPE_P006_FREE_ZIP_PATH'),
        'pro_zip' => p006eEnv('WPE_P006_PRO_ZIP_PATH'),
        'evidence_dir' => $evidenceDir,
        'network_log' => p006eEnv('WPE_P006_NETWORK_LOG'),
        'request_context' => $context,
    ];
}

/** @param array<string,mixed> $payload */
function p006eWriteJson(string $path, array $payload): void
{
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        p006eFail("Unable to create JSON output directory: {$directory}");
    }
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
    p006eAssert(file_put_contents($path, $json) !== false, "Unable to write evidence JSON: {$path}");
}

/** @return array<string,mixed> */
function p006eReadJson(string $path): array
{
    $content = file_get_contents($path);
    p006eAssert($content !== false, "Unable to read evidence JSON: {$path}");
    $decoded = json_decode((string) $content, true, flags: JSON_THROW_ON_ERROR);
    p006eAssert(is_array($decoded), "Expected JSON object at {$path}");
    return $decoded;
}

function p006eHashFile(string $path): string
{
    p006eAssert(is_file($path) && filesize($path) > 0, "Required artifact is missing or empty: {$path}");
    $hash = hash_file('sha256', $path);
    p006eAssert(is_string($hash), "Unable to hash artifact: {$path}");
    return $hash;
}

/** @return array{file_count:int,mismatches:list<string>} */
function p006eVerifyInstalledZip(string $zipPath, string $zipPrefix, string $installedRoot): array
{
    p006eAssert(class_exists(ZipArchive::class), 'ZipArchive is required for installed-byte verification.');
    p006eAssert(is_dir($installedRoot), "Installed plugin root is missing: {$installedRoot}");

    $zip = new ZipArchive();
    p006eAssert($zip->open($zipPath) === true, "Unable to open immutable candidate ZIP: {$zipPath}");
    $expected = [];
    $mismatches = [];
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $name = $zip->getNameIndex($index);
        if (!is_string($name) || !str_starts_with($name, $zipPrefix)) {
            $mismatches[] = is_string($name) ? "unexpected-prefix:{$name}" : "index:{$index}";
            continue;
        }
        if (str_ends_with($name, '/')) {
            continue;
        }
        $relative = str_replace('\\', '/', substr($name, strlen($zipPrefix)));
        if ($relative === '') {
            continue;
        }
        $expected[] = $relative;
        $installed = $installedRoot . '/' . $relative;
        if (!is_file($installed)) {
            $mismatches[] = "missing:{$relative}";
            continue;
        }
        $bytes = $zip->getFromIndex($index);
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
    p006eAssert($mismatches === [], 'Installed candidate differs from immutable ZIP: ' . implode(', ', $mismatches));
    p006eAssert($expected === $actual, 'Installed candidate file set differs from immutable ZIP.');

    return ['file_count' => count($actual), 'mismatches' => []];
}

/** @param array<string,mixed> $input @return array<string,mixed> */
function p006eArtifactIdentity(array $input): array
{
    $freeHash = p006eHashFile((string) $input['free_zip']);
    $proHash = p006eHashFile((string) $input['pro_zip']);
    p006eAssert(hash_equals((string) $input['free_sha256'], $freeHash), 'Free candidate hash drift detected.');
    p006eAssert(hash_equals((string) $input['pro_sha256'], $proHash), 'Pro candidate hash drift detected.');
    $pairId = hash('sha256', "free:{$freeHash}\npro:{$proHash}\n");
    p006eAssert(hash_equals((string) $input['pair_id_sha256'], $pairId), 'Free/Pro pair identity mismatch.');

    $freeRoot = (string) $input['wordpress_dir'] . '/wp-content/plugins/wpessential';
    $proRoot = (string) $input['wordpress_dir'] . '/wp-content/plugins/wpessential-pro';
    p006eAssert(is_file($freeRoot . '/wpessential.php'), 'Packaged Free plugin is not installed canonically.');
    p006eAssert(is_file($proRoot . '/wpessential-pro.php'), 'Packaged Pro plugin is not installed canonically.');
    $free = p006eVerifyInstalledZip((string) $input['free_zip'], 'wpessential/', $freeRoot);
    $pro = p006eVerifyInstalledZip((string) $input['pro_zip'], 'wpessential-pro/', $proRoot);

    return [
        'source_sha' => $input['source_sha'],
        'free_sha256' => $freeHash,
        'pro_sha256' => $proHash,
        'pair_id_sha256' => $pairId,
        'installed_free_matches_zip' => true,
        'installed_pro_matches_zip' => true,
        'installed_free_file_count' => $free['file_count'],
        'installed_pro_file_count' => $pro['file_count'],
    ];
}

/** @param array<string,mixed> $input */
function p006eEnsureNetworkProbe(array $input): void
{
    $dir = (string) $input['wordpress_dir'] . '/wp-content/mu-plugins';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        p006eFail('Unable to create disposable MU-plugin directory.');
    }
    $plugin = <<<'PHP'
<?php
if (!defined('WPE_P006E_NETWORK_DENY_PROBE_ACTIVE')) {
    define('WPE_P006E_NETWORK_DENY_PROBE_ACTIVE', true);
}
add_filter('pre_http_request', static function ($preempt, $parsedArgs, $url) {
    $log = trim((string) getenv('WPE_P006_NETWORK_LOG'));
    if ($log !== '') {
        file_put_contents($log, (string) $url . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    return new WP_Error('p006_wave1e_network_blocked', 'Outbound HTTP is denied during P-006 Wave 1E runtime evidence.');
}, PHP_INT_MIN, 3);
PHP;
    p006eAssert(
        file_put_contents($dir . '/p006-wave1e-network-deny.php', $plugin . PHP_EOL) !== false,
        'Unable to install Wave 1E runtime network-deny probe.'
    );
}

function p006eResetFile(string $path): void
{
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        p006eFail("Unable to create log directory: {$directory}");
    }
    p006eAssert(file_put_contents($path, '') !== false, "Unable to reset log: {$path}");
}

/** @return list<string> */
function p006eNetworkAttempts(array $input): array
{
    $path = (string) $input['network_log'];
    if (!is_file($path)) {
        return [];
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return is_array($lines) ? array_values(array_map('strval', $lines)) : [];
}

function p006eServerEnvelope(string $context): void
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

function p006eApplyContext(string $context): void
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
function p006eEnvironment(array $input): array
{
    global $wp_version, $wpdb;
    p006eAssert(isset($wp_version) && is_string($wp_version), 'WordPress version is unavailable after core boot.');
    p006eAssert(isset($wpdb) && $wpdb instanceof wpdb, 'WordPress database adapter is unavailable.');
    $php = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
    $mysql = method_exists($wpdb, 'db_version') ? (string) $wpdb->db_version() : '';
    p006eAssert($wp_version === $input['expected_wordpress'], "WordPress cell mismatch: {$wp_version}.");
    p006eAssert($php === $input['expected_php'], "PHP cell mismatch: {$php}.");
    p006eAssert(str_starts_with($mysql, (string) $input['expected_mysql']), "MySQL cell mismatch: {$mysql}.");
    return [
        'wordpress' => $wp_version,
        'php' => PHP_VERSION,
        'php_major_minor' => $php,
        'mysql' => $mysql,
        'sapi' => PHP_SAPI,
    ];
}

/** @return list<string> */
function p006eActivePlugins(): array
{
    $plugins = get_option('active_plugins', []);
    return is_array($plugins) ? array_values(array_map('strval', $plugins)) : [];
}

/** @return list<string> */
function p006eIncludedProFiles(array $input): array
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
function p006eProStateSnapshot(array $input): array
{
    $compatibility = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    return [
        'package_active_constant_defined' => defined('WPE_PRO_PACKAGE_ACTIVE'),
        'compatibility_global_present' => is_array($compatibility),
        'compatibility_state' => is_array($compatibility) ? ($compatibility['state'] ?? null) : null,
        'premium_boot_allowed' => is_array($compatibility) ? (($compatibility['premium_boot_allowed'] ?? false) === true) : false,
        'premium_migrations_allowed' => is_array($compatibility) ? (($compatibility['premium_migrations_allowed'] ?? false) === true) : false,
        'compatibility_state_constant_defined' => defined('WPE_PRO_COMPATIBILITY_STATE'),
        'compatibility_state_constant' => defined('WPE_PRO_COMPATIBILITY_STATE') ? (string) constant('WPE_PRO_COMPATIBILITY_STATE') : null,
        'membership_class_loaded' => class_exists(\WPEssential\Modules\Membership\MembershipModule::class, false),
        'included_pro_files' => p006eIncludedProFiles($input),
    ];
}

/** @return array<string,mixed> */
function p006eAssertFreeOnlyRuntime(array $input): array
{
    $freePlugin = 'wpessential/wpessential.php';
    $proPlugin = 'wpessential-pro/wpessential-pro.php';
    p006eAssert(is_plugin_active($freePlugin), 'FP-17 requires packaged Free to be active.');
    p006eAssert(!is_plugin_active($proPlugin), 'FP-17 requires installed packaged Pro to remain inactive.');
    p006eAssert(p006eIncludedProFiles($input) === [], 'Inactive installed Pro contributed executable files to the request.');
    p006eAssert(!isset($GLOBALS['wpe_pro_compatibility_result']), 'Inactive installed Pro published compatibility state.');
    foreach ([
        'WPE_PRO_PACKAGE_ACTIVE',
        'WPE_PRO_COMPATIBILITY_STATE',
        'WPE_PRO_COMPATIBILITY_BOOT_ALLOWED',
        'WPE_PRO_COMPATIBILITY_MIGRATIONS_ALLOWED',
    ] as $constant) {
        p006eAssert(!defined($constant), "Inactive installed Pro manufactured runtime state: {$constant}");
    }
    p006eAssert(
        !class_exists(\WPEssential\Modules\Membership\MembershipModule::class),
        'Inactive installed Pro unexpectedly resolves premium Membership implementation.'
    );
    p006eAssert(class_exists(\WPEssential\Bootstrap\Plugin::class), 'Free Platform bootstrap class did not resolve.');
    $kernel = \WPEssential\Bootstrap\Plugin::kernel();
    p006eAssert($kernel instanceof \WPEssential\Kernel\Kernel && $kernel->isBooted(), 'Free kernel did not boot in FP-17.');
    $modules = $kernel->modules();
    p006eAssert($modules->has('custom-post-types') && $modules->has('taxonomies'), 'Free owner modules are missing in FP-17.');
    $unexpected = [];
    foreach (P006E_PRO_MODULES as $module) {
        if ($modules->has($module)) {
            $unexpected[] = $module;
        }
    }
    p006eAssert($unexpected === [], 'Inactive installed Pro registered premium modules: ' . implode(', ', $unexpected));

    return [
        'free_active' => true,
        'pro_installed' => true,
        'pro_active' => false,
        'kernel_booted' => true,
        'free_custom_post_types_present' => true,
        'free_taxonomies_present' => true,
        'unexpected_pro_modules' => [],
        'pro_compatibility_present' => false,
        'pro_membership_class_resolvable' => false,
        'included_pro_files' => [],
        'migration_admission_observed' => false,
    ];
}

/** @return array<string,mixed> */
function p006ePrepare(array $input): array
{
    $artifact = p006eArtifactIdentity($input);
    p006eAssert(is_file((string) $input['wordpress_dir'] . '/wp-config.php'), 'Disposable wp-config.php is missing.');
    p006eEnsureNetworkProbe($input);
    p006eResetFile((string) $input['network_log']);
    p006eServerEnvelope('activation');
    require (string) $input['wordpress_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    p006eAssert(defined('WPE_P006E_NETWORK_DENY_PROBE_ACTIVE'), 'Wave 1E runtime network-deny probe did not load.');
    p006eAssert(is_blog_installed(), 'Disposable WordPress must be installed before Wave 1E activation evidence.');
    $environment = p006eEnvironment($input);

    $freePlugin = 'wpessential/wpessential.php';
    $proPlugin = 'wpessential-pro/wpessential-pro.php';
    if (is_plugin_active($proPlugin)) {
        deactivate_plugins($proPlugin, true, false);
    }
    if (is_plugin_active($freePlugin)) {
        deactivate_plugins($freePlugin, true, false);
    }
    p006eAssert(!is_plugin_active($freePlugin) && !is_plugin_active($proPlugin), 'Wave 1E must begin with both candidates inactive.');
    p006eResetFile((string) $input['network_log']);

    $sequence = [];
    $platformBehavior = null;
    if ($input['fixture_id'] === 'FP-17') {
        $activation = activate_plugin($freePlugin, '', false, true);
        p006eAssert(!is_wp_error($activation) && is_plugin_active($freePlugin), 'WordPress activation API rejected packaged Free candidate in FP-17.');
        p006eAssert(!is_plugin_active($proPlugin), 'FP-17 unexpectedly activated installed Pro.');
        $sequence[] = ['step' => 'activate_free_only', 'result' => 'success'];
        p006eAssert(p006eIncludedProFiles($input) === [], 'FP-17 activation sequencing included inactive Pro files.');
        p006eAssert(!isset($GLOBALS['wpe_pro_compatibility_result']), 'FP-17 activation sequencing published Pro compatibility while Pro is inactive.');
        p006eAssert(!defined('WPE_PRO_PACKAGE_ACTIVE'), 'FP-17 activation sequencing loaded the inactive Pro package.');
        p006eAssert(!class_exists(\WPEssential\Modules\Membership\MembershipModule::class), 'FP-17 activation sequencing resolves premium implementation.');
        $platformBehavior = ['mode' => 'free_active_pro_installed_inactive'];
    } else {
        $activation = activate_plugin($proPlugin, '', false, true);
        if (is_wp_error($activation)) {
            p006eAssert(!is_plugin_active($proPlugin), 'WordPress reported FP-18 dependency error but Pro became active.');
            p006eAssert(!is_plugin_active($freePlugin), 'FP-18 dependency-blocked state unexpectedly activated Free.');
            p006eAssert($activation->get_error_code() === 'plugin_missing_dependencies', 'FP-18 expected WordPress plugin_missing_dependencies when Pro activation is blocked.');
            p006eAssert(p006eIncludedProFiles($input) === [], 'Dependency-blocked FP-18 included Pro executable files.');
            p006eAssert(!isset($GLOBALS['wpe_pro_compatibility_result']), 'Dependency-blocked FP-18 published Pro compatibility state.');
            p006eAssert(!defined('WPE_PRO_PACKAGE_ACTIVE'), 'Dependency-blocked FP-18 loaded Pro package source.');
            $platformBehavior = [
                'mode' => 'blocked_by_wordpress_dependency_validation',
                'wp_error_code' => $activation->get_error_code(),
                'wp_error_message' => $activation->get_error_message(),
            ];
            $sequence[] = ['step' => 'activate_pro_without_free', 'result' => 'blocked', 'error_code' => $activation->get_error_code()];
        } else {
            p006eAssert(is_plugin_active($proPlugin), 'FP-18 reachable activation returned success but Pro is not active.');
            p006eAssert(!is_plugin_active($freePlugin), 'FP-18 reachable state unexpectedly activated Free.');
            $platformBehavior = ['mode' => 'pro_active_without_free_reachable'];
            $sequence[] = ['step' => 'activate_pro_without_free', 'result' => 'success'];
        }
    }

    $attempts = p006eNetworkAttempts($input);
    p006eAssert($attempts === [], 'Unexpected outbound WordPress HTTP during Wave 1E activation sequencing: ' . implode(', ', $attempts));
    return [
        'status' => 'PASS',
        'phase' => 'activation_sequence',
        'fixture_id' => $input['fixture_id'],
        'cell_id' => $input['cell_id'],
        'expected_environment' => ['wordpress' => $input['expected_wordpress'], 'php' => $input['expected_php'], 'mysql' => $input['expected_mysql']],
        'actual_environment' => $environment,
        'artifact_identity' => $artifact,
        'activation_sequence' => $sequence,
        'platform_behavior' => $platformBehavior,
        'active_plugins_after_sequence' => p006eActivePlugins(),
        'pro_state_after_sequence' => p006eProStateSnapshot($input),
        'runtime_network_attempts' => $attempts,
    ];
}

/** @return array<string,mixed> */
function p006eVerify(array $input): array
{
    $context = (string) $input['request_context'];
    $artifact = p006eArtifactIdentity($input);
    $activation = p006eReadJson((string) $input['evidence_dir'] . '/activation.json');
    p006eAssert(($activation['status'] ?? null) === 'PASS', 'Activation evidence is not PASS before request verification.');
    p006eEnsureNetworkProbe($input);
    p006eResetFile((string) $input['network_log']);
    p006eServerEnvelope($context);
    p006eApplyContext($context);
    require (string) $input['wordpress_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    p006eAssert(defined('WPE_P006E_NETWORK_DENY_PROBE_ACTIVE'), 'Wave 1E runtime network-deny probe did not load.');
    $environment = p006eEnvironment($input);

    $runtime = [];
    if ($input['fixture_id'] === 'FP-17') {
        $runtime = p006eAssertFreeOnlyRuntime($input);
    } else {
        $freePlugin = 'wpessential/wpessential.php';
        $proPlugin = 'wpessential-pro/wpessential-pro.php';
        p006eAssert(!is_plugin_active($freePlugin), 'FP-18 requires Free to remain inactive/missing from the active plugin set.');
        $mode = (string) ($activation['platform_behavior']['mode'] ?? '');
        p006eAssert(
            in_array($mode, ['blocked_by_wordpress_dependency_validation', 'pro_active_without_free_reachable'], true),
            'FP-18 activation evidence has an unsupported platform behavior.'
        );

        $includedPro = p006eIncludedProFiles($input);
        $compatibility = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
        if ($mode === 'blocked_by_wordpress_dependency_validation') {
            p006eAssert(!is_plugin_active($proPlugin), 'Dependency-blocked FP-18 unexpectedly has Pro active in a fresh request.');
            p006eAssert($includedPro === [], 'Dependency-blocked FP-18 loaded Pro files in a fresh request.');
            p006eAssert(!is_array($compatibility), 'Dependency-blocked FP-18 published compatibility state in a fresh request.');
            p006eAssert(!defined('WPE_PRO_PACKAGE_ACTIVE'), 'Dependency-blocked FP-18 loaded Pro package state in a fresh request.');
            p006eAssert(!class_exists(\WPEssential\Bootstrap\Plugin::class, false), 'Dependency-blocked FP-18 unexpectedly booted Free.');
            p006eAssert(!class_exists(\WPEssential\Modules\Membership\MembershipModule::class, false), 'Dependency-blocked FP-18 loaded premium Membership implementation.');
            $runtime = [
                'platform_mode' => $mode,
                'free_active' => false,
                'pro_active' => false,
                'pro_files_loaded' => false,
                'compatibility_published' => false,
                'premium_runtime_present' => false,
                'migration_admission_observed' => false,
            ];
        } else {
            p006eAssert(is_plugin_active($proPlugin), 'Reachable FP-18 activation did not remain recorded as active.');
            if ($includedPro === []) {
                p006eAssert(!is_array($compatibility), 'Platform-suppressed FP-18 unexpectedly published compatibility state without loading Pro.');
                p006eAssert(!defined('WPE_PRO_PACKAGE_ACTIVE'), 'Platform-suppressed FP-18 manufactured Pro package state without loading Pro.');
                $runtime = [
                    'platform_mode' => 'platform_suppressed_active_dependent_plugin',
                    'free_active' => false,
                    'pro_active' => true,
                    'pro_files_loaded' => false,
                    'compatibility_published' => false,
                    'premium_runtime_present' => false,
                    'migration_admission_observed' => false,
                ];
            } else {
                p006eAssert(is_array($compatibility), 'Reachable loaded FP-18 did not publish fail-closed compatibility state.');
                p006eAssert(($compatibility['state'] ?? null) === 'free_missing', 'Reachable loaded FP-18 compatibility state is not free_missing.');
                p006eAssert(($compatibility['premium_boot_allowed'] ?? true) === false, 'Reachable loaded FP-18 incorrectly permits premium boot.');
                p006eAssert(($compatibility['premium_migrations_allowed'] ?? true) === false, 'Reachable loaded FP-18 incorrectly permits premium migrations.');
                p006eAssert(defined('WPE_PRO_PACKAGE_ACTIVE') && WPE_PRO_PACKAGE_ACTIVE === true, 'Reachable loaded FP-18 did not expose Pro package presence.');
                p006eAssert(defined('WPE_PRO_COMPATIBILITY_STATE') && WPE_PRO_COMPATIBILITY_STATE === 'free_missing', 'Reachable loaded FP-18 compatibility mirror is not free_missing.');
                p006eAssert(!class_exists(\WPEssential\Bootstrap\Plugin::class, false), 'Reachable loaded FP-18 unexpectedly booted Free.');
                p006eAssert(!class_exists(\WPEssential\Modules\Membership\MembershipModule::class, false), 'Reachable loaded FP-18 loaded premium Membership implementation.');
                $runtime = [
                    'platform_mode' => 'pro_loaded_fail_closed_free_missing',
                    'free_active' => false,
                    'pro_active' => true,
                    'pro_files_loaded' => true,
                    'compatibility_published' => true,
                    'compatibility_state' => 'free_missing',
                    'premium_boot_allowed' => false,
                    'premium_migrations_allowed' => false,
                    'premium_runtime_present' => false,
                    'migration_admission_observed' => false,
                ];
            }
        }
    }

    $attempts = p006eNetworkAttempts($input);
    p006eAssert($attempts === [], 'Unexpected outbound WordPress HTTP during Wave 1E runtime boot: ' . implode(', ', $attempts));
    return [
        'status' => 'PASS',
        'phase' => 'request_context',
        'fixture_id' => $input['fixture_id'],
        'cell_id' => $input['cell_id'],
        'request_context' => $context,
        'context_constants' => [
            'WP_ADMIN' => defined('WP_ADMIN') ? (bool) constant('WP_ADMIN') : false,
            'REST_REQUEST' => defined('REST_REQUEST') ? (bool) constant('REST_REQUEST') : false,
            'DOING_CRON' => defined('DOING_CRON') ? (bool) constant('DOING_CRON') : false,
            'WP_CLI' => defined('WP_CLI') ? (bool) constant('WP_CLI') : false,
        ],
        'expected_environment' => ['wordpress' => $input['expected_wordpress'], 'php' => $input['expected_php'], 'mysql' => $input['expected_mysql']],
        'actual_environment' => $environment,
        'artifact_identity' => $artifact,
        'runtime' => $runtime,
        'pro_state' => p006eProStateSnapshot($input),
        'active_plugins' => p006eActivePlugins(),
        'runtime_network_attempts' => $attempts,
    ];
}

/** @return array<string,mixed> */
function p006eAggregate(array $input): array
{
    $paths = ['activation' => (string) $input['evidence_dir'] . '/activation.json'];
    foreach (P006E_CONTEXTS as $context) {
        $paths[$context] = (string) $input['evidence_dir'] . "/{$context}.json";
    }
    $records = [];
    $missing = [];
    foreach ($paths as $name => $path) {
        if (!is_file($path)) {
            $missing[] = $name;
            continue;
        }
        $records[$name] = p006eReadJson($path);
    }

    $failed = [];
    $attempts = [];
    foreach ($records as $name => $record) {
        foreach (($record['runtime_network_attempts'] ?? []) as $url) {
            $attempts[] = (string) $url;
        }
        $identity = $record['artifact_identity'] ?? null;
        $identityMatches = is_array($identity)
            && ($identity['source_sha'] ?? null) === $input['source_sha']
            && ($identity['free_sha256'] ?? null) === $input['free_sha256']
            && ($identity['pro_sha256'] ?? null) === $input['pro_sha256']
            && ($identity['pair_id_sha256'] ?? null) === $input['pair_id_sha256'];
        if (($record['status'] ?? null) !== 'PASS' || !$identityMatches) {
            $failed[] = $name;
        }
    }

    $status = $missing === [] && $failed === [] && count($records) === 6 ? 'PASS' : 'FAIL';
    return [
        'protocol' => 'P-006',
        'wave' => '1E-inactive-pro-dependency-state-runtime',
        'fixture_id' => $input['fixture_id'],
        'cell_id' => $input['cell_id'],
        'status' => $status,
        'expected_environment' => ['wordpress' => $input['expected_wordpress'], 'php' => $input['expected_php'], 'mysql' => $input['expected_mysql']],
        'actual_environment' => $records['activation']['actual_environment'] ?? null,
        'source_sha' => $input['source_sha'],
        'artifacts' => [
            'free_sha256' => $input['free_sha256'],
            'pro_sha256' => $input['pro_sha256'],
            'pair_id_sha256' => $input['pair_id_sha256'],
        ],
        'activation' => $records['activation'] ?? null,
        'request_contexts' => array_intersect_key($records, array_flip(P006E_CONTEXTS)),
        'missing_records' => $missing,
        'failed_records' => $failed,
        'summary' => [
            'matrix_cell_executed' => 1,
            'matrix_cell_passed' => $status === 'PASS' ? 1 : 0,
            'matrix_cell_failed' => $status === 'PASS' ? 0 : 1,
            'all_required_contexts_terminal' => $missing === [],
            'formal_fixture_counter_delta' => 'deferred_to_cross_cell_reconciliation',
        ],
        'side_effect_profile' => [
            'real_wordpress_boot' => true,
            'disposable_database_install_and_activation' => true,
            'inactive_pro_runtime_contribution_observed' => false,
            'migration_execution_performed' => false,
            'runtime_network_attempts_detected' => $attempts !== [],
            'runtime_network_attempts' => array_values(array_unique($attempts)),
            'live_provider_or_billing' => false,
            'remote_entitlement_execution' => false,
            'production_credentials_or_data' => false,
            'destructive_or_irreversible_operations' => false,
        ],
        'certification_boundary' => [
            'temporary_p001_matrix_approval_id' => 'GOV-OWNER-CONSENT-P001-TEMP-P006-003',
            'permanent_p001_certified' => false,
            'free_pro_pair_certified' => false,
            'runtime_certified' => false,
            'adr_0010_status' => 'Proposed',
            'adjacent_certifications_promoted' => false,
            'fp19_or_later_executed' => false,
        ],
    ];
}

/** @return array<string,mixed> */
function p006eFailureRecord(array $input, string $phase, Throwable $exception): array
{
    return [
        'status' => 'FAIL',
        'phase' => $phase,
        'fixture_id' => $input['fixture_id'] ?? null,
        'cell_id' => $input['cell_id'] ?? null,
        'request_context' => $input['request_context'] ?? null,
        'expected_environment' => [
            'wordpress' => $input['expected_wordpress'] ?? null,
            'php' => $input['expected_php'] ?? null,
            'mysql' => $input['expected_mysql'] ?? null,
        ],
        'artifact_identity' => [
            'source_sha' => $input['source_sha'] ?? null,
            'free_sha256' => $input['free_sha256'] ?? null,
            'pro_sha256' => $input['pro_sha256'] ?? null,
            'pair_id_sha256' => $input['pair_id_sha256'] ?? null,
        ],
        'failure' => ['type' => get_class($exception), 'message' => $exception->getMessage()],
        'runtime_network_attempts' => isset($input['network_log']) ? p006eNetworkAttempts($input) : [],
    ];
}

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "P-006 Wave 1E evidence harness is CLI-only.\n");
    exit(1);
}

$mode = $argv[1] ?? '';
$input = [];
try {
    $input = p006eInput($mode);
    if ($mode === 'prepare') {
        $record = p006ePrepare($input);
        p006eWriteJson((string) $input['evidence_dir'] . '/activation.json', $record);
        fwrite(STDOUT, "[p006-wave1e] {$input['fixture_id']} {$input['cell_id']} activation-sequence PASS\n");
        exit(0);
    }
    if ($mode === 'verify') {
        $record = p006eVerify($input);
        $context = (string) $input['request_context'];
        p006eWriteJson((string) $input['evidence_dir'] . "/{$context}.json", $record);
        fwrite(STDOUT, "[p006-wave1e] {$input['fixture_id']} {$input['cell_id']} {$context} PASS\n");
        exit(0);
    }
    $summary = p006eAggregate($input);
    p006eWriteJson((string) $input['evidence_dir'] . '/summary.json', $summary);
    fwrite(STDOUT, "[p006-wave1e] {$input['fixture_id']} {$input['cell_id']} aggregate {$summary['status']}\n");
    exit($summary['status'] === 'PASS' ? 0 : 1);
} catch (Throwable $exception) {
    $phase = $mode === 'verify' ? 'request_context' : ($mode === 'aggregate' ? 'aggregate' : 'activation_sequence');
    if ($input !== [] && isset($input['evidence_dir'])) {
        $path = (string) $input['evidence_dir'] . '/failure.json';
        if ($mode === 'prepare') {
            $path = (string) $input['evidence_dir'] . '/activation.json';
        } elseif ($mode === 'verify' && isset($input['request_context'])) {
            $path = (string) $input['evidence_dir'] . '/' . $input['request_context'] . '.json';
        }
        try {
            p006eWriteJson($path, p006eFailureRecord($input, $phase, $exception));
        } catch (Throwable) {
        }
    }
    fwrite(STDERR, "[p006-wave1e] FAIL: {$exception->getMessage()}\n");
    exit(1);
}
