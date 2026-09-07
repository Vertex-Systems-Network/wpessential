<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use LengthException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Modules\AdminColumns\AdminColumnsCsvExportEncoder;
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('page_size');
        $service->export($viewId, ['page_size' => 500], $this->context());
    }

    /**
     * @return array{0:AdminColumnsCsvExportService,1:string}
     */
    private function service(QueryReadConsumerInterface $query, int $maxRows = 1000): array
    {
        $views = new AdminColumnsViewDefinitionService(
            $this->repository(),
            new AdminColumnsViewDefinitionNormalizer(),
            static fn (): string => '01990f6e-1f30-4000-8000-000000000398',
        );
        $view = $views->save($this->payload(), DefinitionStatus::Published);
        $reads = new AdminColumnsReadAdapter($views, $query);

        return [
            new AdminColumnsCsvExportService($reads, new AdminColumnsCsvExportEncoder(), $maxRows),
            $view->id,
        ];
    }

    private function datasetQuery(int $totalRows): QueryReadConsumerInterface
    {
        return new class ($totalRows) implements QueryReadConsumerInterface {
            public function __construct(private int $totalRows)
            {
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
                    'predicates' => ['eq'],
                    'sort_modes' => ['field'],
                    'pagination_modes' => ['offset'],
                    'max_page_size' => 100,
                ];
            }

            public function read(array $request, ExecutionContext $context): array
            {
                $offset = $request['offset'];
                $count = min($request['page_size'], max(0, $this->totalRows - $offset));
                $rows = [];
                for ($index = 0; $index < $count; $index++) {
                    $id = $offset + $index + 1;
                    $rows[] = ['post.id' => $id, 'post.title' => 'Post ' . $id];
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
        };
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

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}
