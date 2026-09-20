<?php

declare(strict_types=1);

const P006T_GRANT = 'GOV-P001-CF-TEMP-018';
const P006T_SENTINEL_KEY = 'wpe_p006_wave1t_sentinel';
const P006T_SENTINEL_VALUE = 'wave1t-preserve-v1';
const P006T_FREE_MIGRATIONS = [
    '006.create-compiled-registration-atomic-store',
    '007.create-definition-persistence',
    '008.create-audit-ptd-store',
];
const P006T_PRO_MIGRATIONS = [
    '220.custom_tables_migration_runs_v1',
    '221.custom_tables_migration_execution_confirmations_v1',
];
const P006T_PRO_MODULES = [
    'roles', 'admin-menu', 'settings', 'dashboard', 'profiles', 'membership',
    'builder-widgets', 'forms-workflows', 'cron', 'notifications', 'emails', 'chat',
];

function tFail(string $message): never { throw new RuntimeException($message); }
function tAssert(bool $ok, string $message): void { if (!$ok) { tFail($message); } }
function tEnv(string $key): string {
    $value = trim((string) getenv($key));
    if ($value === '') { tFail('Missing env: ' . $key); }
    return $value;
}
function tOptEnv(string $key): string { return trim((string) getenv($key)); }
function tHashFile(string $path): string {
    tAssert(is_file($path) && filesize($path) > 0, 'Missing artifact: ' . $path);
    $hash = hash_file('sha256', $path);
    tAssert(is_string($hash), 'Unable to hash artifact: ' . $path);
    return $hash;
}
/** @param array<string,mixed> $value */
function tWrite(string $path, array $value): void {
    if (!is_dir(dirname($path))) {
        tAssert(mkdir(dirname($path), 0775, true) || is_dir(dirname($path)), 'Unable to create evidence directory');
    }
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    tAssert(file_put_contents($path, $json . PHP_EOL) !== false, 'Unable to write evidence: ' . $path);
}
/** @return array<string,mixed> */
function tRead(string $path): array {
    tAssert(is_file($path), 'Missing evidence: ' . $path);
    $value = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    tAssert(is_array($value), 'Invalid evidence: ' . $path);
    return $value;
}
function tPair(string $freeHash, string $proHash): string { return hash('sha256', $freeHash . ':' . $proHash); }

/** @return array<string,mixed> */
function tIdentity(): array {
    $identity = tRead(tEnv('WPE_P006_IDENTITY_PATH'));
    tAssert(($identity['temporary_approval_id'] ?? null) === P006T_GRANT, 'Temporary approval mismatch');
    tAssert(($identity['source_sha'] ?? null) === tEnv('WPE_P006_SOURCE_SHA'), 'Source SHA mismatch');
    $nodes = $identity['nodes'] ?? null;
    tAssert(is_array($nodes), 'Candidate nodes missing');
    $dir = rtrim(tEnv('WPE_P006_CANDIDATE_DIR'), '/\\');
    foreach (['F0', 'F2', 'P0'] as $node) {
        tAssert(isset($nodes[$node]) && is_array($nodes[$node]), 'Node missing: ' . $node);
        $file = $dir . '/' . (string) $nodes[$node]['artifact'];
        tAssert(hash_equals((string) $nodes[$node]['sha256'], tHashFile($file)), 'ZIP hash drift: ' . $node);
    }
    foreach ([
        'F0_P0' => ['F0', 'P0', 'compatible'],
        'F2_P0' => ['F2', 'P0', 'free_version_too_new'],
    ] as $key => $spec) {
        [$free, $pro, $state] = $spec;
        $pair = $identity['pairs'][$key] ?? null;
        tAssert(is_array($pair), 'Pair missing: ' . $key);
        tAssert(($pair['expected_state'] ?? null) === $state, 'Expected state drift: ' . $key);
        $expected = tPair((string) $nodes[$free]['sha256'], (string) $nodes[$pro]['sha256']);
        tAssert(($pair['pair_id_sha256'] ?? null) === $expected, 'Pair id drift: ' . $key);
    }
    return $identity;
}

