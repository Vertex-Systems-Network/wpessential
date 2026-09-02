<?php

declare(strict_types=1);

namespace WPEssential\Platform\Cache;

if (!defined('ABSPATH')) {
    exit;
}

interface CacheClockInterface
{
    public function now(): int;
}
