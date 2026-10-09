<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\Definition;

final class DashboardWidgetSubsitePresetOverrideDefinition
{
    public const TYPE = 'dashboard-widget-subsite-preset-override';
    public const OWNER_SURFACE_ID = 10;

    public static function assertOwned(Definition $definition): void
    {
        if ($definition->type !== self::TYPE || $definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Dashboard subsite preset override type or owner surface is invalid.');
        }
    }
}
