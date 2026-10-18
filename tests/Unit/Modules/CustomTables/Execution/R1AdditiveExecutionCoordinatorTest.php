<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Execution;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\Execution\R1AdditiveExecutionCoordinator;
use WPEssential\Modules\CustomTables\Migration\Execution\R1AdditiveExecutionRequest;
use WPEssential\Modules\CustomTables\Migration\MigrationOperation;
use WPEssential\Modules\CustomTables\Migration\MigrationPlan;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
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
use WPEssential\Modules\CustomTables\Schema\ObservedSchemaReaderInterface;
use WPEssential\Modules\CustomTables\Schema\ObservedTableSchema;
use WPEssential\Modules\CustomTables\Schema\ObservedTableSchemaNormalizer;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class R1AdditiveExecutionCoordinatorTest extends TestCase
{
    private const DEFINITION_ID = '78888888-8888-4888-8888-888888888888';
    private const RUN_ID = '11111111-1111-4111-8111-111111111111';

    public function testReadyR1CreateTablePlanExecutesRecompiledStatementsAndApplies(): void
    {
        $fixture = new CoordinatorFixture();

        $result = $fixture->coordinator()->execute($fixture->request());

        self::assertTrue($result->applied, implode(',', $result->reasons));
        self::assertSame(MigrationRunState::Applied, $fixture->runs->get(self::RUN_ID)?->state);
        self::assertSame(array_map(static fn ($s): string => $s->sql, $fixture->preview()->statements), $fixture->database->queries);
        self::assertStringStartsWith('CREATE TABLE', $fixture->database->queries[0]);
    }

    public function testMissingConfirmationRejectsBeforeAnyMutation(): void
    {
        $fixture = new CoordinatorFixture(confirmed: false);

        $result = $fixture->coordinator()->execute($fixture->request());

        self::assertFalse($result->applied);
        self::assertFalse($result->mutationAttempted);
        self::assertSame([], $fixture->database->queries);
        self::assertSame(MigrationRunState::Revalidating, $fixture->runs->get(self::RUN_ID)?->state);
    }

    public function testNonAdditiveOrHighRiskPlanIsRejectedBeforeReadiness(): void
    {
        $fixture = new CoordinatorFixture();
        $plan = $fixture->plan();
        $risky = new MigrationPlan(
            id: $plan->id,
            tableKey: $plan->tableKey,
            sourceObservedFingerprint: $plan->sourceObservedFingerprint,
            targetDefinitionId: $plan->targetDefinitionId,
            targetRevision: $plan->targetRevision,
            targetSchemaVersion: $plan->targetSchemaVersion,
            targetCompatibilityFingerprint: $plan->targetCompatibilityFingerprint,
            operations: [new MigrationOperation('drop_column', 'settings.value', MigrationRisk::R4, recoveryRequired: true)],
            findings: [],
            risk: MigrationRisk::R4,
            blocked: false,
            recoveryRequired: true,
            fingerprint: $plan->fingerprint,
        );

        $result = $fixture->coordinator()->execute($fixture->request(plan: $risky));

        self::assertFalse($result->mutationAttempted);
        self::assertContains('operation_not_additive', $result->reasons);
        self::assertContains('plan_risk_above_r1', $result->reasons);
        self::assertSame([], $fixture->database->queries);
    }

    public function testRunNotInRevalidatingIsRejected(): void
    {
        $fixture = new CoordinatorFixture(state: MigrationRunState::Approved);

        $result = $fixture->coordinator()->execute($fixture->request());

        self::assertContains('run_not_revalidating', $result->reasons);
        self::assertSame([], $fixture->database->queries);
    }

    public function testReviewedPreviewThatDiffersFromRecompiledPlanIsRejected(): void
    {
        $fixture = new CoordinatorFixture();
        $other = (new ProviderDdlPreviewCompiler())->compile(
            $fixture->plan(),
            $fixture->desired(),
            Ct1ManagedTableIdentity::derive(self::DEFINITION_ID, 'settings', 'wpx_'),
            $fixture->capabilities(),
        );

        $result = $fixture->coordinator()->execute($fixture->request(preview: $other));

        self::assertSame(['reviewed_preview_mismatch'], $result->reasons);
        self::assertSame([], $fixture->database->queries);
    }

    public function testFailingStatementMarksRunFailedRecoverable(): void
    {
        $fixture = new CoordinatorFixture();
        $fixture->database->fail = true;

        $result = $fixture->coordinator()->execute($fixture->request());

        self::assertFalse($result->applied);
        self::assertTrue($result->mutationAttempted);
        self::assertSame(['statement_failed'], $result->reasons);
        self::assertSame(MigrationRunState::FailedRecoverable, $fixture->runs->get(self::RUN_ID)?->state);
    }

    public function testPostExecutionVerificationFailureNeverReportsSuccess(): void
    {
        $fixture = new CoordinatorFixture(tableAppears: false);

        $result = $fixture->coordinator()->execute($fixture->request());

        self::assertFalse($result->applied);
        self::assertSame(['post_execution_verification_failed'], $result->reasons);
        self::assertSame(MigrationRunState::FailedRecoverable, $fixture->runs->get(self::RUN_ID)?->state);
    }
}

final class CoordinatorFixture
{
    private const DEFINITION_ID = '78888888-8888-4888-8888-888888888888';
    private const RUN_ID = '11111111-1111-4111-8111-111111111111';

    public InMemoryMigrationRunRepository $runs;
    public RecordingDatabase $database;
    private MigrationRun $run;

