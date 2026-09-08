<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Recovery;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryClass;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryEvidence;
use WPEssential\Modules\CustomTables\Migration\Recovery\StaticRecoveryEvidenceSource;

final class RecoveryEvidenceSourceTest extends TestCase
{
    public function testStaticSourceReturnsEvidenceByPlanFingerprint(): void
    {
        $evidence = new RecoveryEvidence(
            planFingerprint: str_repeat('a', 64),
            class: RecoveryClass::TriviallyReversible,
        );

        $source = new StaticRecoveryEvidenceSource([$evidence]);

        self::assertSame($evidence, $source->get(str_repeat('a', 64)));
        self::assertNull($source->get(str_repeat('b', 64)));
    }

    public function testDuplicatePlanEvidenceFailsClosed(): void
    {
        $evidence = new RecoveryEvidence(
            planFingerprint: str_repeat('a', 64),
            class: RecoveryClass::TriviallyReversible,
        );

        $this->expectException(InvalidArgumentException::class);
        new StaticRecoveryEvidenceSource([$evidence, $evidence]);
    }

    public function testInvalidLookupFingerprintIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new StaticRecoveryEvidenceSource())->get('invalid');
    }
}
