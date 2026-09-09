<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Readiness\Composition;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\CustomTables\Definition\TableColumnDescriptor;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionKind;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionRequirement;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\MetadataPreconditionFacts;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\MetadataPreconditionFactsProviderInterface;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Migration\ProviderMigrationPreview;
use WPEssential\Modules\CustomTables\Migration\ProviderStatementPreview;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\ExecutionActorType;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\Policy\MigrationExecutionAuthorizationPolicyAdapter;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\BoundMigrationExecutionConfirmation;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\RuntimeCompositionReadinessPackage;
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
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;

final class RuntimeCompositionReadinessServiceTest extends TestCase
{
    public function testCompositionCanBeReadyWhileExecutionRemainsHardDisabled(): void
    {
        $run = self::run();
        $package = self::evaluate(
            self::service($run, self::confirmation($run)),
            $run,
        );

        self::assertTrue($package->compositionReady);
        self::assertSame([], $package->reasons);
        self::assertFalse($package->executionAllowed);
        self::assertFalse($package->canonical()['execution_allowed']);
    }

    public function testStaleConfirmationFailsClosedAgainstRunRevision(): void
    {
        $run = self::run();
        $confirmation = new BoundMigrationExecutionConfirmation(
            $run->id,
            $run->planFingerprint,
            $run->stateRevision - 1,
            ExecutionActorType::User,
            7,
        );

        $package = self::evaluate(self::service($run, $confirmation), $run);

        self::assertBlocked($package, 'confirmation_stale');
        self::assertContains('authorization_confirmation_missing', $package->reasons);
    }

    public function testMissingConfirmationFailsClosed(): void
    {
        $run = self::run();
        $package = self::evaluate(self::service($run), $run);

        self::assertBlocked($package, 'confirmation_missing');
        self::assertContains('authorization_confirmation_missing', $package->reasons);
    }

    public function testConfirmationActorMismatchFailsClosed(): void
    {
        $run = self::run();
        $confirmation = new BoundMigrationExecutionConfirmation(
            $run->id,
            $run->planFingerprint,
            $run->stateRevision,
            ExecutionActorType::User,
            8,
        );

        $package = self::evaluate(self::service($run, $confirmation), $run);

        self::assertBlocked($package, 'confirmation_actor_mismatch');
        self::assertContains('authorization_confirmation_missing', $package->reasons);
    }

    public function testCapabilityDenialFailsClosed(): void
    {
        $run = self::run();
        $package = self::evaluate(
            self::service($run, self::confirmation($run), capabilityAllowed: false),
            $run,
        );

        self::assertBlocked($package, 'authorization_capability_denied');
    }

    public function testRowScanningPreconditionIsNotAllowlisted(): void
    {
        $run = self::run();
        $package = self::evaluate(
            self::service($run, self::confirmation($run)),
            $run,
            requirements: [new PreconditionRequirement(
                'row_count',
                PreconditionKind::RowCountRange,
                'orders',
                ['min' => 0, 'max' => 10],
            )],
        );

        self::assertBlocked($package, 'precondition_kind_not_allowlisted');
        self::assertContains('readiness_preconditions_not_satisfied', $package->reasons);
    }

    public function testMetadataFactsProviderFailureFailsClosed(): void
    {
        $run = self::run();
        $metadata = new FixedMetadataFactsProvider(new MetadataPreconditionFacts(true), fail: true);
        $package = self::evaluate(
            self::service($run, self::confirmation($run), metadataFacts: $metadata),
            $run,
        );

        self::assertBlocked($package, 'metadata_facts_unavailable');
        self::assertContains('readiness_preconditions_not_satisfied', $package->reasons);
    }

    public function testDescriptorRunMismatchFailsClosedBeforeTrustedMetadataLookup(): void
    {
        $run = self::run();
        $descriptor = self::descriptor($run, revision: $run->targetRevision + 1);
        $metadata = new FixedMetadataFactsProvider(new MetadataPreconditionFacts(true));
        $package = self::evaluate(
            self::service($run, self::confirmation($run), metadataFacts: $metadata),
            $run,
            descriptor: $descriptor,
        );

        self::assertBlocked($package, 'descriptor_run_mismatch');
        self::assertSame(0, $metadata->calls);
    }

