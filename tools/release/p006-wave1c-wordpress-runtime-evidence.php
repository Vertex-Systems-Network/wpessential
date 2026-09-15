<?php

declare(strict_types=1);

const P006C_CONTEXTS = ['frontend', 'admin', 'rest', 'cron', 'cli'];
const P006C_PRO_MODULES = [
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

function p006cFail(string $message): never
{
    throw new RuntimeException($message);
}

function p006cEnv(string $name): string
{
    $value = trim((string) getenv($name));
    if ($value === '') {
        p006cFail("Required environment variable is missing: {$name}");
    }

    return $value;
}

function p006cAssert(bool $condition, string $message): void
{
    if (!$condition) {
        p006cFail($message);
    }
}

/** @return array<string,mixed> */
function p006cInput(string $mode): array
{
    if (!in_array($mode, ['prepare', 'verify', 'aggregate'], true)) {
        p006cFail('Usage: php p006-wave1c-wordpress-runtime-evidence.php <prepare|verify|aggregate>');
    }

    $fixture = p006cEnv('WPE_P006_FIXTURE_ID');
    $expectedWp = p006cEnv('WPE_P006_EXPECTED_WP');
    $expectedPhp = p006cEnv('WPE_P006_EXPECTED_PHP');
    $expectedMysql = p006cEnv('WPE_P006_EXPECTED_MYSQL');
    $allowed = [
        'FP-13' => ['wordpress' => '6.9', 'php' => '8.2', 'mysql' => '8.4'],
        'FP-14' => ['wordpress' => '7.1', 'php' => '8.5', 'mysql' => '8.4'],
    ];

    p006cAssert(isset($allowed[$fixture]), 'Wave 1C authorizes only FP-13 and FP-14.');
    p006cAssert(
        $expectedWp === $allowed[$fixture]['wordpress']
            && $expectedPhp === $allowed[$fixture]['php']
            && $expectedMysql === $allowed[$fixture]['mysql'],
        'Requested environment does not match the temporary authorized P-001 matrix cell.'
    );

    $sourceSha = p006cEnv('WPE_P006_SOURCE_SHA');
    $freeHash = p006cEnv('WPE_P006_FREE_SHA256');
    $proHash = p006cEnv('WPE_P006_PRO_SHA256');
    $pairId = p006cEnv('WPE_P006_PAIR_ID');
    p006cAssert(preg_match('/^[0-9a-f]{40}$/', $sourceSha) === 1, 'Source SHA must be exact 40-character lowercase hex.');
    foreach (['Free SHA-256' => $freeHash, 'Pro SHA-256' => $proHash, 'pair ID' => $pairId] as $label => $hash) {
        p006cAssert(preg_match('/^[0-9a-f]{64}$/', $hash) === 1, "{$label} must be exact 64-character lowercase hex.");
    }

    $wpDir = rtrim(p006cEnv('WPE_TEST_WORDPRESS_DIR'), '/\\');
    p006cAssert(is_file($wpDir . '/wp-load.php'), 'Real WordPress fixture is unavailable.');

    $evidenceDir = rtrim(p006cEnv('WPE_P006_EVIDENCE_DIR'), '/\\');
    if (!is_dir($evidenceDir) && !mkdir($evidenceDir, 0775, true) && !is_dir($evidenceDir)) {
        p006cFail("Unable to create evidence directory: {$evidenceDir}");
    }

    $context = null;
    if ($mode === 'verify') {
        $context = p006cEnv('WPE_P006_REQUEST_CONTEXT');
        p006cAssert(in_array($context, P006C_CONTEXTS, true), 'Unsupported request context.');
    }

    return [
        'mode' => $mode,
        'fixture_id' => $fixture,
        'expected_wordpress' => $expectedWp,
        'expected_php' => $expectedPhp,
        'expected_mysql' => $expectedMysql,
        'source_sha' => $sourceSha,
        'free_sha256' => $freeHash,
        'pro_sha256' => $proHash,
        'pair_id_sha256' => $pairId,
        'wordpress_dir' => $wpDir,
        'free_zip' => p006cEnv('WPE_P006_FREE_ZIP_PATH'),
        'pro_zip' => p006cEnv('WPE_P006_PRO_ZIP_PATH'),
        'evidence_dir' => $evidenceDir,
        'network_log' => p006cEnv('WPE_P006_NETWORK_LOG'),
        'request_context' => $context,
    ];
}

/** @param array<string,mixed> $payload */
function p006cWriteJson(string $path, array $payload): void
{
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        p006cFail("Unable to create JSON output directory: {$directory}");
    }

    try {
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
    } catch (JsonException $exception) {
        p006cFail('Unable to encode evidence JSON: ' . $exception->getMessage());
    }

    if (file_put_contents($path, $json) === false) {
        p006cFail("Unable to write evidence JSON: {$path}");
    }
}

