<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use RuntimeException;

final readonly class DashboardWidgetRuntimeClock
{
    private Closure $source;

    public function __construct(?callable $source = null)
    {
        $this->source = $source !== null
            ? Closure::fromCallable($source)
            : static fn (): int => time();
    }

    public function now(): int
    {
        $value = ($this->source)();
        if (!is_int($value) || $value < 0) {
            throw new RuntimeException('Dashboard Widget runtime clock returned an invalid Unix timestamp.');
        }

        return $value;
    }
}
