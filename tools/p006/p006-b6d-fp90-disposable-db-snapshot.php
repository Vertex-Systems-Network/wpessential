<?php

declare(strict_types=1);

const P006B6D_AUTH = 'GOV-P006-B6D-FP90-DISPOSABLE-DB-SNAPSHOT-001';
const P006B6D_DB = 'wpessential_b6d';
const P006B6D_PREFIX = 'wpep006b6d_';
const P006B6D_SENTINEL_KEY = 'wpe_p006_b6d_sentinel';
const P006B6D_SENTINEL_VALUE = 'fp90-older-db-preserve-v1';
const P006B6D_FREE_IDS = [
    '006.create-compiled-registration-atomic-store',
    '007.create-definition-persistence',
    '008.create-audit-ptd-store',
];
const P006B6D_PRO_IDS = [
    '220.custom_tables_migration_runs_v1',
    '221.custom_tables_migration_execution_confirmations_v1',
];
const P006B6D_F0_SHA256 = '2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80';
const P006B6D_F0_TREE_SHA256 = '0698d1a772704bcf44bea1eefae0c490212d6bb6ee214a4f6a5c33db1bf998b9';
const P006B6D_P0_SHA256 = 'bd74077f22c7268765519ac2d4c64dd785ba59c9eee68f5a5f1b4d8fd8de0467';
const P006B6D_P0_TREE_SHA256 = 'fbaa10957eeb482bd248ef2833b021202068ce7f420fe2d78f35b0e522b0bfd0';
const P006B6D_PAIR_ID = '28e96f5207d3dc195bbf1b1a883ea7dcea5a314580661de5400e4e299dbd5211';

function b6dFail(string $message): never
{
    throw new RuntimeException($message);
}

function b6dAssert(bool $condition, string $message): void
{
    if (!$condition) {
        b6dFail($message);
    }
}

function b6dEnv(string $key): string
{
    $value = trim((string) getenv($key));
    if ($value === '') {
        b6dFail('Missing env: ' . $key);
    }
    return $value;
}

/** @param array<string,mixed> $value */
function b6dWriteJson(string $path, array $value): void
{
    if (!is_dir(dirname($path))) {
        b6dAssert(mkdir(dirname($path), 0775, true) || is_dir(dirname($path)), 'Unable to create evidence directory');
    }
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    b6dAssert(file_put_contents($path, $json . PHP_EOL) !== false, 'Unable to write evidence JSON');
}

/** @return array<string,mixed> */
function b6dReadJson(string $path): array
{
    b6dAssert(is_file($path), 'Evidence JSON missing: ' . $path);
    $raw = file_get_contents($path);
    b6dAssert(is_string($raw), 'Unable to read evidence JSON: ' . $path);
    $value = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    b6dAssert(is_array($value), 'Evidence JSON shape invalid: ' . $path);
    return $value;
}

function b6dEnvelope(string $uri): void
{
    $_SERVER['HTTP_HOST'] = 'p006-b6d.test';
    $_SERVER['SERVER_NAME'] = 'p006-b6d.test';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTPS'] = 'off';
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
}

/** @return list<string> */
function b6dNetworkAttempts(string $log): array
{
    if (!is_file($log)) {
        return [];
    }
    $rows = file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return is_array($rows) ? array_values(array_map('strval', $rows)) : [];
}

function b6dResetNetwork(string $log): void
{
    b6dAssert(file_put_contents($log, '') !== false, 'Unable to reset network log');
}

function b6dBoot(bool $install): void
{
    $wpDir = rtrim(b6dEnv('WPE_TEST_WORDPRESS_DIR'), '/\\');
    b6dEnvelope($install ? '/wp-admin/install.php' : '/');
    if ($install && !defined('WP_INSTALLING')) {
        define('WP_INSTALLING', true);
    }
    require $wpDir . '/wp-load.php';
    b6dAssert(defined('P006_B6D_NETWORK_BLOCK_ACTIVE'), 'B6d network blocker unavailable');
}

function b6dInstallWordPress(): int
{
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    if (!is_blog_installed()) {
        $installed = wp_install(
            'WPEssential P-006 B6d',
            'p006_b6d_admin',
            'p006-b6d-admin@example.test',
            false,
            '',
            'p006-b6d-test-password-strong',
        );
        b6dAssert(!is_wp_error($installed), 'Disposable WordPress installation failed');
    }
    if (function_exists('wp_installing')) {
        wp_installing(false);
    }
    b6dAssert(is_blog_installed(), 'Disposable WordPress installation incomplete');
    return 1;
}

