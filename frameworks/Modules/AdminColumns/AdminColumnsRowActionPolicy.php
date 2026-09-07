<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class AdminColumnsRowActionPolicy
{
    public const CONTRACT_VERSION = 1;

    private const MAX_COLUMNS = 100;
    private const MAX_ACTIONS = 20;

    /**
     * Resolve effective primary-column and row-action availability from explicit
     * authored metadata plus an allowlisted target-adapter capability contract.
     *
     * This policy performs no authorization. Callers must still route every
     * action through Policy and the canonical source/target owner.
     *
     * @param list<array{key:string,enabled?:bool,primary?:bool}> $columns
     * @param array{primary_eligible_columns:list<string>,row_actions:list<string>} $targetCapabilities
     * @param list<string> $requestedActions
     * @return array{
     *   contract_version:int,
     *   primary:array{column_key:?string,available:bool,reason:?string},
     *   actions:list<array{action:string,available:bool,reason:?string}>
     * }
     */
    public function resolve(array $columns, array $targetCapabilities, array $requestedActions): array
    {
        $columnState = $this->columns($columns);
        $eligiblePrimary = $this->machineList(
            $targetCapabilities['primary_eligible_columns'] ?? null,
            'Target primary eligible columns',
            self::MAX_COLUMNS,
        );
        $advertisedActions = $this->machineList(
            $targetCapabilities['row_actions'] ?? null,
            'Target row actions',
            self::MAX_ACTIONS,
        );
        $requested = $this->machineList($requestedActions, 'Requested row actions', self::MAX_ACTIONS);

        $primary = $this->primary($columnState, $eligiblePrimary);
        $availableActions = array_fill_keys($advertisedActions, true);
        $actions = [];
        foreach ($requested as $action) {
            $available = $primary['available'] && isset($availableActions[$action]);
            $reason = null;
            if (!$primary['available']) {
                $reason = 'primary_unavailable';
            } elseif (!isset($availableActions[$action])) {
                $reason = 'target_action_unavailable';
            }
            $actions[] = [
                'action' => $action,
                'available' => $available,
                'reason' => $reason,
            ];
        }

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'primary' => $primary,
            'actions' => $actions,
        ];
    }

    /**
     * @param list<array{key:string,enabled?:bool,primary?:bool}> $columns
     * @return array<string,array{enabled:bool,primary:bool}>
     */
    private function columns(array $columns): array
    {
        if (!array_is_list($columns) || $columns === [] || count($columns) > self::MAX_COLUMNS) {
            throw new InvalidArgumentException('Row-action policy columns must be a non-empty bounded list.');
        }

        $result = [];
        $primaryCount = 0;
        foreach ($columns as $index => $column) {
            if (!is_array($column) || array_is_list($column)) {
                throw new InvalidArgumentException(sprintf('Row-action policy column %d is malformed.', $index));
            }
            foreach (array_keys($column) as $key) {
                if (!in_array($key, ['key', 'enabled', 'primary'], true)) {
                    throw new InvalidArgumentException(sprintf('Row-action policy column %d contains unsupported metadata.', $index));
                }
            }
            $columnKey = $column['key'] ?? null;
            if (!is_string($columnKey) || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $columnKey) !== 1) {
                throw new InvalidArgumentException(sprintf('Row-action policy column %d key is malformed.', $index));
            }
            if (isset($result[$columnKey])) {
                throw new InvalidArgumentException('Row-action policy column keys must be unique.');
            }
            $enabled = $column['enabled'] ?? true;
            $primary = $column['primary'] ?? false;
            if (!is_bool($enabled) || !is_bool($primary)) {
                throw new InvalidArgumentException(sprintf('Row-action policy column %d flags must be boolean.', $index));
            }
            if ($primary) {
                ++$primaryCount;
                if ($primaryCount > 1) {
                    throw new InvalidArgumentException('Row-action policy cannot resolve duplicate primary columns.');
                }
            }
            $result[$columnKey] = ['enabled' => $enabled, 'primary' => $primary];
        }

        return $result;
    }

    /**
     * @param array<string,array{enabled:bool,primary:bool}> $columns
     * @param list<string> $eligiblePrimary
     * @return array{column_key:?string,available:bool,reason:?string}
     */
    private function primary(array $columns, array $eligiblePrimary): array
    {
        $declaredPrimary = null;
        foreach ($columns as $columnKey => $state) {
            if ($state['primary']) {
                $declaredPrimary = $columnKey;
                break;
            }
        }

        if ($declaredPrimary === null) {
            return [
                'column_key' => null,
                'available' => false,
                'reason' => 'no_explicit_primary',
            ];
        }
        if (!$columns[$declaredPrimary]['enabled']) {
            return [
                'column_key' => $declaredPrimary,
                'available' => false,
                'reason' => 'primary_disabled',
            ];
        }
        if (!in_array($declaredPrimary, $eligiblePrimary, true)) {
            return [
                'column_key' => $declaredPrimary,
                'available' => false,
                'reason' => 'primary_ineligible',
            ];
        }

        return [
            'column_key' => $declaredPrimary,
            'available' => true,
            'reason' => null,
        ];
    }

    /** @return list<string> */
    private function machineList(mixed $value, string $label, int $maximum): array
    {
        if (!is_array($value) || !array_is_list($value) || count($value) > $maximum) {
            throw new InvalidArgumentException($label . ' must be a bounded ordered list.');
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
}
