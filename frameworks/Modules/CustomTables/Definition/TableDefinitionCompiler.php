<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Definition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class TableDefinitionCompiler
{
    public const OWNER_SURFACE_ID = 7;
    public const TYPE = 'table';
    public const SCHEMA_VERSION = 1;

    /** @var list<string> */
    private const PAYLOAD_KEYS = [
        'key',
        'label',
        'storage_mode',
        'scope',
        'desired_schema_version',
        'columns',
        'primary_key',
        'indexes',
        'charset_collation',
        'data_classification',
    ];

    /** @var list<string> */
    private const COLUMN_KEYS = [
        'key',
        'type',
        'nullable',
        'default',
        'auto_increment',
        'length',
        'precision',
        'scale',
    ];

    /** @var list<string> */
    private const INDEX_KEYS = ['key', 'columns', 'unique'];

    /** @throws JsonException */
    public function compile(Definition $definition): TableSchemaDescriptor
    {
        if ($definition->type !== self::TYPE || $definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Definition is not owned by the canonical Custom Tables surface.');
        }
        if ($definition->schemaVersion !== self::SCHEMA_VERSION) {
            throw new InvalidArgumentException('Custom Tables definition schema version is unsupported.');
        }
        if ($definition->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Only Published Custom Tables definitions may compile.');
        }
        if ($definition->dependencies !== []) {
            throw new InvalidArgumentException('Custom Tables definition dependencies are outside the bounded V1 contract.');
        }

        $payload = $definition->payload;
        if (array_is_list($payload)) {
            throw new InvalidArgumentException('Custom Tables definition payload must be an object/map.');
        }
        $this->assertKnownKeys($payload, self::PAYLOAD_KEYS, 'Custom Tables definition payload');

        $tableKey = $this->identifier($payload['key'] ?? null, 48, 'Custom Tables logical table key');
        $label = $this->label($payload['label'] ?? null);
        $storageMode = $payload['storage_mode'] ?? 'managed';
        if ($storageMode !== 'managed') {
            throw new InvalidArgumentException('Custom Tables V1 storage_mode must be managed.');
        }
        $scope = $payload['scope'] ?? 'site';
        if ($scope !== 'site') {
            throw new InvalidArgumentException('Custom Tables V1 scope must be site.');
        }
        $desiredSchemaVersion = $payload['desired_schema_version'] ?? 1;
        if (!is_int($desiredSchemaVersion) || $desiredSchemaVersion < 1 || $desiredSchemaVersion > 2147483647) {
            throw new InvalidArgumentException('Custom Tables desired_schema_version must be a positive bounded integer.');
        }

        $columns = $this->columns($payload['columns'] ?? null);
        $columnMap = [];
        foreach ($columns as $column) {
            $columnMap[$column->key] = $column;
        }

        $primaryKey = $this->columnReferences(
            $payload['primary_key'] ?? null,
            $columnMap,
            'Custom Tables primary_key',
        );
        $this->assertAutoIncrementPrimaryKey($columns, $primaryKey);
        $indexes = $this->indexes($payload['indexes'] ?? [], $columnMap);

        $charsetCollation = $payload['charset_collation'] ?? 'inherit';
        if ($charsetCollation !== 'inherit') {
            throw new InvalidArgumentException('Custom Tables V1 charset_collation must be inherit.');
        }
        $dataClassification = $payload['data_classification'] ?? 'internal';
        if (!is_string($dataClassification)
            || !in_array($dataClassification, ['public', 'internal', 'confidential', 'restricted'], true)
        ) {
            throw new InvalidArgumentException('Custom Tables data_classification is unsupported.');
        }

        $semanticState = [
            'table_key' => $tableKey,
            'label' => $label,
            'storage_mode' => $storageMode,
            'scope' => $scope,
            'desired_schema_version' => $desiredSchemaVersion,
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
            'data_classification' => $dataClassification,
        ];

        return new TableSchemaDescriptor(
            definitionId: $definition->id,
            revision: $definition->revision,
            tableKey: $tableKey,
            label: $label,
            storageMode: $storageMode,
            scope: $scope,
            desiredSchemaVersion: $desiredSchemaVersion,
            columns: $columns,
            primaryKey: $primaryKey,
            indexes: $indexes,
            charsetCollation: $charsetCollation,
            dataClassification: $dataClassification,
            compatibilityFingerprint: hash(
                'sha256',
                json_encode($semanticState, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ),
        );
    }

    /** @return list<TableColumnDescriptor> */
    private function columns(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value) || $value === [] || count($value) > 128) {
            throw new InvalidArgumentException('Custom Tables columns must be a bounded non-empty list.');
        }

        $columns = [];
        $seen = [];
        foreach ($value as $input) {
            if (!is_array($input) || array_is_list($input)) {
                throw new InvalidArgumentException('Each Custom Tables column must be an object/map.');
            }
            $this->assertKnownKeys($input, self::COLUMN_KEYS, 'Custom Tables column');

            $key = $this->identifier($input['key'] ?? null, 64, 'Custom Tables column key');
            if (isset($seen[$key])) {
                throw new InvalidArgumentException('Custom Tables column keys must be unique.');
            }
            $seen[$key] = true;

            $type = $input['type'] ?? null;
            if (!is_string($type)
                || !in_array($type, ['bigint', 'integer', 'decimal', 'varchar', 'text', 'datetime', 'boolean', 'json'], true)
            ) {
                throw new InvalidArgumentException('Custom Tables column type is outside the bounded V1 vocabulary.');
            }
            $nullable = $input['nullable'] ?? false;
            $autoIncrement = $input['auto_increment'] ?? false;
            if (!is_bool($nullable) || !is_bool($autoIncrement)) {
                throw new InvalidArgumentException('Custom Tables nullable and auto_increment settings must be boolean.');
            }

            $length = $this->optionalInt($input, 'length');
            $precision = $this->optionalInt($input, 'precision');
            $scale = $this->optionalInt($input, 'scale');
            $this->assertTypeShape($type, $length, $precision, $scale);

            $hasDefault = array_key_exists('default', $input);
            $default = $hasDefault ? $this->normalizeDefault($type, $input['default'], $nullable) : null;
            if ($autoIncrement && $hasDefault) {
                throw new InvalidArgumentException('Auto-increment Custom Tables columns cannot declare defaults.');
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
     * @param array<string,TableColumnDescriptor> $columnMap
     * @return list<TableIndexDescriptor>
     */
    private function indexes(mixed $value, array $columnMap): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > 32) {
            throw new InvalidArgumentException('Custom Tables indexes must be a bounded list.');
        }

        $indexes = [];
        $seenKeys = [];
        $seenSemantics = [];
        foreach ($value as $input) {
            if (!is_array($input) || array_is_list($input)) {
                throw new InvalidArgumentException('Each Custom Tables index must be an object/map.');
            }
            $this->assertKnownKeys($input, self::INDEX_KEYS, 'Custom Tables index');
            $key = $this->identifier($input['key'] ?? null, 64, 'Custom Tables index key');
            if ($key === 'primary') {
                throw new InvalidArgumentException('Custom Tables secondary index key cannot be primary.');
            }
            if (isset($seenKeys[$key])) {
                throw new InvalidArgumentException('Custom Tables index keys must be unique.');
            }
            $seenKeys[$key] = true;

            $columns = $this->columnReferences($input['columns'] ?? null, $columnMap, 'Custom Tables index columns');
            $unique = $input['unique'] ?? false;
            if (!is_bool($unique)) {
                throw new InvalidArgumentException('Custom Tables index unique setting must be boolean.');
            }
            $semanticKey = ($unique ? 'u:' : 'n:') . implode(',', $columns);
            if (isset($seenSemantics[$semanticKey])) {
                throw new InvalidArgumentException('Custom Tables duplicate index semantics are not allowed.');
            }
            $seenSemantics[$semanticKey] = true;
            $indexes[] = new TableIndexDescriptor($key, $columns, $unique);
        }

        return $indexes;
    }

    /**
     * @param array<string,TableColumnDescriptor> $columnMap
     * @return list<string>
     */
    private function columnReferences(mixed $value, array $columnMap, string $label): array
    {
        if (!is_array($value) || !array_is_list($value) || $value === [] || count($value) > 8) {
            throw new InvalidArgumentException($label . ' must be a bounded non-empty list.');
        }
        $references = [];
        foreach ($value as $column) {
            if (!is_string($column) || !isset($columnMap[$column])) {
                throw new InvalidArgumentException($label . ' must reference declared columns only.');
            }
            if (in_array($column, $references, true)) {
                throw new InvalidArgumentException($label . ' entries must be unique.');
            }
            $references[] = $column;
        }
        return $references;
    }

    /** @param list<TableColumnDescriptor> $columns @param list<string> $primaryKey */
    private function assertAutoIncrementPrimaryKey(array $columns, array $primaryKey): void
    {
        $autoIncrement = [];
        foreach ($columns as $column) {
            if ($column->autoIncrement) {
                $autoIncrement[] = $column->key;
            }
        }
        if (count($autoIncrement) > 1) {
            throw new InvalidArgumentException('Custom Tables V1 permits at most one auto-increment column.');
        }
        if ($autoIncrement !== [] && !in_array($autoIncrement[0], $primaryKey, true)) {
            throw new InvalidArgumentException('Custom Tables auto-increment column must belong to the primary key.');
        }
    }

    private function assertTypeShape(string $type, ?int $length, ?int $precision, ?int $scale): void
    {
        if ($type === 'varchar') {
            if ($length === null || $length < 1 || $length > 191 || $precision !== null || $scale !== null) {
                throw new InvalidArgumentException('Custom Tables varchar requires length 1..191 and forbids precision/scale.');
            }
            return;
        }
        if ($type === 'decimal') {
            if ($precision === null || $precision < 1 || $precision > 38) {
                throw new InvalidArgumentException('Custom Tables decimal precision must be between 1 and 38.');
            }
            $effectiveScale = $scale ?? 0;
            if ($effectiveScale < 0 || $effectiveScale > $precision || $length !== null) {
                throw new InvalidArgumentException('Custom Tables decimal scale must be between 0 and precision and forbids length.');
            }
            return;
        }
        if ($length !== null || $precision !== null || $scale !== null) {
            throw new InvalidArgumentException('Custom Tables selected column type does not accept length/precision/scale options.');
        }
    }

    private function normalizeDefault(string $type, mixed $value, bool $nullable): mixed
    {
        if ($value === null) {
            if (!$nullable) {
                throw new InvalidArgumentException('Non-nullable Custom Tables columns cannot declare a null default.');
            }
            return null;
        }

        return match ($type) {
            'bigint', 'integer' => is_int($value)
                ? $value
                : throw new InvalidArgumentException('Integer-family Custom Tables defaults must be integers.'),
            'decimal' => $this->decimalDefault($value),
            'varchar' => is_string($value) && strlen($value) <= 191
                ? $value
                : throw new InvalidArgumentException('Custom Tables varchar defaults must be bounded strings.'),
            'datetime' => is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value) === 1
                ? $value
                : throw new InvalidArgumentException('Custom Tables datetime defaults must be literal UTC-compatible date-time strings.'),
            'boolean' => is_bool($value)
                ? $value
                : throw new InvalidArgumentException('Custom Tables boolean defaults must be boolean.'),
            'text', 'json' => throw new InvalidArgumentException('Custom Tables text/json defaults are deferred from bounded V1.'),
            default => throw new InvalidArgumentException('Custom Tables default type is unsupported.'),
        };
    }

    private function decimalDefault(mixed $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }
        if (!is_string($value) || preg_match('/^-?(?:0|[1-9]\d*)(?:\.\d+)?$/', $value) !== 1) {
            throw new InvalidArgumentException('Custom Tables decimal defaults must be canonical decimal literals.');
        }
        return $value;
    }

    /** @param array<string,mixed> $input */
    private function optionalInt(array $input, string $key): ?int
    {
        if (!array_key_exists($key, $input)) {
            return null;
        }
        $value = $input[$key];
        if (!is_int($value)) {
            throw new InvalidArgumentException('Custom Tables numeric column options must be integers.');
        }
        return $value;
    }

    private function identifier(mixed $value, int $maxLength, string $label): string
    {
        $maxTail = $maxLength - 1;
        if (!is_string($value) || preg_match('/^[a-z][a-z0-9_]{0,' . $maxTail . '}$/', $value) !== 1) {
            throw new InvalidArgumentException($label . ' must be a canonical lowercase identifier.');
        }
        return $value;
    }

    private function label(mixed $value): string
    {
        if (!is_string($value) || $value === '' || strlen($value) > 160) {
            throw new InvalidArgumentException('Custom Tables label must be a bounded non-empty string.');
        }
        if (str_contains($value, '<') || str_contains($value, '>') || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
            throw new InvalidArgumentException('Custom Tables label must be bounded plain text.');
        }
        return $value;
    }

    /** @param array<string,mixed> $value @param list<string> $allowed */
    private function assertKnownKeys(array $value, array $allowed, string $label): void
    {
        foreach (array_keys($value) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException($label . ' contains an unsupported key.');
            }
        }
    }
}
