<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class MigrationGenerationStamp
{
    public function __construct(
        public string $planFingerprint,
        public string $sourceObservedFingerprint,
        public string $targetDefinitionId,
        public int $targetRevision,
        public int $targetSchemaVersion,
        public string $provider,
        public string $providerVersion,
    ) {
        foreach ([$this->planFingerprint, $this->sourceObservedFingerprint] as $fingerprint) {
            if (preg_match('/^[a-f0-9]{64}$/', $fingerprint) !== 1) {
                throw new InvalidArgumentException('Custom Tables migration generation fingerprint is invalid.');
            }
        }
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $this->targetDefinitionId) !== 1) {
            throw new InvalidArgumentException('Custom Tables migration generation Definition id is invalid.');
        }
        if ($this->targetRevision < 1 || $this->targetSchemaVersion < 1) {
            throw new InvalidArgumentException('Custom Tables migration generation revisions must be positive.');
        }
        if (!in_array($this->provider, ['mysql', 'mariadb'], true)) {
            throw new InvalidArgumentException('Custom Tables migration generation provider is unsupported.');
        }
        if (preg_match('/^\d+\.\d+\.\d+$/', $this->providerVersion) !== 1) {
            throw new InvalidArgumentException('Custom Tables migration generation provider version is invalid.');
        }
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        return [
            'plan_fingerprint' => $this->planFingerprint,
            'source_observed_fingerprint' => $this->sourceObservedFingerprint,
            'target_definition_id' => strtolower($this->targetDefinitionId),
            'target_revision' => $this->targetRevision,
            'target_schema_version' => $this->targetSchemaVersion,
            'provider' => $this->provider,
            'provider_version' => $this->providerVersion,
        ];
    }
}
