<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Authorization;

if (!defined('ABSPATH')) {
    exit;
}

enum ExecutionActorType: string
{
    case User = 'user';
    case System = 'system';
}
