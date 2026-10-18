<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Review;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Migration\ProviderDdlPreviewCompiler;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryClass;
use WPEssential\Modules\CustomTables\Migration\Review\MigrationReviewSnapshot;
use WPEssential\Modules\CustomTables\Migration\Review\MigrationReviewSnapshotStoreInterface;
use WPEssential\Modules\CustomTables\Migration\Review\R1ExecutionRequestResolver;
use WPEssential\Modules\CustomTables\Migration\Review\WordPressProviderCapabilityProfileResolver;
use WPEssential\Modules\CustomTables\Migration\Run\InMemoryMigrationRunRepository;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunTransitionService;
use WPEssential\Modules\CustomTables\Migration\TableSchemaDiffPlanner;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Modules\CustomTables\Schema\ObservedSchemaReaderInterface;
use WPEssential\Modules\CustomTables\Schema\ObservedTableSchema;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class R1ExecutionRequestResolverTest extends TestCase
{
    private const DEFINITION_ID = '78888888-8888-4888-8888-888888888888';
    private const RUN_ID = '11111111-1111-4111-8111-111111111111';

    public function testRevalidatingRunResolvesFromStoredAndObservedFactsOnly(): void
    {
        $fixture = new ResolverFixture();

        $resolution = $fixture->resolver()->resolve(self::RUN_ID, new ExecutionContext(new Principal(7), 1), 'backup-artifact');

        self::assertSame([], $resolution->reasons);
        self::assertFalse($resolution->superseded);
        $request = $resolution->request;
        self::assertNotNull($request);
        self::assertSame([], $request->requirements);
        self::assertSame(RecoveryClass::VerifiedBackupRequired, $request->recoveryRequirement->class);
        self::assertSame('backup-artifact', $request->recoveryArtifactId);
        self::assertSame($fixture->snapshot->generation->canonical(), $request->reviewedGeneration->canonical());
        self::assertSame($request->reviewedGeneration->canonical(), $request->currentGeneration->canonical());
        self::assertTrue($fixture->snapshot->matchesPreview($request->reviewedPreview));
    }

    public function testNewerDefinitionRevisionSupersedesTheRun(): void
    {
        $fixture = new ResolverFixture(definitionRevision: 3);

        $resolution = $fixture->resolver()->resolve(self::RUN_ID, new ExecutionContext(new Principal(7), 1), 'backup-artifact');

        self::assertTrue($resolution->superseded);
        self::assertNull($resolution->request);
        self::assertSame(MigrationRunState::Superseded, $fixture->runs->get(self::RUN_ID)?->state);
    }

    public function testTableChangedSinceReviewIsRejected(): void
    {
        $fixture = new ResolverFixture(tableExistsNow: true);

        $resolution = $fixture->resolver()->resolve(self::RUN_ID, new ExecutionContext(new Principal(7), 1), 'backup-artifact');

        self::assertNull($resolution->request);
        self::assertSame(['reviewed_plan_changed'], $resolution->reasons);
    }

    public function testRunOutsideRevalidatingIsRejected(): void
    {
        $fixture = new ResolverFixture(state: MigrationRunState::Approved);

        self::assertSame(['run_not_revalidating'], $fixture->resolver()->resolve(self::RUN_ID, new ExecutionContext(new Principal(7), 1), 'backup-artifact')->reasons);
    }

    public function testMissingSnapshotOrRunIsRejected(): void
    {
        $fixture = new ResolverFixture(withSnapshot: false);
        $context = new ExecutionContext(new Principal(7), 1);

        self::assertSame(['review_snapshot_missing'], $fixture->resolver()->resolve(self::RUN_ID, $context, 'backup-artifact')->reasons);
        self::assertSame(['run_not_found'], $fixture->resolver()->resolve('22222222-2222-4222-8222-222222222222', $context, 'backup-artifact')->reasons);
    }
}

final class ResolverFixture
{
    private const DEFINITION_ID = '78888888-8888-4888-8888-888888888888';
    private const RUN_ID = '11111111-1111-4111-8111-111111111111';

    public InMemoryMigrationRunRepository $runs;
    public MigrationReviewSnapshot $snapshot;
    private ResolverDefinitions $definitions;
    private ResolverSnapshots $snapshots;

