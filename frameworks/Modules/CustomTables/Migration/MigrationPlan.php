<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class MigrationPlan
{
    /**
     * @param list<MigrationOperation> $operations
     * @param list<MigrationFinding> $findings
     */
    public function __construct(
        public string $id,
        public string $tableKey,
        public string $sourceObservedFingerprint,
        public string $targetDefinitionId,
        public int $targetRevision,
        public int $targetSchemaVersion,
        public string $targetCompatibilityFingerprint,
        public array $operations,
        public array $findings,
        public MigrationRisk $risk,
        public bool $blocked,
        public bool $recoveryRequired,
        public string $fingerprint,
    ) {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->id)) {
            throw new InvalidArgumentException('Custom Tables Migration Plan id must be a canonical UUID.');
        }
        if (!preg_match('/^[a-z][a-z0-9_]{0,47}$/', $this->tableKey)) {
            throw new InvalidArgumentException('Custom Tables Migration Plan table key is invalid.');
        }
        foreach ([$this->sourceObservedFingerprint, $this->targetCompatibilityFingerprint, $this->fingerprint] as $hash) {
            if (!preg_match('/^[0-9a-f]{64}$/', $hash)) {
                throw new InvalidArgumentException('Custom Tables Migration Plan fingerprints must be SHA-256 hex.');
            }
        }
        if ($this->targetRevision < 1 || $this->targetSchemaVersion < 1) {
            throw new InvalidArgumentException('Custom Tables Migration Plan target revisions must be positive.');
        }
        if (count($this->operations) > 256 || count($this->findings) > 256) {
            throw new InvalidArgumentException('Custom Tables Migration Plan exceeds bounded V1 limits.');
        }
    }

    public function isNoOp(): bool
    {
        return !$this->blocked && $this->operations === [] && $this->findings === [];
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        return [
            'id' => $this->id,
            'table_key' => $this->tableKey,
            'source_observed_fingerprint' => $this->sourceObservedFingerprint,
            'target_definition_id' => $this->targetDefinitionId,
            'target_revision' => $this->targetRevision,
            'target_schema_version' => $this->targetSchemaVersion,
            'target_compatibility_fingerprint' => $this->targetCompatibilityFingerprint,
            'operations' => array_map(
                static fn (MigrationOperation $operation): array => $operation->canonical(),
                $this->operations,
            ),
            'findings' => array_map(
                static fn (MigrationFinding $finding): array => $finding->canonical(),
                $this->findings,
            ),
            'risk' => 'R' . $this->risk->value,
            'blocked' => $this->blocked,
            'recovery_required' => $this->recoveryRequired,
            'fingerprint' => $this->fingerprint,
        ];
    }
}
