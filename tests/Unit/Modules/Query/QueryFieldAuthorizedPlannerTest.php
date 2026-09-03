<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\FieldQueryConsumerInterface;
use WPEssential\Modules\Query\QueryAuthorizedExecutor;
use WPEssential\Modules\Query\QueryAuthorizedPlanner;
use WPEssential\Modules\Query\QueryDefinition;
use WPEssential\Modules\Query\QueryExecutionError;
use WPEssential\Modules\Query\QueryExecutionResult;
use WPEssential\Modules\Query\QueryFieldPredicateResolver;
use WPEssential\Modules\Query\QueryPagination;
use WPEssential\Modules\Query\QueryPredicate;
use WPEssential\Modules\Query\QueryPredicateType;
use WPEssential\Modules\Query\QueryProviderExecutorInterface;
use WPEssential\Modules\Query\QueryProviderPlan;
use WPEssential\Modules\Query\QuerySourceReference;
use WPEssential\Modules\Query\WordPressPostsQueryCompiler;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DataSources\DataSourceAuthorizationMapping;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class QueryFieldAuthorizedPlannerTest extends TestCase
{
    public function testPolicyRunsBeforeFieldsOwnerAndProducesNarrowPlan(): void
    {
        $trace = new ArrayObject();
        $plan = $this->planner(true, $trace, [13])->plan($this->definition(), $this->context());

        self::assertSame(['policy', 'describe', 'matching'], $trace->getArrayCopy());
        self::assertSame([13], $plan->providerPlan->arguments['post__in']);
        self::assertNull($plan->shortCircuitResult);
    }

    public function testPolicyDenialStopsBeforeFieldsOwner(): void
    {
        $trace = new ArrayObject();
        $result = (new QueryAuthorizedExecutor(
            $this->planner(false, $trace, [13]),
            $this->provider($trace),
        ))->execute($this->definition(), $this->context());

        self::assertInstanceOf(QueryExecutionError::class, $result);
        self::assertSame('wpe_query_policy_denied', $result->errorCode);
        self::assertSame(['policy'], $trace->getArrayCopy());
    }

    public function testEmptyFieldsMatchShortCircuitsBeforeProvider(): void
    {
        $trace = new ArrayObject();
        $result = (new QueryAuthorizedExecutor(
            $this->planner(true, $trace, []),
            $this->provider($trace),
        ))->execute($this->definition(), $this->context());

        self::assertInstanceOf(QueryExecutionResult::class, $result);
        self::assertSame([], $result->rows);
        self::assertSame(0, $result->returned);
        self::assertNotContains('provider', $trace->getArrayCopy());
    }

    /** @param list<int> $matches */
    private function planner(bool $allowed, ArrayObject $trace, array $matches): QueryAuthorizedPlanner
    {
        $registry = new DataSourceRegistry();
        $registry->register(new DataSourceDescriptor(
            id: WordPressPostsQueryCompiler::SOURCE_REF,
            sourceType: WordPressPostsQueryCompiler::SOURCE_REF,
            capabilityVersion: 1,
            fieldSchema: ['post.id' => 'integer'],
            predicates: ['eq', 'in'],
            sortModes: ['field'],
            paginationModes: ['offset'],
            maxPageSize: 100,
            maxBatchSize: 100,
            authorization: new DataSourceAuthorizationMapping(
                ability: 'wpessential/query/execute',
                capability: 'read',
                resourceType: 'post',
            ),
        ));

        $checker = new class($allowed, $trace) implements CapabilityCheckerInterface {
            public function __construct(private readonly bool $allowed, private readonly ArrayObject $trace)
            {
            }
            public function can(ExecutionContext $context, string $capability): bool
            {
                $this->trace[] = 'policy';
                return $this->allowed;
            }
        };

        $fields = new class($trace, $matches, $this->fieldRef()) implements FieldQueryConsumerInterface {
            /** @param list<int> $matches */
            public function __construct(
                private readonly ArrayObject $trace,
                private readonly array $matches,
                private readonly string $fieldRef,
            ) {
            }
            public function describe(string $fieldReference, ExecutionContext $context): array
            {
                $this->trace[] = 'describe';
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'field_ref' => $this->fieldRef,
                    'group_revision' => 1,
                    'field_uuid' => '01990f6e-1f30-4000-8000-000000000202',
                    'logical_type' => 'string',
                    'operators' => ['eq', 'neq', 'in', 'not_in'],
                    'max_candidate_ids' => self::MAX_CANDIDATE_IDS,
                    'max_result_ids' => self::MAX_RESULT_IDS,
                    'storage_owner' => 'native_post_meta',
                ];
            }
            public function matchingPostIds(string $fieldReference, string $operator, mixed $value, array $candidatePostIds, int $limit, ExecutionContext $context): array
            {
                $this->trace[] = 'matching';
                return $this->matches;
            }
        };

        return new QueryAuthorizedPlanner(
            $registry,
            new PolicyEngine($checker),
            new WordPressPostsQueryCompiler(),
            null,
            new QueryFieldPredicateResolver($fields),
        );
    }

    private function provider(ArrayObject $trace): QueryProviderExecutorInterface
    {
        return new class($trace) implements QueryProviderExecutorInterface {
            public function __construct(private readonly ArrayObject $trace)
            {
            }
            public function supports(QueryProviderPlan $plan): bool
            {
                return true;
            }
            public function execute(QueryProviderPlan $plan): QueryExecutionResult|QueryExecutionError
            {
                $this->trace[] = 'provider';
                return new QueryExecutionResult($plan->provider, $plan->sourceRef, $plan->projection, [], 0);
            }
        };
    }

    private function definition(): QueryDefinition
    {
        return new QueryDefinition(
            identity: [
                'uuid' => '01990f6e-1f30-4000-8000-000000000204',
                'key' => 'posts.field-authorized',
                'name' => 'Field authorized',
                'revision' => 1,
                'lifecycle' => 'draft',
            ],
            astVersion: 1,
            source: new QuerySourceReference('wordpress.posts', 'wordpress.posts', 1),
            operation: 'select',
            projection: ['post.id'],
            parameters: [],
            filter: new QueryPredicate(QueryPredicateType::Group, ['boolean' => 'and'], [
                new QueryPredicate(QueryPredicateType::SetMembership, [
                    'field_ref' => 'post.id',
                    'operator' => 'in',
                    'values' => [11, 13],
                ]),
                new QueryPredicate(QueryPredicateType::Field, [
                    'field_ref' => $this->fieldRef(),
                    'operator' => 'eq',
                    'value' => 'gold',
                ]),
            ]),
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

    private function fieldRef(): string
    {
        return 'fields.01990f6e-1f30-4000-8000-000000000200.01990f6e-1f30-4000-8000-000000000202';
    }
}
