<?php

declare(strict_types=1);


if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$dsn = getenv('WPE_TEST_MYSQL_DSN') ?: '';
if ($dsn === '') {
    fwrite(STDOUT, "WPEssential platform persistence MySQL integration SKIP (no DSN)\n");
    exit(0);
}

$root = dirname(__DIR__, 2);
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) return;
    $relative = substr($class, strlen($prefix));
    $path = $root . '/frameworks/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) require $path;
});

use WPEssential\Platform\Audit\AuditOutcome;
use WPEssential\Platform\Audit\AuditRecord;
use WPEssential\Platform\Audit\AuditRowCodec;
use WPEssential\Platform\Audit\AuditTableNames;
use WPEssential\Platform\Audit\Migrations\CreateAuditEventsTableMigration;
use WPEssential\Platform\Audit\PersistentAuditLogger;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Database\Migrations\MigrationRegistry;
use WPEssential\Platform\Database\Migrations\MigrationRunner;
use WPEssential\Platform\Database\Migrations\WpdbMigrationStateStore;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionRowCodec;
use WPEssential\Platform\Definitions\DefinitionScope;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\DefinitionTableNames;
use WPEssential\Platform\Definitions\Migrations\CreateDefinitionTablesMigration;
use WPEssential\Platform\Definitions\PersistentDefinitionRepository;
use WPEssential\Platform\Definitions\WpdbDefinitionTableGateway;

function platformPersistenceExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

