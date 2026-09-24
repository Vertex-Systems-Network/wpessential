<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Platform\Definitions\Definition;

final readonly class DashboardWidgetRenderSourceCompiler
{
    /** @var list<string> */
    private const RENDER_SOURCE_KEYS = ['kind', 'blueprint_id', 'blueprint_revision', 'query', 'bindings', 'empty_state'];

    /** @var list<string> */
    private const LITERAL_BINDING_KEYS = ['source', 'value'];

    /** @var list<string> */
    private const QUERY_BINDING_KEYS = ['source', 'field_ref', 'mode'];

    /** @var list<string> */
    private const QUERY_KEYS = ['contract_version', 'source_ref', 'filters', 'order_by', 'page_size', 'offset'];

    /** @var list<string> */
    private const EMPTY_STATE_KEYS = ['kind', 'blueprint_id', 'blueprint_revision', 'bindings'];

    /** @var list<string> */
    private const FILTER_KEYS = ['field_ref', 'operator', 'value'];

    /** @var list<string> */
    private const ORDER_KEYS = ['field_ref', 'direction'];

    /** @var list<string> */
    private const FILTER_OPERATORS = ['eq', 'neq', 'in', 'not_in'];

    private DashboardWidgetContentClassCompiler $contentClassCompiler;
    private DashboardWidgetComponentBlueprintCatalog $componentCatalog;

    public function __construct(
        private ComponentBlueprintRegistryInterface $blueprints,
        ?DashboardWidgetContentClassCompiler $contentClassCompiler = null,
        ?DashboardWidgetComponentBlueprintCatalog $componentCatalog = null,
    ) {
        $this->contentClassCompiler = $contentClassCompiler ?? new DashboardWidgetContentClassCompiler();
        $this->componentCatalog = $componentCatalog ?? new DashboardWidgetComponentBlueprintCatalog();
    }

    public function compile(Definition $definition): DashboardWidgetRenderSourceDescriptor
    {
        $contentClass = $this->contentClassCompiler->compile($definition);

        $widget = $definition->payload['widget'] ?? null;
        if (!is_array($widget) || array_is_list($widget)) {
            throw new InvalidArgumentException('Dashboard Widget metadata must be an object/map for render-source compilation.');
        }

        $renderSource = $widget['render_source'] ?? null;
        if (!is_array($renderSource) || array_is_list($renderSource)) {
            throw new InvalidArgumentException('Dashboard Widget render_source must be an object/map.');
        }
        $this->assertKnownKeys($renderSource, self::RENDER_SOURCE_KEYS, 'Dashboard Widget render_source');

        if (($renderSource['kind'] ?? null) !== 'component_blueprint') {
            throw new InvalidArgumentException('Dashboard Widget render_source kind must be component_blueprint for V1.');
        }

        $blueprintId = $renderSource['blueprint_id'] ?? null;
        if (
            !is_string($blueprintId)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $blueprintId) !== 1
        ) {
            throw new InvalidArgumentException('Dashboard Widget render_source blueprint_id must be a lowercase RFC 4122 UUID.');
        }

        $blueprintRevision = $renderSource['blueprint_revision'] ?? null;
        if (!is_int($blueprintRevision) || $blueprintRevision < 1) {
            throw new InvalidArgumentException('Dashboard Widget render_source blueprint_revision must be a positive integer.');
        }

        $canonicalBlueprint = $this->componentCatalog->forContentType($contentClass->contentType);
        if ($canonicalBlueprint === null) {
            throw new InvalidArgumentException('Dashboard Widget trusted content class has no canonical Component Blueprint.');
        }
        if ($blueprintId !== $canonicalBlueprint->id || $blueprintRevision !== $canonicalBlueprint->revision) {
            throw new InvalidArgumentException(
                'Dashboard Widget render_source Blueprint must match the canonical Blueprint for its trusted content class.',
            );
        }

        $bindings = $renderSource['bindings'] ?? null;
        if (!is_array($bindings) || ($bindings !== [] && array_is_list($bindings))) {
            throw new InvalidArgumentException('Dashboard Widget render_source bindings must be an object/map.');
        }
        if (count($bindings) > 128) {
            throw new InvalidArgumentException('Dashboard Widget render_source bindings exceed the bounded V1 limit.');
        }

        $blueprint = $this->blueprints->get($blueprintId, $blueprintRevision);
        if ($blueprint === null) {
            throw new InvalidArgumentException('Dashboard Widget render_source Blueprint id/revision is not registered.');
        }
        if ($blueprint->ownerSurfaceId !== DashboardWidgetDefinition::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Dashboard Widget render_source Blueprint must be owned by Surface 10.');
        }

        $authoredKeys = array_keys($bindings);
        foreach ($authoredKeys as $key) {
            if (!is_string($key) || preg_match('/^[a-z][a-z0-9_.-]{0,127}$/', $key) !== 1) {
                throw new InvalidArgumentException('Dashboard Widget render_source binding keys must be stable semantic identifiers.');
            }
        }

        $schemaKeys = array_keys($blueprint->bindingSchema);
        sort($authoredKeys, SORT_STRING);
        sort($schemaKeys, SORT_STRING);
        if ($authoredKeys !== $schemaKeys) {
            throw new InvalidArgumentException('Dashboard Widget render_source bindings must exactly match the Blueprint binding schema keys.');
        }

        /** @var array<string, scalar|list<scalar>> $literalBindings */
        $literalBindings = [];
        /** @var array<string,array{field_ref:string,mode:string,binding_type:string}> $queryBindings */
        $queryBindings = [];

        foreach ($blueprint->bindingSchema as $key => $type) {
            $envelope = $bindings[$key] ?? null;
            if (!is_array($envelope) || array_is_list($envelope)) {
                throw new InvalidArgumentException('Dashboard Widget render_source binding entries must be object/maps.');
            }

            $source = $envelope['source'] ?? null;
            if ($source === 'literal') {
                $this->assertKnownKeys($envelope, self::LITERAL_BINDING_KEYS, 'Dashboard Widget literal render_source binding');
                if (!array_key_exists('value', $envelope)) {
                    throw new InvalidArgumentException('Dashboard Widget literal render_source binding requires value.');
                }
                $this->assertValueMatchesType($envelope['value'], $type);
                /** @var scalar|list<scalar> $value */
                $value = $envelope['value'];
                $literalBindings[$key] = $value;
                continue;
            }

            if ($source === 'query') {
                $this->assertKnownKeys($envelope, self::QUERY_BINDING_KEYS, 'Dashboard Widget Query render_source binding');
                $fieldRef = $this->fieldReference($envelope['field_ref'] ?? null, 'Dashboard Widget Query binding field_ref');
                $mode = $envelope['mode'] ?? null;
                if (!is_string($mode) || !in_array($mode, ['first', 'column'], true)) {
                    throw new InvalidArgumentException('Dashboard Widget Query binding mode must be first or column.');
                }
                $queryBindings[$key] = [
                    'field_ref' => $fieldRef,
                    'mode' => $mode,
                    'binding_type' => $type,
                ];
                continue;
            }

            throw new InvalidArgumentException('Dashboard Widget render_source binding source must be literal or query.');
        }

        ksort($literalBindings, SORT_STRING);
        ksort($queryBindings, SORT_STRING);

        $queryAuthored = array_key_exists('query', $renderSource);
        if ($queryBindings === [] && $queryAuthored) {
            throw new InvalidArgumentException('Dashboard Widget render_source query requires at least one Query binding.');
        }
        if ($queryBindings !== [] && !$queryAuthored) {
            throw new InvalidArgumentException('Dashboard Widget Query binding requires render_source.query.');
        }

        $query = null;
        if ($queryBindings !== []) {
            $query = $this->compileQuery($renderSource['query'], $queryBindings);
        }

        $emptyState = null;
        if (array_key_exists('empty_state', $renderSource)) {
            if ($queryBindings === []) {
                throw new InvalidArgumentException(
                    'Dashboard Widget render_source empty_state requires at least one Query binding.',
                );
            }
            $emptyState = $this->compileEmptyState($renderSource['empty_state']);
        }

        return new DashboardWidgetRenderSourceDescriptor(
            definitionId: $definition->id,
            definitionRevision: $definition->revision,
            blueprintId: $blueprintId,
            blueprintRevision: $blueprintRevision,
            bindings: $literalBindings,
            query: $query,
            emptyState: $emptyState,
        );
    }

    private function compileEmptyState(mixed $raw): DashboardWidgetEmptyStateDescriptor
    {
        if (!is_array($raw) || array_is_list($raw)) {
            throw new InvalidArgumentException('Dashboard Widget render_source empty_state must be an object/map.');
        }
        $this->assertKnownKeys($raw, self::EMPTY_STATE_KEYS, 'Dashboard Widget render_source empty_state');

        foreach (self::EMPTY_STATE_KEYS as $requiredKey) {
            if (!array_key_exists($requiredKey, $raw)) {
                throw new InvalidArgumentException('Dashboard Widget render_source empty_state is missing a required key.');
            }
        }

        if ($raw['kind'] !== 'component_blueprint') {
            throw new InvalidArgumentException('Dashboard Widget empty_state kind must be component_blueprint.');
        }

        $blueprintId = $raw['blueprint_id'];
        if (
            !is_string($blueprintId)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $blueprintId) !== 1
        ) {
            throw new InvalidArgumentException('Dashboard Widget empty_state blueprint_id must be a lowercase RFC 4122 UUID.');
        }

        $blueprintRevision = $raw['blueprint_revision'];
        if (!is_int($blueprintRevision) || $blueprintRevision < 1) {
            throw new InvalidArgumentException('Dashboard Widget empty_state blueprint_revision must be a positive integer.');
        }

        $contentType = $this->componentCatalog->contentTypeForBlueprint($blueprintId, $blueprintRevision);
        if (!in_array($contentType, ['rich_text', 'announcement'], true)) {
            throw new InvalidArgumentException(
                'Dashboard Widget empty_state Blueprint must be a trusted rich_text or announcement Blueprint.',
            );
        }

        $blueprint = $this->blueprints->get($blueprintId, $blueprintRevision);
        if ($blueprint === null || $blueprint->ownerSurfaceId !== DashboardWidgetDefinition::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Dashboard Widget empty_state Blueprint must be registered and owned by Surface 10.');
        }

        $bindings = $raw['bindings'];
        if (!is_array($bindings) || ($bindings !== [] && array_is_list($bindings))) {
            throw new InvalidArgumentException('Dashboard Widget empty_state bindings must be an object/map.');
        }

        $authoredKeys = array_keys($bindings);
        $schemaKeys = array_keys($blueprint->bindingSchema);
        sort($authoredKeys, SORT_STRING);
        sort($schemaKeys, SORT_STRING);
        if ($authoredKeys !== $schemaKeys) {
            throw new InvalidArgumentException(
                'Dashboard Widget empty_state bindings must exactly match the trusted Blueprint binding schema.',
            );
        }

        /** @var array<string,string> $literalBindings */
        $literalBindings = [];
        /** @var array<string,array{source:string,value:string}> $normalizedBindings */
        $normalizedBindings = [];
        foreach ($blueprint->bindingSchema as $key => $type) {
            if ($type !== 'string') {
                throw new InvalidArgumentException('Dashboard Widget empty_state V1 supports string Blueprint bindings only.');
            }

            $envelope = $bindings[$key] ?? null;
            if (!is_array($envelope) || array_is_list($envelope)) {
                throw new InvalidArgumentException('Dashboard Widget empty_state binding entries must be object/maps.');
            }
            $this->assertKnownKeys($envelope, self::LITERAL_BINDING_KEYS, 'Dashboard Widget empty_state literal binding');
            if (($envelope['source'] ?? null) !== 'literal' || !array_key_exists('value', $envelope)) {
                throw new InvalidArgumentException('Dashboard Widget empty_state bindings must use literal value envelopes.');
            }

            $value = $envelope['value'];
            if (
                !is_string($value)
                || strlen($value) < 1
                || strlen($value) > DashboardWidgetEmptyStateDescriptor::MAX_STRING_BYTES
                || trim($value, " \t\n\r\0\x0B") === ''
                || !$this->isSafeString($value)
            ) {
                throw new InvalidArgumentException(
                    'Dashboard Widget empty_state binding value must be a safe non-empty string within 2048 bytes.',
                );
            }

            $literalBindings[$key] = $value;
            $normalizedBindings[$key] = ['source' => 'literal', 'value' => $value];
        }
        ksort($literalBindings, SORT_STRING);
        ksort($normalizedBindings, SORT_STRING);

        $encoded = json_encode([
            'kind' => 'component_blueprint',
            'blueprint_id' => $blueprintId,
            'blueprint_revision' => $blueprintRevision,
            'bindings' => $normalizedBindings,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (!is_string($encoded) || strlen($encoded) > DashboardWidgetEmptyStateDescriptor::MAX_ENCODED_BYTES) {
            throw new InvalidArgumentException('Dashboard Widget empty_state exceeds the bounded 4096-byte limit.');
        }

        return new DashboardWidgetEmptyStateDescriptor(
            blueprintId: $blueprintId,
            blueprintRevision: $blueprintRevision,
            bindings: $literalBindings,
        );
    }

    /**
     * @param mixed $raw
     * @param array<string,array{field_ref:string,mode:string,binding_type:string}> $bindings
     */
    private function compileQuery(mixed $raw, array $bindings): DashboardWidgetQueryBindingDescriptor
    {
        if (!is_array($raw) || array_is_list($raw)) {
            throw new InvalidArgumentException('Dashboard Widget render_source query must be an object/map.');
        }
        $this->assertKnownKeys($raw, self::QUERY_KEYS, 'Dashboard Widget render_source query');

        if (($raw['contract_version'] ?? null) !== QueryReadConsumerInterface::CONTRACT_VERSION) {
            throw new InvalidArgumentException('Dashboard Widget Query contract_version must equal canonical Query consumer V1.');
        }

        $sourceRef = $raw['source_ref'] ?? null;
        if (!is_string($sourceRef) || preg_match('/^[a-z][a-z0-9._-]{1,127}$/', $sourceRef) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget Query source_ref must be a stable Data Source identifier.');
        }

        $filters = $this->filters($raw['filters'] ?? []);
        $orderBy = $this->orderBy($raw['order_by'] ?? []);

        $pageSize = $raw['page_size'] ?? 20;
        if (!is_int($pageSize) || $pageSize < 1 || $pageSize > 50) {
            throw new InvalidArgumentException('Dashboard Widget Query page_size must be within 1..50.');
        }

        $offset = $raw['offset'] ?? 0;
        if (!is_int($offset) || $offset < 0 || $offset > 1000) {
            throw new InvalidArgumentException('Dashboard Widget Query offset must be within 0..1000.');
        }

        $projectionSet = [];
        foreach ($bindings as $binding) {
            $projectionSet[$binding['field_ref']] = true;
        }
        $projection = array_keys($projectionSet);
        sort($projection, SORT_STRING);

        return new DashboardWidgetQueryBindingDescriptor(
            sourceRef: $sourceRef,
            projection: $projection,
            filters: $filters,
            orderBy: $orderBy,
            pageSize: $pageSize,
            offset: $offset,
            bindings: $bindings,
        );
    }

    /** @return list<array{field_ref:string,operator:string,value:mixed}> */
    private function filters(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException('Dashboard Widget Query filters must be a list.');
        }
        if (count($value) > 8) {
            throw new InvalidArgumentException('Dashboard Widget Query filters exceed the bounded V1 limit.');
        }

        $filters = [];
        foreach ($value as $index => $filter) {
            if (!is_array($filter) || array_is_list($filter)) {
                throw new InvalidArgumentException(sprintf('Dashboard Widget Query filter %d must be an object/map.', $index));
            }
            $this->assertKnownKeys($filter, self::FILTER_KEYS, sprintf('Dashboard Widget Query filter %d', $index));
            if (!array_key_exists('value', $filter)) {
                throw new InvalidArgumentException(sprintf('Dashboard Widget Query filter %d requires value.', $index));
            }

            $fieldRef = $this->fieldReference($filter['field_ref'] ?? null, sprintf('Dashboard Widget Query filter %d field_ref', $index));
            $operator = $filter['operator'] ?? null;
            if (!is_string($operator) || !in_array($operator, self::FILTER_OPERATORS, true)) {
                throw new InvalidArgumentException(sprintf('Dashboard Widget Query filter %d operator is unsupported.', $index));
            }

            $filterValue = $filter['value'];
            if (in_array($operator, ['in', 'not_in'], true)) {
                if (!is_array($filterValue) || !array_is_list($filterValue) || $filterValue === [] || count($filterValue) > 20) {
                    throw new InvalidArgumentException(sprintf('Dashboard Widget Query filter %d set value must contain 1..20 items.', $index));
                }
                foreach ($filterValue as $item) {
                    if ($item === null || !is_scalar($item)) {
                        throw new InvalidArgumentException(sprintf('Dashboard Widget Query filter %d set values must be non-null scalars.', $index));
                    }
                }
            } elseif ($filterValue !== null && !is_scalar($filterValue)) {
                throw new InvalidArgumentException(sprintf('Dashboard Widget Query filter %d comparison value must be scalar or null.', $index));
            }

            $filters[] = ['field_ref' => $fieldRef, 'operator' => $operator, 'value' => $filterValue];
        }

        return $filters;
    }

    /** @return list<array{field_ref:string,direction:string}> */
    private function orderBy(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException('Dashboard Widget Query order_by must be a list.');
        }
        if (count($value) > 2) {
            throw new InvalidArgumentException('Dashboard Widget Query order_by exceeds the bounded V1 limit.');
        }

        $orders = [];
        $seen = [];
        foreach ($value as $index => $order) {
            if (!is_array($order) || array_is_list($order)) {
                throw new InvalidArgumentException(sprintf('Dashboard Widget Query order %d must be an object/map.', $index));
            }
            $this->assertKnownKeys($order, self::ORDER_KEYS, sprintf('Dashboard Widget Query order %d', $index));

            $fieldRef = $this->fieldReference($order['field_ref'] ?? null, sprintf('Dashboard Widget Query order %d field_ref', $index));
            $direction = $order['direction'] ?? null;
            if (!is_string($direction) || !in_array($direction, ['asc', 'desc'], true)) {
                throw new InvalidArgumentException(sprintf('Dashboard Widget Query order %d direction must be asc or desc.', $index));
            }
            if (isset($seen[$fieldRef])) {
                throw new InvalidArgumentException('Dashboard Widget Query order fields must be unique.');
            }
            $seen[$fieldRef] = true;
            $orders[] = ['field_ref' => $fieldRef, 'direction' => $direction];
        }

        return $orders;
    }

    private function fieldReference(mixed $value, string $label): string
    {
        if (!is_string($value) || preg_match('/^[a-z][a-z0-9._-]{0,127}$/', $value) !== 1) {
            throw new InvalidArgumentException($label . ' must be a stable semantic field reference.');
        }

        return $value;
    }

    /**
     * @param array<string,mixed> $value
     * @param list<string> $allowed
     */
    private function assertKnownKeys(array $value, array $allowed, string $label): void
    {
        foreach (array_keys($value) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException($label . ' contains an unsupported key.');
            }
        }
    }

    private function assertValueMatchesType(mixed $value, string $type): void
    {
        $valid = match ($type) {
            'string' => is_string($value) && $this->isSafeString($value),
            'int' => is_int($value),
            'float' => is_float($value),
            'bool' => is_bool($value),
            'string_list' => $this->isStringList($value),
            'int_list' => $this->isIntList($value),
            default => false,
        };

        if (!$valid) {
            throw new InvalidArgumentException('Dashboard Widget render_source literal binding does not match the Blueprint binding type.');
        }
    }

    private function isSafeString(string $value): bool
    {
        return preg_match('/<\?(?:php|=)|<script\b|javascript:/i', $value) !== 1;
    }

    private function isStringList(mixed $value): bool
    {
        if (!is_array($value) || !array_is_list($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!is_string($item) || !$this->isSafeString($item)) {
                return false;
            }
        }
        return true;
    }

    private function isIntList(mixed $value): bool
    {
        if (!is_array($value) || !array_is_list($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!is_int($item)) {
                return false;
            }
        }
        return true;
    }
}
