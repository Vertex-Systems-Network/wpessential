<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Server;

use PHPUnit\Framework\TestCase;
use WPEssential\Server\HostLock;

final class HostLockTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 3) . '/server/wpessential-server/src/HostLock.php';
    }

    public function testOnlyTheOwnerHostsMayRunTheServer(): void
    {
        self::assertTrue(HostLock::allowed('wpessential.org'));
        self::assertTrue(HostLock::allowed('www.wpessential.org'));
        self::assertTrue(HostLock::allowed('VertexSystemsNetwork.com'));
        self::assertFalse(HostLock::allowed('customer-site.com'));
        self::assertFalse(HostLock::allowed('wpessential.org.evil.com'));
        self::assertFalse(HostLock::allowed('evilwpessential.org'));
        self::assertFalse(HostLock::allowed(''));
    }

    public function testLocalCopiesNeedTheExplicitDevelopmentSwitch(): void
    {
        self::assertFalse(HostLock::allowed('wpessential.local'));
        self::assertTrue(HostLock::allowed('wpessential.local', true));
        self::assertTrue(HostLock::allowed('localhost', true));
        self::assertFalse(HostLock::allowed('shop.example.com', true));
    }

    public function testServerBootstrapStopsBeforeLoadingAnythingOnAForeignHost(): void
    {
        $bootstrap = (string) file_get_contents(dirname(__DIR__, 3) . '/server/wpessential-server/wpessential-server.php');
        $lock = strpos($bootstrap, 'HostLock::ok()');
        self::assertNotFalse($lock);
        foreach (['Install', 'Plugin::instance', 'before_woocommerce_init'] as $later) {
            self::assertGreaterThan($lock, strpos($bootstrap, $later), "{$later} must come after the host lock");
        }
    }

    public function testDistributableBuilderRefusesServerCode(): void
    {
        $builder = (string) file_get_contents(dirname(__DIR__, 3) . '/tools/release/build-free-pro-distributables.php');
        self::assertStringContainsString("assertNoServerCode(\$freeStage, 'Free');", $builder);
        self::assertStringContainsString("assertNoServerCode(\$proStage, 'Pro');", $builder);
    }
}
