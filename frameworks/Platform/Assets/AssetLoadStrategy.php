<?php

declare(strict_types=1);

namespace WPEssential\Platform\Assets;


if (!defined('ABSPATH')) {
    exit;
}

enum AssetLoadStrategy: string
{
    case OnDemand = 'on_demand';
    case AdminRoute = 'admin_route';
    case RenderDiscovery = 'render_discovery';
}
