<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;

final readonly class ProviderStatementPreview
{
    public function __construct(
        public string $operationType,
        public string $target,
        public string $sql,
        public string $algorithmClassification,
        public string $lockClassification,
        public MigrationRisk $risk,
        public string $fingerprint,
        public bool $executionAllowed = false,
    ) {
        if ($this->executionAllowed) {
            throw new InvalidArgumentException('Custom Tables provider preview statements are never executable in bounded V1.');
        }
        if (!in_array($this->algorithmClassification, [
            'not_applicable',
            'instant_candidate',
            'inplace_candidate',
            'provider_default',
        ], true)) {
            throw new InvalidArgumentException('Custom Tables provider algorithm classification is invalid.');
        }
        if (!in_array($this->lockClassification, [
            'not_applicable',
            'metadata_lock_expected',
            'provider_dependent',
        ], true)) {
            throw new InvalidArgumentException('Custom Tables provider lock classification is invalid.');
        }
        if ($this->sql === ''
            || strlen($this->sql) > 65535
            || str_contains($this->sql, ';')
            || str_contains($this->sql, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $this->sql) === 1
        ) {
            throw new InvalidArgumentException('Custom Tables provider statement preview SQL is invalid or ambiguous across SQL modes.');
        }
        if (preg_match('/^[0-9a-f]{64}$/', $this->fingerprint) !== 1) {
            throw new InvalidArgumentException('Custom Tables provider statement fingerprint must be SHA-256 hex.');
        }
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        return [
            'operation_type' => $this->operationType,
            'target' => $this->target,
            'sql' => $this->sql,
            'algorithm_classification' => $this->algorithmClassification,
            'lock_classification' => $this->lockClassification,
            'risk' => 'R' . $this->risk->value,
            'execution_allowed' => false,
            'fingerprint' => $this->fingerprint,
        ];
    }

    /** @throws JsonException */
    public static function create(
        string $operationType,
        string $target,
        string $sql,
        string $algorithmClassification,
        string $lockClassification,
        MigrationRisk $risk,
    ): self {
        $semantic = [
            'operation_type' => $operationType,
            'target' => $target,
            'sql' => $sql,
            'algorithm_classification' => $algorithmClassification,
            'lock_classification' => $lockClassification,
            'risk' => 'R' . $risk->value,
            'execution_allowed' => false,
        ];

        return new self(
            operationType: $operationType,
            target: $target,
            sql: $sql,
            algorithmClassification: $algorithmClassification,
            lockClassification: $lockClassification,
            risk: $risk,
            fingerprint: hash(
                'sha256',
                json_encode($semantic, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ),
        );
    }
}
