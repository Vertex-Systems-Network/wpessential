<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Entitlements\ProductEntitlementState;

final class EntitlementStateCoverageTest extends TestCase
{
    public function testCanonicalStateInventoryIsStable(): void
    {
        self::assertSame([
            'free',
            'trial_active',
            'pro_active',
            'grace',
            'expired',
            'suspended',
            'verification_stale',
            'verification_unavailable',
            'incompatible_version',
        ], array_map(
            static fn (ProductEntitlementState $state): string => $state->value,
            ProductEntitlementState::cases(),
        ));
    }
}
