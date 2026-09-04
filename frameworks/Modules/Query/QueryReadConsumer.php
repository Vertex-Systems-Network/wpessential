<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\DataSources\DataSourceDescriptor;

final readonly class QueryReadConsumer implements QueryReadConsumerInterface
{
    /** @var list<string> */
    private const REQUEST_KEYS = [
        'contract_version',
        'source_ref',
        'projection',
        'filters',
        'search',
        'order_by',
        'page_size',
        'offset',
    ];

    /** @var list<string> */
    private const FILTER_KEYS = ['field_ref', 'operator', 'value'];

    /** @var list<string> */
    private const ORDER_KEYS = ['field_ref', 'direction'];

    /** @var list<string> */
    private const FILTER_OPERATORS = ['eq', 'neq', 'in', 'not_in'];

    public function __construct(
        private DataSourceRegistryInterface $dataSources,
        private QueryAstValidator|QueryFieldAwareAstValidator $validator,
        private QueryAuthorizedExecutor $executor,
    ) {
    }

    public function describe(string $sourceRef, ExecutionContext $context): array
    {
        $descriptor = $this->dataSources->find($sourceRef);
        if ($descriptor === null) {
            throw new InvalidArgumentException('Unknown Query Data Source.');
        }

        // The descriptor is non-authorizing capability metadata. Execution still
        // requires the caller context to pass the canonical Query Policy path.
        $context->principal;

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'source_ref' => $descriptor->id,
            'source_type' => $descriptor->sourceType,
            'capability_version' => $descriptor->capabilityVersion,
            'available' => $descriptor->isAvailable(),
            'field_schema' => $descriptor->fieldSchema,
            'predicates' => $descriptor->predicates,
            'sort_modes' => $descriptor->sortModes,
            'pagination_modes' => $descriptor->paginationModes,
            'max_page_size' => min(self::MAX_PAGE_SIZE, $descriptor->maxPageSize),
        ];
    }

    public function read(array $request, ExecutionContext $context): array
    {
        $sourceRef = is_string($request['source_ref'] ?? null) ? $request['source_ref'] : '';

        try {
            $normalized = $this->normalizeRequest($request);
        } catch (InvalidArgumentException|JsonException $exception) {
            return $this->failure(
                $sourceRef,
                'wpe_query_invalid_consumer_request',
                '$',
                $exception->getMessage(),
            );
        }

        $sourceRef = $normalized['source_ref'];
        $descriptor = $this->dataSources->find($sourceRef);
        if ($descriptor === null) {
            return $this->failure(
                $sourceRef,
                'wpe_query_unknown_source',
                '$.source_ref',
                'Requested Query Data Source is not registered.',
            );
        }

        if ($normalized['page_size'] > min(self::MAX_PAGE_SIZE, $descriptor->maxPageSize)) {
            return $this->failure(
                $sourceRef,
                'wpe_query_cost_blocked',
                '$.page_size',
                'Requested page size exceeds the Query consumer or Data Source limit.',
            );
        }

        try {
            $ast = $this->buildAst($normalized, $descriptor);
        } catch (JsonException) {
            return $this->failure(
                $sourceRef,
                'wpe_query_invalid_consumer_request',
                '$',
                'Query consumer request could not be normalized deterministically.',
            );
        }

        $validation = $this->validator->validate(
            $ast,
            new QueryValidationBudget(
                maxAstBytes: self::MAX_REQUEST_BYTES,
                maxGroupDepth: 2,
                maxPredicateCount: self::MAX_FILTERS + 1,
                maxInListSize: self::MAX_FILTER_VALUES,
                maxPageSize: self::MAX_PAGE_SIZE,
                maxRelationDepth: 1,
            ),
            $context,
        );

        if (!$validation->isValid() || $validation->definition === null) {
            $issue = $validation->issues[0] ?? null;
            return $this->failure(
                $sourceRef,
                $issue?->code ?? 'wpe_query_invalid_ast',
                $issue?->path ?? '$',
                $issue?->message ?? 'Query request failed canonical validation.',
            );
        }

        $result = $this->executor->execute($validation->definition, $context);
        if ($result instanceof QueryExecutionError) {
            return $this->failure(
                $sourceRef,
                $result->errorCode,
                $result->path,
                $result->message,
            );
        }

        if (
            $result->sourceRef !== $sourceRef
            || $result->projection !== $normalized['projection']
            || $result->returned !== count($result->rows)
            || $result->returned > $normalized['page_size']
        ) {
            return $this->failure(
                $sourceRef,
                'wpe_query_provider_failed',
                '$.result',
                'Query provider returned a result outside the bounded consumer contract.',
            );
        }

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'ok' => true,
            'source_ref' => $sourceRef,
            'projection' => $result->projection,
            'rows' => $result->rows,
            'returned' => $result->returned,
            'error' => null,
        ];
    }

    /**
     * @param array<string,mixed> $request
     * @return array{
     *   contract_version:int,
     *   source_ref:string,
     *   projection:list<string>,
     *   filters:list<array{field_ref:string,operator:string,value:mixed}>,
     *   search:?string,
     *   order_by:list<array{field_ref:string,direction:string}>,
     *   page_size:int,
     *   offset:int
     * }
     * @throws JsonException
     */
    private function normalizeRequest(array $request): array
    {
        if (array_is_list($request)) {
            throw new InvalidArgumentException('Query consumer request must be an object/map.');
        }
        $this->assertKnownKeys($request, self::REQUEST_KEYS, 'Query consumer request');

        $encoded = json_encode($request, JSON_THROW_ON_ERROR);
        if (strlen($encoded) > self::MAX_REQUEST_BYTES) {
            throw new InvalidArgumentException('Query consumer request exceeds the bounded byte limit.');
        }

        $contractVersion = $request['contract_version'] ?? null;
        if ($contractVersion !== self::CONTRACT_VERSION) {
            throw new InvalidArgumentException('Query consumer contract version is unsupported.');
        }

        $sourceRef = $this->semanticReference($request['source_ref'] ?? null, 'Query source reference');
        $projection = $this->projection($request['projection'] ?? null);
        $filters = $this->filters($request['filters'] ?? []);
        $search = $this->search($request['search'] ?? null);
        $orderBy = $this->orderBy($request['order_by'] ?? []);

        $pageSize = $request['page_size'] ?? 20;
        if (!is_int($pageSize) || $pageSize < 1 || $pageSize > self::MAX_PAGE_SIZE) {
            throw new InvalidArgumentException('Query page_size must be within the bounded V1 limit.');
        }

        $offset = $request['offset'] ?? 0;
        if (!is_int($offset) || $offset < 0 || $offset > self::MAX_OFFSET) {
            throw new InvalidArgumentException('Query offset exceeds the bounded V1 limit.');
        }

        return [
            'contract_version' => $contractVersion,
            'source_ref' => $sourceRef,
            'projection' => $projection,
            'filters' => $filters,
            'search' => $search,
            'order_by' => $orderBy,
            'page_size' => $pageSize,
            'offset' => $offset,
        ];
    }

    /** @return list<string> */
    private function projection(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value) || $value === []) {
            throw new InvalidArgumentException('Query projection must be a non-empty list.');
        }
        if (count($value) > self::MAX_PROJECTION_FIELDS) {
            throw new InvalidArgumentException('Query projection exceeds the bounded V1 field limit.');
        }

        $projection = [];
        $seen = [];
        foreach ($value as $fieldRef) {
            $fieldRef = $this->semanticReference($fieldRef, 'Query projection field');
            if (isset($seen[$fieldRef])) {
                throw new InvalidArgumentException('Query projection fields must be unique.');
            }
            $seen[$fieldRef] = true;
            $projection[] = $fieldRef;
        }

        return $projection;
    }

    /** @return list<array{field_ref:string,operator:string,value:mixed}> */
    private function filters(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException('Query filters must be a list.');
        }
        if (count($value) > self::MAX_FILTERS) {
            throw new InvalidArgumentException('Query filters exceed the bounded V1 limit.');
        }

        $filters = [];
        foreach ($value as $index => $filter) {
            if (!is_array($filter) || array_is_list($filter)) {
                throw new InvalidArgumentException(sprintf('Query filter %d must be an object/map.', $index));
            }
            $this->assertKnownKeys($filter, self::FILTER_KEYS, sprintf('Query filter %d', $index));
            if (!array_key_exists('value', $filter)) {
                throw new InvalidArgumentException(sprintf('Query filter %d requires a value.', $index));
            }

            $fieldRef = $this->semanticReference($filter['field_ref'] ?? null, sprintf('Query filter %d field', $index));
            $operator = $filter['operator'] ?? null;
            if (!is_string($operator) || !in_array($operator, self::FILTER_OPERATORS, true)) {
                throw new InvalidArgumentException(sprintf('Query filter %d operator is not supported by consumer V1.', $index));
            }

            $filterValue = $filter['value'];
            if (in_array($operator, ['in', 'not_in'], true)) {
                if (!is_array($filterValue) || !array_is_list($filterValue) || $filterValue === []) {
                    throw new InvalidArgumentException(sprintf('Query filter %d set value must be a non-empty list.', $index));
                }
                if (count($filterValue) > self::MAX_FILTER_VALUES) {
                    throw new InvalidArgumentException(sprintf('Query filter %d exceeds the bounded value-list limit.', $index));
                }
            } elseif (is_array($filterValue) || is_object($filterValue) || is_resource($filterValue)) {
                throw new InvalidArgumentException(sprintf('Query filter %d comparison value must be scalar or null.', $index));
            }

            $filters[] = [
                'field_ref' => $fieldRef,
                'operator' => $operator,
                'value' => $filterValue,
            ];
        }

        return $filters;
    }

    private function search(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException('Query search must be a string when provided.');
        }
        $value = trim($value);
        if ($value === '' || strlen($value) > self::MAX_SEARCH_LENGTH) {
            throw new InvalidArgumentException('Query search must be non-empty and within the bounded V1 length.');
        }

        return $value;
    }

    /** @return list<array{field_ref:string,direction:string}> */
    private function orderBy(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException('Query order_by must be a list.');
        }
        if (count($value) > self::MAX_ORDER_FIELDS) {
            throw new InvalidArgumentException('Query order_by exceeds the bounded V1 field limit.');
        }

        $orderBy = [];
        $seen = [];
        foreach ($value as $index => $order) {
            if (!is_array($order) || array_is_list($order)) {
                throw new InvalidArgumentException(sprintf('Query order %d must be an object/map.', $index));
            }
            $this->assertKnownKeys($order, self::ORDER_KEYS, sprintf('Query order %d', $index));
            $fieldRef = $this->semanticReference($order['field_ref'] ?? null, sprintf('Query order %d field', $index));
            $direction = $order['direction'] ?? null;
            if (!is_string($direction) || !in_array($direction, ['asc', 'desc'], true)) {
                throw new InvalidArgumentException(sprintf('Query order %d direction must be asc or desc.', $index));
            }
            if (isset($seen[$fieldRef])) {
                throw new InvalidArgumentException('Query order fields must be unique.');
            }
            $seen[$fieldRef] = true;
            $orderBy[] = ['field_ref' => $fieldRef, 'direction' => $direction];
        }

        return $orderBy;
    }

    /**
     * @param array{
     *   contract_version:int,
     *   source_ref:string,
     *   projection:list<string>,
     *   filters:list<array{field_ref:string,operator:string,value:mixed}>,
     *   search:?string,
     *   order_by:list<array{field_ref:string,direction:string}>,
     *   page_size:int,
     *   offset:int
     * } $request
     * @return array<string,mixed>
     * @throws JsonException
     */
    private function buildAst(array $request, DataSourceDescriptor $descriptor): array
    {
        $predicates = [];
        foreach ($request['filters'] as $filter) {
            if (in_array($filter['operator'], ['in', 'not_in'], true)) {
                $predicates[] = [
                    'type' => 'set_membership',
                    'field_ref' => $filter['field_ref'],
                    'operator' => $filter['operator'],
                    'values' => $filter['value'],
                ];
                continue;
            }
            $predicates[] = [
                'type' => 'comparison',
                'field_ref' => $filter['field_ref'],
                'operator' => $filter['operator'],
                'value' => $filter['value'],
            ];
        }

        if ($request['search'] !== null) {
            $predicates[] = [
                'type' => 'text',
                'search_scope' => 'posts.default',
                'mode' => 'contains',
                'value' => $request['search'],
            ];
        }

        $filter = null;
        if (count($predicates) === 1) {
            $filter = $predicates[0];
        } elseif ($predicates !== []) {
            $filter = [
                'type' => 'group',
                'boolean' => 'and',
                'children' => $predicates,
            ];
        }

        return [
            'identity' => [
                'uuid' => $this->requestUuid($request),
                'key' => 'admin-columns.read.v1',
                'name' => 'Admin Columns bounded read V1',
                'revision' => 1,
                'lifecycle' => 'runtime',
            ],
            'ast_version' => 1,
            'source' => [
                'source_ref' => $descriptor->id,
                'source_type' => $descriptor->sourceType,
                'capability_version' => $descriptor->capabilityVersion,
            ],
            'operation' => 'select',
            'projection' => $request['projection'],
            'parameters' => [],
            'filter' => $filter,
            'order_by' => $request['order_by'],
            'pagination' => [
                'mode' => 'offset',
                'page_size' => $request['page_size'],
                'offset' => $request['offset'],
            ],
            'distinct' => false,
            'execution_policy' => [],
            'cache_policy' => [],
            'metadata' => [
                'consumer' => 'admin_columns',
                'contract_version' => self::CONTRACT_VERSION,
            ],
        ];
    }

    /**
     * @param array<string,mixed> $request
     * @throws JsonException
     */
    private function requestUuid(array $request): string
    {
        $hash = substr(hash('sha256', json_encode($request, JSON_THROW_ON_ERROR)), 0, 32);
        $hash[12] = '5';
        $hash[16] = '8';

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12),
        );
    }

    private function semanticReference(mixed $value, string $label): string
    {
        if (!is_string($value) || preg_match('/^[a-z][a-z0-9._-]{0,127}$/', $value) !== 1) {
            throw new InvalidArgumentException($label . ' must be a stable semantic reference.');
        }

        return $value;
    }

    /** @param array<mixed> $value @param list<string> $allowed */
    private function assertKnownKeys(array $value, array $allowed, string $label): void
    {
        foreach (array_keys($value) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException($label . ' contains an unsupported property.');
            }
        }
    }

    /** @return array<string,mixed> */
    private function failure(string $sourceRef, string $code, string $path, string $message): array
    {
        return [
            'contract_version' => self::CONTRACT_VERSION,
            'ok' => false,
            'source_ref' => $sourceRef,
            'projection' => [],
            'rows' => [],
            'returned' => 0,
            'error' => [
                'code' => $code,
                'path' => $path,
                'message' => $message,
            ],
        ];
    }
}
