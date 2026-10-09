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

use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionCreateOnlyRepositoryInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetImportPreflightService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDraftImportService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetMappedDraftImportService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityMappingPreviewService;
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

// RB-0110: exercise opt-in atomic create-only on the real disposable
// MySQL schema, including scoped primary/unique keys and no upsert fallback.
$importId = '44444444-4444-4444-8444-444444444444';
$conflictingSlugId = '55555555-5555-4555-8555-555555555555';
$importCandidate = new Definition(
    id: $importId,
    slug: 'portable-preset-import-target',
    type: 'dashboard-widget-preset',
    schemaVersion: 1,
    ownerSurfaceId: 10,
    status: DefinitionStatus::Published,
    payload: ['preset' => ['label' => 'Create-only candidate']],
);
$repository->create($importCandidate);
platformPersistenceExpect($repository->get($importId)?->payload === $importCandidate->payload, 'create-only MySQL insert must persist the initial payload');
platformPersistenceExpect($repository->get($importId)?->revision === 1, 'created MySQL row must begin at revision one');

$expectCreateConflict = static function (Definition $attempt, string $assertion) use ($repository): void {
    $rejected = false;
    try {
        $repository->create($attempt);
    } catch (RuntimeException) {
        $rejected = true;
    }
    platformPersistenceExpect($rejected, $assertion);
};

$expectCreateConflict(new Definition(
    id: $importId,
    slug: 'portable-preset-import-target',
    type: 'dashboard-widget-preset',
    schemaVersion: 1,
    ownerSurfaceId: 10,
    status: DefinitionStatus::Published,
    payload: ['preset' => ['label' => 'OVERWRITE ATTEMPT']],
    revision: 1,
), 'create-only real MySQL PK must reject occupied ID even with a different payload');
$expectCreateConflict(new Definition(
    id: $importId,
    slug: 'portable-preset-import-target',
    type: 'dashboard-widget-preset',
    schemaVersion: 1,
    ownerSurfaceId: 10,
    status: DefinitionStatus::Published,
    payload: ['preset' => ['label' => 'ILLEGAL REVISION']],
    revision: 2,
), 'create-only real MySQL must reject even a higher revision instead of updating');
$expectCreateConflict(new Definition(
    id: $conflictingSlugId,
    slug: 'portable-preset-import-target',
    type: 'dashboard-widget-preset',
    schemaVersion: 1,
    ownerSurfaceId: 10,
    status: DefinitionStatus::Published,
    payload: ['preset' => ['label' => 'DUPLICATE SLUG']],
), 'real MySQL scoped type/slug UNIQUE KEY must reject separate IDs');
$expectCreateConflict(new Definition(
    id: $conflictingSlugId,
    slug: 'invalid-checksum',
    type: 'dashboard-widget-preset',
    schemaVersion: 1,
    ownerSurfaceId: 10,
    status: DefinitionStatus::Published,
    payload: ['preset' => ['label' => 'WRONG CHECKSUM']],
    checksum: str_repeat('0', 64),
), 'create-only checksum check must fail without inserting into real MySQL');
platformPersistenceExpect(
    $repository->get($importId)?->payload === $importCandidate->payload
    && $repository->get($importId)?->revision === 1,
    'rejected real MySQL create-only collisions must leave existing committed record unchanged',
);
platformPersistenceExpect($repository->get($conflictingSlugId) === null, 'duplicate-slug and wrong-checksum attempts may not leave inserted rows');
platformPersistenceExpect(
    count($repository->byType('dashboard-widget-preset')) === 1,
    'create-only MySQL collisions may not increase the persisted preset count',
);

// Scoped Definition tables intentionally allow the same ID/type/slug on a
// different subsite or network. Never reuse a production DB in this fixture.
$otherSite = new PersistentDefinitionRepository(new WpdbDefinitionTableGateway(
    $database, DefinitionScope::site(7, 702),
));
$networkStore = new PersistentDefinitionRepository(new WpdbDefinitionTableGateway(
    $database, DefinitionScope::network(7),
));
$otherSite->create($importCandidate);
$networkStore->create($importCandidate);
platformPersistenceExpect($otherSite->get($importId)?->payload === $importCandidate->payload, 'new subsite scope must independently create own preset ID');
platformPersistenceExpect($networkStore->get($importId)?->payload === $importCandidate->payload, 'network scope must independently create own preset ID');
platformPersistenceExpect($repository->get($importId)?->revision === 1, 'isolated inserts may not change original site row');

