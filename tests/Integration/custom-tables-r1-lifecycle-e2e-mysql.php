<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$dsn = getenv('WPE_TEST_MYSQL_DSN') ?: '';
if ($dsn === '') {
    fwrite(STDOUT, "WPEssential Custom Tables R1 lifecycle end-to-end integration SKIP (no DSN)\n");
    exit(0);
}

$root = dirname(__DIR__, 2);
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) return;
    $path = $root . '/frameworks/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) require $path;
});

use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\Backup\Verification\LocalBackupManifestVerifier;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\WordPressMetadataPreconditionFactsProvider;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\CustomTablesRuntimeCompositionFactory;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\CreateMigrationExecutionConfirmationStoreMigration;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationIssuer;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\LocalBackupRecoveryVerificationProvider;
use WPEssential\Modules\CustomTables\Migration\Review\Persistence\CreateMigrationReviewSnapshotStoreMigration;
use WPEssential\Modules\CustomTables\Migration\Review\WordPressProviderCapabilityProfileResolver;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\CreateMigrationRunStoreMigration;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\MigrationRunRecordCodec;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\WpdbMigrationRunRepository;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

const CT_E2E_PREFIX = 'wpetest_';
const CT_E2E_DEFINITION = '78888888-8888-4888-8888-888888888888';
const CT_E2E_SITE = 1;
const CT_E2E_BACKUP = '0f8fad5b-d9cb-469f-a165-70867728950e';

function ctE2eExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

final class CtE2ePdoDatabase implements DatabaseAdapterInterface
{
    public function __construct(private readonly PDO $pdo) {}
    public function networkTablePrefix(): string { return CT_E2E_PREFIX; }
    public function charsetCollate(): string { return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'; }
    public function prepare(string $query, mixed ...$args): string
    {
        $index = 0;
        $result = preg_replace_callback('/%[ds]/', function (array $match) use (&$index, $args): string {
            $value = $args[$index++] ?? throw new RuntimeException('Missing prepare argument.');
            return $match[0] === '%d' ? (string) (int) $value : $this->pdo->quote((string) $value);
        }, $query);
        if (!is_string($result) || $index !== count($args)) throw new RuntimeException('Prepare argument mismatch.');
        return $result;
    }
    public function getRow(string $query): ?array
    {
        $row = $this->pdo->query($query)->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }
    public function getResults(string $query): array { return $this->pdo->query($query)->fetchAll(PDO::FETCH_ASSOC); }
    public function getVar(string $query): mixed { return $this->pdo->query($query)->fetchColumn(); }
    public function query(string $query): int|bool { return $this->pdo->exec($query); }
    public function insert(string $table, array $data, array $formats = []): bool
    {
        $columns = implode(', ', array_map(static fn (string $c): string => "`{$c}`", array_keys($data)));
        $marks = implode(', ', array_fill(0, count($data), '?'));
        return $this->pdo->prepare("INSERT INTO `{$table}` ({$columns}) VALUES ({$marks})")->execute(array_values($data));
    }
    public function lastError(): string { return ''; }
    public function beginTransaction(): void {}
    public function commit(): void {}
    public function rollBack(): void {}
}

final class CtE2ePdoWpdb
{
    public string $prefix = CT_E2E_PREFIX;
    public string $base_prefix = CT_E2E_PREFIX;
    public string $collate = 'utf8mb4_unicode_ci';
    public string $last_error = '';

