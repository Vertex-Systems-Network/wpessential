<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;

final readonly class ProviderMigrationPreview
{
    /** @param list<ProviderStatementPreview> $statements */
    public function __construct(
        public string $planFingerprint,
        public ProviderCapabilityProfile $capabilities,
        public string $physicalTableName,
        public array $statements,
        public string $fingerprint,
        public bool $executionAllowed = false,
    ) {
        if ($this->executionAllowed) {
            throw new InvalidArgumentException('Custom Tables provider migration previews are never executable in bounded V1.');
        }
        if (preg_match('/^[0-9a-f]{64}$/', $this->planFingerprint) !== 1
            || preg_match('/^[0-9a-f]{64}$/', $this->fingerprint) !== 1
        ) {
            throw new InvalidArgumentException('Custom Tables provider preview fingerprints must be SHA-256 hex.');
        }
        if ($this->physicalTableName === ''
            || strlen($this->physicalTableName) > 64
            || preg_match('/^[A-Za-z0-9_]+$/', $this->physicalTableName) !== 1
        ) {
            throw new InvalidArgumentException('Custom Tables provider preview physical table identity is invalid.');
        }
        if (count($this->statements) > 256) {
            throw new InvalidArgumentException('Custom Tables provider preview exceeds bounded statement limits.');
        }
    }

    public function isNoOp(): bool
    {
        return $this->statements === [];
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        return [
            'plan_fingerprint' => $this->planFingerprint,
            'capabilities' => $this->capabilities->canonical(),
            'physical_table_name' => $this->physicalTableName,
            'statements' => array_map(
                static fn (ProviderStatementPreview $statement): array => $statement->canonical(),
                $this->statements,
            ),
            'execution_allowed' => false,
            'fingerprint' => $this->fingerprint,
        ];
    }

    /** @param list<ProviderStatementPreview> $statements @throws JsonException */
    public static function create(
        string $planFingerprint,
        ProviderCapabilityProfile $capabilities,
        string $physicalTableName,
        array $statements,
    ): self {
        $semantic = [
            'plan_fingerprint' => $planFingerprint,
            'capabilities' => $capabilities->canonical(),
            'physical_table_name' => $physicalTableName,
            'statements' => array_map(
                static fn (ProviderStatementPreview $statement): array => $statement->canonical(),
                $statements,
            ),
            'execution_allowed' => false,
        ];

        return new self(
            planFingerprint: $planFingerprint,
            capabilities: $capabilities,
            physicalTableName: $physicalTableName,
            statements: $statements,
            fingerprint: hash(
                'sha256',
                json_encode($semantic, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ),
        );
    }
}
