<?php

declare(strict_types=1);

namespace WPEssential\Platform\Cache;

if (!defined('ABSPATH')) {
    exit;
}

final class SystemCacheClock implements CacheClockInterface
{
    public function now(): int
    {
        return time();
    }
}
