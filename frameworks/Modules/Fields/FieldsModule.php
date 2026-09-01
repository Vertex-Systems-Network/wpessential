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
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityExposure;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AbilityAjaxHandler;
use WPEssential\Platform\WordPress\Ajax\AjaxRoute;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Security\NonceOperation;

final class FieldsModule implements ModuleInterface
{
    private const CAPABILITY = 'manage_options';

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
        $postMetaCompiler = new PostMetaRegistrationCompiler($values, $persistence);
        $postMetaRegistrar = new WordPressPostMetaRegistrar();
        $postMetaValues = new PostMetaValueStore($postMetaCompiler, $values, $persistence);
        $valueTargets = new FieldValueTargetResolver($definitions, $groups);
        $valueAuthorization = new WordPressPostResourceAuthorizer();

        $services->set('module.custom-fields.types', $types);
        $services->set('module.custom-fields.presets', $presets);
        $services->set('module.custom-fields.catalog', $catalog);
        $services->set('module.custom-fields.field-normalizer', $fields);
        $services->set('module.custom-fields.value-normalizer', $values);
        $services->set('module.custom-fields.group-normalizer', $groups);
        $services->set('module.custom-fields.group-validation', $validation);
        $services->set('module.custom-fields.storage.post-meta.compiler', $postMetaCompiler);
        $services->set('module.custom-fields.storage.post-meta.registrar', $postMetaRegistrar);
        $services->set('module.custom-fields.storage.post-meta.values', $postMetaValues);
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
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        // Automatic Field Group target registration, admin rendering, and Pro activation remain separate bounded slices.
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
            'group_id' => ['type' => 'string', 'pattern' => '^[0-9a-f-]{36}$'],
            'field_uuid' => ['type' => 'string', 'pattern' => '^[0-9a-f-]{36}$'],
            'post_id' => ['type' => 'integer', 'minimum' => 1],
        ];
        $required = ['group_id', 'field_uuid', 'post_id'];
        if ($write) {
            $properties['value'] = [
                'type' => ['string', 'number', 'integer', 'boolean', 'array', 'null'],
                'items' => ['type' => ['string', 'number', 'integer', 'boolean', 'null']],
            ];
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
                'group_id' => ['type' => 'string'],
                'group_revision' => ['type' => 'integer', 'minimum' => 1],
                'field_uuid' => ['type' => 'string'],
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
}