/** @return array<string,mixed> */
function tInput(): array {
    $wp = tEnv('WPE_P006_EXPECTED_WP');
    $php = tEnv('WPE_P006_EXPECTED_PHP');
    $mysql = tEnv('WPE_P006_EXPECTED_MYSQL');
    $cell = match (true) {
        $wp === '6.9' && $php === '8.2' && $mysql === '8.4' => 'minimum',
        $wp === '7.1' && $php === '8.5' && $mysql === '8.4' => 'reference',
        default => null,
    };
    tAssert(is_string($cell), 'Runtime cell outside TEMP-018');
    $prefix = tEnv('WPE_P006_TABLE_PREFIX');
    tAssert(preg_match('/^wpep006t[0-9]{2}_$/', $prefix) === 1, 'Unexpected Wave 1T table prefix');
    return [
        'cell' => $cell,
        'wp' => $wp,
        'php' => $php,
        'mysql' => $mysql,
        'wp_dir' => rtrim(tEnv('WPE_TEST_WORDPRESS_DIR'), '/\\'),
        'evidence_dir' => rtrim(tEnv('WPE_P006_EVIDENCE_DIR'), '/\\'),
        'network_log' => tEnv('WPE_P006_NETWORK_LOG'),
        'sql_log' => tEnv('WPE_P006_SQL_LOG'),
        'table_prefix' => $prefix,
    ];
}

