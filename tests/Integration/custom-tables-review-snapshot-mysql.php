<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$dsn = getenv('WPE_TEST_MYSQL_DSN') ?: '';
if ($dsn === '') {
    fwrite(STDOUT, "WPEssential Custom Tables review snapshot integration SKIP (no DSN)\n");
    exit(0);
}

$root = dirname(__DIR__, 2);
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) return;
    $path = $root . '/frameworks/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) require $path;
});

use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Migration\ProviderDdlPreviewCompiler;
use WPEssential\Modules\CustomTables\Migration\Review\MigrationReviewSnapshot;
use WPEssential\Modules\CustomTables\Migration\Review\Persistence\CreateMigrationReviewSnapshotStoreMigration;
use WPEssential\Modules\CustomTables\Migration\Review\Persistence\WpdbMigrationReviewSnapshotRepository;
use WPEssential\Modules\CustomTables\Migration\Review\R1MigrationRunPreparationService;
use WPEssential\Modules\CustomTables\Migration\Run\InMemoryMigrationRunRepository;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunTransitionService;
use WPEssential\Modules\CustomTables\Migration\TableSchemaDiffPlanner;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

const CT_RS_PREFIX = 'wpetest_';
const CT_RS_DEFINITION = '78888888-8888-4888-8888-888888888888';

function ctRsExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** Real PDO-backed adapter: prepared substitution, row reads and inserts as wpdb would perform them. */
final class CtRsPdoDatabase implements DatabaseAdapterInterface
{
    /** @var list<string> */
    public array $ddl = [];

    public function __construct(private readonly PDO $pdo) {}
    public function networkTablePrefix(): string { return CT_RS_PREFIX; }
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
    public function query(string $query): int|bool
    {
        $this->ddl[] = $query;
        return $this->pdo->exec($query);
    }
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

/** Minimal read-only wpdb surface used by WordPressCt1SchemaIntrospector. */
final class CtRsPdoWpdb
{
    public string $prefix = CT_RS_PREFIX;
    public string $base_prefix = CT_RS_PREFIX;
    public string $collate = 'utf8mb4_unicode_ci';
    public string $last_error = '';

    public function __construct(private readonly PDO $pdo, private readonly CtRsPdoDatabase $database) {}
    public function prepare(string $query, mixed ...$args): string { return $this->database->prepare($query, ...$args); }
    /** @return list<array<string,mixed>> */
    public function get_results(string $query, string $output): array
    {
        $rows = $this->pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
        return array_map(static fn (array $row): array => array_change_key_case($row, CASE_LOWER), $rows);
    }
    public function db_server_info(): string { return (string) $this->pdo->query('SELECT VERSION()')->fetchColumn(); }
}

$pdo = new PDO($dsn, getenv('WPE_TEST_MYSQL_USER') ?: 'root', getenv('WPE_TEST_MYSQL_PASSWORD') ?: 'root', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
$database = new CtRsPdoDatabase($pdo);
$wpdb = new CtRsPdoWpdb($pdo, $database);
$introspector = new WordPressCt1SchemaIntrospector($wpdb);
$capabilities = ProviderCapabilityProfile::fromTrustedServerInfo($wpdb->db_server_info());

$desired = (new TableDefinitionCompiler())->compile(new Definition(
    id: CT_RS_DEFINITION,
    slug: 'rsorders',
    type: 'table',
    schemaVersion: 1,
    ownerSurfaceId: 7,
    status: DefinitionStatus::Published,
    payload: [
        'key' => 'rsorders',
        'label' => 'Review Snapshot Orders',
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
$identity = Ct1ManagedTableIdentity::derive(CT_RS_DEFINITION, 'rsorders', CT_RS_PREFIX);
$store = CT_RS_PREFIX . 'wpe_custom_table_migration_reviews';
$pdo->exec("DROP TABLE IF EXISTS `{$identity->physicalName}`");
$pdo->exec("DROP TABLE IF EXISTS `{$store}`");

// 1. The store migration creates a real table; applying it twice is harmless.
$migration = new CreateMigrationReviewSnapshotStoreMigration($database);
$migration->apply();
$migration->apply();
$columns = array_column($pdo->query("SHOW COLUMNS FROM `{$store}`")->fetchAll(PDO::FETCH_ASSOC), 'Field');
ctRsExpect(in_array('preview_hash', $columns, true) && in_array('provider_version', $columns, true), 'review snapshot store columns must exist');

// 2. Preparation against the live server records a snapshot that reads back through real column types.
$runs = new InMemoryMigrationRunRepository();
$snapshots = new WpdbMigrationReviewSnapshotRepository($database, 1);
$compiler = new ProviderDdlPreviewCompiler();
$service = new R1MigrationRunPreparationService(
    $introspector,
    new TableSchemaDiffPlanner(),
    $compiler,
    $database,
    $runs,
    new MigrationRunTransitionService($runs),
    $snapshots,
);
$ddlBefore = count($database->ddl);
$result = $service->prepare($desired, $identity, $capabilities);
ctRsExpect($result->prepared, 'preparation must succeed for a missing table: ' . implode(',', $result->reasons));
ctRsExpect(count($database->ddl) === $ddlBefore, 'preparation must not run any DDL');
ctRsExpect($runs->get($result->run->id)?->state === MigrationRunState::Planned, 'prepared run must be planned');
$tableCount = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = " . $pdo->quote($identity->physicalName))->fetchColumn();
ctRsExpect($tableCount === 0, 'preparation must not create the managed table');

$stored = $snapshots->snapshotFor($result->run->id);
ctRsExpect($stored !== null && $stored->canonical() === $result->snapshot->canonical(), 'stored snapshot must read back identically');
ctRsExpect($stored->generation->provider === $capabilities->provider && $stored->generation->providerVersion === $capabilities->version, 'snapshot must carry the detected provider');

// 3. At execution time the recompiled preview matches the reviewed one while nothing has changed.
$replan = (new TableSchemaDiffPlanner())->plan($desired, $introspector->observe($desired));
$recompiled = $compiler->compile($replan, $desired, $identity, $capabilities, $database->charsetCollate());
ctRsExpect($stored->matchesPreview($recompiled), 'recompiled preview must match the reviewed snapshot');

// 4. Insert-once: a second record for the same run is refused by the store.
$refused = false;
try {
    $snapshots->record(new MigrationReviewSnapshot($result->run->id, str_repeat('a', 64), $stored->generation));
} catch (RuntimeException) {
    $refused = true;
}
ctRsExpect($refused, 'second snapshot for the same run must be refused');
ctRsExpect((int) $pdo->query("SELECT COUNT(*) FROM `{$store}`")->fetchColumn() === 1, 'store must hold exactly one snapshot');

$pdo->exec("DROP TABLE IF EXISTS `{$store}`");
fwrite(STDOUT, 'WPEssential Custom Tables review snapshot integration PASS (' . $capabilities->provider . ' ' . $capabilities->version . ")\n");
