<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;


if (!defined('ABSPATH')) {
    exit;
}

enum JobIdempotencyMode: string
{
    case Natural = 'natural';
    case StableKey = 'stable_key';
    case Checkpoint = 'checkpoint';
    case Reconciliation = 'reconciliation';
}
