<?php

declare(strict_types=1);

namespace WPEssential\Platform\Rendering;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Platform\Assets\AssetRegistry;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\DynamicValues\DynamicValueRouter;

final class RenderingServiceRegistrar
{
    public const SERVICE_ASSETS = 'platform.assets';
    public const SERVICE_BLUEPRINTS = 'platform.components.blueprints';
    public const SERVICE_DYNAMIC_VALUES = 'platform.dynamic-values';
    public const SERVICE_RENDERER = 'platform.renderer';

    /** @var list<string> */
    private const SERVICE_IDS = [
        self::SERVICE_ASSETS,
        self::SERVICE_BLUEPRINTS,
        self::SERVICE_DYNAMIC_VALUES,
        self::SERVICE_RENDERER,
    ];

    public function register(ServiceRegistryInterface $services): void
    {
        foreach (self::SERVICE_IDS as $serviceId) {
            if ($services->has($serviceId)) {
                throw new LogicException(sprintf('Shared rendering service "%s" is already registered.', $serviceId));
            }
        }

        $assets = new AssetRegistry();
        $blueprints = new ComponentBlueprintRegistry();
        $dynamicValues = new DynamicValueRouter();
        $renderer = new BlueprintRendererDispatcher($blueprints);

        $services->set(self::SERVICE_ASSETS, $assets);
        $services->set(self::SERVICE_BLUEPRINTS, $blueprints);
        $services->set(self::SERVICE_DYNAMIC_VALUES, $dynamicValues);
        $services->set(self::SERVICE_RENDERER, $renderer);
    }
}