/** @param array<string,mixed> $in */
function tResetLogs(array $in): void {
    tAssert(file_put_contents($in['network_log'], '') !== false, 'Unable to reset network log');
    tAssert(file_put_contents($in['sql_log'], '') !== false, 'Unable to reset SQL log');
}
/** @param array<string,mixed> $in @return list<string> */
function tNetwork(array $in): array {
    if (!is_file($in['network_log'])) { return []; }
    $lines = file($in['network_log'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return is_array($lines) ? array_values(array_map('strval', $lines)) : [];
}
/** @param array<string,mixed> $in @return list<string> */
function tSql(array $in): array {
    if (!is_file($in['sql_log'])) { return []; }
    $lines = file($in['sql_log'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return is_array($lines) ? array_values(array_map('strval', $lines)) : [];
}
/** @param list<string> $queries @return list<string> */
function tMigrationInsertOrder(array $queries): array {
    $ids = [];
    foreach ($queries as $query) {
        if (preg_match("/INSERT\\s+IGNORE\\s+INTO\\s+`[^`]*wpe_migrations`.*?VALUES\\s*\\(\\s*'([^']+)'/i", $query, $m) === 1) {
            $ids[] = (string) $m[1];
        }
    }
    return $ids;
}
/** @param list<string> $queries @return list<string> */
function tWpeDdl(array $queries): array {
    $ddl = [];
    foreach ($queries as $query) {
        if (stripos($query, 'wpe_') !== false && preg_match('/\\b(?:CREATE|ALTER|DROP|RENAME|TRUNCATE)\\s+TABLE\\b/i', $query) === 1) {
            $ddl[] = $query;
        }
    }
    return $ddl;
}

function tEnvelope(string $uri = '/'): void {
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
function tBoot(array $in): void {
    tEnvelope('/');
    require $in['wp_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    tAssert(defined('WPE_P006T_PROBE_ACTIVE'), 'Wave 1T MU probe not active');
}
/** @param array<string,mixed> $in @return array<string,string> */
function tEnvironment(array $in): array {
    global $wp_version, $wpdb;
    tAssert(isset($wp_version) && is_string($wp_version), 'WordPress version unavailable');
    tAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable');
    tAssert($wp_version === $in['wp'], 'WordPress version drift');
    tAssert(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION === $in['php'], 'PHP version drift');
    $db = (string) $wpdb->db_version();
    tAssert(str_starts_with($db, $in['mysql']), 'MySQL version drift');
    tAssert($wpdb->prefix === $in['table_prefix'], 'WordPress table prefix drift');
    return ['wordpress' => $wp_version, 'php' => PHP_VERSION, 'mysql' => $db, 'table_prefix' => $wpdb->prefix];
}

/** @param array<string,mixed> $in */
function tSelect(array $in, string $free, string $pro): void {
    tAssert(in_array($free, ['F0', 'F2'], true), 'Unsupported Free node selection');
    tAssert($pro === 'P0', 'Unsupported Pro node selection');
    $plugins = $in['wp_dir'] . '/wp-content/plugins';
    $stage = $in['wp_dir'] . '/wp-content/p006-wave1t-packages';
    $specs = [
        'wpessential' => [strtolower($free), 'wpessential.php'],
        'wpessential-pro' => [strtolower($pro), 'wpessential-pro.php'],
    ];
    foreach ($specs as $slug => [$node, $entry]) {
        $target = $stage . '/' . $node . '/' . $slug;
        $link = $plugins . '/' . $slug;
        $next = $plugins . '/.' . $slug . '.p006-wave1t-next';
        tAssert(is_file($target . '/' . $entry), 'Candidate entry missing for ' . $slug);
        if (is_link($next) || file_exists($next)) { unlink($next); }
        tAssert(symlink('../p006-wave1t-packages/' . $node . '/' . $slug, $next), 'Unable to create candidate symlink');
        if (is_link($link) || file_exists($link)) { tAssert(unlink($link), 'Unable to replace candidate symlink'); }
        tAssert(rename($next, $link), 'Unable to publish candidate symlink');
        tAssert(is_file($link . '/' . $entry), 'Published candidate entry missing');
    }
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,string> */
function tTransport(array $identity, array $in, string $free, string $pro): array {
    $freePath = realpath($in['wp_dir'] . '/wp-content/plugins/wpessential');
    $proPath = realpath($in['wp_dir'] . '/wp-content/plugins/wpessential-pro');
    tAssert(is_string($freePath) && is_string($proPath), 'Transport path unavailable');
    tAssert(str_contains($freePath, '/p006-wave1t-packages/' . strtolower($free) . '/wpessential'), 'Free transport drift');
    tAssert(str_contains($proPath, '/p006-wave1t-packages/' . strtolower($pro) . '/wpessential-pro'), 'Pro transport drift');
    $tree = static function (string $root, string $slug): string {
        $files = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $item) {
            tAssert(!$item->isLink(), 'Nested symlink in candidate payload');
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
    tAssert($freeTree === $identity['nodes'][$free]['payload_tree_sha256'], 'Free payload tree drift');
    tAssert($proTree === $identity['nodes'][$pro]['payload_tree_sha256'], 'Pro payload tree drift');
    $pairId = tPair((string) $identity['nodes'][$free]['sha256'], (string) $identity['nodes'][$pro]['sha256']);
    tAssert($pairId === $identity['pairs'][$free . '_' . $pro]['pair_id_sha256'], 'Pair id drift');
    return [
        'free_sha256' => (string) $identity['nodes'][$free]['sha256'],
        'pro_sha256' => (string) $identity['nodes'][$pro]['sha256'],
        'free_payload_tree_sha256' => $freeTree,
        'pro_payload_tree_sha256' => $proTree,
        'pair_id_sha256' => $pairId,
    ];
}

/** @return array<string,mixed> */
function tSnapshot(): array {
    global $wpdb;
    tAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable for snapshot');
    $prefix = $wpdb->prefix;
    tAssert(preg_match('/^[A-Za-z0-9_]+$/', $prefix) === 1, 'Unsafe prefix in snapshot');
    $all = $wpdb->get_col('SHOW TABLES');
    tAssert(is_array($all), 'Unable to list tables');
    $tables = [];
    foreach ($all as $table) {
        $table = (string) $table;
        if (str_starts_with($table, $prefix . 'wpe_')) { $tables[] = $table; }
    }
    sort($tables, SORT_STRING);
    $schema = [];
    foreach ($tables as $table) {
        $row = $wpdb->get_row('SHOW CREATE TABLE `' . $table . '`', ARRAY_N);
        tAssert(is_array($row) && isset($row[1]) && is_string($row[1]), 'Unable to inspect schema: ' . $table);
        $ddl = preg_replace('/AUTO_INCREMENT=\\d+\\s*/', '', $row[1]);
        tAssert(is_string($ddl), 'Unable to normalize schema: ' . $table);
        $schema[$table] = hash('sha256', $ddl);
    }
    ksort($schema, SORT_STRING);
    $migrationTable = $prefix . 'wpe_migrations';
    $rows = [];
    if (in_array($migrationTable, $tables, true)) {
        $values = $wpdb->get_results(
            'SELECT migration_id, DATE_FORMAT(applied_at, \'%Y-%m-%dT%H:%i:%s.%fZ\') AS applied_at FROM `' . $migrationTable . '` ORDER BY applied_at ASC, migration_id ASC',
            ARRAY_A,
        );
        if (is_array($values)) {
            foreach ($values as $row) {
                $rows[] = ['migration_id' => (string) ($row['migration_id'] ?? ''), 'applied_at' => (string) ($row['applied_at'] ?? '')];
            }
        }
    }
    $ids = array_values(array_map(static fn (array $row): string => $row['migration_id'], $rows));
    $proTables = [
        $prefix . 'wpe_custom_table_migration_runs',
        $prefix . 'wpe_custom_table_migration_confirmations',
    ];
    $presence = [];
    foreach ($proTables as $table) { $presence[$table] = in_array($table, $tables, true); }
    $data = [
        'migration_rows' => $rows,
        'migration_ids' => $ids,
        'wpe_tables' => $tables,
        'wpe_table_schema_sha256' => $schema,
        'pro_custom_table_store_presence' => $presence,
        'sentinel' => get_option(P006T_SENTINEL_KEY, '__missing__'),
    ];
    $data['snapshot_sha256'] = hash('sha256', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    return $data;
}
/** @param array<string,mixed> $snapshot @param list<string> $expected */
function tAssertIds(array $snapshot, array $expected, string $label): void {
    tAssert(($snapshot['migration_ids'] ?? null) === $expected, $label . ': migration id set/order drift');
}
/** @param array<string,mixed> $snapshot */
function tAssertSentinel(array $snapshot): void {
    tAssert(($snapshot['sentinel'] ?? null) === P006T_SENTINEL_VALUE, 'Sentinel drift');
}
/** @param array<string,mixed> $snapshot */
function tAssertProTables(array $snapshot, bool $expected): void {
    foreach (($snapshot['pro_custom_table_store_presence'] ?? []) as $table => $present) {
        tAssert($present === $expected, 'Pro table presence drift for ' . $table);
    }
}
/** @return array<string,mixed>|null */
function tCompatibility(): ?array {
    $value = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    return is_array($value) ? $value : null;
}
/** @return list<string> */
function tPremiumModules(): array {
    if (!class_exists(\WPEssential\Bootstrap\Plugin::class)) { return []; }
    $kernel = \WPEssential\Bootstrap\Plugin::kernel();
    if (!$kernel instanceof \WPEssential\Kernel\Kernel || !$kernel->isBooted()) { return []; }
    $registry = $kernel->modules();
    $ids = [];
    foreach (P006T_PRO_MODULES as $id) { if ($registry->has($id)) { $ids[] = $id; } }
    sort($ids, SORT_STRING);
    return $ids;
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function tInstall(array $identity, array $in): array {
    $free = tEnv('WPE_P006_EXPECTED_FREE_NODE');
    $pro = tEnv('WPE_P006_EXPECTED_PRO_NODE');
    tSelect($in, $free, $pro);
    $artifact = tTransport($identity, $in, $free, $pro);
    tResetLogs($in);
    tEnvelope('/wp-admin/install.php');
    if (!defined('WP_INSTALLING')) { define('WP_INSTALLING', true); }
    require $in['wp_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    tAssert(defined('WPE_P006T_PROBE_ACTIVE'), 'Wave 1T MU probe not active during install');
    if (!is_blog_installed()) {
        $installed = wp_install('WPEssential P-006 Wave 1T', 'p006_admin', 'p006-admin@example.test', false, '', 'p006-test-password-strong');
        tAssert(!is_wp_error($installed), 'Disposable WordPress installation failed');
    }
    if (function_exists('wp_installing')) { wp_installing(false); }
    tAssert(is_blog_installed(), 'Disposable WordPress installation did not complete');
    tAssert(tNetwork($in) === [], 'Outbound HTTP observed during install');
    return [
        'status' => 'PASS', 'phase' => 'install', 'cell_id' => $in['cell'],
        'logical_pair' => ['free' => $free, 'pro' => $pro], 'artifact_identity' => $artifact,
        'table_prefix' => $in['table_prefix'], 'network_attempt_count' => 0,
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function tInitialActivate(array $identity, array $in, bool $activatePro): array {
    $free = tEnv('WPE_P006_EXPECTED_FREE_NODE');
    $pro = tEnv('WPE_P006_EXPECTED_PRO_NODE');
    $artifact = tTransport($identity, $in, $free, $pro);
    tResetLogs($in);
    tBoot($in);
    tAssert(!is_plugin_active('wpessential/wpessential.php'), 'Free unexpectedly active before initial activation');
    tAssert(!is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro unexpectedly active before initial activation');
    update_option(P006T_SENTINEL_KEY, P006T_SENTINEL_VALUE, false);
    $freeResult = activate_plugin('wpessential/wpessential.php', '', false, true);
    tAssert(!is_wp_error($freeResult) && is_plugin_active('wpessential/wpessential.php'), 'Free activation failed');
    if ($activatePro) {
        $proResult = activate_plugin('wpessential-pro/wpessential-pro.php', '', false, true);
        tAssert(!is_wp_error($proResult) && is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro activation failed');
    }
    $snapshot = tSnapshot();
    tAssertIds($snapshot, [], 'Initial activation');
    tAssertSentinel($snapshot);
    tAssert(tMigrationInsertOrder(tSql($in)) === [], 'Migration marker written during activation API call');
    tAssert(tWpeDdl(tSql($in)) === [], 'WPE DDL executed during activation API call');
    tAssert(tNetwork($in) === [], 'Outbound HTTP observed during activation API call');
    return [
        'status' => 'PASS', 'phase' => $activatePro ? 'activate-both' : 'activate-free', 'cell_id' => $in['cell'],
        'environment' => tEnvironment($in), 'logical_pair' => ['free' => $free, 'pro' => $pro], 'artifact_identity' => $artifact,
        'snapshot' => $snapshot, 'migration_insert_order' => [], 'wpe_ddl' => [], 'network_attempt_count' => 0,
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function tSettleCompatible(array $identity, array $in): array {
    $artifact = tTransport($identity, $in, 'F0', 'P0');
    tResetLogs($in);
    tBoot($in);
    $snapshot = tSnapshot();
    $all = array_merge(P006T_FREE_MIGRATIONS, P006T_PRO_MIGRATIONS);
    tAssertIds($snapshot, $all, 'Compatible settled state');
    tAssertSentinel($snapshot); tAssertProTables($snapshot, true);
    $order = tMigrationInsertOrder(tSql($in));
    tAssert($order === $all, 'Compatible first boot migration order drift');
    $compat = tCompatibility();
    tAssert(is_array($compat) && ($compat['state'] ?? null) === 'compatible', 'Compatible pair did not resolve compatible');
    tAssert(($compat['premium_migrations_allowed'] ?? null) === true, 'Compatible pair denied premium migrations');
    tAssert(tNetwork($in) === [], 'Outbound HTTP during compatible settle');
    return [
        'status' => 'PASS', 'phase' => 'settle-compatible', 'cell_id' => $in['cell'], 'environment' => tEnvironment($in),
        'artifact_identity' => $artifact, 'compatibility' => $compat, 'snapshot' => $snapshot,
        'migration_insert_order' => $order, 'network_attempt_count' => 0,
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function tFp77(array $identity, array $in): array {
    $baseline = tRead($in['evidence_dir'] . '/fp77-settled.json');
    $artifact = tTransport($identity, $in, 'F0', 'P0');
    tResetLogs($in);
    tBoot($in);
    $snapshot = tSnapshot();
    $all = array_merge(P006T_FREE_MIGRATIONS, P006T_PRO_MIGRATIONS);
    tAssertIds($snapshot, $all, 'FP-77');
    tAssertSentinel($snapshot); tAssertProTables($snapshot, true);
    tAssert(($baseline['snapshot']['snapshot_sha256'] ?? null) === $snapshot['snapshot_sha256'], 'FP-77 settled schema changed on clean boot');
    $order = tMigrationInsertOrder(tSql($in));
    $ddl = tWpeDdl(tSql($in));
    tAssert($order === [], 'FP-77 wrote migration marker on matching schema boot');
    $stateStoreEnsurePrefix = 'CREATE TABLE IF NOT EXISTS `' . $in['table_prefix'] . 'wpe_migrations`';
    foreach ($ddl as $query) {
        tAssert(
            str_starts_with($query, $stateStoreEnsurePrefix),
            'FP-77 executed non-readiness WPE DDL on matching schema boot',
        );
    }
    $compat = tCompatibility();
    tAssert(is_array($compat) && ($compat['state'] ?? null) === 'compatible', 'FP-77 compatibility drift');
    tAssert(tNetwork($in) === [], 'FP-77 outbound HTTP observed');
    return [
        'status' => 'PASS', 'fixture_id' => 'FP-77', 'cell_id' => $in['cell'], 'environment' => tEnvironment($in),
        'logical_pair' => ['free' => 'F0', 'pro' => 'P0'], 'artifact_identity' => $artifact,
        'before_snapshot_sha256' => $baseline['snapshot']['snapshot_sha256'], 'after_snapshot' => $snapshot,
        'migration_insert_order' => [], 'wpe_ddl' => $ddl, 'state_store_readiness_ddl_only' => true,
        'compatibility' => $compat, 'network_attempt_count' => 0, 'fatal_or_error' => null,
    ];
}

/** @param array<string,mixed> $in @return array<string,mixed> */
function tSettleFree(array $in): array {
    tResetLogs($in);
    tBoot($in);
    $snapshot = tSnapshot();
    tAssertIds($snapshot, P006T_FREE_MIGRATIONS, 'Free settled state');
    tAssertSentinel($snapshot); tAssertProTables($snapshot, false);
    $order = tMigrationInsertOrder(tSql($in));
    tAssert($order === P006T_FREE_MIGRATIONS, 'Free first boot migration order drift');
    tAssert(tNetwork($in) === [], 'Outbound HTTP during Free settle');
    return [
        'status' => 'PASS', 'phase' => 'settle-free', 'cell_id' => $in['cell'], 'environment' => tEnvironment($in),
        'snapshot' => $snapshot, 'migration_insert_order' => $order, 'network_attempt_count' => 0,
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function tFp78(array $identity, array $in): array {
    $beforeRecord = tRead($in['evidence_dir'] . '/fp78-before.json');
    $artifact = tTransport($identity, $in, 'F0', 'P0');
    tAssert(($beforeRecord['snapshot']['migration_ids'] ?? null) === [], 'FP-78 pre-state already contains migrations');
    tResetLogs($in);
    tBoot($in);
    $snapshot = tSnapshot();
    tAssertIds($snapshot, P006T_FREE_MIGRATIONS, 'FP-78');
    tAssertSentinel($snapshot); tAssertProTables($snapshot, false);
    $order = tMigrationInsertOrder(tSql($in));
    tAssert($order === P006T_FREE_MIGRATIONS, 'FP-78 Free migration order drift');
    tAssert(tNetwork($in) === [], 'FP-78 outbound HTTP observed');
    return [
        'status' => 'PASS', 'fixture_id' => 'FP-78', 'cell_id' => $in['cell'], 'environment' => tEnvironment($in),
        'logical_pair' => ['free' => 'F0', 'pro' => 'P0-inactive'], 'artifact_identity' => $artifact,
        'before_snapshot' => $beforeRecord['snapshot'], 'after_snapshot' => $snapshot,
        'migration_insert_order' => $order, 'network_attempt_count' => 0, 'fatal_or_error' => null,
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function tActivatePro(array $identity, array $in): array {
    $artifact = tTransport($identity, $in, 'F0', 'P0');
    tResetLogs($in);
    tBoot($in);
    tAssert(is_plugin_active('wpessential/wpessential.php'), 'Free inactive before Pro activation');
    tAssert(!is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro already active before Pro activation');
    $before = tSnapshot();
    tAssertIds($before, P006T_FREE_MIGRATIONS, 'Pre-Pro activation');
    tAssertProTables($before, false); tAssertSentinel($before);
    tResetLogs($in);
    $result = activate_plugin('wpessential-pro/wpessential-pro.php', '', false, true);
    tAssert(!is_wp_error($result) && is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro activation failed');
    $after = tSnapshot();
    tAssert($before['snapshot_sha256'] === $after['snapshot_sha256'], 'Pro activation API mutated WPE schema/migrations');
    tAssert(tMigrationInsertOrder(tSql($in)) === [], 'Pro activation wrote migration marker');
    tAssert(tWpeDdl(tSql($in)) === [], 'Pro activation executed WPE DDL');
    tAssert(tNetwork($in) === [], 'Outbound HTTP during Pro activation');
    return [
        'status' => 'PASS', 'phase' => 'activate-pro', 'cell_id' => $in['cell'], 'environment' => tEnvironment($in),
        'artifact_identity' => $artifact, 'before_snapshot' => $before, 'after_snapshot' => $after,
        'migration_insert_order' => [], 'wpe_ddl' => [], 'network_attempt_count' => 0,
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function tFp79(array $identity, array $in): array {
    $beforeRecord = tRead($in['evidence_dir'] . '/fp79-pro-activated.json');
    $before = $beforeRecord['after_snapshot'] ?? null;
    tAssert(is_array($before), 'FP-79 pre-state unavailable');
    $artifact = tTransport($identity, $in, 'F0', 'P0');
    tResetLogs($in);
    tBoot($in);
    $after = tSnapshot();
    $all = array_merge(P006T_FREE_MIGRATIONS, P006T_PRO_MIGRATIONS);
    tAssertIds($after, $all, 'FP-79');
    tAssertSentinel($after); tAssertProTables($after, true);
    $added = array_values(array_diff($after['migration_ids'], $before['migration_ids']));
    tAssert($added === P006T_PRO_MIGRATIONS, 'FP-79 added migration set drift');
    $order = tMigrationInsertOrder(tSql($in));
    tAssert($order === P006T_PRO_MIGRATIONS, 'FP-79 Pro migration order drift');
    $compat = tCompatibility();
    tAssert(is_array($compat) && ($compat['state'] ?? null) === 'compatible', 'FP-79 compatibility did not pass');
    tAssert(($compat['premium_migrations_allowed'] ?? null) === true, 'FP-79 premium migration admission denied');
    tAssert(defined('WPE_PRO_LOCAL_ENTITLEMENT_STATE') && WPE_PRO_LOCAL_ENTITLEMENT_STATE === 'pro_active', 'FP-79 local entitlement fixture drift');
    tAssert(tNetwork($in) === [], 'FP-79 outbound HTTP observed');
    return [
        'status' => 'PASS', 'fixture_id' => 'FP-79', 'cell_id' => $in['cell'], 'environment' => tEnvironment($in),
        'logical_pair' => ['free' => 'F0', 'pro' => 'P0'], 'artifact_identity' => $artifact,
        'before_snapshot' => $before, 'after_snapshot' => $after, 'added_migration_ids' => $added,
        'migration_insert_order' => $order, 'compatibility' => $compat,
        'local_test_entitlement' => 'pro_active', 'network_attempt_count' => 0, 'fatal_or_error' => null,
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function tFp88(array $identity, array $in): array {
    $beforeRecord = tRead($in['evidence_dir'] . '/fp88-before.json');
    tAssert(($beforeRecord['snapshot']['migration_ids'] ?? null) === [], 'FP-88 pre-state already contains migrations');
    $artifact = tTransport($identity, $in, 'F0', 'P0');
    tResetLogs($in);
    tBoot($in);
    $snapshot = tSnapshot();
    $all = array_merge(P006T_FREE_MIGRATIONS, P006T_PRO_MIGRATIONS);
    tAssertIds($snapshot, $all, 'FP-88');
    tAssertSentinel($snapshot); tAssertProTables($snapshot, true);
    $order = tMigrationInsertOrder(tSql($in));
    tAssert($order === $all, 'FP-88 observed migration order drift');
    $compat = tCompatibility();
    tAssert(is_array($compat) && ($compat['state'] ?? null) === 'compatible', 'FP-88 compatibility did not pass');
    tAssert(tNetwork($in) === [], 'FP-88 outbound HTTP observed');
    return [
        'status' => 'PASS', 'fixture_id' => 'FP-88', 'cell_id' => $in['cell'], 'environment' => tEnvironment($in),
        'logical_pair' => ['free' => 'F0', 'pro' => 'P0'], 'artifact_identity' => $artifact,
        'before_snapshot' => $beforeRecord['snapshot'], 'after_snapshot' => $snapshot,
        'runtime_migration_insert_order' => $order,
        'free_before_pro_order_proved' => true, 'compatibility' => $compat,
        'network_attempt_count' => 0, 'fatal_or_error' => null,
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function tFp80(array $identity, array $in): array {
    $beforeRecord = tRead($in['evidence_dir'] . '/fp80-before.json');
    tAssert(($beforeRecord['snapshot']['migration_ids'] ?? null) === [], 'FP-80 pre-state already contains migrations');
    $artifact = tTransport($identity, $in, 'F2', 'P0');
    tResetLogs($in);
    tBoot($in);
    $snapshot = tSnapshot();
    tAssertIds($snapshot, P006T_FREE_MIGRATIONS, 'FP-80');
    tAssertSentinel($snapshot); tAssertProTables($snapshot, false);
    $order = tMigrationInsertOrder(tSql($in));
    tAssert($order === P006T_FREE_MIGRATIONS, 'FP-80 unexpected migration order');
    $compat = tCompatibility();
    tAssert(is_array($compat) && ($compat['state'] ?? null) === 'free_version_too_new', 'FP-80 incompatibility state drift');
    tAssert(($compat['premium_boot_allowed'] ?? null) === false, 'FP-80 incompatible pair allowed premium boot');
    tAssert(($compat['premium_migrations_allowed'] ?? null) === false, 'FP-80 incompatible pair allowed premium migrations');
    tAssert(defined('WPE_PRO_LOCAL_ENTITLEMENT_STATE') && WPE_PRO_LOCAL_ENTITLEMENT_STATE === 'pro_active', 'FP-80 local entitled fixture missing');
    tAssert(tPremiumModules() === [], 'FP-80 premium modules registered despite incompatibility');
    tAssert(tNetwork($in) === [], 'FP-80 outbound HTTP observed');
    return [
        'status' => 'PASS', 'fixture_id' => 'FP-80', 'cell_id' => $in['cell'], 'environment' => tEnvironment($in),
        'logical_pair' => ['free' => 'F2', 'pro' => 'P0'], 'artifact_identity' => $artifact,
        'requested_local_entitlement' => 'pro_active', 'compatibility' => $compat,
        'before_snapshot' => $beforeRecord['snapshot'], 'after_snapshot' => $snapshot,
        'migration_insert_order' => $order, 'pro_migrations_absent' => true,
        'premium_modules' => [], 'network_attempt_count' => 0, 'fatal_or_error' => null,
    ];
}

/** @param array<string,mixed> $in @return array<string,mixed> */
function tAggregate(array $in): array {
    $fixtures = [];
    foreach (['77','78','79','80','88'] as $id) {
        $record = tRead($in['evidence_dir'] . '/fp' . $id . '.json');
        tAssert(($record['status'] ?? null) === 'PASS', 'FP-' . $id . ' is not PASS');
        tAssert(($record['fixture_id'] ?? null) === 'FP-' . $id, 'FP-' . $id . ' fixture id drift');
        tAssert(($record['network_attempt_count'] ?? null) === 0, 'FP-' . $id . ' network attempt observed');
        tAssert(array_key_exists('fatal_or_error', $record) && $record['fatal_or_error'] === null, 'FP-' . $id . ' fatal/error observed');
        $fixtures['FP-' . $id] = ['status' => 'PASS'];
    }
    return [
        'status' => 'PASS', 'protocol' => 'P-006', 'wave' => '1T',
        'classification' => 'FORMAL FP-77/78/79/80/88 EVIDENCE / NON-CERTIFYING',
        'authorization' => P006T_GRANT, 'source_sha' => tEnv('WPE_P006_SOURCE_SHA'), 'cell_id' => $in['cell'],
        'fixture_results' => $fixtures, 'formal_fp_fixtures_executed' => ['FP-77','FP-78','FP-79','FP-80','FP-88'],
        'accounting_if_terminally_accepted' => ['documented' => 144, 'executed' => 57, 'pass' => 57, 'fail' => 0, 'inconclusive' => 0],
        'product_runtime_source_modified' => false, 'destructive_migration_executed' => false,
        'provider_or_remote_entitlement_used' => false, 'pair_certified' => false, 'runtime_certified' => false,
        'migration_certified' => false, 'permanent_p001_cf_certified' => false,
        'updater_or_tuf_certified' => false, 'adr_0010' => 'Proposed', 'ga_or_release_authorized' => false,
    ];
}

$mode = trim((string) getenv('WPE_P006_EXECUTION_MODE'));
if ($mode === '') { $mode = $argv[1] ?? ''; }

try {
    $identity = tIdentity();
    $in = tInput();
    $result = match ($mode) {
        'install' => tInstall($identity, $in),
        'activate-both' => tInitialActivate($identity, $in, true),
        'activate-free' => tInitialActivate($identity, $in, false),
        'settle-compatible' => tSettleCompatible($identity, $in),
        'settle-free' => tSettleFree($in),
        'activate-pro' => tActivatePro($identity, $in),
        'fp77' => tFp77($identity, $in),
        'fp78' => tFp78($identity, $in),
        'fp79' => tFp79($identity, $in),
        'fp80' => tFp80($identity, $in),
        'fp88' => tFp88($identity, $in),
        'aggregate' => tAggregate($in),
        default => tFail('Usage: <install|activate-both|activate-free|settle-compatible|settle-free|activate-pro|fp77|fp78|fp79|fp80|fp88|aggregate>'),
    };
    if ($mode === 'aggregate') {
        tWrite($in['evidence_dir'] . '/summary.json', $result);
    } else {
        $file = tEnv('WPE_P006_EVIDENCE_FILE');
        tWrite($in['evidence_dir'] . '/' . $file, $result);
    }
    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 Wave 1T schema migration] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
