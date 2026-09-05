<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\AdminColumns\AdminColumnsModule;

final class AdminColumnsModuleBoundaryTest extends TestCase
{
    public function testFieldsDiscoveryRemainsOptionalAtModuleDependencyBoundary(): void
    {
        $manifest = (new AdminColumnsModule())->manifest();

        self::assertSame('admin-columns', $manifest->id);
        self::assertSame(['query'], $manifest->dependencies);
        self::assertNotContains('custom-fields', $manifest->dependencies);
    }
}
