<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use Throwable;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryReader;
use WPEssential\Modules\Listings\Rendering\ListingServerRenderer;
use WPEssential\Modules\Query\QueryModule;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\DynamicValues\DynamicValueRouter;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\Rendering\BlueprintRendererDispatcher;
use WPEssential\Platform\Rendering\RenderingServiceRegistrar;

final class ListingsModule implements ModuleInterface
{
    public const SERVICE_QUERY_READER = 'module.listings.query-reader';
    public const SERVICE_SERVER_RENDERER = 'module.listings.server-renderer';

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'listings',
            name: 'Dynamic Listings',
            version: '0.1.0',
            edition: 'pro',
            dependencies: ['query'],
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $this->assertOutputServiceIdsAvailable($services);
        $this->requireSharedRuntime($services);
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        $this->assertOutputServiceIdsAvailable($services);
        [$query, $blueprints, $renderer, $dynamicValues] = $this->requireSharedRuntime($services);

        try {
            $blueprints->validateGraph();
        } catch (Throwable $exception) {
            throw new LogicException(
                'Listings requires a valid shared Component Blueprint dependency graph.',
                0,
                $exception,
            );
        }

        $queryReader = new ListingQueryReader($query);
        $serverRenderer = new ListingServerRenderer(
            $queryReader,
            $blueprints,
            $renderer,
            $dynamicValues,
        );

        $services->set(self::SERVICE_QUERY_READER, $queryReader);
        $services->set(self::SERVICE_SERVER_RENDERER, $serverRenderer);
    }

    private function assertOutputServiceIdsAvailable(ServiceRegistryInterface $services): void
    {
        foreach ([self::SERVICE_QUERY_READER, self::SERVICE_SERVER_RENDERER] as $serviceId) {
            if ($services->has($serviceId)) {
                throw new LogicException(sprintf('Listings service "%s" is already registered.', $serviceId));
            }
        }
    }

    /**
     * @return array{
     *   QueryReadConsumerInterface,
     *   ComponentBlueprintRegistry,
     *   BlueprintRendererDispatcher,
     *   DynamicValueRouter
     * }
     */
    private function requireSharedRuntime(ServiceRegistryInterface $services): array
    {
        foreach (
            [
                QueryModule::SERVICE_READ_CONSUMER,
                RenderingServiceRegistrar::SERVICE_BLUEPRINTS,
                RenderingServiceRegistrar::SERVICE_RENDERER,
                RenderingServiceRegistrar::SERVICE_DYNAMIC_VALUES,
            ] as $serviceId
        ) {
            if (!$services->has($serviceId)) {
                throw new LogicException(sprintf('Listings requires canonical shared service "%s".', $serviceId));
            }
        }

        $query = $services->get(QueryModule::SERVICE_READ_CONSUMER);
        $blueprints = $services->get(RenderingServiceRegistrar::SERVICE_BLUEPRINTS);
        $renderer = $services->get(RenderingServiceRegistrar::SERVICE_RENDERER);
        $dynamicValues = $services->get(RenderingServiceRegistrar::SERVICE_DYNAMIC_VALUES);

        if (!$query instanceof QueryReadConsumerInterface) {
            throw new LogicException('Listings requires the canonical Query read-consumer contract.');
        }
        if (!$blueprints instanceof ComponentBlueprintRegistry) {
            throw new LogicException('Listings requires the production shared Component Blueprint Registry.');
        }
        if (!$renderer instanceof BlueprintRendererDispatcher) {
            throw new LogicException('Listings requires the production shared Blueprint Renderer Dispatcher.');
        }
        if (!$dynamicValues instanceof DynamicValueRouter) {
            throw new LogicException('Listings requires the production shared Dynamic Value Router.');
        }

        return [$query, $blueprints, $renderer, $dynamicValues];
    }
}
