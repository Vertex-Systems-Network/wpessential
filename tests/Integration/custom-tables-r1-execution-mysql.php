<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$dsn = getenv('WPE_TEST_MYSQL_DSN') ?: '';
if ($dsn === '') {
    fwrite(STDOUT, "WPEssential Custom Tables R1 execution integration SKIP (no DSN)\n");
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
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\Execution\R1AdditiveExecutionCoordinator;
use WPEssential\Modules\CustomTables\Migration\Execution\R1AdditiveExecutionRequest;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\MetadataPreconditionFacts;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\MetadataPreconditionFactsProviderInterface;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Migration\ProviderDdlPreviewCompiler;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\ExecutionActorType;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\Policy\MigrationExecutionAuthorizationPolicyAdapter;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\BoundMigrationExecutionConfirmation;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\RuntimeCompositionReadinessService;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\StaticMigrationExecutionConfirmationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\Binding\BoundRecoveryEvidence;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\StaticRecoveryVerificationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryClass;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryEvidence;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryRequirement;
use WPEssential\Modules\CustomTables\Migration\Run\InMemoryMigrationRunRepository;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunTransitionService;
use WPEssential\Modules\CustomTables\Migration\TableSchemaDiffPlanner;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

const CT_R1_PREFIX = 'wpetest_';
const CT_R1_DEFINITION = '78888888-8888-4888-8888-888888888888';

function ctR1Expect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

final class CtR1PdoDatabase implements DatabaseAdapterInterface
{
    /** @var list<string> */
    public array $executed = [];

    public function __construct(private readonly PDO $pdo) {}
    public function networkTablePrefix(): string { return CT_R1_PREFIX; }
    public function charsetCollate(): string { return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'; }
    public function prepare(string $query, mixed ...$args): string { return $query; }
    public function getRow(string $query): ?array { return null; }
    public function getResults(string $query): array { return []; }
    public function getVar(string $query): mixed { return null; }
    public function query(string $query): int|bool
    {
        $this->executed[] = $query;
        return $this->pdo->exec($query);
    }
    public function insert(string $table, array $data, array $formats = []): bool { return false; }
    public function lastError(): string { return ''; }
    public function beginTransaction(): void {}
    public function commit(): void {}
    public function rollBack(): void {}
}

/** Minimal read-only wpdb surface used by WordPressCt1SchemaIntrospector. */
final class CtR1PdoWpdb
{
    public string $prefix = CT_R1_PREFIX;
    public string $base_prefix = CT_R1_PREFIX;
    public string $collate = 'utf8mb4_unicode_ci';
    public string $last_error = '';

    public function __construct(private readonly PDO $pdo) {}

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

    /** @return list<array<string,mixed>> */
    public function get_results(string $query, string $output): array
    {
        $rows = $this->pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
        return array_map(static fn (array $row): array => array_change_key_case($row, CASE_LOWER), $rows);
    }

    public function db_server_info(): string
    {
        return (string) $this->pdo->query('SELECT VERSION()')->fetchColumn();
    }
}

$pdo = new PDO($dsn, getenv('WPE_TEST_MYSQL_USER') ?: 'root', getenv('WPE_TEST_MYSQL_PASSWORD') ?: 'root', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
$database = new CtR1PdoDatabase($pdo);
$wpdb = new CtR1PdoWpdb($pdo);
$introspector = new WordPressCt1SchemaIntrospector($wpdb);
$compiler = new ProviderDdlPreviewCompiler();
$capabilities = ProviderCapabilityProfile::fromTrustedServerInfo($wpdb->db_server_info());

$desired = (new TableDefinitionCompiler())->compile(new Definition(
    id: CT_R1_DEFINITION,
    slug: 'r1orders',
    type: 'table',
    schemaVersion: 1,
    ownerSurfaceId: 7,
    status: DefinitionStatus::Published,
    payload: [
        'key' => 'r1orders',
        'label' => 'R1 Orders',
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
$identity = Ct1ManagedTableIdentity::derive(CT_R1_DEFINITION, 'r1orders', CT_R1_PREFIX);
$pdo->exec("DROP TABLE IF EXISTS `{$identity->physicalName}`");

$observedBefore = $introspector->observe($desired);
ctR1Expect(!$observedBefore->exists, 'fixture table must not exist before execution');
$plan = (new TableSchemaDiffPlanner())->plan($desired, $observedBefore);
$preview = $compiler->compile($plan, $desired, $identity, $capabilities, $database->charsetCollate());

$runs = new InMemoryMigrationRunRepository();
$run = new MigrationRun(
    id: '11111111-1111-4111-8111-111111111111',
    planFingerprint: $plan->fingerprint,
    tableKey: 'r1orders',
    targetDefinitionId: CT_R1_DEFINITION,
    targetRevision: $plan->targetRevision,
    targetSchemaVersion: $plan->targetSchemaVersion,
    state: MigrationRunState::Revalidating,
    stateRevision: 4,
);
$runs->create($run);

$checker = new class implements CapabilityCheckerInterface {
    public function can(ExecutionContext $context, string $capability): bool
    {
        return $context->principal->userId === 7 && $capability === 'manage_options';
    }
};
$facts = new class implements MetadataPreconditionFactsProviderInterface {
    public function facts(TableSchemaDescriptor $descriptor, ProviderCapabilityProfile $capabilities): MetadataPreconditionFacts
    {
        return new MetadataPreconditionFacts(false);
    }
};
$readiness = new RuntimeCompositionReadinessService(
    runs: $runs,
    metadataFacts: $facts,
    recoveryVerification: new StaticRecoveryVerificationProvider([
        'artifact-1' => new BoundRecoveryEvidence(
            new RecoveryEvidence($run->planFingerprint, RecoveryClass::TriviallyReversible),
            $capabilities->provider,
            $capabilities->version,
            0,
        ),
    ]),
    confirmations: new StaticMigrationExecutionConfirmationProvider([
        new BoundMigrationExecutionConfirmation($run->id, $run->planFingerprint, $run->stateRevision, ExecutionActorType::User, 7),
    ]),
    authorizationPolicy: new MigrationExecutionAuthorizationPolicyAdapter(new PolicyEngine($checker)),
    networkId: 1,
    siteId: 1,
);
$generation = new MigrationGenerationStamp(
    planFingerprint: $run->planFingerprint,
    sourceObservedFingerprint: $plan->sourceObservedFingerprint,
    targetDefinitionId: CT_R1_DEFINITION,
    targetRevision: $run->targetRevision,
    targetSchemaVersion: $run->targetSchemaVersion,
    provider: $capabilities->provider,
    providerVersion: $capabilities->version,
);

$coordinator = new R1AdditiveExecutionCoordinator(
    $readiness,
    new MigrationRunTransitionService($runs),
    $database,
    $introspector,
    $compiler,
);
$result = $coordinator->execute(new R1AdditiveExecutionRequest(
    run: $run,
    plan: $plan,
    desired: $desired,
    identity: $identity,
    capabilities: $capabilities,
    reviewedPreview: $preview,
    requirements: [],
    reviewedGeneration: $generation,
    currentGeneration: $generation,
    recoveryRequirement: new RecoveryRequirement(RecoveryClass::TriviallyReversible),
    recoveryArtifactId: 'artifact-1',
    maxRecoveryEvidenceAgeHours: 4,
    context: new ExecutionContext(new Principal(7), 1, networkId: 1),
));

ctR1Expect($result->applied, 'R1 create-table plan must apply on a real database: ' . implode(',', $result->reasons));
ctR1Expect($runs->get($run->id)?->state === MigrationRunState::Applied, 'run must end in applied');
ctR1Expect($database->executed === array_map(static fn ($s): string => $s->sql, $preview->statements), 'only the reviewed statements may run');

$observedAfter = $introspector->observe($desired);
ctR1Expect($observedAfter->exists && !$observedAfter->isBlockedByDrift(), 'real table must exist without drift after execution');
$columns = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $pdo->quote($identity->physicalName) . " ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_COLUMN);
ctR1Expect($columns === ['id', 'label'], 'real table must have exactly the desired columns');

$replan = (new TableSchemaDiffPlanner())->plan($desired, $observedAfter);
ctR1Expect($replan->operations === [], 'a second plan after execution must be a no-op');

$pdo->exec("DROP TABLE IF EXISTS `{$identity->physicalName}`");
fwrite(STDOUT, "WPEssential Custom Tables R1 execution integration PASS ({$capabilities->provider} {$capabilities->version})\n");
