<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\FieldQueryConsumerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Query\QueryAuthorizedPlanner;
use WPEssential\Modules\Query\QueryFieldAwareAstValidator;
use WPEssential\Modules\Query\QueryFieldPredicateResolver;
use WPEssential\Modules\Query\QueryModule;
use WPEssential\Modules\Query\WordPressPostsQueryCompiler;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class QueryModuleFieldsWiringTest extends TestCase
{
    public function testPublicFieldsConsumerWiresFieldAwareValidatorAndPlannerResolver(): void
    {
        $services = $this->services();
        $services->set('module.custom-fields.query-consumer', $this->fields());

        (new QueryModule())->register($services);

        self::assertInstanceOf(QueryFieldAwareAstValidator::class, $services->get(QueryModule::SERVICE_VALIDATOR));
        $planner = $services->get(QueryModule::SERVICE_PLANNER);
        self::assertInstanceOf(QueryAuthorizedPlanner::class, $planner);
        $property = new ReflectionProperty(QueryAuthorizedPlanner::class, 'fieldResolver');
        self::assertInstanceOf(QueryFieldPredicateResolver::class, $property->getValue($planner));
    }

    public function testMalformedOptionalFieldsConsumerFailsBeforeSourceMutation(): void
    {
        $services = $this->services();
        $services->set('module.custom-fields.query-consumer', new stdClass());
        $dataSources = $services->get('platform.data-sources');
        self::assertInstanceOf(DataSourceRegistry::class, $dataSources);

        try {
            (new QueryModule())->register($services);
            self::fail('Expected malformed Fields consumer to fail closed.');
        } catch (LogicException) {
            self::assertFalse($dataSources->has(WordPressPostsQueryCompiler::SOURCE_REF));
            self::assertFalse($services->has(QueryModule::SERVICE_VALIDATOR));
            self::assertFalse($services->has(QueryModule::SERVICE_PLANNER));
        }
    }

    private function services(): ServiceRegistry
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
        return $services;
    }

    private function fields(): FieldQueryConsumerInterface
    {
        return new class implements FieldQueryConsumerInterface {
            public function describe(string $fieldReference, ExecutionContext $context): array
            {
                return [
                    'contract_version' => self::CONTRACT_VERSION,
                    'field_ref' => $fieldReference,
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
                return [];
            }
        };
    }
}
