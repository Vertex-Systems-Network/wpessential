<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin\Core;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Core\ModuleToggleStore;

final class ModuleToggleStoreTest extends TestCase
{
    public function testModulesAreOnByDefaultAndCanBeSwitchedOffAndOn(): void
    {
        $store = new ModuleToggleStore();

        self::assertTrue($store->isEnabled('relations'));

        $store->set('relations', false);
        self::assertFalse($store->isEnabled('relations'));
        self::assertTrue($store->isEnabled('query'));

        $store->set('relations', true);
        self::assertTrue($store->isEnabled('relations'));
    }

    public function testInvalidModuleIdsAreIgnored(): void
    {
        $store = new ModuleToggleStore();
        $store->set('../Relations', false);

        self::assertTrue($store->isEnabled('../Relations'));
    }
}
