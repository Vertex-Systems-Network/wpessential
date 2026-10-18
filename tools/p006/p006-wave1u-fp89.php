<?php

declare(strict_types=1);

const P006T_GRANT = 'GOV-P001-CF-TEMP-019';
const P006T_SENTINEL_KEY = 'wpe_p006_wave1u_fp89_sentinel';
const P006T_SENTINEL_VALUE = 'wave1u-fp89-preserve-v1';
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
    tAssert(tEnv('WPE_P006_AUTHORIZATION') === P006T_GRANT, 'Wave 1U grant mismatch');
    $identity = tRead(tEnv('WPE_P006_IDENTITY_PATH'));
    tAssert(($identity['source_sha'] ?? null) === tEnv('WPE_P006_SOURCE_SHA'), 'Source SHA mismatch');
    tAssert(($identity['pairs']['F0_P0']['preflight']['state'] ?? null) === 'compatible', 'Canonical pair drift');
    tAssert(($identity['pairs']['F0_P_API']['preflight']['state'] ?? null) === 'platform_api_too_old', 'P-API candidate drift');
    $dir = rtrim(tEnv('WPE_P006_CANDIDATE_DIR'), '/\\');
    foreach (['F0' => 'f0.zip', 'P0' => 'p0.zip', 'P_API' => 'p-api.zip'] as $node => $file) {
        tAssert(($identity['nodes'][$node]['sha256'] ?? null) === tHashFile($dir . '/' . $file), 'Candidate hash drift: ' . $node);
    }
    foreach (['F0_P0' => 'P0', 'F0_P_API' => 'P_API'] as $pair => $pro) {
        tAssert(($identity['pairs'][$pair]['pair_id_sha256'] ?? null) === tPair(
            (string) $identity['nodes']['F0']['sha256'], (string) $identity['nodes'][$pro]['sha256']
        ), 'Pair identity drift: ' . $pair);
    }
    return $identity;
}

