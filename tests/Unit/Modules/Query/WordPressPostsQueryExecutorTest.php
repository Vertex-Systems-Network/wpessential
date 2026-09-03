<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\Query\QueryAuthorizedExecutor;
use WPEssential\Modules\Query\QueryAuthorizedPlanner;
use WPEssential\Modules\Query\QueryDefinition;
use WPEssential\Modules\Query\QueryExecutionError;
use WPEssential\Modules\Query\QueryExecutionResult;
use WPEssential\Modules\Query\QueryPagination;
use WPEssential\Modules\Query\QueryProviderPlan;
use WPEssential\Modules\Query\QuerySourceReference;
use WPEssential\Modules\Query\WordPressPostsQueryCompiler;
use WPEssential\Modules\Query\WordPressPostsQueryExecutor;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DataSources\DataSourceAuthorizationMapping;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class WordPressPostsQueryExecutorTest extends TestCase
{
    public function testAuthorizedExecutionUsesBoundedNativePlanAndNormalizesIdRows(): void
    {
        $calls = 0;
        $lastArguments = null;
        $provider = new WordPressPostsQueryExecutor(
            static function (array $arguments) use (&$calls, &$lastArguments): object {
                ++$calls;
                $lastArguments = $arguments;

                return (object) [
                    'posts' => [41, 42],
                    'found_posts' => 999999,
                    'post_count' => 2,
                ];
            },
        );
        $executor = new QueryAuthorizedExecutor($this->planner(true), $provider);

        $result = $executor->execute($this->definition(), $this->context());

        self::assertInstanceOf(QueryExecutionResult::class, $result);
        self::assertSame(1, $calls);
        self::assertIsArray($lastArguments);
        self::assertSame(true, $lastArguments['ignore_sticky_posts']);
        self::assertSame(true, $lastArguments['suppress_filters']);
        self::assertSame(20, $lastArguments['posts_per_page']);
        self::assertSame(0, $lastArguments['offset']);
        self::assertSame('ids', $lastArguments['fields']);
        self::assertSame(WordPressPostsQueryCompiler::PROVIDER, $result->provider);
        self::assertSame('wordpress.posts', $result->sourceRef);
        self::assertSame(['post.id'], $result->projection);
        self::assertSame([['post.id' => 41], ['post.id' => 42]], $result->rows);
        self::assertFalse(property_exists($result, 'total'));
        self::assertSame(2, $result->returned);
    }

    public function testPolicyDenialReturnsNormalizedErrorBeforeNativeExecution(): void
    {
        $calls = 0;
        $provider = new WordPressPostsQueryExecutor(
            static function (array $arguments) use (&$calls): object {
                ++$calls;
                return (object) ['posts' => []];
            },
        );
        $executor = new QueryAuthorizedExecutor($this->planner(false), $provider);

        $result = $executor->execute($this->definition(), $this->context());

        self::assertInstanceOf(QueryExecutionError::class, $result);
        self::assertSame('wpe_query_policy_denied', $result->errorCode);
        self::assertSame('$.source', $result->path);
        self::assertSame(0, $calls);
    }

    public function testForgedUnsafeProviderArgumentFailsClosedBeforeNativeExecution(): void
    {
        $calls = 0;
        $provider = new WordPressPostsQueryExecutor(
            static function (array $arguments) use (&$calls): object {
                ++$calls;
                return (object) ['posts' => []];
            },
        );
        $plan = new QueryProviderPlan(
            provider: WordPressPostsQueryCompiler::PROVIDER,
            sourceRef: WordPressPostsQueryCompiler::SOURCE_REF,
            arguments: [
                'ignore_sticky_posts' => true,
                'suppress_filters' => true,
                'posts_per_page' => 20,
                'offset' => 0,
                'fields' => 'ids',
                'meta_query' => [['key' => 'unsafe']],
            ],
            projection: ['post.id'],
        );

        $result = $provider->execute($plan);

        self::assertInstanceOf(QueryExecutionError::class, $result);
        self::assertSame('wpe_query_provider_failed', $result->errorCode);
        self::assertSame('$.execution.arguments', $result->path);
        self::assertSame(0, $calls);
    }

    public function testPageSizeAboveCertifiedNativeBoundFailsClosed(): void
    {
        $calls = 0;
        $provider = new WordPressPostsQueryExecutor(
            static function (array $arguments) use (&$calls): object {
                ++$calls;
                return (object) ['posts' => []];
            },
        );
        $plan = new QueryProviderPlan(
            provider: WordPressPostsQueryCompiler::PROVIDER,
            sourceRef: WordPressPostsQueryCompiler::SOURCE_REF,
            arguments: [
                'ignore_sticky_posts' => true,
                'suppress_filters' => true,
                'posts_per_page' => 101,
                'offset' => 0,
                'fields' => 'ids',
            ],
            projection: ['post.id'],
        );

        $result = $provider->execute($plan);

        self::assertInstanceOf(QueryExecutionError::class, $result);
        self::assertSame('wpe_query_provider_failed', $result->errorCode);
        self::assertSame('$.execution.arguments.posts_per_page', $result->path);
        self::assertSame(0, $calls);
    }

    public function testProjectedPostObjectIsNormalizedWithoutApplyingProviderFilters(): void
    {
        $provider = new WordPressPostsQueryExecutor(
            static fn (array $arguments): object => (object) [
                'posts' => [(object) [
                    'ID' => 9,
                    'post_title' => 'Hello',
                    'post_author' => '7',
                    'post_parent' => 0,
                ]],
                'post_count' => 1,
            ],
        );
        $plan = new QueryProviderPlan(
            provider: WordPressPostsQueryCompiler::PROVIDER,
            sourceRef: WordPressPostsQueryCompiler::SOURCE_REF,
            arguments: [
                'ignore_sticky_posts' => true,
                'suppress_filters' => true,
                'posts_per_page' => 10,
                'offset' => 0,
            ],
            projection: ['post.id', 'post.title', 'post.author_id', 'post.parent_id'],
        );

        $result = $provider->execute($plan);

        self::assertInstanceOf(QueryExecutionResult::class, $result);
        self::assertSame([[
            'post.id' => 9,
            'post.title' => 'Hello',
            'post.author_id' => 7,
            'post.parent_id' => 0,
        ]], $result->rows);
    }

    public function testMalformedNativeResultIsNormalizedToProviderFailure(): void
    {
        $provider = new WordPressPostsQueryExecutor(
            static fn (array $arguments): object => (object) [
                'posts' => ['unexpected' => 41],
            ],
        );

        $result = $provider->execute($this->idPlan());

        self::assertInstanceOf(QueryExecutionError::class, $result);
        self::assertSame('wpe_query_provider_failed', $result->errorCode);
        self::assertSame('$.execution', $result->path);
    }

    public function testNativeThrowableIsHiddenBehindStableProviderFailure(): void
    {
        $provider = new WordPressPostsQueryExecutor(
            static function (array $arguments): object {
                throw new RuntimeException('database details must not leak');
            },
        );

        $result = $provider->execute($this->idPlan());

        self::assertInstanceOf(QueryExecutionError::class, $result);
        self::assertSame('wpe_query_provider_failed', $result->errorCode);
        self::assertStringNotContainsString('database details', $result->message);
    }

    private function planner(bool $allowed): QueryAuthorizedPlanner
    {
        $registry = new DataSourceRegistry();
        $registry->register(new DataSourceDescriptor(
            id: WordPressPostsQueryCompiler::SOURCE_REF,
            sourceType: WordPressPostsQueryCompiler::SOURCE_REF,
            capabilityVersion: 1,
            fieldSchema: ['post.id' => 'integer'],
            predicates: ['eq', 'in', 'contains'],
            sortModes: ['field'],
            paginationModes: ['offset'],
            scopes: ['site'],
            maxPageSize: 100,
            maxBatchSize: 100,
            authorization: new DataSourceAuthorizationMapping(
                ability: 'wpessential/query/execute',
                capability: 'read',
                resourceType: 'post',
            ),
        ));
        $checker = new class($allowed) implements CapabilityCheckerInterface {
            public function __construct(private readonly bool $allowed)
            {
            }

            public function can(ExecutionContext $context, string $capability): bool
            {
                return $this->allowed;
            }
        };

        return new QueryAuthorizedPlanner(
            $registry,
            new PolicyEngine($checker),
            new WordPressPostsQueryCompiler(),
        );
    }

    private function definition(): QueryDefinition
    {
        return new QueryDefinition(
            identity: [
                'uuid' => '01990f6e-1f30-7000-8000-000000000008',
                'key' => 'posts.native-execution-test',
                'name' => 'Native execution test',
                'revision' => 1,
                'lifecycle' => 'draft',
            ],
            astVersion: 1,
            source: new QuerySourceReference('wordpress.posts', 'wordpress.posts', 1),
            operation: 'select',
            projection: ['post.id'],
            parameters: [],
            filter: null,
            orderBy: [],
            pagination: new QueryPagination('offset', 20, 0),
            distinct: false,
            executionPolicy: [],
            cachePolicy: [],
        );
    }

    private function idPlan(): QueryProviderPlan
    {
        return new QueryProviderPlan(
            provider: WordPressPostsQueryCompiler::PROVIDER,
            sourceRef: WordPressPostsQueryCompiler::SOURCE_REF,
            arguments: [
                'ignore_sticky_posts' => true,
                'suppress_filters' => true,
                'posts_per_page' => 20,
                'offset' => 0,
                'fields' => 'ids',
            ],
            projection: ['post.id'],
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}
