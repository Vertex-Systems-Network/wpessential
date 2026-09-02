<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Query\QueryDefinition;
use WPEssential\Modules\Query\QueryOrderClause;
use WPEssential\Modules\Query\QueryPagination;
use WPEssential\Modules\Query\QueryPredicate;
use WPEssential\Modules\Query\QueryPredicateType;
use WPEssential\Modules\Query\QueryProviderCompilationException;
use WPEssential\Modules\Query\QuerySourceReference;
use WPEssential\Modules\Query\WordPressPostsQueryCompiler;

final class WordPressPostsQueryCompilerTest extends TestCase
{
    public function testCompilesBoundedComparisonOrderingPaginationAndProjection(): void
    {
        $definition = $this->definition(
            filter: new QueryPredicate(
                QueryPredicateType::Comparison,
                ['field_ref' => 'post.status', 'operator' => 'eq', 'value' => 'publish'],
            ),
            projection: ['post.id', 'post.title'],
            orderBy: [new QueryOrderClause('post.date', 'desc')],
        );

        $plan = (new WordPressPostsQueryCompiler())->compile($definition);

        self::assertSame(WordPressPostsQueryCompiler::PROVIDER, $plan->provider);
        self::assertSame('wordpress.posts', $plan->sourceRef);
        self::assertSame(['post.id', 'post.title'], $plan->projection);
        self::assertSame('publish', $plan->arguments['post_status']);
        self::assertSame(['date' => 'DESC'], $plan->arguments['orderby']);
        self::assertSame(20, $plan->arguments['posts_per_page']);
        self::assertSame(0, $plan->arguments['offset']);
        self::assertTrue($plan->arguments['ignore_sticky_posts']);
        self::assertTrue($plan->arguments['suppress_filters']);
        self::assertArrayNotHasKey('fields', $plan->arguments);
    }

    public function testIdOnlyProjectionUsesPublicWpQueryIdsMode(): void
    {
        $plan = (new WordPressPostsQueryCompiler())->compile(
            $this->definition(projection: ['post.id']),
        );

        self::assertSame('ids', $plan->arguments['fields']);
    }

    public function testCompilesSupportedAndGroupWithoutApproximatingOr(): void
    {
        $filter = new QueryPredicate(
            QueryPredicateType::Group,
            ['boolean' => 'and'],
            [
                new QueryPredicate(
                    QueryPredicateType::Comparison,
                    ['field_ref' => 'post.type', 'operator' => 'eq', 'value' => 'post'],
                ),
                new QueryPredicate(
                    QueryPredicateType::SetMembership,
                    ['field_ref' => 'post.author_id', 'operator' => 'in', 'values' => [3, 2, 3]],
                ),
            ],
        );

        $plan = (new WordPressPostsQueryCompiler())->compile($this->definition(filter: $filter));

        self::assertSame('post', $plan->arguments['post_type']);
        self::assertSame([3, 2], $plan->arguments['author__in']);
    }

    public function testCompilesOnlyProviderWideDefaultTextSearch(): void
    {
        $filter = new QueryPredicate(
            QueryPredicateType::Text,
            [
                'field_ref' => null,
                'search_scope' => 'posts.default',
                'mode' => 'contains',
                'value' => 'needle',
            ],
        );

        $plan = (new WordPressPostsQueryCompiler())->compile($this->definition(filter: $filter));

        self::assertSame('needle', $plan->arguments['s']);
    }

    public function testRejectsFieldScopedSearchBecauseWpQueryWouldApproximateIt(): void
    {
        $filter = new QueryPredicate(
            QueryPredicateType::Text,
            [
                'field_ref' => 'post.title',
                'search_scope' => null,
                'mode' => 'contains',
                'value' => 'needle',
            ],
        );

        $this->expectCompilationError('wpe_query_unsupported_operator', function () use ($filter): void {
            (new WordPressPostsQueryCompiler())->compile($this->definition(filter: $filter));
        });
    }

    public function testRejectsCrossClauseOrWithoutSqlFilters(): void
    {
        $filter = new QueryPredicate(
            QueryPredicateType::Group,
            ['boolean' => 'or'],
            [
                new QueryPredicate(
                    QueryPredicateType::Comparison,
                    ['field_ref' => 'post.status', 'operator' => 'eq', 'value' => 'publish'],
                ),
                new QueryPredicate(
                    QueryPredicateType::Comparison,
                    ['field_ref' => 'post.type', 'operator' => 'eq', 'value' => 'page'],
                ),
            ],
        );

        $this->expectCompilationError('wpe_query_unsupported_operator', function () use ($filter): void {
            (new WordPressPostsQueryCompiler())->compile($this->definition(filter: $filter));
        });
    }

