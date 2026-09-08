<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\State;

if (!defined('ABSPATH')) {
    exit;
}

enum ListingRuntimeState: string
{
    case Content = 'content';
    case Empty = 'empty';
    case Error = 'error';
    case Degraded = 'degraded';
}
