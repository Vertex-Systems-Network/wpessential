<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\ModuleActivationPolicyInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Modules\ModuleManifest;

final class PremiumMutationGateTest extends TestCase
{
    public function testKernelTagsAbilitiesWithTheirModuleEdition(): void
    {
        [$kernel, $registry] = $this->kernel();
        $kernel->boot();

        self::assertSame('free', $registry->edition('wpessential/free-mod/write'));
        self::assertSame('pro', $registry->edition('wpessential/pro-mod/write'));
        self::assertSame('pro', $registry->edition('wpessential/pro-mod/boot-write'), 'boot() registrations are tagged too.');
        self::assertNull($registry->edition('wpessential/missing'));
    }

    public function testClosedGateMakesOnlyProWritesReadOnly(): void
    {
        [$kernel, $registry] = $this->kernel();
        $kernel->boot();
        $allowed = false;
        $registry->setPremiumMutationGate(static function () use (&$allowed): bool {
            return $allowed;
        });
        $context = new ExecutionContext(new Principal(1), 1);

        $denied = $registry->authorize('wpessential/pro-mod/write', $context);
        self::assertFalse($denied->allowed);
        self::assertSame('premium_read_only', $denied->reason);
        self::assertTrue($registry->authorize('wpessential/pro-mod/read', $context)->allowed, 'Pro data stays readable.');
        self::assertTrue($registry->authorize('wpessential/free-mod/write', $context)->allowed, 'Free is never gated.');

        $allowed = true;
        self::assertTrue($registry->authorize('wpessential/pro-mod/write', $context)->allowed);
    }

    public function testWithoutGateProWritesAreUnchanged(): void
    {
        [$kernel, $registry] = $this->kernel();
        $kernel->boot();

        self::assertTrue($registry->authorize('wpessential/pro-mod/write', new ExecutionContext(new Principal(1), 1))->allowed);
    }

    /** @return array{Kernel, AbilityRegistry} */
    private function kernel(): array
    {
        $capabilities = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                unset($context, $capability);
                return true;
            }
        };
        $registry = new AbilityRegistry(new PolicyEngine($capabilities));
        $allowAll = new class implements ModuleActivationPolicyInterface {
            public function allows(ModuleManifest $manifest): bool
            {
                unset($manifest);
                return true;
            }
        };
        $kernel = new Kernel(moduleActivationPolicy: $allowAll);
        $kernel->services()->set('platform.abilities', $registry);
        $kernel->registerModule(new GateTestModule('free-mod', 'free'));
        $kernel->registerModule(new GateTestModule('pro-mod', 'pro'));

        return [$kernel, $registry];
    }
}

final class GateTestModule implements ModuleInterface
{
    public function __construct(private string $id, private string $edition)
    {
    }

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(id: $this->id, name: $this->id, version: '1.0.0', edition: $this->edition);
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $abilities = $services->get('platform.abilities');
        \assert($abilities instanceof AbilityRegistry);
        $abilities->register($this->descriptor('write', true), $this->handler());
        $abilities->register($this->descriptor('read', false), $this->handler());
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        $abilities = $services->get('platform.abilities');
        \assert($abilities instanceof AbilityRegistry);
        $abilities->register($this->descriptor('boot-write', true), $this->handler());
    }

    private function descriptor(string $name, bool $mutates): AbilityDescriptor
    {
        return new AbilityDescriptor(
            name: 'wpessential/' . $this->id . '/' . $name,
            ownerSurfaceId: 3,
            capability: 'read',
            mutates: $mutates,
        );
    }

    private function handler(): AbilityHandlerInterface
    {
        return new class implements AbilityHandlerInterface {
            public function handle(array $input, ExecutionContext $context): mixed
            {
                unset($context);
                return $input;
            }
        };
    }
}
