<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomPostTypes;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinitionProviderRegistry;

final class CustomPostTypeModule implements ModuleInterface
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'custom-post-types',
            name: 'Custom Post Types Builder',
            version: '0.1.0',
            edition: 'free',
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $definitions = $services->get('platform.definitions');
        $providers = $services->get('platform.registrations.providers');
        if (!$definitions instanceof DefinitionRepositoryInterface) {
            throw new LogicException('Custom Post Types requires the shared Definition Repository.');
        }
        if (!$providers instanceof RegistrationDefinitionProviderRegistry) {
            throw new LogicException('Custom Post Types requires the shared registration provider registry.');
        }

        $provider = new CustomPostTypeRegistrationProvider($definitions);
        $providers->register($provider);
        $services->set('module.custom-post-types.registration-provider', $provider);
    }

    public function boot(ServiceRegistryInterface $services): void
    {
    }
}
