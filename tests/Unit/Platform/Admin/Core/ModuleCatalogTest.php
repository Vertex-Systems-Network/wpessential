<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin\Core;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Platform\Admin\Core\ModuleCatalog;

final class ModuleCatalogTest extends TestCase
{
    public function testRuntimeModulesMatchTheirKernelManifests(): void
    {
        $catalog = ModuleCatalog::all();
        $root = dirname(__DIR__, 5);
        $checked = 0;

        foreach (glob($root . '/frameworks/Modules/*/*Module.php') ?: [] as $file) {
            $class = 'WPEssential\\Modules\\' . basename(dirname($file)) . '\\' . basename($file, '.php');
            if (!class_exists($class) || !is_subclass_of($class, ModuleInterface::class)) {
                continue;
            }

            $module = (new ReflectionClass($class))->newInstanceWithoutConstructor();
            $manifest = $module->manifest();

            self::assertArrayHasKey($manifest->id, $catalog, sprintf('Module "%s" is missing from the Core catalog.', $manifest->id));
            self::assertTrue($catalog[$manifest->id]['runtime'], sprintf('Module "%s" must be marked as runtime.', $manifest->id));
            self::assertSame($manifest->edition, $catalog[$manifest->id]['edition'], sprintf('Edition drifted for "%s".', $manifest->id));
            ++$checked;
        }

        self::assertGreaterThanOrEqual(20, $checked);
    }

    public function testEveryModuleHasAContractAndKnownCategory(): void
    {
        $root = dirname(__DIR__, 5);
        $categories = ModuleCatalog::categories();

        foreach (ModuleCatalog::all() as $id => $module) {
            self::assertSame($id, $module['id']);
            self::assertArrayHasKey($module['category'], $categories);
            self::assertContains($module['edition'], ['free', 'pro']);
            self::assertFileExists($root . '/config/product/option-contracts/' . $module['contract'] . '.json');
        }

        self::assertNull(ModuleCatalog::find('unknown-module'));
    }
}