    public function testRejectsRelationTaxonomyFieldAndProviderExtensionExecution(): void
    {
        $unsupported = [
            QueryPredicateType::Relation,
            QueryPredicateType::Taxonomy,
            QueryPredicateType::Field,
            QueryPredicateType::ProviderExtension,
        ];

        foreach ($unsupported as $type) {
            $this->expectCompilationError('wpe_query_unsupported_operator', function () use ($type): void {
                (new WordPressPostsQueryCompiler())->compile(
                    $this->definition(filter: new QueryPredicate($type)),
                );
            });
        }
    }

    public function testRejectsUnsupportedComparisonInsteadOfFallingBackToMetaOrSql(): void
    {
        $filter = new QueryPredicate(
            QueryPredicateType::Comparison,
            ['field_ref' => 'post.title', 'operator' => 'neq', 'value' => 'secret'],
        );

        $this->expectCompilationError('wpe_query_unsupported_operator', function () use ($filter): void {
            (new WordPressPostsQueryCompiler())->compile($this->definition(filter: $filter));
        });
    }

    public function testRejectsMalformedLiteralTypesEvenIfDefinitionWasBuiltManually(): void
    {
        $filter = new QueryPredicate(
            QueryPredicateType::Comparison,
            ['field_ref' => 'post.id', 'operator' => 'eq', 'value' => '1 OR 1=1'],
        );

        $this->expectCompilationError('wpe_query_type_mismatch', function () use ($filter): void {
            (new WordPressPostsQueryCompiler())->compile($this->definition(filter: $filter));
        });
    }

    public function testRejectsCompetingArgumentsRatherThanSilentlyDroppingClause(): void
    {
        $filter = new QueryPredicate(
            QueryPredicateType::Group,
            ['boolean' => 'and'],
            [
                new QueryPredicate(
                    QueryPredicateType::Comparison,
                    ['field_ref' => 'post.status', 'operator' => 'eq', 'value' => 'publish'],
                ),
                new QueryPredicate(
                    QueryPredicateType::Comparison,
                    ['field_ref' => 'post.status', 'operator' => 'eq', 'value' => 'draft'],
                ),
            ],
        );

        $this->expectCompilationError('wpe_query_unsupported_operator', function () use ($filter): void {
            (new WordPressPostsQueryCompiler())->compile($this->definition(filter: $filter));
        });
    }

    public function testRejectsCursorParameterBindingDistinctAndWrongSourceAsLaterTranches(): void
    {
        $compiler = new WordPressPostsQueryCompiler();

        $this->expectCompilationError('wpe_query_unsupported_operator', function () use ($compiler): void {
            $compiler->compile($this->definition(pagination: new QueryPagination('cursor', 20, 0, 'opaque')));
        });

        $this->expectCompilationError('wpe_query_unsupported_operator', function () use ($compiler): void {
            $compiler->compile($this->definition(parameters: ['status' => ['type' => 'string']]));
        });

        $this->expectCompilationError('wpe_query_unsupported_operator', function () use ($compiler): void {
            $compiler->compile($this->definition(distinct: true));
        });

        $this->expectCompilationError('wpe_query_unsupported_operator', function () use ($compiler): void {
            $compiler->compile($this->definition(sourceRef: 'wordpress.users'));
        });
    }

    /**
     * @param list<string> $projection
     * @param array<string,array<string,mixed>> $parameters
     * @param list<QueryOrderClause> $orderBy
     */
    private function definition(
        ?QueryPredicate $filter = null,
        array $projection = ['post.id', 'post.title'],
        array $parameters = [],
        array $orderBy = [],
        ?QueryPagination $pagination = null,
        bool $distinct = false,
        string $sourceRef = 'wordpress.posts',
    ): QueryDefinition {
        return new QueryDefinition(
            identity: [
                'uuid' => '01990f6e-1f30-7000-8000-000000000002',
                'key' => 'posts.compiler-test',
                'name' => 'Compiler test',
                'revision' => 1,
                'lifecycle' => 'draft',
            ],
            astVersion: 1,
            source: new QuerySourceReference($sourceRef, $sourceRef, 1),
            operation: 'select',
            projection: $projection,
            parameters: $parameters,
            filter: $filter,
            orderBy: $orderBy,
            pagination: $pagination ?? new QueryPagination('offset', 20, 0),
            distinct: $distinct,
            executionPolicy: [],
            cachePolicy: [],
        );
    }

    /** @param callable():void $callback */
    private function expectCompilationError(string $errorCode, callable $callback): void
    {
        try {
            $callback();
            self::fail('Expected QueryProviderCompilationException.');
        } catch (QueryProviderCompilationException $exception) {
            self::assertSame($errorCode, $exception->errorCode);
            self::assertNotSame('', $exception->path);
        }
    }
}