function b6dRequireMigrationClasses(): void
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
    ] as $relative) {
        require_once $root . $relative;
    }
}

function b6dTableExists(object $wpdb, string $table): bool
{
    b6dAssert(preg_match('/^[A-Za-z0-9_]+$/', $table) === 1, 'Unsafe table identifier');
    $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
    return is_string($found) && $found === $table;
}

/** @return list<string> */
function b6dWpeTables(object $wpdb, string $prefix): array
{
    $rows = $wpdb->get_col('SHOW TABLES');
    b6dAssert(is_array($rows), 'Unable to list database tables');
    $tables = [];
    foreach ($rows as $row) {
        $table = (string) $row;
        if (str_starts_with($table, $prefix . 'wpe_')) {
            $tables[] = $table;
        }
    }
    sort($tables, SORT_STRING);
    return $tables;
}

function b6dNormalizedDdl(object $wpdb, string $table): string
{
    b6dAssert(b6dTableExists($wpdb, $table), 'Table missing for schema inspection: ' . $table);
    $row = $wpdb->get_row('SHOW CREATE TABLE ' . $table, ARRAY_N);
    b6dAssert(is_array($row) && isset($row[1]) && is_string($row[1]), 'Unable to inspect schema: ' . $table);
    $ddl = preg_replace('/AUTO_INCREMENT=\\d+\\s*/', '', $row[1]);
    b6dAssert(is_string($ddl), 'Unable to normalize schema: ' . $table);
    $ddl = preg_replace(
        '/\\bCHARACTER SET utf8mb4 (?=COLLATE utf8mb4_unicode_520_ci\\b)/',
        '',
        $ddl,
    );
    b6dAssert(is_string($ddl), 'Unable to normalize redundant column charset: ' . $table);
    return preg_replace('/\\s+/', ' ', trim($ddl)) ?? trim($ddl);
}

