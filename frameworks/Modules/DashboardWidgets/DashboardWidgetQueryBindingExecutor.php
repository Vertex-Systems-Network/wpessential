<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\DataSources\DataSourceDescriptor;

final readonly class DashboardWidgetQueryBindingExecutor
{
    public function __construct(
        private DataSourceRegistryInterface $dataSources,
        private QueryReadConsumerInterface $query,
    ) {}

    public function resolve(
        DashboardWidgetRenderSourceDescriptor $renderSource,
        ExecutionContext $context,
    ): DashboardWidgetRenderSourceDescriptor {
        $query = $renderSource->query;
        if ($query === null) {
            return $renderSource;
        }

        $descriptor = $this->dataSources->find($query->sourceRef);
        if ($descriptor === null || !$descriptor->isAvailable()) {
            throw new RuntimeException('Dashboard Widget Query Data Source is unavailable.');
        }
        if (!$descriptor->policyRequired || !$descriptor->hasAuthorizationMapping()) {
            throw new RuntimeException('Dashboard Widget Query Data Source is missing canonical authorization metadata.');
        }
        if ($query->pageSize > $descriptor->maxPageSize) {
            throw new RuntimeException('Dashboard Widget Query page size exceeds the Data Source bound.');
        }

        $this->assertReferencedFieldsExist($query, $descriptor);
        $this->assertBindingTypesCompatible($query, $descriptor);

        $result = $this->query->read($query->request(), $context);
        $rows = $this->validatedRows($result, $query);

        $bindings = $renderSource->bindings;
        foreach ($query->bindings as $bindingKey => $binding) {
            $logicalType = $descriptor->fieldSchema[$binding['field_ref']];
            if ($binding['mode'] === 'first') {
                $value = $this->rowValue($rows[0], $binding['field_ref']);
                $this->assertRuntimeValue($value, $logicalType);
                $this->assertSafeValue($value);
                /** @var scalar $value */
                $bindings[$bindingKey] = $value;
                continue;
            }

            $values = [];
            foreach ($rows as $row) {
                $value = $this->rowValue($row, $binding['field_ref']);
                $this->assertRuntimeValue($value, $logicalType);
                $this->assertSafeValue($value);
                /** @var scalar $value */
                $values[] = $value;
            }
            $bindings[$bindingKey] = $values;
        }

        ksort($bindings, SORT_STRING);

        return $renderSource->resolvedWith($bindings);
    }

    private function assertReferencedFieldsExist(
        DashboardWidgetQueryBindingDescriptor $query,
        DataSourceDescriptor $descriptor,
    ): void {
        $fields = $query->projection;
        foreach ($query->filters as $filter) {
            $fields[] = $filter['field_ref'];
        }
        foreach ($query->orderBy as $order) {
            $fields[] = $order['field_ref'];
        }

        foreach (array_unique($fields) as $fieldRef) {
            if (!array_key_exists($fieldRef, $descriptor->fieldSchema)) {
                throw new RuntimeException('Dashboard Widget Query references a field absent from the Data Source schema.');
            }
        }
    }

    private function assertBindingTypesCompatible(
        DashboardWidgetQueryBindingDescriptor $query,
        DataSourceDescriptor $descriptor,
    ): void {
        foreach ($query->bindings as $binding) {
            $logicalType = $descriptor->fieldSchema[$binding['field_ref']];
            $expected = match ($logicalType) {
                'string', 'datetime' => $binding['mode'] === 'first' ? 'string' : 'string_list',
                'integer' => $binding['mode'] === 'first' ? 'int' : 'int_list',
                'float' => $binding['mode'] === 'first' ? 'float' : null,
                'boolean' => $binding['mode'] === 'first' ? 'bool' : null,
                default => null,
            };

            if ($expected === null || $binding['binding_type'] !== $expected) {
                throw new RuntimeException('Dashboard Widget Query field type is incompatible with the Blueprint binding type.');
            }
        }
    }

    /**
     * @param array<string,mixed> $result
     * @return list<array<string,mixed>>
     */
    private function validatedRows(array $result, DashboardWidgetQueryBindingDescriptor $query): array
    {
        if (
            ($result['contract_version'] ?? null) !== QueryReadConsumerInterface::CONTRACT_VERSION
            || ($result['ok'] ?? null) !== true
            || ($result['source_ref'] ?? null) !== $query->sourceRef
            || ($result['projection'] ?? null) !== $query->projection
        ) {
            throw new RuntimeException('Dashboard Widget Query result does not match the bounded consumer contract.');
        }

        $rows = $result['rows'] ?? null;
        $returned = $result['returned'] ?? null;
        if (
            !is_array($rows)
            || !array_is_list($rows)
            || !is_int($returned)
            || $returned !== count($rows)
            || $returned < 1
            || $returned > $query->pageSize
        ) {
            throw new RuntimeException('Dashboard Widget Query result row/cardinality contract failed.');
        }

        foreach ($rows as $row) {
            if (!is_array($row) || array_is_list($row)) {
                throw new RuntimeException('Dashboard Widget Query rows must be object/maps.');
            }
        }

        /** @var list<array<string,mixed>> $rows */
        return $rows;
    }

    /** @param array<string,mixed> $row */
    private function rowValue(array $row, string $fieldRef): mixed
    {
        if (!array_key_exists($fieldRef, $row)) {
            throw new RuntimeException('Dashboard Widget Query result row is missing a projected binding field.');
        }

        return $row[$fieldRef];
    }

    private function assertRuntimeValue(mixed $value, string $logicalType): void
    {
        $valid = match ($logicalType) {
            'string', 'datetime' => is_string($value),
            'integer' => is_int($value),
            'float' => is_float($value),
            'boolean' => is_bool($value),
            default => false,
        };

        if (!$valid) {
            throw new RuntimeException('Dashboard Widget Query result value does not match the Data Source logical type.');
        }
    }

    private function assertSafeValue(mixed $value): void
    {
        if (is_string($value) && preg_match('/<\?(?:php|=)|<script\b|javascript:/i', $value)) {
            throw new RuntimeException('Dashboard Widget Query result contains an executable string marker.');
        }
    }
}
