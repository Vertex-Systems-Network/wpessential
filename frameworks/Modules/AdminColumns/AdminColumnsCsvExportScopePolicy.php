<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class AdminColumnsCsvExportScopePolicy
{
    public const CONTRACT_VERSION = 1;

    private const MAX_SELECTED_ROWS = 100;
    private const MAX_COLUMNS = 100;
    private const SCOPES = ['current_page', 'selected_rows', 'all_matching'];
    private const REQUEST_KEYS = [
        'scope',
        'selected_row_ids',
        'selected_columns',
        'respect_filters',
        'respect_sort',
    ];

    /**
     * Normalize explicit export-scope intent against canonical enabled Columns and
     * scopes explicitly advertised by the target integration.
     *
     * This policy performs no authorization, Query execution, row resolution,
     * CSV serialization or transport work.
     *
     * @param array<string,mixed> $request
     * @param list<string> $enabledColumnKeys
     * @param list<string> $supportedScopes
     * @return array{
     *   contract_version:int,
     *   scope:string,
     *   selected_row_ids:list<int>,
     *   selected_columns:list<string>,
     *   respect_filters:bool,
     *   respect_sort:bool
     * }
     */
    public function normalize(array $request, array $enabledColumnKeys, array $supportedScopes): array
    {
        $this->assertKnownKeys($request, self::REQUEST_KEYS, 'CSV export scope request');
        foreach (self::REQUEST_KEYS as $key) {
            if (!array_key_exists($key, $request)) {
                throw new InvalidArgumentException('CSV export scope request must explicitly declare every V1 field.');
            }
        }

        $canonicalColumns = $this->machineList(
            $enabledColumnKeys,
            'Enabled export Column keys',
            self::MAX_COLUMNS,
            false,
        );
        $supported = $this->scopeList($supportedScopes);

        $scope = $request['scope'];
        if (!is_string($scope) || !in_array($scope, self::SCOPES, true)) {
            throw new InvalidArgumentException('CSV export scope is unsupported.');
        }
        if (!in_array($scope, $supported, true)) {
            throw new InvalidArgumentException('CSV export scope is not advertised by the target capability contract.');
        }

        $selectedRows = $this->positiveIntList(
            $request['selected_row_ids'],
            'CSV export selected row IDs',
            self::MAX_SELECTED_ROWS,
        );
        if ($scope === 'selected_rows' && $selectedRows === []) {
            throw new InvalidArgumentException('CSV export selected_rows scope requires at least one row ID.');
        }
        if ($scope !== 'selected_rows' && $selectedRows !== []) {
            throw new InvalidArgumentException('CSV export row IDs are only valid for selected_rows scope.');
        }

        $selectedColumns = $this->machineList(
            $request['selected_columns'],
            'CSV export selected Column keys',
            self::MAX_COLUMNS,
            false,
        );
        $canonical = array_fill_keys($canonicalColumns, true);
        foreach ($selectedColumns as $columnKey) {
            if (!isset($canonical[$columnKey])) {
                throw new InvalidArgumentException('CSV export selected Column is not an enabled canonical Column.');
            }
        }

        $respectFilters = $request['respect_filters'];
        $respectSort = $request['respect_sort'];
        if (!is_bool($respectFilters) || !is_bool($respectSort)) {
            throw new InvalidArgumentException('CSV export filter/sort behavior must be explicit booleans.');
        }

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'scope' => $scope,
            'selected_row_ids' => $selectedRows,
            'selected_columns' => $selectedColumns,
            'respect_filters' => $respectFilters,
            'respect_sort' => $respectSort,
        ];
    }

    /** @return list<string> */
    private function scopeList(array $value): array
    {
        if (!array_is_list($value) || count($value) > count(self::SCOPES)) {
            throw new InvalidArgumentException('Supported CSV export scopes must be a bounded ordered list.');
        }

        $result = [];
        $seen = [];
        foreach ($value as $scope) {
            if (!is_string($scope) || !in_array($scope, self::SCOPES, true)) {
                throw new InvalidArgumentException('Supported CSV export scopes contain an unknown scope.');
            }
            if (isset($seen[$scope])) {
                throw new InvalidArgumentException('Supported CSV export scopes must be unique.');
            }
            $seen[$scope] = true;
            $result[] = $scope;
        }
        return $result;
    }

    /** @return list<string> */
    private function machineList(mixed $value, string $label, int $maximum, bool $allowEmpty): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > $maximum) {
            throw new InvalidArgumentException($label . ' must be a bounded ordered list.');
        }
        if (!$allowEmpty && $value === []) {
            throw new InvalidArgumentException($label . ' must not be empty.');
        }

        $result = [];
        $seen = [];
        foreach ($value as $item) {
            if (!is_string($item) || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $item) !== 1) {
                throw new InvalidArgumentException($label . ' contains a malformed machine key.');
            }
            if (isset($seen[$item])) {
                throw new InvalidArgumentException($label . ' must contain unique values.');
            }
            $seen[$item] = true;
            $result[] = $item;
        }
        return $result;
    }

    /** @return list<int> */
    private function positiveIntList(mixed $value, string $label, int $maximum): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > $maximum) {
            throw new InvalidArgumentException($label . ' must be a bounded ordered list.');
        }

        $result = [];
        $seen = [];
        foreach ($value as $item) {
            if (!is_int($item) || $item < 1) {
                throw new InvalidArgumentException($label . ' must contain positive integers.');
            }
            if (isset($seen[$item])) {
                throw new InvalidArgumentException($label . ' must contain unique values.');
            }
            $seen[$item] = true;
            $result[] = $item;
        }
        return $result;
    }

    /** @param array<string,mixed> $map @param list<string> $allowed */
    private function assertKnownKeys(array $map, array $allowed, string $label): void
    {
        foreach (array_keys($map) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException($label . ' contains unsupported input.');
            }
        }
    }
}
