<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

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
use WPEssential\Platform\WordPress\Security\NonceOperation;

final class FieldsModule implements ModuleInterface
{
    private const CAPABILITY = 'manage_options';
    private const UUID_PATTERN = '^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$';
    private const FIELD_KEY_PATTERN = '^[a-z0-9][a-z0-9_-]{0,63}$';

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'custom-fields',
            name: 'Custom Fields Builder',
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
            throw new LogicException('Custom Fields requires shared Definition, Ability, execution-context, and AJAX services.');
        }

        $types = new FieldTypeRegistry();
        $presets = new FieldPresetRegistry($types);
        $catalog = new FieldCatalogService($types, $presets);
        $fields = new FieldDefinitionNormalizer($types, $presets);
        $values = new FieldValueNormalizer();
        $persistence = new FieldValuePersistenceGuard();
        $groups = new FieldGroupDefinitionNormalizer($fields);
        $validation = new FieldGroupValidationService($definitions, $groups);
        $groupStorage = new FieldGroupRuntimeStorageProjection();
        $groupPostTypes = new FieldGroupPostTypeTargetCompiler();
        $postMetaCompiler = new PostMetaRegistrationCompiler($values, $persistence);
        $postMetaRegistrar = new WordPressPostMetaRegistrar();
        $postMetaBinder = new FieldGroupPostMetaBinder(
            $groups,
            $groupStorage,
            $groupPostTypes,
            $postMetaCompiler,
            $postMetaRegistrar,
        );
        $postMetaValues = new PostMetaValueStore($postMetaCompiler, $values, $persistence);
        $postMetaMigrations = new FieldStorageKeyMigrationService(
            $definitions,
            $groups,
            $groupStorage,
            $groupPostTypes,
            $postMetaCompiler,
            $postMetaRegistrar,
            $postMetaValues,
        );
        $valueTargets = new FieldValueTargetResolver($definitions, $groups);
        $valueAuthorization = new WordPressPostResourceAuthorizer();

        $services->set('module.custom-fields.types', $types);
        $services->set('module.custom-fields.presets', $presets);
        $services->set('module.custom-fields.catalog', $catalog);
        $services->set('module.custom-fields.field-normalizer', $fields);
        $services->set('module.custom-fields.value-normalizer', $values);
        $services->set('module.custom-fields.group-normalizer', $groups);
        $services->set('module.custom-fields.group-validation', $validation);
        $services->set('module.custom-fields.runtime.storage-projection', $groupStorage);
        $services->set('module.custom-fields.runtime.post-type-targets', $groupPostTypes);
        $services->set('module.custom-fields.storage.post-meta.compiler', $postMetaCompiler);
        $services->set('module.custom-fields.storage.post-meta.registrar', $postMetaRegistrar);
        $services->set('module.custom-fields.storage.post-meta.binder', $postMetaBinder);
        $services->set('module.custom-fields.storage.post-meta.values', $postMetaValues);
        $services->set('module.custom-fields.storage.post-meta.key-migrations', $postMetaMigrations);
        $services->set('module.custom-fields.values.targets', $valueTargets);
        $services->set('module.custom-fields.values.authorization', $valueAuthorization);

        $handlers = [
            'catalog' => new FieldCatalogAbilityHandler($catalog),
            'list-groups' => new FieldGroupAbilityHandler($definitions, $groups, $validation, FieldGroupAbilityHandler::LIST),
            'get-group' => new FieldGroupAbilityHandler($definitions, $groups, $validation, FieldGroupAbilityHandler::GET),
            'validate-group' => new FieldGroupValidationAbilityHandler($validation),
            'save-group' => new FieldGroupAbilityHandler($definitions, $groups, $validation, FieldGroupAbilityHandler::SAVE),
            'status-group' => new FieldGroupAbilityHandler($definitions, $groups, $validation, FieldGroupAbilityHandler::STATUS),
        ];

        foreach ($handlers as $action => $handler) {
            $this->registerAbility($abilities, $bridge, $handler, $action);
            $ajaxRoutes->register(new AjaxRoute(
                type: 'fields.' . str_replace('-', '.', $action),
                handler: new AbilityAjaxHandler($abilities, 'wpessential/fields/' . $action, $contexts),
                operation: in_array($action, ['save-group', 'status-group'], true) ? NonceOperation::Update : NonceOperation::Apply,
            ));
        }

        $valueHandlers = [
            'read-value' => new FieldValueAbilityHandler(
                $valueTargets,
                $postMetaValues,
                $valueAuthorization,
                FieldValueAbilityHandler::READ,
            ),
            'write-value' => new FieldValueAbilityHandler(
                $valueTargets,
                $postMetaValues,
                $valueAuthorization,
                FieldValueAbilityHandler::WRITE,
            ),
        ];
        foreach ($valueHandlers as $action => $handler) {
            $mutates = $action === 'write-value';
            $descriptor = new AbilityDescriptor(
                name: 'wpessential/fields/' . $action,
                ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
                capability: 'read',
                mutates: $mutates,
                channels: [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest],
                inputSchema: $this->valueInputSchema($mutates),
                outputSchema: $this->valueOutputSchema(),
            );
            $abilities->register($descriptor, $handler);
            $bridge->expose(new WordPressAbilityExposure(
                internalName: $descriptor->name,
                label: 'Custom Fields: ' . str_replace('-', ' ', $action),
                description: 'Surface 3 Field value operation through certified target resolution and post resource authorization.',
                showInRest: true,
            ));
            $ajaxRoutes->register(new AjaxRoute(
                type: 'fields.' . str_replace('-', '.', $action),
                handler: new AbilityAjaxHandler($abilities, $descriptor->name, $contexts),
                operation: $mutates ? NonceOperation::Update : NonceOperation::Apply,
            ));
        }

        $migrationDescriptor = new AbilityDescriptor(
            name: 'wpessential/fields/migrate-storage-key',
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            capability: self::CAPABILITY,
            mutates: true,
            channels: [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest],
            inputSchema: $this->storageKeyMigrationInputSchema(),
            outputSchema: $this->storageKeyMigrationOutputSchema(),
        );
        $abilities->register($migrationDescriptor, new FieldStorageKeyMigrationAbilityHandler($postMetaMigrations));
        $bridge->expose(new WordPressAbilityExposure(
            internalName: $migrationDescriptor->name,
            label: 'Custom Fields: migrate storage key',
            description: 'Explicit Surface 3 native post-meta storage-key migration with verified rollback.',
            showInRest: true,
        ));
        $ajaxRoutes->register(new AjaxRoute(
            type: 'fields.migrate.storage.key',
            handler: new AbilityAjaxHandler($abilities, $migrationDescriptor->name, $contexts),
            operation: NonceOperation::Update,
        ));
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
            throw new LogicException('Custom Fields admin requires the shared admin, Ability, and AJAX services.');
        }

        $admin = new FieldAdminController(
            abilities: $abilities,
            contexts: $contexts,
            ajax: $ajax,
            assets: $assets,
            catalogProjector: new FieldAdminCatalogProjector(),
            ajaxAction: $gateway->action(),
        );
        $services->set('module.custom-fields.admin', $admin);
        $admin->register();

        // Automatic Field Group target registration remains a separate bounded slice.
    }

    private function registerAbility(
        AbilityRegistry $abilities,
        WordPressAbilityBridge $bridge,
        AbilityHandlerInterface $handler,
        string $action,
    ): void {
        $mutates = in_array($action, ['save-group', 'status-group'], true);
        $descriptor = new AbilityDescriptor(
            name: 'wpessential/fields/' . $action,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            capability: self::CAPABILITY,
            mutates: $mutates,
            channels: [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest],
            inputSchema: ['type' => 'object'],
            outputSchema: ['type' => 'object'],
        );
        $abilities->register($descriptor, $handler);
        $bridge->expose(new WordPressAbilityExposure(
            internalName: $descriptor->name,
            label: 'Custom Fields: ' . str_replace('-', ' ', $action),
            description: 'Surface 3 Custom Fields / Field Groups operation.',
            showInRest: true,
        ));
    }

    /** @return array<string,mixed> */
    private function valueInputSchema(bool $write): array
    {
        $properties = [
            'group_id' => ['type' => 'string', 'pattern' => self::UUID_PATTERN],
            'field_uuid' => ['type' => 'string', 'pattern' => self::UUID_PATTERN],
            'post_id' => ['type' => 'integer', 'minimum' => 1],
        ];
        $required = ['group_id', 'field_uuid', 'post_id'];
        if ($write) {
            $properties['expected_group_revision'] = ['type' => 'integer', 'minimum' => 1];
            $properties['value'] = [
                'type' => ['string', 'number', 'integer', 'boolean', 'array', 'null'],
                'items' => ['type' => ['string', 'number', 'integer', 'boolean', 'null']],
            ];
            $required[] = 'expected_group_revision';
            $required[] = 'value';
        }

        return [
            'type' => 'object',
            'required' => $required,
            'properties' => $properties,
            'additionalProperties' => false,
        ];
    }

    /** @return array<string,mixed> */
    private function valueOutputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => [
                'group_id', 'group_revision', 'field_uuid', 'field_key', 'post_id', 'post_type', 'status', 'changed', 'value',
            ],
            'properties' => [
                'group_id' => ['type' => 'string', 'pattern' => self::UUID_PATTERN],
                'group_revision' => ['type' => 'integer', 'minimum' => 1],
                'field_uuid' => ['type' => 'string', 'pattern' => self::UUID_PATTERN],
                'field_key' => ['type' => 'string'],
                'post_id' => ['type' => 'integer', 'minimum' => 1],
                'post_type' => ['type' => 'string'],
                'status' => ['type' => 'string'],
                'changed' => ['type' => 'boolean'],
                'value' => [
                    'type' => ['string', 'number', 'integer', 'boolean', 'array', 'null'],
                    'items' => ['type' => ['string', 'number', 'integer', 'boolean', 'null']],
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    /** @return array<string,mixed> */
    private function storageKeyMigrationInputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['group_id', 'expected_group_revision', 'field_uuid', 'destination_key'],
            'properties' => [
                'group_id' => ['type' => 'string', 'pattern' => self::UUID_PATTERN],
                'expected_group_revision' => ['type' => 'integer', 'minimum' => 1],
                'field_uuid' => ['type' => 'string', 'pattern' => self::UUID_PATTERN],
                'destination_key' => ['type' => 'string', 'pattern' => self::FIELD_KEY_PATTERN],
            ],
            'additionalProperties' => false,
        ];
    }

    /** @return array<string,mixed> */
    private function storageKeyMigrationOutputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => [
                'group_id',
                'group_revision',
                'field_uuid',
                'source_key',
                'destination_key',
                'post_types',
                'migrated_objects',
                'changed',
                'definition',
            ],
            'properties' => [
                'group_id' => ['type' => 'string', 'pattern' => self::UUID_PATTERN],
                'group_revision' => ['type' => 'integer', 'minimum' => 1],
                'field_uuid' => ['type' => 'string', 'pattern' => self::UUID_PATTERN],
                'source_key' => ['type' => 'string', 'pattern' => self::FIELD_KEY_PATTERN],
                'destination_key' => ['type' => 'string', 'pattern' => self::FIELD_KEY_PATTERN],
                'post_types' => ['type' => 'array', 'items' => ['type' => 'string']],
                'migrated_objects' => ['type' => 'integer', 'minimum' => 0],
                'changed' => ['type' => 'boolean'],
                'definition' => ['type' => 'object'],
            ],
            'additionalProperties' => false,
        ];
    }
}
