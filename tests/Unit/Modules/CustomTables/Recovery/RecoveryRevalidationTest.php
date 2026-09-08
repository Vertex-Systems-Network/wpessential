<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Recovery;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationRevalidationDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryClass;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryRequirement;

final class RecoveryRevalidationTest extends TestCase
{
    private const DEFINITION_ID = '33333333-3333-4333-8333-333333333333';

    public function testMatchingReviewedGenerationMayProceedToLaterGate(): void
    {
        $reviewed = $this->stamp();
        $decision = MigrationRevalidationDecision::evaluate($reviewed, $this->stamp());

        self::assertTrue($decision->allowed);
        self::assertSame([], $decision->reasons);
    }

    public function testChangedObservedFingerprintFailsClosed(): void
    {
        $current = new MigrationGenerationStamp(
            planFingerprint: str_repeat('a', 64),
            sourceObservedFingerprint: str_repeat('c', 64),
            targetDefinitionId: self::DEFINITION_ID,
            targetRevision: 4,
            targetSchemaVersion: 5,
            provider: 'mysql',
            providerVersion: '8.4.0',
        );

        $decision = MigrationRevalidationDecision::evaluate($this->stamp(), $current);

        self::assertFalse($decision->allowed);
        self::assertContains('source_observed_fingerprint_changed', $decision->reasons);
    }

    public function testProviderVersionChangeFailsClosed(): void
    {
        $current = new MigrationGenerationStamp(
            planFingerprint: str_repeat('a', 64),
            sourceObservedFingerprint: str_repeat('b', 64),
            targetDefinitionId: self::DEFINITION_ID,
            targetRevision: 4,
            targetSchemaVersion: 5,
            provider: 'mysql',
            providerVersion: '8.0.36',
        );

        self::assertFalse(MigrationRevalidationDecision::evaluate($this->stamp(), $current)->allowed);
    }

    public function testBackupRecoveryClassRequiresVerifiedBackupEvidenceFlag(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RecoveryRequirement(RecoveryClass::VerifiedBackupRequired);
    }

    public function testIrreversibleClassRequiresRecoveryWindow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RecoveryRequirement(
            RecoveryClass::IrreversibleAfterRecoveryWindow,
            verifiedBackupRequired: true,
        );
    }

    private function stamp(): MigrationGenerationStamp
    {
        return new MigrationGenerationStamp(
            planFingerprint: str_repeat('a', 64),
            sourceObservedFingerprint: str_repeat('b', 64),
            targetDefinitionId: self::DEFINITION_ID,
            targetRevision: 4,
            targetSchemaVersion: 5,
            provider: 'mysql',
            providerVersion: '8.4.0',
        );
    }
}
