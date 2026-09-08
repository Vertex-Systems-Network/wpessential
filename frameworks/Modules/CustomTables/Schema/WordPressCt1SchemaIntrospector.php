<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Schema;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;
use RuntimeException;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;

final class WordPressCt1SchemaIntrospector
{
    private const SERVER_MYSQL = 'mysql';
    private const SERVER_MARIADB = 'mariadb';

    private readonly Ct1ManagedTableIdentityResolver $identityResolver;
    private readonly ObservedTableSchemaNormalizer $normalizer;

    public function __construct(private readonly object $wpdb)
    {
        foreach (['prepare', 'get_results'] as $method) {
            if (!method_exists($this->wpdb, $method)) {
                throw new InvalidArgumentException('CT1 schema introspector requires wpdb metadata-read methods.');
            }
        }

        $this->identityResolver = new Ct1ManagedTableIdentityResolver($this->wpdb);
        $this->normalizer = new ObservedTableSchemaNormalizer();
    }

    /** @throws JsonException */
    public function observe(TableSchemaDescriptor $descriptor): ObservedTableSchema
    {
        $identity = $this->identityResolver->resolve($descriptor);
        $tableRows = $this->readRows(
            'SELECT TABLE_NAME AS table_name, TABLE_COLLATION AS table_collation '
            . 'FROM INFORMATION_SCHEMA.TABLES '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s LIMIT 2',
            $identity->physicalName,
        );

        if ($tableRows === []) {
            return $this->normalizer->normalize($identity->tableKey, ['exists' => false]);
        }
        if (count($tableRows) !== 1) {
            throw new RuntimeException('CT1 schema observation resolved an ambiguous physical table identity.');
        }

        $tableName = $this->requiredString($tableRows[0], 'table_name');
        if (!hash_equals($identity->physicalName, $tableName)) {
            throw new RuntimeException('CT1 schema observation returned a different physical table identity.');
        }

        $serverFlavor = $this->serverFlavor();
        $columnRows = $this->readRows(
            'SELECT COLUMN_NAME AS column_name, DATA_TYPE AS data_type, COLUMN_TYPE AS column_type, '
            . 'IS_NULLABLE AS is_nullable, COLUMN_DEFAULT AS column_default, EXTRA AS extra, '
            . 'CHARACTER_MAXIMUM_LENGTH AS character_maximum_length, NUMERIC_PRECISION AS numeric_precision, '
            . 'NUMERIC_SCALE AS numeric_scale '
            . 'FROM INFORMATION_SCHEMA.COLUMNS '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s ORDER BY ORDINAL_POSITION',
            $identity->physicalName,
        );
        if ($columnRows === [] || count($columnRows) > 128) {
            throw new RuntimeException('CT1 schema observation returned an invalid column metadata count.');
        }

        $indexRows = $this->readRows(
            'SELECT INDEX_NAME AS index_name, NON_UNIQUE AS non_unique, SEQ_IN_INDEX AS seq_in_index, '
            . 'COLUMN_NAME AS column_name, SUB_PART AS sub_part, INDEX_TYPE AS index_type '
            . 'FROM INFORMATION_SCHEMA.STATISTICS '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s ORDER BY INDEX_NAME, SEQ_IN_INDEX',
            $identity->physicalName,
        );

        [$primaryKey, $indexes] = $this->indexes($indexRows);
        $tableCollation = $this->requiredString($tableRows[0], 'table_collation');
        $expectedCollation = $this->expectedCollation();

        return $this->normalizer->normalize(
            $identity->tableKey,
            [
                'exists' => true,
                'columns' => array_map(
                    fn (array $row): array => $this->column($row, $serverFlavor),
                    $columnRows,
                ),
                'primary_key' => $primaryKey,
                'indexes' => $indexes,
                'charset_collation' => strcasecmp($tableCollation, $expectedCollation) === 0
                    ? 'inherit'
                    : $tableCollation,
            ],
        );
    }

