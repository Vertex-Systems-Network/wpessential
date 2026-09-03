<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\FieldQueryConsumerInterface;
use WPEssential\Modules\Query\QueryDefinition;
use WPEssential\Modules\Query\QueryFieldPredicateResolver;
use WPEssential\Modules\Query\QueryPagination;
use WPEssential\Modules\Query\QueryPlanningException;
use WPEssential\Modules\Query\QueryPredicate;
use WPEssential\Modules\Query\QueryPredicateType;
use WPEssential\Modules\Query\QuerySourceReference;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

final class QueryFieldPredicateResolverTest extends TestCase
{
    public function testFieldPredicateNarrowsFiniteCandidateSetThroughOwnerContract(): void
    {
        $trace = new ArrayObject();
        $result = (new QueryFieldPredicateResolver($this->fields($trace, [13])))->resolve(
            $this->definition([11, 13]),
            $this->context(),
        );

        self::assertFalse($result->shortCircuitEmpty);
        self::assertCount(1, $result->definition->filter?->children ?? []);
        self::assertSame(QueryPredicateType::SetMembership, $result->definition->filter?->children[0]->type);
        self::assertSame([13], $result->definition->filter?->children[0]->payload['values']);
        self::assertSame(['describe', 'matching'], $trace->getArrayCopy());
    }

    public function testEmptyOwnerMatchProducesShortCircuitMarker(): void
    {
        $result = (new QueryFieldPredicateResolver($this->fields(new ArrayObject(), [])))->resolve(
            $this->definition([11, 13]),
            $this->context(),
        );

        self::assertTrue($result->shortCircuitEmpty);
        self::assertCount(1, $result->definition->filter?->children ?? []);
    }

    public function testFieldExecutionRequiresFiniteAnchor(): void
    {
        $definition = $this->definition([11]);
        $field = $definition->filter?->children[1];
        self::assertInstanceOf(QueryPredicate::class, $field);
        $definition = $this->withFilter($definition, new QueryPredicate(
            QueryPredicateType::Group,
            ['boolean' => 'and'],
            [$field],
        ));

        $this->expectException(QueryPlanningException::class);
        $this->expectExceptionMessage('finite post-id anchor');
        (new QueryFieldPredicateResolver($this->fields(new ArrayObject(), [])))->resolve($definition, $this->context());
    }

    public function testForeignOrDuplicateOwnerResultsFailClosed(): void
    {
        foreach ([[999], [11, 11]] as $matches) {
            try {
                (new QueryFieldPredicateResolver($this->fields(new ArrayObject(), $matches)))->resolve(
                    $this->definition([11, 13]),
                    $this->context(),
                );
                self::fail('Expected malformed owner result to fail closed.');
            } catch (QueryPlanningException $exception) {
                self::assertSame('wpe_query_provider_failed', $exception->errorCode);
            }
        }
    }

    public function testOwnerContractVersionMismatchFailsClosed(): void
    {
        $fields = new class($this->fieldRef()) implements FieldQueryConsumerInterface {
            public function __construct(private readonly string $fieldRef)
            {
            }
            public function describe(string $fieldReference, ExecutionContext $context): array
            {
                return [
                    'contract_version' => 999,
                    'field_ref' => $this->fieldRef,
                    'logical_type' => 'string',
                    'operators' => ['eq'],
                    'max_candidate_ids' => self::MAX_CANDIDATE_IDS,
                    'max_result_ids' => self::MAX_RESULT_IDS,
                    'storage_owner' => 'native_post_meta',
                ];
            }
            public function matchingPostIds(string $fieldReference, string $operator, mixed $value, array $candidatePostIds, int $limit, ExecutionContext $context): array
            {
                return [];
            }
        };

        try {
            (new QueryFieldPredicateResolver($fields))->resolve($this->definition([11]), $this->context());
            self::fail('Expected incompatible owner contract to fail closed.');
        } catch (QueryPlanningException $exception) {
            self::assertSame('wpe_query_dependency_unavailable', $exception->errorCode);
        }
    }

    /** @param list<int> $matches */
    private function fields(ArrayObject $trace, array $matches): FieldQueryConsumerInterface
    {
        return new class($trace, $matches, $this->fieldRef()) implements FieldQueryConsumerInterface {
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

            public function matchingPostIds(
                string $fieldReference,
                string $operator,
                mixed $value,
                array $candidatePostIds,
                int $limit,
                ExecutionContext $context,
            ): array {
                $this->trace[] = 'matching';
                return $this->matches;
            }
        };
    }

    /** @param list<int> $anchorIds */
    private function definition(array $anchorIds): QueryDefinition
    {
        return new QueryDefinition(
            identity: [
                'uuid' => '01990f6e-1f30-4000-8000-000000000203',
                'key' => 'posts.field-owner',
                'name' => 'Field owner',
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

    private function fieldRef(): string
    {
        return 'fields.01990f6e-1f30-4000-8000-000000000200.01990f6e-1f30-4000-8000-000000000202';
    }
}
