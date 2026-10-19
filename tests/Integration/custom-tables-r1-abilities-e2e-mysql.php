<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$dsn = getenv('WPE_TEST_MYSQL_DSN') ?: '';
if ($dsn === '') {
    fwrite(STDOUT, "WPEssential Custom Tables R1 abilities end-to-end integration SKIP (no DSN)\n");
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
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\CustomTables\CustomTablesModule;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Audit\AuditOutcome;
use WPEssential\Platform\Audit\AuditServices;
use WPEssential\Platform\Audit\InMemoryAuditLogger;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;
use WPEssential\Platform\WordPress\Ajax\AjaxResponse;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Ajax\WordPressAjaxEnvironmentInterface;
use WPEssential\Platform\WordPress\Ajax\WordPressAjaxGateway;
use WPEssential\Platform\WordPress\Security\NonceEnvironmentInterface;
use WPEssential\Platform\WordPress\Security\NonceManager;
use WPEssential\Platform\Auth\ExecutionChannel;
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

const CT_AB_PREFIX = 'wpetest_';
const CT_AB_DEFINITION = '79999999-9999-4999-8999-999999999999';
const CT_AB_SITE = 1;
const CT_AB_BACKUP = '0f8fad5b-d9cb-469f-a165-70867728950e';

function ctAbExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

final class CtAbPdoDatabase implements DatabaseAdapterInterface
{
    public function __construct(private readonly PDO $pdo) {}
    public function networkTablePrefix(): string { return CT_AB_PREFIX; }
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

final class CtAbPdoWpdb
{
    public string $prefix = CT_AB_PREFIX;
    public string $base_prefix = CT_AB_PREFIX;
    public string $collate = 'utf8mb4_unicode_ci';
    public string $last_error = '';

    public function __construct(private readonly PDO $pdo, private readonly CtAbPdoDatabase $database) {}
    public function prepare(string $query, mixed ...$args): string { return $this->database->prepare($query, ...$args); }
    /** @return list<array<string,mixed>> */
    public function get_results(string $query, string $output): array
    {
        $rows = $this->pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
        return array_map(static fn (array $row): array => array_change_key_case($row, CASE_LOWER), $rows);
    }
    public function db_server_info(): string { return (string) $this->pdo->query('SELECT VERSION()')->fetchColumn(); }
}

final class CtAbDefinitions implements DefinitionRepositoryInterface
{
    public function __construct(private Definition $definition) {}
    public function save(Definition $definition): void { $this->definition = $definition; }
    public function get(string $id): ?Definition { return $id === $this->definition->id ? $this->definition : null; }
    public function byType(string $type): array { return [$this->definition]; }
    public function dependentsOf(string $id): array { return []; }
}

final class CtAbEnvironment implements WordPressAbilityEnvironmentInterface
{
    public function abilitiesApiAvailable(): bool { return true; }
    public function doingAction(string $hook): bool { return false; }
    public function currentUserId(): ?int { return 7; }
    public function currentSiteId(): int { return CT_AB_SITE; }
    public function currentNetworkId(): ?int { return 1; }
    public function currentUserCan(string $capability): bool { return true; }
    public function isRestRequest(): bool { return false; }
    public function isCli(): bool { return false; }
    public function registerCategory(string $slug, array $args): bool { return true; }
    public function registerAbility(string $name, array $args): bool { return true; }
}

final class CtAbNonceEnvironment implements NonceEnvironmentInterface
{
    public function create(string $action): string { return 'nonce:' . $action; }
    public function verify(string $nonce, string $action): bool { return $nonce === 'nonce:' . $action; }
}

final class CtAbAjaxEnvironment implements WordPressAjaxEnvironmentInterface
{
    public function registerAction(string $hook, callable $callback): void {}
    public function request(): array { return []; }
    public function isAuthenticated(): bool { return true; }
    public function currentUserCan(string $capability): bool { return true; }
    public function respond(AjaxResponse $response): void {}
}

$pdo = new PDO($dsn, getenv('WPE_TEST_MYSQL_USER') ?: 'root', getenv('WPE_TEST_MYSQL_PASSWORD') ?: 'root', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
$database = new CtAbPdoDatabase($pdo);
$wpdb = new CtAbPdoWpdb($pdo, $database);
$introspector = new WordPressCt1SchemaIntrospector($wpdb);
$profiles = new WordPressProviderCapabilityProfileResolver($wpdb);
$capabilities = $profiles->resolve();

$definitions = new CtAbDefinitions(new Definition(
    id: CT_AB_DEFINITION,
    slug: 'aborders',
    type: 'table',
    schemaVersion: 1,
    ownerSurfaceId: 7,
    status: DefinitionStatus::Published,
    payload: [
        'key' => 'aborders',
        'label' => 'Ability Orders',
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
$desired = (new TableDefinitionCompiler())->compile($definitions->get(CT_AB_DEFINITION));
$identity = Ct1ManagedTableIdentity::derive(CT_AB_DEFINITION, 'aborders', CT_AB_PREFIX);

$stores = [
    CT_AB_PREFIX . 'wpe_custom_table_migration_runs',
    CT_AB_PREFIX . 'wpe_custom_table_migration_reviews',
    CT_AB_PREFIX . 'wpe_custom_table_migration_confirmations',
];
foreach (array_merge([$identity->physicalName], $stores) as $table) {
    $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
}
(new CreateMigrationRunStoreMigration($database))->apply();
(new CreateMigrationReviewSnapshotStoreMigration($database))->apply();
(new CreateMigrationExecutionConfirmationStoreMigration($database))->apply();

$backupRoot = sys_get_temp_dir() . '/wpe-ab-backups-' . bin2hex(random_bytes(6));
mkdir($backupRoot . '/' . CT_AB_BACKUP . '/database', 0777, true);

$policy = new PolicyEngine(new class implements CapabilityCheckerInterface {
    public function can(ExecutionContext $context, string $capability): bool
    {
        return $context->principal->userId === 7 && $capability === 'manage_options';
    }
});
$confirmations = new WpdbMigrationExecutionConfirmationProvider($database, CT_AB_SITE);
$factory = CustomTablesRuntimeCompositionFactory::production(
    database: $database,
    policy: $policy,
    metadataFacts: new WordPressMetadataPreconditionFactsProvider($introspector),
    recoveryVerification: new LocalBackupRecoveryVerificationProvider(new LocalBackupManifestVerifier($backupRoot, CT_AB_SITE)),
    confirmations: $confirmations,
    networkId: 1,
    siteId: CT_AB_SITE,
);

// The module wires the abilities exactly as the plugin bootstrap does.
$abilities = new AbilityRegistry($policy);
$services = new ServiceRegistry();
$services->set('platform.abilities', $abilities);
$services->set('platform.definitions', $definitions);
$services->set('custom-tables.runtime-composition.factory', $factory);
$services->set('custom-tables.execution-confirmation.issuer', new WpdbMigrationExecutionConfirmationIssuer($database, $confirmations, $policy, CT_AB_SITE));
$auditLog = new InMemoryAuditLogger();
$services->set(AuditServices::LOGGER, $auditLog);
$services->set('platform.abilities.contexts', new WordPressExecutionContextFactory(new CtAbEnvironment()));
$ajaxRoutes = new AjaxRouteRegistry();
$dispatcher = new AjaxDispatcher($ajaxRoutes, new NonceManager(new CtAbNonceEnvironment(), 'wpessential_request'), static fn (string $capability): bool => true);
$services->set('platform.ajax.routes', $ajaxRoutes);
$services->set('platform.ajax.dispatcher', $dispatcher);
$services->set('platform.ajax.gateway', new WordPressAjaxGateway('wpessential_dispatch', $dispatcher, new CtAbAjaxEnvironment()));
$services->set('platform.admin.assets', new AdminAssetManifest(sys_get_temp_dir(), 'https://example.test/wp-content/plugins/wpessential'));
(new CustomTablesModule($introspector, $profiles, CT_AB_PREFIX))->register($services);

$admin = new ExecutionContext(new Principal(7), CT_AB_SITE, networkId: 1);

// The admin page (ADR-0228-B) mints a nonce for every registered migration route and lists this table.
$adminBootstrap = $services->get('module.custom-tables.admin')->bootstrap('https://example.test/wp-admin/admin-ajax.php');
ctAbExpect(count($adminBootstrap['routes']) === 7 && count(array_unique(array_column($adminBootstrap['routes'], 'nonce'))) === 7, 'admin page must mint one nonce per migration route');
ctAbExpect(array_column($adminBootstrap['tables'], 'key') === ['aborders'], 'admin page must list the table definition');
$call = static fn (string $action, array $input, ?ExecutionContext $context = null): array =>
    $abilities->execute(CustomTablesModule::abilityName($action), $input, $context ?? $admin);

// 1. Prepare and walk the lifecycle through the registry.
$prepared = $call('prepare', ['definition_id' => CT_AB_DEFINITION]);
ctAbExpect($prepared['prepared'] === true && $prepared['preview']['schema_diff'] !== [], 'prepare must return a planned run with a reviewable schema diff');
ctAbExpect($prepared['preview']['operations'] === ['create_table'] && $prepared['preview']['affected_rows_estimate'] === 0, 'a new table affects no existing rows');
ctAbExpect(str_contains($prepared['preview']['data_compatibility'], 'Not applicable') && $prepared['preview']['rollback_strategy'] !== '', 'preview must carry the spec section 17 evidence');
$runId = $prepared['run']['run_id'];
$run = $call('submit', ['run_id' => $runId, 'expected_state_revision' => $prepared['run']['state_revision']])['run'];
$run = $call('approve', ['run_id' => $runId, 'expected_state_revision' => $run['state_revision']])['run'];
$run = $call('revalidate', ['run_id' => $runId, 'expected_state_revision' => $run['state_revision']])['run'];
ctAbExpect($run['state'] === 'revalidating', 'run must be revalidating');
$call('confirm', ['run_id' => $runId, 'expected_state_revision' => $run['state_revision']]);

// 2. A REST context is refused by the registry before any handler runs.
$refusedOnRest = false;
try {
    $call('execute', ['run_id' => $runId, 'backup_id' => CT_AB_BACKUP], new ExecutionContext(new Principal(7), CT_AB_SITE, ExecutionChannel::Rest, networkId: 1));
} catch (RuntimeException $exception) {
    $refusedOnRest = str_contains($exception->getMessage(), 'channel_not_exposed');
}
ctAbExpect($refusedOnRest, 'execute must be refused on the REST channel');

// 3. Without a backup, execution is refused before mutation.
$refused = $call('execute', ['run_id' => $runId, 'backup_id' => CT_AB_BACKUP]);
ctAbExpect($refused['applied'] === false && $refused['mutation_attempted'] === false, 'execute without a backup must not mutate');
ctAbExpect(!$introspector->observe($desired)->exists, 'no table may exist after refused executions');

// 4. With a verified, plan-bound backup the same run applies.
$planFingerprint = $factory->run($runId)?->planFingerprint ?? '';
$dump = "-- abilities e2e fixture dump\n";
file_put_contents($backupRoot . '/' . CT_AB_BACKUP . '/database/dump.sql', $dump);
file_put_contents($backupRoot . '/' . CT_AB_BACKUP . '/manifest.json', json_encode([
    'manifest_version' => 1,
    'backup_id' => CT_AB_BACKUP,
    'site_id' => CT_AB_SITE,
    'created_at' => gmdate('Y-m-d\TH:i:s\Z', time() - 3600),
    'state' => 'completed_verified',
    'scope_requested' => ['database'],
    'scope_captured' => ['database'],
    'table_prefix' => CT_AB_PREFIX,
    'database' => ['provider' => $capabilities->provider, 'version' => $capabilities->version],
    'artifacts' => [['path' => 'database/dump.sql', 'sha256' => hash('sha256', $dump), 'size' => strlen($dump)]],
    'failed_items' => [],
    'bound_plan_fingerprints' => [$planFingerprint],
], JSON_THROW_ON_ERROR));

$applied = $call('execute', ['run_id' => $runId, 'backup_id' => CT_AB_BACKUP]);
ctAbExpect($applied['applied'] === true && $applied['state'] === 'applied', 'execute with a verified backup must apply: ' . implode(',', $applied['reasons']));
$observed = $introspector->observe($desired);
ctAbExpect($observed->exists && !$observed->isBlockedByDrift(), 'managed table must exist without drift');

// 5. An additive change to an existing table reports a real row count and the column lock note.
$pdo->exec("INSERT INTO `{$identity->physicalName}` () VALUES (), ()");
$definitions->save(new Definition(
    id: CT_AB_DEFINITION,
    slug: 'aborders',
    type: 'table',
    schemaVersion: 1,
    ownerSurfaceId: 7,
    status: DefinitionStatus::Published,
    payload: [
        'key' => 'aborders',
        'label' => 'Ability Orders',
        'desired_schema_version' => 2,
        'columns' => [
            ['key' => 'id', 'type' => 'bigint', 'nullable' => false, 'auto_increment' => true],
            ['key' => 'label', 'type' => 'varchar', 'length' => 64, 'nullable' => true],
            ['key' => 'note', 'type' => 'varchar', 'length' => 32, 'nullable' => true],
        ],
        'primary_key' => ['id'],
        'indexes' => [],
        'data_classification' => 'internal',
    ],
    revision: 3,
));
$second = $call('prepare', ['definition_id' => CT_AB_DEFINITION]);
ctAbExpect($second['prepared'] === true, 'adding a nullable column must prepare: ' . implode(',', $second['reasons']));
ctAbExpect($second['preview']['operations'] === ['add_column'], 'second plan must add a column');
ctAbExpect($second['preview']['affected_rows_estimate'] === 2, 'row estimate must count the existing rows');
ctAbExpect(stripos($second['preview']['lock_notes'][0], 'column') !== false, 'lock note must describe the column change');

// 6. Every call above left an audit record; executions are recorded with their outcome.
$records = array_values($auditLog->all());
// 8 handler calls: prepare, submit, approve, revalidate, confirm, execute x2, prepare. The REST call in step 2
// is refused by the registry before any handler runs; ADR-0228 audits such refusals only on the admin-AJAX path.
ctAbExpect(count($records) === 8, 'every migration ability call that reaches a handler must be audited, got ' . count($records));
$executeOutcomes = array_map(
    static fn ($record) => $record->outcome,
    array_values(array_filter($records, static fn ($record) => $record->action === 'custom-tables.migration.execute')),
);
ctAbExpect($executeOutcomes === [AuditOutcome::Failed, AuditOutcome::Success], 'execute without then with a backup must audit failed then success');
ctAbExpect(array_reduce($records, static fn (bool $all, $record) => $all && $record->ownerSurfaceId === 7, true), 'audit records belong to the Custom Tables surface');

foreach (array_merge([$identity->physicalName], $stores) as $table) {
    $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
}
array_map('unlink', array_merge(glob($backupRoot . '/' . CT_AB_BACKUP . '/database/*') ?: [], [$backupRoot . '/' . CT_AB_BACKUP . '/manifest.json']));
rmdir($backupRoot . '/' . CT_AB_BACKUP . '/database');
rmdir($backupRoot . '/' . CT_AB_BACKUP);
rmdir($backupRoot);

fwrite(STDOUT, 'WPEssential Custom Tables R1 abilities end-to-end integration PASS (' . $capabilities->provider . ' ' . $capabilities->version . ")\n");