    /** @return array<string,mixed> */
    private function column(array $row, string $serverFlavor): array
    {
        $key = $this->requiredString($row, 'column_name');
        $dataType = strtolower($this->requiredString($row, 'data_type'));
        $columnType = strtolower($this->requiredString($row, 'column_type'));
        $extra = strtolower(trim($this->nullableString($row, 'extra') ?? ''));
        $isNullable = strtoupper($this->requiredString($row, 'is_nullable'));
        if (!in_array($isNullable, ['YES', 'NO'], true)) {
            throw new RuntimeException('CT1 schema observation returned unsupported nullability metadata.');
        }
        $nullable = $isNullable === 'YES';
        $autoIncrement = $extra === 'auto_increment';

        $type = $this->boundedType($dataType, $columnType, $extra);
        $column = [
            'key' => $key,
            'type' => $type,
            'nullable' => $nullable,
            'auto_increment' => $autoIncrement,
        ];

        if ($type === 'varchar') {
            $column['length'] = $this->requiredPositiveInt($row, 'character_maximum_length');
        } elseif ($type === 'decimal') {
            $column['precision'] = $this->requiredPositiveInt($row, 'numeric_precision');
            $column['scale'] = $this->nullableNonNegativeInt($row, 'numeric_scale') ?? 0;
        }

        if (!$autoIncrement) {
            $rawDefault = $row['column_default'] ?? null;
            if ($rawDefault !== null || $nullable) {
                $column['has_default'] = true;
                $column['default'] = $this->defaultValue($type, $rawDefault, $serverFlavor);
            }
        }

        return $column;
    }

    private function boundedType(string $dataType, string $columnType, string $extra): string
    {
        $safeType = preg_replace('/[^a-z0-9_]/', '_', $dataType);
        if (!is_string($safeType) || $safeType === '') {
            $safeType = 'unknown';
        }

        if ($extra !== '' && $extra !== 'auto_increment') {
            return 'unsupported_extra_' . $safeType;
        }
        if (preg_match('/\bunsigned\b/', $columnType) === 1) {
            return 'unsupported_unsigned_' . $safeType;
        }

        return match ($dataType) {
            'bigint' => 'bigint',
            'int', 'integer' => 'integer',
            'tinyint' => preg_match('/^tinyint\(1\)$/', $columnType) === 1
                ? 'boolean'
                : 'unsupported_tinyint',
            'decimal' => 'decimal',
            'varchar' => 'varchar',
            'text' => 'text',
            'datetime' => preg_match('/^datetime(?:\(0\))?$/', $columnType) === 1
                ? 'datetime'
                : 'unsupported_datetime_precision',
            'json' => 'json',
            default => 'unsupported_' . $safeType,
        };
    }

    private function defaultValue(string $type, mixed $value, string $serverFlavor): mixed
    {
        if ($value === null) {
            return null;
        }
        if (!is_scalar($value)) {
            return $value;
        }

        $string = (string) $value;
        if ($serverFlavor === self::SERVER_MARIADB) {
            if ($string === 'NULL') {
                return null;
            }

            $literal = $this->mariaDbLiteral($string);
            if ($literal !== null) {
                $string = $literal;
            } elseif (in_array($type, ['varchar', 'datetime'], true)) {
                return ['unsupported_default_expression' => substr($string, 0, 191)];
            }
        }

        if (in_array($type, ['bigint', 'integer'], true)) {
            if (preg_match('/^-?(?:0|[1-9]\d*)$/', $string) !== 1) {
                return $string;
            }
            $integer = (int) $string;
            return (string) $integer === $string ? $integer : $string;
        }
        if ($type === 'boolean') {
            return match ($string) {
                '0' => false,
                '1' => true,
                default => $string,
            };
        }

        return $string;
    }

