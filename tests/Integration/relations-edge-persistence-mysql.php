<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$dsn = getenv('WPE_TEST_MYSQL_DSN') ?: '';
if ($dsn === '') {
    fwrite(STDOUT, "WPEssential Relations edge persistence integration SKIP (no DSN)\n");
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

use WPEssential\Modules\Relations\Migrations\CreateRelationEdgeTablesMigration;
use WPEssential\Modules\Relations\RelationEdge;
use WPEssential\Modules\Relations\RelationEdgeScope;
use WPEssential\Modules\Relations\RelationEdgeTableNames;
use WPEssential\Modules\Relations\WpdbRelationEdgeGateway;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

function relationEdgeExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

final class RelationEdgePdoDatabaseAdapter implements DatabaseAdapterInterface
{
    private string $error = '';

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
    public function query(string $query): int|bool
    {
        try {
            $this->error = '';
            return $this->pdo->exec($query);
        } catch (Throwable $error) {
            $this->error = $error->getMessage();
            throw $error;
        }
    }
    public function insert(string $table, array $data, array $formats = []): bool
    {
        if (preg_match('/^[A-Za-z0-9_]+$/', $table) !== 1) throw new RuntimeException('Unsafe test table name.');
        foreach (array_keys($data) as $column) {
            if (preg_match('/^[A-Za-z0-9_]+$/', (string) $column) !== 1) throw new RuntimeException('Unsafe test column name.');
        }
        $columns = array_keys($data);
        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`,`', $columns),
            implode(',', array_fill(0, count($columns), '?')),
        );
        try {
            $this->error = '';
            return $this->pdo->prepare($sql)->execute(array_values($data));
        } catch (Throwable $error) {
            $this->error = $error->getMessage();
            throw $error;
        }
    }
    public function lastError(): string { return $this->error; }
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
$database = new RelationEdgePdoDatabaseAdapter($pdo);
$tables = new RelationEdgeTableNames($database);
$pdo->exec("DROP TABLE IF EXISTS `{$tables->state}`");
$pdo->exec("DROP TABLE IF EXISTS `{$tables->edges}`");
(new CreateRelationEdgeTablesMigration($database))->apply();

$relationId = '11111111-1111-4111-8111-111111111111';
$edgeId = '22222222-2222-4222-8222-222222222222';
$duplicateId = '33333333-3333-4333-8333-333333333333';
$rollbackId = '44444444-4444-4444-8444-444444444444';
$now = '2026-09-02 00:00:00.123456';
$gateway = new WpdbRelationEdgeGateway($database, RelationEdgeScope::site(7, 701), static fn (): string => $now);

relationEdgeExpect($gateway->beginRelationMutation($relationId) === 0, 'first mutation revision must start at zero');
$gateway->insertEdge(new RelationEdge($edgeId, $relationId, 31, 41, $now, $now));
relationEdgeExpect($gateway->completeRelationMutation($relationId, 0) === 1, 'first committed mutation must advance revision to one');
relationEdgeExpect($gateway->revision($relationId) === 1, 'committed revision must persist');
relationEdgeExpect($gateway->findById($edgeId)?->toObjectId === 41, 'edge id lookup must round-trip persisted row');
relationEdgeExpect(count($gateway->bySource($relationId, 31)) === 1, 'source lookup must return persisted edge');
relationEdgeExpect(count($gateway->byTarget($relationId, 41)) === 1, 'target lookup must return persisted edge');
relationEdgeExpect((new WpdbRelationEdgeGateway($database, RelationEdgeScope::site(7, 702)))->findById($edgeId) === null, 'edge rows must be site isolated');
relationEdgeExpect((new WpdbRelationEdgeGateway($database, RelationEdgeScope::network(7)))->findById($edgeId) === null, 'network scope must not see site edges');

$duplicateRejected = false;
try {
    relationEdgeExpect($gateway->beginRelationMutation($relationId) === 1, 'duplicate attempt must lock current revision');
    $gateway->insertEdge(new RelationEdge($duplicateId, $relationId, 31, 41, $now, $now));
} catch (Throwable) {
    $duplicateRejected = true;
}
relationEdgeExpect($duplicateRejected, 'duplicate relation/source/target tuple must be rejected by durable uniqueness');
relationEdgeExpect($gateway->revision($relationId) === 1, 'failed duplicate mutation must not advance revision');
$edgeCount = (int) $pdo->query("SELECT COUNT(*) FROM `{$tables->edges}` WHERE network_id = 7 AND site_id = 701")->fetchColumn();
relationEdgeExpect($edgeCount === 1, 'failed duplicate mutation must preserve exactly one edge');

relationEdgeExpect($gateway->beginRelationMutation($relationId) === 1, 'rollback test must lock revision one');
$gateway->insertEdge(new RelationEdge($rollbackId, $relationId, 32, 42, $now, $now));
$gateway->rollbackRelationMutation();
$edgeCount = (int) $pdo->query("SELECT COUNT(*) FROM `{$tables->edges}` WHERE network_id = 7 AND site_id = 701")->fetchColumn();
relationEdgeExpect($edgeCount === 1, 'explicit rollback must discard uncommitted edge');
relationEdgeExpect($gateway->revision($relationId) === 1, 'explicit rollback must preserve mutation revision');

relationEdgeExpect($gateway->beginRelationMutation($relationId) === 1, 'delete mutation must lock revision one');
relationEdgeExpect($gateway->deleteEdge($relationId, $edgeId), 'delete must remove existing edge');
relationEdgeExpect($gateway->completeRelationMutation($relationId, 1) === 2, 'delete commit must advance revision to two');
relationEdgeExpect($gateway->findById($edgeId) === null, 'deleted edge must no longer resolve');

$pdo->exec("DROP TABLE IF EXISTS `{$tables->state}`");
$pdo->exec("DROP TABLE IF EXISTS `{$tables->edges}`");
fwrite(STDOUT, "WPEssential Relations edge persistence integration PASS\n");
