<?php

declare(strict_types=1);

namespace WPEssential\Platform\DataSources;

if (!defined('ABSPATH')) {
    exit;
}

enum DataSourceAvailability: string
{
    case Available = 'available';
    case Degraded = 'degraded';
}
