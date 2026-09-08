<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Schema;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;
use WPEssential\Modules\CustomTables\Definition\TableColumnDescriptor;
use WPEssential\Modules\CustomTables\Definition\TableIndexDescriptor;

final readonly class ObservedTableSchemaNormalizer
{
    /** @var list<string> */
    private const ROOT_KEYS = ['exists', 'columns', 'primary_key', 'indexes', 'charset_collation'];

    /** @var list<string> */
    private const COLUMN_KEYS = [
        'key',
        'type',
        'nullable',
        'has_default',
        'default',
        'auto_increment',
        'length',
        'precision',
        'scale',
    ];

    /** @var list<string> */
    private const INDEX_KEYS = ['key', 'columns', 'unique'];

    /**
     * Accepts server-resolved, read-only physical metadata. This class performs no database access.
     *
     * @param array<string,mixed> $metadata
     * @throws JsonException
     */
    public function normalize(string $tableKey, array $metadata): ObservedTableSchema
    {
        $this->assertIdentifier($tableKey, 48, 'Observed Custom Tables logical table key');
        if (array_is_list($metadata)) {
            throw new InvalidArgumentException('Observed Custom Tables metadata must be an object/map.');
        }
        $this->assertKnownKeys($metadata, self::ROOT_KEYS, 'Observed Custom Tables metadata');

        $exists = $metadata['exists'] ?? null;
        if (!is_bool($exists)) {
            throw new InvalidArgumentException('Observed Custom Tables metadata requires an explicit exists boolean.');
        }

        if (!$exists) {
            foreach (['columns', 'primary_key', 'indexes'] as $key) {
                if (isset($metadata[$key]) && $metadata[$key] !== []) {
                    throw new InvalidArgumentException('Missing Custom Tables observation cannot contain schema members.');
                }
            }
            $state = [
                'table_key' => $tableKey,
                'exists' => false,
                'columns' => [],
                'primary_key' => [],
                'indexes' => [],
                'charset_collation' => 'unknown',
                'findings' => [],
            ];

            return new ObservedTableSchema(
                tableKey: $tableKey,
                exists: false,
                columns: [],
                primaryKey: [],
                indexes: [],
                charsetCollation: 'unknown',
                findings: [],
                fingerprint: $this->fingerprint($state),
            );
        }

        $findings = [];
        $columns = $this->columns($metadata['columns'] ?? [], $findings);
        $columnMap = [];
        foreach ($columns as $column) {
            $columnMap[$column->key] = $column;
        }

        $primaryKey = $this->references(
            $metadata['primary_key'] ?? [],
            $columnMap,
            'primary_key',
            $findings,
        );
        $indexes = $this->indexes($metadata['indexes'] ?? [], $columnMap, $findings);

        $charsetCollation = $metadata['charset_collation'] ?? 'inherit';
        if (!is_string($charsetCollation) || $charsetCollation !== 'inherit') {
            $findings[] = new SchemaObservationFinding(
                'charset_collation_drift',
                'charset_collation',
                is_scalar($charsetCollation) ? (string) $charsetCollation : get_debug_type($charsetCollation),
            );
            $charsetCollation = 'unknown';
        }

        usort(
            $columns,
            static fn (TableColumnDescriptor $a, TableColumnDescriptor $b): int => strcmp($a->key, $b->key),
        );
        usort(
            $indexes,
            static fn (TableIndexDescriptor $a, TableIndexDescriptor $b): int => strcmp($a->key, $b->key),
        );
        usort(
            $findings,
            static fn (SchemaObservationFinding $a, SchemaObservationFinding $b): int =>
                [$a->code, $a->path, $a->observed] <=> [$b->code, $b->path, $b->observed],
        );

        $state = [
            'table_key' => $tableKey,
            'exists' => true,
            'columns' => array_map(
                static fn (TableColumnDescriptor $column): array => $column->canonical(),
                $columns,
            ),
            'primary_key' => $primaryKey,
            'indexes' => array_map(
                static fn (TableIndexDescriptor $index): array => $index->canonical(),
                $indexes,
            ),
            'charset_collation' => $charsetCollation,
            'findings' => array_map(
                static fn (SchemaObservationFinding $finding): array => $finding->canonical(),
                $findings,
            ),
        ];

        return new ObservedTableSchema(
            tableKey: $tableKey,
            exists: true,
            columns: $columns,
            primaryKey: $primaryKey,
            indexes: $indexes,
            charsetCollation: $charsetCollation,
            findings: $findings,
            fingerprint: $this->fingerprint($state),
        );
    }

    /**
     * @param mixed $value
     * @param list<SchemaObservationFinding> $findings
     * @return list<TableColumnDescriptor>
     */
    private function columns(mixed $value, array &$findings): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > 128) {
            throw new InvalidArgumentException('Observed Custom Tables columns must be a bounded list.');
        }

        $columns = [];
        $seen = [];
        foreach ($value as $offset => $input) {
            if (!is_array($input) || array_is_list($input)) {
                throw new InvalidArgumentException('Observed Custom Tables column metadata must be object/maps.');
            }
            $this->assertKnownKeys($input, self::COLUMN_KEYS, 'Observed Custom Tables column');
            $key = $input['key'] ?? null;
            $this->assertIdentifier($key, 64, 'Observed Custom Tables column key');
            if (isset($seen[$key])) {
                $findings[] = new SchemaObservationFinding('duplicate_column', 'columns.' . $key, $key);
                continue;
            }
            $seen[$key] = true;

            $type = $input['type'] ?? null;
            if (!is_string($type)
                || !in_array($type, ['bigint', 'integer', 'decimal', 'varchar', 'text', 'datetime', 'boolean', 'json'], true)
            ) {
                $findings[] = new SchemaObservationFinding(
                    'unsupported_column_type',
                    'columns.' . $key . '.type',
                    is_scalar($type) ? (string) $type : get_debug_type($type),
                );
                continue;
            }

            $nullable = $input['nullable'] ?? false;
            $autoIncrement = $input['auto_increment'] ?? false;
            if (!is_bool($nullable) || !is_bool($autoIncrement)) {
                $findings[] = new SchemaObservationFinding(
                    'unsupported_column_shape',
                    'columns.' . $key,
                    'boolean flags',
                );
                continue;
            }

            $length = $this->optionalInt($input, 'length');
            $precision = $this->optionalInt($input, 'precision');
            $scale = $this->optionalInt($input, 'scale');
            if (!$this->supportsTypeShape($type, $length, $precision, $scale)) {
                $findings[] = new SchemaObservationFinding(
                    'unsupported_column_shape',
                    'columns.' . $key,
                    $type,
                );
                continue;
            }

            $hasDefault = $input['has_default'] ?? array_key_exists('default', $input);
            if (!is_bool($hasDefault)) {
                throw new InvalidArgumentException('Observed Custom Tables has_default must be boolean.');
            }
            $default = $input['default'] ?? null;
            if (!$hasDefault) {
                $default = null;
            }
            if (!$this->supportsDefault($type, $default, $nullable, $hasDefault, $length)) {
                $findings[] = new SchemaObservationFinding(
                    'unsupported_column_default',
                    'columns.' . $key . '.default',
                    $this->boundedScalar($default),
                );
                continue;
            }
            if ($autoIncrement && ($nullable || $hasDefault || !in_array($type, ['bigint', 'integer'], true))) {
                $findings[] = new SchemaObservationFinding(
                    'unsupported_auto_increment',
                    'columns.' . $key . '.auto_increment',
                    'true',
                );
                continue;
            }

            $columns[] = new TableColumnDescriptor(
                key: $key,
                type: $type,
                nullable: $nullable,
                hasDefault: $hasDefault,
                defaultValue: $default,
                autoIncrement: $autoIncrement,
                length: $length,
                precision: $precision,
                scale: $scale,
            );
        }

        return $columns;
    }

    /**
     * @param mixed $value
     * @param array<string,TableColumnDescriptor> $columnMap
     * @param list<SchemaObservationFinding> $findings
     * @return list<string>
     */
    private function references(mixed $value, array $columnMap, string $path, array &$findings): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > 8) {
            throw new InvalidArgumentException('Observed Custom Tables key references must be a bounded list.');
        }
        $references = [];
        foreach ($value as $column) {
            if (!is_string($column)) {
                throw new InvalidArgumentException('Observed Custom Tables key references must be strings.');
            }
            if (!isset($columnMap[$column])) {
                $findings[] = new SchemaObservationFinding('unknown_column_reference', $path, $column);
                continue;
            }
            if (in_array($column, $references, true)) {
                $findings[] = new SchemaObservationFinding('duplicate_column_reference', $path, $column);
                continue;
            }
            $references[] = $column;
        }
        return $references;
    }

    /**
     * @param mixed $value
     * @param array<string,TableColumnDescriptor> $columnMap
     * @param list<SchemaObservationFinding> $findings
     * @return list<TableIndexDescriptor>
     */
    private function indexes(mixed $value, array $columnMap, array &$findings): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > 32) {
            throw new InvalidArgumentException('Observed Custom Tables indexes must be a bounded list.');
        }
        $indexes = [];
        $seen = [];
        foreach ($value as $input) {
            if (!is_array($input) || array_is_list($input)) {
                throw new InvalidArgumentException('Observed Custom Tables index metadata must be object/maps.');
            }
            $this->assertKnownKeys($input, self::INDEX_KEYS, 'Observed Custom Tables index');
            $key = $input['key'] ?? null;
            $this->assertIdentifier($key, 64, 'Observed Custom Tables index key');
            if (isset($seen[$key])) {
                $findings[] = new SchemaObservationFinding('duplicate_index', 'indexes.' . $key, $key);
                continue;
            }
            $seen[$key] = true;
            $columns = $this->references($input['columns'] ?? [], $columnMap, 'indexes.' . $key, $findings);
            $unique = $input['unique'] ?? false;
            if (!is_bool($unique)) {
                throw new InvalidArgumentException('Observed Custom Tables index unique setting must be boolean.');
            }
            if ($columns === []) {
                $findings[] = new SchemaObservationFinding('unsupported_index', 'indexes.' . $key, 'no supported columns');
                continue;
            }
            $indexes[] = new TableIndexDescriptor($key, $columns, $unique);
        }
        return $indexes;
    }

    private function supportsTypeShape(string $type, ?int $length, ?int $precision, ?int $scale): bool
    {
        if ($type === 'varchar') {
            return $length !== null && $length >= 1 && $length <= 191 && $precision === null && $scale === null;
        }
        if ($type === 'decimal') {
            $effectiveScale = $scale ?? 0;
            return $precision !== null
                && $precision >= 1
                && $precision <= 38
                && $effectiveScale >= 0
                && $effectiveScale <= $precision
                && $length === null;
        }
        return $length === null && $precision === null && $scale === null;
    }

    private function supportsDefault(
        string $type,
        mixed $default,
        bool $nullable,
        bool $hasDefault,
        ?int $length,
    ): bool {
        if (!$hasDefault) {
            return true;
        }
        if ($default === null) {
            return $nullable;
        }
        return match ($type) {
            'bigint', 'integer' => is_int($default),
            'decimal' => is_string($default) && preg_match('/^-?(?:0|[1-9]\d*)(?:\.\d+)?$/', $default) === 1,
            'varchar' => is_string($default) && strlen($default) <= (int) $length,
            'datetime' => is_string($default)
                && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $default) === 1,
            'boolean' => is_bool($default),
            'text', 'json' => false,
            default => false,
        };
    }

    /** @param array<string,mixed> $input */
    private function optionalInt(array $input, string $key): ?int
    {
        if (!array_key_exists($key, $input)) {
            return null;
        }
        $value = $input[$key];
        if (!is_int($value)) {
            throw new InvalidArgumentException('Observed Custom Tables numeric shape options must be integers.');
        }
        return $value;
    }

    private function boundedScalar(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_scalar($value)) {
            return substr((string) $value, 0, 255);
        }
        return get_debug_type($value);
    }

    /** @param array<string,mixed> $input @param list<string> $allowed */
    private function assertKnownKeys(array $input, array $allowed, string $label): void
    {
        foreach (array_keys($input) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException($label . ' contains an unsupported key.');
            }
        }
    }

    private function assertIdentifier(mixed $value, int $maxLength, string $label): void
    {
        $maxTail = $maxLength - 1;
        if (!is_string($value) || preg_match('/^[a-z][a-z0-9_]{0,' . $maxTail . '}$/', $value) !== 1) {
            throw new InvalidArgumentException($label . ' must be a canonical lowercase identifier.');
        }
    }

    /** @param array<string,mixed> $state @throws JsonException */
    private function fingerprint(array $state): string
    {
        return hash(
            'sha256',
            json_encode($state, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        );
    }
}
