<?php

declare(strict_types=1);

const P006K_GRANT = 'GOV-P001-CF-TEMP-016';
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
    if (!defined('WPE_PRO_LOCAL_ENTITLEMENT_STATE')) {
        define('WPE_PRO_LOCAL_ENTITLEMENT_STATE', 'pro_active');
    }
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
function rRequiredFreeModuleSet(): array
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
    foreach (['custom-post-types', 'taxonomies'] as $moduleId) {
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
    kAssert(($compatibility['dimension'] ?? null) === 'pair', 'Compatible overlap pair dimension drift');
    kAssert(($compatibility['reason'] ?? null) === 'compatible_local_pair', 'Compatible overlap pair reason drift');
    kAssert(($compatibility['remediation'] ?? null) === 'none', 'Compatible overlap pair remediation drift');
    kAssert(($compatibility['premium_boot_allowed'] ?? null) === true, 'Compatible overlap pair denied premium boot');
    kAssert(($compatibility['premium_migrations_allowed'] ?? null) === true, 'Compatible overlap pair denied compatibility-layer migration admission');

    kAssert(is_plugin_active('wpessential/wpessential.php'), 'Free plugin is not active');
    kAssert(is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro plugin is not active');

    kAssert(defined('WPE_PRO_ENTITLEMENT_STATE') && WPE_PRO_ENTITLEMENT_STATE === 'pro_active', 'Test-local Pro entitlement state drift');
    kAssert(defined('WPE_PRO_PREMIUM_READS_ALLOWED') && WPE_PRO_PREMIUM_READS_ALLOWED === true, 'Pro-active fixture denied premium reads');
    kAssert(defined('WPE_PRO_PREMIUM_MUTATIONS_ALLOWED') && WPE_PRO_PREMIUM_MUTATIONS_ALLOWED === true, 'Pro-active fixture denied premium mutations');
    kAssert(defined('WPE_PRO_COMPATIBILITY_BOOT_ALLOWED') && WPE_PRO_COMPATIBILITY_BOOT_ALLOWED === true, 'Compatible overlap constant denied premium boot');
    kAssert(defined('WPE_PRO_COMPATIBILITY_MIGRATIONS_ALLOWED') && WPE_PRO_COMPATIBILITY_MIGRATIONS_ALLOWED === true, 'Compatible overlap constant denied premium migrations');

    $premiumModules = kPremiumModuleSet();
    $expectedPremiumModules = P006K_PRO_MODULES;
    sort($expectedPremiumModules, SORT_STRING);
    kAssert($premiumModules === $expectedPremiumModules, 'Compatible overlap pair did not register the expected premium module set');

    $requiredFreeModules = rRequiredFreeModuleSet();
    kAssert($requiredFreeModules === ['custom-post-types', 'taxonomies'], 'Compatible overlap pair lost required Free modules');

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
        'entitlement' => [
            'local_test_state' => 'pro_active',
            'effective_state' => (string) WPE_PRO_ENTITLEMENT_STATE,
            'premium_reads_allowed' => WPE_PRO_PREMIUM_READS_ALLOWED === true,
            'premium_mutations_allowed' => WPE_PRO_PREMIUM_MUTATIONS_ALLOWED === true,
        ],
        'required_free_module_set' => $requiredFreeModules,
        'premium_module_set' => $premiumModules,
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

/** @param array<string,mixed> $in @return array<string,mixed> */
function rAggregate(array $in): array
{
    $fp21 = kRead($in['evidence_dir'] . '/fp21.json');
    $fp22 = kRead($in['evidence_dir'] . '/fp22.json');

    foreach (['FP-21' => $fp21, 'FP-22' => $fp22] as $fixture => $record) {
        kAssert(($record['status'] ?? null) === 'PASS', "{$fixture}: observation is not PASS");
        kAssert(($record['fixture_id'] ?? null) === $fixture, "{$fixture}: fixture id drift");
        kAssert(($record['compatibility']['state'] ?? null) === 'compatible', "{$fixture}: pair is not compatible");
        kAssert(($record['compatibility']['dimension'] ?? null) === 'pair', "{$fixture}: compatibility dimension drift");
        kAssert(($record['compatibility']['reason'] ?? null) === 'compatible_local_pair', "{$fixture}: compatibility reason drift");
        kAssert(($record['compatibility']['remediation'] ?? null) === 'none', "{$fixture}: compatibility remediation drift");
        kAssert(($record['compatibility']['premium_boot_allowed'] ?? null) === true, "{$fixture}: premium boot denied");
        kAssert(($record['compatibility']['premium_migrations_allowed'] ?? null) === true, "{$fixture}: premium migrations denied");
        kAssert(($record['entitlement']['effective_state'] ?? null) === 'pro_active', "{$fixture}: local entitlement drift");
        kAssert(($record['entitlement']['premium_reads_allowed'] ?? null) === true, "{$fixture}: premium reads denied");
        kAssert(($record['entitlement']['premium_mutations_allowed'] ?? null) === true, "{$fixture}: premium mutations denied");
        kAssert(($record['required_free_module_set'] ?? null) === ['custom-post-types', 'taxonomies'], "{$fixture}: required Free modules drift");
        $premium = $record['premium_module_set'] ?? [];
        $expectedPremium = P006K_PRO_MODULES;
        sort($expectedPremium, SORT_STRING);
        kAssert($premium === $expectedPremium, "{$fixture}: premium module set drift");
        kAssert(($record['compatibility_persistence_keys'] ?? null) === [], "{$fixture}: compatibility persistence appeared");
        kAssert(($record['external_object_cache'] ?? null) === false, "{$fixture}: external object cache participated");
        kAssert(($record['network_attempt_count'] ?? null) === 0, "{$fixture}: outbound WordPress HTTP observed");
        kAssert(array_key_exists('fatal_or_error', $record) && $record['fatal_or_error'] === null, "{$fixture}: fatal/error observed");
    }

    kAssert(($fp21['logical_pair'] ?? null) === ['free' => 'F1', 'pro' => 'P0'], 'FP-21 exact pair drift');
    kAssert(($fp22['logical_pair'] ?? null) === ['free' => 'F0', 'pro' => 'P1'], 'FP-22 exact pair drift');

    kAssert(($fp22['metadata']['free_version'] ?? null) === '0.1.0-dev', 'FP-22 older Free marketing version drift');
    kAssert(($fp22['metadata']['pro_version'] ?? null) === '0.1.1-test-overlap', 'FP-22 newer Pro marketing version drift');
    kAssert(($fp22['metadata']['platform_api'] ?? null) === '0.1.0', 'FP-22 inferred unexpected newer Platform API');
    kAssert(($fp22['metadata']['platform_schema'] ?? null) === 1, 'FP-22 inferred unexpected newer Platform schema');
    kAssert(($fp22['metadata']['pro_min_platform_api'] ?? null) === '0.1.0', 'FP-22 Pro min API drift');
    kAssert(($fp22['metadata']['pro_max_platform_api'] ?? null) === '0.1.0', 'FP-22 Pro max API drift');

    return [
        'status' => 'PASS',
        'protocol' => 'P-006',
        'wave' => '1R',
        'classification' => 'FORMAL FP-21 + FP-22 EVIDENCE / NON-CERTIFYING',
        'authorization' => P006K_GRANT,
        'source_sha' => kEnv('WPE_P006_SOURCE_SHA'),
        'cell_id' => $in['cell_id'],
        'fixture_results' => [
            'FP-21' => [
                'status' => 'PASS',
                'pair' => $fp21['logical_pair'],
                'artifact_identity' => $fp21['artifact_identity'],
                'compatibility' => $fp21['compatibility'],
                'entitlement' => $fp21['entitlement'],
                'required_free_module_set' => $fp21['required_free_module_set'],
                'premium_module_set' => $fp21['premium_module_set'],
            ],
            'FP-22' => [
                'status' => 'PASS',
                'pair' => $fp22['logical_pair'],
                'artifact_identity' => $fp22['artifact_identity'],
                'compatibility' => $fp22['compatibility'],
                'entitlement' => $fp22['entitlement'],
                'required_free_module_set' => $fp22['required_free_module_set'],
                'premium_module_set' => $fp22['premium_module_set'],
                'newer_pro_did_not_advance_platform_contract' => true,
            ],
        ],
        'network_attempt_count' => 0,
        'compatibility_persistence_keys' => [],
        'formal_fp_fixtures_executed' => ['FP-21', 'FP-22'],
        'accounting_if_terminally_accepted' => [
            'documented' => 144,
            'executed' => 52,
            'pass' => 51,
            'fail' => 0,
            'inconclusive' => 1,
            'certified_pairs' => 0,
            'runtime_certifications' => 0,
        ],
        'fp24_state' => 'N_A_CURRENT_ACCEPTED_CONTRACT_NOT_EXECUTED',
        'fp33_state' => 'INCONCLUSIVE_NOT_EXECUTED_IN_WAVE_1R',
        'product_runtime_source_modified' => false,
        'pair_certified' => false,
        'runtime_certified' => false,
        'provider_or_remote_entitlement_used' => false,
        'updater_or_tuf_certified' => false,
        'adr_0010' => 'Proposed',
        'ga_or_release_authorized' => false,
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
        kAssert(in_array($fixtureId, ['FP-21', 'FP-22'], true), 'Wave 1R fixture outside authorization');
        $phase = kEnv('WPE_P006_PHASE');

        $result = kHealth($identity, $in, $freeNode, $proNode, $fixtureId, $phase);
        $file = kEnv('WPE_P006_EVIDENCE_FILE');
        kWrite($in['evidence_dir'] . '/' . $file, $result);
    } elseif ($mode === 'aggregate') {
        $result = rAggregate($in);
        kWrite($in['evidence_dir'] . '/summary.json', $result);
    } else {
        kFail('Usage: <prepare|observe|aggregate>');
    }

    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 Wave 1R FP-21/22] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
