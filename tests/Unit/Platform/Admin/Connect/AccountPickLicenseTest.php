<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin\Connect;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Connect\Account;

final class AccountPickLicenseTest extends TestCase
{
    public function testSubscriptionAlreadyOnThisDomainWins(): void
    {
        $pick = Account::pickLicense([
            ['key' => 'PRO', 'status' => 'active', 'pro' => true, 'sites_limit' => 0, 'sites_used' => 3, 'domains' => []],
            ['key' => 'FREE', 'status' => 'active', 'pro' => false, 'sites_limit' => 1, 'sites_used' => 1, 'domains' => ['www.example.test']],
        ], 'example.test');

        self::assertSame('FREE', $pick['key'] ?? null);
    }

    public function testPremiumWithAFreeSlotIsPreferred(): void
    {
        $pick = Account::pickLicense([
            ['key' => 'FREE', 'status' => 'active', 'pro' => false, 'sites_limit' => 1, 'sites_used' => 0, 'domains' => []],
            ['key' => 'FULL', 'status' => 'active', 'pro' => true, 'sites_limit' => 1, 'sites_used' => 1, 'domains' => ['other.test']],
            ['key' => 'PRO5', 'status' => 'active', 'pro' => true, 'sites_limit' => 5, 'sites_used' => 2, 'domains' => ['a.test', 'b.test']],
            ['key' => 'OLD', 'status' => 'expired', 'pro' => true, 'sites_limit' => 0, 'sites_used' => 0, 'domains' => []],
        ], 'example.test');

        self::assertSame('PRO5', $pick['key'] ?? null);
    }

    public function testNoSlotReturnsNull(): void
    {
        self::assertNull(Account::pickLicense([
            ['key' => 'FULL', 'status' => 'active', 'pro' => true, 'sites_limit' => 1, 'sites_used' => 1, 'domains' => ['other.test']],
        ], 'example.test'));
    }
}
