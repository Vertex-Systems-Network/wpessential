<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class JobScope
{
    public function __construct(
        public int $networkId,
        public int $siteId,
    ) {
        if ($this->networkId < 1) {
            throw new InvalidArgumentException('Job network id must be positive.');
        }
        if ($this->siteId < 1) {
            throw new InvalidArgumentException('Job site id must be positive.');
        }
    }
}
