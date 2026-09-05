<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\FieldValueReadConsumerInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class AdminColumnsReadAdapter
{
    public const CONTRACT_VERSION = 1;
    public const QUERY_SOURCE = 'wordpress.posts';

    private const MAX_FIELD_COLUMNS = 16;
    private const FIELD_REFERENCE_PATTERN = '/^fields\.[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

    /** @var list<string> */
    private const INPUT_KEYS = ['filters', 'search', 'order_by', 'page_size', 'offset'];

    /** @var list<string> */
    private const FILTER_KEYS = ['column_key', 'operator', 'value'];

    /** @var list<string> */
    private const ORDER_KEYS = ['column_key', 'direction'];

    public function __construct(
        private AdminColumnsViewDefinitionService $views,
        private QueryReadConsumerInterface $query,
        private ?FieldValueReadConsumerInterface $fields = null,
    ) {
    }

    /**
     * @param array<string,mixed> $input
     * @return array<string,mixed>
     */
    public function read(string $viewId, array $input, ExecutionContext $context): array
    {
        $this->assertKnownKeys($input, self::INPUT_KEYS, 'Admin Columns read input');
        $view = $this->views->get($viewId);
        $this->assertReadableView($view);

        $target = $view->payload['target'] ?? null;
        if (!is_array($target) || ($target['type'] ?? null) !== 'post_type') {
            throw new InvalidArgumentException('Admin Columns Query read V1 supports only post_type Views.');
        }
        $targetKey = $target['key'] ?? null;
        if (!is_string($targetKey) || preg_match('/^[a-z][a-z0-9_-]{0,63}$/', $targetKey) !== 1) {
            throw new InvalidArgumentException('Admin Columns post_type target key is malformed.');
        }

        $description = $this->query->describe(self::QUERY_SOURCE, $context);
        $fieldSchema = $description['field_schema'] ?? null;
        if (!is_array($fieldSchema)) {
            throw new RuntimeException('Query read consumer returned malformed source metadata.');
        }

        [$columns, $queryFieldByColumn, $fieldFieldByColumn, $sourceByColumn, $projection] = $this->readableColumns(
            $view,
            $fieldSchema,
        );
        if ($fieldFieldByColumn !== []) {
            if (!$this->fields instanceof FieldValueReadConsumerInterface) {
                throw new RuntimeException('Admin Columns Fields source requires the certified Field value read consumer.');
            }
            if (!in_array('post.id', $projection, true)) {
                $projection[] = 'post.id';
            }
        }

        $filters = [[
            'field_ref' => 'post.type',
            'operator' => 'eq',
            'value' => $targetKey,
        ]];
        foreach ($this->filters($input['filters'] ?? [], $queryFieldByColumn) as $filter) {
            $filters[] = $filter;
        }

        $request = [
            'contract_version' => QueryReadConsumerInterface::CONTRACT_VERSION,
            'source_ref' => self::QUERY_SOURCE,
            'projection' => $projection,
            'filters' => $filters,
            'order_by' => $this->orderBy($input['order_by'] ?? [], $queryFieldByColumn),
            'page_size' => $input['page_size'] ?? 20,
            'offset' => $input['offset'] ?? 0,
        ];
        if (array_key_exists('search', $input)) {
            $request['search'] = $input['search'];
        }

        $result = $this->query->read($request, $context);
        if (($result['ok'] ?? false) !== true) {
            return [
                'contract_version' => self::CONTRACT_VERSION,
                'ok' => false,
                'view_id' => $view->id,
                'view_revision' => $view->revision,
                'columns' => $columns,
                'rows' => [],
                'returned' => 0,
                'error' => $result['error'] ?? [
                    'code' => 'wpe_admin_columns_query_failed',
                    'path' => '$',
                    'message' => 'Query read failed without a normalized error.',
                ],
            ];
        }

        $rows = $result['rows'] ?? null;
        if (!is_array($rows) || !array_is_list($rows)) {
            throw new RuntimeException('Query read consumer returned malformed rows.');
        }

        [$postIds, $postIdByRow] = $fieldFieldByColumn === []
            ? [[], []]
            : $this->postIds($rows);
        $fieldValues = $this->fieldValues($fieldFieldByColumn, $postIds, $context);

        $renderRows = [];
        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                throw new RuntimeException('Query read consumer returned a malformed row.');
            }
            $renderRow = [];
            foreach ($sourceByColumn as $columnKey => $source) {
                if ($source['owner'] === 'fields') {
                    $postId = $postIdByRow[$index] ?? null;
                    if (!is_int($postId) || !array_key_exists($postId, $fieldValues[$source['reference']] ?? [])) {
                        throw new RuntimeException('Admin Columns Fields source returned incomplete row evidence.');
                    }
                    $renderRow[$columnKey] = $fieldValues[$source['reference']][$postId];
                    continue;
                }
                $renderRow[$columnKey] = $row[$source['reference']] ?? null;
            }
            $renderRows[] = $renderRow;
        }

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'ok' => true,
            'view_id' => $view->id,
            'view_revision' => $view->revision,
            'columns' => $columns,
            'rows' => $renderRows,
            'returned' => count($renderRows),
            'error' => null,
        ];
    }

    private function assertReadableView(Definition $view): void
    {
        if ($view->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Admin Columns runtime reads require a published View.');
        }
        if (($view->payload['enabled'] ?? false) !== true) {
            throw new InvalidArgumentException('Admin Columns runtime reads require an enabled View.');
        }
    }

    /**
     * @param array<mixed> $fieldSchema
     * @return array{
     *     0:list<array<string,mixed>>,
     *     1:array<string,string>,
     *     2:array<string,string>,
     *     3:array<string,array{owner:string,reference:string}>,
     *     4:list<string>
     * }
     */
    private function readableColumns(Definition $view, array $fieldSchema): array
    {
        $rawColumns = $view->payload['columns'] ?? null;
        if (!is_array($rawColumns) || !array_is_list($rawColumns) || $rawColumns === []) {
            throw new RuntimeException('Published Admin Columns View has no bounded columns.');
        }

        $columns = [];
        $queryFieldByColumn = [];
        $fieldFieldByColumn = [];
        $sourceByColumn = [];
        $projection = [];
        $fieldReferences = [];
        foreach ($rawColumns as $column) {
            if (!is_array($column) || ($column['enabled'] ?? true) !== true) {
                continue;
            }
            $columnKey = $column['key'] ?? null;
            $label = $column['label'] ?? null;
            $format = $column['format'] ?? null;
            $source = $column['source'] ?? null;
            if (!is_string($columnKey) || !is_string($label) || !is_string($format) || !is_array($source)) {
                throw new RuntimeException('Published Admin Columns View contains malformed column metadata.');
            }

            $owner = $source['owner'] ?? null;
            $fieldRef = $source['reference'] ?? null;
            if (!is_string($owner) || !is_string($fieldRef)) {
                throw new RuntimeException('Published Admin Columns View contains malformed source ownership metadata.');
            }

            if (in_array($owner, ['native', 'query'], true)) {
                if (!array_key_exists($fieldRef, $fieldSchema)) {
                    throw new InvalidArgumentException(sprintf(
                        'Admin Columns column "%s" source is not declared by the Query Data Source.',
                        $columnKey,
                    ));
                }
                $queryFieldByColumn[$columnKey] = $fieldRef;
                if (!in_array($fieldRef, $projection, true)) {
                    $projection[] = $fieldRef;
                }
            } elseif ($owner === 'fields') {
                if (preg_match(self::FIELD_REFERENCE_PATTERN, $fieldRef) !== 1) {
                    throw new InvalidArgumentException(sprintf(
                        'Admin Columns column "%s" Fields reference is malformed.',
                        $columnKey,
                    ));
                }
                if (isset($fieldReferences[$fieldRef])) {
                    throw new InvalidArgumentException('Admin Columns Fields references must be unique within one bounded View.');
                }
                if (count($fieldReferences) >= self::MAX_FIELD_COLUMNS) {
                    throw new InvalidArgumentException(sprintf(
                        'Admin Columns View exceeds the V1 maximum of %d enabled Fields columns.',
                        self::MAX_FIELD_COLUMNS,
                    ));
                }
                $fieldReferences[$fieldRef] = true;
                $fieldFieldByColumn[$columnKey] = $fieldRef;
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Admin Columns column "%s" source owner is not readable in V1.',
                    $columnKey,
                ));
            }

            $sourceByColumn[$columnKey] = ['owner' => $owner, 'reference' => $fieldRef];
            $columns[] = [
                'key' => $columnKey,
                'label' => $label,
                'format' => $format,
                'primary' => ($column['primary'] ?? false) === true,
                'source_owner' => $owner,
            ];
        }

        if ($columns === []) {
            throw new InvalidArgumentException('Admin Columns View has no readable enabled columns.');
        }
        if ($projection === [] && $fieldFieldByColumn === []) {
            throw new InvalidArgumentException('Admin Columns View has no bounded readable projection.');
        }

        return [$columns, $queryFieldByColumn, $fieldFieldByColumn, $sourceByColumn, $projection];
    }

    /**
     * @param list<array<mixed>> $rows
     * @return array{0:list<int>,1:array<int,int>}
     */
    private function postIds(array $rows): array
    {
        $postIds = [];
        $postIdByRow = [];
        $seen = [];
        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                throw new RuntimeException('Query read consumer returned a malformed row.');
            }
            $postId = $row['post.id'] ?? null;
            if (!is_int($postId) || $postId < 1 || isset($seen[$postId])) {
                throw new RuntimeException('Admin Columns Fields composition requires unique positive Query post ids.');
            }
            $seen[$postId] = true;
            $postIds[] = $postId;
            $postIdByRow[$index] = $postId;
        }
        return [$postIds, $postIdByRow];
    }

    /**
     * @param array<string,string> $fieldFieldByColumn
     * @param list<int> $postIds
     * @return array<string,array<int,mixed>>
     */
    private function fieldValues(array $fieldFieldByColumn, array $postIds, ExecutionContext $context): array
    {
        if ($fieldFieldByColumn === [] || $postIds === []) {
            return [];
        }
        if (!$this->fields instanceof FieldValueReadConsumerInterface) {
            throw new RuntimeException('Admin Columns Fields source requires the certified Field value read consumer.');
        }

        $values = [];
        foreach ($fieldFieldByColumn as $fieldReference) {
            $result = $this->fields->readValues($fieldReference, $postIds, $context);
            if (($result['contract_version'] ?? null) !== FieldValueReadConsumerInterface::CONTRACT_VERSION
                || ($result['field_ref'] ?? null) !== $fieldReference
                || !is_array($result['rows'] ?? null)
                || !array_is_list($result['rows'])
                || count($result['rows']) !== count($postIds)
            ) {
                throw new RuntimeException('Admin Columns Fields source returned malformed owner evidence.');
            }

            $byPostId = [];
            foreach ($result['rows'] as $index => $row) {
                if (!is_array($row)
                    || ($row['post_id'] ?? null) !== $postIds[$index]
                    || !array_key_exists('value', $row)
                ) {
                    throw new RuntimeException('Admin Columns Fields source returned out-of-order owner rows.');
                }
                $byPostId[$postIds[$index]] = $row['value'];
            }
            $values[$fieldReference] = $byPostId;
        }

        return $values;
    }

    /**
     * @param mixed $value
     * @param array<string,string> $fieldByColumn
     * @return list<array{field_ref:string,operator:string,value:mixed}>
     */
    private function filters(mixed $value, array $fieldByColumn): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException('Admin Columns filters must be a list.');
        }

        $filters = [];
        foreach ($value as $index => $filter) {
            if (!is_array($filter) || array_is_list($filter)) {
                throw new InvalidArgumentException(sprintf('Admin Columns filter %d must be an object/map.', $index));
            }
            $this->assertKnownKeys($filter, self::FILTER_KEYS, sprintf('Admin Columns filter %d', $index));
            $columnKey = $filter['column_key'] ?? null;
            if (!is_string($columnKey) || !isset($fieldByColumn[$columnKey])) {
                throw new InvalidArgumentException(sprintf(
                    'Admin Columns filter %d references a column without Query-owned V1 filtering.',
                    $index,
                ));
            }
            if (!array_key_exists('operator', $filter) || !array_key_exists('value', $filter)) {
                throw new InvalidArgumentException(sprintf('Admin Columns filter %d is incomplete.', $index));
            }
            $filters[] = [
                'field_ref' => $fieldByColumn[$columnKey],
                'operator' => $filter['operator'],
                'value' => $filter['value'],
            ];
        }

        return $filters;
    }

    /**
     * @param mixed $value
     * @param array<string,string> $fieldByColumn
     * @return list<array{field_ref:string,direction:string}>
     */
    private function orderBy(mixed $value, array $fieldByColumn): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException('Admin Columns order_by must be a list.');
        }

        $orderBy = [];
        foreach ($value as $index => $order) {
            if (!is_array($order) || array_is_list($order)) {
                throw new InvalidArgumentException(sprintf('Admin Columns order %d must be an object/map.', $index));
            }
            $this->assertKnownKeys($order, self::ORDER_KEYS, sprintf('Admin Columns order %d', $index));
            $columnKey = $order['column_key'] ?? null;
            if (!is_string($columnKey) || !isset($fieldByColumn[$columnKey])) {
                throw new InvalidArgumentException(sprintf(
                    'Admin Columns order %d references a column without Query-owned V1 ordering.',
                    $index,
                ));
            }
            $direction = $order['direction'] ?? null;
            if (!is_string($direction) || !in_array($direction, ['asc', 'desc'], true)) {
                throw new InvalidArgumentException(sprintf('Admin Columns order %d direction must be asc or desc.', $index));
            }
            $orderBy[] = [
                'field_ref' => $fieldByColumn[$columnKey],
                'direction' => $direction,
            ];
        }

        return $orderBy;
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
}
