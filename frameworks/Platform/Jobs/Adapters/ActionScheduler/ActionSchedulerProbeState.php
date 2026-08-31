<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs\Adapters\ActionScheduler;


if (!defined('ABSPATH')) {
    exit;
}

enum ActionSchedulerProbeState: string
{
    case Absent = 'absent';
    case NotInitialized = 'not_initialized';
    case Incompatible = 'incompatible';
    case Ready = 'ready';
}