/** @return array<string,mixed> */
function b6dSnapshot(): array
{
    global $wpdb;
    b6dAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable');
    b6dAssert($wpdb->base_prefix === P006B6D_PREFIX, 'B6d table prefix drift');

    b6dRequireMigrationClasses();
    $adapter = new \WPEssential\Platform\Database\NativeWpdbAdapter($wpdb);
    $store = new \WPEssential\Platform\Database\Migrations\WpdbMigrationStateStore($adapter);
    $ids = $store->appliedIds();
    sort($ids, SORT_STRING);

    $tables = b6dWpeTables($wpdb, P006B6D_PREFIX);
    $schemas = [];
    foreach ($tables as $table) {
        $schemas[$table] = b6dNormalizedDdl($wpdb, $table);
    }
    ksort($schemas, SORT_STRING);

    $sentinel = get_option(P006B6D_SENTINEL_KEY, null);
    b6dAssert($sentinel === P006B6D_SENTINEL_VALUE, 'B6d sentinel drift');

    foreach ([
        P006B6D_PREFIX . 'wpe_custom_table_migration_runs',
        P006B6D_PREFIX . 'wpe_custom_table_migration_confirmations',
    ] as $proTable) {
        b6dAssert(!b6dTableExists($wpdb, $proTable), 'Pro migration table unexpectedly present: ' . $proTable);
    }
    foreach (P006B6D_PRO_IDS as $proId) {
        b6dAssert(!in_array($proId, $ids, true), 'Pro migration marker unexpectedly present: ' . $proId);
    }

    $schemaJson = json_encode($schemas, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $stateJson = json_encode([
        'migration_ids' => $ids,
        'schemas' => $schemas,
        'sentinel' => $sentinel,
        'table_prefix' => P006B6D_PREFIX,
    ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

    return [
        'migration_ids' => $ids,
        'wpe_tables' => $tables,
        'normalized_schema' => $schemas,
        'normalized_schema_table_sha256' => array_map(
            static fn (string $ddl): string => hash('sha256', $ddl),
            $schemas,
        ),
        'normalized_schema_sha256' => hash('sha256', $schemaJson),
        'state_sha256' => hash('sha256', $stateJson),
        'sentinel_key' => P006B6D_SENTINEL_KEY,
        'sentinel_value' => P006B6D_SENTINEL_VALUE,
        'table_prefix' => P006B6D_PREFIX,
        'pro_migration_ids_absent' => true,
        'pro_tables_absent' => true,
    ];
}

/** @return array<string,string> */
function b6dEnvironment(): array
{
    global $wp_version, $wpdb;
    b6dAssert(isset($wp_version) && is_string($wp_version), 'WordPress version unavailable');
    b6dAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable');
    $expectedWp = b6dEnv('WPE_P006_EXPECTED_WP');
    $expectedPhp = b6dEnv('WPE_P006_EXPECTED_PHP');
    $expectedMysql = b6dEnv('WPE_P006_EXPECTED_MYSQL');
    b6dAssert($wp_version === $expectedWp, 'WordPress version drift');
    b6dAssert(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION === $expectedPhp, 'PHP version drift');
    $mysql = (string) $wpdb->db_version();
    b6dAssert(str_starts_with($mysql, $expectedMysql), 'MySQL version drift');
    return ['wordpress' => $wp_version, 'php' => PHP_VERSION, 'mysql' => $mysql];
}

/** @return array<string,string> */
function b6dReadZip(string $path): array
{
    b6dAssert(class_exists(ZipArchive::class), 'ZipArchive unavailable');
    $zip = new ZipArchive();
    b6dAssert($zip->open($path) === true, 'Unable to open ZIP: ' . $path);
    $entries = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        b6dAssert(is_array($stat) && isset($stat['name']) && is_string($stat['name']), 'Unable to inspect ZIP entry');
        $name = $stat['name'];
        b6dAssert($name !== '' && !str_contains($name, '../') && !str_starts_with($name, '/'), 'Unsafe ZIP entry');
        if (str_ends_with($name, '/')) {
            continue;
        }
        $value = $zip->getFromIndex($i);
        b6dAssert(is_string($value), 'Unable to read ZIP entry: ' . $name);
        b6dAssert(!isset($entries[$name]), 'Duplicate ZIP entry: ' . $name);
        $entries[$name] = $value;
    }
    $zip->close();
    ksort($entries, SORT_STRING);
    b6dAssert($entries !== [], 'ZIP contains no files');
    return $entries;
}

/** @param array<string,string> $entries */
function b6dTreeDigest(array $entries): string
{
    ksort($entries, SORT_STRING);
    $ctx = hash_init('sha256');
    foreach ($entries as $name => $value) {
        hash_update($ctx, $name . "\0" . hash('sha256', $value) . "\n");
    }
    return hash_final($ctx);
}

function b6dRegex(string $pattern, string $value, string $label): string
{
    $matched = preg_match($pattern, $value, $m);
    b6dAssert($matched === 1 && isset($m[1]) && is_string($m[1]), 'Unable to parse ' . $label);
    return $m[1];
}

/** @return array<string,mixed> */
function b6dFreeMetadata(string $main): array
{
    return [
        'version' => b6dRegex("/define\\('WPE_VERSION',\\s*'([^']+)'\\);/", $main, 'WPE_VERSION'),
        'platform_api' => b6dRegex("/define\\('WPE_PLATFORM_API_VERSION',\\s*'([^']+)'\\);/", $main, 'WPE_PLATFORM_API_VERSION'),
        'platform_schema' => (int) b6dRegex("/define\\('WPE_PLATFORM_SCHEMA_GENERATION',\\s*([0-9]+)\\);/", $main, 'WPE_PLATFORM_SCHEMA_GENERATION'),
    ];
}

/** @return array<string,mixed> */
function b6dProMetadata(string $main): array
{
    return [
        'version' => b6dRegex("/define\\('WPE_PRO_VERSION',\\s*'([^']+)'\\);/", $main, 'WPE_PRO_VERSION'),
        'min_free_version' => b6dRegex("/define\\('WPE_PRO_MIN_FREE_VERSION',\\s*'([^']+)'\\);/", $main, 'WPE_PRO_MIN_FREE_VERSION'),
        'max_free_version' => b6dRegex("/define\\('WPE_PRO_MAX_FREE_VERSION',\\s*'([^']+)'\\);/", $main, 'WPE_PRO_MAX_FREE_VERSION'),
        'min_platform_api' => b6dRegex("/define\\('WPE_PRO_MIN_PLATFORM_API_VERSION',\\s*'([^']+)'\\);/", $main, 'WPE_PRO_MIN_PLATFORM_API_VERSION'),
        'max_platform_api' => b6dRegex("/define\\('WPE_PRO_MAX_PLATFORM_API_VERSION',\\s*'([^']+)'\\);/", $main, 'WPE_PRO_MAX_PLATFORM_API_VERSION'),
        'min_platform_schema' => (int) b6dRegex("/define\\('WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION',\\s*([0-9]+)\\);/", $main, 'WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION'),
        'max_platform_schema' => (int) b6dRegex("/define\\('WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION',\\s*([0-9]+)\\);/", $main, 'WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION'),
        'schema' => (int) b6dRegex("/define\\('WPE_PRO_SCHEMA_GENERATION',\\s*([0-9]+)\\);/", $main, 'WPE_PRO_SCHEMA_GENERATION'),
    ];
}

/** @return array<string,mixed> */
function b6dCompatibility(): array
{
    $freeZip = b6dEnv('WPE_P006_F0_ZIP_PATH');
    $proZip = b6dEnv('WPE_P006_P0_ZIP_PATH');
    b6dAssert(is_file($freeZip) && is_file($proZip), 'Canonical package ZIP missing');

    $freeHash = hash_file('sha256', $freeZip);
    $proHash = hash_file('sha256', $proZip);
    b6dAssert(is_string($freeHash) && is_string($proHash), 'Unable to hash canonical packages');
    b6dAssert($freeHash === P006B6D_F0_SHA256, 'Canonical Free ZIP drift');
    b6dAssert($proHash === P006B6D_P0_SHA256, 'Canonical Pro ZIP drift');

    $freeEntries = b6dReadZip($freeZip);
    $proEntries = b6dReadZip($proZip);
    $freeTree = b6dTreeDigest($freeEntries);
    $proTree = b6dTreeDigest($proEntries);
    b6dAssert($freeTree === P006B6D_F0_TREE_SHA256, 'Canonical Free payload tree drift');
    b6dAssert($proTree === P006B6D_P0_TREE_SHA256, 'Canonical Pro payload tree drift');

    $freeMain = 'wpessential/wpessential.php';
    $proMain = 'wpessential-pro/wpessential-pro.php';
    b6dAssert(isset($freeEntries[$freeMain], $proEntries[$proMain]), 'Canonical package main entry missing');
    $freeMeta = b6dFreeMetadata($freeEntries[$freeMain]);
    $proMeta = b6dProMetadata($proEntries[$proMain]);

    if (!defined('ABSPATH')) {
        define('ABSPATH', dirname(__DIR__, 2) . '/');
    }
    require_once dirname(__DIR__, 2) . '/frameworks/Modules/Compatibility/LocalCompatibilityPreflight.php';
    $result = \WPEssential\Modules\Compatibility\LocalCompatibilityPreflight::evaluate(
        [
            'present' => true,
            'bootstrap_complete' => true,
            'version' => $freeMeta['version'],
            'platform_api' => $freeMeta['platform_api'],
            'platform_schema' => $freeMeta['platform_schema'],
        ],
        ['package_complete' => true] + $proMeta,
    );
    b6dAssert(($result['state'] ?? null) === 'compatible', 'Canonical F0/P0 compatibility drift');
    b6dAssert(($result['premium_migrations_allowed'] ?? null) === true, 'Canonical F0/P0 denies premium migrations');
    $pairId = hash('sha256', $freeHash . ':' . $proHash);
    b6dAssert($pairId === P006B6D_PAIR_ID, 'Canonical pair id drift');

    return [
        'evaluated_before_pending_migration_execution' => true,
        'pending_pro_migrations_invoked' => false,
        'pair_id_sha256' => $pairId,
        'preflight' => $result,
        'F0' => [
            'sha256' => $freeHash,
            'payload_tree_sha256' => $freeTree,
            'entry_count' => count($freeEntries),
            'main_entry_sha256' => hash('sha256', $freeEntries[$freeMain]),
        ],
        'P0' => [
            'sha256' => $proHash,
            'payload_tree_sha256' => $proTree,
            'entry_count' => count($proEntries),
            'main_entry_sha256' => hash('sha256', $proEntries[$proMain]),
        ],
    ];
}

function b6dPrepare(): array
{
    $networkLog = b6dEnv('WPE_P006_NETWORK_LOG');
    b6dBoot(true);
    b6dInstallWordPress();
    $installerAttempts = b6dNetworkAttempts($networkLog);
    b6dResetNetwork($networkLog);

    global $wpdb;
    b6dAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable after install');
    b6dRequireMigrationClasses();
    $adapter = new \WPEssential\Platform\Database\NativeWpdbAdapter($wpdb);
    $store = new \WPEssential\Platform\Database\Migrations\WpdbMigrationStateStore($adapter);
    $registry = new \WPEssential\Platform\Database\Migrations\MigrationRegistry();
    $registry->register(new \WPEssential\Platform\WordPress\Registrations\Migrations\CreateCompiledRegistrationTablesMigration($adapter));
    $registry->register(new \WPEssential\Platform\Definitions\Migrations\CreateDefinitionTablesMigration($adapter));
    $registry->register(new \WPEssential\Platform\Audit\Migrations\CreateAuditEventsTableMigration($adapter));
    $runner = new \WPEssential\Platform\Database\Migrations\MigrationRunner($registry, $store);
    $applied = $runner->runPending();
    b6dAssert($applied === P006B6D_FREE_IDS, 'Older-state Free migration order drift');
    update_option(P006B6D_SENTINEL_KEY, P006B6D_SENTINEL_VALUE, false);

    $snapshot = b6dSnapshot();
    b6dAssert($snapshot['migration_ids'] === P006B6D_FREE_IDS, 'Older-state migration id drift');
    $phaseAttempts = b6dNetworkAttempts($networkLog);
    b6dAssert($phaseAttempts === [], 'Outbound WordPress HTTP during prerequisite state construction');

    return [
        'status' => 'PASS',
        'phase' => 'prepare-older-state',
        'authorization' => P006B6D_AUTH,
        'source_sha' => b6dEnv('WPE_P006_SOURCE_SHA'),
        'environment' => b6dEnvironment(),
        'database' => P006B6D_DB,
        'snapshot' => $snapshot,
        'applied_ids' => $applied,
        'installer_blocked_http_attempt_count' => count($installerAttempts),
        'prerequisite_phase_network_attempt_count' => 0,
        'formal_fixture_executed' => false,
    ];
}

function b6dVerifyRestored(): array
{
    $networkLog = b6dEnv('WPE_P006_NETWORK_LOG');
    b6dResetNetwork($networkLog);
    b6dBoot(false);
    $before = b6dReadJson(b6dEnv('WPE_P006_BEFORE_EVIDENCE'));
    $snapshot = b6dSnapshot();
    b6dAssert(isset($before['snapshot']) && is_array($before['snapshot']), 'Before snapshot missing');
    $expectedSnapshot = $before['snapshot'];
    if ($snapshot !== $expectedSnapshot) {
        $diff = [];
        foreach (array_values(array_unique(array_merge(array_keys($expectedSnapshot), array_keys($snapshot)))) as $key) {
            $expectedValue = $expectedSnapshot[$key] ?? '__missing__';
            $actualValue = $snapshot[$key] ?? '__missing__';
            if ($expectedValue !== $actualValue) {
                $diff[$key] = ['before' => $expectedValue, 'after' => $actualValue];
            }
        }
        b6dFail(
            'Restored older-state snapshot drift: '
            . json_encode($diff, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        );
    }
    b6dAssert(b6dNetworkAttempts($networkLog) === [], 'Outbound WordPress HTTP during restored-state verification');

    return [
        'status' => 'PASS',
        'phase' => 'verify-restored-state',
        'authorization' => P006B6D_AUTH,
        'source_sha' => b6dEnv('WPE_P006_SOURCE_SHA'),
        'environment' => b6dEnvironment(),
        'database' => P006B6D_DB,
        'snapshot' => $snapshot,
        'matches_pre_export_state' => true,
        'pending_pro_migrations_invoked' => false,
        'prerequisite_phase_network_attempt_count' => 0,
        'formal_fixture_executed' => false,
    ];
}

function b6dAggregate(): array
{
    $before = b6dReadJson(b6dEnv('WPE_P006_BEFORE_EVIDENCE'));
    $restored = b6dReadJson(b6dEnv('WPE_P006_RESTORED_EVIDENCE'));
    $compatibility = b6dReadJson(b6dEnv('WPE_P006_COMPATIBILITY_EVIDENCE'));
    $seedSnapshot = b6dEnv('WPE_P006_SQL_SEED_SNAPSHOT');
    $snapshotA = b6dEnv('WPE_P006_SQL_SNAPSHOT');
    $snapshotB = b6dEnv('WPE_P006_SQL_SNAPSHOT_RESTORED');
    b6dAssert(
        is_file($seedSnapshot) && is_file($snapshotA) && is_file($snapshotB),
        'SQL snapshot artifact missing',
    );
    $seedHash = hash_file('sha256', $seedSnapshot);
    $hashA = hash_file('sha256', $snapshotA);
    $hashB = hash_file('sha256', $snapshotB);
    b6dAssert(
        is_string($seedHash) && is_string($hashA) && is_string($hashB),
        'Unable to hash SQL snapshots',
    );
    b6dAssert(hash_equals($hashA, $hashB), 'Accepted SQL snapshot fixed point is not byte-identical');
    b6dAssert(filesize($snapshotA) > 0 && filesize($snapshotA) === filesize($snapshotB), 'SQL snapshot size drift');
    b6dAssert(($before['snapshot']['state_sha256'] ?? null) === ($restored['snapshot']['state_sha256'] ?? null), 'State hash drift after restore');
    b6dAssert(($compatibility['preflight']['state'] ?? null) === 'compatible', 'Compatibility preflight did not pass');
    b6dAssert(($compatibility['pending_pro_migrations_invoked'] ?? null) === false, 'Pending Pro migration was invoked');

    return [
        'status' => 'PASS',
        'protocol' => 'P-006',
        'prerequisite' => 'B6d-FP90-disposable-db-snapshot',
        'authorization' => P006B6D_AUTH,
        'classification' => 'TEST-ONLY DISPOSABLE SQL SNAPSHOT/RESTORE PREREQUISITE / FP-90 NOT FORMALLY EXECUTED / NON-CERTIFYING',
        'source_sha' => b6dEnv('WPE_P006_SOURCE_SHA'),
        'older_state' => $before['snapshot'],
        'restored_state' => $restored['snapshot'],
        'state_identity_restored' => true,
        'sql_snapshot' => [
            'seed_sha256' => $seedHash,
            'seed_bytes' => filesize($seedSnapshot),
            'accepted_sha256' => $hashA,
            'accepted_bytes' => filesize($snapshotA),
            'fixed_point_export_sha256' => $hashB,
            'second_export_byte_identical' => true,
            'mysql_replay_canonicalization_used' => true,
            'seed_and_accepted_byte_identical' => hash_equals($seedHash, $hashA),
        ],
        'compatibility_before_pending_migrations' => $compatibility,
        'ordering' => [
            'older_state_settled',
            'seed_sql_export',
            'seed_drop_recreate_import',
            'semantic_state_identity_verified',
            'canonical_pair_compatibility_pass',
            'accepted_sql_export',
            'accepted_drop_recreate_import',
            'semantic_state_identity_reverified',
            'fixed_point_sql_export_byte_identical',
            'stop_before_pro_migrations',
        ],
        'pending_pro_migrations_invoked' => false,
        'product_backup_restore_implemented_or_certified' => false,
        'production_or_live_data_used' => false,
        'product_runtime_source_modified' => false,
        'destructive_or_irreversible_wpe_migration_executed' => false,
        'provider_or_remote_runtime_used' => false,
        'runtime_grant_created' => false,
        'formal_fixture_executed' => false,
        'fixture_accounting_changed' => false,
        'accounting' => ['documented' => 144, 'executed' => 57, 'pass' => 57, 'fail' => 0, 'inconclusive' => 0],
    ];
}

$mode = trim((string) getenv('WPE_P006_EXECUTION_MODE'));
if ($mode === '') {
    $mode = $argv[1] ?? '';
}

try {
    $result = match ($mode) {
        'prepare' => b6dPrepare(),
        'verify-restored' => b6dVerifyRestored(),
        'compatibility' => [
            'status' => 'PASS',
            'phase' => 'compatibility-before-pending-migrations',
            'authorization' => P006B6D_AUTH,
            'source_sha' => b6dEnv('WPE_P006_SOURCE_SHA'),
        ] + b6dCompatibility() + [
            'formal_fixture_executed' => false,
            'fixture_accounting_changed' => false,
        ],
        'aggregate' => b6dAggregate(),
        default => b6dFail('Usage: <prepare|verify-restored|compatibility|aggregate>'),
    };

    $output = match ($mode) {
        'prepare' => b6dEnv('WPE_P006_BEFORE_EVIDENCE'),
        'verify-restored' => b6dEnv('WPE_P006_RESTORED_EVIDENCE'),
        'compatibility' => b6dEnv('WPE_P006_COMPATIBILITY_EVIDENCE'),
        'aggregate' => b6dEnv('WPE_P006_EVIDENCE_FILE'),
        default => b6dFail('Invalid execution mode'),
    };
    b6dWriteJson($output, $result);
    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 B6d FP-90 prerequisite] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
