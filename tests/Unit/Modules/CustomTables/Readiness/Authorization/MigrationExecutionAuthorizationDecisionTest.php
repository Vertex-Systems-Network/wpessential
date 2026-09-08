<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Readiness\Authorization;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionEvaluation;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionOutcome;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionReport;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Migration\ProviderMigrationPreview;
use WPEssential\Modules\CustomTables\Migration\ProviderStatementPreview;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\ExecutionActorType;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\MigrationExecutionAuthorizationDecision;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\MigrationExecutionAuthorizationRequest;
use WPEssential\Modules\CustomTables\Migration\Readiness\MigrationExecutionReadinessDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationRevalidationDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryClass;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryEvidence;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryReadinessDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryRequirement;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;

final class MigrationExecutionAuthorizationDecisionTest extends TestCase
{
    public function testReadyLowRiskConfirmedCapableUserCanBeAuthorized(): void
    {
        $run = self::run();
        $request = new MigrationExecutionAuthorizationRequest(
            runId: $run->id,
            readinessStateRevision: $run->stateRevision,
            actorType: ExecutionActorType::User,
            actorUserId: 7,
            capabilityAllowed: true,
            confirmationProvided: true,
            risk: MigrationRisk::R1,
        );

        $decision = MigrationExecutionAuthorizationDecision::evaluate($run, self::readyDecision($run), $request);

        self::assertTrue($decision->authorized);
        self::assertSame([], $decision->reasons);
        self::assertSame(7, $request->canonical()['actor_user_id']);
    }

    public function testUnsafeFactsFailClosedWithDeterministicReasons(): void
    {
        $run = self::run();
        $request = new MigrationExecutionAuthorizationRequest(
            runId: '33333333-3333-4333-8333-333333333333',
            readinessStateRevision: $run->stateRevision + 1,
            actorType: ExecutionActorType::User,
            actorUserId: 7,
            capabilityAllowed: false,
            confirmationProvided: false,
            risk: MigrationRisk::R4,
        );

        $decision = MigrationExecutionAuthorizationDecision::evaluate(
            $run,
            self::notReadyDecision(),
            $request,
        );

        self::assertFalse($decision->authorized);
        self::assertSame([
            'capability_denied',
            'confirmation_missing',
            'readiness_not_ready',
            'readiness_stale',
            'run_id_mismatch',
            'unsupported_risk',
        ], $decision->reasons);
    }

    public function testHighImpactR3RemainsUnsupportedEvenWhenOtherFactsPass(): void
    {
        $run = self::run();
        $request = new MigrationExecutionAuthorizationRequest(
            runId: $run->id,
            readinessStateRevision: $run->stateRevision,
            actorType: ExecutionActorType::System,
            actorUserId: null,
            capabilityAllowed: true,
            confirmationProvided: true,
            risk: MigrationRisk::R3,
        );

        $decision = MigrationExecutionAuthorizationDecision::evaluate($run, self::readyDecision($run), $request);

        self::assertFalse($decision->authorized);
        self::assertSame(['unsupported_risk'], $decision->reasons);
    }

    public function testActorIdentityInvariantsFailAtConstruction(): void
    {
        try {
            new MigrationExecutionAuthorizationRequest(
                runId: '11111111-1111-4111-8111-111111111111',
                readinessStateRevision: 1,
                actorType: ExecutionActorType::User,
                actorUserId: null,
                capabilityAllowed: true,
                confirmationProvided: true,
                risk: MigrationRisk::R1,
            );
            self::fail('User actor without id should fail closed.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $this->expectException(InvalidArgumentException::class);
        new MigrationExecutionAuthorizationRequest(
            runId: '11111111-1111-4111-8111-111111111111',
            readinessStateRevision: 1,
            actorType: ExecutionActorType::System,
            actorUserId: 7,
            capabilityAllowed: true,
            confirmationProvided: true,
            risk: MigrationRisk::R1,
        );
    }

    private static function run(): MigrationRun
    {
        return new MigrationRun(
            id: '11111111-1111-4111-8111-111111111111',
            planFingerprint: str_repeat('a', 64),
            tableKey: 'orders',
            targetDefinitionId: '22222222-2222-4222-8222-222222222222',
            targetRevision: 1,
            targetSchemaVersion: 1,
            state: MigrationRunState::Revalidating,
            stateRevision: 4,
        );
    }

    private static function readyDecision(MigrationRun $run): MigrationExecutionReadinessDecision
    {
        $preconditions = new PreconditionReport([
            new PreconditionEvaluation('table_exists', PreconditionOutcome::Satisfied),
        ]);
        $stamp = self::stamp();
        $revalidation = MigrationRevalidationDecision::evaluate($stamp, $stamp);
        $recovery = RecoveryReadinessDecision::evaluate(
            new RecoveryRequirement(RecoveryClass::TriviallyReversible),
            new RecoveryEvidence($run->planFingerprint, RecoveryClass::TriviallyReversible),
            $run->planFingerprint,
        );

        return MigrationExecutionReadinessDecision::evaluate(
            $run,
            $preconditions,
            $revalidation,
            $recovery,
            self::preview($run->planFingerprint),
        );
    }

    private static function notReadyDecision(): MigrationExecutionReadinessDecision
    {
        $run = new MigrationRun(
            id: '11111111-1111-4111-8111-111111111111',
            planFingerprint: str_repeat('a', 64),
            tableKey: 'orders',
            targetDefinitionId: '22222222-2222-4222-8222-222222222222',
            targetRevision: 1,
            targetSchemaVersion: 1,
            state: MigrationRunState::Approved,
        );
        $stamp = self::stamp();

        return MigrationExecutionReadinessDecision::evaluate(
            $run,
            new PreconditionReport([
                new PreconditionEvaluation('table_exists', PreconditionOutcome::Blocked),
            ]),
            MigrationRevalidationDecision::evaluate($stamp, $stamp),
            RecoveryReadinessDecision::evaluate(
                new RecoveryRequirement(RecoveryClass::TriviallyReversible),
                new RecoveryEvidence(str_repeat('a', 64), RecoveryClass::TriviallyReversible),
                str_repeat('a', 64),
            ),
            self::preview(str_repeat('a', 64)),
        );
    }

    private static function stamp(): MigrationGenerationStamp
    {
        return new MigrationGenerationStamp(
            planFingerprint: str_repeat('a', 64),
            sourceObservedFingerprint: str_repeat('c', 64),
            targetDefinitionId: '22222222-2222-4222-8222-222222222222',
            targetRevision: 1,
            targetSchemaVersion: 1,
            provider: 'mysql',
            providerVersion: '8.0.36',
        );
    }

    private static function preview(string $planFingerprint): ProviderMigrationPreview
    {
        return ProviderMigrationPreview::create(
            planFingerprint: $planFingerprint,
            capabilities: new ProviderCapabilityProfile('mysql', '8.0.36', true, true, true),
            physicalTableName: 'wp_orders',
            statements: [
                ProviderStatementPreview::create(
                    operationType: 'add_column',
                    target: 'orders.note',
                    sql: 'ALTER TABLE `wp_orders` ADD COLUMN `note` VARCHAR(20)',
                    algorithmClassification: 'provider_default',
                    lockClassification: 'metadata_lock_expected',
                    risk: MigrationRisk::R1,
                ),
            ],
        );
    }
}
