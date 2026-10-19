<?php

declare(strict_types=1);

/*
 * P-006 Wave 1U formal minimum-cell evidence for FP-86 / FP-89 / FP-90 / FP-94.
 *
 * Composes the accepted B6a/B6b/B6c/B6d prerequisite tooling. This tool never
 * modifies product runtime source, never contacts a provider, never executes a
 * destructive migration and only touches disposable WordPress/MySQL state.
 */

const P006U_GRANT = 'GOV-P001-CF-TEMP-019';
const P006U_PARENT_ISSUE = 1102;
const P006U_ISSUE = 1425;
const P006U_FIXTURES = ['FP-86', 'FP-89', 'FP-90', 'FP-94'];
const P006U_CELL = ['wordpress' => '6.9', 'php' => '8.2', 'mysql' => '8.4'];
const P006U_FP89_PREFIX = 'wpep006u89_';
const P006U_B6B_PREFIX = 'wpep006b6b_';
const P006U_B6D_PREFIX = 'wpep006b6d_';
const P006U_SENTINEL_KEY = 'wpe_p006_wave1u_sentinel';
const P006U_SENTINEL_VALUE = 'wave1u-preserve-v1';
const P006U_B6D_SENTINEL_KEY = 'wpe_p006_b6d_sentinel';
const P006U_B6D_SENTINEL_VALUE = 'fp90-older-db-preserve-v1';

// Accepted canonical identities (docs/QUALITY/P006-B6A-*, P006-B6D-*).
const P006U_F0_SHA256 = '2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80';
const P006U_F0_TREE_SHA256 = '0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9';
const P006U_P0_SHA256 = 'bd74077f22c7268765519ac2d4c64dd785ba59c9eee68f5a5f1b4d8fd8de0467';
const P006U_P0_TREE_SHA256 = 'fbaa10957eeb482bd248ef2833b021202068ce7f420fe2d78f35b0e522b0bfd0';
const P006U_PAPI_SHA256 = '5a2634a0d3c72ff7ede89545837c3fe2773aefad90a3fed4b060485c55645af9';
const P006U_PAPI_TREE_SHA256 = '8985a20a68c7aafeee2d758845b26bddfe34ba83b5141ea335fad174acd52a69';
const P006U_PAIR_F0_P0 = '28e96f5207d3dc195bbf1b1a883ea7dcea5a314580661de5400e4e299dbd5211';
const P006U_PAIR_F0_PAPI = '9447c78174b7702879f318339c60942ebc20be9a477df78f9dbd2b734705d841';
const P006U_B6D_OLDER_STATE_SHA256 = '24178a1289fb5929eda850f488d932c94bbbddc5936a82cc26cb83979823b058';
const P006U_B6D_ACCEPTED_RUN_ID = '35542007281';

const P006U_FREE_MIGRATIONS = [
    '006.create-compiled-registration-atomic-store',
    '007.create-definition-persistence',
    '008.create-audit-ptd-store',
];
const P006U_PRO_MIGRATIONS = [
    '220.custom_tables_migration_runs_v1',
    '221.custom_tables_migration_execution_confirmations_v1',
];
const P006U_PRO_TABLES = ['wpe_custom_table_migration_runs', 'wpe_custom_table_migration_confirmations'];
const P006U_PRO_MODULES = [
    'roles', 'admin-menu', 'settings', 'dashboard', 'profiles', 'membership',
    'builder-widgets', 'forms-workflows', 'cron', 'notifications', 'emails', 'chat',
];
const P006U_CANARIES = [
    'license_like' => 'P006_W1U_LICENSE_TOKEN_DO_NOT_EMIT',
    'vault_plaintext' => 'P006_W1U_VAULT_PLAINTEXT_DO_NOT_EMIT',
    'private_user_data' => 'P006_W1U_PRIVATE_USER_DATA_DO_NOT_EMIT',
];
const P006U_B6C_CANARIES = [
    'P006_B6C_LICENSE_TOKEN_DO_NOT_EMIT',
    'P006_B6C_VAULT_PLAINTEXT_DO_NOT_EMIT',
    'P006_B6C_PRIVATE_USER_DATA_DO_NOT_EMIT',
];
const P006U_ACCOUNTING_BEFORE = ['documented' => 144, 'executed' => 57, 'pass' => 57, 'fail' => 0, 'inconclusive' => 0];
const P006U_ACCOUNTING_IF_PASS = ['documented' => 144, 'executed' => 61, 'pass' => 61, 'fail' => 0, 'inconclusive' => 0];

/**
 * Fixture subset for aggregate / FP-94 scope. WPE_P006_AGGREGATE_FIXTURES lists
 * fixtures (space/comma separated); default is all four. Only authorized fixtures
 * are accepted, order is canonical, and duplicates are rejected.
 *
 * @return list<string>
 */
function uAggregateFixtures(): array
{
    $raw = trim((string) getenv('WPE_P006_AGGREGATE_FIXTURES'));
    if ($raw === '') {
        return P006U_FIXTURES;
    }
    $list = preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    uAssert(count($list) === count(array_unique($list)), 'aggregate fixture list has duplicates');
    foreach ($list as $fixture) {
        uAssert(in_array($fixture, P006U_FIXTURES, true), 'aggregate fixture not authorized: ' . $fixture);
    }
    return array_values(array_filter(P006U_FIXTURES, static fn (string $f): bool => in_array($f, $list, true)));
}

