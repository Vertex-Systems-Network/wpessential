<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\FieldQueryConsumerInterface;
use WPEssential\Modules\Query\QueryFieldAwareAstValidator;
use WPEssential\Modules\Query\QueryPredicateType;
use WPEssential\Modules\Query\QueryValidationBudget;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class QueryFieldAwareAstValidatorTest extends TestCase
{
    public function testOwnerFieldReferenceIsProjectedIntoCanonicalValidationView(): void
    {
        $result = (new QueryFieldAwareAstValidator(
            $this->registry(),
            null,
            $this->fields(),
        ))->validate($this->ast(), $this->budget(), $this->context());

        self::assertTrue($result->isValid());
        self::assertSame(QueryPredicateType::Group, $result->definition?->filter?->type);
        self::assertSame(QueryPredicateType::Field, $result->definition?->filter?->children[1]->type);
        self::assertSame($this->fieldRef(), $result->definition?->filter?->children[1]->payload['field_ref']);
    }

    public function testOwnerFieldReferenceCannotEscapeIntoProjection(): void
    {
        $ast = $this->ast();
        $ast['projection'] = [$this->fieldRef()];
        $result = (new QueryFieldAwareAstValidator($this->registry(), null, $this->fields()))->validate(
            $ast,
            $this->budget(),
            $this->context(),
        );

        self::assertFalse($result->isValid());
        self::assertSame('wpe_query_unsupported_operator', $result->issues[0]->code);
    }

    public function testOwnerFieldReferenceCannotEscapeIntoSorting(): void
    {
        $ast = $this->ast();
        $ast['order_by'] = [[
            'field_ref' => $this->fieldRef(),
            'direction' => 'asc',
        ]];
        $result = (new QueryFieldAwareAstValidator($this->registry(), null, $this->fields()))->validate(
            $ast,
            $this->budget(),
            $this->context(),
        );

        self::assertFalse($result->isValid());
        self::assertSame('wpe_query_unsupported_operator', $result->issues[0]->code);
    }

    public function testOwnerFieldReferenceCannotUseNonFieldPredicateShortcut(): void
    {
        $ast = $this->ast();
        $ast['filter']['children'][] = [
            'type' => 'comparison',
            'field_ref' => $this->fieldRef(),
            'operator' => 'eq',
            'value' => 'gold',
        ];
        $result = (new QueryFieldAwareAstValidator($this->registry(), null, $this->fields()))->validate(
            $ast,
            $this->budget(),
            $this->context(),
        );

        self::assertFalse($result->isValid());
        self::assertSame('wpe_query_unsupported_operator', $result->issues[0]->code);
    }

    public function testMalformedOwnerDescriptorFailsClosed(): void
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
                    'group_revision' => 1,
                    'field_uuid' => '01990f6e-1f30-4000-8000-000000000202',
                    'logical_type' => 'string',
                    'operators' => ['eq'],
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
                return [];
            }
        };

        $result = (new QueryFieldAwareAstValidator($this->registry(), null, $fields))->validate(
            $this->ast(),
            $this->budget(),
            $this->context(),
        );

        self::assertFalse($result->isValid());
        self::assertSame('wpe_query_dependency_unavailable', $result->issues[0]->code);
    }

    private function fields(): FieldQueryConsumerInterface
    {
        return new class($this->fieldRef()) implements FieldQueryConsumerInterface {
            public function __construct(private readonly string $fieldRef)
            {
            }

            public function describe(string $fieldReference, ExecutionContext $context): array
            {
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
                return [];
            }
        };
    }

    private function registry(): DataSourceRegistry
    {
        $registry = new DataSourceRegistry();
        $registry->register(new DataSourceDescriptor(
            id: 'wordpress.posts',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: ['post.id' => 'integer', 'post.title' => 'string'],
            predicates: ['eq', 'neq', 'in', 'not_in'],
            sortModes: ['field'],
            paginationModes: ['offset'],
            maxPageSize: 100,
            maxBatchSize: 100,
        ));
        return $registry;
    }

    private function budget(): QueryValidationBudget
    {
        return new QueryValidationBudget(100000, 8, 100, 100, 100, 2);
    }

    /** @return array<string,mixed> */
    private function ast(): array
    {
        return [
            'identity' => [
                'uuid' => '01990f6e-1f30-4000-8000-000000000201',
                'key' => 'posts.field-filter',
                'name' => 'Field filter',
                'revision' => 1,
                'lifecycle' => 'draft',
            ],
            'ast_version' => 1,
            'source' => [
                'source_ref' => 'wordpress.posts',
                'source_type' => 'wordpress.posts',
                'capability_version' => 1,
            ],
            'operation' => 'select',
            'projection' => ['post.id'],
            'parameters' => [],
            'filter' => [
                'type' => 'group',
                'boolean' => 'and',
                'children' => [
                    [
                        'type' => 'set_membership',
                        'field_ref' => 'post.id',
                        'operator' => 'in',
                        'values' => [11, 13],
                    ],
                    [
                        'type' => 'field',
                        'field_ref' => $this->fieldRef(),
                        'operator' => 'eq',
                        'value' => 'gold',
                    ],
                ],
            ],
            'order_by' => [],
            'pagination' => ['mode' => 'offset', 'page_size' => 20, 'offset' => 0],
            'distinct' => false,
            'execution_policy' => [],
            'cache_policy' => [],
        ];
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
