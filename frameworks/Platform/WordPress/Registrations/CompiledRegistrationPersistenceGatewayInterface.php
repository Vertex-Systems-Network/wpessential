<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

interface CompiledRegistrationPersistenceGatewayInterface
{
    public function pointer(CompiledRegistrationScope $scope): CompiledRegistrationPointer;

    /** Historical high-watermark, including corrupt/quarantined generations. */
    public function latestGeneration(CompiledRegistrationScope $scope): ?int;

    public function generation(CompiledRegistrationScope $scope, int $generation): ?CompiledRegistrationManifest;

    /**
     * Atomically persist the immutable generation and move the active pointer only when
     * the current active generation still equals $expectedActiveGeneration.
     * Implementations must not expose a partially published generation as active and
     * must reject generation-number reuse below/equal to the historical high-watermark.
     */
    public function publishAtomically(
        CompiledRegistrationScope $scope,
        ?int $expectedActiveGeneration,
        CompiledRegistrationManifest $manifest,
    ): bool;

    /** Atomically replace a corrupt active pointer with its verified fallback generation. */
    public function recoverAtomically(
        CompiledRegistrationScope $scope,
        int $expectedActiveGeneration,
        int $fallbackGeneration,
    ): bool;

    public function recordCorruption(CompiledRegistrationScope $scope, int $generation, string $reason): void;
}
