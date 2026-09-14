<?php

declare(strict_types=1);

namespace WPEssential\Platform\Entitlements;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class ProductEntitlementSnapshot
{
    public function __construct(
        public ProductEntitlementState $state,
        public ?string $reason = null,
    ) {
    }

    public function permitsPremiumModuleActivation(): bool
    {
        return !in_array($this->state, [
            ProductEntitlementState::Free,
            ProductEntitlementState::IncompatibleVersion,
        ], true);
    }

    public function permitsPremiumRead(): bool
    {
        return $this->permitsPremiumModuleActivation();
    }

    public function permitsPremiumMutation(): bool
    {
        return in_array($this->state, [
            ProductEntitlementState::TrialActive,
            ProductEntitlementState::ProActive,
            ProductEntitlementState::Grace,
        ], true);
    }
}
