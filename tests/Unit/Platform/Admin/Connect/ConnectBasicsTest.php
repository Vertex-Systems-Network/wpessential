<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin\Connect;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Connect\LicenseService;
use WPEssential\Platform\Admin\Elements\ElementRegistry;
use WPEssential\Platform\Admin\Ui\Icon;

final class ConnectBasicsTest extends TestCase
{
    public function testLicenseKeysAreMasked(): void
    {
        self::assertSame('ABCD••••••••WXYZ', LicenseService::mask('ABCD-1234-5678-WXYZ'));
        self::assertSame('••••••••', LicenseService::mask('short'));
        self::assertSame('', LicenseService::mask(''));
    }

    public function testElementsShipEmptyAndUnknownElementsCannotBeToggled(): void
    {
        $elements = new ElementRegistry();

        self::assertSame([], $elements->all('gutenberg'));
        self::assertSame([], $elements->all('elementor'));
        self::assertFalse($elements->set('gutenberg', 'missing', false));
    }

    public function testEveryShellTabHasAFlatIcon(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 5) . '/frameworks/Platform/Admin/Ui/Shell.php');
        preg_match_all("/'wpessential'\\s*\\),\\s*'([a-z]+)'\\s*\\]/", $source, $matches);
        self::assertNotEmpty($matches[1]);
        foreach ($matches[1] as $icon) {
            self::assertContains($icon, Icon::names());
        }
        self::assertStringContainsString('stroke="currentColor"', Icon::render('spark'));
    }
}
