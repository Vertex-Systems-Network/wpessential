<?php

declare(strict_types=1);

namespace WPEssential\Platform\Cache;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class CacheLookup
{
    private function __construct(
        public bool $hit,
        public mixed $value,
    ) {
    }

    public static function miss(): self
    {
        return new self(false, null);
    }

    public static function hit(mixed $value): self
    {
        return new self(true, $value);
    }
}
