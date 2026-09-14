<?php

declare(strict_types=1);

namespace WPEssential\Platform\Entitlements;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\ModuleActivationPolicyInterface;
use WPEssential\Contracts\ProductEntitlementProviderInterface;
use WPEssential\Platform\Modules\ModuleManifest;

final readonly class EntitlementAwareModuleActivationPolicy implements ModuleActivationPolicyInterface
{
    public function __construct(private ProductEntitlementProviderInterface $entitlements)
    {
    }

    public function allows(ModuleManifest $manifest): bool
    {
        if ($manifest->edition === 'free') {
            return true;
        }

        return $this->entitlements->snapshot()->permitsPremiumModuleActivation();
    }
}
