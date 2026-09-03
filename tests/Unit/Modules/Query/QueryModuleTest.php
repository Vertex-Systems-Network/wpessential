<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Query\QueryAstValidator;
use WPEssential\Modules\Query\QueryAuthorizedPlanner;
use WPEssential\Modules\Query\QueryDefinition;
use WPEssential\Modules\Query\QueryModule;
use WPEssential\Modules\Query\QueryPagination;
use WPEssential\Modules\Query\QuerySourceReference;
use WPEssential\Modules\Query\WordPressPostsQueryCompiler;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class QueryModuleTest extends TestCase
{
    public function testManifestDeclaresQueryAsProWithoutPeerHardDependencies(): void
    {
        $manifest = (new QueryModule())->manifest();

        self::assertSame('query', $manifest->id);
        self::assertSame('Custom Query Builder', $manifest->name);
        self::assertSame('pro', $manifest->edition);
        self::assertSame([], $manifest->dependencies);
    }

    public function testRegisterPublishesBoundedPostsDescriptorAndPlanningServices(): void
    {
        $services = new ServiceRegistry();
        $dataSources = new DataSourceRegistry();
        $checker = new class implements CapabilityCheckerInterface {
            /** @var list<string> */
            public array $checked = [];

            public function can(ExecutionContext $context, string $capability): bool
            {
                $this->checked[] = $capability;
                return true;
            }
        };

        $services->set('platform.data-sources', $dataSources);
        $services->set('platform.abilities.policy', new PolicyEngine($checker));

        (new QueryModule())->register($services);

        $descriptor = $dataSources->require(WordPressPostsQueryCompiler::SOURCE_REF);
        self::assertSame('wordpress.posts', $descriptor->sourceType);
        self::assertSame(1, $descriptor->capabilityVersion);
        self::assertSame([
            'post.id' => 'integer',
            'post.title' => 'string',
            'post.slug' => 'string',
            'post.date' => 'datetime',
            'post.modified' => 'datetime',
            'post.status' => 'string',
            'post.type' => 'string',
            'post.author_id' => 'integer',
            'post.parent_id' => 'integer',
            'post.excerpt' => 'string',
            'post.content' => 'string',
        ], $descriptor->fieldSchema);
        self::assertSame(['eq', 'neq', 'in', 'not_in', 'contains'], $descriptor->predicates);
        self::assertSame(['field'], $descriptor->sortModes);
        self::assertSame(['offset'], $descriptor->paginationModes);
        self::assertSame([], $descriptor->aggregationModes);
        self::assertFalse($descriptor->supportsRelations);
        self::assertTrue($descriptor->policyRequired);
        self::assertSame(['site'], $descriptor->scopes);
        self::assertFalse($descriptor->cacheable);
        self::assertFalse($descriptor->diagnosticsAvailable);

        $authorization = $descriptor->requireAuthorizationMapping();
        self::assertSame('wpessential/query/execute', $authorization->ability);
        self::assertSame('read', $authorization->capability);
        self::assertSame('post', $authorization->resourceType);

        self::assertInstanceOf(QueryAstValidator::class, $services->get(QueryModule::SERVICE_VALIDATOR));
        self::assertInstanceOf(WordPressPostsQueryCompiler::class, $services->get(QueryModule::SERVICE_COMPILER));
        $planner = $services->get(QueryModule::SERVICE_PLANNER);
        self::assertInstanceOf(QueryAuthorizedPlanner::class, $planner);

        $plan = $planner->plan($this->definition(), new ExecutionContext(new Principal(7), 1));
        self::assertSame(WordPressPostsQueryCompiler::PROVIDER, $plan->providerPlan->provider);
        self::assertSame('ids', $plan->providerPlan->arguments['fields']);
        self::assertSame(['read'], $checker->checked);
    }

    public function testMissingPolicyFailsBeforeRegisteringSourceOrQueryServices(): void
    {
        $services = new ServiceRegistry();
        $dataSources = new DataSourceRegistry();
        $services->set('platform.data-sources', $dataSources);

        $this->assertFailsClosed($services, $dataSources);
    }

    public function testMalformedOptionalRelationsConsumerFailsBeforeMutation(): void
    {
        $services = $this->servicesWithRequiredDependencies();
        $dataSources = $services->get('platform.data-sources');
        self::assertInstanceOf(DataSourceRegistry::class, $dataSources);
        $services->set('module.relations.query-consumer', new stdClass());

        $this->assertFailsClosed($services, $dataSources);
    }

    public function testExistingCanonicalPostsSourceFailsBeforeQueryServiceRegistration(): void
    {
        $services = $this->servicesWithRequiredDependencies();
        $dataSources = $services->get('platform.data-sources');
        self::assertInstanceOf(DataSourceRegistry::class, $dataSources);
        $dataSources->register(new DataSourceDescriptor(
            id: WordPressPostsQueryCompiler::SOURCE_REF,
            sourceType: WordPressPostsQueryCompiler::SOURCE_REF,
            capabilityVersion: 1,
            fieldSchema: ['post.id' => 'integer'],
        ));

        try {
            (new QueryModule())->register($services);
            self::fail('Expected Query registration to reject a duplicate canonical source.');
        } catch (LogicException) {
            self::assertCount(1, $dataSources->all());
            self::assertFalse($services->has(QueryModule::SERVICE_VALIDATOR));
            self::assertFalse($services->has(QueryModule::SERVICE_COMPILER));
            self::assertFalse($services->has(QueryModule::SERVICE_PLANNER));
        }
    }

    public function testExistingQueryServiceIdFailsBeforeCanonicalSourceMutation(): void
    {
        $services = $this->servicesWithRequiredDependencies();
        $dataSources = $services->get('platform.data-sources');
        self::assertInstanceOf(DataSourceRegistry::class, $dataSources);
        $sentinel = new stdClass();
        $services->set(QueryModule::SERVICE_VALIDATOR, $sentinel);

        try {
            (new QueryModule())->register($services);
            self::fail('Expected Query registration to reject an occupied Query service id.');
        } catch (LogicException) {
            self::assertFalse($dataSources->has(WordPressPostsQueryCompiler::SOURCE_REF));
            self::assertSame($sentinel, $services->get(QueryModule::SERVICE_VALIDATOR));
            self::assertFalse($services->has(QueryModule::SERVICE_COMPILER));
            self::assertFalse($services->has(QueryModule::SERVICE_PLANNER));
        }
    }

    private function servicesWithRequiredDependencies(): ServiceRegistry
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

    private function assertFailsClosed(ServiceRegistry $services, DataSourceRegistry $dataSources): void
    {
        try {
            (new QueryModule())->register($services);
            self::fail('Expected Query registration to fail closed.');
        } catch (LogicException) {
            self::assertFalse($dataSources->has(WordPressPostsQueryCompiler::SOURCE_REF));
            self::assertFalse($services->has(QueryModule::SERVICE_VALIDATOR));
            self::assertFalse($services->has(QueryModule::SERVICE_COMPILER));
            self::assertFalse($services->has(QueryModule::SERVICE_PLANNER));
        }
    }

    private function definition(): QueryDefinition
    {
        return new QueryDefinition(
            identity: [
                'uuid' => '01990f6e-1f30-7000-8000-000000000003',
                'key' => 'posts.module-test',
                'name' => 'Module test',
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
}
