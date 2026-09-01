<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\ModuleActivationPolicyInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\Modules\ModuleState;

final class ModuleActivationGateTest extends TestCase
{
    public function testFreeModuleIsAdmittedAndBootedByDefault(): void
    {
        $module = new ActivationGateTestModule('free-module', 'free');
        $kernel = new Kernel();

        $kernel->registerModule($module);

        self::assertTrue($kernel->modules()->has('free-module'));
        self::assertSame(ModuleState::Registered, $kernel->modules()->state('free-module'));

        $kernel->boot();

        self::assertSame(1, $module->registerCalls);
        self::assertSame(1, $module->bootCalls);
        self::assertSame(ModuleState::Booted, $kernel->modules()->state('free-module'));
    }

    public function testProModuleFailsClosedAndNeverEntersRegistryByDefault(): void
    {
        $module = new ActivationGateTestModule('synthetic-pro', 'pro');
        $kernel = new Kernel();

        $kernel->registerModule($module);

        self::assertFalse($kernel->modules()->has('synthetic-pro'));
        self::assertNull($kernel->modules()->state('synthetic-pro'));

        $kernel->boot();

        self::assertSame(0, $module->registerCalls);
        self::assertSame(0, $module->bootCalls);
    }

    public function testExplicitInjectedPolicyCanAuthorizeSyntheticProModule(): void
    {
        $policy = new class implements ModuleActivationPolicyInterface {
            public function allows(ModuleManifest $manifest): bool
            {
                return $manifest->edition === 'free' || $manifest->id === 'synthetic-pro';
            }
        };
        $module = new ActivationGateTestModule('synthetic-pro', 'pro');
        $kernel = new Kernel(moduleActivationPolicy: $policy);

        $kernel->registerModule($module);
        $kernel->boot();

        self::assertTrue($kernel->modules()->has('synthetic-pro'));
        self::assertSame(1, $module->registerCalls);
        self::assertSame(1, $module->bootCalls);
        self::assertSame(ModuleState::Booted, $kernel->modules()->state('synthetic-pro'));
    }

    public function testDependencyOnDeniedProModuleUsesExistingDegradedPath(): void
    {
        $denied = new ActivationGateTestModule('synthetic-pro', 'pro');
        $dependent = new ActivationGateTestModule('dependent-free', 'free', ['synthetic-pro']);
        $kernel = new Kernel();

        $kernel->registerModule($denied);
        $kernel->registerModule($dependent);
        $kernel->boot();

        self::assertFalse($kernel->modules()->has('synthetic-pro'));
        self::assertSame(ModuleState::Degraded, $kernel->modules()->state('dependent-free'));
        self::assertSame(0, $denied->registerCalls);
        self::assertSame(0, $denied->bootCalls);
        self::assertSame(0, $dependent->registerCalls);
        self::assertSame(0, $dependent->bootCalls);
    }

    public function testFreeBootstrapDoesNotReferenceFieldsOrProImplementation(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/frameworks/Bootstrap/Plugin.php');
        self::assertIsString($source);
        self::assertStringNotContainsString('FieldsModule', $source);
        self::assertStringNotContainsString('Modules\\Fields', $source);
    }
}

final class ActivationGateTestModule implements ModuleInterface
{
    public int $registerCalls = 0;
    public int $bootCalls = 0;

    /** @param list<string> $dependencies */
    public function __construct(
        private readonly string $id,
        private readonly string $edition,
        private readonly array $dependencies = [],
    ) {
    }

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: $this->id,
            name: $this->id,
            version: '1.0.0',
            edition: $this->edition,
            dependencies: $this->dependencies,
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        unset($services);
        $this->registerCalls++;
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        unset($services);
        $this->bootCalls++;
    }
}
