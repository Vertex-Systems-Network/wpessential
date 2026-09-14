<?php

declare(strict_types=1);

namespace WPEssential\Platform\Entitlements;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\ProductEntitlementProviderInterface;

final readonly class PremiumOperationPolicy
{
    public function __construct(private ProductEntitlementProviderInterface $entitlements)
    {
    }

    public function allowsRead(): bool
    {
        return $this->entitlements->snapshot()->permitsPremiumRead();
    }

    public function allowsMutation(): bool
    {
        return $this->entitlements->snapshot()->permitsPremiumMutation();
    }
}
