<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetEmptyStateDescriptor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetQueryBindingDescriptor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetQueryBindingExecutor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceDescriptor;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DataSources\DataSourceAuthorizationMapping;
use WPEssential\Platform\DataSources\DataSourceAvailability;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class DashboardWidgetQueryBindingExecutorTest extends TestCase
{
    public function testResolvesColumnBindingsThroughCanonicalConsumerAndPreservesContext(): void
    {
        $registry = $this->registry();
        $consumer = new QueryBindingCapturingConsumer([
            'contract_version' => 1,
            'ok' => true,
            'source_ref' => 'wordpress.posts',
            'projection' => ['post.id', 'post.title'],
            'rows' => [
                ['post.id' => 10, 'post.title' => 'First'],
                ['post.id' => 11, 'post.title' => 'Second'],
            ],
            'returned' => 2,
            'error' => null,
        ]);
        $executor = new DashboardWidgetQueryBindingExecutor($registry, $consumer);
        $context = new ExecutionContext(new Principal(7), 3);

        $resolved = $executor->resolve($this->renderSource(), $context);

        self::assertSame($context, $consumer->seenContext);
        self::assertSame(
            ['contract_version', 'source_ref', 'projection', 'filters', 'order_by', 'page_size', 'offset'],
            array_keys($consumer->seenRequest ?? []),
        );
        self::assertSame(['post.id', 'post.title'], $consumer->seenRequest['projection'] ?? null);
        self::assertNull($resolved->query);
        self::assertSame(
            ['labels' => ['First', 'Second'], 'values' => [10, 11]],
            $resolved->toRenderInput()->bindings,
        );
    }

    public function testFailsClosedForUnknownDegradedOrUnauthorizedSource(): void
    {
        $consumer = new QueryBindingCapturingConsumer([]);

        foreach ([
            new DataSourceRegistry(),
            $this->registry(availability: DataSourceAvailability::Degraded),
            $this->registry(withAuthorization: false),
        ] as $registry) {
            try {
                (new DashboardWidgetQueryBindingExecutor($registry, $consumer))
                    ->resolve($this->renderSource(), new ExecutionContext(new Principal(7), 3));
                self::fail('Expected Data Source preflight to fail closed.');
            } catch (RuntimeException) {
                self::assertTrue(true);
            }
        }

        self::assertSame(0, $consumer->calls);
    }

    public function testRejectsDescriptorTypeMismatchAndPageLimitBeforeQueryRead(): void
    {
        $consumer = new QueryBindingCapturingConsumer([]);

        $badType = $this->registry(fieldSchema: [
            'post.id' => 'string',
            'post.title' => 'string',
            'post.status' => 'string',
            'post.date' => 'datetime',
        ]);
        try {
            (new DashboardWidgetQueryBindingExecutor($badType, $consumer))
                ->resolve($this->renderSource(), new ExecutionContext(new Principal(7), 3));
            self::fail('Expected logical-type mismatch to fail closed.');
        } catch (RuntimeException) {
            self::assertTrue(true);
        }

        $smallPage = $this->registry(maxPageSize: 1);
        try {
            (new DashboardWidgetQueryBindingExecutor($smallPage, $consumer))
                ->resolve($this->renderSource(pageSize: 2), new ExecutionContext(new Principal(7), 3));
            self::fail('Expected Data Source page-size bound to fail closed.');
        } catch (RuntimeException) {
            self::assertTrue(true);
        }

        self::assertSame(0, $consumer->calls);
    }

    public function testRejectsResultShapeMismatchAndExecutableQueryStrings(): void
    {
        $context = new ExecutionContext(new Principal(7), 3);
        $registry = $this->registry();

        foreach ([
            [
                'contract_version' => 1,
                'ok' => true,
                'source_ref' => 'wrong.source',
                'projection' => ['post.id', 'post.title'],
                'rows' => [['post.id' => 10, 'post.title' => 'Safe']],
                'returned' => 1,
            ],
            [
                'contract_version' => 1,
                'ok' => true,
                'source_ref' => 'wordpress.posts',
                'projection' => ['post.id', 'post.title'],
                'rows' => [['post.id' => 10, 'post.title' => '<script>alert(1)</script>']],
                'returned' => 1,
            ],
        ] as $result) {
            try {
                (new DashboardWidgetQueryBindingExecutor($registry, new QueryBindingCapturingConsumer($result)))
                    ->resolve($this->renderSource(), $context);
                self::fail('Expected invalid Query result to fail closed.');
            } catch (RuntimeException) {
                self::assertTrue(true);
            }
        }
    }

    public function testValidZeroRowsResolveAuthoredEmptyStateAndMissingStateFailsClosed(): void
    {
        $zeroRows = [
            'contract_version' => 1,
            'ok' => true,
            'source_ref' => 'wordpress.posts',
            'projection' => ['post.id', 'post.title'],
            'rows' => [],
            'returned' => 0,
            'error' => null,
        ];
        $context = new ExecutionContext(new Principal(7), 3);
        $emptyState = new DashboardWidgetEmptyStateDescriptor(
            blueprintId: '31000000-0000-4000-8000-000000000005',
            blueprintRevision: 1,
            bindings: ['text' => 'Nothing to display yet.', 'title' => 'No results'],
        );

        $consumer = new QueryBindingCapturingConsumer($zeroRows);
        $resolved = (new DashboardWidgetQueryBindingExecutor($this->registry(), $consumer))
            ->resolve($this->renderSource(emptyState: $emptyState), $context);

        self::assertSame($context, $consumer->seenContext);
        self::assertNull($resolved->query);
        self::assertNull($resolved->emptyState);
        self::assertSame($emptyState->blueprintId, $resolved->toRenderInput()->blueprintId);
        self::assertSame($emptyState->bindings, $resolved->toRenderInput()->bindings);

        $this->expectException(RuntimeException::class);
        (new DashboardWidgetQueryBindingExecutor(
            $this->registry(),
            new QueryBindingCapturingConsumer($zeroRows),
        ))->resolve($this->renderSource(), $context);
    }

    private function renderSource(
        int $pageSize = 2,
        ?DashboardWidgetEmptyStateDescriptor $emptyState = null,
    ): DashboardWidgetRenderSourceDescriptor
    {
        return new DashboardWidgetRenderSourceDescriptor(
            definitionId: '77777777-7777-4777-8777-777777777777',
            definitionRevision: 1,
            blueprintId: '31000000-0000-4000-8000-000000000003',
            blueprintRevision: 1,
            bindings: [],
            query: new DashboardWidgetQueryBindingDescriptor(
                sourceRef: 'wordpress.posts',
                projection: ['post.id', 'post.title'],
                filters: [['field_ref' => 'post.status', 'operator' => 'eq', 'value' => 'publish']],
                orderBy: [['field_ref' => 'post.date', 'direction' => 'desc']],
                pageSize: $pageSize,
                offset: 0,
                bindings: [
                    'labels' => ['field_ref' => 'post.title', 'mode' => 'column', 'binding_type' => 'string_list'],
                    'values' => ['field_ref' => 'post.id', 'mode' => 'column', 'binding_type' => 'int_list'],
                ],
            ),
            emptyState: $emptyState,
        );
    }

    /**
     * @param array<string,string>|null $fieldSchema
     */
    private function registry(
        ?array $fieldSchema = null,
        DataSourceAvailability $availability = DataSourceAvailability::Available,
        bool $withAuthorization = true,
        int $maxPageSize = 50,
    ): DataSourceRegistry {
        $registry = new DataSourceRegistry();
        $registry->register(new DataSourceDescriptor(
            id: 'wordpress.posts',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: $fieldSchema ?? [
                'post.id' => 'integer',
                'post.title' => 'string',
                'post.status' => 'string',
                'post.date' => 'datetime',
            ],
            predicates: ['eq', 'neq', 'in', 'not_in'],
            sortModes: ['field'],
            paginationModes: ['offset'],
            maxPageSize: $maxPageSize,
            availability: $availability,
            degradedReason: $availability === DataSourceAvailability::Degraded ? 'test degraded source' : null,
            authorization: $withAuthorization
                ? new DataSourceAuthorizationMapping('wpessential/query/execute', 'read', 'post')
                : null,
        ));

        return $registry;
    }
}

final class QueryBindingCapturingConsumer implements QueryReadConsumerInterface
{
    public int $calls = 0;
    public ?ExecutionContext $seenContext = null;

    /** @var array<string,mixed>|null */
    public ?array $seenRequest = null;

    /** @param array<string,mixed> $result */
    public function __construct(private array $result) {}

    public function describe(string $sourceRef, ExecutionContext $context): array
    {
        return [];
    }

    public function read(array $request, ExecutionContext $context): array
    {
        ++$this->calls;
        $this->seenRequest = $request;
        $this->seenContext = $context;

        return $this->result;
    }
}
