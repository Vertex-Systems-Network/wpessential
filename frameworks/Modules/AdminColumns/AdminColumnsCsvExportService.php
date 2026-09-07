<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use LengthException;
use RuntimeException;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;

/**
 * Bounded internal CSV export orchestration over the certified Admin Columns read path.
 *
 * This service owns neither source execution nor authorization. Every row is
 * resolved through AdminColumnsReadAdapter and all CSV serialization is delegated
 * to AdminColumnsCsvExportEncoder.
 */
final readonly class AdminColumnsCsvExportService
{
    private const PAGE_SIZE = 100;
    private const MAX_ROWS = 1000;
    private const MAX_CURRENT_PAGE_SIZE = 100;
    private const MAX_OFFSET = 10000;

    /** @var list<string> */
    private const CONTROL_KEYS = ['filters', 'search', 'order_by'];

    /** @var list<string> */
    private const CURRENT_PAGE_KEYS = ['page_size', 'offset'];

    /** @var list<string> */
    private const SUPPORTED_SCOPES = ['current_page', 'selected_rows', 'all_matching'];

    public function __construct(
        private AdminColumnsReadAdapter $reads,
        private AdminColumnsCsvExportEncoder $encoder,
        private int $maxRows = self::MAX_ROWS,
        private ?AdminColumnsViewDefinitionService $views = null,
        private ?AdminColumnsCsvExportScopePolicy $scopePolicy = null,
    ) {
        if ($this->maxRows < 1 || $this->maxRows > self::MAX_ROWS) {
            throw new InvalidArgumentException(sprintf(
                'Admin Columns CSV export maxRows must be between 1 and %d.',
                self::MAX_ROWS,
            ));
        }
    }

    /**
     * Backward-compatible bounded all-matching export entrypoint.
     *
     * @param array<string,mixed> $controls
     */
    public function export(string $viewId, array $controls, ExecutionContext $context): string
    {
        $this->assertControls($controls);
        [$columns, $rows] = $this->collectAllMatching($viewId, $controls, $context, null);

        return $this->encoder->encode($columns, $rows);
    }

    /**
     * Execute one promoted V1 export scope without exposing transport behavior.
     *
     * @param array<string,mixed> $scopeRequest
     * @param array<string,mixed> $controls
     * @param array<string,mixed> $runtime
     */
    public function exportScoped(
        string $viewId,
        int $expectedViewRevision,
        array $scopeRequest,
        array $controls,
        array $runtime,
        ExecutionContext $context,
    ): string {
        if ($expectedViewRevision < 1) {
            throw new InvalidArgumentException('Admin Columns scoped export requires a positive expected View revision.');
        }
        $this->assertControls($controls);

        $views = $this->requiredViews();
        $scopePolicy = $this->requiredScopePolicy();
        $view = $views->get($viewId);
        $this->assertViewRevision($view->revision, $expectedViewRevision);

        $enabledColumnKeys = $this->enabledViewColumnKeys($view);
        $scope = $scopePolicy->normalize($scopeRequest, $enabledColumnKeys, self::SUPPORTED_SCOPES);
        $effectiveControls = $this->effectiveControls($controls, $scope);

        return match ($scope['scope']) {
            'current_page' => $this->exportCurrentPage(
                $viewId,
                $expectedViewRevision,
                $scope['selected_columns'],
                $effectiveControls,
                $runtime,
                $context,
            ),
            'selected_rows' => $this->exportSelectedRows(
                $view,
                $expectedViewRevision,
                $scope['selected_row_ids'],
                $scope['selected_columns'],
                $scope['respect_sort'],
                $effectiveControls,
                $runtime,
                $context,
            ),
            'all_matching' => $this->exportAllMatchingScope(
                $viewId,
                $expectedViewRevision,
                $scope['selected_columns'],
                $effectiveControls,
                $runtime,
                $context,
            ),
            default => throw new RuntimeException('Admin Columns CSV export scope normalization returned an unsupported scope.'),
        };
    }

    /**
     * @param list<string> $selectedColumns
     * @param array<string,mixed> $controls
     * @param array<string,mixed> $runtime
     */
    private function exportCurrentPage(
        string $viewId,
        int $expectedViewRevision,
        array $selectedColumns,
        array $controls,
        array $runtime,
        ExecutionContext $context,
    ): string {
        [$pageSize, $offset] = $this->currentPageRuntime($runtime);
        $page = $this->readPage($viewId, $controls, $pageSize, $offset, $context);
        [$revision, , $columns] = $this->assertStablePage(
            $page,
            $viewId,
            $pageSize,
            null,
            null,
            null,
        );
        $this->assertViewRevision($revision, $expectedViewRevision);

        /** @var list<array<string,scalar|null>> $rows */
        $rows = $page['rows'];
        [$selectedMetadata, $selectedRows] = $this->selectColumns($columns, $rows, $selectedColumns);

        return $this->encoder->encode($selectedMetadata, $selectedRows);
    }

    /**
     * @param list<int> $selectedRowIds
     * @param list<string> $selectedColumns
     * @param array<string,mixed> $controls
     * @param array<string,mixed> $runtime
     */
    private function exportSelectedRows(
        Definition $view,
        int $expectedViewRevision,
        array $selectedRowIds,
        array $selectedColumns,
        bool $respectSort,
        array $controls,
        array $runtime,
        ExecutionContext $context,
    ): string {
        $this->assertNoRuntime($runtime, 'selected_rows');
        $idColumnKey = $this->postIdColumnKey($view);
        $controls = $this->withSelectedRowFilter($controls, $idColumnKey, $selectedRowIds);
        $pageSize = count($selectedRowIds);
        $page = $this->readPage($view->id, $controls, $pageSize, 0, $context);
        [$revision, , $columns] = $this->assertStablePage(
            $page,
            $view->id,
            $pageSize,
            null,
            null,
            null,
        );
        $this->assertViewRevision($revision, $expectedViewRevision);

        /** @var list<array<string,scalar|null>> $rows */
        $rows = $page['rows'];
        $rows = $this->exactSelectedRows($rows, $idColumnKey, $selectedRowIds, $respectSort);
        [$selectedMetadata, $selectedRows] = $this->selectColumns($columns, $rows, $selectedColumns);

        return $this->encoder->encode($selectedMetadata, $selectedRows);
    }

    /**
     * @param list<string> $selectedColumns
     * @param array<string,mixed> $controls
     * @param array<string,mixed> $runtime
     */
    private function exportAllMatchingScope(
        string $viewId,
        int $expectedViewRevision,
        array $selectedColumns,
        array $controls,
        array $runtime,
        ExecutionContext $context,
    ): string {
        $this->assertNoRuntime($runtime, 'all_matching');
        [$columns, $rows] = $this->collectAllMatching(
            $viewId,
            $controls,
            $context,
            $expectedViewRevision,
        );
        [$selectedMetadata, $selectedRows] = $this->selectColumns($columns, $rows, $selectedColumns);

        return $this->encoder->encode($selectedMetadata, $selectedRows);
    }

    /**
     * @param array<string,mixed> $controls
     * @return array{0:list<array{key:string,label:string}>,1:list<array<string,scalar|null>>}
     */
    private function collectAllMatching(
        string $viewId,
        array $controls,
        ExecutionContext $context,
        ?int $expectedViewRevision,
    ): array {
        $offset = 0;
        $rows = [];
        $baselineRevision = null;
        $baselineColumns = null;
        $encoderColumns = null;

        while ($offset < $this->maxRows) {
            $pageSize = min(self::PAGE_SIZE, $this->maxRows - $offset);
            $page = $this->readPage($viewId, $controls, $pageSize, $offset, $context);

            [$baselineRevision, $baselineColumns, $encoderColumns] = $this->assertStablePage(
                $page,
                $viewId,
                $pageSize,
                $baselineRevision,
                $baselineColumns,
                $encoderColumns,
            );
            if ($expectedViewRevision !== null) {
                $this->assertViewRevision($baselineRevision, $expectedViewRevision);
            }

            /** @var list<array<string,scalar|null>> $pageRows */
            $pageRows = $page['rows'];
            foreach ($pageRows as $row) {
                $rows[] = $row;
            }

            $returned = $page['returned'];
            $offset += $returned;
            if ($returned < $pageSize) {
                break;
            }
        }

        if ($offset === $this->maxRows) {
            $probe = $this->readPage($viewId, $controls, 1, $offset, $context);
            [$probeRevision] = $this->assertStablePage(
                $probe,
                $viewId,
                1,
                $baselineRevision,
                $baselineColumns,
                $encoderColumns,
            );
            if ($expectedViewRevision !== null) {
                $this->assertViewRevision($probeRevision, $expectedViewRevision);
            }
            if (($probe['returned'] ?? null) !== 0) {
                throw new LengthException(sprintf(
                    'Admin Columns CSV export exceeds the V1 maximum of %d rows.',
                    $this->maxRows,
                ));
            }
        }

        if (!is_array($encoderColumns) || $encoderColumns === []) {
            throw new RuntimeException('Admin Columns CSV export did not establish a stable column contract.');
        }

        return [$encoderColumns, $rows];
    }

    /**
     * @param array<string,mixed> $controls
     * @return array<string,mixed>
     */
    private function readPage(
        string $viewId,
        array $controls,
        int $pageSize,
        int $offset,
        ExecutionContext $context,
    ): array {
        $input = $controls;
        $input['page_size'] = $pageSize;
        $input['offset'] = $offset;

        $page = $this->reads->read($viewId, $input, $context);
        if (($page['ok'] ?? null) !== true) {
            $code = is_array($page['error'] ?? null) && is_string($page['error']['code'] ?? null)
                ? $page['error']['code']
                : 'wpe_admin_columns_export_read_failed';
            throw new RuntimeException(sprintf('Admin Columns CSV export read failed: %s.', $code));
        }

        return $page;
    }

    /**
     * @param array<string,mixed> $page
     * @param int|null $baselineRevision
     * @param list<array<string,mixed>>|null $baselineColumns
     * @param list<array{key:string,label:string}>|null $encoderColumns
     * @return array{0:int,1:list<array<string,mixed>>,2:list<array{key:string,label:string}>}
     */
    private function assertStablePage(
        array $page,
        string $viewId,
        int $requestedPageSize,
        ?int $baselineRevision,
        ?array $baselineColumns,
        ?array $encoderColumns,
    ): array {
        if (($page['contract_version'] ?? null) !== AdminColumnsReadAdapter::CONTRACT_VERSION
            || ($page['view_id'] ?? null) !== $viewId
        ) {
            throw new RuntimeException('Admin Columns CSV export received malformed read contract identity.');
        }

        $revision = $page['view_revision'] ?? null;
        if (!is_int($revision) || $revision < 1) {
            throw new RuntimeException('Admin Columns CSV export received malformed View revision evidence.');
        }

        $columns = $page['columns'] ?? null;
        if (!is_array($columns) || !array_is_list($columns) || $columns === []) {
            throw new RuntimeException('Admin Columns CSV export received malformed column metadata.');
        }

        $pageRows = $page['rows'] ?? null;
        $returned = $page['returned'] ?? null;
        if (!is_array($pageRows)
            || !array_is_list($pageRows)
            || !is_int($returned)
            || $returned < 0
            || $returned > $requestedPageSize
            || $returned !== count($pageRows)
        ) {
            throw new RuntimeException('Admin Columns CSV export received malformed bounded row evidence.');
        }

        if ($baselineRevision !== null && $revision !== $baselineRevision) {
            throw new RuntimeException('Admin Columns CSV export View revision changed between pages.');
        }
        if ($baselineColumns !== null && $columns !== $baselineColumns) {
            throw new RuntimeException('Admin Columns CSV export column contract changed between pages.');
        }

        if ($encoderColumns === null) {
            $encoderColumns = [];
            $seen = [];
            foreach ($columns as $column) {
                if (!is_array($column)) {
                    throw new RuntimeException('Admin Columns CSV export column metadata is malformed.');
                }
                $key = $column['key'] ?? null;
                $label = $column['label'] ?? null;
                if (!is_string($key) || $key === '' || !is_string($label) || $label === '' || isset($seen[$key])) {
                    throw new RuntimeException('Admin Columns CSV export column identity is malformed.');
                }
                $seen[$key] = true;
                $encoderColumns[] = ['key' => $key, 'label' => $label];
            }
        }

        return [
            $baselineRevision ?? $revision,
            $baselineColumns ?? $columns,
            $encoderColumns,
        ];
    }

    /**
     * @param array<string,mixed> $controls
     * @param array{respect_filters:bool,respect_sort:bool} $scope
     * @return array<string,mixed>
     */
    private function effectiveControls(array $controls, array $scope): array
    {
        if (!$scope['respect_filters']) {
            unset($controls['filters'], $controls['search']);
        }
        if (!$scope['respect_sort']) {
            unset($controls['order_by']);
        }

        return $controls;
    }

    /**
     * @param array<string,mixed> $controls
     * @param list<int> $selectedRowIds
     * @return array<string,mixed>
     */
    private function withSelectedRowFilter(array $controls, string $idColumnKey, array $selectedRowIds): array
    {
        $filters = $controls['filters'] ?? [];
        if (!is_array($filters) || !array_is_list($filters)) {
            throw new InvalidArgumentException('Admin Columns CSV export filters must be a list.');
        }
        $filters[] = [
            'column_key' => $idColumnKey,
            'operator' => 'in',
            'value' => $selectedRowIds,
        ];
        $controls['filters'] = $filters;

        return $controls;
    }

    /**
     * @param list<array<string,scalar|null>> $rows
     * @param list<int> $selectedRowIds
     * @return list<array<string,scalar|null>>
     */
    private function exactSelectedRows(
        array $rows,
        string $idColumnKey,
        array $selectedRowIds,
        bool $respectSort,
    ): array {
        $requested = array_fill_keys($selectedRowIds, true);
        $byId = [];
        foreach ($rows as $row) {
            $rowId = $row[$idColumnKey] ?? null;
            if (!is_int($rowId) || $rowId < 1 || !isset($requested[$rowId]) || isset($byId[$rowId])) {
                throw new RuntimeException('Admin Columns selected-row export returned invalid authoritative row identity.');
            }
            $byId[$rowId] = $row;
        }
        if (count($byId) !== count($selectedRowIds)) {
            throw new RuntimeException('Admin Columns selected-row export did not return the exact requested row membership.');
        }
        if ($respectSort) {
            return $rows;
        }

        $ordered = [];
        foreach ($selectedRowIds as $rowId) {
            $ordered[] = $byId[$rowId];
        }

        return $ordered;
    }

    /**
     * @param list<array{key:string,label:string}> $columns
     * @param list<array<string,scalar|null>> $rows
     * @param list<string> $selectedColumnKeys
     * @return array{0:list<array{key:string,label:string}>,1:list<array<string,scalar|null>>}
     */
    private function selectColumns(array $columns, array $rows, array $selectedColumnKeys): array
    {
        $metadataByKey = [];
        foreach ($columns as $column) {
            $metadataByKey[$column['key']] = $column;
        }

        $selectedMetadata = [];
        foreach ($selectedColumnKeys as $columnKey) {
            if (!isset($metadataByKey[$columnKey])) {
                throw new RuntimeException('Admin Columns CSV export selected Column is absent from the authoritative read contract.');
            }
            $selectedMetadata[] = $metadataByKey[$columnKey];
        }

        $selectedRows = [];
        foreach ($rows as $row) {
            $selectedRow = [];
            foreach ($selectedColumnKeys as $columnKey) {
                if (!array_key_exists($columnKey, $row)) {
                    throw new RuntimeException('Admin Columns CSV export row is missing a selected Column value.');
                }
                $selectedRow[$columnKey] = $row[$columnKey];
            }
            $selectedRows[] = $selectedRow;
        }

        return [$selectedMetadata, $selectedRows];
    }

    /** @return array{0:int,1:int} */
    private function currentPageRuntime(array $runtime): array
    {
        $this->assertKnownKeys($runtime, self::CURRENT_PAGE_KEYS, 'Admin Columns current-page export runtime');
        foreach (self::CURRENT_PAGE_KEYS as $key) {
            if (!array_key_exists($key, $runtime)) {
                throw new InvalidArgumentException('Admin Columns current-page export requires explicit page_size and offset.');
            }
        }

        $pageSize = $runtime['page_size'];
        $offset = $runtime['offset'];
        if (!is_int($pageSize) || $pageSize < 1 || $pageSize > self::MAX_CURRENT_PAGE_SIZE) {
            throw new InvalidArgumentException('Admin Columns current-page export page_size is outside the bounded V1 range.');
        }
        if (!is_int($offset) || $offset < 0 || $offset > self::MAX_OFFSET) {
            throw new InvalidArgumentException('Admin Columns current-page export offset is outside the bounded V1 range.');
        }

        return [$pageSize, $offset];
    }

    private function assertNoRuntime(array $runtime, string $scope): void
    {
        if ($runtime !== []) {
            throw new InvalidArgumentException(sprintf(
                'Admin Columns %s export scope does not accept current-page runtime coordinates.',
                $scope,
            ));
        }
    }

    /** @return list<string> */
    private function enabledViewColumnKeys(Definition $view): array
    {
        $columns = $view->payload['columns'] ?? null;
        if (!is_array($columns) || !array_is_list($columns) || $columns === []) {
            throw new RuntimeException('Admin Columns scoped export View has no bounded Column definition.');
        }

        $keys = [];
        $seen = [];
        foreach ($columns as $column) {
            if (!is_array($column) || ($column['enabled'] ?? true) !== true) {
                continue;
            }
            $key = $column['key'] ?? null;
            if (!is_string($key) || $key === '' || isset($seen[$key])) {
                throw new RuntimeException('Admin Columns scoped export View contains malformed enabled Column identity.');
            }
            $seen[$key] = true;
            $keys[] = $key;
        }
        if ($keys === []) {
            throw new RuntimeException('Admin Columns scoped export View has no enabled Columns.');
        }

        return $keys;
    }

    private function postIdColumnKey(Definition $view): string
    {
        $columns = $view->payload['columns'] ?? null;
        if (!is_array($columns) || !array_is_list($columns)) {
            throw new RuntimeException('Admin Columns selected-row export View columns are malformed.');
        }

        $matches = [];
        foreach ($columns as $column) {
            if (!is_array($column) || ($column['enabled'] ?? true) !== true) {
                continue;
            }
            $source = $column['source'] ?? null;
            $key = $column['key'] ?? null;
            if (!is_array($source) || !is_string($key)) {
                continue;
            }
            if (in_array($source['owner'] ?? null, ['native', 'query'], true)
                && ($source['reference'] ?? null) === 'post.id'
            ) {
                $matches[] = $key;
            }
        }

        if (count($matches) !== 1) {
            throw new InvalidArgumentException(
                'Admin Columns selected-row export requires exactly one enabled native/query post.id Column.',
            );
        }

        return $matches[0];
    }

    private function assertViewRevision(int $actualRevision, int $expectedRevision): void
    {
        if ($actualRevision !== $expectedRevision) {
            throw new RuntimeException(sprintf(
                'Admin Columns CSV export View revision changed: expected %d, current revision is %d.',
                $expectedRevision,
                $actualRevision,
            ));
        }
    }

    private function requiredViews(): AdminColumnsViewDefinitionService
    {
        if (!$this->views instanceof AdminColumnsViewDefinitionService) {
            throw new RuntimeException('Admin Columns scoped CSV export requires the canonical View service.');
        }

        return $this->views;
    }

    private function requiredScopePolicy(): AdminColumnsCsvExportScopePolicy
    {
        if (!$this->scopePolicy instanceof AdminColumnsCsvExportScopePolicy) {
            throw new RuntimeException('Admin Columns scoped CSV export requires the canonical scope policy.');
        }

        return $this->scopePolicy;
    }

    /** @param array<string,mixed> $controls */
    private function assertControls(array $controls): void
    {
        if (array_is_list($controls) && $controls !== []) {
            throw new InvalidArgumentException('Admin Columns CSV export controls must be an object/map.');
        }

        $this->assertKnownKeys($controls, self::CONTROL_KEYS, 'Admin Columns CSV export controls');
    }

    /** @param array<string,mixed> $map @param list<string> $allowed */
    private function assertKnownKeys(array $map, array $allowed, string $label): void
    {
        foreach (array_keys($map) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException(sprintf(
                    '%s contains unsupported input "%s".',
                    $label,
                    is_scalar($key) ? (string) $key : 'unknown',
                ));
            }
        }
    }
}
