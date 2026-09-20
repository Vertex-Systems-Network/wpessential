<?php

declare(strict_types=1);

const P006S_GRANT = 'GOV-P001-CF-TEMP-017';
const P006S_SENTINEL_KEY = 'wpe_p006_fp33_sentinel';
const P006S_SENTINEL_VALUE = 'fp33-preserve-v1';
const P006S_PRO_MIGRATIONS = [
    '220.custom_tables_migration_runs_v1',
    '221.custom_tables_migration_execution_confirmations_v1',
];
const P006S_FREE_MIGRATIONS = [
    '006.create-compiled-registration-atomic-store',
    '007.create-definition-persistence',
    '008.create-audit-ptd-store',
];
const P006S_FREE_MODULES = ['custom-post-types', 'taxonomies'];
const P006S_PRO_MODULES = [
    'roles', 'admin-menu', 'settings', 'dashboard', 'profiles', 'membership',
    'builder-widgets', 'forms-workflows', 'cron', 'notifications', 'emails', 'chat',
];

function sFail(string $message): never { throw new RuntimeException($message); }
function sAssert(bool $ok, string $message): void { if (!$ok) { sFail($message); } }
function sEnv(string $key): string {
    $value = trim((string) getenv($key));
    if ($value === '') { sFail('Missing env: ' . $key); }
    return $value;
}
function sOptEnv(string $key): string { return trim((string) getenv($key)); }
function sHashFile(string $path): string {
    sAssert(is_file($path) && filesize($path) > 0, 'Missing artifact: ' . $path);
    $hash = hash_file('sha256', $path);
    sAssert(is_string($hash), 'Unable to hash artifact: ' . $path);
    return $hash;
}
/** @param array<string,mixed> $value */
function sWrite(string $path, array $value): void {
    if (!is_dir(dirname($path))) {
        sAssert(mkdir(dirname($path), 0775, true) || is_dir(dirname($path)), 'Unable to create evidence directory');
    }
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    sAssert(file_put_contents($path, $json . PHP_EOL) !== false, 'Unable to write evidence');
}
/** @return array<string,mixed> */
function sRead(string $path): array {
    sAssert(is_file($path), 'Missing evidence: ' . $path);
    $value = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    sAssert(is_array($value), 'Invalid evidence: ' . $path);
    return $value;
}
function sPair(string $free, string $pro): string { return hash('sha256', $free . ':' . $pro); }

/** @return array<string,mixed> */
function sIdentity(): array {
    $identity = sRead(sEnv('WPE_P006_IDENTITY_PATH'));
    sAssert(($identity['temporary_approval_id'] ?? null) === P006S_GRANT, 'Temporary approval mismatch');
    sAssert(($identity['source_sha'] ?? null) === sEnv('WPE_P006_SOURCE_SHA'), 'Source SHA mismatch');
    $nodes = $identity['nodes'] ?? null;
    sAssert(is_array($nodes), 'Candidate nodes missing');
    $dir = rtrim(sEnv('WPE_P006_CANDIDATE_DIR'), '/\\');
    foreach (['F1', 'F2', 'P0', 'P2'] as $node) {
        sAssert(isset($nodes[$node]) && is_array($nodes[$node]), 'Node missing: ' . $node);
        $file = $dir . '/' . (string) $nodes[$node]['artifact'];
        sAssert(hash_equals((string) $nodes[$node]['sha256'], sHashFile($file)), 'ZIP hash drift: ' . $node);
    }
    foreach ([
        'F1_P0' => ['F1', 'P0', 'compatible'],
        'F2_P0' => ['F2', 'P0', 'free_version_too_new'],
        'F1_P2' => ['F1', 'P2', 'free_version_too_old'],
    ] as $key => $spec) {
        [$free, $pro, $state] = $spec;
        $pair = $identity['pairs'][$key] ?? null;
        sAssert(is_array($pair), 'Pair missing: ' . $key);
        sAssert(($pair['expected_state'] ?? null) === $state, 'Expected state drift: ' . $key);
        $expected = sPair((string) $nodes[$free]['sha256'], (string) $nodes[$pro]['sha256']);
        sAssert(($pair['pair_id_sha256'] ?? null) === $expected, 'Pair id drift: ' . $key);
    }
    return $identity;
}

