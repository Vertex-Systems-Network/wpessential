<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\ModuleActivationPolicyInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Modules\Fields\FieldsModule;
use WPEssential\Platform\Modules\ModuleManifest;

final class FieldsModuleActivationGateTest extends TestCase
{
    public function testConcreteFieldsModuleIsDeniedByDefaultBeforeLifecycleRegistration(): void
    {
        $kernel = new Kernel();
        $kernel->registerModule(new FieldsModule());

        self::assertFalse($kernel->modules()->has('custom-fields'));

        $kernel->boot();
        self::assertFalse($kernel->modules()->has('custom-fields'));
    }

    public function testExplicitExternalPolicyCanAdmitConcreteFieldsModuleToRegistry(): void
    {
        $policy = new class implements ModuleActivationPolicyInterface {
            public function allows(ModuleManifest $manifest): bool
            {
                return $manifest->edition === 'free' || $manifest->id === 'custom-fields';
            }
        };
        $kernel = new Kernel(moduleActivationPolicy: $policy);

        $kernel->registerModule(new FieldsModule());

        self::assertTrue($kernel->modules()->has('custom-fields'));
    }
}
