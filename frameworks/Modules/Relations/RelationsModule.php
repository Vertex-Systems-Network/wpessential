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
use WPEssential\Modules\Relations\Migrations\AllowNonUniqueRelationEdgeTuplesMigration;
use WPEssential\Modules\Relations\Migrations\CreateRelationEdgeTablesMigration;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Database\Migrations\MigrationCoordinator;
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
        $endpointSupport = new RelationEndpointSupport();
        $validation = new RelationDefinitionValidationService($normalizer, $endpointSupport);
        $portability = new RelationPortabilityService($definitions, $normalizer, $validation);
        $services->set('module.relations.definition-normalizer', $normalizer);
        $services->set('module.relations.endpoint-support', $endpointSupport);
        $services->set('module.relations.definition-validation', $validation);
        $services->set('module.relations.portability', $portability);
        $this->registerEdgePersistence($services);

        $gateway = null;
        if ($services->has('module.relations.edge-gateway')) {
            $candidateGateway = $services->get('module.relations.edge-gateway');
            if (!$candidateGateway instanceof WpdbRelationEdgeGateway) {
                throw new LogicException('Relations edge persistence service must use the canonical edge gateway.');
            }
            $gateway = $candidateGateway;
        }

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
            'export-definitions' => new RelationPortabilityAbilityHandler(
                $portability,
                RelationPortabilityAbilityHandler::EXPORT,
            ),
            'import-definitions' => new RelationPortabilityAbilityHandler(
                $portability,
                RelationPortabilityAbilityHandler::IMPORT,
            ),
            'diagnostics' => new RelationDiagnosticsAbilityHandler(
                $definitions,
                $normalizer,
                $validation,
                $endpointSupport,
                $gateway,
            ),
        ];

        if ($gateway instanceof WpdbRelationEdgeGateway) {
            $mutations = new RelationEdgeMutationService(
                $definitions,
                $normalizer,
                $gateway,
                new RelationEndpointObjectAuthorizer(),
                supportsNonUniqueTuples: true,
            );
            $services->set('module.relations.edge-mutations', $mutations);
            $handlers['connect'] = new RelationEdgeMutationAbilityHandler(
                $mutations,
                RelationEdgeMutationAbilityHandler::CONNECT,
            );
            $handlers['disconnect'] = new RelationEdgeMutationAbilityHandler(
                $mutations,
                RelationEdgeMutationAbilityHandler::DISCONNECT,
            );
        }

        foreach ($handlers as $action => $handler) {
            $this->registerAbility($abilities, $bridge, $handler, $action);
            $ajaxRoutes->register(new AjaxRoute(
                type: 'relations.' . str_replace('-', '.', $action),
                handler: new AbilityAjaxHandler($abilities, 'wpessential/relations/' . $action, $contexts),
                operation: $this->isMutationAction($action) ? NonceOperation::Update : NonceOperation::Apply,
            ));
        }
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        if ($services->has('platform.database.migrations')) {
            $migrations = $services->get('platform.database.migrations');
            if (!$migrations instanceof MigrationCoordinator) {
                throw new LogicException('Relations migration service must use the shared MigrationCoordinator.');
            }
            $migrations->runPending();
        }

        $abilities = $services->get('platform.abilities');
        $contexts = $services->get('platform.abilities.contexts');
        if (!$abilities instanceof AbilityRegistry || !$contexts instanceof WordPressExecutionContextFactory) {
            throw new LogicException('Relations admin requires shared Ability and execution-context services.');
        }

        $admin = new RelationAdminController($abilities, $contexts);
        $services->set('module.relations.admin', $admin);
        $admin->register();
    }

    private function registerEdgePersistence(ServiceRegistryInterface $services): void
    {
        $hasDatabase = $services->has('platform.database');
        $hasMigrations = $services->has('platform.database.migrations');
        if (!$hasDatabase && !$hasMigrations) {
            return;
        }
        if (!$hasDatabase || !$hasMigrations) {
            throw new LogicException('Relations native persistence requires database and migration services together.');
        }

        $database = $services->get('platform.database');
        $migrations = $services->get('platform.database.migrations');
        if (!$database instanceof DatabaseAdapterInterface || !$migrations instanceof MigrationCoordinator) {
            throw new LogicException('Relations native persistence requires canonical database and migration services.');
        }

        $migrations->register(new CreateRelationEdgeTablesMigration($database));
        $migrations->register(new AllowNonUniqueRelationEdgeTuplesMigration($database));

        $networkId = function_exists('get_current_network_id') ? max(1, (int) get_current_network_id()) : 1;
        $siteId = function_exists('get_current_blog_id') ? max(1, (int) get_current_blog_id()) : 1;
        $services->set(
            'module.relations.edge-gateway',
            new WpdbRelationEdgeGateway($database, RelationEdgeScope::site($networkId, $siteId)),
        );
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
            mutates: $this->isMutationAction($action),
            channels: [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest],
            inputSchema: $this->inputSchema($action),
            outputSchema: $this->outputSchema($action),
        );
        $abilities->register($descriptor, $handler);
        $bridge->expose(new WordPressAbilityExposure(
            internalName: $descriptor->name,
            label: 'Relations: ' . str_replace('-', ' ', $action),
            description: in_array($action, ['connect', 'disconnect'], true)
                ? 'Surface 4 transactional Relation edge mutation operation.'
                : 'Surface 4 Relation definition lifecycle, portability, or diagnostics operation.',
            showInRest: true,
        ));
    }

    private function isMutationAction(string $action): bool
    {
        return in_array(
            $action,
            ['save-definition', 'status-definition', 'import-definitions', 'connect', 'disconnect'],
            true,
        );
    }

    /** @return array<string,mixed> */
    private function inputSchema(string $action): array
    {
        if (!in_array($action, ['connect', 'disconnect'], true)) {
            return ['type' => 'object'];
        }

        return [
            'type' => 'object',
            'required' => ['relation_definition_id', 'from_object_id', 'to_object_id'],
            'properties' => [
                'relation_definition_id' => [
                    'type' => 'string',
                    'pattern' => '^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$',
                ],
                'from_object_id' => ['type' => 'integer', 'minimum' => 1],
                'to_object_id' => ['type' => 'integer', 'minimum' => 1],
            ],
            'additionalProperties' => false,
        ];
    }

    /** @return array<string,mixed> */
    private function outputSchema(string $action): array
    {
        if (!in_array($action, ['connect', 'disconnect'], true)) {
            return ['type' => 'object'];
        }

        return [
            'type' => 'object',
            'required' => ['mutation'],
            'properties' => [
                'mutation' => [
                    'type' => 'object',
                    'required' => [
                        'changed',
                        'relation_definition_id',
                        'edge_id',
                        'from_object_id',
                        'to_object_id',
                        'revision',
                    ],
                    'properties' => [
                        'changed' => ['type' => 'boolean'],
                        'relation_definition_id' => ['type' => 'string'],
                        'edge_id' => ['type' => ['string', 'null']],
                        'from_object_id' => ['type' => 'integer'],
                        'to_object_id' => ['type' => 'integer'],
                        'revision' => ['type' => 'integer', 'minimum' => 0],
                    ],
                    'additionalProperties' => false,
                ],
            ],
            'additionalProperties' => false,
        ];
    }
}
