<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Ajax;

use InvalidArgumentException;
use WPEssential\Platform\WordPress\Security\NonceOperation;

final readonly class AjaxRoute
{
    public function __construct(
        public string $type,
        public AjaxHandlerInterface $handler,
        public NonceOperation $operation,
        public ?string $capability = null,
        public bool $allowGuests = false,
        public bool $requiresNonce = true,
    ) {
        if (preg_match('/^[a-z0-9][a-z0-9._-]*$/', $this->type) !== 1) {
            throw new InvalidArgumentException('AJAX request type is invalid.');
        }
        if ($this->capability !== null && trim($this->capability) === '') {
            throw new InvalidArgumentException('AJAX capability cannot be blank.');
        }
    }
}
