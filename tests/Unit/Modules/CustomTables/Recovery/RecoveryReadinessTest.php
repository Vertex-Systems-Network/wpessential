<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Recovery;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryClass;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryEvidence;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryReadinessDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryRequirement;

final class RecoveryReadinessTest extends TestCase
{
    public function testTriviallyReversibleRequirementIsReadyWithMatchingEvidence(): void
    {
        $fingerprint = str_repeat('a', 64);
        $decision = RecoveryReadinessDecision::evaluate(
            new RecoveryRequirement(RecoveryClass::TriviallyReversible),
            new RecoveryEvidence($fingerprint, RecoveryClass::TriviallyReversible),
            $fingerprint,
        );

        self::assertTrue($decision->ready);
        self::assertSame([], $decision->reasons);
    }

    public function testMissingRetainedCopyFailsClosed(): void
    {
        $fingerprint = str_repeat('a', 64);
        $decision = RecoveryReadinessDecision::evaluate(
            new RecoveryRequirement(RecoveryClass::ReversibleWhileRetainedCopyExists, retainedCopyRequired: true),
            new RecoveryEvidence($fingerprint, RecoveryClass::ReversibleWhileRetainedCopyExists),
            $fingerprint,
        );

        self::assertFalse($decision->ready);
        self::assertContains('retained_copy_missing', $decision->reasons);
    }

    public function testVerifiedBackupWithinRecoveryWindowIsReady(): void
    {
        $fingerprint = str_repeat('a', 64);
        $decision = RecoveryReadinessDecision::evaluate(
            new RecoveryRequirement(
                RecoveryClass::IrreversibleAfterRecoveryWindow,
                verifiedBackupRequired: true,
                recoveryWindowHours: 24,
            ),
            new RecoveryEvidence(
                $fingerprint,
                RecoveryClass::IrreversibleAfterRecoveryWindow,
                verifiedBackupAvailable: true,
                backupAgeHours: 6,
            ),
            $fingerprint,
        );

        self::assertTrue($decision->ready);
    }

    public function testStaleBackupAndMismatchedFingerprintFailClosed(): void
    {
        $expected = str_repeat('a', 64);
        $decision = RecoveryReadinessDecision::evaluate(
            new RecoveryRequirement(
                RecoveryClass::IrreversibleAfterRecoveryWindow,
                verifiedBackupRequired: true,
                recoveryWindowHours: 12,
            ),
            new RecoveryEvidence(
                str_repeat('b', 64),
                RecoveryClass::IrreversibleAfterRecoveryWindow,
                verifiedBackupAvailable: true,
                backupAgeHours: 13,
            ),
            $expected,
        );

        self::assertFalse($decision->ready);
        self::assertContains('plan_fingerprint_mismatch', $decision->reasons);
        self::assertContains('verified_backup_stale', $decision->reasons);
    }

    public function testRecoveryClassMismatchFailsClosed(): void
    {
        $fingerprint = str_repeat('a', 64);
        $decision = RecoveryReadinessDecision::evaluate(
            new RecoveryRequirement(RecoveryClass::TriviallyReversible),
            new RecoveryEvidence($fingerprint, RecoveryClass::VerifiedBackupRequired, verifiedBackupAvailable: true),
            $fingerprint,
        );

        self::assertFalse($decision->ready);
        self::assertContains('recovery_class_mismatch', $decision->reasons);
    }
}
