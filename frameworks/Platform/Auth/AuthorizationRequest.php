<?php

declare(strict_types=1);

namespace WPEssential\Platform\Auth;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class AuthorizationRequest
{
    public function __construct(
        public ExecutionContext $context,
        public string $ability,
        public string $capability,
        public ?string $resourceType = null,
        public string|int|null $resourceId = null,
    ) {
        if ($this->ability === '' || $this->capability === '') {
            throw new InvalidArgumentException('Authorization ability and capability cannot be empty.');
        }
    }
}
