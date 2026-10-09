<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\Definition;

final class DashboardWidgetMultisitePolicyDefinition
{
    public const TYPE = 'dashboard-widget-multisite-policy';
    public const OWNER_SURFACE_ID = 10;

    public static function assertOwned(Definition $definition): void
    {
        if ($definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Dashboard Widget Multisite policy owner surface is invalid.');
        }
        if ($definition->type !== self::TYPE) {
            throw new InvalidArgumentException('Dashboard Widget Multisite policy definition type is invalid.');
        }
    }
}
