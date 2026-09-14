<?php

declare(strict_types=1);

namespace WPEssential\Platform\Entitlements;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\ProductEntitlementProviderInterface;

final readonly class LocalProductEntitlementProvider implements ProductEntitlementProviderInterface
{
    public function __construct(private ProductEntitlementSnapshot $snapshot)
    {
    }

    public function snapshot(): ProductEntitlementSnapshot
    {
        return $this->snapshot;
    }
}
