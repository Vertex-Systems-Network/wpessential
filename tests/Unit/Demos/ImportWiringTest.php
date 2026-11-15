<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Demos;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Connect\LicenseService;
use WPEssential\Platform\Demos\Import\Demos;

/** The demo importer (moved from the Hi WPEssential theme) unlocks Pro demos only with Premium. */
final class ImportWiringTest extends TestCase
{
    public function testProDemosFollowThePremiumCheck(): void
    {
        $premium = false;
        Demos::useLicense(new LicenseService(), static function () use (&$premium): bool {
            return $premium;
        });
        self::assertFalse(Demos::hasPro());
        $premium = true;
        self::assertTrue(Demos::hasPro());
    }

    public function testRoutesAreAdminOnlyAndNamespaced(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 3) . '/frameworks/Platform/Demos/Import/Rest.php');
        self::assertStringContainsString("current_user_can( 'manage_options' )", $source);
        foreach (['start', 'run', 'status', 'cancel', 'remove', 'library', 'plugins'] as $route) {
            self::assertStringContainsString("'/demos/import/{$route}'", $source);
        }
    }
}
