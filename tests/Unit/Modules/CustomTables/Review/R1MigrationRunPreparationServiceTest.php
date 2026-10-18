<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Review;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Migration\ProviderDdlPreviewCompiler;
use WPEssential\Modules\CustomTables\Migration\Review\MigrationReviewSnapshot;
use WPEssential\Modules\CustomTables\Migration\Review\MigrationReviewSnapshotStoreInterface;
use WPEssential\Modules\CustomTables\Migration\Review\R1MigrationRunPreparationService;
use WPEssential\Modules\CustomTables\Migration\Run\InMemoryMigrationRunRepository;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunTransitionService;
use WPEssential\Modules\CustomTables\Migration\TableSchemaDiffPlanner;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Modules\CustomTables\Schema\ObservedSchemaReaderInterface;
use WPEssential\Modules\CustomTables\Schema\ObservedTableSchema;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class R1MigrationRunPreparationServiceTest extends TestCase
{
    private const DEFINITION_ID = '78888888-8888-4888-8888-888888888888';

    public function testNewTablePreparesPlannedRunAndRecordsReviewSnapshot(): void
    {
        $runs = new InMemoryMigrationRunRepository();
        $snapshots = new InMemorySnapshotStore();

        $result = $this->service($runs, $snapshots, tableExists: false)->prepare(
            self::desired(),
            self::identity(),
            self::capabilities(),
        );

        self::assertTrue($result->prepared);
        self::assertSame([], $result->reasons);
        self::assertNotNull($result->run);
        self::assertNotNull($result->preview);
        self::assertNotNull($result->snapshot);

        $stored = $runs->get($result->run->id);
        self::assertNotNull($stored);
        self::assertSame(MigrationRunState::Planned, $stored->state);
        self::assertSame(self::DEFINITION_ID, $stored->targetDefinitionId);
        self::assertSame(2, $stored->targetRevision);

        $snapshot = $snapshots->snapshotFor($result->run->id);
        self::assertNotNull($snapshot);
        self::assertTrue($snapshot->matchesPreview($result->preview));
        self::assertSame($stored->planFingerprint, $snapshot->generation->planFingerprint);
        self::assertSame('mysql', $snapshot->generation->provider);
        self::assertSame('8.0.36', $snapshot->generation->providerVersion);
        self::assertNotSame([], $result->preview->statements);
    }

    public function testTableAlreadyMatchingIsRejectedWithoutCreatingARun(): void
    {
        $runs = new InMemoryMigrationRunRepository();
        $snapshots = new InMemorySnapshotStore();

        $result = $this->service($runs, $snapshots, tableExists: true)->prepare(
            self::desired(),
            self::identity(),
            self::capabilities(),
        );

        self::assertFalse($result->prepared);
        self::assertSame(['plan_empty'], $result->reasons);
        self::assertSame([], $snapshots->records);
    }

    public function testIdentityForAnotherDefinitionIsRejected(): void
    {
        $snapshots = new InMemorySnapshotStore();

        $result = $this->service(new InMemoryMigrationRunRepository(), $snapshots, tableExists: false)->prepare(
            self::desired(),
            Ct1ManagedTableIdentity::derive('79999999-9999-4999-8999-999999999999', 'settings', 'wp_'),
            self::capabilities(),
        );

        self::assertFalse($result->prepared);
        self::assertSame(['identity_mismatch'], $result->reasons);
        self::assertSame([], $snapshots->records);
    }

    public function testSnapshotFailureCancelsTheRunBeforeAnyMutation(): void
    {
        $runs = new InMemoryMigrationRunRepository();
        $snapshots = new InMemorySnapshotStore(fail: true);
        $database = new PreparationDatabase();

        $result = (new R1MigrationRunPreparationService(
            self::reader(false),
            new TableSchemaDiffPlanner(),
            new ProviderDdlPreviewCompiler(),
            $database,
            $runs,
            new MigrationRunTransitionService($runs),
            $snapshots,
        ))->prepare(self::desired(), self::identity(), self::capabilities());

        self::assertFalse($result->prepared);
        self::assertSame(['snapshot_record_failed'], $result->reasons);
        self::assertSame([], $database->queries);

        self::assertNotNull($snapshots->attempted);
        $run = $runs->get($snapshots->attempted);
        self::assertNotNull($run);
        self::assertSame(MigrationRunState::CancelledBeforeMutation, $run->state);
    }

    private function service(InMemoryMigrationRunRepository $runs, InMemorySnapshotStore $snapshots, bool $tableExists): R1MigrationRunPreparationService
    {
        return new R1MigrationRunPreparationService(
            self::reader($tableExists),
            new TableSchemaDiffPlanner(),
            new ProviderDdlPreviewCompiler(),
            new PreparationDatabase(),
            $runs,
            new MigrationRunTransitionService($runs),
            $snapshots,
        );
    }

    private static function reader(bool $exists): ObservedSchemaReaderInterface
    {
        $desired = self::desired();

        return new class ($desired, $exists) implements ObservedSchemaReaderInterface {
            public function __construct(private TableSchemaDescriptor $desired, private bool $exists)
            {
            }

            public function observe(TableSchemaDescriptor $descriptor): ObservedTableSchema
            {
                return $this->exists
                    ? new ObservedTableSchema('settings', true, $this->desired->columns, $this->desired->primaryKey, $this->desired->indexes, 'inherit', [], str_repeat('e', 64))
                    : new ObservedTableSchema('settings', false, [], [], [], 'unknown', [], str_repeat('f', 64));
            }
        };
    }

    private static function desired(): TableSchemaDescriptor
    {
        return (new TableDefinitionCompiler())->compile(new Definition(
            id: self::DEFINITION_ID,
            slug: 'settings',
            type: 'table',
            schemaVersion: 1,
            ownerSurfaceId: 7,
            status: DefinitionStatus::Published,
            payload: [
                'key' => 'settings',
                'label' => 'Settings',
                'desired_schema_version' => 1,
                'columns' => [
                    ['key' => 'id', 'type' => 'bigint', 'nullable' => false, 'auto_increment' => true],
                    ['key' => 'value', 'type' => 'varchar', 'length' => 64, 'nullable' => true],
                ],
                'primary_key' => ['id'],
                'indexes' => [],
                'data_classification' => 'internal',
            ],
            revision: 2,
        ));
    }

    private static function identity(): Ct1ManagedTableIdentity
    {
        return Ct1ManagedTableIdentity::derive(self::DEFINITION_ID, 'settings', 'wp_');
    }

    private static function capabilities(): ProviderCapabilityProfile
    {
        return ProviderCapabilityProfile::fromTrustedServerInfo('8.0.36');
    }
}

