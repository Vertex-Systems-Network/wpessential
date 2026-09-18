<?php

declare(strict_types=1);

const P006M_GRANT = 'GOV-P001-CF-TEMP-011';

const P006M_PREMIUM_MODULES = [
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

const P006M_REQUIRED_FREE_MODULES = [
    'custom-post-types',
    'taxonomies',
];

function rFail(string $message): never
{
    throw new RuntimeException($message);
}

function rAssert(bool $condition, string $message): void
{
    if (!$condition) {
        rFail($message);
    }
}

function rEnv(string $key): string
{
    $value = trim((string) getenv($key));
    if ($value === '') {
        rFail("Missing env: {$key}");
    }
    return $value;
}

/** @return array<string,mixed> */
function rReadJson(string $path): array
{
    rAssert(is_file($path), "Missing JSON: {$path}");
    $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    rAssert(is_array($decoded), "Invalid JSON object: {$path}");
    return $decoded;
}

/** @param array<string,mixed> $value */
function rWriteJson(string $path, array $value): void
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        rAssert(mkdir($dir, 0775, true) || is_dir($dir), "Unable to create evidence directory: {$dir}");
    }
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    rAssert(file_put_contents($path, $json . PHP_EOL) !== false, "Unable to write JSON: {$path}");
}

function rHashFile(string $path): string
{
    rAssert(is_file($path) && filesize($path) > 0, "Missing artifact: {$path}");
    $hash = hash_file('sha256', $path);
    rAssert(is_string($hash), "Unable to hash artifact: {$path}");
    return $hash;
}

function rPair(string $freeHash, string $proHash): string
{
    return hash('sha256', $freeHash . ':' . $proHash);
}

function rTreeDigest(string $root, string $slug): string
{
    rAssert(!is_link($root), "Live plugin root must not be a symlink: {$root}");
    $real = realpath($root);
    rAssert(is_string($real) && is_dir($real), "Unable to resolve payload root: {$slug}");

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $item) {
        rAssert(!$item->isLink(), "Nested symlink in live payload: {$item->getPathname()}");
        if (!$item->isFile()) {
            continue;
        }
        $relative = substr($item->getPathname(), strlen($real) + 1);
        $relative = str_replace('\\', '/', $relative);
        $sha = hash_file('sha256', $item->getPathname());
        rAssert(is_string($sha), "Unable to hash live file: {$item->getPathname()}");
        $files[$slug . '/' . $relative] = $sha;
    }

    ksort($files, SORT_STRING);
    $ctx = hash_init('sha256');
    foreach ($files as $name => $sha) {
        hash_update($ctx, $name . "\0" . $sha . "\n");
    }
    return hash_final($ctx);
}

/** @return list<string> */
function rNetworkAttempts(string $log): array
{
    if (!is_file($log)) {
        return [];
    }
    $lines = file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return is_array($lines) ? array_values(array_map('strval', $lines)) : [];
}

/** @return list<string> */
function rBackupResidue(string $wpDir): array
{
    $root = $wpDir . '/wp-content/upgrade-temp-backup/plugins';
    if (!is_dir($root)) {
        return [];
    }
    $entries = array_values(array_filter(
        scandir($root) ?: [],
        static fn(string $value): bool => $value !== '.' && $value !== '..'
    ));
    sort($entries, SORT_STRING);
    return $entries;
}

/** @return array<string,mixed> */
function rScenario(string $name): array
{
    return match ($name) {
        'compatible-free' => [
            'baseline_free' => 'F0',
            'baseline_pro' => 'P0',
            'target_plugin' => 'free',
            'target_node' => 'F1',
            'after_free' => 'F1',
            'after_pro' => 'P0',
            'expected_after_state' => 'compatible',
        ],
        'compatible-pro' => [
            'baseline_free' => 'F0',
            'baseline_pro' => 'P0',
            'target_plugin' => 'pro',
            'target_node' => 'P1',
            'after_free' => 'F0',
            'after_pro' => 'P1',
            'expected_after_state' => 'compatible',
        ],
        'breaking-free' => [
            'baseline_free' => 'F1',
            'baseline_pro' => 'P0',
            'target_plugin' => 'free',
            'target_node' => 'F2',
            'after_free' => 'F2',
            'after_pro' => 'P0',
            'expected_after_state' => 'free_version_too_new',
        ],
        'breaking-pro' => [
            'baseline_free' => 'F1',
            'baseline_pro' => 'P0',
            'target_plugin' => 'pro',
            'target_node' => 'P2',
            'after_free' => 'F1',
            'after_pro' => 'P2',
            'expected_after_state' => 'free_version_too_old',
        ],
        default => rFail("Unsupported FP-58 scenario: {$name}"),
    };
}

