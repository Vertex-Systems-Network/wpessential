<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition;

if (!defined('ABSPATH')) {
    exit;
}

enum PreconditionOutcome: string
{
    case Satisfied = 'satisfied';
    case Blocked = 'blocked';
    case Unsupported = 'unsupported';
}
