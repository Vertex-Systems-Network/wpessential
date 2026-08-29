<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

use JsonException;
use RuntimeException;
use Throwable;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class WpdbCompiledRegistrationPersistenceGateway implements CompiledRegistrationPersistenceGatewayInterface
{
    private readonly CompiledRegistrationTableNames $tables;

    public function __construct(private readonly DatabaseAdapterInterface $database)
    {
        $this->tables = new CompiledRegistrationTableNames($database);
    }

    public function pointer(CompiledRegistrationScope $scope): CompiledRegistrationPointer
    {
        $row = $this->database->getRow($this->database->prepare(
            "SELECT active_generation, fallback_generation FROM `{$this->tables->state}` WHERE network_id = %d AND site_id = %d",
            $scope->networkId,
            $scope->databaseSiteId(),
        ));
        if ($row === null) {
            return new CompiledRegistrationPointer(null, null);
        }
        return new CompiledRegistrationPointer(
            self::nullablePositiveInt($row['active_generation'] ?? null),
            self::nullablePositiveInt($row['fallback_generation'] ?? null),
        );
    }

    public function latestGeneration(CompiledRegistrationScope $scope): ?int
    {
        return self::nullablePositiveInt($this->database->getVar($this->database->prepare(
            "SELECT MAX(generation) FROM `{$this->tables->generations}` WHERE network_id = %d AND site_id = %d",
            $scope->networkId,
            $scope->databaseSiteId(),
        )));
    }

    public function generation(CompiledRegistrationScope $scope, int $generation): ?CompiledRegistrationManifest
    {
        $row = $this->database->getRow($this->database->prepare(
            "SELECT checksum, manifest_json, corrupt_at FROM `{$this->tables->generations}` WHERE network_id = %d AND site_id = %d AND generation = %d",
            $scope->networkId,
            $scope->databaseSiteId(),
            $generation,
        ));
        if ($row === null || !empty($row['corrupt_at'])) {
            return null;
        }

        try {
            $entries = json_decode((string) ($row['manifest_json'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($entries)) {
                return null;
            }
            /** @var array<string,array<string,array<string,mixed>>> $entries */
            return new CompiledRegistrationManifest($generation, $entries, (string) ($row['checksum'] ?? ''));
        } catch (JsonException|\InvalidArgumentException) {
            return null;
        }
    }

    public function publishAtomically(
        CompiledRegistrationScope $scope,
        ?int $expectedActiveGeneration,
        CompiledRegistrationManifest $manifest,
    ): bool {
        $this->database->beginTransaction();
        try {
            $this->ensureStateRow($scope);
            $row = $this->lockedPointerRow($scope);
            $current = self::nullablePositiveInt($row['active_generation'] ?? null);
            if ($current !== $expectedActiveGeneration) {
                $this->database->rollBack();
                return false;
            }

            // The scope state row is locked, serializing WPE writers for this scope. The
            // immutable generation table remains the sequence authority even after recovery.
            $latest = self::nullablePositiveInt($this->database->getVar($this->database->prepare(
                "SELECT MAX(generation) FROM `{$this->tables->generations}` WHERE network_id = %d AND site_id = %d",
                $scope->networkId,
                $scope->databaseSiteId(),
            )));
            $expectedNext = ($latest ?? 0) + 1;
            if ($manifest->generation !== $expectedNext) {
                throw new RuntimeException(sprintf(
                    'Database publication requires historical generation %d; generation numbers cannot be reused.',
                    $expectedNext,
                ));
            }

            $json = json_encode(
                CompiledRegistrationManifestIntegrity::canonicalEntries($manifest->entries),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION,
            );
            $this->database->insert($this->tables->generations, [
                'network_id' => $scope->networkId,
                'site_id' => $scope->databaseSiteId(),
                'generation' => $manifest->generation,
                'checksum' => $manifest->checksum,
                'manifest_json' => $json,
                'created_at' => gmdate('Y-m-d H:i:s'),
            ], ['%d', '%d', '%d', '%s', '%s', '%s']);

            $fallbackSql = $current === null ? 'NULL' : (string) $current;
            $affected = $this->database->query($this->database->prepare(
                "UPDATE `{$this->tables->state}` SET active_generation = %d, fallback_generation = {$fallbackSql}, updated_at = UTC_TIMESTAMP(6) WHERE network_id = %d AND site_id = %d",
                $manifest->generation,
                $scope->networkId,
                $scope->databaseSiteId(),
            ));
            if ($affected !== 1) {
                throw new RuntimeException('Compiled registration active pointer update did not affect exactly one row.');
            }

            $this->database->commit();
            return true;
        } catch (Throwable $exception) {
            $this->safeRollback();
            throw new RuntimeException('Atomic compiled registration publication failed.', 0, $exception);
        }
    }

    public function recoverAtomically(
        CompiledRegistrationScope $scope,
        int $expectedActiveGeneration,
        int $fallbackGeneration,
    ): bool {
        $this->database->beginTransaction();
        try {
            $this->ensureStateRow($scope);
            $row = $this->lockedPointerRow($scope);
            $current = self::nullablePositiveInt($row['active_generation'] ?? null);
            $currentFallback = self::nullablePositiveInt($row['fallback_generation'] ?? null);
            if ($current !== $expectedActiveGeneration || $currentFallback !== $fallbackGeneration) {
                $this->database->rollBack();
                return false;
            }

            $fallbackRow = $this->database->getRow($this->database->prepare(
                "SELECT generation FROM `{$this->tables->generations}` WHERE network_id = %d AND site_id = %d AND generation = %d AND corrupt_at IS NULL",
                $scope->networkId,
                $scope->databaseSiteId(),
                $fallbackGeneration,
            ));
            if ($fallbackRow === null) {
                $this->database->rollBack();
                return false;
            }

            $previous = self::nullablePositiveInt($this->database->getVar($this->database->prepare(
                "SELECT MAX(generation) FROM `{$this->tables->generations}` WHERE network_id = %d AND site_id = %d AND generation < %d AND corrupt_at IS NULL",
                $scope->networkId,
                $scope->databaseSiteId(),
                $fallbackGeneration,
            )));
            $previousSql = $previous === null ? 'NULL' : (string) $previous;
            $affected = $this->database->query($this->database->prepare(
                "UPDATE `{$this->tables->state}` SET active_generation = %d, fallback_generation = {$previousSql}, updated_at = UTC_TIMESTAMP(6) WHERE network_id = %d AND site_id = %d",
                $fallbackGeneration,
                $scope->networkId,
                $scope->databaseSiteId(),
            ));
            if ($affected !== 1) {
                throw new RuntimeException('Compiled registration recovery pointer update did not affect exactly one row.');
            }

            $this->database->commit();
            return true;
        } catch (Throwable $exception) {
            $this->safeRollback();
            throw new RuntimeException('Atomic compiled registration recovery failed.', 0, $exception);
        }
    }

    public function recordCorruption(CompiledRegistrationScope $scope, int $generation, string $reason): void
    {
        $reason = substr(trim($reason), 0, 191);
        if ($reason === '') {
            $reason = 'unspecified_corruption';
        }
        $this->database->query($this->database->prepare(
            "UPDATE `{$this->tables->generations}` SET corrupt_at = COALESCE(corrupt_at, UTC_TIMESTAMP(6)), corrupt_reason = CASE WHEN corrupt_reason IS NULL OR corrupt_reason = '' THEN %s ELSE corrupt_reason END WHERE network_id = %d AND site_id = %d AND generation = %d",
            $reason,
            $scope->networkId,
            $scope->databaseSiteId(),
            $generation,
        ));
    }

    private function ensureStateRow(CompiledRegistrationScope $scope): void
    {
        $this->database->query($this->database->prepare(
            "INSERT IGNORE INTO `{$this->tables->state}` (network_id, site_id, active_generation, fallback_generation, updated_at) VALUES (%d, %d, NULL, NULL, UTC_TIMESTAMP(6))",
            $scope->networkId,
            $scope->databaseSiteId(),
        ));
    }

    /** @return array<string,mixed> */
    private function lockedPointerRow(CompiledRegistrationScope $scope): array
    {
        $row = $this->database->getRow($this->database->prepare(
            "SELECT active_generation, fallback_generation FROM `{$this->tables->state}` WHERE network_id = %d AND site_id = %d FOR UPDATE",
            $scope->networkId,
            $scope->databaseSiteId(),
        ));
        if ($row === null) {
            throw new RuntimeException('Compiled registration state row could not be locked.');
        }
        return $row;
    }

    private function safeRollback(): void
    {
        try {
            $this->database->rollBack();
        } catch (Throwable) {
        }
    }

    private static function nullablePositiveInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $integer = filter_var($value, FILTER_VALIDATE_INT);
        if ($integer === false || $integer < 1) {
            return null;
        }
        return $integer;
    }
}
