<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

use InvalidArgumentException;

final readonly class CompiledRegistrationPointer
{
    public function __construct(
        public ?int $activeGeneration,
        public ?int $fallbackGeneration,
    ) {
        if ($this->activeGeneration === null && $this->fallbackGeneration !== null) {
            throw new InvalidArgumentException('Compiled registration fallback cannot exist without an active generation.');
        }
        if ($this->activeGeneration !== null && $this->activeGeneration < 1) {
            throw new InvalidArgumentException('Active compiled registration generation must be positive.');
        }
        if ($this->fallbackGeneration !== null) {
            if ($this->fallbackGeneration < 1 || $this->activeGeneration === null || $this->fallbackGeneration >= $this->activeGeneration) {
                throw new InvalidArgumentException('Fallback generation must be positive and older than active generation.');
            }
        }
    }
}
