<?php

declare(strict_types=1);

namespace WPEssential\Platform\Modules;

enum ModuleState: string
{
    case Registered = 'registered';
    case Degraded = 'degraded';
    case Booted = 'booted';
}
