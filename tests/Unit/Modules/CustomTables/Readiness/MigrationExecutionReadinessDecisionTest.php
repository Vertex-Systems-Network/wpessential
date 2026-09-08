<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Readiness;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionEvaluation;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionOutcome;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionReport;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Migration\ProviderMigrationPreview;
use WPEssential\Modules\CustomTables\Migration\ProviderStatementPreview;
use WPEssential\Modules\CustomTables\Migration\Readiness\MigrationExecutionReadinessDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationRevalidationDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryClass;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryEvidence;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryReadinessDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryRequirement;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;

final class MigrationExecutionReadinessDecisionTest extends TestCase
{
    public function testAllExecutionFreeFactsCanProduceReadyDecision(): void
    {
        $run = self::run(MigrationRunState::Revalidating);
        $preconditions = new PreconditionReport([
            new PreconditionEvaluation('table_exists', PreconditionOutcome::Satisfied),
        ]);
        $stamp = self::stamp();
        $revalidation = MigrationRevalidationDecision::evaluate($stamp, $stamp);
        $recovery = RecoveryReadinessDecision::evaluate(
            new RecoveryRequirement(RecoveryClass::TriviallyReversible),
            new RecoveryEvidence(str_repeat('a', 64), RecoveryClass::TriviallyReversible),
            str_repeat('a', 64),
        );
        $preview = self::preview(str_repeat('a', 64), true);

        $decision = MigrationExecutionReadinessDecision::evaluate(
            $run,
            $preconditions,
            $revalidation,
            $recovery,
            $preview,
        );

        self::assertTrue($decision->ready);
        self::assertSame([], $decision->reasons);
    }

    public function testUnsafeOrIncompleteFactsFailClosedWithDeterministicReasons(): void
    {
        $run = self::run(MigrationRunState::Approved);
        $preconditions = new PreconditionReport([
            new PreconditionEvaluation('row_count', PreconditionOutcome::Blocked),
        ]);
        $reviewed = self::stamp();
        $changed = new MigrationGenerationStamp(
            planFingerprint: str_repeat('b', 64),
            sourceObservedFingerprint: str_repeat('c', 64),
            targetDefinitionId: '22222222-2222-4222-8222-222222222222',
            targetRevision: 1,
            targetSchemaVersion: 1,
            provider: 'mysql',
            providerVersion: '8.0.36',
        );
        $revalidation = MigrationRevalidationDecision::evaluate($reviewed, $changed);
        $recovery = RecoveryReadinessDecision::evaluate(
            new RecoveryRequirement(RecoveryClass::VerifiedBackupRequired, verifiedBackupRequired: true),
            new RecoveryEvidence(str_repeat('a', 64), RecoveryClass::VerifiedBackupRequired),
            str_repeat('a', 64),
        );
        $preview = self::preview(str_repeat('b', 64), false);

        $decision = MigrationExecutionReadinessDecision::evaluate(
            $run,
            $preconditions,
            $revalidation,
            $recovery,
            $preview,
        );

        self::assertFalse($decision->ready);
        self::assertSame([
            'preconditions_not_satisfied',
            'preview_has_no_statements',
            'preview_plan_fingerprint_mismatch',
            'recovery_not_ready',
            'revalidation_failed',
            'run_not_revalidating',
        ], $decision->reasons);
    }

    private static function run(MigrationRunState $state): MigrationRun
    {
        return new MigrationRun(
            id: '11111111-1111-4111-8111-111111111111',
            planFingerprint: str_repeat('a', 64),
            tableKey: 'orders',
            targetDefinitionId: '22222222-2222-4222-8222-222222222222',
            targetRevision: 1,
            targetSchemaVersion: 1,
            state: $state,
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

    private static function preview(string $planFingerprint, bool $withStatement): ProviderMigrationPreview
    {
        $statements = [];
        if ($withStatement) {
            $statements[] = ProviderStatementPreview::create(
                operationType: 'add_column',
                target: 'orders.note',
                sql: 'ALTER TABLE `wp_orders` ADD COLUMN `note` VARCHAR(20)',
                algorithmClassification: 'provider_default',
                lockClassification: 'metadata_lock_expected',
                risk: MigrationRisk::R1,
            );
        }

        return ProviderMigrationPreview::create(
            planFingerprint: $planFingerprint,
            capabilities: new ProviderCapabilityProfile('mysql', '8.0.36', true, true, true),
            physicalTableName: 'wp_orders',
            statements: $statements,
        );
    }
}