// RB-0113: prove that the *existing* RB-0111 internal Draft-only import
// service uses real disposable MySQL insert-once persistence. No actual
// WordPress current-user or site identity is asserted here; RB-0112 covered
// those gates against pinned real WordPress. This fixture uses a synthetic
// scoped test capability checker and never touches production credentials.
$draftWidgetId = '66666666-6666-4666-8666-666666666666';
$draftTargetId = '77777777-7777-4777-8777-777777777777';
$draftSecondId = '88888888-8888-4888-8888-888888888888';
$draftWidget = new Definition(
    id: $draftWidgetId, slug: 'draft-import-ref-widget', type: DashboardWidgetDefinition::TYPE,
    schemaVersion: 1, ownerSurfaceId: 10, status: DefinitionStatus::Published, payload: [],
);
$repository->create($draftWidget);
$otherSite->create($draftWidget);
$draftPayload = [
    'definition_id' => $draftTargetId,
    'revision' => 5,
    'label' => 'MySQL Draft Import',
    'widget_definition_ids' => [$draftWidgetId],
    'assignment' => ['roles' => ['administrator', 'editor'], 'network_default' => false],
];
$draftSnapshot = [
    'format' => 'wpessential-dashboard-preset',
    'version' => 1,
    'payload' => $draftPayload,
    'sha256' => hash('sha256', json_encode(
        $draftPayload,
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    )),
];
$draftChecker = new class implements CapabilityCheckerInterface {
    public function can(ExecutionContext $context, string $capability): bool
    {
        return $capability === 'manage_options'
            && $context->principal->userId === 42
            && $context->siteId > 0;
    }
};
$draftService = static fn (PersistentDefinitionRepository $store): DashboardWidgetPresetDraftImportService =>
    new DashboardWidgetPresetDraftImportService(
        $store,
        new DashboardWidgetPresetImportPreflightService($store, new DashboardWidgetPresetCompiler($store)),
        $draftChecker,
    );
$draftContext = new ExecutionContext(new Principal(42), 701, ExecutionChannel::Internal, 7);
$draftOtherContext = new ExecutionContext(new Principal(42), 702, ExecutionChannel::Internal, 7);
$originalPublishedPreset = $repository->get($importId);
$originalPublishedWidget = $repository->get($draftWidgetId);
$draftImporter = $draftService($repository);
$otherImporter = $draftService($otherSite);

