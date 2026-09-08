<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Definition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class TableSchemaDescriptor
{
    /**
     * @param list<TableColumnDescriptor> $columns
     * @param list<string> $primaryKey
     * @param list<TableIndexDescriptor> $indexes
     */
    public function __construct(
        public string $definitionId,
        public int $revision,
        public string $tableKey,
        public string $label,
        public string $storageMode,
        public string $scope,
        public int $desiredSchemaVersion,
        public array $columns,
        public array $primaryKey,
        public array $indexes,
        public string $charsetCollation,
        public string $dataClassification,
        public string $compatibilityFingerprint,
    ) {
        if ($this->revision < 1) {
            throw new InvalidArgumentException('Custom Tables descriptor revision must be positive.');
        }
        if (!preg_match('/^[a-z][a-z0-9_]{0,47}$/', $this->tableKey)) {
            throw new InvalidArgumentException('Custom Tables logical table key must be a canonical lowercase identifier within 48 characters.');
        }
        if ($this->label === '' || strlen($this->label) > 160) {
            throw new InvalidArgumentException('Custom Tables label must be a bounded non-empty string.');
        }
        if ($this->storageMode !== 'managed') {
            throw new InvalidArgumentException('Custom Tables V1 storage mode must be managed.');
        }
        if ($this->scope !== 'site') {
            throw new InvalidArgumentException('Custom Tables V1 scope must be site-owned.');
        }
        if ($this->desiredSchemaVersion < 1) {
            throw new InvalidArgumentException('Custom Tables desired schema version must be positive.');
        }
        if ($this->columns === [] || count($this->columns) > 128) {
            throw new InvalidArgumentException('Custom Tables descriptor requires between one and 128 columns.');
        }
        if ($this->primaryKey === [] || count($this->primaryKey) > 8) {
            throw new InvalidArgumentException('Custom Tables descriptor requires a bounded primary key.');
        }
        if (count($this->indexes) > 32) {
            throw new InvalidArgumentException('Custom Tables descriptor supports at most 32 secondary indexes.');
        }
        if ($this->charsetCollation !== 'inherit') {
            throw new InvalidArgumentException('Custom Tables V1 charset/collation policy must inherit from the canonical database environment.');
        }
        if (!in_array($this->dataClassification, ['public', 'internal', 'confidential', 'restricted'], true)) {
            throw new InvalidArgumentException('Custom Tables data classification is unsupported.');
        }
        if (!preg_match('/^[0-9a-f]{64}$/', $this->compatibilityFingerprint)) {
            throw new InvalidArgumentException('Custom Tables compatibility fingerprint must be SHA-256 hex.');
        }
    }

    /** @return array<string,mixed> */
    public function canonicalSemanticState(): array
    {
        return [
            'table_key' => $this->tableKey,
            'label' => $this->label,
            'storage_mode' => $this->storageMode,
            'scope' => $this->scope,
            'desired_schema_version' => $this->desiredSchemaVersion,
            'columns' => array_map(
                static fn (TableColumnDescriptor $column): array => $column->canonical(),
                $this->columns,
            ),
            'primary_key' => $this->primaryKey,
            'indexes' => array_map(
                static fn (TableIndexDescriptor $index): array => $index->canonical(),
                $this->indexes,
            ),
            'charset_collation' => $this->charsetCollation,
            'data_classification' => $this->dataClassification,
        ];
    }
}