    public function testNetworkScopeMismatchFailsClosed(): void
    {
        $run = self::run();
        $package = self::evaluate(
            self::service($run, self::confirmation($run)),
            $run,
            context: new ExecutionContext(new Principal(7), 1, networkId: 2),
        );

        self::assertBlocked($package, 'network_scope_mismatch');
    }

    public function testSiteScopeMismatchFailsClosed(): void
    {
        $run = self::run();
        $package = self::evaluate(
            self::service($run, self::confirmation($run)),
            $run,
            context: new ExecutionContext(new Principal(7), 2, networkId: 1),
        );

        self::assertBlocked($package, 'site_scope_mismatch');
    }

    public function testMissingRecoveryEvidenceFailsClosed(): void
    {
        $run = self::run();
        $package = self::evaluate(
            self::service($run, self::confirmation($run), recoveryAvailable: false),
            $run,
        );

        self::assertBlocked($package, 'recovery_binding_missing_evidence');
        self::assertContains('recovery_evidence_missing', $package->reasons);
        self::assertContains('readiness_incomplete', $package->reasons);
    }

    public function testR3RiskRemainsUnsupportedEvenWithOtherwiseValidEvidence(): void
    {
        $run = self::run();
        $package = self::evaluate(
            self::service($run, self::confirmation($run)),
            $run,
            risk: MigrationRisk::R3,
        );

        self::assertBlocked($package, 'authorization_unsupported_risk');
    }

    public function testCurrentGenerationMismatchFailsClosed(): void
    {
        $run = self::run();
        $current = new MigrationGenerationStamp(
            planFingerprint: $run->planFingerprint,
            sourceObservedFingerprint: str_repeat('d', 64),
            targetDefinitionId: $run->targetDefinitionId,
            targetRevision: $run->targetRevision + 1,
            targetSchemaVersion: $run->targetSchemaVersion,
            provider: 'mysql',
            providerVersion: '8.0.36',
        );
        $package = self::evaluate(
            self::service($run, self::confirmation($run)),
            $run,
            currentGeneration: $current,
        );

        self::assertBlocked($package, 'current_generation_run_mismatch');
        self::assertContains('readiness_revalidation_failed', $package->reasons);
    }

    /** @param list<PreconditionRequirement>|null $requirements */
    private static function evaluate(
        RuntimeCompositionReadinessService $service,
        MigrationRun $run,
        ?TableSchemaDescriptor $descriptor = null,
        ?ExecutionContext $context = null,
        MigrationRisk $risk = MigrationRisk::R1,
        ?array $requirements = null,
        ?MigrationGenerationStamp $currentGeneration = null,
    ): RuntimeCompositionReadinessPackage {
        return $service->evaluate(
            run: $run,
            requirements: $requirements ?? [
                new PreconditionRequirement('table_exists', PreconditionKind::TableExists, 'orders'),
            ],
            descriptor: $descriptor ?? self::descriptor($run),
            reviewedGeneration: self::generation($run),
            currentGeneration: $currentGeneration ?? self::generation($run),
            recoveryRequirement: new RecoveryRequirement(RecoveryClass::TriviallyReversible),
            recoveryArtifactId: 'artifact-1',
            maxRecoveryEvidenceAgeHours: 4,
            preview: self::preview($run),
            context: $context ?? self::context(),
            risk: $risk,
        );
    }

    private static function service(
        MigrationRun $run,
        ?BoundMigrationExecutionConfirmation $confirmation = null,
        bool $capabilityAllowed = true,
        bool $recoveryAvailable = true,
        ?MetadataPreconditionFactsProviderInterface $metadataFacts = null,
    ): RuntimeCompositionReadinessService {
        $runs = new InMemoryMigrationRunRepository();
        $runs->create($run);

        $recovery = new BoundRecoveryEvidence(
            new RecoveryEvidence($run->planFingerprint, RecoveryClass::TriviallyReversible),
            'mysql',
            '8.0.36',
            0,
        );
        $recoveryProvider = new StaticRecoveryVerificationProvider(
            $recoveryAvailable ? ['artifact-1' => $recovery] : [],
        );
        $confirmationProvider = new StaticMigrationExecutionConfirmationProvider(
            $confirmation instanceof BoundMigrationExecutionConfirmation ? [$confirmation] : [],
        );
        $checker = new class($capabilityAllowed) implements CapabilityCheckerInterface {
            public function __construct(private bool $allowed)
            {
            }

            public function can(ExecutionContext $context, string $capability): bool
            {
                return $this->allowed
                    && $context->principal->userId === 7
                    && $capability === 'manage_options';
            }
        };

        return new RuntimeCompositionReadinessService(
            runs: $runs,
            metadataFacts: $metadataFacts ?? new FixedMetadataFactsProvider(new MetadataPreconditionFacts(true)),
            recoveryVerification: $recoveryProvider,
            confirmations: $confirmationProvider,
            authorizationPolicy: new MigrationExecutionAuthorizationPolicyAdapter(new PolicyEngine($checker)),
            networkId: 1,
            siteId: 1,
        );
    }

