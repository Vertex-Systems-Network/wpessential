<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Connect\Account;
use WPEssential\Platform\Admin\Connect\SiteProof;

/**
 * WPE Server lives in Vertex-Systems-Network/wpessential-server. Free only talks to it, and
 * must never ship its code.
 */
final class ServerBoundaryTest extends TestCase
{
    public function testFreeRepositoryHasNoServerCode(): void
    {
        $root = dirname(__DIR__, 3);
        self::assertDirectoryDoesNotExist($root . '/server');
        $builder = (string) file_get_contents($root . '/tools/release/build-free-pro-distributables.php');
        self::assertStringContainsString("assertNoServerCode(\$freeStage, 'Free');", $builder, 'The Free ZIP builder still refuses server code.');
    }

    public function testSiteProofHashMatchesTheServer(): void
    {
        // The server checks exactly this hash (WPEssential\Server\SiteProof::hash).
        $token = str_repeat('ab', 32);
        self::assertSame(hash('sha256', 'wpessential-site-proof|' . $token), SiteProof::hash($token));
    }

    public function testFreeFollowsOnlyTheServerOrLemonSqueezyCheckout(): void
    {
        self::assertTrue(Account::trustedCheckoutHost('wpessential.org', 'wpessential.org', true));
        self::assertTrue(Account::trustedCheckoutHost('wpessential.lemonsqueezy.com', 'wpessential.org', true));
        self::assertFalse(Account::trustedCheckoutHost('wpessential.lemonsqueezy.com', 'wpessential.org', false), 'HTTPS only.');
        self::assertFalse(Account::trustedCheckoutHost('lemonsqueezy.com.evil.test', 'wpessential.org', true));
        self::assertFalse(Account::trustedCheckoutHost('evil-lemonsqueezy.com', 'wpessential.org', true));
        self::assertFalse(Account::trustedCheckoutHost(null, 'wpessential.org', true));
    }
}