    public function __construct(private readonly PDO $pdo, private readonly CtE2ePdoDatabase $database) {}
    public function prepare(string $query, mixed ...$args): string { return $this->database->prepare($query, ...$args); }
    /** @return list<array<string,mixed>> */
    public function get_results(string $query, string $output): array
    {
        $rows = $this->pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
        return array_map(static fn (array $row): array => array_change_key_case($row, CASE_LOWER), $rows);
    }
    public function db_server_info(): string { return (string) $this->pdo->query('SELECT VERSION()')->fetchColumn(); }
}

final class CtE2eDefinitions implements DefinitionRepositoryInterface
{
    public function __construct(private Definition $definition) {}
    public function save(Definition $definition): void { $this->definition = $definition; }
    public function get(string $id): ?Definition { return $id === $this->definition->id ? $this->definition : null; }
    public function byType(string $type): array { return [$this->definition]; }
    public function dependentsOf(string $id): array { return []; }
}

$pdo = new PDO($dsn, getenv('WPE_TEST_MYSQL_USER') ?: 'root', getenv('WPE_TEST_MYSQL_PASSWORD') ?: 'root', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
$database = new CtE2ePdoDatabase($pdo);
$wpdb = new CtE2ePdoWpdb($pdo, $database);
$introspector = new WordPressCt1SchemaIntrospector($wpdb);
$profiles = new WordPressProviderCapabilityProfileResolver($wpdb);
$capabilities = $profiles->resolve();

$definitions = new CtE2eDefinitions(new Definition(
    id: CT_E2E_DEFINITION,
    slug: 'e2eorders',
    type: 'table',
    schemaVersion: 1,
    ownerSurfaceId: 7,
    status: DefinitionStatus::Published,
    payload: [
        'key' => 'e2eorders',
        'label' => 'End To End Orders',
        'desired_schema_version' => 1,
        'columns' => [
            ['key' => 'id', 'type' => 'bigint', 'nullable' => false, 'auto_increment' => true],
            ['key' => 'label', 'type' => 'varchar', 'length' => 64, 'nullable' => true],
        ],
        'primary_key' => ['id'],
        'indexes' => [],
        'data_classification' => 'internal',
    ],
    revision: 2,
));
$desired = (new TableDefinitionCompiler())->compile($definitions->get(CT_E2E_DEFINITION));
$identity = Ct1ManagedTableIdentity::derive(CT_E2E_DEFINITION, 'e2eorders', CT_E2E_PREFIX);

$stores = [
    CT_E2E_PREFIX . 'wpe_custom_table_migration_runs',
    CT_E2E_PREFIX . 'wpe_custom_table_migration_reviews',
    CT_E2E_PREFIX . 'wpe_custom_table_migration_confirmations',
];
foreach (array_merge([$identity->physicalName], $stores) as $table) {
    $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
}
(new CreateMigrationRunStoreMigration($database))->apply();
(new CreateMigrationReviewSnapshotStoreMigration($database))->apply();
(new CreateMigrationExecutionConfirmationStoreMigration($database))->apply();

$backupRoot = sys_get_temp_dir() . '/wpe-e2e-backups-' . bin2hex(random_bytes(6));
mkdir($backupRoot . '/' . CT_E2E_BACKUP . '/database', 0777, true);

$policy = new PolicyEngine(new class implements CapabilityCheckerInterface {
    public function can(ExecutionContext $context, string $capability): bool
    {
        return $context->principal->userId === 7 && $capability === 'wpessential_apply_table_migrations';
    }
});
$confirmations = new WpdbMigrationExecutionConfirmationProvider($database, CT_E2E_SITE);
$factory = CustomTablesRuntimeCompositionFactory::production(
    database: $database,
    policy: $policy,
    metadataFacts: new WordPressMetadataPreconditionFactsProvider($introspector),
    recoveryVerification: new LocalBackupRecoveryVerificationProvider(new LocalBackupManifestVerifier($backupRoot, CT_E2E_SITE)),
    confirmations: $confirmations,
    networkId: 1,
    siteId: CT_E2E_SITE,
);
$admin = new ExecutionContext(new Principal(7), CT_E2E_SITE, networkId: 1);
$runs = new WpdbMigrationRunRepository($database, new MigrationRunRecordCodec(), CT_E2E_SITE);

// 1. Prepare: a planned run and a stored review, no DDL.
$prepared = $factory->createR1MigrationRunPreparationService($introspector)->prepare($desired, $identity, $capabilities);
ctE2eExpect($prepared->prepared, 'preparation must succeed: ' . implode(',', $prepared->reasons));
$runId = $prepared->run->id;

// 2. Lifecycle: submit, approve and revalidate through the authorized service.
$lifecycle = $factory->createR1MigrationRunLifecycleService();
$run = $lifecycle->submit($runId, $prepared->run->stateRevision);
$run = $lifecycle->approve($admin, $runId, $run->stateRevision);
$run = $lifecycle->startRevalidation($admin, $runId, $run->stateRevision);
ctE2eExpect($runs->get($runId)?->state === MigrationRunState::Revalidating, 'run must be revalidating after approval');

(new WpdbMigrationExecutionConfirmationIssuer($database, $confirmations, $policy, CT_E2E_SITE))
    ->issue($admin, $runId, $run->planFingerprint, $run->stateRevision);

$resolver = $factory->createR1ExecutionRequestResolver($introspector, $profiles, $definitions, CT_E2E_PREFIX);
$coordinator = $factory->createR1AdditiveExecutionCoordinator($introspector);

// 3. Without a verified, plan-bound backup the production composition refuses before any mutation.
$resolution = $resolver->resolve($runId, $admin, CT_E2E_BACKUP);
ctE2eExpect($resolution->request !== null, 'request must resolve: ' . implode(',', $resolution->reasons));
$refused = $coordinator->execute($resolution->request);
ctE2eExpect(!$refused->applied && !$refused->mutationAttempted, 'execution without a backup must be refused before mutation');
ctE2eExpect(!$introspector->observe($desired)->exists, 'refused execution must not create the table');
ctE2eExpect($runs->get($runId)?->state === MigrationRunState::Revalidating, 'refused execution must leave the run revalidating');

// 4. With a verified database backup bound to this plan the same run executes and verifies.
$dump = "-- e2e fixture dump\n";
file_put_contents($backupRoot . '/' . CT_E2E_BACKUP . '/database/dump.sql', $dump);
file_put_contents($backupRoot . '/' . CT_E2E_BACKUP . '/manifest.json', json_encode([
    'manifest_version' => 1,
    'backup_id' => CT_E2E_BACKUP,
    'site_id' => CT_E2E_SITE,
    'created_at' => gmdate('Y-m-d\TH:i:s\Z', time() - 3600),
    'state' => 'completed_verified',
    'scope_requested' => ['database'],
    'scope_captured' => ['database'],
    'table_prefix' => CT_E2E_PREFIX,
    'database' => ['provider' => $capabilities->provider, 'version' => $capabilities->version],
    'artifacts' => [['path' => 'database/dump.sql', 'sha256' => hash('sha256', $dump), 'size' => strlen($dump)]],
    'failed_items' => [],
    'bound_plan_fingerprints' => [$run->planFingerprint],
], JSON_THROW_ON_ERROR));

$resolution = $resolver->resolve($runId, $admin, CT_E2E_BACKUP);
ctE2eExpect($resolution->request !== null, 'request must resolve with a backup: ' . implode(',', $resolution->reasons));
$applied = $coordinator->execute($resolution->request);
ctE2eExpect($applied->applied, 'execution with a verified backup must apply: ' . implode(',', $applied->reasons));
ctE2eExpect($runs->get($runId)?->state === MigrationRunState::Applied, 'run must be applied');
$observed = $introspector->observe($desired);
ctE2eExpect($observed->exists && !$observed->isBlockedByDrift(), 'managed table must exist without drift');

// 5. Once applied, preparing again finds nothing to do.
$second = $factory->createR1MigrationRunPreparationService($introspector)->prepare($desired, $identity, $capabilities);
ctE2eExpect(!$second->prepared && $second->reasons === ['plan_empty'], 'an applied schema leaves nothing to prepare');

foreach (array_merge([$identity->physicalName], $stores) as $table) {
    $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
}
array_map('unlink', array_merge(glob($backupRoot . '/' . CT_E2E_BACKUP . '/database/*') ?: [], [$backupRoot . '/' . CT_E2E_BACKUP . '/manifest.json']));
rmdir($backupRoot . '/' . CT_E2E_BACKUP . '/database');
rmdir($backupRoot . '/' . CT_E2E_BACKUP);
rmdir($backupRoot);

fwrite(STDOUT, 'WPEssential Custom Tables R1 lifecycle end-to-end integration PASS (' . $capabilities->provider . ' ' . $capabilities->version . ")\n");
