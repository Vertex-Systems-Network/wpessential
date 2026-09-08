<?php

declare(strict_types=1);

namespace WPEssential\Platform\Events;

if (!defined('ABSPATH')) {
    exit;
}

final class EventServices
{
    public const BUS = 'platform.events';

    private function __construct() {}
}
