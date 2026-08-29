<?php

declare(strict_types=1);

namespace WPEssential\Platform\Assets;

enum AssetLoadStrategy: string
{
    case OnDemand = 'on_demand';
    case AdminRoute = 'admin_route';
    case RenderDiscovery = 'render_discovery';
}
