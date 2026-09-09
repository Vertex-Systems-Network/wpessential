<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Recovery\Provider;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomTables\Migration\Recovery\Binding\BoundRecoveryEvidence;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\StaticRecoveryVerificationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryClass;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryEvidence;

final class StaticRecoveryVerificationProviderTest extends TestCase
{
    public function testExistingArtifactReturnsBoundEvidenceWithoutSideEffects(): void
    {
        $bound = self::bound(str_repeat('a', 64));
        $provider = new StaticRecoveryVerificationProvider(['backup:42' => $bound]);

        self::assertSame($bound, $provider->verify('backup:42', str_repeat('a', 64)));
        self::assertNull($provider->verify('backup:99', str_repeat('a', 64)));
    }

    public function testMismatchedPlanFailsClosed(): void
    {
        $provider = new StaticRecoveryVerificationProvider(['backup:42' => self::bound(str_repeat('a', 64))]);

        $this->expectException(RuntimeException::class);
        $provider->verify('backup:42', str_repeat('b', 64));
    }

    public function testInvalidArtifactIdFailsClosed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new StaticRecoveryVerificationProvider())->verify('?', str_repeat('a', 64));
    }

    private static function bound(string $planFingerprint): BoundRecoveryEvidence
    {
        return new BoundRecoveryEvidence(
            new RecoveryEvidence(
                planFingerprint: $planFingerprint,
                class: RecoveryClass::VerifiedBackupRequired,
                verifiedBackupAvailable: true,
                backupAgeHours: 1,
            ),
            'mysql',
            '8.0.36',
            1,
        );
    }
}
