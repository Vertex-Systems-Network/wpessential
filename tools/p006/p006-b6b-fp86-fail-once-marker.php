<?php

declare(strict_types=1);

const P006B6B_AUTH = 'GOV-P006-B6B-FP86-FAIL-ONCE-MARKER-001';
const P006B6B_TARGET_ID = '220.custom_tables_migration_runs_v1';
const P006B6B_TARGET_SEQUENCE = 220;
const P006B6B_FAIL_MESSAGE = 'P006_B6B_FAIL_ONCE_MARKER_WRITE';

function b6bFail(string $message): never
{
    throw new RuntimeException($message);
}

function b6bAssert(bool $condition, string $message): void
{
    if (!$condition) {
        b6bFail($message);
    }
}

function b6bEnv(string $key): string
{
    $value = trim((string) getenv($key));
    if ($value === '') {
        b6bFail('Missing env: ' . $key);
    }
    return $value;
}

/** @param array<string,mixed> $value */
function b6bWrite(string $path, array $value): void
{
    if (!is_dir(dirname($path))) {
        b6bAssert(mkdir(dirname($path), 0775, true) || is_dir(dirname($path)), 'Unable to create evidence directory');
    }
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    b6bAssert(file_put_contents($path, $json . PHP_EOL) !== false, 'Unable to write evidence');
}

function b6bEnvelope(string $uri): void
{
    $_SERVER['HTTP_HOST'] = 'p006-b6b.test';
    $_SERVER['SERVER_NAME'] = 'p006-b6b.test';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTPS'] = 'off';
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
}

function b6bTableExists(object $wpdb, string $table): bool
{
    b6bAssert(preg_match('/^[A-Za-z0-9_]+$/', $table) === 1, 'Unsafe table identifier');
    $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
    return is_string($found) && $found === $table;
}

function b6bSchemaHash(object $wpdb, string $table): ?string
{
    if (!b6bTableExists($wpdb, $table)) {
        return null;
    }
    $row = $wpdb->get_row('SHOW CREATE TABLE ' . $table, ARRAY_N);
    b6bAssert(is_array($row) && isset($row[1]) && is_string($row[1]), 'Unable to inspect target schema');
    $ddl = preg_replace('/AUTO_INCREMENT=\d+\s*/', '', $row[1]);
    b6bAssert(is_string($ddl), 'Unable to normalize target schema');
    return hash('sha256', $ddl);
}