final class InMemorySnapshotStore implements MigrationReviewSnapshotStoreInterface
{
    /** @var array<string, MigrationReviewSnapshot> */
    public array $records = [];
    public ?string $attempted = null;

    public function __construct(private bool $fail = false)
    {
    }

    public function record(MigrationReviewSnapshot $snapshot): MigrationReviewSnapshot
    {
        $this->attempted = $snapshot->runId;
        if ($this->fail) {
            throw new RuntimeException('store unavailable');
        }
        if (isset($this->records[$snapshot->runId])) {
            throw new RuntimeException('already recorded');
        }

        return $this->records[$snapshot->runId] = $snapshot;
    }

    public function snapshotFor(string $runId): ?MigrationReviewSnapshot
    {
        return $this->records[strtolower($runId)] ?? null;
    }
}

final class PreparationDatabase implements DatabaseAdapterInterface
{
    /** @var list<string> */
    public array $queries = [];

    public function networkTablePrefix(): string { return 'wp_'; }
    public function charsetCollate(): string { return ''; }
    public function prepare(string $query, mixed ...$args): string { return $query; }
    public function getRow(string $query): ?array { return null; }
    public function getResults(string $query): array { return []; }
    public function getVar(string $query): mixed { return null; }
    public function query(string $query): int|bool
    {
        $this->queries[] = $query;
        return 0;
    }
    public function insert(string $table, array $data, array $formats = []): bool { return true; }
    public function lastError(): string { return ''; }
    public function beginTransaction(): void {}
    public function commit(): void {}
    public function rollBack(): void {}
}