/** @return array<string,mixed> */
function rInput(): array
{
    $scenarioName = rEnv('WPE_P006_SCENARIO');
    return [
        'source_sha' => rEnv('WPE_P006_SOURCE_SHA'),
        'cell_id' => rEnv('WPE_P006_CELL_ID'),
        'expected_wp' => rEnv('WPE_P006_EXPECTED_WP'),
        'expected_php' => rEnv('WPE_P006_EXPECTED_PHP'),
        'expected_mysql' => rEnv('WPE_P006_EXPECTED_MYSQL'),
        'scenario_name' => $scenarioName,
        'scenario' => rScenario($scenarioName),
        'wp_dir' => rtrim(rEnv('WPE_TEST_WORDPRESS_DIR'), '/\\'),
        'candidate_dir' => rtrim(rEnv('WPE_P006_CANDIDATE_DIR'), '/\\'),
        'identity_path' => rEnv('WPE_P006_IDENTITY_PATH'),
        'evidence_dir' => rtrim(rEnv('WPE_P006_EVIDENCE_DIR'), '/\\'),
        'network_log' => rEnv('WPE_P006_NETWORK_LOG'),
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in */
function rVerifyIdentity(array $identity, array $in): void
{
    rAssert(($identity['protocol'] ?? null) === 'P-006', 'Protocol identity mismatch');
    rAssert(($identity['wave'] ?? null) === '1M-fp58-wordpress-manual-replacement-order', 'Wave identity mismatch');
    rAssert(($identity['source_sha'] ?? null) === $in['source_sha'], 'Candidate source SHA mismatch');
    rAssert(($identity['temporary_approval_id'] ?? null) === P006M_GRANT, 'FP-58 authorization mismatch');
    rAssert(($identity['authorized_fixture'] ?? null) === 'FP-58', 'Candidate fixture authorization mismatch');

    $nodes = $identity['nodes'] ?? null;
    rAssert(is_array($nodes), 'Candidate nodes missing');
    foreach (['F0','F1','F2','P0','P1','P2'] as $nodeId) {
        $node = $nodes[$nodeId] ?? null;
        rAssert(is_array($node), "Candidate node missing: {$nodeId}");
        $artifact = $node['artifact'] ?? null;
        $sha = $node['sha256'] ?? null;
        rAssert(is_string($artifact) && is_string($sha), "{$nodeId}: malformed artifact identity");
        $path = $in['candidate_dir'] . '/' . $artifact;
        rAssert(hash_equals($sha, rHashFile($path)), "{$nodeId}: ZIP hash drift");
    }

    $pairs = $identity['pairs'] ?? null;
    rAssert(is_array($pairs), 'Candidate pairs missing');
    foreach ([
        'F0_P0' => ['F0','P0'],
        'F1_P0' => ['F1','P0'],
        'F0_P1' => ['F0','P1'],
        'F2_P0' => ['F2','P0'],
        'F1_P2' => ['F1','P2'],
    ] as $key => [$free,$pro]) {
        $pair = $pairs[$key] ?? null;
        rAssert(is_array($pair), "Pair missing: {$key}");
        $expected = rPair((string) $nodes[$free]['sha256'], (string) $nodes[$pro]['sha256']);
        rAssert(($pair['pair_id_sha256'] ?? null) === $expected, "{$key}: pair identity drift");
    }
}

function rRequestEnvelope(): void
{
    $_SERVER['HTTP_HOST'] = 'p006-wave1m.test';
    $_SERVER['SERVER_NAME'] = 'p006-wave1m.test';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTPS'] = 'off';
    $_SERVER['REQUEST_URI'] = '/wp-admin/plugins.php';
    $_SERVER['SCRIPT_NAME'] = '/wp-admin/plugins.php';
    $_SERVER['PHP_SELF'] = '/wp-admin/plugins.php';
}

/** @param array<string,mixed> $in */
function rBoot(array $in): void
{
    rRequestEnvelope();
    require $in['wp_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    rAssert(defined('WPE_P006M_NETWORK_DENY_ACTIVE'), 'Outbound HTTP deny probe is not active');
}

/** @param array<string,mixed> $in @return array<string,string> */
function rEnvironment(array $in): array
{
    global $wp_version, $wpdb;
    rAssert(isset($wp_version) && is_string($wp_version), 'WordPress version unavailable');
    rAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable');

    $php = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
    $mysql = (string) $wpdb->db_version();
    rAssert($wp_version === $in['expected_wp'], 'WordPress runtime cell mismatch');
    rAssert($php === $in['expected_php'], 'PHP runtime cell mismatch');
    rAssert(str_starts_with($mysql, $in['expected_mysql']), 'MySQL runtime cell mismatch');

    return [
        'wordpress' => $wp_version,
        'php' => PHP_VERSION,
        'mysql' => $mysql,
        'sapi' => PHP_SAPI,
    ];
}

/** @return array<string,string|int> */
function rRuntimeMetadata(): array
{
    foreach ([
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
    ] as $constant) {
        rAssert(defined($constant), "Runtime constant missing: {$constant}");
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
function rAssertMetadata(array $metadata, array $freeExpected, array $proExpected): void
{
    rAssert($metadata['free_version'] === $freeExpected['version'], 'Installed Free version mismatch');
    rAssert($metadata['platform_api'] === $freeExpected['platform_api'], 'Installed Platform API mismatch');
    rAssert($metadata['platform_schema'] === $freeExpected['platform_schema'], 'Installed Platform schema mismatch');
    rAssert($metadata['pro_version'] === $proExpected['version'], 'Installed Pro version mismatch');
    rAssert($metadata['pro_min_free_version'] === $proExpected['min_free_version'], 'Installed Pro min Free mismatch');
    rAssert($metadata['pro_max_free_version'] === $proExpected['max_free_version'], 'Installed Pro max Free mismatch');
    rAssert($metadata['pro_min_platform_api'] === $proExpected['min_platform_api'], 'Installed Pro min API mismatch');
    rAssert($metadata['pro_max_platform_api'] === $proExpected['max_platform_api'], 'Installed Pro max API mismatch');
    rAssert($metadata['pro_min_platform_schema'] === $proExpected['min_platform_schema'], 'Installed Pro min schema mismatch');
    rAssert($metadata['pro_max_platform_schema'] === $proExpected['max_platform_schema'], 'Installed Pro max schema mismatch');
    rAssert($metadata['pro_schema'] === $proExpected['schema'], 'Installed Pro schema mismatch');
}

/** @return array{kernel_booted:bool,premium_modules:list<string>,required_free_modules:list<string>} */
function rModuleState(): array
{
    rAssert(class_exists(\WPEssential\Bootstrap\Plugin::class), 'Free bootstrap class unavailable');
    $kernel = \WPEssential\Bootstrap\Plugin::kernel();
    rAssert($kernel instanceof \WPEssential\Kernel\Kernel, 'Free kernel unavailable');
    rAssert($kernel->isBooted(), 'Free kernel is not booted');
    $modules = $kernel->modules();

    $premium = [];
    foreach (P006M_PREMIUM_MODULES as $moduleId) {
        if ($modules->has($moduleId)) {
            $premium[] = $moduleId;
        }
    }
    $free = [];
    foreach (P006M_REQUIRED_FREE_MODULES as $moduleId) {
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
function rCompatibilityPersistenceKeys(): array
{
    global $wpdb;
    rAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable for persistence scan');
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
    return is_array($values) ? array_values(array_map('strval', $values)) : [];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function rObserve(array $identity, array $in, string $phase): array
{
    $scenario = $in['scenario'];
    if ($phase === 'baseline') {
        $freeNode = $scenario['baseline_free'];
        $proNode = $scenario['baseline_pro'];
        $expectedState = 'compatible';
    } elseif ($phase === 'after') {
        $freeNode = $scenario['after_free'];
        $proNode = $scenario['after_pro'];
        $expectedState = $scenario['expected_after_state'];
    } else {
        rFail("Unsupported observation phase: {$phase}");
    }

    file_put_contents($in['network_log'], '');
    rBoot($in);

    $environment = rEnvironment($in);
    $nodes = $identity['nodes'];
    $pairs = $identity['pairs'];
    $pairKey = $freeNode . '_' . $proNode;
    rAssert(isset($nodes[$freeNode], $nodes[$proNode], $pairs[$pairKey]), 'Observation identity missing');

    $freeRoot = $in['wp_dir'] . '/wp-content/plugins/wpessential';
    $proRoot = $in['wp_dir'] . '/wp-content/plugins/wpessential-pro';
    $freeTree = rTreeDigest($freeRoot, 'wpessential');
    $proTree = rTreeDigest($proRoot, 'wpessential-pro');
    rAssert($freeTree === $nodes[$freeNode]['payload_tree_sha256'], "{$freeNode}: live Free payload drift");
    rAssert($proTree === $nodes[$proNode]['payload_tree_sha256'], "{$proNode}: live Pro payload drift");

    $metadata = rRuntimeMetadata();
    rAssertMetadata($metadata, $nodes[$freeNode]['metadata'], $nodes[$proNode]['metadata']);

    $compatibility = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    rAssert(is_array($compatibility), 'Compatibility result absent');
    rAssert(($compatibility['state'] ?? null) === $expectedState, "Unexpected compatibility state; expected {$expectedState}");

    rAssert(is_plugin_active('wpessential/wpessential.php'), 'Free plugin is not active');
    rAssert(is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro plugin is not active');

    $modules = rModuleState();
    $expectedFree = P006M_REQUIRED_FREE_MODULES;
    sort($expectedFree, SORT_STRING);
    rAssert($modules['required_free_modules'] === $expectedFree, 'Required Free module continuity failed');

    if ($expectedState === 'compatible') {
        rAssert(($compatibility['premium_boot_allowed'] ?? null) === true, 'Compatible pair denied premium boot');
        rAssert(($compatibility['premium_migrations_allowed'] ?? null) === true, 'Compatible pair denied premium migration admission');
        $expectedPremium = P006M_PREMIUM_MODULES;
        sort($expectedPremium, SORT_STRING);
        rAssert($modules['premium_modules'] === $expectedPremium, 'Compatible premium module set mismatch');
    } else {
        rAssert(($compatibility['premium_boot_allowed'] ?? null) === false, 'Breaking pair allowed premium boot');
        rAssert(($compatibility['premium_migrations_allowed'] ?? null) === false, 'Breaking pair allowed premium migration admission');
        rAssert($modules['premium_modules'] === [], 'Breaking pair registered premium modules');
    }

    $attempts = rNetworkAttempts($in['network_log']);
    rAssert($attempts === [], 'Unexpected outbound HTTP during observation');
    $persistence = rCompatibilityPersistenceKeys();
    rAssert($persistence === [], 'Compatibility persistence key(s) appeared: ' . implode(', ', $persistence));
    rAssert(!wp_using_ext_object_cache(), 'External object cache unexpectedly active');

    $pairId = rPair((string) $nodes[$freeNode]['sha256'], (string) $nodes[$proNode]['sha256']);
    rAssert($pairId === $pairs[$pairKey]['pair_id_sha256'], 'Observed pair identity drift');

    return [
        'status' => 'PASS',
        'fixture_id' => 'FP-58',
        'phase' => $phase,
        'cell_id' => $in['cell_id'],
        'scenario' => $in['scenario_name'],
        'environment' => $environment,
        'logical_pair' => ['free'=>$freeNode,'pro'=>$proNode],
        'artifact_identity' => [
            'free_sha256' => $nodes[$freeNode]['sha256'],
            'pro_sha256' => $nodes[$proNode]['sha256'],
            'pair_id_sha256' => $pairId,
            'free_payload_tree_sha256' => $freeTree,
            'pro_payload_tree_sha256' => $proTree,
        ],
        'metadata' => $metadata,
        'compatibility' => $compatibility,
        'module_state' => $modules,
        'active_plugins' => [
            'free' => true,
            'pro' => true,
        ],
        'plugin_roots_are_symlinks' => [
            'free' => is_link($freeRoot),
            'pro' => is_link($proRoot),
        ],
        'compatibility_persistence_keys' => [],
        'external_object_cache' => false,
        'outbound_http_attempts' => [],
        'network_attempt_count' => 0,
        'temp_backup_residue' => rBackupResidue($in['wp_dir']),
        'fatal_or_error' => null,
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function rPrepare(array $identity, array $in): array
{
    file_put_contents($in['network_log'], '');
    rBoot($in);
    rAssert(is_blog_installed(), 'Disposable WordPress is not installed');

    foreach (['wpessential-pro/wpessential-pro.php','wpessential/wpessential.php'] as $plugin) {
        if (is_plugin_active($plugin)) {
            deactivate_plugins($plugin, true, false);
        }
    }

    $result = activate_plugin('wpessential/wpessential.php', '', false, true);
    rAssert(!is_wp_error($result) && is_plugin_active('wpessential/wpessential.php'), 'Baseline Free activation failed');

    $result = activate_plugin('wpessential-pro/wpessential-pro.php', '', false, true);
    rAssert(!is_wp_error($result) && is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Baseline Pro activation failed');

    $attempts = rNetworkAttempts($in['network_log']);
    rAssert($attempts === [], 'Unexpected outbound HTTP during baseline activation');
    rAssert(rCompatibilityPersistenceKeys() === [], 'Compatibility persistence appeared during baseline activation');

    return [
        'status' => 'PASS',
        'fixture_id' => 'FP-58',
        'phase' => 'prepare',
        'cell_id' => $in['cell_id'],
        'scenario' => $in['scenario_name'],
        'baseline_pair' => [
            'free' => $in['scenario']['baseline_free'],
            'pro' => $in['scenario']['baseline_pro'],
        ],
        'network_attempts' => [],
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function rOverwrite(array $identity, array $in): array
{
    file_put_contents($in['network_log'], '');
    rBoot($in);
    rEnvironment($in);

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skins.php';
    require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

    rAssert(is_plugin_active('wpessential/wpessential.php'), 'Free inactive before manual overwrite');
    rAssert(is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro inactive before manual overwrite');

    $scenario = $in['scenario'];
    $targetPlugin = $scenario['target_plugin'];
    $targetNode = $scenario['target_node'];
    $beforeNode = $targetPlugin === 'free' ? $scenario['baseline_free'] : $scenario['baseline_pro'];
    $slug = $targetPlugin === 'free' ? 'wpessential' : 'wpessential-pro';
    $root = $in['wp_dir'] . '/wp-content/plugins/' . $slug;

    $beforeTree = rTreeDigest($root, $slug);
    rAssert($beforeTree === $identity['nodes'][$beforeNode]['payload_tree_sha256'], 'Manual overwrite baseline target payload drift');

    $artifact = $identity['nodes'][$targetNode]['artifact'];
    $zip = $in['candidate_dir'] . '/' . $artifact;
    rAssert(hash_equals((string) $identity['nodes'][$targetNode]['sha256'], rHashFile($zip)), 'Manual overwrite target ZIP hash drift');

    $filesystemMethod = get_filesystem_method([], WP_CONTENT_DIR);
    rAssert($filesystemMethod === 'direct', "Expected direct filesystem method, got {$filesystemMethod}");
    rAssert(WP_Filesystem(), 'WP_Filesystem() failed');
    global $wp_filesystem;
    rAssert(is_object($wp_filesystem), 'WordPress filesystem object missing');

    $skin = new Automatic_Upgrader_Skin();
    $upgrader = new Plugin_Upgrader($skin);
    $result = $upgrader->install(
        $zip,
        [
            'overwrite_package' => true,
            'clear_update_cache' => false,
        ],
    );
    rAssert($result === true, 'Plugin_Upgrader::install() did not return true');

    $afterTree = rTreeDigest($root, $slug);
    rAssert($afterTree === $identity['nodes'][$targetNode]['payload_tree_sha256'], 'Manual overwrite target payload mismatch');

    $otherSlug = $targetPlugin === 'free' ? 'wpessential-pro' : 'wpessential';
    $otherNode = $targetPlugin === 'free' ? $scenario['baseline_pro'] : $scenario['baseline_free'];
    $otherTree = rTreeDigest($in['wp_dir'] . '/wp-content/plugins/' . $otherSlug, $otherSlug);
    rAssert($otherTree === $identity['nodes'][$otherNode]['payload_tree_sha256'], 'Counterpart payload changed during manual overwrite');

    $attempts = rNetworkAttempts($in['network_log']);
    rAssert($attempts === [], 'Unexpected outbound HTTP during local manual overwrite');
    $residue = rBackupResidue($in['wp_dir']);
    rAssert($residue === [], 'Temporary plugin backup residue remained: ' . implode(', ', $residue));

    return [
        'status' => 'PASS',
        'fixture_id' => 'FP-58',
        'phase' => 'overwrite',
        'cell_id' => $in['cell_id'],
        'scenario' => $in['scenario_name'],
        'transport' => [
            'owner' => 'WordPress core',
            'api' => 'Plugin_Upgrader::install',
            'local_zip' => true,
            'overwrite_package' => true,
            'filesystem_method' => $filesystemMethod,
            'plugin_root_is_symlink' => is_link($root),
            'upgrader_result' => true,
        ],
        'target' => [
            'plugin' => $targetPlugin,
            'before_node' => $beforeNode,
            'after_node' => $targetNode,
            'before_payload_tree_sha256' => $beforeTree,
            'after_payload_tree_sha256' => $afterTree,
            'zip_sha256' => $identity['nodes'][$targetNode]['sha256'],
        ],
        'counterpart' => [
            'node' => $otherNode,
            'payload_tree_sha256' => $otherTree,
        ],
        'activation_state_same_process' => [
            'free' => is_plugin_active('wpessential/wpessential.php'),
            'pro' => is_plugin_active('wpessential-pro/wpessential-pro.php'),
        ],
        'temp_backup_residue' => [],
        'network_attempt_count' => 0,
    ];
}

/** @param array<string,mixed> $in @return array<string,mixed> */
function rAggregate(array $in): array
{
    $prepare = rReadJson($in['evidence_dir'] . '/prepare.json');
    $baseline = rReadJson($in['evidence_dir'] . '/baseline.json');
    $overwrite = rReadJson($in['evidence_dir'] . '/overwrite.json');
    $after = rReadJson($in['evidence_dir'] . '/after.json');

    foreach ([$prepare,$baseline,$overwrite,$after] as $record) {
        rAssert(($record['status'] ?? null) === 'PASS', 'FP-58 phase did not PASS');
        rAssert(($record['fixture_id'] ?? null) === 'FP-58', 'Fixture identity drift');
        rAssert(($record['scenario'] ?? null) === $in['scenario_name'], 'Scenario identity drift');
    }

    rAssert(($baseline['compatibility']['state'] ?? null) === 'compatible', 'FP-58 baseline is not compatible');
    rAssert(($after['compatibility']['state'] ?? null) === $in['scenario']['expected_after_state'], 'FP-58 final compatibility state mismatch');

    return [
        'status' => 'PASS',
        'fixture_id' => 'FP-58',
        'classification' => 'FORMAL BOUNDED P-006 EVIDENCE',
        'cell_id' => $in['cell_id'],
        'scenario' => $in['scenario_name'],
        'baseline_pair' => $baseline['logical_pair'],
        'after_pair' => $after['logical_pair'],
        'expected_after_state' => $in['scenario']['expected_after_state'],
        'compatibility_before' => $baseline['compatibility'],
        'compatibility_after' => $after['compatibility'],
        'module_state_before' => $baseline['module_state'],
        'module_state_after' => $after['module_state'],
        'transport' => $overwrite['transport'],
        'target_replacement' => $overwrite['target'],
        'counterpart_preserved' => $overwrite['counterpart'],
        'artifact_identity_before' => $baseline['artifact_identity'],
        'artifact_identity_after' => $after['artifact_identity'],
        'activation_state_after_fresh_boot' => $after['active_plugins'],
        'compatibility_persistence_keys' => [],
        'temp_backup_residue' => [],
        'network_attempt_count' => 0,
        'fatal_or_error' => null,
        'fixture_result' => 'PASS',
        'certification_boundary' => [
            'pair_certified' => false,
            'runtime_certified' => false,
            'automatic_updater_or_tuf_certified' => false,
            'rollback_or_migration_certified' => false,
            'partial_or_interrupted_replacement_certified' => false,
            'adr_0010' => 'Proposed',
            'ga_or_release_authorized' => false,
        ],
    ];
}

$mode = trim((string) getenv('WPE_P006_EXECUTION_MODE'));
if ($mode === '') {
    $mode = $argv[1] ?? '';
}

try {
    $in = rInput();
    $identity = rReadJson($in['identity_path']);
    rVerifyIdentity($identity, $in);

    if ($mode === 'prepare') {
        $result = rPrepare($identity, $in);
        rWriteJson($in['evidence_dir'] . '/prepare.json', $result);
    } elseif ($mode === 'baseline') {
        $result = rObserve($identity, $in, 'baseline');
        rWriteJson($in['evidence_dir'] . '/baseline.json', $result);
    } elseif ($mode === 'overwrite') {
        $result = rOverwrite($identity, $in);
        rWriteJson($in['evidence_dir'] . '/overwrite.json', $result);
    } elseif ($mode === 'after') {
        $result = rObserve($identity, $in, 'after');
        rWriteJson($in['evidence_dir'] . '/after.json', $result);
    } elseif ($mode === 'aggregate') {
        $result = rAggregate($in);
        rWriteJson($in['evidence_dir'] . '/summary.json', $result);
    } else {
        rFail('Usage: <prepare|baseline|overwrite|after|aggregate>');
    }

    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 Wave 1M runtime] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
