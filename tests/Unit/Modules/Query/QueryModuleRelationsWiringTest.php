<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\RelationQueryConsumerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Query\QueryAuthorizedPlanner;
use WPEssential\Modules\Query\QueryModule;
use WPEssential\Modules\Query\QueryRelationPredicateResolver;
use WPEssential\Modules\Query\WordPressPostsQueryCompiler;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class QueryModuleRelationsWiringTest extends TestCase
{
    public function testPublicRelationsConsumerEnablesDescriptorAndPlannerResolver(): void
    {
        $services = new ServiceRegistry();
        $dataSources = new DataSourceRegistry();
        $services->set('platform.data-sources', $dataSources);
        $services->set('platform.abilities.policy', new PolicyEngine(
            new class implements CapabilityCheckerInterface {
                public function can(ExecutionContext $context, string $capability): bool
                {
                    return true;
                }
            },
        ));
        $services->set('module.relations.query-consumer', $this->relations());

        (new QueryModule())->register($services);

        $descriptor = $dataSources->require(WordPressPostsQueryCompiler::SOURCE_REF);
        self::assertTrue($descriptor->supportsRelations);

        $planner = $services->get(QueryModule::SERVICE_PLANNER);
        self::assertInstanceOf(QueryAuthorizedPlanner::class, $planner);
        $property = new ReflectionProperty(QueryAuthorizedPlanner::class, 'relationResolver');
        self::assertInstanceOf(QueryRelationPredicateResolver::class, $property->getValue($planner));
    }

    private function relations(): RelationQueryConsumerInterface
    {
        return new class implements RelationQueryConsumerInterface {
            public function describe(string $relationDefinitionId, ExecutionContext $context): array
            {
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
    }
}
