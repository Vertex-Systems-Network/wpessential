<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\Query\AuthorizedQueryPlan;
use WPEssential\Modules\Query\QueryAuthorizedPlanner;
use WPEssential\Modules\Query\QueryDefinition;
use WPEssential\Modules\Query\QueryPagination;
use WPEssential\Modules\Query\QueryPlanningException;
use WPEssential\Modules\Query\QueryProviderCompilerInterface;
use WPEssential\Modules\Query\QueryProviderPlan;
use WPEssential\Modules\Query\QuerySourceReference;
use WPEssential\Modules\Query\WordPressPostsQueryCompiler;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DataSources\DataSourceAuthorizationMapping;
use WPEssential\Platform\DataSources\DataSourceAvailability;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class QueryAuthorizedPlannerTest extends TestCase
{
    public function testAuthorizedPlanRequiresCanonicalPolicyThenCompiles(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public int $calls = 0;
            public ?string $lastCapability = null;

            public function can(ExecutionContext $context, string $capability): bool
            {
                ++$this->calls;
                $this->lastCapability = $capability;

                return true;
            }
        };

        $planner = new QueryAuthorizedPlanner(
            $this->registry($this->mapping()),
            new PolicyEngine($checker),
            new WordPressPostsQueryCompiler(),
        );

        $authorized = $planner->plan($this->definition(), $this->context());

        self::assertInstanceOf(AuthorizedQueryPlan::class, $authorized);
        self::assertSame(1, $checker->calls);
        self::assertSame('read', $checker->lastCapability);
        self::assertSame('wpessential/query/execute', $authorized->ability);
        self::assertSame('read', $authorized->capability);
        self::assertSame('post', $authorized->resourceType);
        self::assertSame('capability_allowed', $authorized->policyReason);
        self::assertSame('wordpress.posts', $authorized->providerPlan->sourceRef);
        self::assertSame(WordPressPostsQueryCompiler::PROVIDER, $authorized->providerPlan->provider);
        self::assertSame('ids', $authorized->providerPlan->arguments['fields']);
    }

    public function testPolicyDenialStopsBeforeCompilerSupportOrCompilation(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return false;
            }
        };
        $compiler = new class implements QueryProviderCompilerInterface {
            public int $supportsCalls = 0;
            public int $compileCalls = 0;

            public function supports(QueryDefinition $definition): bool
            {
                ++$this->supportsCalls;
                return true;
            }

            public function compile(QueryDefinition $definition): QueryProviderPlan
            {
                ++$this->compileCalls;
                return new QueryProviderPlan('test.provider', $definition->source->sourceRef, [], $definition->projection);
            }
        };

        $planner = new QueryAuthorizedPlanner(
            $this->registry($this->mapping()),
            new PolicyEngine($checker),
            $compiler,
        );

        $this->expectPlanningError('wpe_query_policy_denied', function () use ($planner): void {
            $planner->plan($this->definition(), $this->context());
        });

        self::assertSame(0, $compiler->supportsCalls);
        self::assertSame(0, $compiler->compileCalls);
    }

    public function testUnauthenticatedPrincipalFailsBeforeCompiler(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public int $calls = 0;

            public function can(ExecutionContext $context, string $capability): bool
            {
                ++$this->calls;
                return true;
            }
        };
        $compiler = new class implements QueryProviderCompilerInterface {
            public int $compileCalls = 0;

            public function supports(QueryDefinition $definition): bool
            {
                return true;
            }

            public function compile(QueryDefinition $definition): QueryProviderPlan
            {
                ++$this->compileCalls;
                return new QueryProviderPlan('test.provider', $definition->source->sourceRef, [], $definition->projection);
            }
        };

        $planner = new QueryAuthorizedPlanner(
            $this->registry($this->mapping()),
            new PolicyEngine($checker),
            $compiler,
        );

        $this->expectPlanningError('wpe_query_policy_denied', function () use ($planner): void {
            $planner->plan(
                $this->definition(),
                new ExecutionContext(new Principal(null), 1),
            );
        });

        self::assertSame(0, $checker->calls);
        self::assertSame(0, $compiler->compileCalls);
    }

    public function testMissingAuthorizationMappingFailsClosedBeforePolicy(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public int $calls = 0;

            public function can(ExecutionContext $context, string $capability): bool
            {
                ++$this->calls;
                return true;
            }
        };

        $planner = new QueryAuthorizedPlanner(
            $this->registry(null),
            new PolicyEngine($checker),
            new WordPressPostsQueryCompiler(),
        );

        $this->expectPlanningError('wpe_query_dependency_unavailable', function () use ($planner): void {
            $planner->plan($this->definition(), $this->context());
        });

        self::assertSame(0, $checker->calls);
    }

    public function testUnknownMismatchedAndDegradedSourcesFailBeforePolicy(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public int $calls = 0;

            public function can(ExecutionContext $context, string $capability): bool
            {
                ++$this->calls;
                return true;
            }
        };
        $policy = new PolicyEngine($checker);

        $this->expectPlanningError('wpe_query_unknown_source', function () use ($policy): void {
            $planner = new QueryAuthorizedPlanner(new DataSourceRegistry(), $policy, new WordPressPostsQueryCompiler());
            $planner->plan($this->definition(), $this->context());
        });
        self::assertSame(0, $checker->calls);

        $mismatched = new DataSourceRegistry();
        $mismatched->register($this->descriptor($this->mapping(), capabilityVersion: 2));
        $this->expectPlanningError('wpe_query_dependency_unavailable', function () use ($mismatched, $policy): void {
            (new QueryAuthorizedPlanner($mismatched, $policy, new WordPressPostsQueryCompiler()))
                ->plan($this->definition(), $this->context());
        });
        self::assertSame(0, $checker->calls);

        $degraded = new DataSourceRegistry();
        $degraded->register($this->descriptor(
            $this->mapping(),
            availability: DataSourceAvailability::Degraded,
            degradedReason: 'maintenance',
        ));
        $this->expectPlanningError('wpe_query_dependency_unavailable', function () use ($degraded, $policy): void {
            (new QueryAuthorizedPlanner($degraded, $policy, new WordPressPostsQueryCompiler()))
                ->plan($this->definition(), $this->context());
        });
        self::assertSame(0, $checker->calls);
    }

    public function testUnsupportedCompilerIsCheckedOnlyAfterPolicyAllow(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public int $calls = 0;

            public function can(ExecutionContext $context, string $capability): bool
            {
                ++$this->calls;
                return true;
            }
        };
        $compiler = new class implements QueryProviderCompilerInterface {
            public int $supportsCalls = 0;
            public int $compileCalls = 0;

            public function supports(QueryDefinition $definition): bool
            {
                ++$this->supportsCalls;
                return false;
            }

            public function compile(QueryDefinition $definition): QueryProviderPlan
            {
                ++$this->compileCalls;
                return new QueryProviderPlan('never', $definition->source->sourceRef, [], $definition->projection);
            }
        };

        $planner = new QueryAuthorizedPlanner(
            $this->registry($this->mapping()),
            new PolicyEngine($checker),
            $compiler,
        );

        $this->expectPlanningError('wpe_query_dependency_unavailable', function () use ($planner): void {
            $planner->plan($this->definition(), $this->context());
        });

        self::assertSame(1, $checker->calls);
        self::assertSame(1, $compiler->supportsCalls);
        self::assertSame(0, $compiler->compileCalls);
    }

    public function testCompilerCannotReturnPlanForDifferentSource(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return true;
            }
        };
        $compiler = new class implements QueryProviderCompilerInterface {
            public function supports(QueryDefinition $definition): bool
            {
                return true;
            }

            public function compile(QueryDefinition $definition): QueryProviderPlan
            {
                return new QueryProviderPlan('broken.provider', 'wordpress.users', [], $definition->projection);
            }
        };

        $planner = new QueryAuthorizedPlanner(
            $this->registry($this->mapping()),
            new PolicyEngine($checker),
            $compiler,
        );

        $this->expectPlanningError('wpe_query_provider_failed', function () use ($planner): void {
            $planner->plan($this->definition(), $this->context());
        });
    }

    private function mapping(): DataSourceAuthorizationMapping
    {
        return new DataSourceAuthorizationMapping(
            ability: 'wpessential/query/execute',
            capability: 'read',
            resourceType: 'post',
        );
    }

    private function registry(?DataSourceAuthorizationMapping $authorization): DataSourceRegistry
    {
        $registry = new DataSourceRegistry();
        $registry->register($this->descriptor($authorization));

        return $registry;
    }

    private function descriptor(
        ?DataSourceAuthorizationMapping $authorization,
        int $capabilityVersion = 1,
        DataSourceAvailability $availability = DataSourceAvailability::Available,
        ?string $degradedReason = null,
    ): DataSourceDescriptor {
        return new DataSourceDescriptor(
            id: 'wordpress.posts',
            sourceType: 'wordpress.posts',
            capabilityVersion: $capabilityVersion,
            fieldSchema: [
                'post.id' => 'integer',
                'post.title' => 'string',
                'post.status' => 'string',
                'post.date' => 'datetime',
            ],
            predicates: ['eq', 'in', 'contains'],
            sortModes: ['field'],
            paginationModes: ['offset'],
            scopes: ['site'],
            maxPageSize: 100,
            maxBatchSize: 100,
            availability: $availability,
            degradedReason: $degradedReason,
            authorization: $authorization,
        );
    }

    private function definition(): QueryDefinition
    {
        return new QueryDefinition(
            identity: [
                'uuid' => '01990f6e-1f30-7000-8000-000000000003',
                'key' => 'posts.authorized-plan-test',
                'name' => 'Authorized plan test',
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

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }

    /** @param callable():void $callback */
    private function expectPlanningError(string $errorCode, callable $callback): void
    {
        try {
            $callback();
            self::fail('Expected QueryPlanningException.');
        } catch (QueryPlanningException $exception) {
            self::assertSame($errorCode, $exception->errorCode);
        }
    }
}
