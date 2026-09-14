<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Entitlements\ProductEntitlementSnapshot;

interface ProductEntitlementProviderInterface
{
    public function snapshot(): ProductEntitlementSnapshot;
}
