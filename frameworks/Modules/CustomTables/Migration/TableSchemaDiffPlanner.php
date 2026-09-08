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
use WPEssential\Modules\CustomTables\Schema\ObservedTableSchema;
use WPEssential\Modules\CustomTables\Schema\SchemaObservationFinding;

final readonly class TableSchemaDiffPlanner
{
    /** @throws JsonException */
    public function plan(TableSchemaDescriptor $desired, ObservedTableSchema $observed): MigrationPlan
    {
        if ($desired->tableKey !== $observed->tableKey) {
            throw new InvalidArgumentException('Custom Tables desired and observed logical table keys must match.');
        }

        $operations = [];
        $findings = [];
        foreach ($observed->findings as $finding) {
            $findings[] = $this->observationFinding($finding);
        }

        if ($observed->findings === [] && !$observed->exists) {
            $operations[] = new MigrationOperation(
                type: 'create_table',
                target: $desired->tableKey,
                risk: MigrationRisk::R1,
            );
        } elseif ($observed->findings === []) {
            $this->planExistingTable($desired, $observed, $operations, $findings);
        }

        $this->sortOperations($operations);
        $this->sortFindings($findings);

        [$risk, $blocked, $recoveryRequired] = $this->aggregatePlanState($operations, $findings);

        $semantic = [
            'table_key' => $desired->tableKey,
            'source_observed_fingerprint' => $observed->fingerprint,
            'target_definition_id' => $desired->definitionId,
            'target_revision' => $desired->revision,
            'target_schema_version' => $desired->desiredSchemaVersion,
            'target_compatibility_fingerprint' => $desired->compatibilityFingerprint,
            'operations' => array_map(
                static fn (MigrationOperation $operation): array => $operation->canonical(),
                $operations,
            ),
            'findings' => array_map(
                static fn (MigrationFinding $finding): array => $finding->canonical(),
                $findings,
            ),
            'risk' => 'R' . $risk->value,
            'blocked' => $blocked,
            'recovery_required' => $recoveryRequired,
        ];
        $fingerprint = hash(
            'sha256',
            json_encode($semantic, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        );

        return new MigrationPlan(
            id: $this->uuidFromHash($fingerprint),
            tableKey: $desired->tableKey,
            sourceObservedFingerprint: $observed->fingerprint,
            targetDefinitionId: $desired->definitionId,
            targetRevision: $desired->revision,
            targetSchemaVersion: $desired->desiredSchemaVersion,
            targetCompatibilityFingerprint: $desired->compatibilityFingerprint,
            operations: $operations,
            findings: $findings,
            risk: $risk,
            blocked: $blocked,
            recoveryRequired: $recoveryRequired,
            fingerprint: $fingerprint,
        );
    }

    /**
     * @param list<MigrationOperation> $operations
     * @param list<MigrationFinding> $findings
     */
    private function planExistingTable(
        TableSchemaDescriptor $desired,
        ObservedTableSchema $observed,
        array &$operations,
        array &$findings,
    ): void {
        $desiredColumns = $this->columnMap($desired->columns);
        $observedColumns = $this->columnMap($observed->columns);
        $columnKeys = array_unique(array_merge(array_keys($desiredColumns), array_keys($observedColumns)));
        sort($columnKeys, SORT_STRING);

        foreach ($columnKeys as $key) {
            $target = $desiredColumns[$key] ?? null;
            $source = $observedColumns[$key] ?? null;

            if ($target instanceof TableColumnDescriptor && !$source instanceof TableColumnDescriptor) {
                $operations[] = $this->addColumnOperation($target);
                continue;
            }

            if ($source instanceof TableColumnDescriptor && !$target instanceof TableColumnDescriptor) {
                $operations[] = new MigrationOperation(
                    type: 'drop_column',
                    target: 'columns.' . $key,
                    risk: MigrationRisk::R4,
                    preconditions: ['dependency_analysis_required', 'verified_restore_point_required'],
                    recoveryRequired: true,
                    blocked: true,
                );
                continue;
            }

            if ($source instanceof TableColumnDescriptor && $target instanceof TableColumnDescriptor) {
                $this->planColumnChange($source, $target, $operations, $findings);
            }
        }

        if ($desired->primaryKey !== $observed->primaryKey) {
            $operations[] = new MigrationOperation(
                type: 'replace_primary_key',
                target: 'primary_key',
                risk: MigrationRisk::R3,
                preconditions: [
                    'dependency_analysis_required',
                    'primary_key_values_unique',
                    'primary_key_values_non_null',
                    'verified_restore_point_required',
                ],
                recoveryRequired: true,
                blocked: true,
            );
        }

        $this->planIndexes($desired->indexes, $observed->indexes, $operations, $findings);
    }

    /**
     * @param list<MigrationOperation> $operations
     * @param list<MigrationFinding> $findings
     */
    private function planColumnChange(
        TableColumnDescriptor $source,
        TableColumnDescriptor $target,
        array &$operations,
        array &$findings,
    ): void {
        if ($source->autoIncrement !== $target->autoIncrement) {
            $findings[] = new MigrationFinding(
                code: 'auto_increment_change_deferred',
                path: 'columns.' . $target->key . '.auto_increment',
                risk: MigrationRisk::R3,
                blocking: true,
                detail: 'Auto-increment mutation requires a later provider/recovery contract.',
            );
            return;
        }

        if (!$this->sameTypeShape($source, $target)) {
            $operations[] = $this->typeChangeOperation($source, $target);
        }

        if ($source->nullable !== $target->nullable) {
            $operations[] = new MigrationOperation(
                type: 'alter_column_nullability',
                target: 'columns.' . $target->key,
                risk: $target->nullable ? MigrationRisk::R1 : MigrationRisk::R2,
                preconditions: $target->nullable ? [] : ['no_null_values'],
            );
        }

        if ($source->hasDefault !== $target->hasDefault || $source->defaultValue !== $target->defaultValue) {
            $operations[] = new MigrationOperation(
                type: 'alter_column_default',
                target: 'columns.' . $target->key,
                risk: MigrationRisk::R1,
            );
        }
    }

    private function addColumnOperation(TableColumnDescriptor $column): MigrationOperation
    {
        $safeAdditive = $column->nullable || $column->hasDefault || $column->autoIncrement;

        return new MigrationOperation(
            type: 'add_column',
            target: 'columns.' . $column->key,
            risk: $safeAdditive ? MigrationRisk::R1 : MigrationRisk::R2,
            preconditions: $safeAdditive ? [] : ['table_empty_or_backfill_required'],
            blocked: !$safeAdditive,
        );
    }

    private function typeChangeOperation(
        TableColumnDescriptor $source,
        TableColumnDescriptor $target,
    ): MigrationOperation {
        $path = 'columns.' . $target->key;

        if ($source->type !== $target->type) {
            return $this->blockedTypeChange(
                $path,
                ['conversion_strategy_required', 'verified_restore_point_required'],
            );
        }

        if ($target->type === 'varchar') {
            $sourceLength = (int) $source->length;
            $targetLength = (int) $target->length;

            if ($targetLength >= $sourceLength) {
                return new MigrationOperation(
                    type: 'alter_column_type',
                    target: $path,
                    risk: MigrationRisk::R2,
                    preconditions: ['provider_capability_required'],
                );
            }

            return $this->blockedTypeChange(
                $path,
                ['max_length_fits_target', 'verified_restore_point_required'],
            );
        }

        if ($target->type === 'decimal') {
            return $this->decimalTypeChangeOperation($source, $target, $path);
        }

        return $this->blockedTypeChange(
            $path,
            ['provider_capability_required', 'verified_restore_point_required'],
        );
    }

    private function decimalTypeChangeOperation(
        TableColumnDescriptor $source,
        TableColumnDescriptor $target,
        string $path,
    ): MigrationOperation {
        $sourcePrecision = (int) $source->precision;
        $sourceScale = $source->scale ?? 0;
        $targetPrecision = (int) $target->precision;
        $targetScale = $target->scale ?? 0;

        $sourceIntegerDigits = $sourcePrecision - $sourceScale;
        $targetIntegerDigits = $targetPrecision - $targetScale;
        $preservesIntegerCapacity = $targetIntegerDigits >= $sourceIntegerDigits;
        $preservesFractionalCapacity = $targetScale >= $sourceScale;

        if ($preservesIntegerCapacity && $preservesFractionalCapacity) {
            return new MigrationOperation(
                type: 'alter_column_type',
                target: $path,
                risk: MigrationRisk::R2,
                preconditions: ['provider_capability_required'],
            );
        }

        return $this->blockedTypeChange(
            $path,
            ['decimal_values_fit_target', 'verified_restore_point_required'],
        );
    }

    /** @param list<string> $preconditions */
    private function blockedTypeChange(string $path, array $preconditions): MigrationOperation
    {
        return new MigrationOperation(
            type: 'alter_column_type',
            target: $path,
            risk: MigrationRisk::R3,
            preconditions: $preconditions,
            recoveryRequired: true,
            blocked: true,
        );
    }

    /**
     * @param list<TableIndexDescriptor> $desired
     * @param list<TableIndexDescriptor> $observed
     * @param list<MigrationOperation> $operations
     * @param list<MigrationFinding> $findings
     */
    private function planIndexes(
        array $desired,
        array $observed,
        array &$operations,
        array &$findings,
    ): void {
        $targetIndexes = $this->indexMap($desired);
        $sourceIndexes = $this->indexMap($observed);
        $keys = array_unique(array_merge(array_keys($targetIndexes), array_keys($sourceIndexes)));
        sort($keys, SORT_STRING);

        foreach ($keys as $key) {
            $target = $targetIndexes[$key] ?? null;
            $source = $sourceIndexes[$key] ?? null;

            if ($target instanceof TableIndexDescriptor && !$source instanceof TableIndexDescriptor) {
                $operations[] = new MigrationOperation(
                    type: $target->unique ? 'add_unique_constraint' : 'add_index',
                    target: 'indexes.' . $key,
                    risk: $target->unique ? MigrationRisk::R2 : MigrationRisk::R1,
                    preconditions: $target->unique ? ['no_duplicate_values'] : [],
                );
                continue;
            }

            if ($source instanceof TableIndexDescriptor && !$target instanceof TableIndexDescriptor) {
                $operations[] = new MigrationOperation(
                    type: $source->unique ? 'drop_unique_constraint' : 'drop_index',
                    target: 'indexes.' . $key,
                    risk: MigrationRisk::R3,
                    preconditions: ['dependency_analysis_required'],
                    recoveryRequired: true,
                    blocked: true,
                );
                continue;
            }

            if ($source instanceof TableIndexDescriptor
                && $target instanceof TableIndexDescriptor
                && $source->canonical() !== $target->canonical()
            ) {
                $findings[] = new MigrationFinding(
                    code: 'index_semantics_change_deferred',
                    path: 'indexes.' . $key,
                    risk: MigrationRisk::R3,
                    blocking: true,
                    detail: 'Index replacement requires dependency and provider execution evidence.',
                );
            }
        }
    }

    private function sameTypeShape(TableColumnDescriptor $source, TableColumnDescriptor $target): bool
    {
        return $source->type === $target->type
            && $source->length === $target->length
            && $source->precision === $target->precision
            && ($source->scale ?? 0) === ($target->scale ?? 0);
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

    private function observationFinding(SchemaObservationFinding $finding): MigrationFinding
    {
        return new MigrationFinding(
            code: 'observed_' . $finding->code,
            path: $finding->path,
            risk: MigrationRisk::R3,
            blocking: true,
            detail: $finding->observed,
        );
    }

    /**
     * @param list<MigrationOperation> $operations
     * @param list<MigrationFinding> $findings
     * @return array{MigrationRisk,bool,bool}
     */
    private function aggregatePlanState(array $operations, array $findings): array
    {
        $risk = MigrationRisk::R0;
        $blocked = false;
        $recoveryRequired = false;

        foreach ($operations as $operation) {
            $risk = MigrationRisk::max($risk, $operation->risk);
            $blocked = $blocked || $operation->blocked;
            $recoveryRequired = $recoveryRequired || $operation->recoveryRequired;
        }

        foreach ($findings as $finding) {
            $risk = MigrationRisk::max($risk, $finding->risk);
            $blocked = $blocked || $finding->blocking;
            $recoveryRequired = $recoveryRequired || $finding->risk->value >= MigrationRisk::R3->value;
        }

        return [$risk, $blocked, $recoveryRequired];
    }

    /** @param list<MigrationOperation> $operations */
    private function sortOperations(array &$operations): void
    {
        $order = [
            'create_table' => 10,
            'add_column' => 20,
            'alter_column_type' => 30,
            'alter_column_nullability' => 40,
            'alter_column_default' => 50,
            'replace_primary_key' => 60,
            'add_index' => 70,
            'add_unique_constraint' => 80,
            'drop_index' => 90,
            'drop_unique_constraint' => 100,
            'drop_column' => 110,
        ];

        usort(
            $operations,
            static fn (MigrationOperation $a, MigrationOperation $b): int =>
                [$order[$a->type], $a->target] <=> [$order[$b->type], $b->target],
        );
    }

    /** @param list<MigrationFinding> $findings */
    private function sortFindings(array &$findings): void
    {
        usort(
            $findings,
            static fn (MigrationFinding $a, MigrationFinding $b): int =>
                [$a->code, $a->path, $a->detail] <=> [$b->code, $b->path, $b->detail],
        );
    }

    private function uuidFromHash(string $hash): string
    {
        $hex = substr($hash, 0, 32);
        $hex[12] = '5';
        $variant = hexdec($hex[16]);
        $hex[16] = dechex(($variant & 0x3) | 0x8);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        );
    }
}
