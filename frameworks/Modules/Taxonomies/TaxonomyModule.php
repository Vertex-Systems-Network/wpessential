<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

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

final class TaxonomyModule implements ModuleInterface
{
    private const CAPABILITY = 'manage_options';

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'taxonomies',
            name: 'Taxonomy Builder',
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
            throw new LogicException('Taxonomies requires the shared Definition Repository.');
        }
        if (!$providers instanceof RegistrationDefinitionProviderRegistry) {
            throw new LogicException('Taxonomies requires the shared registration provider registry.');
        }
        if (!$abilities instanceof AbilityRegistry) {
            throw new LogicException('Taxonomies requires the shared Ability Registry.');
        }
        if (!$abilityBridge instanceof WordPressAbilityBridge) {
            throw new LogicException('Taxonomies requires the shared WordPress Ability bridge.');
        }
        if (!$abilityContexts instanceof WordPressExecutionContextFactory) {
            throw new LogicException('Taxonomies requires the shared WordPress execution context factory.');
        }
        if (!$ajaxRoutes instanceof AjaxRouteRegistry) {
            throw new LogicException('Taxonomies requires the shared AJAX route registry.');
        }

        $projector = new TaxonomyDefinitionProjector();
        $provider = new TaxonomyRegistrationProvider($definitions, $projector);
        $validation = new TaxonomyValidationService($definitions, $projector);
        $providers->register($provider);
        $services->set('module.taxonomies.projector', $projector);
        $services->set('module.taxonomies.registration-provider', $provider);
        $services->set('module.taxonomies.validation', $validation);

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
            throw new LogicException('Taxonomy admin requires the shared admin, Ability, and AJAX services.');
        }

        $objectTypes = new TaxonomyObjectTypeCatalog($abilities, $contexts);
        $services->set('module.taxonomies.object-types', $objectTypes);

        $admin = new TaxonomyAdminController(
            abilities: $abilities,
            contexts: $contexts,
            ajax: $ajax,
            assets: $assets,
            objectTypes: $objectTypes,
            ajaxAction: $gateway->action(),
        );
        $services->set('module.taxonomies.admin', $admin);
        $admin->register();
    }

    private function registerAbilities(
        AbilityRegistry $abilities,
        WordPressAbilityBridge $bridge,
        DefinitionRepositoryInterface $definitions,
        TaxonomyDefinitionProjector $projector,
        TaxonomyValidationService $validation,
    ): void {
        $channels = [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest];
        $outputSchema = ['type' => 'object'];

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/taxonomy/list',
                ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: ['type' => 'object'],
                outputSchema: $outputSchema,
            ),
            new TaxonomyAbilityHandler($definitions, $projector, $validation, TaxonomyAbilityHandler::LIST),
            'List taxonomies',
            'Lists canonical WPEssential Taxonomy definitions for the current site scope.',
        );

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/taxonomy/get',
                ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
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
            new TaxonomyAbilityHandler($definitions, $projector, $validation, TaxonomyAbilityHandler::GET),
            'Get taxonomy',
            'Reads one canonical WPEssential Taxonomy definition by immutable definition id.',
        );

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/taxonomy/validate',
                ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
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
            new TaxonomyValidationAbilityHandler($validation),
            'Validate taxonomy',
            'Preflights a Taxonomy candidate without mutating canonical definitions or runtime registration.',
        );

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/taxonomy/save',
                ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
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
            new TaxonomyAbilityHandler($definitions, $projector, $validation, TaxonomyAbilityHandler::SAVE),
            'Save taxonomy',
            'Creates or revision-safely updates a canonical Taxonomy definition through Surface 2.',
        );

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/taxonomy/status',
                ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
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
            new TaxonomyAbilityHandler($definitions, $projector, $validation, TaxonomyAbilityHandler::STATUS),
            'Change taxonomy status',
            'Changes Taxonomy lifecycle status without deleting its canonical persisted definition.',
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
        $this->registerAjaxRoute($routes, $abilities, $contexts, 'taxonomy.list', 'wpessential/taxonomy/list', NonceOperation::Apply);
        $this->registerAjaxRoute($routes, $abilities, $contexts, 'taxonomy.get', 'wpessential/taxonomy/get', NonceOperation::Apply);
        $this->registerAjaxRoute($routes, $abilities, $contexts, 'taxonomy.validate', 'wpessential/taxonomy/validate', NonceOperation::Apply);
        $this->registerAjaxRoute($routes, $abilities, $contexts, 'taxonomy.save', 'wpessential/taxonomy/save', NonceOperation::Update);
        $this->registerAjaxRoute($routes, $abilities, $contexts, 'taxonomy.status', 'wpessential/taxonomy/status', NonceOperation::Update);
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
