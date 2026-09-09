<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Readiness\Composition;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionKind;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionRequirement;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\MetadataPreconditionFacts;
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
        $service = self::service($run, self::confirmation($run));

        $package = $service->evaluate(
            run: $run,
            requirements: [new PreconditionRequirement('table_exists', PreconditionKind::TableExists, 'orders')],
            metadataFacts: new MetadataPreconditionFacts(true),
            reviewedGeneration: self::generation($run),
            currentGeneration: self::generation($run),
            recoveryRequirement: new RecoveryRequirement(RecoveryClass::TriviallyReversible),
            recoveryArtifactId: 'artifact-1',
            maxRecoveryEvidenceAgeHours: 4,
            preview: self::preview($run),
            context: self::context(),
            risk: MigrationRisk::R1,
        );

        self::assertTrue($package->compositionReady);
        self::assertFalse($package->executionAllowed);
        self::assertSame([], $package->reasons);
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
        $service = self::service($run, $confirmation);

        $package = self::evaluate($service, $run);

        self::assertFalse($package->compositionReady);
        self::assertContains('confirmation_stale', $package->reasons);
        self::assertContains('authorization_confirmation_missing', $package->reasons);
        self::assertFalse($package->executionAllowed);
    }

    public function testRowScanningPreconditionIsNotAllowlisted(): void
    {
        $run = self::run();
        $service = self::service($run, self::confirmation($run));

        $package = $service->evaluate(
            run: $run,
            requirements: [new PreconditionRequirement(
                'row_count',
                PreconditionKind::RowCountRange,
                'orders',
                ['min' => 0, 'max' => 10],
            )],
            metadataFacts: new MetadataPreconditionFacts(true),
            reviewedGeneration: self::generation($run),
            currentGeneration: self::generation($run),
            recoveryRequirement: new RecoveryRequirement(RecoveryClass::TriviallyReversible),
            recoveryArtifactId: 'artifact-1',
            maxRecoveryEvidenceAgeHours: 4,
            preview: self::preview($run),
            context: self::context(),
            risk: MigrationRisk::R1,
        );

        self::assertFalse($package->compositionReady);
        self::assertContains('precondition_kind_not_allowlisted', $package->reasons);
        self::assertContains('readiness_preconditions_not_satisfied', $package->reasons);
        self::assertFalse($package->executionAllowed);
    }

    public function testNetworkScopeMismatchFailsClosed(): void
    {
        $run = self::run();
        $service = self::service($run, self::confirmation($run));

        $package = $service->evaluate(
            run: $run,
            requirements: [new PreconditionRequirement('table_exists', PreconditionKind::TableExists, 'orders')],
            metadataFacts: new MetadataPreconditionFacts(true),
            reviewedGeneration: self::generation($run),
            currentGeneration: self::generation($run),
            recoveryRequirement: new RecoveryRequirement(RecoveryClass::TriviallyReversible),
            recoveryArtifactId: 'artifact-1',
            maxRecoveryEvidenceAgeHours: 4,
            preview: self::preview($run),
            context: new ExecutionContext(new Principal(7), 1, networkId: 2),
            risk: MigrationRisk::R1,
        );

        self::assertFalse($package->compositionReady);
        self::assertContains('network_scope_mismatch', $package->reasons);
        self::assertFalse($package->executionAllowed);
    }

    private static function evaluate(
        RuntimeCompositionReadinessService $service,
        MigrationRun $run,
    ): RuntimeCompositionReadinessPackage {
        return $service->evaluate(
            run: $run,
            requirements: [new PreconditionRequirement('table_exists', PreconditionKind::TableExists, 'orders')],
            metadataFacts: new MetadataPreconditionFacts(true),
            reviewedGeneration: self::generation($run),
            currentGeneration: self::generation($run),
            recoveryRequirement: new RecoveryRequirement(RecoveryClass::TriviallyReversible),
            recoveryArtifactId: 'artifact-1',
            maxRecoveryEvidenceAgeHours: 4,
            preview: self::preview($run),
            context: self::context(),
            risk: MigrationRisk::R1,
        );
    }

    private static function service(
        MigrationRun $run,
        BoundMigrationExecutionConfirmation $confirmation,
    ): RuntimeCompositionReadinessService {
        $runs = new InMemoryMigrationRunRepository();
        $runs->create($run);

        $recovery = new BoundRecoveryEvidence(
            new RecoveryEvidence($run->planFingerprint, RecoveryClass::TriviallyReversible),
            'mysql',
            '8.0.36',
            0,
        );

        $checker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return $context->principal->userId === 7 && $capability === 'manage_options';
            }
        };

        return new RuntimeCompositionReadinessService(
            runs: $runs,
            recoveryVerification: new StaticRecoveryVerificationProvider(['artifact-1' => $recovery]),
            confirmations: new StaticMigrationExecutionConfirmationProvider([$confirmation]),
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
}
