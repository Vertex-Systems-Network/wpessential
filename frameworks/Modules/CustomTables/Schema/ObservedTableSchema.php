<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Schema;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\CustomTables\Definition\TableColumnDescriptor;
use WPEssential\Modules\CustomTables\Definition\TableIndexDescriptor;

final readonly class ObservedTableSchema
{
    /**
     * @param list<TableColumnDescriptor> $columns
     * @param list<string> $primaryKey
     * @param list<TableIndexDescriptor> $indexes
     * @param list<SchemaObservationFinding> $findings
     */
    public function __construct(
        public string $tableKey,
        public bool $exists,
        public array $columns,
        public array $primaryKey,
        public array $indexes,
        public string $charsetCollation,
        public array $findings,
        public string $fingerprint,
    ) {
        if (!preg_match('/^[a-z][a-z0-9_]{0,47}$/', $this->tableKey)) {
            throw new InvalidArgumentException('Observed Custom Tables logical table key is invalid.');
        }
        if (!$this->exists && ($this->columns !== [] || $this->primaryKey !== [] || $this->indexes !== [])) {
            throw new InvalidArgumentException('Missing Custom Tables observation cannot contain physical schema members.');
        }
        if ($this->exists && count($this->columns) > 128) {
            throw new InvalidArgumentException('Observed Custom Tables column count exceeds the bounded V1 limit.');
        }
        if (count($this->primaryKey) > 8 || count($this->indexes) > 32 || count($this->findings) > 128) {
            throw new InvalidArgumentException('Observed Custom Tables schema exceeds bounded V1 limits.');
        }
        if (!in_array($this->charsetCollation, ['inherit', 'unknown'], true)) {
            throw new InvalidArgumentException('Observed Custom Tables charset/collation classification is unsupported.');
        }
        if (!preg_match('/^[0-9a-f]{64}$/', $this->fingerprint)) {
            throw new InvalidArgumentException('Observed Custom Tables fingerprint must be SHA-256 hex.');
        }
    }

    public function isBlockedByDrift(): bool
    {
        return $this->findings !== [];
    }

    /** @return array<string,mixed> */
    public function canonicalObservedState(): array
    {
        return [
            'table_key' => $this->tableKey,
            'exists' => $this->exists,
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
            'findings' => array_map(
                static fn (SchemaObservationFinding $finding): array => $finding->canonical(),
                $this->findings,
            ),
        ];
    }
}
