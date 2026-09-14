<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin;

use PHPUnit\Framework\TestCase;

final class PlatformAdminCommercialInventorySourceTest extends TestCase
{
    public function testModulesInventoryMarkupIsReadOnlyAccessibleAndNonSecret(): void
    {
        $root = dirname(__DIR__, 4);
        $source = file_get_contents($root . '/frameworks/Platform/Admin/PlatformAdminController.php');

        self::assertIsString($source);
        foreach (['Module', 'Edition', 'Package', 'Compatibility', 'Entitlement', 'Runtime state', 'Reason'] as $heading) {
            self::assertStringContainsString("'{$heading}'", $source);
        }
        self::assertStringContainsString('screen-reader-text', $source);
        self::assertStringContainsString('ADR-0010 certified Free/Pro version pairs are not claimed here', $source);
        self::assertStringNotContainsString('<form', $source);
        self::assertStringNotContainsString('<button', $source);
        self::assertStringNotContainsString('license_key', $source);
        self::assertStringNotContainsString('license_token', $source);
    }
}
