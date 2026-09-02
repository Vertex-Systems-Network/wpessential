<?php

declare(strict_types=1);

namespace WPEssential\Platform\Cache;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class CachePolicy
{
    private function __construct(
        public bool $enabled,
        public int $ttlSeconds,
    ) {
        if ($this->enabled && $this->ttlSeconds < 1) {
            throw new InvalidArgumentException('Enabled cache policy TTL must be positive.');
        }
        if (!$this->enabled && $this->ttlSeconds !== 0) {
            throw new InvalidArgumentException('Disabled cache policy TTL must be zero.');
        }
    }

    public static function disabled(): self
    {
        return new self(false, 0);
    }

    public static function ttl(int $seconds): self
    {
        return new self(true, $seconds);
    }
}
