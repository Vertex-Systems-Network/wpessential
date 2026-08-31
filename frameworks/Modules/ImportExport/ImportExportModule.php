<?php

declare(strict_types=1);

namespace WPEssential\Modules\ImportExport;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeImportAbilityRegistrar;
use WPEssential\Modules\Taxonomies\TaxonomyImportAbilityRegistrar;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityExposure;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AbilityAjaxHandler;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;
use WPEssential\Platform\WordPress\Ajax\AjaxRoute;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Ajax\WordPressAjaxGateway;
use WPEssential\Platform\WordPress\Security\NonceOperation;

final class ImportExportModule implements ModuleInterface
{
    public const OWNER_SURFACE_ID = 26;
    private const CAPABILITY = 'manage_options';

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'import-export',
            name: 'Import / Export',
            version: '0.1.0',
            edition: 'free',
            dependencies: ['custom-post-types', 'taxonomies'],
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $abilities = $services->get('platform.abilities');
        $bridge = $services->get('platform.abilities.wordpress');
        $contexts = $services->get('platform.abilities.contexts');
        $routes = $services->get('platform.ajax.routes');
        if (!$abilities instanceof AbilityRegistry
            || !$bridge instanceof WordPressAbilityBridge
            || !$contexts instanceof WordPressExecutionContextFactory
            || !$routes instanceof AjaxRouteRegistry
        ) {
            throw new LogicException('Import / Export requires the shared Ability and AJAX foundation.');
        }

        CustomPostTypeImportAbilityRegistrar::register($services);
        TaxonomyImportAbilityRegistrar::register($services);

        $codec = new DefinitionPackageCodec();
        $packages = new ConfigurationPackageService($abilities, $codec);
        $services->set('module.import-export.package-codec', $codec);
        $services->set('module.import-export.configuration-packages', $packages);

        $this->registerAbilities($abilities, $bridge, $packages);
        $this->registerAjaxRoutes($routes, $abilities, $contexts);
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        $ajax = $services->get('platform.ajax.dispatcher');
        $gateway = $services->get('platform.ajax.gateway');
        $assets = $services->get('platform.admin.assets');
        if (!$ajax instanceof AjaxDispatcher
            || !$gateway instanceof WordPressAjaxGateway
            || !$assets instanceof AdminAssetManifest
        ) {
            throw new LogicException('Configuration Packages admin requires shared AJAX and admin assets.');
        }

        $admin = new ImportExportAdminController(
            ajax: $ajax,
            assets: $assets,
            ajaxAction: $gateway->action(),
        );
        $services->set('module.import-export.admin', $admin);
        $admin->register();
    }

    private function registerAbilities(
        AbilityRegistry $abilities,
        WordPressAbilityBridge $bridge,
        ConfigurationPackageService $packages,
    ): void {
        $channels = [ExecutionChannel::Internal, ExecutionChannel::Ui];
        $outputSchema = ['type' => 'object'];

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/import-export/config-export',
                ownerSurfaceId: self::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: [
                    'type' => 'object',
                    'properties' => [
                        'include_cpt' => ['type' => 'boolean'],
                        'include_taxonomy' => ['type' => 'boolean'],
                    ],
                ],
                outputSchema: $outputSchema,
            ),
            new ConfigurationPackageAbilityHandler($packages, ConfigurationPackageAbilityHandler::EXPORT),
            'Export WPE configuration package',
            'Generates a bounded portable JSON package from canonical CPT and Taxonomy definitions.',
        );

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/import-export/config-preflight',
                ownerSurfaceId: self::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: [
                    'type' => 'object',
                    'required' => ['package_json', 'strategy'],
                    'properties' => [
                        'package_json' => ['type' => 'string'],
                        'strategy' => ['type' => 'string', 'enum' => ['create_only', 'update_existing']],
                    ],
                ],
                outputSchema: $outputSchema,
            ),
            new ConfigurationPackageAbilityHandler($packages, ConfigurationPackageAbilityHandler::PREFLIGHT),
            'Preflight WPE configuration package',
            'Verifies package integrity and owner-level conflicts without mutating definitions.',
        );

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/import-export/config-import',
                ownerSurfaceId: self::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: true,
                channels: $channels,
                inputSchema: [
                    'type' => 'object',
                    'required' => ['package_json', 'strategy', 'expected_package_checksum'],
                    'properties' => [
                        'package_json' => ['type' => 'string'],
                        'strategy' => ['type' => 'string', 'enum' => ['create_only', 'update_existing']],
                        'expected_package_checksum' => ['type' => 'string'],
                    ],
                ],
                outputSchema: $outputSchema,
            ),
            new ConfigurationPackageAbilityHandler($packages, ConfigurationPackageAbilityHandler::IMPORT),
            'Import WPE configuration package',
            'Applies a successfully preflighted definition package through each canonical owner Ability.',
        );
    }

    private function registerAbility(
        AbilityRegistry $abilities,
        WordPressAbilityBridge $bridge,
        AbilityDescriptor $descriptor,
        AbilityHandlerInterface $handler,
        string $label,
        string $description,
    ): void {
        $abilities->register($descriptor, $handler);
        $bridge->expose(new WordPressAbilityExposure(
            internalName: $descriptor->name,
            label: $label,
            description: $description,
            showInRest: false,
        ));
    }

    private function registerAjaxRoutes(
        AjaxRouteRegistry $routes,
        AbilityRegistry $abilities,
        WordPressExecutionContextFactory $contexts,
    ): void {
        $this->registerAjaxRoute(
            $routes,
            $abilities,
            $contexts,
            'import-export.export',
            'wpessential/import-export/config-export',
            NonceOperation::Apply,
        );
        $this->registerAjaxRoute(
            $routes,
            $abilities,
            $contexts,
            'import-export.preflight',
            'wpessential/import-export/config-preflight',
            NonceOperation::Apply,
        );
        $this->registerAjaxRoute(
            $routes,
            $abilities,
            $contexts,
            'import-export.import',
            'wpessential/import-export/config-import',
            NonceOperation::Update,
        );
    }

    private function registerAjaxRoute(
        AjaxRouteRegistry $routes,
        AbilityRegistry $abilities,
        WordPressExecutionContextFactory $contexts,
        string $type,
        string $abilityName,
        NonceOperation $operation,
    ): void {
        $routes->register(new AjaxRoute(
            type: $type,
            handler: new AbilityAjaxHandler($abilities, $abilityName, $contexts),
            operation: $operation,
            capability: self::CAPABILITY,
            allowGuests: false,
            requiresNonce: true,
        ));
    }
}