/** @return array<string,mixed> */
function p006cReadJson(string $path): array
{
    $content = file_get_contents($path);
    if ($content === false) {
        p006cFail("Unable to read evidence JSON: {$path}");
    }

    try {
        $decoded = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        p006cFail("Invalid evidence JSON at {$path}: {$exception->getMessage()}");
    }

    if (!is_array($decoded)) {
        p006cFail("Expected JSON object at {$path}");
    }

    return $decoded;
}

function p006cHashFile(string $path): string
{
    p006cAssert(is_file($path) && filesize($path) > 0, "Required artifact is missing or empty: {$path}");
    $hash = hash_file('sha256', $path);
    if ($hash === false) {
        p006cFail("Unable to hash artifact: {$path}");
    }

    return $hash;
}

/** @param array<string,mixed> $input
 *  @return array<string,mixed>
 */
function p006cArtifactIdentity(array $input): array
{
    $freeHash = p006cHashFile((string) $input['free_zip']);
    $proHash = p006cHashFile((string) $input['pro_zip']);
    p006cAssert(hash_equals((string) $input['free_sha256'], $freeHash), 'Free candidate hash drift detected.');
    p006cAssert(hash_equals((string) $input['pro_sha256'], $proHash), 'Pro candidate hash drift detected.');

    $pairId = hash('sha256', "free:{$freeHash}\npro:{$proHash}\n");
    p006cAssert(hash_equals((string) $input['pair_id_sha256'], $pairId), 'Free/Pro pair identity mismatch.');

    $installedRoot = (string) $input['wordpress_dir'] . '/wp-content/plugins/wpessential';
    p006cAssert(is_file($installedRoot . '/wpessential.php'), 'Packaged Free plugin is not installed under the canonical plugin directory.');
    p006cAssert(!file_exists((string) $input['wordpress_dir'] . '/wp-content/plugins/wpessential-pro'), 'Pro package must remain absent from the Free-only runtime fixture.');

    if (!class_exists(ZipArchive::class)) {
        p006cFail('ZipArchive is required to verify installed Free candidate bytes.');
    }

    $zip = new ZipArchive();
    p006cAssert($zip->open((string) $input['free_zip']) === true, 'Unable to open immutable Free candidate ZIP.');

    $expectedFiles = [];
    $mismatches = [];
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $name = $zip->getNameIndex($index);
        if (!is_string($name) || !str_starts_with($name, 'wpessential/')) {
            $mismatches[] = is_string($name) ? $name : "index:{$index}";
            continue;
        }
        if (str_ends_with($name, '/')) {
            continue;
        }

        $relative = substr($name, strlen('wpessential/'));
        if ($relative === '') {
            continue;
        }
        $expectedFiles[] = str_replace('\\', '/', $relative);
        $installed = $installedRoot . '/' . $relative;
        if (!is_file($installed)) {
            $mismatches[] = "missing:{$relative}";
            continue;
        }
        $bytes = $zip->getFromIndex($index);
        if (!is_string($bytes)) {
            $mismatches[] = "unreadable:{$relative}";
            continue;
        }
        $installedHash = hash_file('sha256', $installed);
        if ($installedHash === false || !hash_equals(hash('sha256', $bytes), $installedHash)) {
            $mismatches[] = "bytes:{$relative}";
        }
    }
    $zip->close();

    $actualFiles = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($installedRoot, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }
        $relative = substr($file->getPathname(), strlen($installedRoot) + 1);
        $actualFiles[] = str_replace('\\', '/', $relative);
    }
    sort($expectedFiles, SORT_STRING);
    sort($actualFiles, SORT_STRING);
    p006cAssert($mismatches === [], 'Installed Free candidate differs from immutable ZIP: ' . implode(', ', $mismatches));
    p006cAssert($expectedFiles === $actualFiles, 'Installed Free candidate file set differs from immutable ZIP.');

    return [
        'source_sha' => $input['source_sha'],
        'free_sha256' => $freeHash,
        'pro_sha256' => $proHash,
        'pair_id_sha256' => $pairId,
        'installed_free_matches_zip' => true,
        'installed_file_count' => count($actualFiles),
        'pro_directory_present' => false,
    ];
}

