<?php

declare(strict_types=1);

namespace WPEssential\Platform\Modules;


if (!defined('ABSPATH')) {
    exit;
}

enum ModuleState: string
{
    case Registered = 'registered';
    case Degraded = 'degraded';
    case Booted = 'booted';
}
