<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class RetryPolicy
{
    public function __construct(
        public int $maxAttempts = 3,
        public int $initialDelaySeconds = 30,
        public float $backoffFactor = 2.0,
        public int $maxDelaySeconds = 3600,
    ) {
        if ($this->maxAttempts < 1) {
            throw new InvalidArgumentException('Job maxAttempts must be at least one.');
        }
        if ($this->initialDelaySeconds < 0 || $this->maxDelaySeconds < 0) {
            throw new InvalidArgumentException('Job retry delays cannot be negative.');
        }
        if ($this->backoffFactor < 1.0) {
            throw new InvalidArgumentException('Job backoff factor must be at least 1.0.');
        }
    }

    public function delayAfterAttempt(int $attempt): int
    {
        if ($attempt < 1) {
            throw new InvalidArgumentException('Attempt must be positive.');
        }
        $delay = (int) round($this->initialDelaySeconds * ($this->backoffFactor ** ($attempt - 1)));
        return min($delay, $this->maxDelaySeconds);
    }
}
