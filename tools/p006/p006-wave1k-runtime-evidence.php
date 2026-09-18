<?php

declare(strict_types=1);

const P006K_GRANT = 'GOV-P001-CF-TEMP-009';
const P006K_PRO_MODULES = [
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

function kOptionalEnv(string $key): string
{
    return trim((string) getenv($key));
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

/** @param array<string,mixed> $value */
function kWrite(string $path, array $value): void
{
    if (!is_dir(dirname($path))) {
        kAssert(mkdir(dirname($path), 0775, true) || is_dir(dirname($path)), "Unable to create evidence directory");
    }

    $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    kAssert(file_put_contents($path, $encoded . PHP_EOL) !== false, "Unable to write evidence: {$path}");
}

/** @return array<string,mixed> */
function kRead(string $path): array
{
    kAssert(is_file($path), "Missing JSON evidence: {$path}");
    $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    kAssert(is_array($decoded), "Invalid JSON evidence: {$path}");

    return $decoded;
}

/** @return array<string,mixed> */
function kIdentity(): array
{
    $identity = kRead(kEnv('WPE_P006_IDENTITY_PATH'));
    kAssert(($identity['temporary_approval_id'] ?? null) === P006K_GRANT, 'Temporary approval mismatch');
    kAssert(($identity['source_sha'] ?? null) === kEnv('WPE_P006_SOURCE_SHA'), 'Identity source SHA mismatch');

    $nodes = $identity['nodes'] ?? null;
    kAssert(is_array($nodes), 'Candidate nodes missing');

    $candidateDir = rtrim(kEnv('WPE_P006_CANDIDATE_DIR'), '/\\');
    foreach (['F0', 'F1', 'P0', 'P1'] as $nodeId) {
        $node = $nodes[$nodeId] ?? null;
        kAssert(is_array($node), "Identity node missing: {$nodeId}");
        $artifact = $node['artifact'] ?? null;
        $sha = $node['sha256'] ?? null;
        kAssert(is_string($artifact) && $artifact !== '' && is_string($sha), "{$nodeId}: malformed identity");
        $path = $candidateDir . '/' . $artifact;
        kAssert(hash_equals($sha, kHashFile($path)), "{$nodeId}: downloaded ZIP hash drift; STOP");
    }

    $pairs = $identity['pairs'] ?? null;
    kAssert(is_array($pairs), 'Pair identities missing');

    foreach ([
        'F0_P0' => ['F0', 'P0'],
        'F1_P0' => ['F1', 'P0'],
        'F0_P1' => ['F0', 'P1'],
    ] as $pairKey => [$freeNode, $proNode]) {
        $pair = $pairs[$pairKey] ?? null;
        kAssert(is_array($pair), "Pair identity missing: {$pairKey}");
        $expected = kPair($nodes[$freeNode]['sha256'], $nodes[$proNode]['sha256']);
        kAssert(($pair['pair_id_sha256'] ?? null) === $expected, "{$pairKey}: canonical pair identity drift");
    }

    return $identity;
}

/** @return array{cell_id:string,expected_wp:string,expected_php:string,expected_mysql:string,wp_dir:string,evidence_dir:string,network_log:string} */
function kRuntimeInput(): array
{
    kIdentity();

    $wp = kEnv('WPE_P006_EXPECTED_WP');
    $php = kEnv('WPE_P006_EXPECTED_PHP');
    $mysql = kEnv('WPE_P006_EXPECTED_MYSQL');

    $cell = match (true) {
        $wp === '6.9' && $php === '8.2' && $mysql === '8.4' => 'minimum',
        $wp === '7.1' && $php === '8.5' && $mysql === '8.4' => 'reference',
        default => null,
    };
    kAssert(is_string($cell), 'Runtime cell outside GOV-P001-CF-TEMP-009');

    return [
        'cell_id' => $cell,
        'expected_wp' => $wp,
        'expected_php' => $php,
        'expected_mysql' => $mysql,
        'wp_dir' => rtrim(kEnv('WPE_TEST_WORDPRESS_DIR'), '/\\'),
        'evidence_dir' => rtrim(kEnv('WPE_P006_EVIDENCE_DIR'), '/\\'),
        'network_log' => kEnv('WPE_P006_NETWORK_LOG'),
    ];
}

/** @param array<string,mixed> $in */
function kInstallNetworkProbe(array $in): void
{
    $dir = $in['wp_dir'] . '/wp-content/mu-plugins';
    if (!is_dir($dir)) {
        kAssert(mkdir($dir, 0775, true) || is_dir($dir), 'Unable to create mu-plugin directory');
    }

    $probe = <<<'PHP'
<?php
if (!defined('WPE_P006K_NETWORK_DENY_ACTIVE')) {
    define('WPE_P006K_NETWORK_DENY_ACTIVE', true);
}
add_filter('pre_http_request', static function ($preempt, $args, $url) {
    $log = trim((string) getenv('WPE_P006_NETWORK_LOG'));
    if ($log !== '') {
        file_put_contents($log, (string) $url . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    return new WP_Error('p006_wave1k_network_blocked', 'Outbound HTTP denied during P-006 Wave 1K evidence.');
}, PHP_INT_MIN, 3);
PHP;

    kAssert(
        file_put_contents($dir . '/p006-wave1k-network-deny.php', $probe . PHP_EOL) !== false,
        'Unable to install outbound HTTP deny probe',
    );
}

/** @param array<string,mixed> $in */
function kResetNetworkLog(array $in): void
{
    if (!is_dir(dirname($in['network_log']))) {
        kAssert(mkdir(dirname($in['network_log']), 0775, true) || is_dir(dirname($in['network_log'])), 'Unable to create network log directory');
    }
    kAssert(file_put_contents($in['network_log'], '') !== false, 'Unable to reset network log');
}

/** @param array<string,mixed> $in @return list<string> */
function kNetworkAttempts(array $in): array
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

function kEnvelope(string $uri = '/'): void
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
function kBootWordPress(array $in): void
{
    kEnvelope('/');
    require $in['wp_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    kAssert(defined('WPE_P006K_NETWORK_DENY_ACTIVE'), 'Outbound HTTP deny probe is not active');
}

/** @param array<string,mixed> $in @return array<string,string> */
function kEnvironment(array $in): array
{
    global $wp_version, $wpdb;

    kAssert(isset($wp_version) && is_string($wp_version), 'WordPress version unavailable');
    kAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable');

    $php = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
    $mysql = (string) $wpdb->db_version();

    kAssert($wp_version === $in['expected_wp'], 'WordPress runtime cell mismatch');
    kAssert($php === $in['expected_php'], 'PHP runtime cell mismatch');
    kAssert(str_starts_with($mysql, $in['expected_mysql']), 'MySQL runtime cell mismatch');

    return [
        'wordpress' => $wp_version,
        'php' => PHP_VERSION,
        'mysql' => $mysql,
        'sapi' => PHP_SAPI,
    ];
}

/** @return list<string> */
function kPremiumModuleSet(): array
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
    foreach (P006K_PRO_MODULES as $moduleId) {
        if ($modules->has($moduleId)) {
            $present[] = $moduleId;
        }
    }

    sort($present, SORT_STRING);
    return $present;
}

/** @return list<string> */
function kCompatibilityPersistenceKeys(): array
{
    global $wpdb;
    kAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable for persistence scan');

    $sql = "
        SELECT option_name
        FROM {$wpdb->options}
        WHERE option_name LIKE '%compatib%'
           OR option_name LIKE 'wpe%compat%'
           OR option_name LIKE '_transient_%compat%'
           OR option_name LIKE '_site_transient_%compat%'
        ORDER BY option_name ASC
    ";

    $values = $wpdb->get_col($sql);
    if (!is_array($values)) {
        return [];
    }

    return array_values(array_map('strval', $values));
}

/** @return array<string,string|int> */
function kRuntimeMetadata(): array
{
    $required = [
        'WPE_VERSION',
        'WPE_PLATFORM_API_VERSION',
        'WPE_PLATFORM_SCHEMA_GENERATION',
        'WPE_PRO_VERSION',
        'WPE_PRO_MIN_FREE_VERSION',
        'WPE_PRO_MAX_FREE_VERSION',
        'WPE_PRO_MIN_PLATFORM_API_VERSION',
        'WPE_PRO_MAX_PLATFORM_API_VERSION',
        'WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION',
        'WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION',
        'WPE_PRO_SCHEMA_GENERATION',
    ];

    foreach ($required as $constant) {
        kAssert(defined($constant), "Runtime metadata missing: {$constant}");
    }

    return [
        'free_version' => (string) constant('WPE_VERSION'),
        'platform_api' => (string) constant('WPE_PLATFORM_API_VERSION'),
        'platform_schema' => (int) constant('WPE_PLATFORM_SCHEMA_GENERATION'),
        'pro_version' => (string) constant('WPE_PRO_VERSION'),
        'pro_min_free_version' => (string) constant('WPE_PRO_MIN_FREE_VERSION'),
        'pro_max_free_version' => (string) constant('WPE_PRO_MAX_FREE_VERSION'),
        'pro_min_platform_api' => (string) constant('WPE_PRO_MIN_PLATFORM_API_VERSION'),
        'pro_max_platform_api' => (string) constant('WPE_PRO_MAX_PLATFORM_API_VERSION'),
        'pro_min_platform_schema' => (int) constant('WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION'),
        'pro_max_platform_schema' => (int) constant('WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION'),
        'pro_schema' => (int) constant('WPE_PRO_SCHEMA_GENERATION'),
    ];
}

/** @param array<string,mixed> $metadata @param array<string,mixed> $freeExpected @param array<string,mixed> $proExpected */
function kAssertMetadata(array $metadata, array $freeExpected, array $proExpected): void
{
    kAssert($metadata['free_version'] === $freeExpected['version'], 'Installed Free version mismatch');
    kAssert($metadata['platform_api'] === $freeExpected['platform_api'], 'Installed Platform API mismatch');
    kAssert($metadata['platform_schema'] === $freeExpected['platform_schema'], 'Installed Platform schema mismatch');

    kAssert($metadata['pro_version'] === $proExpected['version'], 'Installed Pro version mismatch');
    kAssert($metadata['pro_min_free_version'] === $proExpected['min_free_version'], 'Installed Pro min Free mismatch');
    kAssert($metadata['pro_max_free_version'] === $proExpected['max_free_version'], 'Installed Pro max Free mismatch');
    kAssert($metadata['pro_min_platform_api'] === $proExpected['min_platform_api'], 'Installed Pro min API mismatch');
    kAssert($metadata['pro_max_platform_api'] === $proExpected['max_platform_api'], 'Installed Pro max API mismatch');
    kAssert($metadata['pro_min_platform_schema'] === $proExpected['min_platform_schema'], 'Installed Pro min schema mismatch');
    kAssert($metadata['pro_max_platform_schema'] === $proExpected['max_platform_schema'], 'Installed Pro max schema mismatch');
    kAssert($metadata['pro_schema'] === $proExpected['schema'], 'Installed Pro schema mismatch');
}

/** @return string */
function kTreeDigestFromDirectory(string $root, string $slug): string
{
    $root = realpath($root);
    kAssert(is_string($root) && is_dir($root), "Unable to resolve payload root: {$slug}");

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item) {
        kAssert(!$item->isLink(), "Nested symlink in staged payload: {$item->getPathname()}");
        if (!$item->isFile()) {
            continue;
        }

        $relative = substr($item->getPathname(), strlen($root) + 1);
        $relative = str_replace('\\', '/', $relative);
        $files[$slug . '/' . $relative] = hash_file('sha256', $item->getPathname());
    }

    ksort($files, SORT_STRING);
    $ctx = hash_init('sha256');
    foreach ($files as $name => $sha) {
        hash_update($ctx, $name . "\0" . $sha . "\n");
    }

    return hash_final($ctx);
}

/** @param array<string,mixed> $in @return array<string,string> */
function kTransport(array $in): array
{
    $freeLink = $in['wp_dir'] . '/wp-content/plugins/wpessential';
    $proLink = $in['wp_dir'] . '/wp-content/plugins/wpessential-pro';

    kAssert(is_link($freeLink), 'Free transport path is not a symlink');
    kAssert(is_link($proLink), 'Pro transport path is not a symlink');

    $freeReal = realpath($freeLink);
    $proReal = realpath($proLink);
    kAssert(is_string($freeReal) && is_string($proReal), 'Unable to resolve transport targets');

    return [
        'free_realpath' => str_replace('\\', '/', $freeReal),
        'pro_realpath' => str_replace('\\', '/', $proReal),
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function kHealth(array $identity, array $in, string $freeNode, string $proNode, string $fixtureId, string $phase): array
{
    $nodes = $identity['nodes'];
    $pairs = $identity['pairs'];
    kAssert(isset($nodes[$freeNode], $nodes[$proNode]), 'Unknown logical candidate node');

    $pairKey = $freeNode . '_' . $proNode;
    kAssert(isset($pairs[$pairKey]), "Unpinned pair requested: {$pairKey}");

    $transport = kTransport($in);
    $freeInstance = kEnv('WPE_P006_EXPECTED_FREE_INSTANCE');
    $proInstance = kEnv('WPE_P006_EXPECTED_PRO_INSTANCE');

    kAssert(
        str_contains($transport['free_realpath'], '/p006-wave1k-packages/' . $freeInstance . '/wpessential'),
        'Free transport instance mismatch',
    );
    kAssert(
        str_contains($transport['pro_realpath'], '/p006-wave1k-packages/' . $proInstance . '/wpessential-pro'),
        'Pro transport instance mismatch',
    );

    $freeTree = kTreeDigestFromDirectory($transport['free_realpath'], 'wpessential');
    $proTree = kTreeDigestFromDirectory($transport['pro_realpath'], 'wpessential-pro');
    kAssert($freeTree === $nodes[$freeNode]['payload_tree_sha256'], "{$freeNode}: installed Free payload tree drift");
    kAssert($proTree === $nodes[$proNode]['payload_tree_sha256'], "{$proNode}: installed Pro payload tree drift");

    $metadata = kRuntimeMetadata();
    kAssertMetadata($metadata, $nodes[$freeNode]['metadata'], $nodes[$proNode]['metadata']);

    $compatibility = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    kAssert(is_array($compatibility), 'Compatibility result is absent');
    kAssert(($compatibility['state'] ?? null) === 'compatible', 'Declared overlap pair did not remain compatible');
    kAssert(($compatibility['premium_boot_allowed'] ?? null) === true, 'Compatible overlap pair denied premium boot');
    kAssert(($compatibility['premium_migrations_allowed'] ?? null) === true, 'Compatible overlap pair denied compatibility-layer migration admission');

    kAssert(is_plugin_active('wpessential/wpessential.php'), 'Free plugin is not active');
    kAssert(is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro plugin is not active');

    $attempts = kNetworkAttempts($in);
    kAssert($attempts === [], 'Unexpected outbound HTTP: ' . implode(', ', $attempts));

    $persistenceKeys = kCompatibilityPersistenceKeys();
    kAssert($persistenceKeys === [], 'Compatibility persistence key(s) appeared: ' . implode(', ', $persistenceKeys));
    kAssert(!wp_using_ext_object_cache(), 'External object cache unexpectedly active in Wave 1K fixture');

    $freeHash = $nodes[$freeNode]['sha256'];
    $proHash = $nodes[$proNode]['sha256'];
    $pairId = kPair($freeHash, $proHash);
    kAssert($pairId === $pairs[$pairKey]['pair_id_sha256'], 'Health pair identity drift');

    return [
        'status' => 'PASS',
        'fixture_id' => $fixtureId,
        'phase' => $phase,
        'cell_id' => $in['cell_id'],
        'environment' => kEnvironment($in),
        'logical_pair' => ['free' => $freeNode, 'pro' => $proNode],
        'artifact_identity' => [
            'free_sha256' => $freeHash,
            'pro_sha256' => $proHash,
            'pair_id_sha256' => $pairId,
            'free_payload_tree_sha256' => $freeTree,
            'pro_payload_tree_sha256' => $proTree,
        ],
        'transport' => [
            'method' => 'quiescent atomic symlink-target rename over exact ZIP-extracted payload',
            'free_instance' => $freeInstance,
            'pro_instance' => $proInstance,
            'free_realpath' => $transport['free_realpath'],
            'pro_realpath' => $transport['pro_realpath'],
            'manual_upload_certified' => false,
            'automatic_updater_or_tuf_certified' => false,
        ],
        'metadata' => $metadata,
        'compatibility' => $compatibility,
        'premium_module_set' => kPremiumModuleSet(),
        'compatibility_persistence_keys' => [],
        'external_object_cache' => false,
        'outbound_http_attempts' => [],
        'network_attempt_count' => 0,
        'fatal_or_error' => null,
        'certification_boundary' => [
            'runtime_evidence_only' => true,
            'pair_certified' => false,
            'runtime_certified' => false,
            'updater_or_tuf_certified' => false,
            'manual_upload_certified' => false,
            'adr_0010' => 'Proposed',
        ],
    ];
}

/** @param array<string,mixed> $in */
function kPrepare(array $in): array
{
    kInstallNetworkProbe($in);
    kResetNetworkLog($in);
    kBootWordPress($in);

    kAssert(is_blog_installed(), 'Disposable WordPress is not installed');

    foreach (['wpessential-pro/wpessential-pro.php', 'wpessential/wpessential.php'] as $plugin) {
        if (is_plugin_active($plugin)) {
            deactivate_plugins($plugin, true, false);
        }
    }

    kResetNetworkLog($in);

    $result = activate_plugin('wpessential/wpessential.php', '', false, true);
    kAssert(!is_wp_error($result) && is_plugin_active('wpessential/wpessential.php'), 'F0 activation failed');

    $result = activate_plugin('wpessential-pro/wpessential-pro.php', '', false, true);
    kAssert(!is_wp_error($result) && is_plugin_active('wpessential-pro/wpessential-pro.php'), 'P0 activation failed');

    $attempts = kNetworkAttempts($in);
    kAssert($attempts === [], 'Unexpected outbound HTTP during baseline activation');
    kAssert(kCompatibilityPersistenceKeys() === [], 'Compatibility persistence appeared during baseline activation');

    return [
        'status' => 'PASS',
        'phase' => 'prepare',
        'cell_id' => $in['cell_id'],
        'environment' => kEnvironment($in),
        'network_attempts' => [],
        'compatibility_persistence_keys' => [],
    ];
}

/** @return array<string,mixed> */
function kNormalizedHealth(array $health): array
{
    return [
        'logical_pair' => $health['logical_pair'] ?? null,
        'artifact_identity' => $health['artifact_identity'] ?? null,
        'metadata' => $health['metadata'] ?? null,
        'compatibility' => $health['compatibility'] ?? null,
        'premium_module_set' => $health['premium_module_set'] ?? null,
        'compatibility_persistence_keys' => $health['compatibility_persistence_keys'] ?? null,
        'network_attempt_count' => $health['network_attempt_count'] ?? null,
    ];
}

/** @param array<string,mixed> $in */
function kAggregateFree(array $in): array
{
    $baseline = kRead($in['evidence_dir'] . '/baseline.json');
    $updated = kRead($in['evidence_dir'] . '/updated.json');
    $retry = kRead($in['evidence_dir'] . '/retry.json');

    foreach ([$baseline, $updated, $retry] as $record) {
        kAssert(($record['status'] ?? null) === 'PASS', 'Free-first health record is not PASS');
    }

    kAssert(($baseline['logical_pair'] ?? null) === ['free' => 'F0', 'pro' => 'P0'], 'Free-first baseline pair mismatch');
    kAssert(($updated['logical_pair'] ?? null) === ['free' => 'F1', 'pro' => 'P0'], 'FP-45 updated pair mismatch');
    kAssert(($retry['logical_pair'] ?? null) === ['free' => 'F1', 'pro' => 'P0'], 'FP-53 retry pair mismatch');

    kAssert(($baseline['compatibility']['state'] ?? null) === 'compatible', 'FP-45 baseline incompatible');
    kAssert(($updated['compatibility']['state'] ?? null) === 'compatible', 'FP-45 updated pair incompatible');
    kAssert(($baseline['premium_module_set'] ?? null) === ($updated['premium_module_set'] ?? null), 'FP-45 premium module set changed across compatible Free-first replacement');
    kAssert(($baseline['metadata']['platform_api'] ?? null) === ($updated['metadata']['platform_api'] ?? null), 'FP-45 Platform API changed unexpectedly');
    kAssert(($baseline['metadata']['platform_schema'] ?? null) === ($updated['metadata']['platform_schema'] ?? null), 'FP-45 Platform schema changed unexpectedly');
    kAssert(($baseline['metadata']['pro_schema'] ?? null) === ($updated['metadata']['pro_schema'] ?? null), 'FP-45 Pro schema changed unexpectedly');

    kAssert(kNormalizedHealth($updated) === kNormalizedHealth($retry), 'FP-53 same-artifact retry changed normalized compatibility health');
    kAssert(
        ($updated['transport']['free_instance'] ?? null) !== ($retry['transport']['free_instance'] ?? null),
        'FP-53 retry did not switch to the independently extracted second F1 instance',
    );

    return [
        'status' => 'PASS',
        'cell_id' => $in['cell_id'],
        'fixture_results' => [
            'FP-45' => [
                'status' => 'PASS',
                'baseline_pair' => $baseline['logical_pair'],
                'updated_pair' => $updated['logical_pair'],
                'compatibility_before' => $baseline['compatibility'],
                'compatibility_after' => $updated['compatibility'],
                'premium_module_set_preserved' => true,
                'api_schema_contract_unchanged' => true,
                'network_attempts' => 0,
            ],
            'FP-53' => [
                'status' => 'PASS',
                'same_exact_artifact_hash' => $updated['artifact_identity']['free_sha256'] === $retry['artifact_identity']['free_sha256'],
                'independent_extraction_instances' => [
                    $updated['transport']['free_instance'],
                    $retry['transport']['free_instance'],
                ],
                'normalized_health_identical' => true,
                'compatibility_persistence_keys_before' => $updated['compatibility_persistence_keys'],
                'compatibility_persistence_keys_after' => $retry['compatibility_persistence_keys'],
                'updater_package_layer_idempotency_certified' => false,
            ],
            'FP-60' => [
                'status' => 'PASS',
                'health_records' => ['baseline.json', 'updated.json', 'retry.json'],
                'exact_artifact_identity_recorded_each_step' => true,
                'diagnostics_are_trust_authority' => false,
            ],
        ],
        'certification_boundary' => [
            'pair_certified' => false,
            'runtime_certified' => false,
            'updater_or_tuf_certified' => false,
            'manual_upload_certified' => false,
            'adr_0010' => 'Proposed',
        ],
    ];
}

/** @param array<string,mixed> $in */
function kAggregatePro(array $in): array
{
    $baseline = kRead($in['evidence_dir'] . '/baseline.json');
    $updated = kRead($in['evidence_dir'] . '/updated.json');

    foreach ([$baseline, $updated] as $record) {
        kAssert(($record['status'] ?? null) === 'PASS', 'Pro-first health record is not PASS');
    }

    kAssert(($baseline['logical_pair'] ?? null) === ['free' => 'F0', 'pro' => 'P0'], 'Pro-first baseline pair mismatch');
    kAssert(($updated['logical_pair'] ?? null) === ['free' => 'F0', 'pro' => 'P1'], 'FP-46 updated pair mismatch');

    kAssert(($baseline['compatibility']['state'] ?? null) === 'compatible', 'FP-46 baseline incompatible');
    kAssert(($updated['compatibility']['state'] ?? null) === 'compatible', 'FP-46 updated pair incompatible');
    kAssert(($baseline['premium_module_set'] ?? null) === ($updated['premium_module_set'] ?? null), 'FP-46 premium module set changed across compatible Pro-first replacement');
    kAssert(($baseline['metadata']['platform_api'] ?? null) === ($updated['metadata']['platform_api'] ?? null), 'FP-46 Platform API changed unexpectedly');
    kAssert(($baseline['metadata']['platform_schema'] ?? null) === ($updated['metadata']['platform_schema'] ?? null), 'FP-46 Platform schema changed unexpectedly');
    kAssert(($baseline['metadata']['pro_schema'] ?? null) === ($updated['metadata']['pro_schema'] ?? null), 'FP-46 Pro schema changed unexpectedly');

    return [
        'status' => 'PASS',
        'cell_id' => $in['cell_id'],
        'fixture_results' => [
            'FP-46' => [
                'status' => 'PASS',
                'baseline_pair' => $baseline['logical_pair'],
                'updated_pair' => $updated['logical_pair'],
                'compatibility_before' => $baseline['compatibility'],
                'compatibility_after' => $updated['compatibility'],
                'premium_module_set_preserved' => true,
                'api_schema_contract_unchanged' => true,
                'network_attempts' => 0,
            ],
            'FP-60' => [
                'status' => 'PASS',
                'health_records' => ['baseline.json', 'updated.json'],
                'exact_artifact_identity_recorded_each_step' => true,
                'diagnostics_are_trust_authority' => false,
            ],
        ],
        'certification_boundary' => [
            'pair_certified' => false,
            'runtime_certified' => false,
            'updater_or_tuf_certified' => false,
            'manual_upload_certified' => false,
            'adr_0010' => 'Proposed',
        ],
    ];
}

$mode = trim((string) getenv('WPE_P006_EXECUTION_MODE'));
if ($mode === '') {
    $mode = $argv[1] ?? '';
}

try {
    $identity = kIdentity();
    $in = kRuntimeInput();

    if ($mode === 'prepare') {
        $result = kPrepare($in);
        kWrite($in['evidence_dir'] . '/prepare.json', $result);
    } elseif ($mode === 'observe') {
        kInstallNetworkProbe($in);
        kResetNetworkLog($in);
        kBootWordPress($in);

        $freeNode = kEnv('WPE_P006_EXPECTED_FREE_NODE');
        $proNode = kEnv('WPE_P006_EXPECTED_PRO_NODE');
        $fixtureId = kEnv('WPE_P006_FIXTURE_ID');
        $phase = kEnv('WPE_P006_PHASE');

        $result = kHealth($identity, $in, $freeNode, $proNode, $fixtureId, $phase);
        $file = kEnv('WPE_P006_EVIDENCE_FILE');
        kWrite($in['evidence_dir'] . '/' . $file, $result);
    } elseif ($mode === 'aggregate-free') {
        $result = kAggregateFree($in);
        kWrite($in['evidence_dir'] . '/summary.json', $result);
    } elseif ($mode === 'aggregate-pro') {
        $result = kAggregatePro($in);
        kWrite($in['evidence_dir'] . '/summary.json', $result);
    } else {
        kFail('Usage: <prepare|observe|aggregate-free|aggregate-pro>');
    }

    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 Wave 1K runtime] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
