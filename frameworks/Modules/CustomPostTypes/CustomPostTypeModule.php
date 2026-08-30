<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomPostTypes;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
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
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinitionProviderRegistry;
use WPEssential\Platform\WordPress\Security\NonceOperation;

final class CustomPostTypeModule implements ModuleInterface
{
    private const CAPABILITY = 'manage_options';

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
        $abilities = $services->get('platform.abilities');
        $abilityBridge = $services->get('platform.abilities.wordpress');
        $abilityContexts = $services->get('platform.abilities.contexts');
        $ajaxRoutes = $services->get('platform.ajax.routes');
        if (!$definitions instanceof DefinitionRepositoryInterface) {
            throw new LogicException('Custom Post Types requires the shared Definition Repository.');
        }
        if (!$providers instanceof RegistrationDefinitionProviderRegistry) {
            throw new LogicException('Custom Post Types requires the shared registration provider registry.');
        }
        if (!$abilities instanceof AbilityRegistry) {
            throw new LogicException('Custom Post Types requires the shared Ability Registry.');
        }
        if (!$abilityBridge instanceof WordPressAbilityBridge) {
            throw new LogicException('Custom Post Types requires the shared WordPress Ability bridge.');
        }
        if (!$abilityContexts instanceof WordPressExecutionContextFactory) {
            throw new LogicException('Custom Post Types requires the shared WordPress execution context factory.');
        }
        if (!$ajaxRoutes instanceof AjaxRouteRegistry) {
            throw new LogicException('Custom Post Types requires the shared AJAX route registry.');
        }

        $projector = new CustomPostTypeDefinitionProjector();
        $provider = new CustomPostTypeRegistrationProvider($definitions, $projector);
        $validation = new CustomPostTypeValidationService($definitions, $projector);
        $providers->register($provider);
        $services->set('module.custom-post-types.projector', $projector);
        $services->set('module.custom-post-types.registration-provider', $provider);
        $services->set('module.custom-post-types.validation', $validation);

        $this->registerAbilities($abilities, $abilityBridge, $definitions, $projector, $validation);
        $this->registerAjaxRoutes($ajaxRoutes, $abilities, $abilityContexts);
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        $abilities = $services->get('platform.abilities');
        $contexts = $services->get('platform.abilities.contexts');
        $ajax = $services->get('platform.ajax.dispatcher');
        $gateway = $services->get('platform.ajax.gateway');
        $assets = $services->get('platform.admin.assets');

        if (!$abilities instanceof AbilityRegistry
            || !$contexts instanceof WordPressExecutionContextFactory
            || !$ajax instanceof AjaxDispatcher
            || !$gateway instanceof WordPressAjaxGateway
            || !$assets instanceof AdminAssetManifest
        ) {
            throw new LogicException('Custom Post Types admin requires the shared admin, Ability, and AJAX services.');
        }

        $admin = new CustomPostTypeAdminController(
            abilities: $abilities,
            contexts: $contexts,
            ajax: $ajax,
            assets: $assets,
            ajaxAction: $gateway->action(),
        );
        $services->set('module.custom-post-types.admin', $admin);
        $admin->register();
    }

    private function registerAbilities(
        AbilityRegistry $abilities,
        WordPressAbilityBridge $bridge,
        DefinitionRepositoryInterface $definitions,
        CustomPostTypeDefinitionProjector $projector,
        CustomPostTypeValidationService $validation,
    ): void {
        $channels = [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest];
        $outputSchema = ['type' => 'object'];

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/cpt/list',
                ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: ['type' => 'object'],
                outputSchema: $outputSchema,
            ),
            new CustomPostTypeAbilityHandler(
                $definitions,
                $projector,
                $validation,
                CustomPostTypeAbilityHandler::LIST,
            ),
            'List custom post types',
            'Lists canonical WPEssential Custom Post Type definitions for the current site scope.',
        );

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/cpt/get',
                ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: [
                    'type' => 'object',
                    'required' => ['id'],
                    'properties' => ['id' => ['type' => 'string']],
                ],
                outputSchema: $outputSchema,
            ),
            new CustomPostTypeAbilityHandler(
                $definitions,
                $projector,
                $validation,
                CustomPostTypeAbilityHandler::GET,
            ),
            'Get custom post type',
            'Reads one canonical WPEssential Custom Post Type definition by immutable definition id.',
        );

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/cpt/validate',
                ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: [
                    'type' => 'object',
                    'required' => ['payload'],
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'payload' => ['type' => 'object'],
                    ],
                ],
                outputSchema: $outputSchema,
            ),
            new CustomPostTypeValidationAbilityHandler($validation),
            'Validate custom post type',
            'Preflights a Custom Post Type candidate without mutating canonical definitions or runtime registration.',
        );

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/cpt/save',
                ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: true,
                channels: $channels,
                inputSchema: [
                    'type' => 'object',
                    'required' => ['payload'],
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'expected_revision' => ['type' => 'integer', 'minimum' => 1],
                        'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'disabled', 'archived']],
                        'payload' => ['type' => 'object'],
                    ],
                ],
                outputSchema: $outputSchema,
            ),
            new CustomPostTypeAbilityHandler(
                $definitions,
                $projector,
                $validation,
                CustomPostTypeAbilityHandler::SAVE,
            ),
            'Save custom post type',
            'Creates or revision-safely updates a canonical Custom Post Type definition through Surface 1.',
        );

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/cpt/status',
                ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: true,
                channels: $channels,
                inputSchema: [
                    'type' => 'object',
                    'required' => ['id', 'expected_revision', 'status'],
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'expected_revision' => ['type' => 'integer', 'minimum' => 1],
                        'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'disabled', 'archived']],
                    ],
                ],
                outputSchema: $outputSchema,
            ),
            new CustomPostTypeAbilityHandler(
                $definitions,
                $projector,
                $validation,
                CustomPostTypeAbilityHandler::STATUS,
            ),
            'Change custom post type status',
            'Changes CPT lifecycle status without deleting its canonical persisted definition.',
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
            showInRest: true,
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
            'cpt.list',
            'wpessential/cpt/list',
            NonceOperation::Apply,
        );
        $this->registerAjaxRoute(
            $routes,
            $abilities,
            $contexts,
            'cpt.get',
            'wpessential/cpt/get',
            NonceOperation::Apply,
        );
        $this->registerAjaxRoute(
            $routes,
            $abilities,
            $contexts,
            'cpt.validate',
            'wpessential/cpt/validate',
            NonceOperation::Apply,
        );
        $this->registerAjaxRoute(
            $routes,
            $abilities,
            $contexts,
            'cpt.save',
            'wpessential/cpt/save',
            NonceOperation::Update,
        );
        $this->registerAjaxRoute(
            $routes,
            $abilities,
            $contexts,
            'cpt.status',
            'wpessential/cpt/status',
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