function uFail(string $message): never
{
    throw new RuntimeException($message);
}

function uAssert(bool $ok, string $message): void
{
    if (!$ok) {
        uFail($message);
    }
}

function uEnv(string $key): string
{
    $value = trim((string) getenv($key));
    if ($value === '') {
        uFail('Missing env: ' . $key);
    }
    return $value;
}

function uHashFile(string $path): string
{
    uAssert(is_file($path) && filesize($path) > 0, 'Missing artifact: ' . $path);
    $hash = hash_file('sha256', $path);
    uAssert(is_string($hash), 'Unable to hash artifact: ' . $path);
    return $hash;
}

/** @param array<string,mixed> $value */
function uJson(array $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
}

/** @param array<string,mixed> $value */
function uWrite(string $path, array $value): void
{
    if (!is_dir(dirname($path))) {
        uAssert(mkdir(dirname($path), 0775, true) || is_dir(dirname($path)), 'Unable to create evidence directory');
    }
    $json = uJson($value);
    uAssertNoCanaries('evidence ' . basename($path), $json);
    uAssert(file_put_contents($path, $json . PHP_EOL) !== false, 'Unable to write evidence: ' . $path);
}

/** @return array<string,mixed> */
function uRead(string $path): array
{
    uAssert(is_file($path), 'Missing evidence: ' . $path);
    $value = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    uAssert(is_array($value), 'Invalid evidence: ' . $path);
    return $value;
}

function uPair(string $freeHash, string $proHash): string
{
    return hash('sha256', $freeHash . ':' . $proHash);
}

/** @return list<string> */
function uAllCanaries(): array
{
    return array_merge(array_values(P006U_CANARIES), P006U_B6C_CANARIES);
}

function uAssertNoCanaries(string $label, string $surface): void
{
    foreach (uAllCanaries() as $canary) {
        uAssert(!str_contains($surface, $canary), $label . ' contains a raw canary');
    }
}

/** @return array<string,mixed> */
function uIdentity(): array
{
    $identity = uRead(uEnv('WPE_P006_IDENTITY_PATH'));
    uAssert(($identity['temporary_approval_id'] ?? null) === P006U_GRANT, 'Temporary approval mismatch');
    uAssert(($identity['source_sha'] ?? null) === uEnv('WPE_P006_SOURCE_SHA'), 'Source SHA mismatch');
    $nodes = $identity['nodes'] ?? null;
    uAssert(is_array($nodes), 'Candidate nodes missing');
    $expected = [
        'F0' => [P006U_F0_SHA256, P006U_F0_TREE_SHA256],
        'P0' => [P006U_P0_SHA256, P006U_P0_TREE_SHA256],
        'P_API' => [P006U_PAPI_SHA256, P006U_PAPI_TREE_SHA256],
    ];
    $dir = rtrim(uEnv('WPE_P006_CANDIDATE_DIR'), '/\\');
    foreach ($expected as $node => [$sha, $tree]) {
        uAssert(isset($nodes[$node]) && is_array($nodes[$node]), 'Node missing: ' . $node);
        uAssert(($nodes[$node]['sha256'] ?? null) === $sha, 'Accepted ZIP identity drift: ' . $node);
        uAssert(($nodes[$node]['payload_tree_sha256'] ?? null) === $tree, 'Accepted payload tree drift: ' . $node);
        $file = $dir . '/' . (string) $nodes[$node]['artifact'];
        uAssert(hash_equals($sha, uHashFile($file)), 'Downloaded ZIP hash drift: ' . $node);
    }
    uAssert(($identity['pairs']['F0_P0']['pair_id_sha256'] ?? null) === P006U_PAIR_F0_P0, 'Canonical pair id drift');
    uAssert(($identity['pairs']['F0_P_API']['pair_id_sha256'] ?? null) === P006U_PAIR_F0_PAPI, 'P-API pair id drift');
    uAssert(uPair(P006U_F0_SHA256, P006U_P0_SHA256) === P006U_PAIR_F0_P0, 'Canonical pair derivation drift');
    uAssert(uPair(P006U_F0_SHA256, P006U_PAPI_SHA256) === P006U_PAIR_F0_PAPI, 'P-API pair derivation drift');
    return $identity;
}

/** @return array<string,mixed> */
function uInput(): array
{
    $wp = uEnv('WPE_P006_EXPECTED_WP');
    $php = uEnv('WPE_P006_EXPECTED_PHP');
    $mysql = uEnv('WPE_P006_EXPECTED_MYSQL');
    uAssert(['wordpress' => $wp, 'php' => $php, 'mysql' => $mysql] === P006U_CELL, 'Runtime cell outside TEMP-019 minimum cell');
    return [
        'cell' => 'minimum',
        'wp' => $wp,
        'php' => $php,
        'mysql' => $mysql,
        'wp_dir' => rtrim(uEnv('WPE_TEST_WORDPRESS_DIR'), '/\\'),
        'evidence_dir' => rtrim(uEnv('WPE_P006_EVIDENCE_DIR'), '/\\'),
        'network_log' => uEnv('WPE_P006_NETWORK_LOG'),
        'sql_log' => uEnv('WPE_P006_SQL_LOG'),
        'table_prefix' => uEnv('WPE_P006_TABLE_PREFIX'),
    ];
}