    public function __construct(
        int $definitionRevision = 2,
        private bool $tableExistsNow = false,
        MigrationRunState $state = MigrationRunState::Revalidating,
        bool $withSnapshot = true,
    ) {
        $reviewed = self::definition(2);
        $desired = (new TableDefinitionCompiler())->compile($reviewed);
        $capabilities = ProviderCapabilityProfile::fromTrustedServerInfo('8.0.36');
        $plan = (new TableSchemaDiffPlanner())->plan($desired, self::observed($desired, false));
        $preview = (new ProviderDdlPreviewCompiler())->compile(
            $plan,
            $desired,
            Ct1ManagedTableIdentity::derive(self::DEFINITION_ID, 'settings', 'wp_'),
            $capabilities,
            '',
        );

        $this->runs = new InMemoryMigrationRunRepository();
        $this->runs->create(new MigrationRun(
            id: self::RUN_ID,
            planFingerprint: $plan->fingerprint,
            tableKey: 'settings',
            targetDefinitionId: self::DEFINITION_ID,
            targetRevision: 2,
            targetSchemaVersion: 1,
            state: $state,
            stateRevision: 4,
        ));
        $this->snapshot = new MigrationReviewSnapshot(
            self::RUN_ID,
            MigrationReviewSnapshot::previewHash($preview),
            new MigrationGenerationStamp($plan->fingerprint, $plan->sourceObservedFingerprint, self::DEFINITION_ID, 2, 1, 'mysql', '8.0.36'),
        );
        $this->snapshots = new ResolverSnapshots($withSnapshot ? $this->snapshot : null);
        $this->definitions = new ResolverDefinitions(self::definition($definitionRevision));
    }

    public function resolver(): R1ExecutionRequestResolver
    {
        $exists = $this->tableExistsNow;

        return new R1ExecutionRequestResolver(
            runs: $this->runs,
            snapshots: $this->snapshots,
            definitions: $this->definitions,
            schema: new class ($exists) implements ObservedSchemaReaderInterface {
                public function __construct(private bool $exists)
                {
                }

                public function observe(TableSchemaDescriptor $descriptor): ObservedTableSchema
                {
                    return ResolverFixture::observed($descriptor, $this->exists);
                }
            },
            profiles: new WordPressProviderCapabilityProfileResolver(new class {
                public function db_server_info(): string
                {
                    return '8.0.36';
                }
            }),
            database: new PreparationDatabase(),
            transitions: new MigrationRunTransitionService($this->runs),
            sitePrefix: 'wp_',
            maxRecoveryEvidenceAgeHours: 24,
        );
    }

    public static function observed(TableSchemaDescriptor $desired, bool $exists): ObservedTableSchema
    {
        return $exists
            ? new ObservedTableSchema('settings', true, $desired->columns, $desired->primaryKey, $desired->indexes, 'inherit', [], str_repeat('e', 64))
            : new ObservedTableSchema('settings', false, [], [], [], 'unknown', [], str_repeat('f', 64));
    }

    private static function definition(int $revision): Definition
    {
        return new Definition(
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
            revision: $revision,
        );
    }
}

final class ResolverDefinitions implements DefinitionRepositoryInterface
{
    public function __construct(private Definition $definition)
    {
    }

    public function save(Definition $definition): void
    {
        throw new RuntimeException('not used');
    }

    public function get(string $id): ?Definition
    {
        return $id === $this->definition->id ? $this->definition : null;
    }

    public function byType(string $type): array
    {
        return [];
    }

    public function dependentsOf(string $id): array
    {
        return [];
    }
}

final class ResolverSnapshots implements MigrationReviewSnapshotStoreInterface
{
    public function __construct(private ?MigrationReviewSnapshot $snapshot)
    {
    }

    public function record(MigrationReviewSnapshot $snapshot): MigrationReviewSnapshot
    {
        throw new RuntimeException('not used');
    }

    public function snapshotFor(string $runId): ?MigrationReviewSnapshot
    {
        return $this->snapshot !== null && $this->snapshot->runId === strtolower($runId) ? $this->snapshot : null;
    }
}
