<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use LengthException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Modules\AdminColumns\AdminColumnsCsvExportEncoder;
use WPEssential\Modules\AdminColumns\AdminColumnsCsvExportScopePolicy;
use WPEssential\Modules\AdminColumns\AdminColumnsCsvExportService;
use WPEssential\Modules\AdminColumns\AdminColumnsReadAdapter;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class AdminColumnsCsvExportServiceTest extends TestCase
{
    public function testExportsMultipleAuthorizedReadPagesInStableViewColumnOrder(): void
    {
        $query = new class implements QueryReadConsumerInterface {
            /** @var list<array<string,mixed>> */
            public array $requests = [];

            public function describe(string $sourceRef, ExecutionContext $context): array
            {
                return $this->description();
            }

            public function read(array $request, ExecutionContext $context): array
            {
                $this->requests[] = $request;
                $offset = $request['offset'];
                $pageSize = $request['page_size'];
                $remaining = max(0, 150 - $offset);
                $count = min($pageSize, $remaining);
                $rows = [];
                for ($index = 0; $index < $count; $index++) {
                    $id = $offset + $index + 1;
                    $rows[] = [
                        'post.id' => $id,
                        'post.title' => $id === 2 ? '=SUM(A1:A2)' : 'Post ' . $id,
                    ];
                }

                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'ok' => true,
                    'source_ref' => 'wordpress.posts',
                    'projection' => $request['projection'],
                    'rows' => $rows,
                    'returned' => count($rows),
                    'error' => null,
                ];
            }

            /** @return array<string,mixed> */
            private function description(): array
            {
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'source_ref' => 'wordpress.posts',
                    'source_type' => 'wordpress.posts',
                    'capability_version' => 1,
                    'available' => true,
                    'field_schema' => [
                        'post.id' => 'integer',
                        'post.title' => 'string',
                        'post.type' => 'string',
                    ],
                    'predicates' => ['eq'],
                    'sort_modes' => ['field'],
                    'pagination_modes' => ['offset'],
                    'max_page_size' => 100,
                ];
            }
        };

        [$service, $viewId] = $this->service($query);
        $csv = $service->export($viewId, [], $this->context());

        self::assertStringStartsWith("\"ID\",\"Title\"\r\n\"1\",\"Post 1\"\r\n\"2\",\"'=SUM(A1:A2)\"\r\n", $csv);
        self::assertStringEndsWith("\"150\",\"Post 150\"\r\n", $csv);
        self::assertCount(2, $query->requests);
        self::assertSame(100, $query->requests[0]['page_size']);
        self::assertSame(0, $query->requests[0]['offset']);
        self::assertSame(100, $query->requests[1]['page_size']);
        self::assertSame(100, $query->requests[1]['offset']);
    }

    public function testFailsClosedWhenAnotherAuthorizedRowExistsPastFiniteExportCeiling(): void
    {
        $query = $this->datasetQuery(3);
        [$service, $viewId] = $this->service($query, 2);

        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('maximum of 2 rows');
        $service->export($viewId, [], $this->context());
    }

    public function testPropagatesNormalizedReadFailureInsteadOfReturningPartialCsv(): void
    {
        $query = new class implements QueryReadConsumerInterface {
            public function describe(string $sourceRef, ExecutionContext $context): array
            {
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'source_ref' => 'wordpress.posts',
                    'source_type' => 'wordpress.posts',
                    'capability_version' => 1,
                    'available' => true,
                    'field_schema' => [
                        'post.id' => 'integer',
                        'post.title' => 'string',
                        'post.type' => 'string',
                    ],
                    'predicates' => ['eq'],
                    'sort_modes' => ['field'],
                    'pagination_modes' => ['offset'],
                    'max_page_size' => 100,
                ];
            }

            public function read(array $request, ExecutionContext $context): array
            {
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'ok' => false,
                    'source_ref' => 'wordpress.posts',
                    'projection' => [],
                    'rows' => [],
                    'returned' => 0,
                    'error' => [
                        'code' => 'wpe_query_policy_denied',
                        'path' => '$',
                        'message' => 'Denied.',
                    ],
                ];
            }
        };

        [$service, $viewId] = $this->service($query);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('wpe_query_policy_denied');
        $service->export($viewId, [], $this->context());
    }

    public function testRejectsCallerPaginationOverride(): void
    {
        [$service, $viewId] = $this->service($this->datasetQuery(0));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('page_size');
        $service->export($viewId, ['page_size' => 500], $this->context());
    }

    public function testCurrentPageScopeUsesExplicitCoordinatesAndSelectedColumnsOnly(): void
    {
        $query = new AdminColumnsCsvExportScopeQueryStub(10);
        [$service, $viewId] = $this->service($query);

        $csv = $service->exportScoped(
            $viewId,
            1,
            $this->scopeRequest('current_page', [], ['title'], false, false),
            [
                'filters' => [['column_key' => 'id', 'operator' => 'eq', 'value' => 999]],
                'search' => 'ignored',
                'order_by' => [['column_key' => 'id', 'direction' => 'desc']],
            ],
            ['page_size' => 2, 'offset' => 3],
            $this->context(),
        );

        self::assertSame("\"Title\"\r\n\"Post 4\"\r\n\"Post 5\"\r\n", $csv);
        self::assertCount(1, $query->requests);
        self::assertSame(2, $query->requests[0]['page_size']);
        self::assertSame(3, $query->requests[0]['offset']);
        self::assertSame([], $query->requests[0]['order_by']);
        self::assertArrayNotHasKey('search', $query->requests[0]);
        self::assertCount(1, $query->requests[0]['filters']);
        self::assertSame('post.type', $query->requests[0]['filters'][0]['field_ref']);
    }

    public function testSelectedRowsUsesQueryInPredicateAndRestoresCallerOrderWhenSortIsIgnored(): void
    {
        $query = new AdminColumnsCsvExportScopeQueryStub(10);
        [$service, $viewId] = $this->service($query);

        $csv = $service->exportScoped(
            $viewId,
            1,
            $this->scopeRequest('selected_rows', [4, 2, 7], ['id', 'title'], false, false),
            ['order_by' => [['column_key' => 'id', 'direction' => 'desc']]],
            [],
            $this->context(),
        );

        self::assertSame(
            "\"ID\",\"Title\"\r\n\"4\",\"Post 4\"\r\n\"2\",\"Post 2\"\r\n\"7\",\"Post 7\"\r\n",
            $csv,
        );
        self::assertCount(1, $query->requests);
        self::assertSame(3, $query->requests[0]['page_size']);
        self::assertSame([], $query->requests[0]['order_by']);
        self::assertSame('post.id', $query->requests[0]['filters'][1]['field_ref']);
        self::assertSame('in', $query->requests[0]['filters'][1]['operator']);
        self::assertSame([4, 2, 7], $query->requests[0]['filters'][1]['value']);
    }

    public function testSelectedRowsKeepsQueryAuthoritativeOrderWhenSortIsRespected(): void
    {
        $query = new AdminColumnsCsvExportScopeQueryStub(10);
        [$service, $viewId] = $this->service($query);

        $csv = $service->exportScoped(
            $viewId,
            1,
            $this->scopeRequest('selected_rows', [4, 2, 7], ['id'], false, true),
            ['order_by' => [['column_key' => 'id', 'direction' => 'desc']]],
            [],
            $this->context(),
        );

        self::assertSame("\"ID\"\r\n\"7\"\r\n\"4\"\r\n\"2\"\r\n", $csv);
        self::assertSame(
            [['field_ref' => 'post.id', 'direction' => 'desc']],
            $query->requests[0]['order_by'],
        );
    }

    public function testSelectedRowsFailsClosedWithoutExplicitAuthoredPostIdColumn(): void
    {
        $query = new AdminColumnsCsvExportScopeQueryStub(5);
        [$service, $viewId] = $this->service($query, 1000, $this->payloadWithoutId());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exactly one enabled native/query post.id Column');
        $service->exportScoped(
            $viewId,
            1,
            $this->scopeRequest('selected_rows', [2], ['title'], true, true),
            [],
            [],
            $this->context(),
        );
    }

    public function testSelectedRowsFailsClosedOnIncompleteAuthoritativeMembership(): void
    {
        $query = new AdminColumnsCsvExportScopeQueryStub(5, 4);
        [$service, $viewId] = $this->service($query);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('exact requested row membership');
        $service->exportScoped(
            $viewId,
            1,
            $this->scopeRequest('selected_rows', [2, 4], ['id'], false, false),
            [],
            [],
            $this->context(),
        );
    }

    public function testScopedExportFailsClosedOnExpectedViewRevisionDrift(): void
    {
        $query = new AdminColumnsCsvExportScopeQueryStub(5);
        [$service, $viewId] = $this->service($query);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('expected 2, current revision is 1');
        $service->exportScoped(
            $viewId,
            2,
            $this->scopeRequest('all_matching', [], ['id'], true, true),
            [],
            [],
            $this->context(),
        );
    }

    public function testAllMatchingScopePreservesBoundedLoopAndProjectsSelectedColumns(): void
    {
        $query = new AdminColumnsCsvExportScopeQueryStub(3);
        [$service, $viewId] = $this->service($query, 10);

        $csv = $service->exportScoped(
            $viewId,
            1,
            $this->scopeRequest('all_matching', [], ['title'], true, true),
            [],
            [],
            $this->context(),
        );

        self::assertSame(
            "\"Title\"\r\n\"Post 1\"\r\n\"Post 2\"\r\n\"Post 3\"\r\n",
            $csv,
        );
        self::assertCount(1, $query->requests);
    }

    /**
     * @param array<string,mixed>|null $payload
     * @return array{0:AdminColumnsCsvExportService,1:string}
     */
    private function service(
        QueryReadConsumerInterface $query,
        int $maxRows = 1000,
        ?array $payload = null,
    ): array {
        $views = new AdminColumnsViewDefinitionService(
            $this->repository(),
            new AdminColumnsViewDefinitionNormalizer(),
            static fn (): string => '01990f6e-1f30-4000-8000-000000000398',
        );
        $view = $views->save($payload ?? $this->payload(), DefinitionStatus::Published);
        $reads = new AdminColumnsReadAdapter($views, $query);

        return [
            new AdminColumnsCsvExportService(
                $reads,
                new AdminColumnsCsvExportEncoder(),
                $maxRows,
                $views,
                new AdminColumnsCsvExportScopePolicy(),
            ),
            $view->id,
        ];
    }

    private function datasetQuery(int $totalRows): QueryReadConsumerInterface
    {
        return new AdminColumnsCsvExportScopeQueryStub($totalRows);
    }

    private function repository(): DefinitionRepositoryInterface
    {
        return new class implements DefinitionRepositoryInterface {
            /** @var array<string,Definition> */
            private array $definitions = [];

            public function save(Definition $definition): void
            {
                $this->definitions[$definition->id] = $definition;
            }

            public function get(string $id): ?Definition
            {
                return $this->definitions[$id] ?? null;
            }

            public function byType(string $type): array
            {
                return array_values(array_filter(
                    $this->definitions,
                    static fn (Definition $definition): bool => $definition->type === $type,
                ));
            }

            public function dependentsOf(string $id): array
            {
                return [];
            }
        };
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'view_key' => 'posts_export',
            'name' => 'Posts export',
            'enabled' => true,
            'target' => ['type' => 'post_type', 'key' => 'post'],
            'columns' => [
                [
                    'uuid' => '01990f6e-1f30-4000-8000-000000000301',
                    'key' => 'id',
                    'label' => 'ID',
                    'source' => ['owner' => 'native', 'reference' => 'post.id'],
                    'format' => 'number',
                    'primary' => true,
                ],
                [
                    'uuid' => '01990f6e-1f30-4000-8000-000000000302',
                    'key' => 'title',
                    'label' => 'Title',
                    'source' => ['owner' => 'native', 'reference' => 'post.title'],
                    'format' => 'text',
                ],
            ],
        ];
    }

    /** @return array<string,mixed> */
    private function payloadWithoutId(): array
    {
        $payload = $this->payload();
        $payload['columns'] = [$payload['columns'][1]];
        return $payload;
    }

    /**
     * @param list<int> $selectedRowIds
     * @param list<string> $selectedColumns
     * @return array<string,mixed>
     */
    private function scopeRequest(
        string $scope,
        array $selectedRowIds,
        array $selectedColumns,
        bool $respectFilters,
        bool $respectSort,
    ): array {
        return [
            'scope' => $scope,
            'selected_row_ids' => $selectedRowIds,
            'selected_columns' => $selectedColumns,
            'respect_filters' => $respectFilters,
            'respect_sort' => $respectSort,
        ];
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}

