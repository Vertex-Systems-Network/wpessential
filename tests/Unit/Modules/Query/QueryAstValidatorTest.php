<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\RelationQueryConsumerInterface;
use WPEssential\Modules\Query\QueryAstValidator;
use WPEssential\Modules\Query\QueryPredicateType;
use WPEssential\Modules\Query\QueryValidationBudget;
use WPEssential\Modules\Query\QueryValidationResult;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class QueryAstValidatorTest extends TestCase
{
    public function testValidMinimalAstProducesTypedDefinition(): void
    {
        $validator = new QueryAstValidator($this->registry());
        $result = $validator->validate($this->validAst(), $this->budget());

        self::assertTrue($result->isValid());
        self::assertNotNull($result->definition);
        self::assertSame('wordpress.posts', $result->definition->source->sourceRef);
        self::assertSame(['post.id', 'post.title'], $result->definition->projection);
        self::assertSame(QueryPredicateType::Comparison, $result->definition->filter?->type);
        self::assertSame(20, $result->definition->pagination->pageSize);
    }

    public function testUnknownSemanticNodeFailsClosed(): void
    {
        $ast = $this->validAst();
        $ast['filter'] = ['type' => 'raw_where', 'value' => 'post.id = 1'];

        $result = (new QueryAstValidator($this->registry()))->validate($ast, $this->budget());

        self::assertFalse($result->isValid());
        self::assertContains('wpe_query_invalid_ast', $this->codes($result));
        self::assertNull($result->definition);
    }

    public function testUnsafeSqlPhpCallbackAndIdentifierPayloadsFailClosed(): void
    {
        $cases = [
            ['raw_sql' => 'SELECT * FROM wp_posts'],
            ['payload' => '<?php eval($code);'],
            ['callback' => 'dangerous_handler'],
            ['identifier' => 'wp_posts.post_title'],
        ];

        foreach ($cases as $unsafeMetadata) {
            $ast = $this->validAst();
            $ast['metadata'] = $unsafeMetadata;
            $result = (new QueryAstValidator($this->registry()))->validate($ast, $this->budget());

            self::assertFalse($result->isValid());
            self::assertContains('wpe_query_invalid_ast', $this->codes($result));
        }
    }

    public function testMalformedTypesAndUnknownTopLevelPropertiesAreRejected(): void
    {
        $ast = $this->validAst();
        $ast['distinct'] = 'yes';
        $ast['pagination']['page_size'] = '20';
        $ast['mystery_semantic'] = true;

        $result = (new QueryAstValidator($this->registry()))->validate($ast, $this->budget());

        self::assertFalse($result->isValid());
        self::assertContains('wpe_query_type_mismatch', $this->codes($result));
        self::assertContains('wpe_query_invalid_ast', $this->codes($result));
    }

    public function testStructuralBudgetsBlockOversizedAstDepthPredicateSetAndPageSize(): void
    {
        $ast = $this->validAst();
        $ast['filter'] = [
            'type' => 'group',
            'boolean' => 'and',
            'children' => [
                [
                    'type' => 'set_membership',
                    'field_ref' => 'post.id',
                    'operator' => 'in',
                    'values' => [1, 2, 3],
                ],
                [
                    'type' => 'group',
                    'boolean' => 'or',
                    'children' => [
                        ['type' => 'comparison', 'field_ref' => 'post.id', 'operator' => 'eq', 'value' => 1],
                    ],
                ],
            ],
        ];
        $ast['pagination']['page_size'] = 20;
        $budget = new QueryValidationBudget(
            maxAstBytes: 100000,
            maxGroupDepth: 2,
            maxPredicateCount: 2,
            maxInListSize: 2,
            maxPageSize: 10,
            maxRelationDepth: 1,
        );

        $result = (new QueryAstValidator($this->registry()))->validate($ast, $budget);

        self::assertFalse($result->isValid());
        self::assertContains('wpe_query_cost_blocked', $this->codes($result));
    }

    public function testAstByteBudgetIsEnforcedWithoutInventingProductionThreshold(): void
    {
        $result = (new QueryAstValidator($this->registry()))->validate(
            $this->validAst(),
            new QueryValidationBudget(64, 10, 20, 20, 100, 2),
        );

        self::assertFalse($result->isValid());
        self::assertContains('wpe_query_cost_blocked', $this->codes($result));
    }

    public function testSourceMustResolveThroughCanonicalRegistryAndCapabilityMustMatch(): void
    {
        $ast = $this->validAst();
        $ast['source']['source_ref'] = 'missing.source';

        $unknown = (new QueryAstValidator($this->registry()))->validate($ast, $this->budget());
        self::assertContains('wpe_query_unknown_source', $this->codes($unknown));

        $ast = $this->validAst();
        $ast['source']['capability_version'] = 2;
        $mismatch = (new QueryAstValidator($this->registry()))->validate($ast, $this->budget());
        self::assertContains('wpe_query_dependency_unavailable', $this->codes($mismatch));
    }

    public function testUnknownFieldAndUnsupportedOperatorAreRejectedFromDescriptorCapabilities(): void
    {
        $ast = $this->validAst();
        $ast['filter'] = [
            'type' => 'comparison',
            'field_ref' => 'post.secret_column',
            'operator' => 'regex',
            'value' => '.*',
        ];

        $result = (new QueryAstValidator($this->registry()))->validate($ast, $this->budget());

        self::assertFalse($result->isValid());
        self::assertContains('wpe_query_invalid_ast', $this->codes($result));
        self::assertContains('wpe_query_unsupported_operator', $this->codes($result));
    }

    public function testRelationPredicateUsesOnlyPublicRelationsConsumerContract(): void
    {
        $relations = new class implements RelationQueryConsumerInterface {
            public function describe(string $relationDefinitionId, ExecutionContext $context): array
            {
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'relation_definition_id' => $relationDefinitionId,
                    'relation_key' => 'post.author',
                    'definition_revision' => 1,
                    'mutation_revision' => 1,
                    'cardinality' => 'many_to_one',
                    'direction' => ['reciprocal' => false, 'bidirectional_traversal' => true],
                    'from' => ['object_type' => 'post', 'object_subtype' => null],
                    'to' => ['object_type' => 'user', 'object_subtype' => null],
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
                return [];
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
        $ast = $this->validAst();
        $ast['filter'] = [
            'type' => 'relation',
            'relation_ref' => 'relations.post_author',
            'direction' => 'from',
            'mode' => 'exists',
        ];

        $result = (new QueryAstValidator($this->registry(), $relations))->validate(
            $ast,
            $this->budget(),
            new ExecutionContext(new Principal(1), 1),
        );

        self::assertTrue($result->isValid());
        self::assertSame(QueryPredicateType::Relation, $result->definition?->filter?->type);
    }

    public function testRelationPredicateFailsWhenPublicConsumerDependencyIsUnavailable(): void
    {
        $ast = $this->validAst();
        $ast['filter'] = [
            'type' => 'relation',
            'relation_ref' => 'relations.post_author',
            'direction' => 'from',
            'mode' => 'exists',
        ];

        $result = (new QueryAstValidator($this->registry()))->validate($ast, $this->budget());

        self::assertFalse($result->isValid());
        self::assertContains('wpe_query_dependency_unavailable', $this->codes($result));
    }

    private function registry(): DataSourceRegistry
    {
        $registry = new DataSourceRegistry();
        $registry->register(new DataSourceDescriptor(
            id: 'wordpress.posts',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: [
                'post.id' => 'integer',
                'post.title' => 'string',
                'post.date' => 'datetime',
            ],
            predicates: ['eq', 'neq', 'contains', 'exists', 'not_exists', 'in', 'not_in', 'between'],
            sortModes: ['field'],
            paginationModes: ['offset', 'cursor'],
            supportsRelations: true,
            scopes: ['site'],
            maxPageSize: 100,
        ));

        return $registry;
    }

    private function budget(): QueryValidationBudget
    {
        return new QueryValidationBudget(
            maxAstBytes: 100000,
            maxGroupDepth: 8,
            maxPredicateCount: 100,
            maxInListSize: 100,
            maxPageSize: 100,
            maxRelationDepth: 2,
        );
    }

    /** @return array<string,mixed> */
    private function validAst(): array
    {
        return [
            'identity' => [
                'uuid' => '01990f6e-1f30-7000-8000-000000000001',
                'key' => 'posts.latest',
                'name' => 'Latest posts',
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
            'projection' => ['post.id', 'post.title'],
            'parameters' => [],
            'filter' => [
                'type' => 'comparison',
                'field_ref' => 'post.id',
                'operator' => 'eq',
                'value' => 1,
            ],
            'order_by' => [],
            'pagination' => [
                'mode' => 'offset',
                'page_size' => 20,
                'offset' => 0,
            ],
            'distinct' => false,
            'execution_policy' => [],
            'cache_policy' => [],
        ];
    }

    /** @return list<string> */
    private function codes(QueryValidationResult $result): array
    {
        return array_map(
            static fn ($issue): string => $issue->code,
            $result->issues,
        );
    }
}