    private function mariaDbLiteral(string $value): ?string
    {
        $length = strlen($value);
        if ($length < 2 || $value[0] !== "'" || $value[$length - 1] !== "'") {
            return null;
        }

        $body = substr($value, 1, -1);
        $decoded = '';
        $bodyLength = strlen($body);
        for ($offset = 0; $offset < $bodyLength; ++$offset) {
            $character = $body[$offset];
            if ($character === '\\') {
                return null;
            }
            if ($character !== "'") {
                $decoded .= $character;
                continue;
            }
            if ($offset + 1 >= $bodyLength || $body[$offset + 1] !== "'") {
                return null;
            }
            $decoded .= "'";
            ++$offset;
        }

        return $decoded;
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return array{list<string>,list<array{key:string,columns:list<string>,unique:bool}>}
     */
    private function indexes(array $rows): array
    {
        if (count($rows) > 256) {
            throw new RuntimeException('CT1 schema observation returned too many index metadata rows.');
        }

        /** @var array<string,array{columns:array<int,string>,unique:bool}> $groups */
        $groups = [];
        foreach ($rows as $row) {
            $indexName = $this->requiredString($row, 'index_name');
            $columnName = $this->requiredString($row, 'column_name');
            $sequence = $this->requiredPositiveInt($row, 'seq_in_index');
            $nonUnique = $this->requiredNonNegativeInt($row, 'non_unique');
            $indexType = strtoupper($this->requiredString($row, 'index_type'));

            if ($indexType !== 'BTREE') {
                throw new RuntimeException('CT1 schema observation encountered an unsupported physical index type.');
            }
            if (($row['sub_part'] ?? null) !== null) {
                throw new RuntimeException('CT1 schema observation encountered an unsupported prefix index.');
            }
            if ($nonUnique !== 0 && $nonUnique !== 1) {
                throw new RuntimeException('CT1 schema observation returned invalid uniqueness metadata.');
            }
            if ($sequence > 8) {
                throw new RuntimeException('CT1 schema observation encountered an index wider than bounded V1.');
            }

            $key = $indexName === 'PRIMARY' ? 'PRIMARY' : $indexName;
            if (!isset($groups[$key])) {
                $groups[$key] = ['columns' => [], 'unique' => $nonUnique === 0];
            }
            if ($groups[$key]['unique'] !== ($nonUnique === 0) || isset($groups[$key]['columns'][$sequence])) {
                throw new RuntimeException('CT1 schema observation returned inconsistent index metadata.');
            }
            $groups[$key]['columns'][$sequence] = $columnName;
        }

        $primaryKey = [];
        $indexes = [];
        ksort($groups, SORT_STRING);
        foreach ($groups as $key => $group) {
            ksort($group['columns'], SORT_NUMERIC);
            $expectedSequence = 1;
            foreach (array_keys($group['columns']) as $sequence) {
                if ($sequence !== $expectedSequence) {
                    throw new RuntimeException('CT1 schema observation returned non-contiguous index sequence metadata.');
                }
                ++$expectedSequence;
            }

            $columns = array_values($group['columns']);
            if ($key === 'PRIMARY') {
                $primaryKey = $columns;
                continue;
            }
            $indexes[] = [
                'key' => $key,
                'columns' => $columns,
                'unique' => $group['unique'],
            ];
        }

        return [$primaryKey, $indexes];
    }

    private function serverFlavor(): string
    {
        if (method_exists($this->wpdb, 'db_server_info')) {
            $serverInfo = $this->wpdb->db_server_info();
        } elseif (method_exists($this->wpdb, 'db_version')) {
            $serverInfo = $this->wpdb->db_version();
        } else {
            throw new RuntimeException('CT1 schema observation cannot resolve the trusted database provider identity.');
        }

        if (!is_string($serverInfo) || trim($serverInfo) === '') {
            throw new RuntimeException('CT1 schema observation returned an invalid database provider identity.');
        }

        return str_contains(strtolower($serverInfo), 'mariadb')
            ? self::SERVER_MARIADB
            : self::SERVER_MYSQL;
    }

    private function expectedCollation(): string
    {
        $configured = property_exists($this->wpdb, 'collate') ? trim((string) $this->wpdb->collate) : '';
        if ($configured !== '') {
            if (preg_match('/^[A-Za-z0-9_]+$/', $configured) !== 1) {
                throw new RuntimeException('Trusted WordPress database collation is invalid.');
            }
            return $configured;
        }

        $rows = $this->readRows(
            'SELECT DEFAULT_COLLATION_NAME AS default_collation '
            . 'FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = DATABASE() LIMIT 2',
        );
        if (count($rows) !== 1) {
            throw new RuntimeException('CT1 schema observation could not resolve the current database collation.');
        }
        return $this->requiredString($rows[0], 'default_collation');
    }

    /** @return list<array<string,mixed>> */
    private function readRows(string $query, mixed ...$args): array
    {
        if (preg_match('/^\s*SELECT\b/i', $query) !== 1) {
            throw new RuntimeException('CT1 schema introspection permits metadata SELECT statements only.');
        }

        $prepared = $args === [] ? $query : $this->wpdb->prepare($query, ...$args);
        if (!is_string($prepared) || $prepared === '') {
            throw new RuntimeException('wpdb failed to prepare a CT1 metadata query.');
        }

        $rows = $this->wpdb->get_results($prepared, 'ARRAY_A');
        $lastError = property_exists($this->wpdb, 'last_error') ? trim((string) $this->wpdb->last_error) : '';
        if ($lastError !== '') {
            throw new RuntimeException('wpdb failed to read CT1 schema metadata: ' . $lastError);
        }
        if (!is_array($rows)) {
            throw new RuntimeException('wpdb returned an invalid CT1 schema metadata result set.');
        }

        $result = [];
        foreach ($rows as $row) {
            if (is_object($row)) {
                $row = (array) $row;
            }
            if (!is_array($row)) {
                throw new RuntimeException('wpdb returned an invalid CT1 schema metadata row.');
            }
            $result[] = $row;
        }

        return $result;
    }

    /** @param array<string,mixed> $row */
    private function requiredString(array $row, string $key): string
    {
        $value = $row[$key] ?? null;
        if (!is_string($value) || $value === '') {
            throw new RuntimeException('CT1 schema observation is missing required string metadata.');
        }
        return $value;
    }

    /** @param array<string,mixed> $row */
    private function nullableString(array $row, string $key): ?string
    {
        $value = $row[$key] ?? null;
        if ($value === null) {
            return null;
        }
        if (!is_string($value)) {
            throw new RuntimeException('CT1 schema observation returned invalid string metadata.');
        }
        return $value;
    }

    /** @param array<string,mixed> $row */
    private function requiredPositiveInt(array $row, string $key): int
    {
        $value = $this->integerMetadata($row[$key] ?? null);
        if ($value < 1) {
            throw new RuntimeException('CT1 schema observation requires positive integer metadata.');
        }
        return $value;
    }

    /** @param array<string,mixed> $row */
    private function requiredNonNegativeInt(array $row, string $key): int
    {
        $value = $this->integerMetadata($row[$key] ?? null);
        if ($value < 0) {
            throw new RuntimeException('CT1 schema observation requires non-negative integer metadata.');
        }
        return $value;
    }

    /** @param array<string,mixed> $row */
    private function nullableNonNegativeInt(array $row, string $key): ?int
    {
        if (!array_key_exists($key, $row) || $row[$key] === null) {
            return null;
        }
        return $this->requiredNonNegativeInt($row, $key);
    }

    private function integerMetadata(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (!is_string($value) || preg_match('/^\d+$/', $value) !== 1) {
            throw new RuntimeException('CT1 schema observation returned invalid integer metadata.');
        }
        $integer = (int) $value;
        if ((string) $integer !== ltrim($value, '0') && !preg_match('/^0+$/', $value)) {
            throw new RuntimeException('CT1 schema observation integer metadata exceeds the supported range.');
        }
        return $integer;
    }
}
