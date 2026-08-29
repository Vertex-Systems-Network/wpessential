<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

use InvalidArgumentException;

final readonly class CompiledRegistrationManifest
{
    /** @param array<string,array<string,array<string,mixed>>> $entries */
    public function __construct(
        public int $generation,
        public array $entries,
        public string $checksum,
    ) {
        if ($this->generation < 1 || preg_match('/^[a-f0-9]{64}$/', $this->checksum) !== 1) {
            throw new InvalidArgumentException('Compiled registration manifest is invalid.');
        }
    }
}