    public function __construct(
        private bool $confirmed = true,
        MigrationRunState $state = MigrationRunState::Revalidating,
        private bool $tableAppears = true,
    ) {
        $this->runs = new InMemoryMigrationRunRepository();
        $this->database = new RecordingDatabase();
        $plan = $this->plan();
        $this->run = new MigrationRun(
            id: self::RUN_ID,
            planFingerprint: $plan->fingerprint,
            tableKey: 'settings',
            targetDefinitionId: self::DEFINITION_ID,
            targetRevision: $plan->targetRevision,
            targetSchemaVersion: $plan->targetSchemaVersion,
            state: $state,
            stateRevision: 4,
        );
        $this->runs->create($this->run);
    }

    public function desired(): TableSchemaDescriptor
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

    public function plan(): MigrationPlan
    {
        return (new TableSchemaDiffPlanner())->plan(
            $this->desired(),
            (new ObservedTableSchemaNormalizer())->normalize('settings', ['exists' => false]),
        );
    }

    public function capabilities(): ProviderCapabilityProfile
    {
        return ProviderCapabilityProfile::fromTrustedServerInfo('8.0.36');
    }

    public function preview(): \WPEssential\Modules\CustomTables\Migration\ProviderMigrationPreview
    {
        return (new ProviderDdlPreviewCompiler())->compile(
            $this->plan(),
            $this->desired(),
            Ct1ManagedTableIdentity::derive(self::DEFINITION_ID, 'settings', 'wp_'),
            $this->capabilities(),
        );
    }

    public function request(?MigrationPlan $plan = null, ?\WPEssential\Modules\CustomTables\Migration\ProviderMigrationPreview $preview = null): R1AdditiveExecutionRequest
    {
        $generation = new MigrationGenerationStamp(
            planFingerprint: $this->run->planFingerprint,
            sourceObservedFingerprint: $this->plan()->sourceObservedFingerprint,
            targetDefinitionId: self::DEFINITION_ID,
            targetRevision: $this->run->targetRevision,
            targetSchemaVersion: $this->run->targetSchemaVersion,
            provider: 'mysql',
            providerVersion: '8.0.36',
        );

        return new R1AdditiveExecutionRequest(
            run: $this->run,
            plan: $plan ?? $this->plan(),
            desired: $this->desired(),
            identity: Ct1ManagedTableIdentity::derive(self::DEFINITION_ID, 'settings', 'wp_'),
            capabilities: $this->capabilities(),
            reviewedPreview: $preview ?? $this->preview(),
            requirements: [],
            reviewedGeneration: $generation,
            currentGeneration: $generation,
            recoveryRequirement: new RecoveryRequirement(RecoveryClass::TriviallyReversible),
            recoveryArtifactId: 'artifact-1',
            maxRecoveryEvidenceAgeHours: 4,
            context: new ExecutionContext(new Principal(7), 1, networkId: 1),
        );
    }

    public function coordinator(): R1AdditiveExecutionCoordinator
    {
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
            runs: $this->runs,
            metadataFacts: $facts,
            recoveryVerification: new StaticRecoveryVerificationProvider([
                'artifact-1' => new BoundRecoveryEvidence(
                    new RecoveryEvidence($this->run->planFingerprint, RecoveryClass::TriviallyReversible),
                    'mysql',
                    '8.0.36',
                    0,
                ),
            ]),
            confirmations: new StaticMigrationExecutionConfirmationProvider($this->confirmed ? [
                new BoundMigrationExecutionConfirmation($this->run->id, $this->run->planFingerprint, $this->run->stateRevision, ExecutionActorType::User, 7),
            ] : []),
            authorizationPolicy: new MigrationExecutionAuthorizationPolicyAdapter(new PolicyEngine($checker)),
            networkId: 1,
            siteId: 1,
        );

        $desired = $this->desired();
        $appears = $this->tableAppears;
        $reader = new class ($desired, $appears) implements ObservedSchemaReaderInterface {
            public function __construct(private TableSchemaDescriptor $desired, private bool $appears)
            {
            }

            public function observe(TableSchemaDescriptor $descriptor): ObservedTableSchema
            {
                return $this->appears
                    ? new ObservedTableSchema('settings', true, $this->desired->columns, $this->desired->primaryKey, $this->desired->indexes, 'inherit', [], str_repeat('f', 64))
                    : new ObservedTableSchema('settings', false, [], [], [], 'unknown', [], str_repeat('f', 64));
            }
        };

        return new R1AdditiveExecutionCoordinator(
            $readiness,
            new MigrationRunTransitionService($this->runs),
            $this->database,
            $reader,
            new ProviderDdlPreviewCompiler(),
        );
    }
}

final class RecordingDatabase implements DatabaseAdapterInterface
{
    /** @var list<string> */
    public array $queries = [];
    public bool $fail = false;

    public function networkTablePrefix(): string { return 'wp_'; }
    public function charsetCollate(): string { return ''; }
    public function prepare(string $query, mixed ...$args): string { return $query; }
    public function getRow(string $query): ?array { return null; }
    public function getResults(string $query): array { return []; }
    public function getVar(string $query): mixed { return null; }
    public function query(string $query): int|bool
    {
        $this->queries[] = $query;
        return $this->fail ? false : 0;
    }
    public function insert(string $table, array $data, array $formats = []): bool { return true; }
    public function lastError(): string { return ''; }
    public function beginTransaction(): void {}
    public function commit(): void {}
    public function rollBack(): void {}
}
