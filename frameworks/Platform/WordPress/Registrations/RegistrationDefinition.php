<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

use InvalidArgumentException;

final readonly class RegistrationDefinition
{
    /** @param array<string,mixed> $payload */
    public function __construct(
        public string $id,
        public RegistrationKind $kind,
        public string $key,
        public array $payload,
        public bool $enabled = true,
        public int $revision = 1,
    ) {
        if (trim($this->id) === '' || preg_match('/^[a-z0-9][a-z0-9._-]*$/', $this->key) !== 1 || $this->revision < 1) {
            throw new InvalidArgumentException('Registration definition identity is invalid.');
        }
    }
}
