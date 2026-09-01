<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

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
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityExposure;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AbilityAjaxHandler;
use WPEssential\Platform\WordPress\Ajax\AjaxRoute;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Security\NonceOperation;

final class RelationsModule implements ModuleInterface
{
    private const CAPABILITY = 'manage_options';

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'relations',
            name: 'Relations',
            version: '0.1.0',
            edition: 'pro',
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $definitions = $services->get('platform.definitions');
        $abilities = $services->get('platform.abilities');
        $bridge = $services->get('platform.abilities.wordpress');
        $contexts = $services->get('platform.abilities.contexts');
        $ajaxRoutes = $services->get('platform.ajax.routes');
        if (!$definitions instanceof DefinitionRepositoryInterface
            || !$abilities instanceof AbilityRegistry
            || !$bridge instanceof WordPressAbilityBridge
            || !$contexts instanceof WordPressExecutionContextFactory
            || !$ajaxRoutes instanceof AjaxRouteRegistry
        ) {
            throw new LogicException('Relations requires shared Definition, Ability, execution-context, and AJAX services.');
        }

        $normalizer = new RelationDefinitionNormalizer();
        $validation = new RelationDefinitionValidationService(
            $normalizer,
            new RelationEndpointSupport(),
        );
        $services->set('module.relations.definition-normalizer', $normalizer);
        $services->set('module.relations.definition-validation', $validation);

        $handlers = [
            'list-definitions' => new RelationDefinitionAbilityHandler(
                $definitions,
                $normalizer,
                $validation,
                RelationDefinitionAbilityHandler::LIST,
            ),
            'get-definition' => new RelationDefinitionAbilityHandler(
                $definitions,
                $normalizer,
                $validation,
                RelationDefinitionAbilityHandler::GET,
            ),
            'validate-definition' => new RelationDefinitionAbilityHandler(
                $definitions,
                $normalizer,
                $validation,
                RelationDefinitionAbilityHandler::VALIDATE,
            ),
            'save-definition' => new RelationDefinitionAbilityHandler(
                $definitions,
                $normalizer,
                $validation,
                RelationDefinitionAbilityHandler::SAVE,
            ),
            'status-definition' => new RelationDefinitionAbilityHandler(
                $definitions,
                $normalizer,
                $validation,
                RelationDefinitionAbilityHandler::STATUS,
            ),
        ];

        foreach ($handlers as $action => $handler) {
            $this->registerAbility($abilities, $bridge, $handler, $action);
            $ajaxRoutes->register(new AjaxRoute(
                type: 'relations.' . str_replace('-', '.', $action),
                handler: new AbilityAjaxHandler($abilities, 'wpessential/relations/' . $action, $contexts),
                operation: in_array($action, ['save-definition', 'status-definition'], true)
                    ? NonceOperation::Update
                    : NonceOperation::Apply,
            ));
        }
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        // Definition lifecycle has no WordPress runtime hook until edge storage is certified.
    }

    private function registerAbility(
        AbilityRegistry $abilities,
        WordPressAbilityBridge $bridge,
        AbilityHandlerInterface $handler,
        string $action,
    ): void {
        $descriptor = new AbilityDescriptor(
            name: 'wpessential/relations/' . $action,
            ownerSurfaceId: RelationDefinitionNormalizer::OWNER_SURFACE_ID,
            capability: self::CAPABILITY,
            mutates: in_array($action, ['save-definition', 'status-definition'], true),
            channels: [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest],
            inputSchema: ['type' => 'object'],
            outputSchema: ['type' => 'object'],
        );
        $abilities->register($descriptor, $handler);
        $bridge->expose(new WordPressAbilityExposure(
            internalName: $descriptor->name,
            label: 'Relations: ' . str_replace('-', ' ', $action),
            description: 'Surface 4 Relation definition lifecycle operation.',
            showInRest: true,
        ));
    }
}