function tInput(): array {
    $wp = tEnv('WPE_P006_EXPECTED_WP');
    $php = tEnv('WPE_P006_EXPECTED_PHP');
    $mysql = tEnv('WPE_P006_EXPECTED_MYSQL');
    $cell = match (true) {
        $wp === '6.9' && $php === '8.2' && $mysql === '8.4' => 'minimum',
        default => null,
    };
    tAssert(is_string($cell), 'Runtime cell outside TEMP-019');
    $prefix = tEnv('WPE_P006_TABLE_PREFIX');
    tAssert(preg_match('/^wpep006u89_$/', $prefix) === 1, 'Unexpected Wave 1U table prefix');
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
        if (stripos($query, 'wpe_') !== false && preg_match('/^\\s*(?:CREATE|ALTER|DROP|RENAME|TRUNCATE)\\s+TABLE\\b/i', $query) === 1) {
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
    tAssert(defined('WPE_P006U_PROBE_ACTIVE'), 'Wave 1U MU probe not active');
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
    tAssert(in_array($free, ['F0'], true), 'Unsupported Free node selection');
    tAssert(in_array($pro, ['P0', 'P_API'], true), 'Unsupported Pro node selection');
    $plugins = $in['wp_dir'] . '/wp-content/plugins';
    $stage = $in['wp_dir'] . '/wp-content/p006-wave1u-packages';
    $specs = [
        'wpessential' => [strtolower($free), 'wpessential.php'],
        'wpessential-pro' => [str_replace('_', '-', strtolower($pro)), 'wpessential-pro.php'],
    ];
    foreach ($specs as $slug => [$node, $entry]) {
        $target = $stage . '/' . $node . '/' . $slug;
        $link = $plugins . '/' . $slug;
        $next = $plugins . '/.' . $slug . '.p006-wave1u-next';
        tAssert(is_file($target . '/' . $entry), 'Candidate entry missing for ' . $slug);
        if (is_link($next) || file_exists($next)) { unlink($next); }
        tAssert(symlink('../p006-wave1u-packages/' . $node . '/' . $slug, $next), 'Unable to create candidate symlink');
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
    tAssert(str_contains($freePath, '/p006-wave1u-packages/' . strtolower($free) . '/wpessential'), 'Free transport drift');
    tAssert(str_contains($proPath, '/p006-wave1u-packages/' . str_replace('_', '-', strtolower($pro)) . '/wpessential-pro'), 'Pro transport drift');
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
    tAssert(defined('WPE_P006U_PROBE_ACTIVE'), 'Wave 1U MU probe not active during install');
    if (!is_blog_installed()) {
        $installed = wp_install('WPEssential P-006 Wave 1U', 'p006_admin', 'p006-admin@example.test', false, '', 'p006-test-password-strong');
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
function tFp89(array $identity, array $in): array {
    $beforeRecord = tRead($in['evidence_dir'] . '/fp89-before.json');
    tAssert(($beforeRecord['snapshot']['migration_ids'] ?? null) === [], 'FP-89 pre-state already contains migrations');
    $artifact = tTransport($identity, $in, 'F0', 'P_API');
    tResetLogs($in);
    tBoot($in);
    $snapshot = tSnapshot();
    tAssertIds($snapshot, P006T_FREE_MIGRATIONS, 'FP-89');
    tAssertSentinel($snapshot); tAssertProTables($snapshot, false);
    $order = tMigrationInsertOrder(tSql($in));
    tAssert($order === P006T_FREE_MIGRATIONS, 'FP-89 unexpected migration order');
    $compat = tCompatibility();
    tAssert(is_array($compat) && ($compat['state'] ?? null) === 'platform_api_too_old', 'FP-89 incompatibility state drift');
    tAssert(($compat['premium_boot_allowed'] ?? null) === false, 'FP-89 incompatible pair allowed premium boot');
    tAssert(($compat['premium_migrations_allowed'] ?? null) === false, 'FP-89 incompatible pair allowed premium migrations');
    tAssert(defined('WPE_PRO_LOCAL_ENTITLEMENT_STATE') && WPE_PRO_LOCAL_ENTITLEMENT_STATE === 'pro_active', 'FP-89 local entitled fixture missing');
    tAssert(tPremiumModules() === [], 'FP-89 premium modules registered despite incompatibility');
    tAssert(tNetwork($in) === [], 'FP-89 outbound HTTP observed');
    return [
        'status' => 'PASS', 'fixture_id' => 'FP-89', 'cell_id' => $in['cell'], 'environment' => tEnvironment($in),
        'logical_pair' => ['free' => 'F0', 'pro' => 'P_API'], 'artifact_identity' => $artifact,
        'requested_local_entitlement' => 'pro_active', 'compatibility' => $compat,
        'before_snapshot' => $beforeRecord['snapshot'], 'after_snapshot' => $snapshot,
        'migration_insert_order' => $order, 'pro_migrations_absent' => true,
        'premium_modules' => [], 'network_attempt_count' => 0, 'fatal_or_error' => null,
    ];
}

$mode = trim((string) getenv('WPE_P006_EXECUTION_MODE'));
try {
    $identity = tIdentity();
    $in = tInput();
    tAssert($in['cell'] === 'minimum', 'Only the minimum cell is authorized');
    tAssert($in['table_prefix'] === 'wpep006u89_', 'Unexpected disposable prefix');
    $result = match ($mode) {
        'install' => tInstall($identity, $in),
        'activate-both' => tInitialActivate($identity, $in, true),
        'fp89' => tFp89($identity, $in),
        default => tFail('Usage: <install|activate-both|fp89>'),
    };
    if ($mode === 'fp89') {
        $result['wave'] = '1U';
        $result['authorization'] = P006T_GRANT;
        $result['classification'] = 'FORMAL FP-89 MINIMUM-CELL EVIDENCE / NON-CERTIFYING';
        $result['source_sha'] = tEnv('WPE_P006_SOURCE_SHA');
        $result['formal_fixture_executed'] = true;
    }
    tWrite($in['evidence_dir'] . '/' . tEnv('WPE_P006_EVIDENCE_FILE'), $result);
    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 Wave 1U FP-89] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
