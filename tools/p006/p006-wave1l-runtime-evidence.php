<?php

declare(strict_types=1);

const P006L_GRANT = 'GOV-P001-CF-TEMP-010';

const P006L_PREMIUM_MODULES = [
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

const P006L_REQUIRED_FREE_MODULES = [
    'custom-post-types',
    'taxonomies',
];

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

/** @param array<string,mixed> $value */
function lWriteJson(string $path, array $value): void
{
    if (!is_dir(dirname($path))) {
        lAssert(mkdir(dirname($path), 0775, true) || is_dir(dirname($path)), "Unable to create evidence directory");
    }

    $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    lAssert(file_put_contents($path, $encoded . PHP_EOL) !== false, "Unable to write evidence: {$path}");
}

/** @return array<string,mixed> */
function lReadJson(string $path): array
{
    lAssert(is_file($path), "Missing JSON evidence: {$path}");
    $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    lAssert(is_array($decoded), "Invalid JSON evidence: {$path}");

    return $decoded;
}

/** @return array<string,mixed> */
function lIdentity(): array
{
    $identity = lReadJson(lEnv('WPE_P006_IDENTITY_PATH'));

    lAssert(($identity['temporary_approval_id'] ?? null) === P006L_GRANT, 'Temporary approval mismatch');
    lAssert(($identity['source_sha'] ?? null) === lEnv('WPE_P006_SOURCE_SHA'), 'Identity source SHA mismatch');

    $nodes = $identity['nodes'] ?? null;
    lAssert(is_array($nodes), 'Candidate nodes missing');

    $candidateDir = rtrim(lEnv('WPE_P006_CANDIDATE_DIR'), '/\\');

    foreach (['F1', 'F2', 'P0', 'P2'] as $nodeId) {
        $node = $nodes[$nodeId] ?? null;
        lAssert(is_array($node), "Identity node missing: {$nodeId}");

        $artifact = $node['artifact'] ?? null;
        $sha = $node['sha256'] ?? null;
        lAssert(is_string($artifact) && $artifact !== '' && is_string($sha), "{$nodeId}: malformed identity");

        $path = $candidateDir . '/' . $artifact;
        lAssert(hash_equals($sha, lHashFile($path)), "{$nodeId}: downloaded ZIP hash drift; STOP");
    }

    $pairs = $identity['pairs'] ?? null;
    lAssert(is_array($pairs), 'Pair identities missing');

    foreach ([
        'F1_P0' => ['F1', 'P0'],
        'F2_P0' => ['F2', 'P0'],
        'F1_P2' => ['F1', 'P2'],
    ] as $pairKey => [$freeNode, $proNode]) {
        $pair = $pairs[$pairKey] ?? null;
        lAssert(is_array($pair), "Pair identity missing: {$pairKey}");

        $expected = lPair($nodes[$freeNode]['sha256'], $nodes[$proNode]['sha256']);
        lAssert(($pair['pair_id_sha256'] ?? null) === $expected, "{$pairKey}: pair identity drift");
    }

    return $identity;
}

/** @return array{cell_id:string,expected_wp:string,expected_php:string,expected_mysql:string,wp_dir:string,evidence_dir:string,network_log:string} */
function lRuntimeInput(): array
{
    lIdentity();

    $wp = lEnv('WPE_P006_EXPECTED_WP');
    $php = lEnv('WPE_P006_EXPECTED_PHP');
    $mysql = lEnv('WPE_P006_EXPECTED_MYSQL');

    $cell = match (true) {
        $wp === '6.9' && $php === '8.2' && $mysql === '8.4' => 'minimum',
        $wp === '7.1' && $php === '8.5' && $mysql === '8.4' => 'reference',
        default => null,
    };

    lAssert(is_string($cell), 'Runtime cell outside GOV-P001-CF-TEMP-010');

    return [
        'cell_id' => $cell,
        'expected_wp' => $wp,
        'expected_php' => $php,
        'expected_mysql' => $mysql,
        'wp_dir' => rtrim(lEnv('WPE_TEST_WORDPRESS_DIR'), '/\\'),
        'evidence_dir' => rtrim(lEnv('WPE_P006_EVIDENCE_DIR'), '/\\'),
        'network_log' => lEnv('WPE_P006_NETWORK_LOG'),
    ];
}

/** @param array<string,mixed> $in */
function lInstallNetworkProbe(array $in): void
{
    $dir = $in['wp_dir'] . '/wp-content/mu-plugins';
    if (!is_dir($dir)) {
        lAssert(mkdir($dir, 0775, true) || is_dir($dir), 'Unable to create mu-plugin directory');
    }

    $probe = <<<'PHP'
<?php
if (!defined('WPE_P006L_NETWORK_DENY_ACTIVE')) {
    define('WPE_P006L_NETWORK_DENY_ACTIVE', true);
}
add_filter('pre_http_request', static function ($preempt, $args, $url) {
    $log = trim((string) getenv('WPE_P006_NETWORK_LOG'));
    if ($log !== '') {
        file_put_contents($log, (string) $url . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    return new WP_Error(
        'p006_wave1l_network_blocked',
        'Outbound HTTP denied during P-006 Wave 1L evidence.'
    );
}, PHP_INT_MIN, 3);
PHP;

    lAssert(
        file_put_contents($dir . '/p006-wave1l-network-deny.php', $probe . PHP_EOL) !== false,
        'Unable to install outbound HTTP deny probe',
    );
}

/** @param array<string,mixed> $in */
function lResetNetworkLog(array $in): void
{
    if (!is_dir(dirname($in['network_log']))) {
        lAssert(
            mkdir(dirname($in['network_log']), 0775, true) || is_dir(dirname($in['network_log'])),
            'Unable to create network log directory',
        );
    }

    lAssert(file_put_contents($in['network_log'], '') !== false, 'Unable to reset network log');
}

/** @param array<string,mixed> $in @return list<string> */
function lNetworkAttempts(array $in): array
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

function lRequestEnvelope(string $uri = '/'): void
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
function lBootWordPress(array $in): void
{
    lRequestEnvelope('/');
    require $in['wp_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    lAssert(defined('WPE_P006L_NETWORK_DENY_ACTIVE'), 'Outbound HTTP deny probe is not active');
}

/** @param array<string,mixed> $in @return array<string,string> */
function lEnvironment(array $in): array
{
    global $wp_version, $wpdb;

    lAssert(isset($wp_version) && is_string($wp_version), 'WordPress version unavailable');
    lAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable');

    $php = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
    $mysql = (string) $wpdb->db_version();

    lAssert($wp_version === $in['expected_wp'], 'WordPress runtime cell mismatch');
    lAssert($php === $in['expected_php'], 'PHP runtime cell mismatch');
    lAssert(str_starts_with($mysql, $in['expected_mysql']), 'MySQL runtime cell mismatch');

    return [
        'wordpress' => $wp_version,
        'php' => PHP_VERSION,
        'mysql' => $mysql,
        'sapi' => PHP_SAPI,
    ];
}

/** @return array{kernel_booted:bool,premium_modules:list<string>,required_free_modules:list<string>} */
function lModuleState(): array
{
    lAssert(class_exists(\WPEssential\Bootstrap\Plugin::class), 'Free Plugin bootstrap class unavailable');

    $kernel = \WPEssential\Bootstrap\Plugin::kernel();
    lAssert($kernel instanceof \WPEssential\Kernel\Kernel, 'Free kernel unavailable');
    lAssert($kernel->isBooted(), 'Free kernel is not booted');

    $modules = $kernel->modules();

    $premium = [];
    foreach (P006L_PREMIUM_MODULES as $moduleId) {
        if ($modules->has($moduleId)) {
            $premium[] = $moduleId;
        }
    }

    $free = [];
    foreach (P006L_REQUIRED_FREE_MODULES as $moduleId) {
        if ($modules->has($moduleId)) {
            $free[] = $moduleId;
        }
    }

    sort($premium, SORT_STRING);
    sort($free, SORT_STRING);

    return [
        'kernel_booted' => true,
        'premium_modules' => $premium,
        'required_free_modules' => $free,
    ];
}

/** @return list<string> */
function lCompatibilityPersistenceKeys(): array
{
    global $wpdb;

    lAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable for persistence scan');

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
function lRuntimeMetadata(): array
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
        lAssert(defined($constant), "Runtime metadata missing: {$constant}");
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
function lAssertMetadata(array $metadata, array $freeExpected, array $proExpected): void
{
    lAssert($metadata['free_version'] === $freeExpected['version'], 'Installed Free version mismatch');
    lAssert($metadata['platform_api'] === $freeExpected['platform_api'], 'Installed Platform API mismatch');
    lAssert($metadata['platform_schema'] === $freeExpected['platform_schema'], 'Installed Platform schema mismatch');

    lAssert($metadata['pro_version'] === $proExpected['version'], 'Installed Pro version mismatch');
    lAssert($metadata['pro_min_free_version'] === $proExpected['min_free_version'], 'Installed Pro min Free mismatch');
    lAssert($metadata['pro_max_free_version'] === $proExpected['max_free_version'], 'Installed Pro max Free mismatch');
    lAssert($metadata['pro_min_platform_api'] === $proExpected['min_platform_api'], 'Installed Pro min API mismatch');
    lAssert($metadata['pro_max_platform_api'] === $proExpected['max_platform_api'], 'Installed Pro max API mismatch');
    lAssert($metadata['pro_min_platform_schema'] === $proExpected['min_platform_schema'], 'Installed Pro min schema mismatch');
    lAssert($metadata['pro_max_platform_schema'] === $proExpected['max_platform_schema'], 'Installed Pro max schema mismatch');
    lAssert($metadata['pro_schema'] === $proExpected['schema'], 'Installed Pro schema mismatch');
}

function lTreeDigestFromDirectory(string $root, string $slug): string
{
    $root = realpath($root);
    lAssert(is_string($root) && is_dir($root), "Unable to resolve payload root: {$slug}");

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item) {
        lAssert(!$item->isLink(), "Nested symlink in staged payload: {$item->getPathname()}");
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
function lTransport(array $in): array
{
    $freeLink = $in['wp_dir'] . '/wp-content/plugins/wpessential';
    $proLink = $in['wp_dir'] . '/wp-content/plugins/wpessential-pro';

    lAssert(is_link($freeLink), 'Free transport path is not a symlink');
    lAssert(is_link($proLink), 'Pro transport path is not a symlink');

    $freeReal = realpath($freeLink);
    $proReal = realpath($proLink);

    lAssert(is_string($freeReal) && is_string($proReal), 'Unable to resolve transport targets');

    return [
        'free_realpath' => str_replace('\\', '/', $freeReal),
        'pro_realpath' => str_replace('\\', '/', $proReal),
    ];
}

/**
 * @param array<string,mixed> $identity
 * @param array<string,mixed> $in
 * @return array<string,mixed>
 */
function lObserve(
    array $identity,
    array $in,
    string $freeNode,
    string $proNode,
    string $expectedState,
    string $fixtureId,
    string $phase,
): array {
    $nodes = $identity['nodes'];
    $pairs = $identity['pairs'];

    lAssert(isset($nodes[$freeNode], $nodes[$proNode]), 'Unknown logical candidate node');

    $pairKey = $freeNode . '_' . $proNode;
    lAssert(isset($pairs[$pairKey]), "Unpinned pair requested: {$pairKey}");

    $transport = lTransport($in);
    $freeInstance = lEnv('WPE_P006_EXPECTED_FREE_INSTANCE');
    $proInstance = lEnv('WPE_P006_EXPECTED_PRO_INSTANCE');

    lAssert(
        str_contains($transport['free_realpath'], '/p006-wave1l-packages/' . $freeInstance . '/wpessential'),
        'Free transport instance mismatch',
    );
    lAssert(
        str_contains($transport['pro_realpath'], '/p006-wave1l-packages/' . $proInstance . '/wpessential-pro'),
        'Pro transport instance mismatch',
    );

    $freeTree = lTreeDigestFromDirectory($transport['free_realpath'], 'wpessential');
    $proTree = lTreeDigestFromDirectory($transport['pro_realpath'], 'wpessential-pro');

    lAssert($freeTree === $nodes[$freeNode]['payload_tree_sha256'], "{$freeNode}: installed Free payload tree drift");
    lAssert($proTree === $nodes[$proNode]['payload_tree_sha256'], "{$proNode}: installed Pro payload tree drift");

    $metadata = lRuntimeMetadata();
    lAssertMetadata($metadata, $nodes[$freeNode]['metadata'], $nodes[$proNode]['metadata']);

    $compatibility = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    lAssert(is_array($compatibility), 'Compatibility result is absent');
    lAssert(($compatibility['state'] ?? null) === $expectedState, "Unexpected compatibility state; expected {$expectedState}");

    $modules = lModuleState();

    lAssert(is_plugin_active('wpessential/wpessential.php'), 'Free plugin is not active');
    lAssert(is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro plugin is not active');

    $expectedFreeModules = P006L_REQUIRED_FREE_MODULES;
    sort($expectedFreeModules, SORT_STRING);
    lAssert($modules['required_free_modules'] === $expectedFreeModules, 'Required Free module continuity failed');

    if ($expectedState === 'compatible') {
        lAssert(($compatibility['premium_boot_allowed'] ?? null) === true, 'Compatible baseline denied premium boot');
        lAssert(($compatibility['premium_migrations_allowed'] ?? null) === true, 'Compatible baseline denied compatibility-layer migration admission');

        $expectedPremium = P006L_PREMIUM_MODULES;
        sort($expectedPremium, SORT_STRING);
        lAssert($modules['premium_modules'] === $expectedPremium, 'Compatible baseline premium module set mismatch');
    } else {
        lAssert(($compatibility['premium_boot_allowed'] ?? null) === false, 'Breaking pair allowed premium boot');
        lAssert(($compatibility['premium_migrations_allowed'] ?? null) === false, 'Breaking pair allowed premium migration admission');
        lAssert($modules['premium_modules'] === [], 'Breaking pair registered premium modules');
    }

    $attempts = lNetworkAttempts($in);
    lAssert($attempts === [], 'Unexpected outbound HTTP: ' . implode(', ', $attempts));

    $persistenceKeys = lCompatibilityPersistenceKeys();
    lAssert($persistenceKeys === [], 'Compatibility persistence key(s) appeared: ' . implode(', ', $persistenceKeys));
    lAssert(!wp_using_ext_object_cache(), 'External object cache unexpectedly active');

    $freeHash = $nodes[$freeNode]['sha256'];
    $proHash = $nodes[$proNode]['sha256'];
    $pairId = lPair($freeHash, $proHash);

    lAssert($pairId === $pairs[$pairKey]['pair_id_sha256'], 'Pair identity drift');

    return [
        'status' => 'PASS',
        'fixture_id' => $fixtureId,
        'phase' => $phase,
        'cell_id' => $in['cell_id'],
        'environment' => lEnvironment($in),
        'logical_pair' => ['free' => $freeNode, 'pro' => $proNode],
        'expected_compatibility_state' => $expectedState,
        'artifact_identity' => [
            'free_sha256' => $freeHash,
            'pro_sha256' => $proHash,
            'pair_id_sha256' => $pairId,
            'free_payload_tree_sha256' => $freeTree,
            'pro_payload_tree_sha256' => $proTree,
        ],
        'transport' => [
            'method' => 'quiescent atomic symlink-target rename over exact complete ZIP-extracted payload',
            'free_instance' => $freeInstance,
            'pro_instance' => $proInstance,
            'free_realpath' => $transport['free_realpath'],
            'pro_realpath' => $transport['pro_realpath'],
            'partial_or_interrupted_replacement_certified' => false,
            'manual_upload_certified' => false,
            'automatic_updater_or_tuf_certified' => false,
        ],
        'metadata' => $metadata,
        'compatibility' => $compatibility,
        'module_state' => $modules,
        'compatibility_persistence_keys' => [],
        'external_object_cache' => false,
        'outbound_http_attempts' => [],
        'network_attempt_count' => 0,
        'fatal_or_error' => null,
        'certification_boundary' => [
            'runtime_evidence_only' => true,
            'pair_certified' => false,
            'runtime_certified' => false,
            'manual_upload_certified' => false,
            'updater_or_tuf_certified' => false,
            'interrupted_replacement_certified' => false,
            'rollback_certified' => false,
            'migration_certified' => false,
            'adr_0010' => 'Proposed',
        ],
    ];
}

/** @param array<string,mixed> $in @return array<string,mixed> */
function lPrepare(array $in): array
{
    lInstallNetworkProbe($in);
    lResetNetworkLog($in);
    lBootWordPress($in);

    lAssert(is_blog_installed(), 'Disposable WordPress is not installed');

    foreach (['wpessential-pro/wpessential-pro.php', 'wpessential/wpessential.php'] as $plugin) {
        if (is_plugin_active($plugin)) {
            deactivate_plugins($plugin, true, false);
        }
    }

    lResetNetworkLog($in);

    $result = activate_plugin('wpessential/wpessential.php', '', false, true);
    lAssert(!is_wp_error($result) && is_plugin_active('wpessential/wpessential.php'), 'F1 activation failed');

    $result = activate_plugin('wpessential-pro/wpessential-pro.php', '', false, true);
    lAssert(!is_wp_error($result) && is_plugin_active('wpessential-pro/wpessential-pro.php'), 'P0 activation failed');

    lAssert(lNetworkAttempts($in) === [], 'Unexpected outbound HTTP during baseline activation');
    lAssert(lCompatibilityPersistenceKeys() === [], 'Compatibility persistence appeared during baseline activation');

    return [
        'status' => 'PASS',
        'phase' => 'prepare',
        'cell_id' => $in['cell_id'],
        'environment' => lEnvironment($in),
        'network_attempts' => [],
        'compatibility_persistence_keys' => [],
    ];
}

/** @param array<string,mixed> $in @return array<string,mixed> */
function lAggregate(array $in, string $fixture): array
{
    $baseline = lReadJson($in['evidence_dir'] . '/baseline.json');
    $breaking = lReadJson($in['evidence_dir'] . '/breaking.json');

    lAssert(($baseline['status'] ?? null) === 'PASS', "{$fixture}: baseline record is not PASS");
    lAssert(($breaking['status'] ?? null) === 'PASS', "{$fixture}: breaking record is not PASS");

    lAssert(($baseline['logical_pair'] ?? null) === ['free' => 'F1', 'pro' => 'P0'], "{$fixture}: baseline pair mismatch");
    lAssert(($baseline['compatibility']['state'] ?? null) === 'compatible', "{$fixture}: baseline is not compatible");
    lAssert(($baseline['compatibility']['premium_boot_allowed'] ?? null) === true, "{$fixture}: baseline premium boot unavailable");

    if ($fixture === 'FP-47') {
        lAssert(($breaking['logical_pair'] ?? null) === ['free' => 'F2', 'pro' => 'P0'], 'FP-47 breaking pair mismatch');
        lAssert(($breaking['compatibility']['state'] ?? null) === 'free_version_too_new', 'FP-47 did not fail closed as free_version_too_new');
        lAssert(($breaking['metadata']['free_version'] ?? null) === '0.2.0-test-breaking', 'FP-47 Free version mismatch');
        lAssert(($breaking['metadata']['platform_api'] ?? null) === '0.2.0', 'FP-47 Platform API mismatch');
        lAssert(($breaking['metadata']['pro_version'] ?? null) === '0.1.0-dev', 'FP-47 old Pro was not preserved');
    } elseif ($fixture === 'FP-48') {
        lAssert(($breaking['logical_pair'] ?? null) === ['free' => 'F1', 'pro' => 'P2'], 'FP-48 breaking pair mismatch');
        lAssert(($breaking['compatibility']['state'] ?? null) === 'free_version_too_old', 'FP-48 did not fail closed as free_version_too_old');
        lAssert(($breaking['metadata']['free_version'] ?? null) === '0.1.1-test-overlap', 'FP-48 old Free was not preserved');
        lAssert(($breaking['metadata']['pro_version'] ?? null) === '0.2.0-test-breaking', 'FP-48 Pro version mismatch');
        lAssert(($breaking['metadata']['pro_min_platform_api'] ?? null) === '0.2.0', 'FP-48 breaking Pro API minimum mismatch');
    } else {
        lFail("Unsupported aggregate fixture: {$fixture}");
    }

    lAssert(($breaking['compatibility']['premium_boot_allowed'] ?? null) === false, "{$fixture}: premium boot remained allowed");
    lAssert(($breaking['compatibility']['premium_migrations_allowed'] ?? null) === false, "{$fixture}: migration admission remained allowed");
    lAssert(($breaking['module_state']['premium_modules'] ?? null) === [], "{$fixture}: premium modules remained registered");

    $requiredFree = P006L_REQUIRED_FREE_MODULES;
    sort($requiredFree, SORT_STRING);
    lAssert(($breaking['module_state']['required_free_modules'] ?? null) === $requiredFree, "{$fixture}: Free module continuity failed");

    lAssert(($breaking['network_attempt_count'] ?? null) === 0, "{$fixture}: outbound HTTP observed");
    lAssert(($breaking['compatibility_persistence_keys'] ?? null) === [], "{$fixture}: compatibility persistence appeared");

    return [
        'status' => 'PASS',
        'cell_id' => $in['cell_id'],
        'fixture_results' => [
            $fixture => [
                'status' => 'PASS',
                'baseline_pair' => $baseline['logical_pair'],
                'breaking_pair' => $breaking['logical_pair'],
                'baseline_compatibility' => $baseline['compatibility'],
                'breaking_compatibility' => $breaking['compatibility'],
                'free_kernel_booted_after_break' => $breaking['module_state']['kernel_booted'],
                'free_modules_preserved' => $breaking['module_state']['required_free_modules'],
                'premium_modules_after_break' => $breaking['module_state']['premium_modules'],
                'network_attempts' => 0,
                'compatibility_persistence_keys' => [],
            ],
        ],
        'certification_boundary' => [
            'pair_certified' => false,
            'runtime_certified' => false,
            'partial_or_interrupted_replacement_certified' => false,
            'manual_upload_certified' => false,
            'updater_or_tuf_certified' => false,
            'rollback_certified' => false,
            'migration_certified' => false,
            'adr_0010' => 'Proposed',
        ],
    ];
}

$mode = trim((string) getenv('WPE_P006_EXECUTION_MODE'));
if ($mode === '') {
    $mode = $argv[1] ?? '';
}

try {
    $identity = lIdentity();
    $in = lRuntimeInput();

    if ($mode === 'prepare') {
        $result = lPrepare($in);
        lWriteJson($in['evidence_dir'] . '/prepare.json', $result);
    } elseif ($mode === 'observe') {
        lInstallNetworkProbe($in);
        lResetNetworkLog($in);
        lBootWordPress($in);

        $result = lObserve(
            $identity,
            $in,
            lEnv('WPE_P006_EXPECTED_FREE_NODE'),
            lEnv('WPE_P006_EXPECTED_PRO_NODE'),
            lEnv('WPE_P006_EXPECTED_STATE'),
            lEnv('WPE_P006_FIXTURE_ID'),
            lEnv('WPE_P006_PHASE'),
        );

        lWriteJson($in['evidence_dir'] . '/' . lEnv('WPE_P006_EVIDENCE_FILE'), $result);
    } elseif ($mode === 'aggregate') {
        $result = lAggregate($in, lEnv('WPE_P006_FIXTURE_ID'));
        lWriteJson($in['evidence_dir'] . '/summary.json', $result);
    } else {
        lFail('Usage: <prepare|observe|aggregate>');
    }

    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 Wave 1L runtime] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
