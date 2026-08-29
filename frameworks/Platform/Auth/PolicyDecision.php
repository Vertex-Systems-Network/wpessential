<?php

declare(strict_types=1);

namespace WPEssential\Platform\Auth;

final readonly class PolicyDecision
{
    private function __construct(
        public bool $allowed,
        public string $reason,
    ) {}

    public static function allow(string $reason = 'allowed'): self
    {
        return new self(true, $reason);
    }

    public static function deny(string $reason): self
    {
        return new self(false, $reason);
    }
}
