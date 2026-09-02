<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$dsn = getenv('WPE_TEST_MYSQL_DSN') ?: '';
if ($dsn === '') {
    fwrite(STDOUT, "WPEssential Relations Query consumer integration SKIP (no DSN)\n");
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

use WPEssential\Contracts\RelationQueryConsumerInterface;
use WPEssential\Modules\Relations\Migrations\AllowNonUniqueRelationEdgeTuplesMigration;
use WPEssential\Modules\Relations\Migrations\CreateRelationEdgeTablesMigration;
use WPEssential\Modules\Relations\RelationDefinitionNormalizer;
use WPEssential\Modules\Relations\RelationEdge;
use WPEssential\Modules\Relations\RelationEdgeScope;
use WPEssential\Modules\Relations\RelationEdgeTableNames;
use WPEssential\Modules\Relations\RelationQueryConsumer;
use WPEssential\Modules\Relations\RelationQueryReadGateway;
use WPEssential\Modules\Relations\WpdbRelationEdgeGateway;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

function relationQueryExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

final class RelationQueryPdoDatabaseAdapter implements DatabaseAdapterInterface
{
    private string $error = '';

    public function __construct(private readonly PDO $pdo) {}
    public function networkTablePrefix(): string { return 'wpeq_'; }
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
        $row = $this->pdo->query($query)->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }
    public function getResults(string $query): array
    {
        return $this->pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getVar(string $query): mixed
    {
        $value = $this->pdo->query($query)->fetchColumn();
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
        $columns = array_keys($data);
        foreach ($columns as $column) {
            if (preg_match('/^[A-Za-z0-9_]+$/', (string) $column) !== 1) throw new RuntimeException('Unsafe test column name.');
        }
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
$database = new RelationQueryPdoDatabaseAdapter($pdo);
$tables = new RelationEdgeTableNames($database);
$pdo->exec("DROP TABLE IF EXISTS `{$tables->state}`");
$pdo->exec("DROP TABLE IF EXISTS `{$tables->edges}`");
(new CreateRelationEdgeTablesMigration($database))->apply();
(new AllowNonUniqueRelationEdgeTuplesMigration($database))->apply();

$scope = RelationEdgeScope::site(9, 17);
$gateway = new WpdbRelationEdgeGateway($database, $scope, static fn (): string => '2026-09-02 08:00:00.000000');
$relationId = '11111111-1111-4111-8111-111111111111';
$edges = [
    new RelationEdge('21111111-1111-4111-8111-111111111111', $relationId, 31, 41, '2026-09-02 08:00:00.000000', '2026-09-02 08:00:00.000000'),
    new RelationEdge('31111111-1111-4111-8111-111111111111', $relationId, 31, 42, '2026-09-02 08:00:01.000000', '2026-09-02 08:00:01.000000'),
    new RelationEdge('41111111-1111-4111-8111-111111111111', $relationId, 32, 42, '2026-09-02 08:00:02.000000', '2026-09-02 08:00:02.000000'),
    new RelationEdge('51111111-1111-4111-8111-111111111111', $relationId, 31, 42, '2026-09-02 08:00:03.000000', '2026-09-02 08:00:03.000000'),
];
$revision = $gateway->beginRelationMutation($relationId);
foreach ($edges as $edge) {
    $gateway->insertEdge($edge);
}
$gateway->completeRelationMutation($relationId, $revision);

$normalizer = new RelationDefinitionNormalizer();
$definitions = new InMemoryDefinitionRepository();
$definitions->save(new Definition(
    id: $relationId,
    slug: 'relation-book-authors',
    type: RelationDefinitionNormalizer::DEFINITION_TYPE,
    schemaVersion: 1,
    ownerSurfaceId: RelationDefinitionNormalizer::OWNER_SURFACE_ID,
    status: DefinitionStatus::Published,
    payload: $normalizer->normalize([
        'relation_key' => 'book_authors',
        'title' => 'Book Authors',
        'description' => '',
        'cardinality' => 'many_to_many',
        'direction' => ['reciprocal' => false, 'bidirectional_traversal' => true],
        'from' => ['object_type' => 'post', 'object_subtype' => 'book', 'label' => 'Books'],
        'to' => ['object_type' => 'user', 'label' => 'Authors'],
        'bounds' => ['from_min' => 0, 'from_max' => null, 'to_min' => 0, 'to_max' => null],
        'unique_edge' => false,
    ], true),
    revision: 4,
));

$consumer = new RelationQueryConsumer(
    $definitions,
    $normalizer,
    new RelationQueryReadGateway($database, $scope),
    $scope,
);
$context = new ExecutionContext(new Principal(7), 17, networkId: 9);
$descriptor = $consumer->describe($relationId, $context);
relationQueryExpect($descriptor['contract_version'] === RelationQueryConsumerInterface::CONTRACT_VERSION, 'contract version must be public and stable');
relationQueryExpect($descriptor['definition_revision'] === 4, 'definition revision must be exposed');
relationQueryExpect($descriptor['mutation_revision'] === 1, 'mutation revision must reflect committed edge generation');
relationQueryExpect(!isset($descriptor['table'], $descriptor['sql'], $descriptor['gateway']), 'descriptor must not leak private storage');

$related = $consumer->relatedObjectIds($relationId, RelationQueryConsumerInterface::DIRECTION_FROM, 31, 10, $context);
relationQueryExpect($related === [41, 42], 'related ids must be distinct even when duplicate physical edges exist');
$matching = $consumer->matchingAnchorObjectIds(
    $relationId,
    RelationQueryConsumerInterface::DIRECTION_FROM,
    [31, 32, 33],
    [42],
    10,
    $context,
);
relationQueryExpect($matching === [31, 32], 'batch existence must filter anchors in one bounded public operation');
relationQueryExpect(
    $consumer->countRelatedObjects($relationId, RelationQueryConsumerInterface::DIRECTION_FROM, 31, $context) === 2,
    'count semantics must count distinct related objects rather than physical duplicate edges',
);
$reverse = $consumer->relatedObjectIds($relationId, RelationQueryConsumerInterface::DIRECTION_TO, 42, 10, $context);
relationQueryExpect($reverse === [31, 32], 'enabled reverse traversal must return distinct source ids');

$pdo->exec("DROP TABLE IF EXISTS `{$tables->state}`");
$pdo->exec("DROP TABLE IF EXISTS `{$tables->edges}`");
fwrite(STDOUT, "WPEssential Relations Query consumer integration PASS\n");