/** @param array<string,mixed> $in */
function uResetLogs(array $in): void
{
    uAssert(file_put_contents($in['network_log'], '') !== false, 'Unable to reset network log');
    uAssert(file_put_contents($in['sql_log'], '') !== false, 'Unable to reset SQL log');
}

/** @return list<string> */
function uLines(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return is_array($lines) ? array_values(array_map('strval', $lines)) : [];
}

/** @param list<string> $queries @return list<string> */
function uMigrationInsertOrder(array $queries): array
{
    $ids = [];
    foreach ($queries as $query) {
        if (preg_match("/INSERT\\s+IGNORE\\s+INTO\\s+`[^`]*wpe_migrations`.*?VALUES\\s*\\(\\s*'([^']+)'/i", $query, $m) === 1) {
            $ids[] = (string) $m[1];
        }
    }
    return $ids;
}

/** @param list<string> $queries @return list<string> */
function uWpeDdl(array $queries): array
{
    $ddl = [];
    foreach ($queries as $query) {
        if (stripos($query, 'wpe_') !== false && preg_match('/^\\s*(?:CREATE|ALTER|DROP|RENAME|TRUNCATE)\\s+TABLE\\b/i', $query) === 1) {
            $ddl[] = $query;
        }
    }
    return $ddl;
}

/** @param list<string> $ddl */
function uAssertNonDestructive(array $ddl, string $label): void
{
    foreach ($ddl as $query) {
        uAssert(preg_match('/^\\s*CREATE\\s+TABLE\\s+IF\\s+NOT\\s+EXISTS\\b/i', $query) === 1, $label . ' executed non-additive WPE DDL');
    }
}

