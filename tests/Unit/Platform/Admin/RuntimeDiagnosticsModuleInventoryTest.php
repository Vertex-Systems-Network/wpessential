<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Platform\Admin\RuntimeDiagnosticsSnapshot;
use WPEssential\Platform\Entitlements\EntitlementAwareModuleActivationPolicy;
use WPEssential\Platform\Entitlements\LocalProductEntitlementProvider;
use WPEssential\Platform\Entitlements\ProductEntitlementSnapshot;
use WPEssential\Platform\Entitlements\ProductEntitlementState;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\Observability\NullTraceRecorder;

final class RuntimeDiagnosticsModuleInventoryTest extends TestCase
{
    public function testInventoryUsesCanonicalRegistryAndEntitlementTruth(): void
    {
        $entitlements = new LocalProductEntitlementProvider(
            new ProductEntitlementSnapshot(ProductEntitlementState::VerificationUnavailable),
        );
        $kernel = new Kernel(
            moduleActivationPolicy: new EntitlementAwareModuleActivationPolicy($entitlements),
        );
        $kernel->registerModule($this->module('free-inventory', 'Free Inventory', 'free'));
        $kernel->registerModule($this->module('pro-inventory', 'Pro Inventory', 'pro'));
        $kernel->modules()->markBooted('free-inventory');
        $kernel->modules()->markBooted('pro-inventory');

        $snapshot = new RuntimeDiagnosticsSnapshot($kernel, new NullTraceRecorder(), false);
        $modules = $snapshot->build()['modules'];

        self::assertIsArray($modules);
        self::assertSame(2, $modules['count']);
        self::assertTrue($modules['read_only']);
        self::assertSame('adr_0010_not_certified', $modules['compatibility_certification']);
        self::assertSame('unavailable', $modules['pro_compatibility']['state']);
        self::assertSame('adr_0010_not_certified', $modules['pro_compatibility']['certification']);

        $inventory = $modules['inventory'];
        self::assertIsArray($inventory);
        self::assertSame('free-inventory', $inventory[0]['id']);
        self::assertSame('free', $inventory[0]['edition']);
        self::assertSame('wpessential', $inventory[0]['package']);
        self::assertSame('not_applicable', $inventory[0]['entitlement']);
        self::assertSame('booted', $inventory[0]['runtime_state']);
        self::assertSame('booted', $inventory[0]['reason']);
        self::assertSame('local_prerequisites_met', $inventory[0]['compatibility']);

        self::assertSame('pro-inventory', $inventory[1]['id']);
        self::assertSame('pro', $inventory[1]['edition']);
        self::assertSame('wpessential-pro', $inventory[1]['package']);
        self::assertSame('verification_unavailable', $inventory[1]['entitlement']);
        self::assertSame('booted', $inventory[1]['runtime_state']);
        self::assertSame('read_safe_entitlement_verification_unavailable', $inventory[1]['reason']);
        self::assertSame(
            'compatibility_state_unavailable_adr_0010_not_certified',
            $inventory[1]['compatibility'],
        );
    }

    public function testDegradedModuleReportsCanonicalRegistryStateAndReason(): void
    {
        $kernel = new Kernel();
        $kernel->registerModule($this->module('needs-missing', 'Needs Missing', 'free', ['missing-module']));
        $kernel->modules()->bootOrder();

        $snapshot = new RuntimeDiagnosticsSnapshot($kernel, new NullTraceRecorder(), false);
        $inventory = $snapshot->build()['modules']['inventory'];

        self::assertSame('degraded', $inventory[0]['runtime_state']);
        self::assertSame('dependency_unavailable', $inventory[0]['reason']);
    }

    /** @param list<string> $dependencies */
    private function module(string $id, string $name, string $edition, array $dependencies = []): ModuleInterface
    {
        $manifest = new ModuleManifest(
            id: $id,
            name: $name,
            version: '1.0.0',
            edition: $edition,
            dependencies: $dependencies,
        );

        return new class ($manifest) implements ModuleInterface {
            public function __construct(private readonly ModuleManifest $manifest)
            {
            }

            public function manifest(): ModuleManifest
            {
                return $this->manifest;
            }

            public function register(ServiceRegistryInterface $services): void
            {
            }

            public function boot(ServiceRegistryInterface $services): void
            {
            }
        };
    }
}
