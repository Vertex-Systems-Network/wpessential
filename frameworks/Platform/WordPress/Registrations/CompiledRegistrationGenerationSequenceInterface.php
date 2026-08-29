<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

interface CompiledRegistrationGenerationSequenceInterface
{
    /**
     * Returns the next immutable generation number for the store scope.
     * The sequence is based on the historical high-watermark, including
     * quarantined/corrupt generations, and must never reuse an old number.
     */
    public function nextGeneration(): int;
}
