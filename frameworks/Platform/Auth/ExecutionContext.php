<?php

declare(strict_types=1);

namespace WPEssential\Platform\Auth;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class ExecutionContext
{
    public function __construct(
        public Principal $principal,
        public int $siteId,
        public ExecutionChannel $channel = ExecutionChannel::Internal,
        public ?int $networkId = null,
        public ?string $correlationId = null,
    ) {
        if ($this->siteId < 1) {
            throw new InvalidArgumentException('Site id must be a positive integer.');
        }
        if ($this->networkId !== null && $this->networkId < 1) {
            throw new InvalidArgumentException('Network id must be a positive integer when provided.');
        }
    }
}
