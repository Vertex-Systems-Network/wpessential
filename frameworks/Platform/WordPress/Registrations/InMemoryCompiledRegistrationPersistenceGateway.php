<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

use RuntimeException;

final class InMemoryCompiledRegistrationPersistenceGateway implements CompiledRegistrationPersistenceGatewayInterface
{
    /** @var array<string, CompiledRegistrationPointer> */
    private array $pointers = [];

    /** @var array<string, array<int, CompiledRegistrationManifest>> */
    private array $generations = [];

    /** @var array<string, array<int, string>> */
    private array $corruptions = [];

    private bool $failNextPublishAfterStage = false;

    public function pointer(CompiledRegistrationScope $scope): CompiledRegistrationPointer
    {
        return $this->pointers[$scope->key()] ?? new CompiledRegistrationPointer(null, null);
    }

    public function latestGeneration(CompiledRegistrationScope $scope): ?int
    {
        $generations = array_keys($this->generations[$scope->key()] ?? []);
        if ($generations === []) {
            return null;
        }
        return max($generations);
    }

    public function generation(CompiledRegistrationScope $scope, int $generation): ?CompiledRegistrationManifest
    {
        if (isset($this->corruptions[$scope->key()][$generation])) {
            return null;
        }
        return $this->generations[$scope->key()][$generation] ?? null;
    }

    public function publishAtomically(
        CompiledRegistrationScope $scope,
        ?int $expectedActiveGeneration,
        CompiledRegistrationManifest $manifest,
    ): bool {
        $key = $scope->key();
        $pointer = $this->pointer($scope);
        if ($pointer->activeGeneration !== $expectedActiveGeneration) {
            return false;
        }

        $expectedNextGeneration = ($this->latestGeneration($scope) ?? 0) + 1;
        if ($manifest->generation !== $expectedNextGeneration) {
            throw new RuntimeException(sprintf(
                'Atomic registration publication requires historical generation %d; generation numbers are immutable and cannot be reused.',
                $expectedNextGeneration,
            ));
        }
        if (isset($this->generations[$key][$manifest->generation])) {
            throw new RuntimeException('Compiled registration generations are immutable.');
        }

        $this->generations[$key][$manifest->generation] = $manifest;
        if ($this->failNextPublishAfterStage) {
            unset($this->generations[$key][$manifest->generation]);
            $this->failNextPublishAfterStage = false;
            throw new RuntimeException('Simulated atomic publication failure after generation staging.');
        }

        $this->pointers[$key] = new CompiledRegistrationPointer($manifest->generation, $expectedActiveGeneration);
        return true;
    }

    public function recoverAtomically(
        CompiledRegistrationScope $scope,
        int $expectedActiveGeneration,
        int $fallbackGeneration,
    ): bool {
        $key = $scope->key();
        $pointer = $this->pointer($scope);
        if ($pointer->activeGeneration !== $expectedActiveGeneration || $pointer->fallbackGeneration !== $fallbackGeneration) {
            return false;
        }
        if (!isset($this->generations[$key][$fallbackGeneration]) || isset($this->corruptions[$key][$fallbackGeneration])) {
            return false;
        }

        $nextFallback = null;
        $candidates = array_keys($this->generations[$key] ?? []);
        rsort($candidates, SORT_NUMERIC);
        foreach ($candidates as $candidate) {
            if ($candidate < $fallbackGeneration && !isset($this->corruptions[$key][$candidate])) {
                $nextFallback = $candidate;
                break;
            }
        }

        $this->pointers[$key] = new CompiledRegistrationPointer($fallbackGeneration, $nextFallback);
        return true;
    }

    public function recordCorruption(CompiledRegistrationScope $scope, int $generation, string $reason): void
    {
        $this->corruptions[$scope->key()][$generation] = substr(trim($reason), 0, 191);
    }

    public function failNextPublishAfterStageForTesting(): void
    {
        $this->failNextPublishAfterStage = true;
    }

    public function tamperGenerationChecksumForTesting(CompiledRegistrationScope $scope, int $generation): void
    {
        $manifest = $this->generations[$scope->key()][$generation] ?? null;
        if (!$manifest instanceof CompiledRegistrationManifest) {
            throw new RuntimeException('Cannot tamper a missing compiled generation.');
        }
        $this->generations[$scope->key()][$generation] = new CompiledRegistrationManifest(
            $manifest->generation,
            $manifest->entries,
            str_repeat('0', 64),
        );
    }

    public function hasGenerationForTesting(CompiledRegistrationScope $scope, int $generation): bool
    {
        return isset($this->generations[$scope->key()][$generation]);
    }

    /** @return array<int, string> */
    public function corruptionsForTesting(CompiledRegistrationScope $scope): array
    {
        return $this->corruptions[$scope->key()] ?? [];
    }
}
