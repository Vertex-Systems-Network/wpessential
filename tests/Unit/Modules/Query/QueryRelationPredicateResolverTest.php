<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\RelationQueryConsumerInterface;
use WPEssential\Modules\Query\QueryDefinition;
use WPEssential\Modules\Query\QueryPagination;
use WPEssential\Modules\Query\QueryPlanningException;
use WPEssential\Modules\Query\QueryPostsResourceAuthorizer;
use WPEssential\Modules\Query\QueryPredicate;
use WPEssential\Modules\Query\QueryPredicateType;
use WPEssential\Modules\Query\QueryRelationPredicateResolver;
use WPEssential\Modules\Query\QuerySourceReference;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

final class QueryRelationPredicateResolverTest extends TestCase
{
    public function testExistsNarrowsFiniteAuthorizedPostAnchorsThroughPublicContract(): void
    {
        $trace = new ArrayObject();
        $relations = $this->relations($trace, [13]);
        $posts = new QueryPostsResourceAuthorizer(
            static function (int $postId) use ($trace): ?object {
                $trace[] = 'load:' . $postId;
                return (object) ['ID' => $postId, 'post_type' => 'post'];
            },
            static function (int $actorId, int $postId) use ($trace): bool {
                $trace[] = 'read:' . $actorId . ':' . $postId;
                return true;
            },
        );

        $result = (new QueryRelationPredicateResolver($relations, $posts))->resolve(
            $this->definition([11, 13]),
            $this->context(),
        );

        self::assertFalse($result->shortCircuitEmpty);
        self::assertInstanceOf(QueryPredicate::class, $result->definition->filter);
        self::assertCount(1, $result->definition->filter->children);
        self::assertSame(QueryPredicateType::SetMembership, $result->definition->filter->children[0]->type);
        self::assertSame([13], $result->definition->filter->children[0]->payload['values']);
        self::assertSame([
            'describe',
            'load:11',
            'read:7:11',
            'load:13',
            'read:7:13',
            'matching',
        ], $trace->getArrayCopy());
    }

    public function testEmptyRelationMatchReturnsAuthorizedShortCircuitMarker(): void
    {
        $trace = new ArrayObject();
        $resolver = new QueryRelationPredicateResolver(
            $this->relations($trace, []),
            $this->allowingPostAuthorizer(),
        );

        $result = $resolver->resolve($this->definition([11, 13]), $this->context());

        self::assertTrue($result->shortCircuitEmpty);
        self::assertInstanceOf(QueryPredicate::class, $result->definition->filter);
        self::assertCount(1, $result->definition->filter->children);
        self::assertSame([11, 13], $result->definition->filter->children[0]->payload['values']);
    }

    public function testRelationExecutionRequiresExplicitFinitePostIdAnchorSet(): void
    {
        $definition = $this->definition([11]);
        $relation = $definition->filter?->children[1];
        self::assertInstanceOf(QueryPredicate::class, $relation);
        $definition = $this->withFilter($definition, new QueryPredicate(
            QueryPredicateType::Group,
            ['boolean' => 'and'],
            [$relation],
        ));

        $this->expectException(QueryPlanningException::class);
        $this->expectExceptionMessage('finite post-id anchor');

        (new QueryRelationPredicateResolver(
            $this->relations(new ArrayObject(), [11]),
            $this->allowingPostAuthorizer(),
        ))->resolve($definition, $this->context());
    }

    public function testOnlyDirectExistsWithoutRelatedIdsIsSupported(): void
    {
        $definition = $this->definition([11]);
        $anchor = $definition->filter?->children[0];
        self::assertInstanceOf(QueryPredicate::class, $anchor);
        $relation = new QueryPredicate(QueryPredicateType::Relation, [
            'relation_ref' => $this->relationId(),
            'direction' => RelationQueryConsumerInterface::DIRECTION_FROM,
            'mode' => 'any',
            'related_ids' => [99],
        ]);
        $definition = $this->withFilter($definition, new QueryPredicate(
            QueryPredicateType::Group,
            ['boolean' => 'and'],
            [$anchor, $relation],
        ));

        try {
            (new QueryRelationPredicateResolver(
                $this->relations(new ArrayObject(), [11]),
                $this->allowingPostAuthorizer(),
            ))->resolve($definition, $this->context());
            self::fail('Expected non-exists relation mode to fail closed.');
        } catch (QueryPlanningException $exception) {
            self::assertSame('wpe_query_unsupported_operator', $exception->errorCode);
        }
    }

    public function testUnreadableAnchorFailsBeforeRelationMatching(): void
    {
        $trace = new ArrayObject();
        $posts = new QueryPostsResourceAuthorizer(
            static fn (int $postId): ?object => (object) ['ID' => $postId, 'post_type' => 'post'],
            static fn (int $actorId, int $postId): bool => $postId !== 13,
        );

        try {
            (new QueryRelationPredicateResolver($this->relations($trace, [13]), $posts))->resolve(
                $this->definition([11, 13]),
                $this->context(),
            );
            self::fail('Expected unreadable relation anchor to be denied.');
        } catch (QueryPlanningException $exception) {
            self::assertSame('wpe_query_policy_denied', $exception->errorCode);
            self::assertSame(['describe'], $trace->getArrayCopy());
        }
    }

    public function testForeignAnchorReturnedByRelationsConsumerFailsClosed(): void
    {
        try {
            (new QueryRelationPredicateResolver(
                $this->relations(new ArrayObject(), [999]),
                $this->allowingPostAuthorizer(),
            ))->resolve($this->definition([11, 13]), $this->context());
            self::fail('Expected foreign Relations result to fail closed.');
        } catch (QueryPlanningException $exception) {
            self::assertSame('wpe_query_provider_failed', $exception->errorCode);
        }
    }

    /** @param list<int> $matches */
    private function relations(ArrayObject $trace, array $matches): RelationQueryConsumerInterface
    {
        return new class($trace, $matches, $this->relationId()) implements RelationQueryConsumerInterface {
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
                if ($relationDefinitionId !== $this->relationId) {
                    throw new \RuntimeException('unexpected relation');
                }
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'relation_definition_id' => $relationDefinitionId,
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
    }

    private function allowingPostAuthorizer(): QueryPostsResourceAuthorizer
    {
        return new QueryPostsResourceAuthorizer(
            static fn (int $postId): ?object => (object) ['ID' => $postId, 'post_type' => 'post'],
            static fn (int $actorId, int $postId): bool => true,
        );
    }

    /** @param list<int> $anchorIds */
    private function definition(array $anchorIds): QueryDefinition
    {
        return new QueryDefinition(
            identity: [
                'uuid' => '01990f6e-1f30-4000-8000-000000000101',
                'key' => 'posts.relation-exists',
                'name' => 'Relation exists',
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
                    'values' => $anchorIds,
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

    private function withFilter(QueryDefinition $definition, QueryPredicate $filter): QueryDefinition
    {
        return new QueryDefinition(
            identity: $definition->identity,
            astVersion: $definition->astVersion,
            source: $definition->source,
            operation: $definition->operation,
            projection: $definition->projection,
            parameters: $definition->parameters,
            filter: $filter,
            orderBy: $definition->orderBy,
            pagination: $definition->pagination,
            distinct: $definition->distinct,
            executionPolicy: $definition->executionPolicy,
            cachePolicy: $definition->cachePolicy,
            metadata: $definition->metadata,
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
