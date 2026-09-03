<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\RelationQueryConsumerInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\DataSources\DataSourceAuthorizationMapping;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\Modules\ModuleManifest;

final class QueryModule implements ModuleInterface
{
    public const SERVICE_VALIDATOR = 'module.query.validator';
    public const SERVICE_COMPILER = 'module.query.compiler.wordpress-posts';
    public const SERVICE_PLANNER = 'module.query.authorized-planner';
    public const SERVICE_EXECUTOR = 'module.query.authorized-executor';

    private const DATA_SOURCE_SERVICE = 'platform.data-sources';
    private const POLICY_SERVICE = 'platform.abilities.policy';
    private const RELATIONS_QUERY_CONSUMER_SERVICE = 'module.relations.query-consumer';

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'query',
            name: 'Custom Query Builder',
            version: '0.1.0',
            edition: 'pro',
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $dataSources = $this->requireDataSources($services);
        $policy = $this->requirePolicy($services);
        $relations = $this->optionalRelationsConsumer($services);

        $this->assertServiceIdsAvailable($services);
        if ($dataSources->has(WordPressPostsQueryCompiler::SOURCE_REF)) {
            throw new LogicException('Query canonical wordpress.posts Data Source is already registered.');
        }

        $compiler = new WordPressPostsQueryCompiler();
        $validator = new QueryAstValidator($dataSources, $relations);
        $relationResolver = $relations !== null ? new QueryRelationPredicateResolver($relations) : null;
        $planner = new QueryAuthorizedPlanner($dataSources, $policy, $compiler, $relationResolver);
        $providerExecutor = new WordPressPostsQueryExecutor();
        $executor = new QueryAuthorizedExecutor($planner, $providerExecutor);

        $dataSources->register($this->wordpressPostsDescriptor($relations !== null));
        $services->set(self::SERVICE_VALIDATOR, $validator);
        $services->set(self::SERVICE_COMPILER, $compiler);
        $services->set(self::SERVICE_PLANNER, $planner);
        $services->set(self::SERVICE_EXECUTOR, $executor);
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        $assets = $services->get('platform.admin.assets');
        $dataSources = $services->get(self::DATA_SOURCE_SERVICE);
        if (!$assets instanceof AdminAssetManifest || !$dataSources instanceof DataSourceRegistryInterface) {
            throw new LogicException('Query admin requires canonical shared admin assets and Data Source Registry.');
        }

        $admin = new QueryAdminController(new QueryAdminBootstrapProjector($dataSources), $assets);
        $services->set('module.query.admin', $admin);
        $admin->register();
        // Query execution remains an internal service. No REST/AJAX/admin execution endpoint is exposed here.
    }

    private function requireDataSources(ServiceRegistryInterface $services): DataSourceRegistryInterface
    {
        if (!$services->has(self::DATA_SOURCE_SERVICE)) {
            throw new LogicException('Query requires the canonical shared Data Source Registry.');
        }

        $dataSources = $services->get(self::DATA_SOURCE_SERVICE);
        if (!$dataSources instanceof DataSourceRegistryInterface) {
            throw new LogicException('Query requires platform.data-sources to implement DataSourceRegistryInterface.');
        }

        return $dataSources;
    }

    private function requirePolicy(ServiceRegistryInterface $services): PolicyEngine
    {
        if (!$services->has(self::POLICY_SERVICE)) {
            throw new LogicException('Query requires the canonical shared PolicyEngine.');
        }

        $policy = $services->get(self::POLICY_SERVICE);
        if (!$policy instanceof PolicyEngine) {
            throw new LogicException('Query requires platform.abilities.policy to be the canonical PolicyEngine.');
        }

        return $policy;
    }

    private function optionalRelationsConsumer(ServiceRegistryInterface $services): ?RelationQueryConsumerInterface
    {
        if (!$services->has(self::RELATIONS_QUERY_CONSUMER_SERVICE)) {
            return null;
        }

        $relations = $services->get(self::RELATIONS_QUERY_CONSUMER_SERVICE);
        if (!$relations instanceof RelationQueryConsumerInterface) {
            throw new LogicException('Query optional Relations consumer must implement RelationQueryConsumerInterface.');
        }

        return $relations;
    }

    private function assertServiceIdsAvailable(ServiceRegistryInterface $services): void
    {
        foreach (
            [
                self::SERVICE_VALIDATOR,
                self::SERVICE_COMPILER,
                self::SERVICE_PLANNER,
                self::SERVICE_EXECUTOR,
            ] as $serviceId
        ) {
            if ($services->has($serviceId)) {
                throw new LogicException(sprintf('Query service "%s" is already registered.', $serviceId));
            }
        }
    }

    private function wordpressPostsDescriptor(bool $supportsRelations): DataSourceDescriptor
    {
        return new DataSourceDescriptor(
            id: WordPressPostsQueryCompiler::SOURCE_REF,
            sourceType: WordPressPostsQueryCompiler::SOURCE_REF,
            capabilityVersion: 1,
            fieldSchema: [
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
            ],
            predicates: ['eq', 'neq', 'in', 'not_in', 'contains'],
            sortModes: ['field'],
            paginationModes: ['offset'],
            aggregationModes: [],
            supportsRelations: $supportsRelations,
            policyRequired: true,
            scopes: ['site'],
            maxPageSize: 100,
            maxBatchSize: 100,
            cacheable: false,
            cacheGenerationKeys: [],
            diagnosticsAvailable: false,
            authorization: new DataSourceAuthorizationMapping(
                ability: 'wpessential/query/execute',
                capability: 'read',
                resourceType: 'post',
            ),
        );
    }
}