final class PlatformPdoDatabaseAdapter implements DatabaseAdapterInterface
{
    public function __construct(private readonly PDO $pdo) {}
    public function networkTablePrefix(): string { return 'wpetest_'; }
    public function charsetCollate(): string { return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'; }
    public function prepare(string $query, mixed ...$args): string
    {
        $index = 0;
        $result = preg_replace_callback('/%[ds]/', function (array $match) use (&$index, $args): string {
            if (!array_key_exists($index, $args)) throw new RuntimeException('Missing SQL prepare argument.');
            $value = $args[$index++];
            return $match[0] === '%d' ? (string) (int) $value : $this->pdo->quote((string) $value);
        }, $query);
        if (!is_string($result) || $index !== count($args)) throw new RuntimeException('SQL prepare argument mismatch.');
        return $result;
    }
    public function getRow(string $query): ?array
    {
        $statement = $this->pdo->query($query);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }
    public function getResults(string $query): array
    {
        $statement = $this->pdo->query($query);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getVar(string $query): mixed
    {
        $statement = $this->pdo->query($query);
        $value = $statement->fetchColumn();
        return $value === false ? null : $value;
    }
    public function query(string $query): int|bool { return $this->pdo->exec($query); }
    public function insert(string $table, array $data, array $formats = []): bool
    {
        if (preg_match('/^[A-Za-z0-9_]+$/', $table) !== 1) throw new RuntimeException('Unsafe test table name.');
        foreach (array_keys($data) as $column) {
            if (preg_match('/^[A-Za-z0-9_]+$/', (string) $column) !== 1) throw new RuntimeException('Unsafe test column name.');
        }
        $columns = array_keys($data);
        $sql = sprintf('INSERT INTO `%s` (`%s`) VALUES (%s)', $table, implode('`,`', $columns), implode(',', array_fill(0, count($columns), '?')));
        return $this->pdo->prepare($sql)->execute(array_values($data));
    }
    public function lastError(): string { return ''; }
    public function beginTransaction(): void { if (!$this->pdo->beginTransaction()) throw new RuntimeException('Unable to begin transaction.'); }
    public function commit(): void { if (!$this->pdo->commit()) throw new RuntimeException('Unable to commit transaction.'); }
    public function rollBack(): void { if ($this->pdo->inTransaction() && !$this->pdo->rollBack()) throw new RuntimeException('Unable to roll back transaction.'); }
}

$pdo = new PDO(
    $dsn,
    getenv('WPE_TEST_MYSQL_USER') ?: 'root',
    getenv('WPE_TEST_MYSQL_PASSWORD') ?: 'root',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false],
);
$database = new PlatformPdoDatabaseAdapter($pdo);
$definitionTables = new DefinitionTableNames($database);
$auditTables = new AuditTableNames($database);
$migrationTable = $database->networkTablePrefix() . 'wpe_migrations';

foreach ([$auditTables->events, $definitionTables->dependencies, $definitionTables->definitions, $migrationTable] as $table) {
    $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
}

$registry = new MigrationRegistry();
$registry->register(new CreateDefinitionTablesMigration($database));
$registry->register(new CreateAuditEventsTableMigration($database));
$state = new WpdbMigrationStateStore($database);
$runner = new MigrationRunner($registry, $state);
$firstApplied = $runner->runPending();
platformPersistenceExpect($firstApplied === ['007.create-definition-persistence', '008.create-audit-ptd-store'], 'persistent migration runner must apply Definition then Audit migrations in sequence');
platformPersistenceExpect($runner->runPending() === [], 'persistent migration ledger must make a second migration run idempotent');
platformPersistenceExpect((new WpdbMigrationStateStore($database))->appliedIds() === $firstApplied, 'migration state must survive a new state-store instance');

$baseId = '11111111-1111-4111-8111-111111111111';
$childId = '22222222-2222-4222-8222-222222222222';
$scope = DefinitionScope::site(7, 701);
$gateway = new WpdbDefinitionTableGateway($database, $scope);
$repository = new PersistentDefinitionRepository($gateway);

$base = new Definition(
    id: $baseId,
    slug: 'base-settings',
    type: 'settings-page',
    schemaVersion: 1,
    ownerSurfaceId: 1,
    status: DefinitionStatus::Published,
    payload: ['menu_slug' => 'base-settings'],
);
$child = new Definition(
    id: $childId,
    slug: 'child-settings',
    type: 'settings-page',
    schemaVersion: 1,
    ownerSurfaceId: 1,
    status: DefinitionStatus::Published,
    payload: ['menu_slug' => 'child-settings'],
    dependencies: [$baseId],
);
$repository->save($base);
$repository->save($child);

platformPersistenceExpect($repository->get($childId)?->dependencies === [$baseId], 'Definition dependencies must round-trip from MySQL');
platformPersistenceExpect(count($repository->byType('settings-page')) === 2, 'Definition type query must return both scoped rows');
platformPersistenceExpect($repository->dependentsOf($baseId)[0]->id === $childId, 'Definition reverse dependency query must resolve the child');
platformPersistenceExpect((new PersistentDefinitionRepository(new WpdbDefinitionTableGateway($database, DefinitionScope::site(7, 702))))->get($baseId) === null, 'Definition rows must be site isolated');
platformPersistenceExpect((new PersistentDefinitionRepository(new WpdbDefinitionTableGateway($database, DefinitionScope::network(7))))->get($baseId) === null, 'Definition network scope must be isolated from site scope');

$childRevision2 = new Definition(
    id: $childId,
    slug: 'child-settings',
    type: 'settings-page',
    schemaVersion: 1,
    ownerSurfaceId: 1,
    status: DefinitionStatus::Published,
    payload: ['menu_slug' => 'child-settings', 'enabled' => true],
    revision: 2,
    dependencies: [],
);
$repository->save($childRevision2);
platformPersistenceExpect($repository->dependentsOf($baseId) === [], 'Definition dependency replacement must commit atomically with the revision update');

$staleCandidate = new Definition(
    id: $childId,
    slug: 'child-settings',
    type: 'settings-page',
    schemaVersion: 1,
    ownerSurfaceId: 1,
    status: DefinitionStatus::Published,
    payload: ['menu_slug' => 'stale-write'],
    revision: 3,
);
$staleRow = (new DefinitionRowCodec())->encode($staleCandidate);
platformPersistenceExpect(!$gateway->updateIfCurrentRevision($childId, 1, $staleRow, []), 'Definition gateway CAS must reject a stale expected revision');
platformPersistenceExpect($repository->get($childId)?->revision === 2, 'stale Definition CAS must not mutate the committed revision');

$context = new ExecutionContext(
    principal: new Principal(42),
    siteId: 701,
    channel: ExecutionChannel::Ui,
    networkId: 7,
    correlationId: 'corr-definition-save-1',
);
$audit = new AuditRecord(
    id: '33333333-3333-4333-8333-333333333333',
    context: $context,
    ownerSurfaceId: 1,
    action: 'definition/update',
    outcome: AuditOutcome::Success,
    resourceType: 'definition',
    resourceId: $childId,
    reason: 'revision_advanced',
    metadata: ['api_key' => 'do-not-persist', 'safe' => 'visible'],
    retentionClass: 'AR-A',
    privacyClass: 'standard',
);
$codec = new AuditRowCodec();
$expectedAuditHash = (string) $codec->encode($audit)['content_hash'];
$logger = new PersistentAuditLogger($database, $codec);
$logger->record($audit);

$auditRow = $database->getRow($database->prepare(
    "SELECT * FROM `{$auditTables->events}` WHERE event_uuid = %s",
    $audit->id,
));
platformPersistenceExpect($auditRow !== null, 'Audit append must persist the event');
platformPersistenceExpect((int) ($auditRow['network_id'] ?? 0) === 7 && (int) ($auditRow['site_id'] ?? 0) === 701, 'Audit event must retain explicit network/site scope');
platformPersistenceExpect((string) ($auditRow['content_hash'] ?? '') === $expectedAuditHash, 'Audit persisted content hash must match the deterministic semantic envelope');
$metadata = json_decode((string) ($auditRow['metadata_json'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
platformPersistenceExpect(($metadata['api_key'] ?? null) === '[REDACTED]', 'Audit metadata must redact sensitive keys before persistence');
platformPersistenceExpect(($metadata['safe'] ?? null) === 'visible', 'Audit metadata must retain allowed diagnostic context');
platformPersistenceExpect(!str_contains((string) ($auditRow['metadata_json'] ?? ''), 'do-not-persist'), 'Audit plaintext secret must not reach storage');

$duplicateRejected = false;
try {
    $logger->record($audit);
} catch (RuntimeException) {
    $duplicateRejected = true;
}
platformPersistenceExpect($duplicateRejected, 'Audit event UUID uniqueness must reject duplicate appends rather than rewriting history');
platformPersistenceExpect((int) $database->getVar("SELECT COUNT(*) FROM `{$auditTables->events}`") === 1, 'duplicate Audit append must leave exactly one committed event');

foreach ([$auditTables->events, $definitionTables->dependencies, $definitionTables->definitions, $migrationTable] as $table) {
    $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
}

fwrite(STDOUT, "WPEssential Definition/Audit MySQL persistence integration PASS\n");