/** @param array<string,mixed> $input */
function p006cEnsureWpConfig(array $input): void
{
    $path = (string) $input['wordpress_dir'] . '/wp-config.php';
    if (is_file($path)) {
        return;
    }

    $config = <<<'PHP'
<?php
define('DB_NAME', getenv('WPE_TEST_WP_DB') ?: 'wpessential_test');
define('DB_USER', getenv('WPE_TEST_MYSQL_USER') ?: 'root');
define('DB_PASSWORD', getenv('WPE_TEST_MYSQL_PASSWORD') ?: 'root');
define('DB_HOST', getenv('WPE_TEST_WP_DB_HOST') ?: '127.0.0.1:3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('AUTH_KEY',         'p006-wave1c-auth-key');
define('SECURE_AUTH_KEY',  'p006-wave1c-secure-auth-key');
define('LOGGED_IN_KEY',    'p006-wave1c-logged-in-key');
define('NONCE_KEY',        'p006-wave1c-nonce-key');
define('AUTH_SALT',        'p006-wave1c-auth-salt');
define('SECURE_AUTH_SALT', 'p006-wave1c-secure-auth-salt');
define('LOGGED_IN_SALT',   'p006-wave1c-logged-in-salt');
define('NONCE_SALT',       'p006-wave1c-nonce-salt');
define('WP_DEBUG', false);
define('DISABLE_WP_CRON', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);
define('WP_ENVIRONMENT_TYPE', 'development');
$table_prefix = 'wpep006_';
require_once ABSPATH . 'wp-settings.php';
PHP;

    if (file_put_contents($path, $config . PHP_EOL) === false) {
        p006cFail('Unable to create disposable WordPress configuration.');
    }
}

/** @param array<string,mixed> $input */
function p006cEnsureNetworkDenyMuPlugin(array $input): void
{
    $dir = (string) $input['wordpress_dir'] . '/wp-content/mu-plugins';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        p006cFail('Unable to create disposable MU-plugin directory.');
    }

    $path = $dir . '/p006-wave1c-network-deny.php';
    $plugin = <<<'PHP'
<?php
add_filter(
    'pre_http_request',
    static function ($preempt, $parsedArgs, $url) {
        $log = trim((string) getenv('WPE_P006_NETWORK_LOG'));
        if ($log !== '') {
            file_put_contents($log, (string) $url . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
        return new WP_Error('p006_wave1c_network_blocked', 'Outbound HTTP is denied during P-006 Wave 1C runtime evidence.');
    },
    PHP_INT_MIN,
    3
);
PHP;

    if (file_put_contents($path, $plugin . PHP_EOL) === false) {
        p006cFail('Unable to install disposable runtime network-deny probe.');
    }
}

/** @param array<string,mixed> $input */
function p006cResetNetworkLog(array $input): void
{
    $log = (string) $input['network_log'];
    $directory = dirname($log);
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        p006cFail('Unable to create runtime network-log directory.');
    }
    if (file_put_contents($log, '') === false) {
        p006cFail('Unable to reset runtime network log.');
    }
}

/** @param array<string,mixed> $input
 *  @return string[]
 */
function p006cNetworkAttempts(array $input): array
{
    $log = (string) $input['network_log'];
    if (!is_file($log)) {
        return [];
    }
    $lines = file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return is_array($lines) ? array_values(array_map('strval', $lines)) : [];
}

function p006cApplyContext(string $context): void
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

/** @param array<string,mixed> $input
 *  @return array<string,mixed>
 */
function p006cEnvironment(array $input): array
{
    global $wp_version, $wpdb;

    p006cAssert(isset($wp_version) && is_string($wp_version), 'WordPress version is unavailable after real core boot.');
    p006cAssert(isset($wpdb) && $wpdb instanceof wpdb, 'WordPress database adapter is unavailable.');

    $php = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
    $mysql = method_exists($wpdb, 'db_version') ? (string) $wpdb->db_version() : '';
    p006cAssert($wp_version === $input['expected_wordpress'], "WordPress cell mismatch: expected {$input['expected_wordpress']}, got {$wp_version}.");
    p006cAssert($php === $input['expected_php'], "PHP cell mismatch: expected {$input['expected_php']}, got {$php}.");
    p006cAssert(str_starts_with($mysql, (string) $input['expected_mysql']), "MySQL cell mismatch: expected {$input['expected_mysql']}.x, got {$mysql}.");

    return [
        'wordpress' => $wp_version,
        'php' => PHP_VERSION,
        'php_major_minor' => $php,
        'mysql' => $mysql,
        'sapi' => PHP_SAPI,
    ];
}

/** @param array<string,mixed> $input
 *  @return array<string,mixed>
 */
function p006cPrepare(array $input): array
{
    $artifact = p006cArtifactIdentity($input);
    p006cEnsureWpConfig($input);
    p006cEnsureNetworkDenyMuPlugin($input);
    p006cResetNetworkLog($input);

    require (string) $input['wordpress_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $environment = p006cEnvironment($input);
    if (!is_blog_installed()) {
        $installed = wp_install(
            'WPEssential P-006 Wave 1C',
            'p006_admin',
            'p006-admin@example.test',
            false,
            '',
            'p006-test-password-strong'
        );
        p006cAssert(!is_wp_error($installed), 'Disposable WordPress installation failed.');
    }

    $plugin = 'wpessential/wpessential.php';
    if (is_plugin_active($plugin)) {
        deactivate_plugins($plugin, true, false);
    }
    p006cAssert(!is_plugin_active($plugin), 'Free plugin must start from an inactive state before clean activation.');

    $activation = activate_plugin($plugin, '', false, true);
    p006cAssert(!is_wp_error($activation), 'WordPress activation API rejected the packaged Free candidate.');
    p006cAssert(is_plugin_active($plugin), 'Packaged Free candidate is not active after WordPress activation API success.');

    $networkAttempts = p006cNetworkAttempts($input);
    p006cAssert($networkAttempts === [], 'Unexpected outbound WordPress HTTP attempt during installation/activation: ' . implode(', ', $networkAttempts));

    return [
        'status' => 'PASS',
        'phase' => 'activation',
        'fixture_id' => $input['fixture_id'],
        'expected_environment' => [
            'wordpress' => $input['expected_wordpress'],
            'php' => $input['expected_php'],
            'mysql' => $input['expected_mysql'],
        ],
        'actual_environment' => $environment,
        'artifact_identity' => $artifact,
        'activation' => [
            'plugin' => $plugin,
            'via_wordpress_activation_api' => true,
            'active_after_activation' => true,
        ],
        'runtime_network_attempts' => $networkAttempts,
    ];
}

/** @param array<string,mixed> $input
 *  @return array<string,mixed>
 */
function p006cVerify(array $input): array
{
    $context = (string) $input['request_context'];
    $artifact = p006cArtifactIdentity($input);
    p006cEnsureWpConfig($input);
    p006cEnsureNetworkDenyMuPlugin($input);
    p006cResetNetworkLog($input);
    p006cApplyContext($context);

    require (string) $input['wordpress_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $environment = p006cEnvironment($input);
    $plugin = 'wpessential/wpessential.php';
    p006cAssert(is_plugin_active($plugin), 'Packaged Free candidate is not active in the fresh WordPress request.');
    p006cAssert(class_exists(\WPEssential\Bootstrap\Plugin::class), 'Free Platform bootstrap class did not resolve in real WordPress.');

    $kernel = \WPEssential\Bootstrap\Plugin::kernel();
    p006cAssert($kernel instanceof \WPEssential\Kernel\Kernel, 'WPEssential kernel is unavailable in real WordPress.');
    p006cAssert($kernel->isBooted(), 'WPEssential kernel did not boot in real WordPress.');

    $modules = $kernel->modules();
    p006cAssert($modules->has('custom-post-types'), 'Free Custom Post Types module is missing after real WordPress boot.');
    p006cAssert($modules->has('taxonomies'), 'Free Taxonomies module is missing after real WordPress boot.');

    $unexpectedProModules = [];
    foreach (P006C_PRO_MODULES as $proModule) {
        if ($modules->has($proModule)) {
            $unexpectedProModules[] = $proModule;
        }
    }
    p006cAssert($unexpectedProModules === [], 'Free-only runtime registered Pro modules: ' . implode(', ', $unexpectedProModules));

    foreach ([
        'WPE_PRO_PACKAGE_ACTIVE',
        'WPE_PRO_COMPATIBILITY_STATE',
        'WPE_PRO_COMPATIBILITY_BOOT_ALLOWED',
        'WPE_PRO_COMPATIBILITY_MIGRATIONS_ALLOWED',
    ] as $proConstant) {
        p006cAssert(!defined($proConstant), "Free-only runtime manufactured Pro state constant: {$proConstant}");
    }

    p006cAssert(
        !class_exists(\WPEssential\Modules\Membership\MembershipModule::class),
        'Free-only runtime unexpectedly resolves Pro Membership implementation.'
    );

    $networkAttempts = p006cNetworkAttempts($input);
    p006cAssert($networkAttempts === [], 'Unexpected outbound WordPress HTTP attempt during fresh runtime boot: ' . implode(', ', $networkAttempts));

    return [
        'status' => 'PASS',
        'phase' => 'request_context',
        'fixture_id' => $input['fixture_id'],
        'request_context' => $context,
        'context_constants' => [
            'WP_ADMIN' => defined('WP_ADMIN') ? (bool) constant('WP_ADMIN') : false,
            'REST_REQUEST' => defined('REST_REQUEST') ? (bool) constant('REST_REQUEST') : false,
            'DOING_CRON' => defined('DOING_CRON') ? (bool) constant('DOING_CRON') : false,
            'WP_CLI' => defined('WP_CLI') ? (bool) constant('WP_CLI') : false,
        ],
        'expected_environment' => [
            'wordpress' => $input['expected_wordpress'],
            'php' => $input['expected_php'],
            'mysql' => $input['expected_mysql'],
        ],
        'actual_environment' => $environment,
        'artifact_identity' => $artifact,
        'free_runtime' => [
            'active_plugin' => true,
            'kernel_booted' => true,
            'custom_post_types_present' => true,
            'taxonomies_present' => true,
            'unexpected_pro_modules' => [],
            'pro_package_state_present' => false,
            'pro_membership_class_resolvable' => false,
        ],
        'runtime_network_attempts' => $networkAttempts,
    ];
}

/** @param array<string,mixed> $input
 *  @return array<string,mixed>
 */
function p006cAggregate(array $input): array
{
    $records = [];
    $missing = [];
    $paths = ['activation' => (string) $input['evidence_dir'] . '/activation.json'];
    foreach (P006C_CONTEXTS as $context) {
        $paths[$context] = (string) $input['evidence_dir'] . '/' . $context . '.json';
    }

    foreach ($paths as $name => $path) {
        if (!is_file($path)) {
            $missing[] = $name;
            continue;
        }
        $records[$name] = p006cReadJson($path);
    }

    $failedRecords = [];
    foreach ($records as $name => $record) {
        if (($record['status'] ?? null) !== 'PASS') {
            $failedRecords[] = $name;
            continue;
        }
        $identity = $record['artifact_identity'] ?? null;
        if (!is_array($identity)
            || ($identity['source_sha'] ?? null) !== $input['source_sha']
            || ($identity['free_sha256'] ?? null) !== $input['free_sha256']
            || ($identity['pro_sha256'] ?? null) !== $input['pro_sha256']
            || ($identity['pair_id_sha256'] ?? null) !== $input['pair_id_sha256']) {
            $failedRecords[] = $name . ':identity';
        }
    }

    $status = $missing === [] && $failedRecords === [] && count($records) === 6 ? 'PASS' : 'FAIL';
    $summary = [
        'protocol' => 'P-006',
        'wave' => '1C-real-wordpress-free-only-baseline',
        'fixture_id' => $input['fixture_id'],
        'status' => $status,
        'expected_environment' => [
            'wordpress' => $input['expected_wordpress'],
            'php' => $input['expected_php'],
            'mysql' => $input['expected_mysql'],
        ],
        'source_sha' => $input['source_sha'],
        'artifacts' => [
            'free_sha256' => $input['free_sha256'],
            'pro_sha256' => $input['pro_sha256'],
            'pair_id_sha256' => $input['pair_id_sha256'],
        ],
        'activation' => $records['activation'] ?? null,
        'request_contexts' => array_intersect_key($records, array_flip(P006C_CONTEXTS)),
        'missing_records' => $missing,
        'failed_records' => $failedRecords,
        'summary' => [
            'executed' => 1,
            'passed' => $status === 'PASS' ? 1 : 0,
            'failed' => $status === 'PASS' ? 0 : 1,
            'all_required_contexts_terminal' => $missing === [],
        ],
        'side_effect_profile' => [
            'real_wordpress_boot' => true,
            'disposable_database_install_and_activation' => true,
            'runtime_network_attempts' => false,
            'pro_runtime' => false,
            'provider_or_billing' => false,
            'entitlement' => false,
            'membership_authorization' => false,
            'production_credentials_or_data' => false,
            'destructive_or_irreversible_operations' => false,
        ],
        'certification_boundary' => [
            'temporary_p001_matrix_used' => true,
            'permanent_p001_certified' => false,
            'free_pro_pair_certified' => false,
            'runtime_certified' => false,
            'adr_0010_status' => 'Proposed',
            'adjacent_certifications_promoted' => false,
            'fp15_or_later_executed' => false,
        ],
    ];

    return $summary;
}

function p006cFailureRecord(array $input, string $phase, Throwable $exception): array
{
    return [
        'status' => 'FAIL',
        'phase' => $phase,
        'fixture_id' => $input['fixture_id'] ?? null,
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
        'failure' => [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
        ],
        'runtime_network_attempts' => isset($input['network_log']) ? p006cNetworkAttempts($input) : [],
    ];
}

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "P-006 Wave 1C evidence harness is CLI-only.\n");
    exit(1);
}

$mode = $argv[1] ?? '';
$input = [];
try {
    $input = p006cInput($mode);

    if ($mode === 'prepare') {
        $record = p006cPrepare($input);
        p006cWriteJson((string) $input['evidence_dir'] . '/activation.json', $record);
        fwrite(STDOUT, "[p006-wave1c] {$input['fixture_id']} activation PASS\n");
        exit(0);
    }

    if ($mode === 'verify') {
        $record = p006cVerify($input);
        $context = (string) $input['request_context'];
        p006cWriteJson((string) $input['evidence_dir'] . "/{$context}.json", $record);
        fwrite(STDOUT, "[p006-wave1c] {$input['fixture_id']} {$context} PASS\n");
        exit(0);
    }

    $summary = p006cAggregate($input);
    p006cWriteJson((string) $input['evidence_dir'] . '/summary.json', $summary);
    fwrite(STDOUT, "[p006-wave1c] {$input['fixture_id']} aggregate {$summary['status']}\n");
    exit($summary['status'] === 'PASS' ? 0 : 1);
} catch (Throwable $exception) {
    $phase = $mode === 'verify' ? 'request_context' : ($mode === 'aggregate' ? 'aggregate' : 'activation');
    if ($input !== [] && isset($input['evidence_dir'])) {
        $path = (string) $input['evidence_dir'] . '/failure.json';
        if ($mode === 'prepare') {
            $path = (string) $input['evidence_dir'] . '/activation.json';
        } elseif ($mode === 'verify' && isset($input['request_context'])) {
            $path = (string) $input['evidence_dir'] . '/' . $input['request_context'] . '.json';
        }
        try {
            p006cWriteJson($path, p006cFailureRecord($input, $phase, $exception));
        } catch (Throwable) {
            // Preserve the original failure as the terminal workflow signal.
        }
    }
    fwrite(STDERR, "[p006-wave1c] FAIL: {$exception->getMessage()}\n");
    exit(1);
}
