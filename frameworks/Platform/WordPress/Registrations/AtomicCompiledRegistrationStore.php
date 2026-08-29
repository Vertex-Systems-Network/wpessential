<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

use RuntimeException;

final class AtomicCompiledRegistrationStore implements CompiledRegistrationStoreInterface, CompiledRegistrationGenerationSequenceInterface
{
    public function __construct(
        private readonly CompiledRegistrationPersistenceGatewayInterface $gateway,
        private readonly CompiledRegistrationScope $scope,
    ) {
    }

    public function active(): ?CompiledRegistrationManifest
    {
        return $this->resolveActive(true);
    }

    public function nextGeneration(): int
    {
        return ($this->gateway->latestGeneration($this->scope) ?? 0) + 1;
    }

    public function publish(CompiledRegistrationManifest $manifest): void
    {
        if (!CompiledRegistrationManifestIntegrity::verify($manifest)) {
            throw new RuntimeException('Compiled registration manifest checksum is invalid.');
        }

        // Resolve/recover the current active pointer first, but generation sequencing is
        // based on the immutable historical high-watermark, not the potentially rolled-back
        // active pointer. Corrupt generations therefore remain reserved forever.
        $current = $this->active();
        $expectedActiveGeneration = $current?->generation;
        $expectedNextGeneration = $this->nextGeneration();
        if ($manifest->generation !== $expectedNextGeneration) {
            throw new RuntimeException(sprintf(
                'Compiled registration generation must be historical high-watermark + 1 (%d) for the current scope.',
                $expectedNextGeneration,
            ));
        }

        if (!$this->gateway->publishAtomically($this->scope, $expectedActiveGeneration, $manifest)) {
            throw new RuntimeException('Compiled registration publication lost a concurrent compare-and-swap race; recompile from current state.');
        }
    }

    public function lastKnownGood(): ?CompiledRegistrationManifest
    {
        $fallback = $this->gateway->pointer($this->scope)->fallbackGeneration;
        if ($fallback === null) {
            return null;
        }
        $manifest = $this->gateway->generation($this->scope, $fallback);
        if (!$manifest instanceof CompiledRegistrationManifest || !CompiledRegistrationManifestIntegrity::verify($manifest)) {
            return null;
        }
        return $manifest;
    }

    public function recoverLastKnownGood(): CompiledRegistrationManifest
    {
        $pointer = $this->gateway->pointer($this->scope);
        if ($pointer->activeGeneration === null || $pointer->fallbackGeneration === null) {
            throw new RuntimeException('No last-known-good compiled registration generation is available.');
        }
        $fallback = $this->gateway->generation($this->scope, $pointer->fallbackGeneration);
        if (!$fallback instanceof CompiledRegistrationManifest || !CompiledRegistrationManifestIntegrity::verify($fallback)) {
            $this->gateway->recordCorruption($this->scope, $pointer->fallbackGeneration, 'fallback_checksum_or_payload_invalid');
            throw new RuntimeException('Last-known-good compiled registration generation is invalid.');
        }
        if (!$this->gateway->recoverAtomically($this->scope, $pointer->activeGeneration, $pointer->fallbackGeneration)) {
            throw new RuntimeException('Compiled registration recovery lost a concurrent compare-and-swap race.');
        }
        return $fallback;
    }

    private function resolveActive(bool $allowConcurrentRetry): ?CompiledRegistrationManifest
    {
        $pointer = $this->gateway->pointer($this->scope);
        if ($pointer->activeGeneration === null) {
            return null;
        }

        $active = $this->gateway->generation($this->scope, $pointer->activeGeneration);
        if ($active instanceof CompiledRegistrationManifest && CompiledRegistrationManifestIntegrity::verify($active)) {
            return $active;
        }

        $this->gateway->recordCorruption(
            $this->scope,
            $pointer->activeGeneration,
            $active instanceof CompiledRegistrationManifest ? 'active_checksum_mismatch' : 'active_generation_missing_or_quarantined',
        );

        if ($pointer->fallbackGeneration === null) {
            throw new RuntimeException('Active compiled registration generation is invalid and no last-known-good fallback exists.');
        }

        $fallback = $this->gateway->generation($this->scope, $pointer->fallbackGeneration);
        if (!$fallback instanceof CompiledRegistrationManifest || !CompiledRegistrationManifestIntegrity::verify($fallback)) {
            $this->gateway->recordCorruption($this->scope, $pointer->fallbackGeneration, 'fallback_checksum_or_payload_invalid');
            throw new RuntimeException('Active and fallback compiled registration generations are both invalid.');
        }

        if ($this->gateway->recoverAtomically($this->scope, $pointer->activeGeneration, $pointer->fallbackGeneration)) {
            return $fallback;
        }

        if ($allowConcurrentRetry) {
            return $this->resolveActive(false);
        }
        throw new RuntimeException('Compiled registration recovery could not stabilize after a concurrent pointer change.');
    }
}
