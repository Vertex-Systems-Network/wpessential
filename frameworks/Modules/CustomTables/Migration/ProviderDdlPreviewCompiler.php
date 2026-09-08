<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;
use WPEssential\Modules\CustomTables\Definition\TableColumnDescriptor;
use WPEssential\Modules\CustomTables\Definition\TableIndexDescriptor;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;

final readonly class ProviderDdlPreviewCompiler
{
    /** @throws JsonException */
    public function compile(
        MigrationPlan $plan,
        TableSchemaDescriptor $desired,
        Ct1ManagedTableIdentity $identity,
        ProviderCapabilityProfile $capabilities,
    ): ProviderMigrationPreview {
        $this->assertAligned($plan, $desired, $identity);

        if ($plan->blocked
            || $plan->recoveryRequired
            || $plan->risk->value >= MigrationRisk::R3->value
            || $plan->findings !== []
        ) {
            throw new InvalidArgumentException('Blocked, recovery-required or R3/R4 Custom Tables plans cannot compile provider statements.');
        }

        $columns = $this->columnMap($desired->columns);
        $indexes = $this->indexMap($desired->indexes);
        $statements = [];

        foreach ($plan->operations as $operation) {
            if ($operation->blocked
                || $operation->recoveryRequired
                || $operation->risk->value >= MigrationRisk::R3->value
            ) {
                throw new InvalidArgumentException('Blocked or high-risk Custom Tables operations cannot compile provider statements.');
            }

            $statements[] = $this->statement(
                $operation,
                $desired,
                $identity,
                $capabilities,
                $columns,
                $indexes,
            );
        }

        return ProviderMigrationPreview::create(
            planFingerprint: $plan->fingerprint,
            capabilities: $capabilities,
            physicalTableName: $identity->physicalName,
            statements: $statements,
        );
    }

    private function assertAligned(
        MigrationPlan $plan,
        TableSchemaDescriptor $desired,
        Ct1ManagedTableIdentity $identity,
    ): void {
        if ($plan->tableKey !== $desired->tableKey
            || $identity->tableKey !== $desired->tableKey
            || $plan->targetDefinitionId !== $desired->definitionId
            || $identity->definitionId !== $desired->definitionId
            || $plan->targetRevision !== $desired->revision
            || $plan->targetSchemaVersion !== $desired->desiredSchemaVersion
            || $plan->targetCompatibilityFingerprint !== $desired->compatibilityFingerprint
        ) {
            throw new InvalidArgumentException('Custom Tables provider compilation inputs are not the same trusted migration generation.');
        }
    }

    /**
     * @param array<string,TableColumnDescriptor> $columns
     * @param array<string,TableIndexDescriptor> $indexes
     * @throws JsonException
     */
    private function statement(
        MigrationOperation $operation,
        TableSchemaDescriptor $desired,
        Ct1ManagedTableIdentity $identity,
        ProviderCapabilityProfile $capabilities,
        array $columns,
        array $indexes,
    ): ProviderStatementPreview {
        $table = $this->quoteIdentifier($identity->physicalName);

        return match ($operation->type) {
            'create_table' => $this->preview(
                $operation,
                $this->createTableSql($desired, $table),
                'not_applicable',
                'not_applicable',
            ),
            'add_column' => $this->addColumnPreview($operation, $table, $capabilities, $columns),
            'alter_column_default' => $this->alterDefaultPreview($operation, $table, $capabilities, $columns),
            'add_index', 'add_unique_constraint' => $this->addIndexPreview($operation, $table, $indexes),
            default => throw new InvalidArgumentException(
                'Custom Tables provider compiler does not certify this MigrationPlan operation family in bounded V1.',
            ),
        };
    }

    /** @throws JsonException */
    private function addColumnPreview(
        MigrationOperation $operation,
        string $table,
        ProviderCapabilityProfile $capabilities,
        array $columns,
    ): ProviderStatementPreview {
        $column = $this->targetColumn($operation, $columns);
        $classification = !$column->autoIncrement && $capabilities->instantAddColumnCandidate
            ? 'instant_candidate'
            : 'provider_default';
        $lock = $classification === 'instant_candidate' ? 'metadata_lock_expected' : 'provider_dependent';

        return $this->preview(
            $operation,
            'ALTER TABLE ' . $table
                . ' ADD COLUMN ' . $this->quoteIdentifier($column->key)
                . ' ' . $this->columnDefinition($column),
            $classification,
            $lock,
        );
    }

    /** @throws JsonException */
    private function alterDefaultPreview(
        MigrationOperation $operation,
        string $table,
        ProviderCapabilityProfile $capabilities,
        array $columns,
    ): ProviderStatementPreview {
        $column = $this->targetColumn($operation, $columns);
        $sql = 'ALTER TABLE ' . $table . ' ALTER COLUMN ' . $this->quoteIdentifier($column->key) . ' ';
        $sql .= $column->hasDefault
            ? 'SET DEFAULT ' . $this->defaultLiteral($column)
            : 'DROP DEFAULT';
        $classification = $capabilities->instantDefaultChangeCandidate
            ? 'instant_candidate'
            : 'provider_default';

        return $this->preview(
            $operation,
            $sql,
            $classification,
            $classification === 'instant_candidate' ? 'metadata_lock_expected' : 'provider_dependent',
        );
    }

    /** @throws JsonException */
    private function addIndexPreview(
        MigrationOperation $operation,
        string $table,
        array $indexes,
    ): ProviderStatementPreview {
        $index = $this->targetIndex($operation, $indexes);
        $columns = implode(', ', array_map($this->quoteIdentifier(...), $index->columns));
        $keyword = $index->unique ? 'ADD UNIQUE INDEX ' : 'ADD INDEX ';

        if (($operation->type === 'add_unique_constraint') !== $index->unique) {
            throw new InvalidArgumentException('Custom Tables MigrationPlan index operation does not match the target descriptor.');
        }

        return $this->preview(
            $operation,
            'ALTER TABLE ' . $table . ' ' . $keyword . $this->quoteIdentifier($index->key) . ' (' . $columns . ')',
            'provider_default',
            'provider_dependent',
        );
    }

    private function createTableSql(TableSchemaDescriptor $desired, string $table): string
    {
        $parts = [];
        foreach ($desired->columns as $column) {
            $parts[] = $this->quoteIdentifier($column->key) . ' ' . $this->columnDefinition($column);
        }

        $parts[] = 'PRIMARY KEY (' . implode(', ', array_map($this->quoteIdentifier(...), $desired->primaryKey)) . ')';
        foreach ($desired->indexes as $index) {
            $columns = implode(', ', array_map($this->quoteIdentifier(...), $index->columns));
            $parts[] = ($index->unique ? 'UNIQUE KEY ' : 'KEY ')
                . $this->quoteIdentifier($index->key)
                . ' (' . $columns . ')';
        }

        return 'CREATE TABLE ' . $table . ' (' . implode(', ', $parts) . ') ENGINE=InnoDB';
    }

    private function columnDefinition(TableColumnDescriptor $column): string
    {
        $definition = match ($column->type) {
            'bigint' => 'BIGINT',
            'integer' => 'INT',
            'decimal' => sprintf('DECIMAL(%d,%d)', (int) $column->precision, (int) ($column->scale ?? 0)),
            'varchar' => sprintf('VARCHAR(%d)', (int) $column->length),
            'text' => 'TEXT',
            'datetime' => 'DATETIME',
            'boolean' => 'TINYINT(1)',
            'json' => 'JSON',
            default => throw new InvalidArgumentException('Custom Tables provider compiler encountered an unsupported target column type.'),
        };

        $definition .= $column->nullable ? ' NULL' : ' NOT NULL';
        if ($column->hasDefault) {
            $definition .= ' DEFAULT ' . $this->defaultLiteral($column);
        }
        if ($column->autoIncrement) {
            $definition .= ' AUTO_INCREMENT';
        }

        return $definition;
    }

    private function defaultLiteral(TableColumnDescriptor $column): string
    {
        if (!$column->hasDefault) {
            throw new InvalidArgumentException('Custom Tables provider compiler requested a missing target default.');
        }
        if ($column->defaultValue === null) {
            return 'NULL';
        }

        return match ($column->type) {
            'bigint', 'integer' => is_int($column->defaultValue)
                ? (string) $column->defaultValue
                : throw new InvalidArgumentException('Custom Tables integer target default is invalid.'),
            'decimal' => is_string($column->defaultValue)
                && preg_match('/^-?(?:0|[1-9]\d*)(?:\.\d+)?$/', $column->defaultValue) === 1
                    ? $column->defaultValue
                    : throw new InvalidArgumentException('Custom Tables decimal target default is invalid.'),
            'boolean' => is_bool($column->defaultValue)
                ? ($column->defaultValue ? '1' : '0')
                : throw new InvalidArgumentException('Custom Tables boolean target default is invalid.'),
            'varchar', 'datetime' => is_string($column->defaultValue)
                ? $this->quoteLiteral($column->defaultValue)
                : throw new InvalidArgumentException('Custom Tables string target default is invalid.'),
            default => throw new InvalidArgumentException('Custom Tables provider compiler does not support defaults for this target type.'),
        };
    }

    private function quoteIdentifier(string $identifier): string
    {
        if ($identifier === ''
            || strlen($identifier) > 64
            || preg_match('/^[A-Za-z0-9_]+$/', $identifier) !== 1
        ) {
            throw new InvalidArgumentException('Custom Tables provider compiler received an untrusted identifier.');
        }

        return '`' . $identifier . '`';
    }

    private function quoteLiteral(string $value): string
    {
        if (strlen($value) > 191
            || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value) === 1
        ) {
            throw new InvalidArgumentException('Custom Tables provider compiler refuses an unsafe string default literal.');
        }

        return "'" . str_replace("'", "''", $value) . "'";
    }

    /** @param array<string,TableColumnDescriptor> $columns */
    private function targetColumn(MigrationOperation $operation, array $columns): TableColumnDescriptor
    {
        if (preg_match('/^columns\.([a-z][a-z0-9_]{0,63})$/', $operation->target, $matches) !== 1) {
            throw new InvalidArgumentException('Custom Tables provider compiler received an invalid column operation target.');
        }
        $column = $columns[$matches[1]] ?? null;
        if (!$column instanceof TableColumnDescriptor) {
            throw new InvalidArgumentException('Custom Tables provider compiler column target is absent from the trusted descriptor.');
        }

        return $column;
    }

    /** @param array<string,TableIndexDescriptor> $indexes */
    private function targetIndex(MigrationOperation $operation, array $indexes): TableIndexDescriptor
    {
        if (preg_match('/^indexes\.([a-z][a-z0-9_]{0,63})$/', $operation->target, $matches) !== 1) {
            throw new InvalidArgumentException('Custom Tables provider compiler received an invalid index operation target.');
        }
        $index = $indexes[$matches[1]] ?? null;
        if (!$index instanceof TableIndexDescriptor) {
            throw new InvalidArgumentException('Custom Tables provider compiler index target is absent from the trusted descriptor.');
        }

        return $index;
    }

    /** @param list<TableColumnDescriptor> $columns @return array<string,TableColumnDescriptor> */
    private function columnMap(array $columns): array
    {
        $map = [];
        foreach ($columns as $column) {
            $map[$column->key] = $column;
        }
        return $map;
    }

    /** @param list<TableIndexDescriptor> $indexes @return array<string,TableIndexDescriptor> */
    private function indexMap(array $indexes): array
    {
        $map = [];
        foreach ($indexes as $index) {
            $map[$index->key] = $index;
        }
        return $map;
    }

    /** @throws JsonException */
    private function preview(
        MigrationOperation $operation,
        string $sql,
        string $algorithm,
        string $lock,
    ): ProviderStatementPreview {
        return ProviderStatementPreview::create(
            operationType: $operation->type,
            target: $operation->target,
            sql: $sql,
            algorithmClassification: $algorithm,
            lockClassification: $lock,
            risk: $operation->risk,
        );
    }
}