function uEnvelope(string $uri): void
{
    $_SERVER['HTTP_HOST'] = 'p006-wave1u.test';
    $_SERVER['SERVER_NAME'] = 'p006-wave1u.test';
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
function uBoot(array $in, string $probeConstant): void
{
    uEnvelope('/');
    require $in['wp_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    uAssert(defined($probeConstant), 'Network probe not active: ' . $probeConstant);
}

/** @param array<string,mixed> $in @return array<string,string> */
function uEnvironment(array $in): array
{
    global $wp_version, $wpdb;
    uAssert(isset($wp_version) && is_string($wp_version), 'WordPress version unavailable');
    uAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable');
    uAssert($wp_version === $in['wp'], 'WordPress version drift');
    uAssert(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION === $in['php'], 'PHP version drift');
    $db = (string) $wpdb->db_version();
    uAssert(str_starts_with($db, $in['mysql']), 'MySQL version drift');
    uAssert($wpdb->prefix === $in['table_prefix'], 'WordPress table prefix drift');
    return ['wordpress' => $wp_version, 'php' => PHP_VERSION, 'mysql' => $db, 'table_prefix' => $wpdb->prefix];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,string> */
function uTransport(array $identity, array $in, string $free, string $pro): array
{
    $freePath = realpath($in['wp_dir'] . '/wp-content/plugins/wpessential');
    $proPath = realpath($in['wp_dir'] . '/wp-content/plugins/wpessential-pro');
    uAssert(is_string($freePath) && is_string($proPath), 'Transport path unavailable');
    uAssert(str_contains($freePath, '/p006-wave1u-packages/' . strtolower($free) . '/wpessential'), 'Free transport drift');
    uAssert(str_contains($proPath, '/p006-wave1u-packages/' . strtolower(str_replace('_', '-', $pro)) . '/wpessential-pro'), 'Pro transport drift');
    $tree = static function (string $root, string $slug): string {
        $files = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $item) {
            uAssert(!$item->isLink(), 'Nested symlink in candidate payload');
            if (!$item->isFile()) {
                continue;
            }
            $rel = str_replace('\\', '/', substr($item->getPathname(), strlen($root) + 1));
            $files[$slug . '/' . $rel] = (string) hash_file('sha256', $item->getPathname());
        }
        ksort($files, SORT_STRING);
        $ctx = hash_init('sha256');
        foreach ($files as $name => $sha) {
            hash_update($ctx, $name . "\0" . $sha . "\n");
        }
        return hash_final($ctx);
    };
    $freeTree = $tree($freePath, 'wpessential');
    $proTree = $tree($proPath, 'wpessential-pro');
    uAssert($freeTree === $identity['nodes'][$free]['payload_tree_sha256'], 'Free payload tree drift');
    uAssert($proTree === $identity['nodes'][$pro]['payload_tree_sha256'], 'Pro payload tree drift');
    $pairId = uPair((string) $identity['nodes'][$free]['sha256'], (string) $identity['nodes'][$pro]['sha256']);
    uAssert($pairId === $identity['pairs'][$free . '_' . $pro]['pair_id_sha256'], 'Pair id drift');
    return [
        'free_sha256' => (string) $identity['nodes'][$free]['sha256'],
        'pro_sha256' => (string) $identity['nodes'][$pro]['sha256'],
        'free_payload_tree_sha256' => $freeTree,
        'pro_payload_tree_sha256' => $proTree,
        'pair_id_sha256' => $pairId,
    ];
}

/** @return array<string,mixed> */
function uSnapshot(string $sentinelKey): array
{
    global $wpdb;
    uAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable for snapshot');
    $prefix = $wpdb->prefix;
    uAssert(preg_match('/^[A-Za-z0-9_]+$/', $prefix) === 1, 'Unsafe prefix in snapshot');
    $all = $wpdb->get_col('SHOW TABLES');
    uAssert(is_array($all), 'Unable to list tables');
    $tables = [];
    foreach ($all as $table) {
        $table = (string) $table;
        if (str_starts_with($table, $prefix . 'wpe_')) {
            $tables[] = $table;
        }
    }
    sort($tables, SORT_STRING);
    $schema = [];
    foreach ($tables as $table) {
        $row = $wpdb->get_row('SHOW CREATE TABLE `' . $table . '`', ARRAY_N);
        uAssert(is_array($row) && isset($row[1]) && is_string($row[1]), 'Unable to inspect schema: ' . $table);
        $ddl = preg_replace('/AUTO_INCREMENT=\\d+\\s*/', '', $row[1]);
        uAssert(is_string($ddl), 'Unable to normalize schema: ' . $table);
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
    $presence = [];
    foreach (P006U_PRO_TABLES as $table) {
        $presence[$prefix . $table] = in_array($prefix . $table, $tables, true);
    }
    $data = [
        'migration_rows' => $rows,
        'migration_ids' => array_values(array_map(static fn (array $row): string => $row['migration_id'], $rows)),
        'wpe_tables' => $tables,
        'wpe_table_schema_sha256' => $schema,
        'pro_custom_table_store_presence' => $presence,
        'sentinel' => get_option($sentinelKey, '__missing__'),
    ];
    $data['snapshot_sha256'] = hash('sha256', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    return $data;
}

/** @param array<string,mixed> $snapshot */
function uAssertProTables(array $snapshot, bool $expected, string $label): void
{
    foreach (($snapshot['pro_custom_table_store_presence'] ?? []) as $table => $present) {
        uAssert($present === $expected, $label . ': Pro table presence drift for ' . $table);
    }
}

/** @return array<string,mixed>|null */
function uCompatibility(): ?array
{
    $value = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    return is_array($value) ? $value : null;
}

/** @return list<string> */
function uPremiumModules(): array
{
    if (!class_exists(\WPEssential\Bootstrap\Plugin::class)) {
        return [];
    }
    $kernel = \WPEssential\Bootstrap\Plugin::kernel();
    if (!$kernel instanceof \WPEssential\Kernel\Kernel || !$kernel->isBooted()) {
        return [];
    }
    $registry = $kernel->modules();
    $ids = [];
    foreach (P006U_PRO_MODULES as $id) {
        if ($registry->has($id)) {
            $ids[] = $id;
        }
    }
    sort($ids, SORT_STRING);
    return $ids;
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function uFp89Install(array $identity, array $in): array
{
    uAssert($in['table_prefix'] === P006U_FP89_PREFIX, 'FP-89 table prefix drift');
    $artifact = uTransport($identity, $in, 'F0', 'P_API');
    uResetLogs($in);
    uEnvelope('/wp-admin/install.php');
    if (!defined('WP_INSTALLING')) {
        define('WP_INSTALLING', true);
    }
    require $in['wp_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    uAssert(defined('WPE_P006U_PROBE_ACTIVE'), 'Wave 1U probe not active during install');
    if (!is_blog_installed()) {
        $installed = wp_install('WPEssential P-006 Wave 1U', 'p006u_admin', 'p006u-admin@example.test', false, '', 'p006u-test-password-strong');
        uAssert(!is_wp_error($installed), 'Disposable WordPress installation failed');
    }
    if (function_exists('wp_installing')) {
        wp_installing(false);
    }
    uAssert(is_blog_installed(), 'Disposable WordPress installation did not complete');
    uAssert(uLines($in['network_log']) === [], 'Outbound HTTP observed during install');

    // FP-94 canaries are seeded into disposable state before the formal scenarios run.
    update_option('p006u_canary_license_token', P006U_CANARIES['license_like'], false);
    update_option('p006u_canary_vault_plaintext', P006U_CANARIES['vault_plaintext'], false);
    $user = get_user_by('login', 'p006u_admin');
    uAssert($user instanceof WP_User, 'Disposable admin user unavailable');
    update_user_meta($user->ID, 'p006u_canary_private', P006U_CANARIES['private_user_data']);

    return [
        'status' => 'PASS', 'phase' => 'install', 'cell_id' => $in['cell'],
        'logical_pair' => ['free' => 'F0', 'pro' => 'P_API'], 'artifact_identity' => $artifact,
        'table_prefix' => $in['table_prefix'], 'canaries_seeded' => array_keys(P006U_CANARIES),
        'canary_fingerprints_sha256' => array_map(static fn (string $c): string => hash('sha256', $c), P006U_CANARIES),
        'network_attempt_count' => 0,
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function uFp89Activate(array $identity, array $in): array
{
    $artifact = uTransport($identity, $in, 'F0', 'P_API');
    uResetLogs($in);
    uBoot($in, 'WPE_P006U_PROBE_ACTIVE');
    uAssert(!is_plugin_active('wpessential/wpessential.php'), 'Free unexpectedly active before activation');
    uAssert(!is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro unexpectedly active before activation');
    update_option(P006U_SENTINEL_KEY, P006U_SENTINEL_VALUE, false);
    $free = activate_plugin('wpessential/wpessential.php', '', false, true);
    uAssert(!is_wp_error($free) && is_plugin_active('wpessential/wpessential.php'), 'Free activation failed');
    $pro = activate_plugin('wpessential-pro/wpessential-pro.php', '', false, true);
    uAssert(!is_wp_error($pro) && is_plugin_active('wpessential-pro/wpessential-pro.php'), 'P-API activation API call failed');
    $snapshot = uSnapshot(P006U_SENTINEL_KEY);
    uAssert($snapshot['migration_ids'] === [], 'Activation API call wrote migrations');
    uAssert(uMigrationInsertOrder(uLines($in['sql_log'])) === [], 'Migration marker written during activation');
    uAssert(uWpeDdl(uLines($in['sql_log'])) === [], 'WPE DDL during activation');
    uAssert(uLines($in['network_log']) === [], 'Outbound HTTP during activation');
    return [
        'status' => 'PASS', 'phase' => 'activate-both', 'cell_id' => $in['cell'], 'environment' => uEnvironment($in),
        'logical_pair' => ['free' => 'F0', 'pro' => 'P_API'], 'artifact_identity' => $artifact,
        'snapshot' => $snapshot, 'network_attempt_count' => 0,
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function uFp89(array $identity, array $in): array
{
    $beforeRecord = uRead($in['evidence_dir'] . '/fp89-before.json');
    uAssert(($beforeRecord['snapshot']['migration_ids'] ?? null) === [], 'FP-89 pre-state already contains migrations');
    $artifact = uTransport($identity, $in, 'F0', 'P_API');
    uResetLogs($in);
    uBoot($in, 'WPE_P006U_PROBE_ACTIVE');
    $snapshot = uSnapshot(P006U_SENTINEL_KEY);
    uAssert($snapshot['migration_ids'] === P006U_FREE_MIGRATIONS, 'FP-89 migration id set drift');
    uAssert($snapshot['sentinel'] === P006U_SENTINEL_VALUE, 'FP-89 sentinel drift');
    uAssertProTables($snapshot, false, 'FP-89');
    $order = uMigrationInsertOrder(uLines($in['sql_log']));
    uAssert($order === P006U_FREE_MIGRATIONS, 'FP-89 observed Pro migration or order drift');
    foreach ($order as $id) {
        uAssert(!in_array($id, P006U_PRO_MIGRATIONS, true), 'FP-89 Pro migration executed');
    }
    $compat = uCompatibility();
    uAssert(is_array($compat), 'FP-89 compatibility result unavailable');
    uAssert(($compat['state'] ?? null) === 'platform_api_too_old', 'FP-89 incompatibility state drift');
    uAssert(($compat['dimension'] ?? null) === 'platform_api', 'FP-89 incompatibility dimension drift');
    uAssert(($compat['premium_boot_allowed'] ?? null) === false, 'FP-89 premium boot allowed');
    uAssert(($compat['premium_migrations_allowed'] ?? null) === false, 'FP-89 premium migrations allowed');
    uAssert(uPremiumModules() === [], 'FP-89 premium modules registered despite rejection');
    uAssert(uLines($in['network_log']) === [], 'FP-89 outbound HTTP observed');
    return [
        'status' => 'PASS', 'fixture_id' => 'FP-89', 'cell_id' => $in['cell'], 'environment' => uEnvironment($in),
        'logical_pair' => ['free' => 'F0', 'pro' => 'P_API'], 'artifact_identity' => $artifact,
        'compatibility' => $compat, 'rejected_before_premium_boot' => true, 'rejected_before_premium_migrations' => true,
        'before_snapshot' => $beforeRecord['snapshot'], 'after_snapshot' => $snapshot,
        'migration_insert_order' => $order, 'pro_migrations_absent' => true, 'premium_modules' => [],
        'network_attempt_count' => 0, 'fatal_or_error' => null,
    ];
}

/** @param array<string,mixed> $in @return array<string,mixed> */
function uFp86(array $in): array
{
    $b6b = uRead(uEnv('WPE_P006_B6B_EVIDENCE'));
    uAssert(($b6b['status'] ?? null) === 'PASS', 'FP-86 composed B6b harness did not PASS');
    uAssert(($b6b['source_sha'] ?? null) === uEnv('WPE_P006_SOURCE_SHA'), 'FP-86 B6b source drift');
    uAssert(($b6b['table_prefix'] ?? null) === P006U_B6B_PREFIX, 'FP-86 B6b prefix drift');
    $env = $b6b['environment'] ?? [];
    uAssert(($env['wordpress'] ?? null) === $in['wp'], 'FP-86 WordPress drift');
    uAssert(str_starts_with((string) ($env['php'] ?? ''), $in['php'] . '.'), 'FP-86 PHP drift');
    uAssert(str_starts_with((string) ($env['mysql'] ?? ''), $in['mysql']), 'FP-86 MySQL drift');
    uAssert(($b6b['target']['migration_id'] ?? null) === P006U_PRO_MIGRATIONS[0], 'FP-86 target drift');
    uAssert(($b6b['target']['destructive'] ?? null) === false, 'FP-86 target destructive');
    uAssert(($b6b['first_run']['exception']['message'] ?? null) === 'P006_B6B_FAIL_ONCE_MARKER_WRITE', 'FP-86 fail-once marker not observed');
    uAssert(($b6b['first_run']['migration_ids_after_marker_failure'] ?? null) === [], 'FP-86 marker persisted after failure');
    uAssert(($b6b['second_fresh_run']['applied_ids'] ?? null) === [P006U_PRO_MIGRATIONS[0]], 'FP-86 retry did not re-apply 220');
    uAssert(($b6b['second_fresh_run']['marker_count'] ?? null) === 1, 'FP-86 marker not exactly once');
    uAssert(($b6b['second_fresh_run']['schema_unchanged_from_first_apply'] ?? null) === true, 'FP-86 schema changed on retry');
    uAssert(($b6b['third_fresh_run']['applied_ids'] ?? null) === [], 'FP-86 settled run not idempotent');
    uAssert(($b6b['network_attempt_count'] ?? null) === 0, 'FP-86 network attempt observed');
    return [
        'status' => 'PASS', 'fixture_id' => 'FP-86', 'cell_id' => $in['cell'], 'environment' => $env,
        'composed_prerequisite' => 'B6b-FP86-fail-once-marker', 'migration_id' => P006U_PRO_MIGRATIONS[0],
        'fail_once_marker_exception' => 'P006_B6B_FAIL_ONCE_MARKER_WRITE',
        'apply_completed_before_marker_failure' => true, 'marker_absent_after_failure' => true,
        'retry_applied_ids' => [P006U_PRO_MIGRATIONS[0]], 'marker_count_after_retry' => 1,
        'idempotent_schema_sha256' => $b6b['second_fresh_run']['target_schema_sha256'] ?? null,
        'settled_rerun_applied_ids' => [], 'destructive_migration_executed' => false,
        'network_attempt_count' => 0, 'fatal_or_error' => null,
    ];
}

function uRequireMigrationClasses(): void
{
    $root = dirname(__DIR__, 2);
    foreach ([
        '/frameworks/Contracts/MigrationInterface.php',
        '/frameworks/Contracts/MigrationStateStoreInterface.php',
        '/frameworks/Platform/Database/DatabaseAdapterInterface.php',
        '/frameworks/Platform/Database/NativeWpdbAdapter.php',
        '/frameworks/Platform/Database/Migrations/MigrationRegistry.php',
        '/frameworks/Platform/Database/Migrations/MigrationRunner.php',
        '/frameworks/Platform/Database/Migrations/WpdbMigrationStateStore.php',
        '/frameworks/Platform/WordPress/Registrations/CompiledRegistrationTableNames.php',
        '/frameworks/Platform/WordPress/Registrations/Migrations/CreateCompiledRegistrationTablesMigration.php',
        '/frameworks/Platform/Definitions/DefinitionTableNames.php',
        '/frameworks/Platform/Definitions/Migrations/CreateDefinitionTablesMigration.php',
        '/frameworks/Platform/Audit/AuditTableNames.php',
        '/frameworks/Platform/Audit/Migrations/CreateAuditEventsTableMigration.php',
        '/frameworks/Modules/CustomTables/Migration/Run/Persistence/CreateMigrationRunStoreMigration.php',
        '/frameworks/Modules/CustomTables/Migration/Readiness/Composition/Persistence/CreateMigrationExecutionConfirmationStoreMigration.php',
    ] as $relative) {
        require_once $root . $relative;
    }
}

/** @return object */
function uRunner(object $adapter): object
{
    $store = new \WPEssential\Platform\Database\Migrations\WpdbMigrationStateStore($adapter);
    $registry = new \WPEssential\Platform\Database\Migrations\MigrationRegistry();
    $registry->register(new \WPEssential\Platform\WordPress\Registrations\Migrations\CreateCompiledRegistrationTablesMigration($adapter));
    $registry->register(new \WPEssential\Platform\Definitions\Migrations\CreateDefinitionTablesMigration($adapter));
    $registry->register(new \WPEssential\Platform\Audit\Migrations\CreateAuditEventsTableMigration($adapter));
    $registry->register(new \WPEssential\Modules\CustomTables\Migration\Run\Persistence\CreateMigrationRunStoreMigration($adapter));
    $registry->register(new \WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\CreateMigrationExecutionConfirmationStoreMigration($adapter));
    return new \WPEssential\Platform\Database\Migrations\MigrationRunner($registry, $store);
}

/** @param array<string,mixed> $in @return array<string,mixed> */
function uFp90(array $in): array
{
    uAssert($in['table_prefix'] === P006U_B6D_PREFIX, 'FP-90 table prefix drift');
    $snapshotFile = uEnv('WPE_P006_B6D_ACCEPTED_SNAPSHOT');
    uAssert(uHashFile($snapshotFile) === P006U_B6D_OLDER_STATE_SHA256, 'FP-90 accepted older-state snapshot drift');
    $restored = uRead(uEnv('WPE_P006_B6D_RESTORED_EVIDENCE'));
    uAssert(($restored['status'] ?? null) === 'PASS' && ($restored['matches_pre_export_state'] ?? null) === true, 'FP-90 restored-state identity not proved');
    uAssert(($restored['source_sha'] ?? null) === uEnv('WPE_P006_SOURCE_SHA'), 'FP-90 restore source drift');
    $compat = uRead(uEnv('WPE_P006_B6D_COMPATIBILITY_EVIDENCE'));
    uAssert(($compat['status'] ?? null) === 'PASS', 'FP-90 compatibility step did not PASS');
    uAssert(($compat['source_sha'] ?? null) === uEnv('WPE_P006_SOURCE_SHA'), 'FP-90 compatibility source drift');
    uAssert(($compat['preflight']['state'] ?? null) === 'compatible', 'FP-90 compatibility state drift');
    uAssert(($compat['preflight']['premium_migrations_allowed'] ?? null) === true, 'FP-90 premium migrations denied');
    uAssert(($compat['pair_id_sha256'] ?? null) === P006U_PAIR_F0_P0, 'FP-90 pair id drift');
    uAssert(($compat['evaluated_before_pending_migration_execution'] ?? null) === true, 'FP-90 compatibility not before migrations');
    uAssert(($compat['pending_pro_migrations_invoked'] ?? null) === false, 'FP-90 migrations invoked before compatibility');

    uResetLogs($in);
    uBoot($in, 'P006_B6D_NETWORK_BLOCK_ACTIVE');
    $environment = uEnvironment($in);
    $sqlLog = $in['sql_log'];
    add_filter('query', static function ($query) use ($sqlLog) {
        if (is_string($query) && stripos($query, 'wpe_') !== false) {
            $normalized = preg_replace('/\s+/', ' ', trim($query));
            file_put_contents($sqlLog, (string) $normalized . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
        return $query;
    }, PHP_INT_MIN, 1);

    global $wpdb;
    $before = uSnapshot(P006U_B6D_SENTINEL_KEY);
    uAssert($before['migration_ids'] === P006U_FREE_MIGRATIONS, 'FP-90 older state is not the accepted Free-only state');
    uAssert($before['sentinel'] === P006U_B6D_SENTINEL_VALUE, 'FP-90 older-state sentinel drift');
    uAssertProTables($before, false, 'FP-90 before');

    uRequireMigrationClasses();
    $adapter = new \WPEssential\Platform\Database\NativeWpdbAdapter($wpdb);
    $applied = uRunner($adapter)->runPending();
    uAssert($applied === P006U_PRO_MIGRATIONS, 'FP-90 current migration set/order drift');
    $settled = uRunner($adapter)->runPending();
    uAssert($settled === [], 'FP-90 settled rerun not idempotent');

    $after = uSnapshot(P006U_B6D_SENTINEL_KEY);
    uAssert($after['migration_ids'] === array_merge(P006U_FREE_MIGRATIONS, P006U_PRO_MIGRATIONS), 'FP-90 post-migration id drift');
    uAssert($after['sentinel'] === P006U_B6D_SENTINEL_VALUE, 'FP-90 sentinel not preserved');
    uAssertProTables($after, true, 'FP-90 after');
    foreach ($before['wpe_table_schema_sha256'] as $table => $sha) {
        uAssert(($after['wpe_table_schema_sha256'][$table] ?? null) === $sha, 'FP-90 pre-existing table schema mutated: ' . $table);
    }
    uAssert(array_slice($after['migration_rows'], 0, count($before['migration_rows'])) === $before['migration_rows'], 'FP-90 pre-existing migration rows mutated');
    $queries = uLines($sqlLog);
    $ddl = uWpeDdl($queries);
    uAssertNonDestructive($ddl, 'FP-90');
    uAssert(uMigrationInsertOrder($queries) === P006U_PRO_MIGRATIONS, 'FP-90 marker insert order drift');
    uAssert(uLines($in['network_log']) === [], 'FP-90 outbound HTTP observed');
    return [
        'status' => 'PASS', 'fixture_id' => 'FP-90', 'cell_id' => $in['cell'], 'environment' => $environment,
        'composed_prerequisite' => 'B6d-FP90-disposable-db-snapshot',
        'accepted_snapshot' => ['sha256' => P006U_B6D_OLDER_STATE_SHA256, 'source_run_id' => P006U_B6D_ACCEPTED_RUN_ID],
        'restored_state_sha256' => $restored['snapshot']['state_sha256'] ?? null,
        'compatibility_before_migrations' => $compat['preflight'], 'pair_id_sha256' => P006U_PAIR_F0_P0,
        'before_snapshot' => $before, 'after_snapshot' => $after, 'applied_ids' => $applied,
        'settled_rerun_applied_ids' => [], 'wpe_ddl' => $ddl, 'non_destructive' => true,
        'pre_existing_schema_preserved' => true, 'pre_existing_migration_rows_preserved' => true,
        'production_or_live_data_used' => false, 'network_attempt_count' => 0, 'fatal_or_error' => null,
    ];
}

/** @param array<string,mixed> $in @return array<string,mixed> */
function uFp94(array $in): array
{
    $b6c = uRead(uEnv('WPE_P006_B6C_EVIDENCE'));
    uAssert(($b6c['status'] ?? null) === 'PASS', 'FP-94 composed B6c capture did not PASS');
    uAssert(($b6c['source_sha'] ?? null) === uEnv('WPE_P006_SOURCE_SHA'), 'FP-94 B6c source drift');
    uAssert(($b6c['raw_canaries_absent_from_all_captured_surfaces'] ?? null) === true, 'FP-94 B6c canary leak');
    $surfaces = [];
    foreach (preg_split('/[\s:]+/', uEnv('WPE_P006_CAPTURE_PATHS')) ?: [] as $root) {
        if ($root === '' || !file_exists($root)) {
            continue;
        }
        $files = is_dir($root)
            ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS))
            : [new SplFileInfo($root)];
        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $content = (string) file_get_contents($file->getPathname());
            uAssertNoCanaries('captured surface ' . $file->getFilename(), $content);
            $surfaces[$file->getPathname()] = ['bytes' => strlen($content), 'sha256' => hash('sha256', $content), 'no_raw_canaries' => true];
        }
    }
    ksort($surfaces, SORT_STRING);
    uAssert(count($surfaces) >= 8, 'FP-94 captured too few surfaces');
    $formal = array_values(array_diff(uAggregateFixtures(), ['FP-94']));
    uAssert($formal !== [], 'FP-94 has no formal scenarios to cover');
    foreach ($formal as $fixture) {
        $file = $in['evidence_dir'] . '/' . strtolower(str_replace('-', '', $fixture)) . '.json';
        uAssert(isset($surfaces[$file]), 'FP-94 did not scan formal evidence for ' . $fixture);
    }
    return [
        'status' => 'PASS', 'fixture_id' => 'FP-94', 'cell_id' => $in['cell'],
        'composed_prerequisite' => 'B6c-FP94-diagnostics-redaction',
        'canary_fingerprints_sha256' => array_map(static fn (string $c): string => hash('sha256', $c), P006U_CANARIES),
        'b6c_canary_fingerprints_sha256' => $b6c['canary_fingerprints_sha256'] ?? null,
        'formal_scenarios_covered' => $formal,
        'captured_surfaces' => $surfaces, 'raw_canaries_absent_from_all_captured_surfaces' => true,
        'network_attempt_count' => 0, 'fatal_or_error' => null,
    ];
}

/** @param array<string,mixed> $in @return array<string,mixed> */
function uAggregate(array $in): array
{
    $selected = uAggregateFixtures();
    $fixtures = [];
    foreach ($selected as $fixture) {
        $record = uRead($in['evidence_dir'] . '/' . strtolower(str_replace('-', '', $fixture)) . '.json');
        uAssert(($record['status'] ?? null) === 'PASS', $fixture . ' is not PASS');
        uAssert(($record['fixture_id'] ?? null) === $fixture, $fixture . ' fixture id drift');
        uAssert(($record['network_attempt_count'] ?? null) === 0, $fixture . ' network attempt observed');
        uAssert(array_key_exists('fatal_or_error', $record) && $record['fatal_or_error'] === null, $fixture . ' fatal/error observed');
        $fixtures[$fixture] = ['status' => 'PASS'];
    }
    return [
        'status' => 'PASS', 'protocol' => 'P-006', 'wave' => '1U', 'issue' => P006U_ISSUE, 'parent_issue' => P006U_PARENT_ISSUE,
        'classification' => 'FORMAL FP-86/89/90/94 MINIMUM-CELL EVIDENCE / NON-CERTIFYING',
        'authorization' => P006U_GRANT, 'temporary_approval_consumed_once' => true,
        'source_sha' => uEnv('WPE_P006_SOURCE_SHA'), 'cell_id' => $in['cell'], 'cell' => P006U_CELL,
        'fixture_results' => $fixtures, 'formal_fp_fixtures_executed' => $selected,
        'fixtures_not_in_this_aggregate' => array_values(array_diff(P006U_FIXTURES, $selected)),
        'accounting_before' => P006U_ACCOUNTING_BEFORE,
        'accounting_if_terminally_accepted' => [
            'documented' => 144,
            'executed' => P006U_ACCOUNTING_BEFORE['executed'] + count($selected),
            'pass' => P006U_ACCOUNTING_BEFORE['pass'] + count($selected),
            'fail' => 0, 'inconclusive' => 0,
        ],
        'accounting_if_all_four_accepted' => P006U_ACCOUNTING_IF_PASS,
        'product_runtime_source_modified' => false, 'product_di_modified' => false, 'destructive_migration_executed' => false,
        'provider_or_remote_entitlement_used' => false, 'production_or_live_data_used' => false,
        'reference_cell_executed' => false, 'pair_certified' => false, 'runtime_certified' => false,
        'migration_certified' => false, 'permanent_p001_cf_certified' => false,
        'updater_or_tuf_certified' => false, 'adr_0010' => 'Proposed', 'ga_or_release_authorized' => false,
    ];
}

$mode = trim((string) getenv('WPE_P006_EXECUTION_MODE'));
if ($mode === '') {
    $mode = $argv[1] ?? '';
}

try {
    $identity = uIdentity();
    $in = uInput();
    $result = match ($mode) {
        'fp89-install' => uFp89Install($identity, $in),
        'fp89-activate' => uFp89Activate($identity, $in),
        'fp89' => uFp89($identity, $in),
        'fp86' => uFp86($in),
        'fp90' => uFp90($in),
        'fp94' => uFp94($in),
        'aggregate' => uAggregate($in),
        default => uFail('Usage: <fp89-install|fp89-activate|fp89|fp86|fp90|fp94|aggregate>'),
    };
    $file = $mode === 'aggregate' ? 'summary.json' : uEnv('WPE_P006_EVIDENCE_FILE');
    uWrite($in['evidence_dir'] . '/' . $file, $result);
    fwrite(STDOUT, uJson($result) . PHP_EOL);
} catch (Throwable $e) {
    $message = $e->getMessage();
    foreach (uAllCanaries() as $canary) {
        $message = str_replace($canary, '[redacted-canary]', $message);
    }
    fwrite(STDERR, '[P-006 Wave 1U minimum cell] ' . $message . PHP_EOL);
    exit(1);
}
