<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Recovery\Binding;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Migration\Recovery\Binding\BoundRecoveryEvidence;
use WPEssential\Modules\CustomTables\Migration\Recovery\Binding\RecoveryEvidenceBindingDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryClass;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryEvidence;

final class RecoveryEvidenceBindingDecisionTest extends TestCase
{
    public function testMatchingFreshEvidenceIsValid(): void
    {
        $reviewed = self::stamp();
        $bound = new BoundRecoveryEvidence(
            new RecoveryEvidence(str_repeat('a', 64), RecoveryClass::TriviallyReversible),
            'mysql',
            '8.0.36',
            2,
        );

        $decision = RecoveryEvidenceBindingDecision::evaluate($reviewed, $bound, 4);

        self::assertTrue($decision->valid);
        self::assertSame([], $decision->reasons);
        self::assertSame('mysql', $bound->canonical()['provider']);
    }

    public function testMissingEvidenceFailsClosed(): void
    {
        $decision = RecoveryEvidenceBindingDecision::evaluate(self::stamp(), null, 4);

        self::assertFalse($decision->valid);
        self::assertSame(['missing_evidence'], $decision->reasons);
    }

    public function testStaleAndMismatchedEvidenceReturnsDeterministicReasons(): void
    {
        $bound = new BoundRecoveryEvidence(
            new RecoveryEvidence(str_repeat('b', 64), RecoveryClass::TriviallyReversible),
            'mariadb',
            '10.11.8',
            9,
        );

        $decision = RecoveryEvidenceBindingDecision::evaluate(self::stamp(), $bound, 4);

        self::assertFalse($decision->valid);
        self::assertSame([
            'evidence_stale',
            'plan_fingerprint_mismatch',
            'provider_mismatch',
            'provider_version_mismatch',
        ], $decision->reasons);
    }

    public function testInvalidBindingFactsFailAtConstruction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new BoundRecoveryEvidence(
            new RecoveryEvidence(str_repeat('a', 64), RecoveryClass::TriviallyReversible),
            'sqlite',
            '3.45.1',
            0,
        );
    }

    public function testNegativeMaximumEvidenceAgeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        RecoveryEvidenceBindingDecision::evaluate(self::stamp(), null, -1);
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
}
