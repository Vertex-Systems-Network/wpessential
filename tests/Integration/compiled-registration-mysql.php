<?php

declare(strict_types=1);


if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$dsn = getenv('WPE_TEST_MYSQL_DSN') ?: '';
if ($dsn === '') {
    fwrite(STDOUT, "WPEssential compiled registration MySQL integration SKIP (no DSN)\n");
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

use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\WordPress\Registrations\AtomicCompiledRegistrationStore;
use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationManifest;
use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationManifestIntegrity;
use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationScope;
use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationTableNames;
use WPEssential\Platform\WordPress\Registrations\Migrations\CreateCompiledRegistrationTablesMigration;
use WPEssential\Platform\WordPress\Registrations\RegistrationCompiler;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinition;
use WPEssential\Platform\WordPress\Registrations\RegistrationKind;
use WPEssential\Platform\WordPress\Registrations\WpdbCompiledRegistrationPersistenceGateway;

function mysqlCompiledExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

final class PdoDatabaseAdapter implements DatabaseAdapterInterface
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
$database = new PdoDatabaseAdapter($pdo);
$tables = new CompiledRegistrationTableNames($database);
$pdo->exec("DROP TABLE IF EXISTS `{$tables->state}`");
$pdo->exec("DROP TABLE IF EXISTS `{$tables->generations}`");
(new CreateCompiledRegistrationTablesMigration($database))->apply();

$gateway = new WpdbCompiledRegistrationPersistenceGateway($database);
$scope = CompiledRegistrationScope::site(7, 701);
$store = new AtomicCompiledRegistrationStore($gateway, $scope);
$compiler = new RegistrationCompiler($store);
$compiler->compileAndPublish([new RegistrationDefinition('pt-1', RegistrationKind::PostType, 'book', ['public' => true])]);
$compiler->compileAndPublish([new RegistrationDefinition('pt-1', RegistrationKind::PostType, 'book', ['public' => false], true, 2)]);
$pointer = $gateway->pointer($scope);
mysqlCompiledExpect($pointer->activeGeneration === 2 && $pointer->fallbackGeneration === 1, 'MySQL pointer must atomically retain active + fallback generations');
mysqlCompiledExpect((new AtomicCompiledRegistrationStore($gateway, CompiledRegistrationScope::site(7, 702)))->active() === null, 'MySQL compiled state must be site isolated');
mysqlCompiledExpect((new AtomicCompiledRegistrationStore($gateway, CompiledRegistrationScope::network(7)))->active() === null, 'MySQL network scope must be isolated from site scope');

$pdo->exec("UPDATE `{$tables->generations}` SET checksum = REPEAT('0', 64) WHERE network_id = 7 AND site_id = 701 AND generation = 2");
$recovered = $store->active();
mysqlCompiledExpect($recovered?->generation === 1, 'MySQL store must recover a checksum-corrupt active generation to last-known-good');
$recoveredPointer = $gateway->pointer($scope);
mysqlCompiledExpect($recoveredPointer->activeGeneration === 1 && $recoveredPointer->fallbackGeneration === null, 'MySQL recovery must atomically update scope pointer');
mysqlCompiledExpect($gateway->latestGeneration($scope) === 2 && $store->nextGeneration() === 3, 'MySQL recovery must preserve corrupt generation 2 in the historical high-watermark');

$generation3 = $compiler->compileAndPublish([new RegistrationDefinition('pt-1', RegistrationKind::PostType, 'book', ['public' => true], true, 3)]);
mysqlCompiledExpect($generation3->generation === 3, 'post-recovery MySQL publication must advance to generation 3 rather than reusing generation 2');
mysqlCompiledExpect($gateway->pointer($scope)->fallbackGeneration === 1, 'post-recovery MySQL publication must retain recovered active generation as fallback');

$entries = ['post_type' => ['movie' => ['id' => 'pt-2', 'revision' => 1, 'payload' => ['public' => true]]]];
$staleManifest = new CompiledRegistrationManifest(4, $entries, CompiledRegistrationManifestIntegrity::checksum(4, $entries));
mysqlCompiledExpect(!$gateway->publishAtomically($scope, 1, $staleManifest), 'MySQL compare-and-swap must reject stale active-pointer writers');
mysqlCompiledExpect($gateway->latestGeneration($scope) === 3, 'failed stale MySQL CAS must not consume generation 4');

$pdo->exec("UPDATE `{$tables->generations}` SET manifest_json = '{invalid-json' WHERE network_id = 7 AND site_id = 701 AND generation = 3");
$recoveredAgain = $store->active();
mysqlCompiledExpect($recoveredAgain?->generation === 1, 'invalid active JSON payload must be quarantined and recover to last-known-good');
mysqlCompiledExpect($store->nextGeneration() === 4, 'invalid JSON recovery must preserve immutable generation 3 high-watermark');

$generationCount = (int) $pdo->query("SELECT COUNT(*) FROM `{$tables->generations}` WHERE network_id = 7 AND site_id = 701")->fetchColumn();
mysqlCompiledExpect($generationCount === 3, 'MySQL compiled generations must remain immutable, including two quarantined historical generations');

$pdo->exec("DROP TABLE IF EXISTS `{$tables->state}`");
$pdo->exec("DROP TABLE IF EXISTS `{$tables->generations}`");
fwrite(STDOUT, "WPEssential compiled registration MySQL integration PASS\n");
