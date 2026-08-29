<?php

declare(strict_types=1);

namespace WPEssential\Platform\Auth;

use InvalidArgumentException;

final readonly class Principal
{
    public function __construct(
        public ?int $userId,
        public string $actorType = 'user',
    ) {
        if ($this->userId !== null && $this->userId < 1) {
            throw new InvalidArgumentException('Authenticated WordPress user ids must be positive integers.');
        }
        if (!preg_match('/^[a-z][a-z0-9_-]*$/', $this->actorType)) {
            throw new InvalidArgumentException('Actor type must be a stable lowercase identifier.');
        }
    }

    public function isAuthenticated(): bool
    {
        return $this->userId !== null;
    }
}