/** @return list<string> */
function b6bWpeTables(object $wpdb, string $prefix): array
{
    $rows = $wpdb->get_col('SHOW TABLES');
    b6bAssert(is_array($rows), 'Unable to list database tables');
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

/** @return array<string,string> */
function b6bEnvironment(): array
{
    global $wp_version, $wpdb;
    b6bAssert(isset($wp_version) && is_string($wp_version), 'WordPress version unavailable');
    b6bAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable');
    $expectedWp = b6bEnv('WPE_P006_EXPECTED_WP');
    $expectedPhp = b6bEnv('WPE_P006_EXPECTED_PHP');
    $expectedMysql = b6bEnv('WPE_P006_EXPECTED_MYSQL');
    b6bAssert($wp_version === $expectedWp, 'WordPress version drift');
    b6bAssert(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION === $expectedPhp, 'PHP version drift');
    $mysql = (string) $wpdb->db_version();
    b6bAssert(str_starts_with($mysql, $expectedMysql), 'MySQL version drift');
    return ['wordpress' => $wp_version, 'php' => PHP_VERSION, 'mysql' => $mysql];
}

$sourceSha = b6bEnv('WPE_P006_SOURCE_SHA');
b6bAssert(preg_match('/^[0-9a-f]{40}$/', $sourceSha) === 1, 'Invalid source SHA');
$wpDir = rtrim(b6bEnv('WPE_TEST_WORDPRESS_DIR'), '/\\');
$prefix = b6bEnv('WPE_P006_TABLE_PREFIX');
b6bAssert($prefix === 'wpep006b6b_', 'Unexpected B6b table prefix');
$networkLog = b6bEnv('WPE_P006_NETWORK_LOG');
$evidenceFile = b6bEnv('WPE_P006_EVIDENCE_FILE');

try {
    b6bEnvelope('/wp-admin/install.php');
    if (!defined('WP_INSTALLING')) {
        define('WP_INSTALLING', true);
    }
    require $wpDir . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    b6bAssert(defined('P006_B6B_NETWORK_BLOCK_ACTIVE'), 'B6b network blocker unavailable');
    if (!is_blog_installed()) {
        $installed = wp_install(
            'WPEssential P-006 B6b',
            'p006_b6b_admin',
            'p006-b6b-admin@example.test',
            false,
            '',
            'p006-b6b-test-password-strong',
        );
        b6bAssert(!is_wp_error($installed), 'Disposable WordPress installation failed');
    }
    if (function_exists('wp_installing')) {
        wp_installing(false);
    }
    b6bAssert(is_blog_installed(), 'Disposable WordPress installation incomplete');

    $installerBlockedAttempts = [];
    if (is_file($networkLog)) {
        $installerLines = file($networkLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $installerBlockedAttempts = is_array($installerLines) ? array_values(array_map('strval', $installerLines)) : [];
    }
    b6bAssert(
        file_put_contents($networkLog, '') !== false,
        'Unable to reset network-attempt log after disposable WordPress installation',
    );

    global $wpdb;
    b6bAssert(isset($wpdb) && $wpdb instanceof wpdb, 'wpdb unavailable after install');
    b6bAssert($wpdb->base_prefix === $prefix, 'WordPress base prefix drift');

    $repoRoot = dirname(__DIR__, 2);
    foreach ([
        '/frameworks/Contracts/MigrationInterface.php',
        '/frameworks/Contracts/MigrationStateStoreInterface.php',
        '/frameworks/Platform/Database/DatabaseAdapterInterface.php',
        '/frameworks/Platform/Database/NativeWpdbAdapter.php',
        '/frameworks/Platform/Database/Migrations/MigrationRegistry.php',
        '/frameworks/Platform/Database/Migrations/MigrationRunner.php',
        '/frameworks/Platform/Database/Migrations/WpdbMigrationStateStore.php',
        '/frameworks/Modules/CustomTables/Migration/Run/Persistence/CreateMigrationRunStoreMigration.php',
    ] as $relative) {
        require_once $repoRoot . $relative;
    }

    $adapter = new \WPEssential\Platform\Database\NativeWpdbAdapter($wpdb);
    $realStore = new \WPEssential\Platform\Database\Migrations\WpdbMigrationStateStore($adapter);
    $targetTable = $prefix . 'wpe_custom_table_migration_runs';
    $markerTable = $prefix . 'wpe_migrations';

    b6bAssert(!b6bTableExists($wpdb, $targetTable), 'Target table unexpectedly exists before prerequisite');
    $beforeIds = $realStore->appliedIds();
    b6bAssert($beforeIds === [], 'Migration markers unexpectedly present before prerequisite');
    b6bAssert(b6bTableExists($wpdb, $markerTable), 'Real state store did not initialize marker table');

    $targetMigration = new \WPEssential\Modules\CustomTables\Migration\Run\Persistence\CreateMigrationRunStoreMigration($adapter);
    b6bAssert($targetMigration->id() === P006B6B_TARGET_ID, 'Target migration id drift');
    b6bAssert($targetMigration->sequence() === P006B6B_TARGET_SEQUENCE, 'Target migration sequence drift');
    b6bAssert($targetMigration->isDestructive() === false, 'B6b target migration became destructive');

    $failOnceStore = new class($realStore) implements \WPEssential\Contracts\MigrationStateStoreInterface {
        private bool $failed = false;

        public function __construct(
            private readonly \WPEssential\Contracts\MigrationStateStoreInterface $inner,
        ) {
        }

        public function appliedIds(): array
        {
            return $this->inner->appliedIds();
        }

        public function markApplied(string $id): void
        {
            if (!$this->failed && $id === P006B6B_TARGET_ID) {
                $this->failed = true;
                throw new RuntimeException(P006B6B_FAIL_MESSAGE);
            }
            $this->inner->markApplied($id);
        }
    };

    $firstRegistry = new \WPEssential\Platform\Database\Migrations\MigrationRegistry();
    $firstRegistry->register($targetMigration);
    $firstRunner = new \WPEssential\Platform\Database\Migrations\MigrationRunner($firstRegistry, $failOnceStore);

    $firstException = null;
    try {
        $firstRunner->runPending();
        b6bFail('First runner unexpectedly completed without fail-once marker exception');
    } catch (RuntimeException $exception) {
        b6bAssert($exception->getMessage() === P006B6B_FAIL_MESSAGE, 'Unexpected first-run exception');
        $firstException = ['class' => $exception::class, 'message' => $exception->getMessage()];
    }

    b6bAssert(is_array($firstException), 'First-run exception was not captured');
    b6bAssert(b6bTableExists($wpdb, $targetTable), 'Target migration apply did not complete before marker failure');
    $afterFailureIds = $realStore->appliedIds();
    b6bAssert($afterFailureIds === [], 'Marker persisted despite fail-once seam');
    $schemaAfterFailure = b6bSchemaHash($wpdb, $targetTable);
    b6bAssert(is_string($schemaAfterFailure), 'Target schema hash unavailable after first apply');

    $secondStore = new \WPEssential\Platform\Database\Migrations\WpdbMigrationStateStore($adapter);
    $secondRegistry = new \WPEssential\Platform\Database\Migrations\MigrationRegistry();
    $secondRegistry->register(new \WPEssential\Modules\CustomTables\Migration\Run\Persistence\CreateMigrationRunStoreMigration($adapter));
    $secondRunner = new \WPEssential\Platform\Database\Migrations\MigrationRunner($secondRegistry, $secondStore);
    $secondResult = $secondRunner->runPending();
    b6bAssert($secondResult === [P006B6B_TARGET_ID], 'Fresh retry did not apply exactly migration 220');

    $afterRetryIds = $secondStore->appliedIds();
    b6bAssert($afterRetryIds === [P006B6B_TARGET_ID], 'Retry marker set drift');
    $schemaAfterRetry = b6bSchemaHash($wpdb, $targetTable);
    b6bAssert($schemaAfterRetry === $schemaAfterFailure, 'Target table schema changed across idempotent retry');

    $markerCount = (int) $adapter->getVar($adapter->prepare(
        'SELECT COUNT(*) FROM ' . $markerTable . ' WHERE migration_id = %s',
        P006B6B_TARGET_ID,
    ));
    b6bAssert($markerCount === 1, 'Migration marker was not persisted exactly once');

    $thirdStore = new \WPEssential\Platform\Database\Migrations\WpdbMigrationStateStore($adapter);
    $thirdRegistry = new \WPEssential\Platform\Database\Migrations\MigrationRegistry();
    $thirdRegistry->register(new \WPEssential\Modules\CustomTables\Migration\Run\Persistence\CreateMigrationRunStoreMigration($adapter));
    $thirdRunner = new \WPEssential\Platform\Database\Migrations\MigrationRunner($thirdRegistry, $thirdStore);
    $thirdResult = $thirdRunner->runPending();
    b6bAssert($thirdResult === [], 'Third fresh runner found unexpected pending migration');
    $schemaAfterThird = b6bSchemaHash($wpdb, $targetTable);
    b6bAssert($schemaAfterThird === $schemaAfterRetry, 'Target schema changed on settled third run');
    b6bAssert($thirdStore->appliedIds() === [P006B6B_TARGET_ID], 'Settled marker set drift');

    $wpeTables = b6bWpeTables($wpdb, $prefix);
    $expectedTables = [$targetTable, $markerTable];
    sort($expectedTables, SORT_STRING);
    b6bAssert($wpeTables === $expectedTables, 'B6b created unexpected WPE-owned tables');

    $networkAttempts = [];
    if (is_file($networkLog)) {
        $lines = file($networkLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $networkAttempts = is_array($lines) ? array_values(array_map('strval', $lines)) : [];
    }
    b6bAssert($networkAttempts === [], 'Outbound WordPress HTTP attempt observed');

    $evidence = [
        'status' => 'PASS',
        'protocol' => 'P-006',
        'prerequisite' => 'B6b-FP86-fail-once-marker',
        'authorization' => P006B6B_AUTH,
        'classification' => 'TEST-ONLY HARNESS PREREQUISITE / FP-86 NOT FORMALLY EXECUTED / NON-CERTIFYING',
        'source_sha' => $sourceSha,
        'environment' => b6bEnvironment(),
        'table_prefix' => $prefix,
        'target' => [
            'migration_id' => P006B6B_TARGET_ID,
            'sequence' => P006B6B_TARGET_SEQUENCE,
            'table' => $targetTable,
            'destructive' => false,
        ],
        'before' => [
            'target_table_present' => false,
            'migration_ids' => $beforeIds,
        ],
        'first_run' => [
            'exception' => $firstException,
            'target_table_present_after_apply' => true,
            'migration_ids_after_marker_failure' => $afterFailureIds,
            'target_schema_sha256' => $schemaAfterFailure,
        ],
        'second_fresh_run' => [
            'applied_ids' => $secondResult,
            'migration_ids' => $afterRetryIds,
            'marker_count' => $markerCount,
            'target_schema_sha256' => $schemaAfterRetry,
            'schema_unchanged_from_first_apply' => true,
        ],
        'third_fresh_run' => [
            'applied_ids' => $thirdResult,
            'migration_ids' => $thirdStore->appliedIds(),
            'target_schema_sha256' => $schemaAfterThird,
            'schema_unchanged_from_retry' => true,
        ],
        'wpe_tables' => $wpeTables,
        'installer_blocked_http_attempt_count' => count($installerBlockedAttempts),
        'installer_blocked_http_attempts' => $installerBlockedAttempts,
        'prerequisite_phase_network_attempt_count' => 0,
        'network_attempt_count' => 0,
        'formal_fixture_executed' => false,
        'fixture_accounting_changed' => false,
        'generic_migration_recovery_certified' => false,
        'product_runtime_source_modified' => false,
        'runtime_grant_created' => false,
        'accounting' => ['documented' => 144, 'executed' => 57, 'pass' => 57, 'fail' => 0, 'inconclusive' => 0],
    ];

    b6bWrite($evidenceFile, $evidence);
    fwrite(STDOUT, json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 B6b FP-86 prerequisite] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
