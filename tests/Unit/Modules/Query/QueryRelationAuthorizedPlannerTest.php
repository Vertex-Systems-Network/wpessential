<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\RelationQueryConsumerInterface;
use WPEssential\Modules\Query\QueryAuthorizedExecutor;
use WPEssential\Modules\Query\QueryAuthorizedPlanner;
use WPEssential\Modules\Query\QueryDefinition;
use WPEssential\Modules\Query\QueryExecutionError;
use WPEssential\Modules\Query\QueryExecutionResult;
use WPEssential\Modules\Query\QueryPagination;
use WPEssential\Modules\Query\QueryPostsResourceAuthorizer;
use WPEssential\Modules\Query\QueryPredicate;
use WPEssential\Modules\Query\QueryPredicateType;
use WPEssential\Modules\Query\QueryProviderExecutorInterface;
use WPEssential\Modules\Query\QueryProviderPlan;
use WPEssential\Modules\Query\QueryRelationPredicateResolver;
use WPEssential\Modules\Query\QuerySourceReference;
use WPEssential\Modules\Query\WordPressPostsQueryCompiler;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DataSources\DataSourceAuthorizationMapping;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class QueryRelationAuthorizedPlannerTest extends TestCase
{
    public function testSourcePolicyRunsBeforeRelationResolutionAndProducesNarrowNativePlan(): void
    {
        $trace = new ArrayObject();
        $planner = $this->planner(true, $trace, [13]);

        $plan = $planner->plan($this->definition(), $this->context());

        self::assertSame('policy', $trace[0]);
        self::assertSame('describe', $trace[1]);
        self::assertSame('read:11', $trace[2]);
        self::assertSame('read:13', $trace[3]);
        self::assertSame('matching', $trace[4]);
        self::assertSame([13], $plan->providerPlan->arguments['post__in']);
        self::assertSame('ids', $plan->providerPlan->arguments['fields']);
        self::assertNull($plan->shortCircuitResult);
    }

    public function testPolicyDenialStopsBeforeRelationConsumerAndPostAuthorization(): void
    {
        $trace = new ArrayObject();
        $executor = new QueryAuthorizedExecutor(
            $this->planner(false, $trace, [13]),
            $this->provider($trace),
        );

        $result = $executor->execute($this->definition(), $this->context());

        self::assertInstanceOf(QueryExecutionError::class, $result);
        self::assertSame('wpe_query_policy_denied', $result->errorCode);
        self::assertSame(['policy'], $trace->getArrayCopy());
    }

    public function testEmptyRelationResolutionShortCircuitsBeforeNativeProvider(): void
    {
        $trace = new ArrayObject();
        $executor = new QueryAuthorizedExecutor(
            $this->planner(true, $trace, []),
            $this->provider($trace),
        );

        $result = $executor->execute($this->definition(), $this->context());

        self::assertInstanceOf(QueryExecutionResult::class, $result);
        self::assertSame([], $result->rows);
        self::assertSame(0, $result->returned);
        self::assertNotContains('provider', $trace->getArrayCopy());
    }

    /** @param list<int> $matches */
    private function planner(bool $policyAllowed, ArrayObject $trace, array $matches): QueryAuthorizedPlanner
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
            supportsRelations: true,
            policyRequired: true,
            scopes: ['site'],
            maxPageSize: 100,
            maxBatchSize: 100,
            authorization: new DataSourceAuthorizationMapping(
                ability: 'wpessential/query/execute',
                capability: 'read',
                resourceType: 'post',
            ),
        ));

        $checker = new class($policyAllowed, $trace) implements CapabilityCheckerInterface {
            public function __construct(
                private readonly bool $allowed,
                private readonly ArrayObject $trace,
            ) {
            }

            public function can(ExecutionContext $context, string $capability): bool
            {
                $this->trace[] = 'policy';
                return $this->allowed;
            }
        };

        $relations = new class($trace, $matches, $this->relationId()) implements RelationQueryConsumerInterface {
            /** @param list<int> $matches */
            public function __construct(
                private readonly ArrayObject $trace,
                private readonly array $matches,
                private readonly string $relationId,
            ) {
            }

            public function describe(string $relationDefinitionId, ExecutionContext $context): array
            {
                $this->trace[] = 'describe';
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'relation_definition_id' => $this->relationId,
                    'relation_key' => 'posts.related',
                    'definition_revision' => 1,
                    'mutation_revision' => 1,
                    'cardinality' => 'many_to_many',
                    'direction' => ['reciprocal' => false, 'bidirectional_traversal' => true],
                    'from' => ['object_type' => 'post', 'object_subtype' => 'post'],
                    'to' => ['object_type' => 'post', 'object_subtype' => 'post'],
                    'capabilities' => [
                        'exists' => true,
                        'related_ids' => true,
                        'count_distinct' => true,
                        'batch_exists' => true,
                        'max_batch_size' => self::MAX_BATCH_SIZE,
                        'max_result_limit' => self::MAX_RESULT_LIMIT,
                        'max_traversal_depth' => 1,
                    ],
                ];
            }

            public function relatedObjectIds(
                string $relationDefinitionId,
                string $direction,
                int $anchorObjectId,
                int $limit,
                ExecutionContext $context,
            ): array {
                return [];
            }

            public function matchingAnchorObjectIds(
                string $relationDefinitionId,
                string $direction,
                array $anchorObjectIds,
                ?array $relatedObjectIds,
                int $limit,
                ExecutionContext $context,
            ): array {
                $this->trace[] = 'matching';
                return $this->matches;
            }

            public function countRelatedObjects(
                string $relationDefinitionId,
                string $direction,
                int $anchorObjectId,
                ExecutionContext $context,
            ): int {
                return 0;
            }
        };

        $posts = new QueryPostsResourceAuthorizer(
            static fn (int $postId): ?object => (object) ['ID' => $postId, 'post_type' => 'post'],
            static function (int $actorId, int $postId) use ($trace): bool {
                $trace[] = 'read:' . $postId;
                return true;
            },
        );

        return new QueryAuthorizedPlanner(
            $registry,
            new PolicyEngine($checker),
            new WordPressPostsQueryCompiler(),
            new QueryRelationPredicateResolver($relations, $posts),
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
                return new QueryExecutionResult(
                    provider: $plan->provider,
                    sourceRef: $plan->sourceRef,
                    projection: $plan->projection,
                    rows: [],
                    returned: 0,
                );
            }
        };
    }

    private function definition(): QueryDefinition
    {
        return new QueryDefinition(
            identity: [
                'uuid' => '01990f6e-1f30-4000-8000-000000000102',
                'key' => 'posts.relation-authorized',
                'name' => 'Relation authorized',
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
                new QueryPredicate(QueryPredicateType::Relation, [
                    'relation_ref' => $this->relationId(),
                    'direction' => RelationQueryConsumerInterface::DIRECTION_FROM,
                    'mode' => 'exists',
                    'related_ids' => null,
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

    private function relationId(): string
    {
        return '01990f6e-1f30-4000-8000-000000000099';
    }
}
