<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use PHPUnit\Framework\TestCase;

final class CompatibilityFreeBootstrapSourceTest extends TestCase
{
    public function testOptionalProRuntimeRequiresCanonicalCompatibilityPass(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/frameworks/Bootstrap/Plugin.php');
        self::assertIsString($source);

        $method = strpos($source, 'private static function proCustomTablesRuntimeAvailable');
        self::assertIsInt($method);
        $body = substr($source, $method, 1800);

        self::assertStringContainsString('WPE_PRO_PACKAGE_ACTIVE', $body);
        self::assertStringContainsString('WPE_PRO_COMPATIBILITY_STATE', $body);
        self::assertStringContainsString("WPE_PRO_COMPATIBILITY_STATE !== 'compatible'", $body);
        self::assertStringContainsString('WPE_PRO_COMPATIBILITY_BOOT_ALLOWED', $body);
        self::assertStringContainsString('WordPressMetadataPreconditionFactsProvider::class', $body);
    }

    public function testOptionalProMigrationsRequireMigrationAdmission(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/frameworks/Bootstrap/Plugin.php');
        self::assertIsString($source);

        self::assertStringContainsString('if (self::proCustomTablesMigrationsAllowed())', $source);
        self::assertStringContainsString('WPE_PRO_COMPATIBILITY_MIGRATIONS_ALLOWED', $source);
    }
}
