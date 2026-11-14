<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Server;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Connect\SiteProof as FreeSiteProof;
use WPEssential\Server\SiteProof;

final class SiteProofTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 3) . '/server/wpessential-server/src/SiteProof.php';
    }

    public function testServerAndFreeHashTheSameWay(): void
    {
        $token = str_repeat('ab', 32);
        self::assertSame(FreeSiteProof::hash($token), SiteProof::hash($token));
    }

    public function testOnlyTheTokenWhoseHashTheSitePublishedMatches(): void
    {
        $token = bin2hex(random_bytes(32));
        $published = FreeSiteProof::hash($token);

        self::assertTrue(SiteProof::matches($token, $published));
        self::assertFalse(SiteProof::matches(bin2hex(random_bytes(32)), $published), 'Another token (another account naming this domain).');
        self::assertFalse(SiteProof::matches($token, ''), 'Site publishes nothing.');
        self::assertFalse(SiteProof::matches('short', FreeSiteProof::hash('short')), 'Tokens must be long.');
    }
}
