<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Query\QueryAstValidator;
use WPEssential\Modules\Query\QueryAuthorizedExecutor;
use WPEssential\Modules\Query\QueryAuthorizedPlanner;
use WPEssential\Modules\Query\QueryExecutionError;
use WPEssential\Modules\Query\QueryExecutionResult;
use WPEssential\Modules\Query\QueryModule;
use WPEssential\Modules\Query\QueryProviderExecutorInterface;
use WPEssential\Modules\Query\QueryProviderPlan;
use WPEssential\Modules\Query\QueryReadConsumer;
use WPEssential\Modules\Query\WordPressPostsQueryCompiler;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DataSources\DataSourceAuthorizationMapping;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class QueryReadConsumerTest extends TestCase
{
    public function testBoundedReadUsesCanonicalValidationPolicyPlanningAndExecutor(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            /** @var list<string> */
            public array $checked = [];

            public function can(ExecutionContext $context, string $capability): bool
            {
                $this->checked[] = $capability;
                return true;
            }
        };
        $provider = new class implements QueryProviderExecutorInterface {
            public ?QueryProviderPlan $lastPlan = null;

            public function supports(QueryProviderPlan $plan): bool
            {
                return $plan->provider === WordPressPostsQueryCompiler::PROVIDER;
            }

            public function execute(QueryProviderPlan $plan): QueryExecutionResult|QueryExecutionError
            {
                $this->lastPlan = $plan;

                return new QueryExecutionResult(
                    provider: $plan->provider,
                    sourceRef: $plan->sourceRef,
                    projection: $plan->projection,
                    rows: [[
                        'post.id' => 17,
                        'post.title' => 'Alpha',
                    ]],
                    returned: 1,
                );
            }
        };

        $consumer = $this->consumer($checker, $provider);
        $result = $consumer->read([
            'contract_version' => QueryReadConsumerInterface::CONTRACT_VERSION,
            'source_ref' => 'wordpress.posts',
            'projection' => ['post.id', 'post.title'],
            'filters' => [[
                'field_ref' => 'post.status',
                'operator' => 'eq',
                'value' => 'publish',
            ]],
            'search' => 'Alpha',
            'order_by' => [[
                'field_ref' => 'post.title',
                'direction' => 'asc',
            ]],
            'page_size' => 25,
            'offset' => 0,
        ], $this->context());

        self::assertTrue($result['ok']);
        self::assertSame(1, $result['contract_version']);
        self::assertSame('wordpress.posts', $result['source_ref']);
        self::assertSame(['post.id', 'post.title'], $result['projection']);
        self::assertSame([['post.id' => 17, 'post.title' => 'Alpha']], $result['rows']);
        self::assertSame(1, $result['returned']);
        self::assertNull($result['error']);
        self::assertSame(['read'], $checker->checked);

        self::assertInstanceOf(QueryProviderPlan::class, $provider->lastPlan);
        self::assertSame('publish', $provider->lastPlan->arguments['post_status']);
        self::assertSame('Alpha', $provider->lastPlan->arguments['s']);
        self::assertSame('title', $provider->lastPlan->arguments['orderby']);
        self::assertSame('ASC', $provider->lastPlan->arguments['order']);
        self::assertSame(25, $provider->lastPlan->arguments['posts_per_page']);
        self::assertArrayNotHasKey('arguments', $result);
    }

    public function testUnknownRequestPropertyAndOversizedOffsetFailBeforePolicyOrProvider(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public int $calls = 0;

            public function can(ExecutionContext $context, string $capability): bool
            {
                ++$this->calls;
                return true;
            }
        };
        $provider = new class implements QueryProviderExecutorInterface {
            public int $calls = 0;

            public function supports(QueryProviderPlan $plan): bool
            {
                return true;
            }

            public function execute(QueryProviderPlan $plan): QueryExecutionResult|QueryExecutionError
            {
                ++$this->calls;
                return new QueryExecutionError('never', '$', 'never');
            }
        };
        $consumer = $this->consumer($checker, $provider);

        $rawProvider = $consumer->read([
            'contract_version' => 1,
            'source_ref' => 'wordpress.posts',
            'projection' => ['post.id'],
            'wp_query' => ['post_type' => 'post'],
        ], $this->context());
        self::assertFalse($rawProvider['ok']);
        self::assertSame('wpe_query_invalid_consumer_request', $rawProvider['error']['code']);

        $oversizedOffset = $consumer->read([
            'contract_version' => 1,
            'source_ref' => 'wordpress.posts',
            'projection' => ['post.id'],
            'offset' => QueryReadConsumerInterface::MAX_OFFSET + 1,
        ], $this->context());
        self::assertFalse($oversizedOffset['ok']);
        self::assertSame('wpe_query_invalid_consumer_request', $oversizedOffset['error']['code']);
        self::assertSame(0, $checker->calls);
        self::assertSame(0, $provider->calls);
    }

    public function testUnknownProjectionFailsCanonicalDataSourceValidationBeforePolicy(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public int $calls = 0;

            public function can(ExecutionContext $context, string $capability): bool
            {
                ++$this->calls;
                return true;
            }
        };
        $provider = new class implements QueryProviderExecutorInterface {
            public int $calls = 0;

            public function supports(QueryProviderPlan $plan): bool
            {
                return true;
            }

            public function execute(QueryProviderPlan $plan): QueryExecutionResult|QueryExecutionError
            {
                ++$this->calls;
                return new QueryExecutionError('never', '$', 'never');
            }
        };

        $result = $this->consumer($checker, $provider)->read([
            'contract_version' => 1,
            'source_ref' => 'wordpress.posts',
            'projection' => ['private.table.column'],
        ], $this->context());

        self::assertFalse($result['ok']);
        self::assertSame('wpe_query_invalid_ast', $result['error']['code']);
        self::assertSame(0, $checker->calls);
        self::assertSame(0, $provider->calls);
    }

    public function testPolicyDenialIsReturnedAsNormalizedFailureWithoutProviderExecution(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return false;
            }
        };
        $provider = new class implements QueryProviderExecutorInterface {
            public int $calls = 0;

            public function supports(QueryProviderPlan $plan): bool
            {
                return true;
            }

            public function execute(QueryProviderPlan $plan): QueryExecutionResult|QueryExecutionError
            {
                ++$this->calls;
                return new QueryExecutionError('never', '$', 'never');
            }
        };

        $result = $this->consumer($checker, $provider)->read([
            'contract_version' => 1,
            'source_ref' => 'wordpress.posts',
            'projection' => ['post.id'],
        ], $this->context());

        self::assertFalse($result['ok']);
        self::assertSame('wpe_query_policy_denied', $result['error']['code']);
        self::assertSame(0, $provider->calls);
    }

    public function testModuleRegistersPublicConsumerWithoutCreatingPublicEndpoint(): void
    {
        $services = new ServiceRegistry();
        $services->set('platform.data-sources', new DataSourceRegistry());
        $services->set('platform.abilities.policy', new PolicyEngine(
            new class implements CapabilityCheckerInterface {
                public function can(ExecutionContext $context, string $capability): bool
                {
                    return true;
                }
            },
        ));

        (new QueryModule())->register($services);

        $consumer = $services->get(QueryModule::SERVICE_READ_CONSUMER);
        self::assertInstanceOf(QueryReadConsumerInterface::class, $consumer);
        self::assertInstanceOf(QueryReadConsumer::class, $consumer);

        $description = $consumer->describe('wordpress.posts', $this->context());
        self::assertSame(1, $description['contract_version']);
        self::assertSame('wordpress.posts', $description['source_ref']);
        self::assertTrue($description['available']);
        self::assertSame(100, $description['max_page_size']);
        self::assertArrayHasKey('post.id', $description['field_schema']);
        self::assertFalse($services->has('module.query.rest'));
        self::assertFalse($services->has('module.query.ajax'));
    }

    private function consumer(
        CapabilityCheckerInterface $checker,
        QueryProviderExecutorInterface $provider,
    ): QueryReadConsumer {
        $dataSources = new DataSourceRegistry();
        $dataSources->register(new DataSourceDescriptor(
            id: 'wordpress.posts',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: [
                'post.id' => 'integer',
                'post.title' => 'string',
                'post.status' => 'string',
                'post.type' => 'string',
                'post.author_id' => 'integer',
                'post.parent_id' => 'integer',
            ],
            predicates: ['eq', 'neq', 'in', 'not_in', 'contains'],
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

        $validator = new QueryAstValidator($dataSources);
        $planner = new QueryAuthorizedPlanner(
            $dataSources,
            new PolicyEngine($checker),
            new WordPressPostsQueryCompiler(),
        );
        $executor = new QueryAuthorizedExecutor($planner, $provider);

        return new QueryReadConsumer($dataSources, $validator, $executor);
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}
