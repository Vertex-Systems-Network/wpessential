<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;

final class ProviderCapabilityProfileTest extends TestCase
{
    public function testMariaDbCompatibilityPrefixUsesActualMariaDbVersion(): void
    {
        $profile = ProviderCapabilityProfile::fromTrustedServerInfo(
            '5.5.5-10.11.8-MariaDB-0ubuntu0.24.04.1',
        );

        self::assertSame(ProviderCapabilityProfile::MARIADB, $profile->provider);
        self::assertSame('10.11.8', $profile->version);
        self::assertTrue($profile->instantAddColumnCandidate);
    }

    public function testProviderVersionChangesPreviewCapabilityIdentity(): void
    {
        $first = ProviderCapabilityProfile::fromTrustedServerInfo('8.4.0 MySQL Community Server');
        $second = ProviderCapabilityProfile::fromTrustedServerInfo('8.4.1 MySQL Community Server');

        self::assertNotSame($first->canonical(), $second->canonical());
    }
}