final class AdminColumnsCsvExportScopeQueryStub implements QueryReadConsumerInterface
{
    /** @var list<array<string,mixed>> */
    public array $requests = [];

    public function __construct(
        private readonly int $totalRows,
        private readonly ?int $dropId = null,
    ) {
    }

    public function describe(string $sourceRef, ExecutionContext $context): array
    {
        return [
            'contract_version' => self::CONTRACT_VERSION,
            'source_ref' => 'wordpress.posts',
            'source_type' => 'wordpress.posts',
            'capability_version' => 1,
            'available' => true,
            'field_schema' => [
                'post.id' => 'integer',
                'post.title' => 'string',
                'post.type' => 'string',
            ],
            'predicates' => ['eq', 'neq', 'in', 'not_in'],
            'sort_modes' => ['field'],
            'pagination_modes' => ['offset'],
            'max_page_size' => 100,
        ];
    }

    public function read(array $request, ExecutionContext $context): array
    {
        $this->requests[] = $request;
        $rows = [];
        for ($id = 1; $id <= $this->totalRows; $id++) {
            if ($this->dropId === $id) {
                continue;
            }
            $rows[] = [
                'post.id' => $id,
                'post.title' => 'Post ' . $id,
                'post.type' => 'post',
            ];
        }

        foreach ($request['filters'] as $filter) {
            $fieldRef = $filter['field_ref'];
            $operator = $filter['operator'];
            $value = $filter['value'];
            if ($operator === 'eq') {
                $rows = array_values(array_filter(
                    $rows,
                    static fn (array $row): bool => ($row[$fieldRef] ?? null) === $value,
                ));
                continue;
            }
            if ($operator === 'in' && is_array($value)) {
                $rows = array_values(array_filter(
                    $rows,
                    static fn (array $row): bool => in_array($row[$fieldRef] ?? null, $value, true),
                ));
            }
        }

        foreach (array_reverse($request['order_by']) as $order) {
            $fieldRef = $order['field_ref'];
            $direction = $order['direction'];
            usort($rows, static function (array $left, array $right) use ($fieldRef, $direction): int {
                $comparison = ($left[$fieldRef] ?? null) <=> ($right[$fieldRef] ?? null);
                return $direction === 'desc' ? -$comparison : $comparison;
            });
        }

        $rows = array_slice($rows, $request['offset'], $request['page_size']);
        $projection = $request['projection'];
        $projected = [];
        foreach ($rows as $row) {
            $projectedRow = [];
            foreach ($projection as $fieldRef) {
                $projectedRow[$fieldRef] = $row[$fieldRef] ?? null;
            }
            $projected[] = $projectedRow;
        }

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'ok' => true,
            'source_ref' => 'wordpress.posts',
            'projection' => $projection,
            'rows' => $projected,
            'returned' => count($projected),
            'error' => null,
        ];
    }
}
