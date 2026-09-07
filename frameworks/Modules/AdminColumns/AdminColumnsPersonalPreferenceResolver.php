<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class AdminColumnsPersonalPreferenceResolver
{
    public const CONTRACT_VERSION = 1;

    private const MAX_COLUMNS = 100;
    private const MAX_FILTERS = 20;
    private const MAX_SORTS = 5;
    private const MAX_LIST_VALUES = 20;
    private const MAX_STRING_BYTES = 1024;
    private const DENSITIES = ['compact', 'comfortable'];
    private const DIRECTIONS = ['asc', 'desc'];
    private const OPERATORS = ['eq', 'neq', 'contains', 'in', 'not_in'];
    private const INPUT_KEYS = [
        'chosen_view_id',
        'temporary_sort',
        'temporary_filters',
        'hidden_columns',
        'density',
        'saved_filter_state',
    ];

    /**
     * Resolve bounded user-scoped presentation state against one canonical View projection.
     *
     * This method is deliberately side-effect free. It performs no persistence,
     * authorization or Query execution and never mutates the supplied View data.
     *
     * @param list<array{key:string,enabled?:bool}> $columns
     * @param array<string,mixed> $preference
     * @return array<string,mixed>
     */
    public function resolve(
        string $viewId,
        int $viewRevision,
        array $columns,
        array $preference,
    ): array {
        $this->uuid($viewId, 'Personal preference View id');
        if ($viewRevision < 1) {
            throw new InvalidArgumentException('Personal preference View revision must be positive.');
        }
        $columnKeys = $this->columnKeys($columns);
        $this->assertKnownKeys($preference, self::INPUT_KEYS, 'Personal preference');

        $chosenViewId = $preference['chosen_view_id'] ?? $viewId;
        if (!is_string($chosenViewId)) {
            throw new InvalidArgumentException('Chosen View id must be a UUID string.');
        }
        $this->uuid($chosenViewId, 'Chosen View id');
        if ($chosenViewId !== $viewId) {
            throw new InvalidArgumentException('Chosen View id does not match the canonical View being resolved.');
        }

        $sort = $this->sorts($preference['temporary_sort'] ?? [], $columnKeys, 'Temporary sort');
        $filters = $this->filters($preference['temporary_filters'] ?? [], $columnKeys, 'Temporary filters');
        $hiddenColumns = $this->hiddenColumns($preference['hidden_columns'] ?? [], $columnKeys);

        $density = $preference['density'] ?? 'comfortable';
        if (!is_string($density) || !in_array($density, self::DENSITIES, true)) {
            throw new InvalidArgumentException('Personal density is not supported.');
        }

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'view_id' => $viewId,
            'view_revision' => $viewRevision,
            'chosen_view_id' => $chosenViewId,
            'temporary_sort' => $sort,
            'temporary_filters' => $filters,
            'hidden_columns' => $hiddenColumns,
            'density' => $density,
            'saved_filter_state' => $this->savedFilterState(
                $preference['saved_filter_state'] ?? null,
                $columnKeys,
            ),
        ];
    }

    /**
     * @param list<array{key:string,enabled?:bool}> $columns
     * @return array<string,true>
     */
    private function columnKeys(array $columns): array
    {
        if (!array_is_list($columns) || $columns === [] || count($columns) > self::MAX_COLUMNS) {
            throw new InvalidArgumentException('Canonical View columns must be a non-empty bounded list.');
        }

        $keys = [];
        foreach ($columns as $index => $column) {
            if (!is_array($column) || array_is_list($column)) {
                throw new InvalidArgumentException(sprintf('Canonical View column %d is malformed.', $index));
            }
            $key = $column['key'] ?? null;
            if (!is_string($key) || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $key) !== 1) {
                throw new InvalidArgumentException(sprintf('Canonical View column %d key is malformed.', $index));
            }
            if (($column['enabled'] ?? true) !== true) {
                continue;
            }
            if (isset($keys[$key])) {
                throw new InvalidArgumentException('Canonical View enabled column keys must be unique.');
            }
            $keys[$key] = true;
        }

        if ($keys === []) {
            throw new InvalidArgumentException('Canonical View has no enabled columns for personal state.');
        }

        return $keys;
    }

    /**
     * @param array<string,true> $columnKeys
     * @return list<array{column_key:string,direction:string}>
     */
    private function sorts(mixed $value, array $columnKeys, string $label): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > self::MAX_SORTS) {
            throw new InvalidArgumentException($label . ' must be a bounded ordered list.');
        }

        $resolved = [];
        $seen = [];
        foreach ($value as $index => $sort) {
            if (!is_array($sort) || array_is_list($sort)) {
                throw new InvalidArgumentException(sprintf('%s item %d is malformed.', $label, $index));
            }
            $this->assertKnownKeys($sort, ['column_key', 'direction'], sprintf('%s item %d', $label, $index));
            $columnKey = $sort['column_key'] ?? null;
            $direction = $sort['direction'] ?? null;
            if (!is_string($columnKey) || !isset($columnKeys[$columnKey])) {
                throw new InvalidArgumentException(sprintf('%s item %d references an unavailable column.', $label, $index));
            }
            if (isset($seen[$columnKey])) {
                throw new InvalidArgumentException($label . ' cannot sort one column more than once.');
            }
            if (!is_string($direction) || !in_array($direction, self::DIRECTIONS, true)) {
                throw new InvalidArgumentException(sprintf('%s item %d direction is unsupported.', $label, $index));
            }
            $seen[$columnKey] = true;
            $resolved[] = ['column_key' => $columnKey, 'direction' => $direction];
        }

        return $resolved;
    }

    /**
     * @param array<string,true> $columnKeys
     * @return list<array{column_key:string,operator:string,value:mixed}>
     */
    private function filters(mixed $value, array $columnKeys, string $label): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > self::MAX_FILTERS) {
            throw new InvalidArgumentException($label . ' must be a bounded ordered list.');
        }

        $resolved = [];
        foreach ($value as $index => $filter) {
            if (!is_array($filter) || array_is_list($filter)) {
                throw new InvalidArgumentException(sprintf('%s item %d is malformed.', $label, $index));
            }
            $this->assertKnownKeys($filter, ['column_key', 'operator', 'value'], sprintf('%s item %d', $label, $index));
            $columnKey = $filter['column_key'] ?? null;
            $operator = $filter['operator'] ?? null;
            if (!is_string($columnKey) || !isset($columnKeys[$columnKey])) {
                throw new InvalidArgumentException(sprintf('%s item %d references an unavailable column.', $label, $index));
            }
            if (!is_string($operator) || !in_array($operator, self::OPERATORS, true)) {
                throw new InvalidArgumentException(sprintf('%s item %d operator is unsupported.', $label, $index));
            }
            if (!array_key_exists('value', $filter)) {
                throw new InvalidArgumentException(sprintf('%s item %d value is missing.', $label, $index));
            }
            $resolved[] = [
                'column_key' => $columnKey,
                'operator' => $operator,
                'value' => $this->filterValue($filter['value'], sprintf('%s item %d value', $label, $index)),
            ];
        }

        return $resolved;
    }

    private function filterValue(mixed $value, string $label): mixed
    {
        if (is_array($value)) {
            if (!array_is_list($value) || count($value) > self::MAX_LIST_VALUES) {
                throw new InvalidArgumentException($label . ' list is over budget or malformed.');
            }
            return array_map(fn (mixed $item): mixed => $this->scalarValue($item, $label), $value);
        }

        return $this->scalarValue($value, $label);
    }

    private function scalarValue(mixed $value, string $label): mixed
    {
        if ($value === null || is_bool($value) || is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            if (!is_finite($value)) {
                throw new InvalidArgumentException($label . ' cannot contain a non-finite float.');
            }
            return $value;
        }
        if (is_string($value)) {
            if (strlen($value) > self::MAX_STRING_BYTES || preg_match('//u', $value) !== 1) {
                throw new InvalidArgumentException($label . ' string is invalid or over budget.');
            }
            return $value;
        }

        throw new InvalidArgumentException($label . ' must contain only scalar, null or bounded scalar-list values.');
    }

    /** @param array<string,true> $columnKeys @return list<string> */
    private function hiddenColumns(mixed $value, array $columnKeys): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > self::MAX_COLUMNS) {
            throw new InvalidArgumentException('Personal hidden_columns must be a bounded list.');
        }

        $result = [];
        $seen = [];
        foreach ($value as $columnKey) {
            if (!is_string($columnKey) || !isset($columnKeys[$columnKey])) {
                throw new InvalidArgumentException('Personal hidden_columns references an unavailable column.');
            }
            if (isset($seen[$columnKey])) {
                throw new InvalidArgumentException('Personal hidden_columns must contain unique keys.');
            }
            $seen[$columnKey] = true;
            $result[] = $columnKey;
        }
        return $result;
    }

    /**
     * @param array<string,true> $columnKeys
     * @return array{key:string,filters:list<array{column_key:string,operator:string,value:mixed}>,sort:list<array{column_key:string,direction:string}>}|null
     */
    private function savedFilterState(mixed $value, array $columnKeys): ?array
    {
        if ($value === null) {
            return null;
        }
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException('Personal saved_filter_state must be null or an object/map.');
        }
        $this->assertKnownKeys($value, ['key', 'filters', 'sort'], 'Personal saved_filter_state');
        $key = $value['key'] ?? null;
        if (!is_string($key) || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $key) !== 1) {
            throw new InvalidArgumentException('Personal saved_filter_state key must be a stable machine key.');
        }

        return [
            'key' => $key,
            'filters' => $this->filters($value['filters'] ?? [], $columnKeys, 'Personal saved-filter filters'),
            'sort' => $this->sorts($value['sort'] ?? [], $columnKeys, 'Personal saved-filter sort'),
        ];
    }

    /** @param array<string,mixed> $map @param list<string> $allowed */
    private function assertKnownKeys(array $map, array $allowed, string $label): void
    {
        foreach (array_keys($map) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new InvalidArgumentException(sprintf('%s contains unsupported key "%s".', $label, (string) $key));
            }
        }
    }

    private function uuid(string $value, string $label): void
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException($label . ' must be a lowercase RFC 4122 UUID.');
        }
    }
}