/** @return array<string,mixed> */
function sInput(): array {
    $wp = sEnv('WPE_P006_EXPECTED_WP');
    $php = sEnv('WPE_P006_EXPECTED_PHP');
    $mysql = sEnv('WPE_P006_EXPECTED_MYSQL');
    $cell = match (true) {
        $wp === '6.9' && $php === '8.2' && $mysql === '8.4' => 'minimum',
        $wp === '7.1' && $php === '8.5' && $mysql === '8.4' => 'reference',
        default => null,
    };
    sAssert(is_string($cell), 'Runtime cell outside TEMP-017');
    return [
        'cell' => $cell,
        'wp' => $wp,
        'php' => $php,
        'mysql' => $mysql,
        'wp_dir' => rtrim(sEnv('WPE_TEST_WORDPRESS_DIR'), '/\\'),
        'evidence_dir' => rtrim(sEnv('WPE_P006_EVIDENCE_DIR'), '/\\'),
        'network_log' => sEnv('WPE_P006_NETWORK_LOG'),
    ];
}

/** @param array<string,mixed> $in */
function sInstallNetworkProbe(array $in): void {
    $dir = $in['wp_dir'] . '/wp-content/mu-plugins';
    if (!is_dir($dir)) { sAssert(mkdir($dir, 0775, true) || is_dir($dir), 'Unable to create mu-plugin dir'); }
    $php = <<<'PROBE'
<?php
if (!defined('WPE_P006S_NETWORK_DENY_ACTIVE')) {
    define('WPE_P006S_NETWORK_DENY_ACTIVE', true);
}
add_filter('pre_http_request', static function ($preempt, $args, $url) {
    $log = trim((string) getenv('WPE_P006_NETWORK_LOG'));
    if ($log !== '') { file_put_contents($log, (string) $url . PHP_EOL, FILE_APPEND | LOCK_EX); }
    return new WP_Error('p006_wave1s_network_blocked', 'Outbound HTTP denied during Wave 1S.');
}, PHP_INT_MIN, 3);
PROBE;
    sAssert(file_put_contents($dir . '/p006-wave1s-network-deny.php', $php . PHP_EOL) !== false, 'Unable to install network probe');
}
/** @param array<string,mixed> $in */
function sResetNetwork(array $in): void {
    sAssert(file_put_contents($in['network_log'], '') !== false, 'Unable to reset network log');
}
/** @param array<string,mixed> $in @return list<string> */
function sNetwork(array $in): array {
    if (!is_file($in['network_log'])) { return []; }
    $lines = file($in['network_log'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return is_array($lines) ? array_values(array_map('strval', $lines)) : [];
}
function sEnvelope(): void {
    $_SERVER['HTTP_HOST'] = 'p006.test';
    $_SERVER['SERVER_NAME'] = 'p006.test';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTPS'] = 'off';
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
}
/** @param array<string,mixed> $in */
function sBoot(array $in): void {
    sEnvelope();
    require $in['wp_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    sAssert(defined('WPE_P006S_NETWORK_DENY_ACTIVE'), 'Network probe not active');
}
/** @param array<string,mixed> $in @return array<string,string> */
function sEnvironment(array $in): array {
    global $wp_version, $wpdb;
    sAssert(isset($wp_version) && is_string($wp_version), 'WordPress version unavailable');
    sAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable');
    sAssert($wp_version === $in['wp'], 'WordPress version drift');
    sAssert(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION === $in['php'], 'PHP version drift');
    $mysql = (string) $wpdb->db_version();
    sAssert(str_starts_with($mysql, $in['mysql']), 'MySQL version drift');
    return ['wordpress' => $wp_version, 'php' => PHP_VERSION, 'mysql' => $mysql];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,string> */
function sTransport(array $identity, array $in, string $free, string $pro): array {
    $freePath = realpath($in['wp_dir'] . '/wp-content/plugins/wpessential');
    $proPath = realpath($in['wp_dir'] . '/wp-content/plugins/wpessential-pro');
    sAssert(is_string($freePath) && is_string($proPath), 'Transport path unavailable');
    sAssert(str_contains($freePath, '/p006-wave1l-packages/' . strtolower($free) . '/wpessential'), 'Free transport drift');
    sAssert(str_contains($proPath, '/p006-wave1l-packages/' . strtolower($pro) . '/wpessential-pro'), 'Pro transport drift');

    $tree = static function (string $root, string $slug): string {
        $files = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $item) {
            sAssert(!$item->isLink(), 'Nested symlink in candidate payload');
            if (!$item->isFile()) { continue; }
            $rel = str_replace('\\', '/', substr($item->getPathname(), strlen($root) + 1));
            $files[$slug . '/' . $rel] = hash_file('sha256', $item->getPathname());
        }
        ksort($files, SORT_STRING);
        $ctx = hash_init('sha256');
        foreach ($files as $name => $sha) { hash_update($ctx, $name . "\0" . $sha . "\n"); }
        return hash_final($ctx);
    };

    $freeTree = $tree($freePath, 'wpessential');
    $proTree = $tree($proPath, 'wpessential-pro');
    sAssert($freeTree === $identity['nodes'][$free]['payload_tree_sha256'], 'Free tree drift');
    sAssert($proTree === $identity['nodes'][$pro]['payload_tree_sha256'], 'Pro tree drift');
    $pair = sPair((string) $identity['nodes'][$free]['sha256'], (string) $identity['nodes'][$pro]['sha256']);
    sAssert($pair === $identity['pairs'][$free . '_' . $pro]['pair_id_sha256'], 'Pair drift');
    return [
        'free_sha256' => (string) $identity['nodes'][$free]['sha256'],
        'pro_sha256' => (string) $identity['nodes'][$pro]['sha256'],
        'free_payload_tree_sha256' => $freeTree,
        'pro_payload_tree_sha256' => $proTree,
        'pair_id_sha256' => $pair,
    ];
}

/** @return array<string,mixed> */
function sSnapshot(): array {
    global $wpdb;
    sAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable for snapshot');
    $prefix = is_string($wpdb->base_prefix ?? null) && $wpdb->base_prefix !== '' ? $wpdb->base_prefix : $wpdb->prefix;
    $all = $wpdb->get_col('SHOW TABLES');
    sAssert(is_array($all), 'Unable to list tables');
    $tables = [];
    foreach ($all as $table) {
        $table = (string) $table;
        if (str_starts_with($table, $prefix . 'wpe_')) { $tables[] = $table; }
    }
    sort($tables, SORT_STRING);

    $schema = [];
    foreach ($tables as $table) {
        $row = $wpdb->get_row('SHOW CREATE TABLE ' . $table, ARRAY_N);
        sAssert(is_array($row) && isset($row[1]) && is_string($row[1]), 'Unable to inspect schema: ' . $table);
        $ddl = preg_replace('/AUTO_INCREMENT=\d+\s*/', '', $row[1]);
        sAssert(is_string($ddl), 'Unable to normalize schema');
        $schema[$table] = hash('sha256', $ddl);
    }
    ksort($schema, SORT_STRING);

    $migrationTable = $prefix . 'wpe_migrations';
    $migrationIds = [];
    if (in_array($migrationTable, $tables, true)) {
        $values = $wpdb->get_col('SELECT migration_id FROM ' . $migrationTable . ' ORDER BY migration_id ASC');
        if (is_array($values)) { $migrationIds = array_values(array_map('strval', $values)); }
    }

    $proTables = [
        $prefix . 'wpe_custom_table_migration_runs',
        $prefix . 'wpe_custom_table_migration_confirmations',
    ];
    $proPresence = [];
    foreach ($proTables as $table) { $proPresence[$table] = in_array($table, $tables, true); }

    $data = [
        'migration_ids' => $migrationIds,
        'wpe_tables' => $tables,
        'wpe_table_schema_sha256' => $schema,
        'pro_custom_table_store_presence' => $proPresence,
        'sentinel' => get_option(P006S_SENTINEL_KEY, '__missing__'),
    ];
    $data['snapshot_sha256'] = hash('sha256', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    return $data;
}
function sAssertBaseline(array $snapshot): void {
    foreach (P006S_FREE_MIGRATIONS as $id) {
        sAssert(in_array($id, $snapshot['migration_ids'], true), 'Baseline Free migration missing: ' . $id);
    }
    foreach (P006S_PRO_MIGRATIONS as $id) {
        sAssert(!in_array($id, $snapshot['migration_ids'], true), 'Pro migration unexpectedly applied: ' . $id);
    }
    foreach ($snapshot['pro_custom_table_store_presence'] as $table => $present) {
        sAssert($present === false, 'Pro migration table unexpectedly present: ' . $table);
    }
    sAssert(($snapshot['sentinel'] ?? null) === P006S_SENTINEL_VALUE, 'Sentinel drift');
}

/** @return array<string,mixed> */
function sModules(): array {
    sAssert(class_exists(\WPEssential\Bootstrap\Plugin::class), 'Free bootstrap class unavailable');
    $kernel = \WPEssential\Bootstrap\Plugin::kernel();
    sAssert($kernel instanceof \WPEssential\Kernel\Kernel && $kernel->isBooted(), 'Free kernel not booted');
    $registry = $kernel->modules();
    $free = []; $pro = [];
    foreach (P006S_FREE_MODULES as $id) { if ($registry->has($id)) { $free[] = $id; } }
    foreach (P006S_PRO_MODULES as $id) { if ($registry->has($id)) { $pro[] = $id; } }
    sort($free, SORT_STRING); sort($pro, SORT_STRING);
    return ['required_free_modules' => $free, 'premium_modules' => $pro];
}
/** @return list<string> */
function sCompatibilityKeys(): array {
    global $wpdb;
    $values = $wpdb->get_col(
        "SELECT option_name FROM {$wpdb->options}
         WHERE option_name LIKE '%compatib%'
            OR option_name LIKE 'wpe%compat%'
            OR option_name LIKE '_transient_%compat%'
            OR option_name LIKE '_site_transient_%compat%'
         ORDER BY option_name ASC"
    );
    return is_array($values) ? array_values(array_map('strval', $values)) : [];
}

/** @param array<string,mixed> $identity @return array<string,mixed> */
function sStatic(array $identity): array {
    $dir = rtrim(sEnv('WPE_P006_CANDIDATE_DIR'), '/\\');
    $pattern = '/register_(?:activation|deactivation|uninstall)_hook\s*\(/i';
    $hits = [];
    foreach (['F1', 'F2', 'P0', 'P2'] as $node) {
        $zip = new ZipArchive();
        sAssert($zip->open($dir . '/' . $identity['nodes'][$node]['artifact']) === true, 'Unable to open ZIP: ' . $node);
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (!is_array($stat) || !isset($stat['name'])) { continue; }
            $name = (string) $stat['name'];
            if (!str_ends_with($name, '.php') || str_contains($name, '/vendor/')) { continue; }
            $value = $zip->getFromIndex($i);
            if (is_string($value) && preg_match($pattern, $value) === 1) { $hits[] = $node . ':' . $name; }
        }
        $zip->close();
    }
    foreach (['wpessential.php', 'wpessential-pro.php'] as $entry) {
        $value = file_get_contents($entry);
        sAssert(is_string($value), 'Unable to scan entry: ' . $entry);
        if (preg_match($pattern, $value) === 1) { $hits[] = 'current:' . $entry; }
    }
    sAssert($hits === [], 'First-party activation lifecycle hook found: ' . implode(', ', $hits));
    return [
        'status' => 'PASS',
        'authorization' => P006S_GRANT,
        'source_sha' => sEnv('WPE_P006_SOURCE_SHA'),
        'patterns' => ['register_activation_hook', 'register_deactivation_hook', 'register_uninstall_hook'],
        'hits' => [],
        'migration_owner_classes_are_activation_hooks' => false,
    ];
}

/** @param array<string,mixed> $in @return array<string,mixed> */
function sPrepare(array $in): array {
    sInstallNetworkProbe($in);
    sResetNetwork($in);
    sBoot($in);
    foreach (['wpessential-pro/wpessential-pro.php', 'wpessential/wpessential.php'] as $plugin) {
        if (is_plugin_active($plugin)) { deactivate_plugins($plugin, true, false); }
    }
    sResetNetwork($in);
    $activation = activate_plugin('wpessential/wpessential.php', '', false, true);
    sAssert(!is_wp_error($activation) && is_plugin_active('wpessential/wpessential.php'), 'Free activation failed');
    sAssert(!is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro unexpectedly active');
    sAssert(sNetwork($in) === [], 'Network attempt during prepare');
    return ['status' => 'PASS', 'phase' => 'prepare', 'cell_id' => $in['cell'], 'environment' => sEnvironment($in)];
}
/** @param array<string,mixed> $in @return array<string,mixed> */
function sBaseline(array $in): array {
    sResetNetwork($in);
    sBoot($in);
    sAssert(is_plugin_active('wpessential/wpessential.php'), 'Free inactive at baseline');
    sAssert(!is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro active at baseline');
    update_option(P006S_SENTINEL_KEY, P006S_SENTINEL_VALUE, false);
    $snapshot = sSnapshot();
    sAssertBaseline($snapshot);
    sAssert(sNetwork($in) === [], 'Network attempt at baseline');
    return ['status' => 'PASS', 'phase' => 'baseline', 'cell_id' => $in['cell'], 'environment' => sEnvironment($in), 'snapshot' => $snapshot];
}
/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function sActivate(array $identity, array $in): array {
    $free = sEnv('WPE_P006_EXPECTED_FREE_NODE');
    $pro = sEnv('WPE_P006_EXPECTED_PRO_NODE');
    $artifact = sTransport($identity, $in, $free, $pro);
    sResetNetwork($in);
    sBoot($in);
    sAssert(is_plugin_active('wpessential/wpessential.php'), 'Free inactive before Pro activation');
    sAssert(!is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro must be inactive before activation');
    $before = sSnapshot(); sAssertBaseline($before);
    $activation = activate_plugin('wpessential-pro/wpessential-pro.php', '', false, true);
    sAssert(!is_wp_error($activation) && is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro activation failed');
    $after = sSnapshot(); sAssertBaseline($after);
    sAssert($before['snapshot_sha256'] === $after['snapshot_sha256'], 'activate_plugin changed WPE migration/schema state');
    sAssert(sNetwork($in) === [], 'Network attempt during activate_plugin');
    return [
        'status' => 'PASS', 'fixture_id' => 'FP-33', 'phase' => sEnv('WPE_P006_PHASE'),
        'cell_id' => $in['cell'], 'environment' => sEnvironment($in),
        'logical_pair' => ['free' => $free, 'pro' => $pro], 'artifact_identity' => $artifact,
        'before_snapshot' => $before, 'after_snapshot' => $after,
        'wpe_state_unchanged_during_activation' => true, 'network_attempt_count' => 0, 'fatal_or_error' => null,
    ];
}
/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function sObserve(array $identity, array $in): array {
    $free = sEnv('WPE_P006_EXPECTED_FREE_NODE');
    $pro = sEnv('WPE_P006_EXPECTED_PRO_NODE');
    $state = sEnv('WPE_P006_EXPECTED_STATE');
    $artifact = sTransport($identity, $in, $free, $pro);
    sResetNetwork($in);
    sBoot($in);
    sAssert(is_plugin_active('wpessential/wpessential.php') && is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Plugin activation state drift');
    $compat = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    sAssert(is_array($compat) && ($compat['state'] ?? null) === $state, 'Compatibility state drift');
    sAssert(($compat['premium_boot_allowed'] ?? null) === false, 'Incompatible pair allowed premium boot');
    sAssert(($compat['premium_migrations_allowed'] ?? null) === false, 'Incompatible pair allowed premium migration');
    $mutation = defined('WPE_PRO_PREMIUM_MUTATIONS_ALLOWED') && WPE_PRO_PREMIUM_MUTATIONS_ALLOWED === true;
    sAssert($mutation === false, 'Incompatible pair allowed premium mutation');
    $modules = sModules();
    $freeExpected = P006S_FREE_MODULES; sort($freeExpected, SORT_STRING);
    sAssert($modules['required_free_modules'] === $freeExpected, 'Free module continuity failed');
    sAssert($modules['premium_modules'] === [], 'Premium modules registered under incompatibility');
    $snapshot = sSnapshot(); sAssertBaseline($snapshot);
    $baseline = sRead($in['evidence_dir'] . '/baseline.json');
    sAssert(($baseline['snapshot']['snapshot_sha256'] ?? null) === $snapshot['snapshot_sha256'], 'Fresh mismatch request changed WPE migration/schema state');
    sAssert(sCompatibilityKeys() === [], 'Compatibility persistence appeared');
    sAssert(!wp_using_ext_object_cache(), 'External object cache participated');
    sAssert(sNetwork($in) === [], 'Network attempt during mismatch observation');

    $deactivation = null;
    if (sOptEnv('WPE_P006_DEACTIVATE_AFTER') === '1') {
        $before = $snapshot;
        deactivate_plugins('wpessential-pro/wpessential-pro.php', true, false);
        sAssert(!is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro remained active after deactivation');
        $after = sSnapshot(); sAssertBaseline($after);
        sAssert($before['snapshot_sha256'] === $after['snapshot_sha256'], 'deactivate_plugins changed WPE migration/schema state');
        $deactivation = ['before_snapshot' => $before, 'after_snapshot' => $after, 'wpe_state_unchanged' => true];
    }
    return [
        'status' => 'PASS', 'fixture_id' => 'FP-33', 'phase' => sEnv('WPE_P006_PHASE'),
        'cell_id' => $in['cell'], 'environment' => sEnvironment($in),
        'logical_pair' => ['free' => $free, 'pro' => $pro], 'artifact_identity' => $artifact,
        'expected_compatibility_state' => $state, 'compatibility' => $compat,
        'premium_mutations_allowed' => false, 'module_state' => $modules,
        'snapshot' => $snapshot, 'deactivation' => $deactivation,
        'compatibility_persistence_keys' => [], 'external_object_cache' => false,
        'network_attempt_count' => 0, 'fatal_or_error' => null,
    ];
}
/** @param array<string,mixed> $in @return array<string,mixed> */
function sAggregate(array $in): array {
    $baseline = sRead($in['evidence_dir'] . '/baseline.json');
    $files = [
        'a-activate-1.json', 'a-observe-1.json', 'a-activate-2.json', 'a-observe-2.json',
        'b-activate-1.json', 'b-observe-1.json', 'b-activate-2.json', 'b-observe-2.json',
    ];
    $records = [];
    foreach ($files as $file) {
        $records[$file] = sRead($in['evidence_dir'] . '/' . $file);
        $record = $records[$file];
        sAssert(($record['status'] ?? null) === 'PASS', $file . ': not PASS');
        sAssert(($record['fixture_id'] ?? null) === 'FP-33', $file . ': fixture drift');
        sAssert(($record['network_attempt_count'] ?? null) === 0, $file . ': network attempt');
        sAssert(array_key_exists('fatal_or_error', $record) && $record['fatal_or_error'] === null, $file . ': fatal/error');
    }
    foreach (['a-activate-1.json','a-activate-2.json','b-activate-1.json','b-activate-2.json'] as $file) {
        $record = $records[$file];
        sAssert(($record['wpe_state_unchanged_during_activation'] ?? null) === true, $file . ': activation mutation');
        sAssert($record['before_snapshot']['snapshot_sha256'] === $record['after_snapshot']['snapshot_sha256'], $file . ': activation snapshot mismatch');
    }
    foreach (['a-observe-1.json','a-observe-2.json'] as $file) {
        sAssert(($records[$file]['logical_pair'] ?? null) === ['free' => 'F2', 'pro' => 'P0'], $file . ': pair drift');
        sAssert(($records[$file]['compatibility']['state'] ?? null) === 'free_version_too_new', $file . ': state drift');
    }
    foreach (['b-observe-1.json','b-observe-2.json'] as $file) {
        sAssert(($records[$file]['logical_pair'] ?? null) === ['free' => 'F1', 'pro' => 'P2'], $file . ': pair drift');
        sAssert(($records[$file]['compatibility']['state'] ?? null) === 'free_version_too_old', $file . ': state drift');
    }
    foreach (['a-observe-1.json','a-observe-2.json','b-observe-1.json','b-observe-2.json'] as $file) {
        $record = $records[$file];
        sAssert($record['snapshot']['snapshot_sha256'] === $baseline['snapshot']['snapshot_sha256'], $file . ': baseline WPE state drift');
        sAssert(($record['module_state']['premium_modules'] ?? null) === [], $file . ': premium module drift');
        sAssert(($record['compatibility']['premium_migrations_allowed'] ?? null) === false, $file . ': premium migration drift');
        sAssert(($record['deactivation']['wpe_state_unchanged'] ?? null) === true, $file . ': deactivation mutation');
    }
    return [
        'status' => 'PASS', 'protocol' => 'P-006', 'wave' => '1S', 'fixture' => 'FP-33',
        'classification' => 'FORMAL FP-33 RE-EXECUTION / NON-CERTIFYING',
        'authorization' => P006S_GRANT, 'source_sha' => sEnv('WPE_P006_SOURCE_SHA'),
        'cell_id' => $in['cell'], 'baseline_snapshot_sha256' => $baseline['snapshot']['snapshot_sha256'],
        'scenario_a' => ['pair' => 'F2/P0', 'expected_state' => 'free_version_too_new', 'activation_attempts' => 2, 'observations' => 2],
        'scenario_b' => ['pair' => 'F1/P2', 'expected_state' => 'free_version_too_old', 'activation_attempts' => 2, 'observations' => 2],
        'wpe_state_unchanged' => true, 'sentinel_preserved' => true, 'network_attempt_count' => 0,
        'accounting_if_terminally_accepted' => ['documented' => 144, 'executed' => 52, 'pass' => 52, 'fail' => 0, 'inconclusive' => 0],
        'prior_execution_reclassified_not_double_counted' => true,
        'product_runtime_source_modified' => false, 'destructive_migration_executed' => false,
        'pair_certified' => false, 'runtime_certified' => false, 'migration_certified' => false,
        'provider_or_remote_entitlement_used' => false, 'updater_or_tuf_certified' => false,
        'adr_0010' => 'Proposed', 'ga_or_release_authorized' => false,
    ];
}

$mode = trim((string) getenv('WPE_P006_EXECUTION_MODE'));
if ($mode === '') { $mode = $argv[1] ?? ''; }

try {
    $identity = sIdentity();
    if ($mode === 'static') {
        $result = sStatic($identity);
        sWrite(sEnv('WPE_P006_STATIC_SCAN_OUTPUT'), $result);
    } else {
        $in = sInput();
        if ($mode === 'prepare') {
            $result = sPrepare($in);
            sWrite($in['evidence_dir'] . '/prepare.json', $result);
        } elseif ($mode === 'baseline') {
            $result = sBaseline($in);
            sWrite($in['evidence_dir'] . '/baseline.json', $result);
        } elseif ($mode === 'activate') {
            $result = sActivate($identity, $in);
            sWrite($in['evidence_dir'] . '/' . sEnv('WPE_P006_EVIDENCE_FILE'), $result);
        } elseif ($mode === 'observe') {
            $result = sObserve($identity, $in);
            sWrite($in['evidence_dir'] . '/' . sEnv('WPE_P006_EVIDENCE_FILE'), $result);
        } elseif ($mode === 'aggregate') {
            $result = sAggregate($in);
            sWrite($in['evidence_dir'] . '/summary.json', $result);
        } else {
            sFail('Usage: <static|prepare|baseline|activate|observe|aggregate>');
        }
    }
    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 Wave 1S FP-33] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