platformPersistenceExpect(
    $draftImporter->importDraft(
        new ExecutionContext(new Principal(42), 701, ExecutionChannel::Rest, 7),
        $draftSnapshot, 'mysql-draft-import',
    ) === ['status' => 'forbidden'],
    'REST channel may not call internal-only MySQL Draft importer',
);
platformPersistenceExpect(
    $draftImporter->importDraft(
        new ExecutionContext(new Principal(null), 701, ExecutionChannel::Internal, 7),
        $draftSnapshot, 'mysql-draft-import',
    ) === ['status' => 'forbidden'],
    'guest may not call internal MySQL Draft importer',
);
$badDraft = $draftSnapshot;
$badDraft['sha256'] = str_repeat('0', 64);
platformPersistenceExpect(
    $draftImporter->importDraft($draftContext, $badDraft, 'mysql-draft-import') === ['status' => 'invalid_snapshot'],
    'invalid snapshot digest may not create MySQL Draft',
);
$networkDraft = $draftSnapshot;
$networkDraft['payload']['assignment'] = ['roles' => [], 'network_default' => true];
$networkDraft['sha256'] = hash('sha256', json_encode(
    $networkDraft['payload'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
));
platformPersistenceExpect(
    $draftImporter->importDraft($draftContext, $networkDraft, 'mysql-draft-import') === ['status' => 'forbidden'],
    'site administrator may not create network-default Draft through internal importer',
);
platformPersistenceExpect($repository->get($draftTargetId) === null, 'all denied MySQL Draft imports must leave no row');
platformPersistenceExpect(
    $draftImporter->importDraft($draftContext, $draftSnapshot, 'mysql-draft-import')
        === ['status' => 'created_draft', 'definition_id' => $draftTargetId],
    'authorized internal human test context must create one real MySQL Draft row',
);
$persistedDraft = $repository->get($draftTargetId);
platformPersistenceExpect($persistedDraft instanceof Definition, 'created Draft must persist in actual MySQL');
platformPersistenceExpect($persistedDraft->status === DefinitionStatus::Draft, 'MySQL importer must persist only Draft');
platformPersistenceExpect($persistedDraft->revision === 1, 'imported Draft must start at destination revision one');
platformPersistenceExpect($persistedDraft->slug === 'mysql-draft-import', 'imported Draft must preserve supplied bounded slug');
platformPersistenceExpect($persistedDraft->payload === ['preset' => [
    'label' => 'MySQL Draft Import',
    'widget_definition_ids' => [$draftWidgetId],
    'assignment' => ['roles' => ['administrator', 'editor'], 'network_default' => false],
]], 'MySQL Draft must preserve canonical preset label/order/role assignment');
platformPersistenceExpect(
    $draftImporter->importDraft($draftContext, $draftSnapshot, 'changed-slug') === ['status' => 'id_conflict'],
    'same preset UUID must not update existing committed MySQL Draft',
);
$duplicateSlugSnapshot = $draftSnapshot;
$duplicateSlugSnapshot['payload']['definition_id'] = $draftSecondId;
$duplicateSlugSnapshot['sha256'] = hash('sha256', json_encode(
    $duplicateSlugSnapshot['payload'],
    JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
));
platformPersistenceExpect(
    $draftImporter->importDraft($draftContext, $duplicateSlugSnapshot, 'mysql-draft-import') === ['status' => 'write_failed'],
    'different UUID with existing type and slug must fail insert-only unique index',
);
platformPersistenceExpect($repository->get($draftSecondId) === null, 'type+slug collision cannot leave MySQL candidate row');
platformPersistenceExpect($repository->get($draftTargetId) == $persistedDraft, 'rejected MySQL imports may not update committed Draft');
platformPersistenceExpect($repository->get($importId) == $originalPublishedPreset, 'existing Published preset must remain unchanged');
platformPersistenceExpect($repository->get($draftWidgetId) == $originalPublishedWidget, 'Published target widget must remain unchanged');
platformPersistenceExpect($networkStore->get($draftTargetId) === null, 'site Draft import must not write to network scope');
platformPersistenceExpect(
    $otherImporter->importDraft($draftOtherContext, $draftSnapshot, 'mysql-draft-import')
        === ['status' => 'created_draft', 'definition_id' => $draftTargetId],
    'independent subsite MySQL scope may create the same Draft id once without modifying original site',
);
platformPersistenceExpect($otherSite->get($draftTargetId)?->status === DefinitionStatus::Draft, 'second site import must also stay Draft');
platformPersistenceExpect($repository->get($draftTargetId) == $persistedDraft, 'second subsite create may not modify first site');

// RB-0116: use actual disposable MySQL persistence for the accepted
// internal-only cross-site mapped Draft flow. Synthetic capability assertions
// here are NOT real WP identity evidence (RB-0115 proves that separately).
$mappedMysqlWidgetB = '99999999-9999-4999-8999-999999999999';
$mappedMysqlTarget = '3d3d3d3d-3d3d-4d3d-8d3d-3d3d3d3d3d3d';
$mappedMysqlSecondTarget = '4d4d4d4d-4d4d-4d4d-8d4d-4d4d4d4d4d4d';
$mappedPublishedB = new Definition(
    id: $mappedMysqlWidgetB, slug: 'mapped-draft-destination-widget-b',
    type: DashboardWidgetDefinition::TYPE, schemaVersion: 1,
    ownerSurfaceId: 10, status: DefinitionStatus::Published, payload: [],
);
$repository->create($mappedPublishedB);
$otherSite->create($mappedPublishedB);
$sourceIdA = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
$sourceIdB = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
$mappedSourcePayload = [
    'definition_id' => '5d5d5d5d-5d5d-4d5d-8d5d-5d5d5d5d5d5d',
    'revision' => 8,
    'label' => 'MySQL Cross-site Mapping',
    'widget_definition_ids' => [$sourceIdB, $sourceIdA],
    'assignment' => ['roles' => ['administrator', 'editor'], 'network_default' => false],
];
$mappedMysqlSnapshot = [
    'format' => 'wpessential-dashboard-preset',
    'version' => 1,
    'payload' => $mappedSourcePayload,
    'sha256' => hash('sha256', json_encode(
        $mappedSourcePayload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    )),
];
$mappingToPublished = [$sourceIdA => $draftWidgetId, $sourceIdB => $mappedMysqlWidgetB];
$mappedImporterFor = static function (PersistentDefinitionRepository $store) use ($draftChecker): DashboardWidgetPresetMappedDraftImportService {
    $preflight = new DashboardWidgetPresetImportPreflightService($store, new DashboardWidgetPresetCompiler($store));
    return new DashboardWidgetPresetMappedDraftImportService(
        new DashboardWidgetPresetPortabilityMappingPreviewService($preflight),
        new DashboardWidgetPresetDraftImportService($store, $preflight, $draftChecker),
    );
};
$mappedMysqlImporter = $mappedImporterFor($repository);
$site702MappedImporter = $mappedImporterFor($otherSite);
$originalPublishedMappedWidget = $repository->get($draftWidgetId);
$originalNewPublishedWidget = $repository->get($mappedMysqlWidgetB);

foreach ([
    new ExecutionContext(new Principal(null), 701, ExecutionChannel::Internal, 7),
    new ExecutionContext(new Principal(42), 701, ExecutionChannel::Rest, 7),
    new ExecutionContext(new Principal(43), 701, ExecutionChannel::Internal, 7),
] as $deniedContext) {
    platformPersistenceExpect(
        $mappedMysqlImporter->importMappedDraft(
            $deniedContext, ['untrusted' => true], 'bad-id', ['unsafe' => []], 'invalid',
        ) === ['status' => 'forbidden'],
        'synthetic fixture caller gate must reject BEFORE mapping malformed external payload',
    );
}
$badMappedSha = $mappedMysqlSnapshot;
$badMappedSha['sha256'] = str_repeat('0', 64);
platformPersistenceExpect(
    $mappedMysqlImporter->importMappedDraft(
        $draftContext, $badMappedSha, $mappedMysqlTarget, $mappingToPublished, 'mysql-mapped-draft',
    ) === ['status' => 'invalid_snapshot'],
    'modified source checksum fails before MySQL mapped Draft insert',
);
foreach ([
    [$sourceIdA => $draftWidgetId],
    [$sourceIdA => $draftWidgetId, $sourceIdB => $draftWidgetId],
    [$sourceIdA => $draftWidgetId, $sourceIdB => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc'],
] as $badMapping) {
    platformPersistenceExpect(
        $mappedMysqlImporter->importMappedDraft(
            $draftContext, $mappedMysqlSnapshot, $mappedMysqlTarget, $badMapping, 'mysql-mapped-draft',
        ) === ['status' => 'invalid_snapshot'],
        'incomplete/duplicate/unpublished target refs cannot write MySQL mapped Draft',
    );
}
$networkMappedSource = $mappedMysqlSnapshot;
$networkMappedSource['payload']['assignment'] = ['roles' => [], 'network_default' => true];
$networkMappedSource['sha256'] = hash('sha256', json_encode(
    $networkMappedSource['payload'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
));
platformPersistenceExpect(
    $mappedMysqlImporter->importMappedDraft(
        $draftContext, $networkMappedSource, $mappedMysqlTarget, $mappingToPublished, 'mysql-mapped-draft',
    ) === ['status' => 'forbidden'],
    'site capability cannot authorize mapped network-default preset import',
);
platformPersistenceExpect($repository->get($mappedMysqlTarget) === null, 'rejected mapped preflight must leave MySQL target empty');
platformPersistenceExpect(
    $mappedMysqlImporter->importMappedDraft(
        $draftContext, $mappedMysqlSnapshot, $mappedMysqlTarget, $mappingToPublished, 'mysql-mapped-draft',
    ) === ['status' => 'created_draft', 'definition_id' => $mappedMysqlTarget],
    'accepted synthetic Internal caller creates mapped Draft using atomic MySQL insert',
);
$mappedDraftMySQL = $repository->get($mappedMysqlTarget);
platformPersistenceExpect($mappedDraftMySQL instanceof Definition, 'mapped Draft exists in actual disposable MySQL');
platformPersistenceExpect($mappedDraftMySQL->status === DefinitionStatus::Draft, 'mapped MySQL preset cannot be Published');
platformPersistenceExpect($mappedDraftMySQL->revision === 1, 'mapped Draft revision must reset to one');
platformPersistenceExpect($mappedDraftMySQL->slug === 'mysql-mapped-draft', 'mapped Draft stored slug must be destination slug');
platformPersistenceExpect($mappedDraftMySQL->payload === ['preset' => [
    'label' => 'MySQL Cross-site Mapping',
    'widget_definition_ids' => [$mappedMysqlWidgetB, $draftWidgetId],
    'assignment' => ['roles' => ['administrator', 'editor'], 'network_default' => false],
]], 'mapped real MySQL Draft must preserve original widget order through target mapping, roles and label');
platformPersistenceExpect(
    $mappedMysqlImporter->importMappedDraft(
        $draftContext, $mappedMysqlSnapshot, $mappedMysqlTarget, $mappingToPublished, 'cannot-overwrite',
    ) === ['status' => 'id_conflict'],
    'mapped MySQL UUID uniqueness blocks second create from overwriting',
);
platformPersistenceExpect(
    $mappedMysqlImporter->importMappedDraft(
        $draftContext, $mappedMysqlSnapshot, $mappedMysqlSecondTarget, $mappingToPublished, 'mysql-mapped-draft',
    ) === ['status' => 'write_failed'],
    'distinct target UUID with duplicate type/slug fails atomic MySQL uniqueness',
);
platformPersistenceExpect($repository->get($mappedMysqlSecondTarget) === null, 'slug collision must leave no second mapped MySQL row');
platformPersistenceExpect($repository->get($mappedMysqlTarget) == $mappedDraftMySQL, 'conflicts cannot alter committed MySQL Draft');
platformPersistenceExpect($networkStore->get($mappedMysqlTarget) === null, 'site mapped Draft cannot touch network-scope MySQL rows');
platformPersistenceExpect(
    $site702MappedImporter->importMappedDraft(
        $draftOtherContext, $mappedMysqlSnapshot, $mappedMysqlTarget, $mappingToPublished, 'mysql-mapped-draft',
    ) === ['status' => 'created_draft', 'definition_id' => $mappedMysqlTarget],
    'another subsite may independently insert same mapped Draft id without cross-site overwrite',
);
platformPersistenceExpect($otherSite->get($mappedMysqlTarget)?->status === DefinitionStatus::Draft, 'other site mapped import remains Draft');
platformPersistenceExpect($repository->get($mappedMysqlTarget) == $mappedDraftMySQL, 'second site mapped create cannot change first site row');
platformPersistenceExpect($repository->get($draftWidgetId) == $originalPublishedMappedWidget, 'existing Published MySQL target widget unchanged');
platformPersistenceExpect($repository->get($mappedMysqlWidgetB) == $originalNewPublishedWidget, 'second Published MySQL target widget unchanged');

// RB-0118: verify the accepted persisted readback guard on actual disposable
// MySQL plus fake adapters that *acknowledge* a write without a matching row.
// Synthetic capability checker: real WP user authorization remains RB-0112/15.
platformPersistenceExpect(
    $mappedDraftMySQL->computedChecksum() === (new Definition(
        id: $mappedMysqlTarget,
        slug: 'mysql-mapped-draft',
        type: DashboardWidgetPresetDefinition::TYPE,
        schemaVersion: 1,
        ownerSurfaceId: 10,
        status: DefinitionStatus::Draft,
        payload: $mappedDraftMySQL->payload,
        revision: 1,
    ))->computedChecksum(),
    'real mapped MySQL Draft readback must preserve canonical payload digest',
);
$uncommittedReadbackId = '5e5e5e5e-5e5e-4e5e-8e5e-5e5e5e5e5e5e';
$uncommittedPayload = $draftSnapshot['payload'];
$uncommittedPayload['definition_id'] = $uncommittedReadbackId;
$uncommittedSnapshot = [
    'format' => 'wpessential-dashboard-preset',
    'version' => 1,
    'payload' => $uncommittedPayload,
    'sha256' => hash('sha256', json_encode(
        $uncommittedPayload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    )),
];
foreach (['ack_only', 'changed_row', 'read_error'] as $mode) {
    $ackAdapter = new class($repository, $mode) implements DefinitionCreateOnlyRepositoryInterface {
        private ?Definition $acknowledged = null;

        public function __construct(
            private readonly PersistentDefinitionRepository $actual,
            private readonly string $mode,
        ) {}

        public function create(Definition $definition): void
        {
            // Deliberately do NOT persist; this is a fake only for failure
            // verification, not a database adapter or production mutation.
            $this->acknowledged = $definition;
        }

        public function get(string $id): ?Definition
        {
            if ($this->acknowledged?->id === $id) {
                if ($this->mode === 'read_error') {
                    throw new RuntimeException('simulated post-create read error');
                }
                if ($this->mode === 'ack_only') {
                    return null;
                }
                $draft = $this->acknowledged;
                return new Definition(
                    id: $draft->id,
                    slug: 'wrong-stored-slug',
                    type: $draft->type,
                    schemaVersion: $draft->schemaVersion,
                    ownerSurfaceId: $draft->ownerSurfaceId,
                    status: $draft->status,
                    payload: $draft->payload,
                    revision: $draft->revision,
                );
            }
            return $this->actual->get($id);
        }

        public function save(Definition $definition): void
        {
            throw new RuntimeException('Fake adapter must never call save');
        }

        public function byType(string $type): array
        {
            return $this->actual->byType($type);
        }

        public function dependentsOf(string $id): array
        {
            return $this->actual->dependentsOf($id);
        }
    };
    $checkService = new DashboardWidgetPresetDraftImportService(
        $ackAdapter,
        new DashboardWidgetPresetImportPreflightService(
            $ackAdapter, new DashboardWidgetPresetCompiler($ackAdapter),
        ),
        $draftChecker,
    );
    platformPersistenceExpect(
        $checkService->importDraft(
            $draftContext, $uncommittedSnapshot, 'mysql-readback-probe',
        ) === ['status' => 'write_failed'],
        'adapter ' . $mode . ' cannot claim created_draft without persisted equivalent Draft',
    );
    platformPersistenceExpect(
        $repository->get($uncommittedReadbackId) === null,
        'failed fake acknowledgment must not create real disposable MySQL rows',
    );
}
platformPersistenceExpect(
    $otherSite->get($uncommittedReadbackId) === null
        && $networkStore->get($uncommittedReadbackId) === null,
    'fake readback failures must not leak across real MySQL site/network scopes',
);

// RB-0123: prove RB-0122's Published widget fingerprint race guard using
// *real disposable MySQL persistence*, not only an in-memory/mock database.
// The synthetic Internal caller is scoped evidence, NOT a live WP identity
// or native Dashboard preference write authorization.
$otherSiteWidgetBeforeDrift = $otherSite->get($draftWidgetId);
$networkWidgetBeforeDrift = $networkStore->get($draftWidgetId);
foreach ([
    ['mode' => 'revision_only', 'id' => '6e6e6e6e-6e6e-4e6e-8e6e-6e6e6e6e6e6e'],
    ['mode' => 'payload_slug', 'id' => '7e7e7e7e-7e7e-4e7e-8e7e-7e7e7e7e7e7e'],
] as $driftCase) {
    $candidate = $draftSnapshot;
    $candidate['payload']['definition_id'] = $driftCase['id'];
    $candidate['sha256'] = hash('sha256', json_encode(
        $candidate['payload'],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    ));
    $originalPublished = $repository->get($draftWidgetId);
    platformPersistenceExpect(
        $originalPublished instanceof Definition && $originalPublished->status === DefinitionStatus::Published,
        'real MySQL precondition: referenced target widget must still be Published',
    );

    $raceRepo = new class($repository, $driftCase['mode'], $draftWidgetId) implements DefinitionCreateOnlyRepositoryInterface {
        public int $createCalls = 0;

        public function __construct(
            private readonly PersistentDefinitionRepository $actual,
            private readonly string $mode,
            private readonly string $widgetId,
        ) {}

        public function create(Definition $draft): void
        {
            ++$this->createCalls;
            $this->actual->create($draft);

            // A distinct writer updates a real MySQL Published widget AFTER
            // the atomic Draft insert. Same id/type/status, advanced revision.
            $widget = $this->actual->get($this->widgetId);
            if (!$widget instanceof Definition) {
                throw new RuntimeException('Missing race fixture widget');
            }
            $this->actual->save(new Definition(
                id: $widget->id,
                slug: $this->mode === 'payload_slug' ? 'race-renamed-widget' : $widget->slug,
                type: $widget->type,
                schemaVersion: $widget->schemaVersion,
                ownerSurfaceId: $widget->ownerSurfaceId,
                status: DefinitionStatus::Published,
                payload: $this->mode === 'payload_slug' ? ['race' => 'changed'] : $widget->payload,
                revision: $widget->revision + 1,
                dependencies: $widget->dependencies,
            ));
        }

        public function save(Definition $definition): void
        {
            throw new RuntimeException('Draft importer may not update, retry or rollback');
        }

        public function get(string $id): ?Definition { return $this->actual->get($id); }
        public function byType(string $type): array { return $this->actual->byType($type); }
        public function dependentsOf(string $id): array { return $this->actual->dependentsOf($id); }
    };

    $raceImporter = new DashboardWidgetPresetDraftImportService(
        $raceRepo,
        new DashboardWidgetPresetImportPreflightService(
            $raceRepo, new DashboardWidgetPresetCompiler($raceRepo),
        ),
        $draftChecker,
    );
    $raceResult = $raceImporter->importDraft(
        $draftContext, $candidate, 'mysql-fingerprint-race-' . $driftCase['mode'],
    );
    platformPersistenceExpect(
        $raceResult === ['status' => 'write_failed'],
        'concurrent Published widget fingerprint drift must fail closed on real MySQL: '
            . $driftCase['mode'] . ', returned ' . json_encode($raceResult)
            . ', draft_status=' . ($repository->get($driftCase['id'])?->status->value ?? 'missing')
            . ', widget_revision=' . ($repository->get($draftWidgetId)?->revision ?? 'missing'),
    );
    platformPersistenceExpect(
        $raceRepo->createCalls === 1,
        'real MySQL race guard must not retry atomic Draft create',
    );
    $stored = $repository->get($driftCase['id']);
    $currentWidget = $repository->get($draftWidgetId);
    platformPersistenceExpect(
        $stored instanceof Definition && $stored->status === DefinitionStatus::Draft
            && $stored->revision === 1,
        'race-detected import leaves its one inserted MySQL Draft intact; never claims rollback',
    );
    platformPersistenceExpect(
        $currentWidget instanceof Definition
            && $currentWidget->status === DefinitionStatus::Published
            && $currentWidget->id === $originalPublished->id
            && $currentWidget->revision === $originalPublished->revision + 1,
        'concurrent actual MySQL Published widget edit retains ID/status but advances revision',
    );
    platformPersistenceExpect(
        $currentWidget->computedChecksum()
            !== $originalPublished->computedChecksum() || $currentWidget->revision !== $originalPublished->revision,
        'race fixture must observably change fingerprint',
    );
    platformPersistenceExpect(
        $otherSite->get($driftCase['id']) === null && $networkStore->get($driftCase['id']) === null,
        'failed-but-persisted Draft cannot write to other site/network scope',
    );
}
platformPersistenceExpect(
    $otherSite->get($draftWidgetId) == $otherSiteWidgetBeforeDrift
        && $networkStore->get($draftWidgetId) == $networkWidgetBeforeDrift,
    'real MySQL Published widget concurrency probe must not change other site/network scope',
);

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
