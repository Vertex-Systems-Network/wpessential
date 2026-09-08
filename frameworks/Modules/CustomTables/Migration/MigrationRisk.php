<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration;

if (!defined('ABSPATH')) {
    exit;
}

enum MigrationRisk: int
{
    case R0 = 0;
    case R1 = 1;
    case R2 = 2;
    case R3 = 3;
    case R4 = 4;

    public static function max(self $left, self $right): self
    {
        return $left->value >= $right->value ? $left : $right;
    }
}