    private static function confirmation(MigrationRun $run): BoundMigrationExecutionConfirmation
    {
        return new BoundMigrationExecutionConfirmation(
            $run->id,
            $run->planFingerprint,
            $run->stateRevision,
            ExecutionActorType::User,
            7,
        );
    }

    private static function run(): MigrationRun
    {
        return new MigrationRun(
            id: '11111111-1111-4111-8111-111111111111',
            planFingerprint: str_repeat('a', 64),
            tableKey: 'orders',
            targetDefinitionId: '22222222-2222-4222-8222-222222222222',
            targetRevision: 3,
            targetSchemaVersion: 2,
            state: MigrationRunState::Revalidating,
            stateRevision: 4,
        );
    }

    private static function descriptor(MigrationRun $run, ?int $revision = null): TableSchemaDescriptor
    {
        return new TableSchemaDescriptor(
            definitionId: $run->targetDefinitionId,
            revision: $revision ?? $run->targetRevision,
            tableKey: $run->tableKey,
            label: 'Orders',
            storageMode: 'managed',
            scope: 'site',
            desiredSchemaVersion: $run->targetSchemaVersion,
            columns: [new TableColumnDescriptor(
                key: 'id',
                type: 'bigint',
                nullable: false,
                hasDefault: false,
                defaultValue: null,
                autoIncrement: true,
            )],
            primaryKey: ['id'],
            indexes: [],
            charsetCollation: 'inherit',
            dataClassification: 'internal',
            compatibilityFingerprint: str_repeat('c', 64),
        );
    }

    private static function generation(MigrationRun $run): MigrationGenerationStamp
    {
        return new MigrationGenerationStamp(
            planFingerprint: $run->planFingerprint,
            sourceObservedFingerprint: str_repeat('b', 64),
            targetDefinitionId: $run->targetDefinitionId,
            targetRevision: $run->targetRevision,
            targetSchemaVersion: $run->targetSchemaVersion,
            provider: 'mysql',
            providerVersion: '8.0.36',
        );
    }

    private static function preview(MigrationRun $run): ProviderMigrationPreview
    {
        $capabilities = new ProviderCapabilityProfile(
            provider: 'mysql',
            version: '8.0.36',
            instantAddColumnCandidate: true,
            instantDefaultChangeCandidate: true,
            inplaceVarcharWideningCandidate: true,
        );
        $statement = ProviderStatementPreview::create(
            operationType: 'add_column',
            target: 'orders.total',
            sql: 'ALTER TABLE `wp_orders` ADD COLUMN `total` BIGINT',
            algorithmClassification: 'instant_candidate',
            lockClassification: 'metadata_lock_expected',
            risk: MigrationRisk::R1,
        );

        return ProviderMigrationPreview::create(
            planFingerprint: $run->planFingerprint,
            capabilities: $capabilities,
            physicalTableName: 'wp_orders',
            statements: [$statement],
        );
    }

    private static function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1, networkId: 1);
    }

    private static function assertBlocked(RuntimeCompositionReadinessPackage $package, string $reason): void
    {
        self::assertFalse($package->compositionReady);
        self::assertContains($reason, $package->reasons);
        self::assertFalse($package->executionAllowed);
        self::assertFalse($package->canonical()['execution_allowed']);
    }
}

final class FixedMetadataFactsProvider implements MetadataPreconditionFactsProviderInterface
{
    public int $calls = 0;

    public function __construct(
        private MetadataPreconditionFacts $facts,
        private bool $fail = false,
    ) {
    }

    public function facts(
        TableSchemaDescriptor $descriptor,
        ProviderCapabilityProfile $capabilities,
    ): MetadataPreconditionFacts {
        ++$this->calls;
        if ($this->fail) {
            throw new RuntimeException('Metadata facts unavailable.');
        }

        return $this->facts;
    }
}
