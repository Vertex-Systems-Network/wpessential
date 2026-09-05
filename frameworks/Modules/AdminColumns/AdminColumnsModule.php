<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\AdminColumnsSourceCatalogInterface;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\FieldValueReadConsumerInterface;
use WPEssential\Contracts\FieldValueWriteConsumerInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AbilityAjaxHandler;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;
use WPEssential\Platform\WordPress\Ajax\AjaxRoute;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Ajax\WordPressAjaxGateway;
use WPEssential\Platform\WordPress\Security\NonceOperation;

final class AdminColumnsModule implements ModuleInterface
{
    public const SERVICE_NORMALIZER = 'module.admin-columns.view-normalizer';
    public const SERVICE_VIEWS = 'module.admin-columns.views';
    public const SERVICE_READ_ADAPTER = 'module.admin-columns.read-adapter';
    public const SERVICE_FIELD_WRITE_ADAPTER = 'module.admin-columns.fields-write-adapter';
    public const SERVICE_ADMIN = 'module.admin-columns.admin';

    private const QUERY_READ_SERVICE = 'module.query.read-consumer';
    private const FIELD_VALUE_READ_SERVICE = 'module.custom-fields.values.read-consumer';
    private const FIELD_VALUE_WRITE_SERVICE = 'module.custom-fields.values.write-consumer';
    private const FIELD_SOURCE_CATALOG_SERVICE = 'module.custom-fields.admin-columns.sources';
    private const DATA_SOURCE_SERVICE = 'platform.data-sources';
    private const ADMIN_ASSET_SERVICE = 'platform.admin.assets';
    private const ABILITY_SERVICE = 'platform.abilities';
    private const ABILITY_CONTEXT_SERVICE = 'platform.abilities.contexts';
    private const AJAX_ROUTE_SERVICE = 'platform.ajax.routes';
    private const AJAX_DISPATCHER_SERVICE = 'platform.ajax.dispatcher';
    private const AJAX_GATEWAY_SERVICE = 'platform.ajax.gateway';
    private const CAPABILITY = 'manage_options';
    private const UUID_PATTERN = '^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$';

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'admin-columns',
            name: 'Admin Columns',
            version: '0.1.0',
            edition: 'pro',
            dependencies: ['query'],
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $definitions = $services->get('platform.definitions');
        $query = $services->get(self::QUERY_READ_SERVICE);
        $abilities = $services->get(self::ABILITY_SERVICE);
        $contexts = $services->get(self::ABILITY_CONTEXT_SERVICE);
        $ajaxRoutes = $services->get(self::AJAX_ROUTE_SERVICE);
        if (!$definitions instanceof DefinitionRepositoryInterface
            || !$query instanceof QueryReadConsumerInterface
            || !$abilities instanceof AbilityRegistry
            || !$contexts instanceof WordPressExecutionContextFactory
            || !$ajaxRoutes instanceof AjaxRouteRegistry
        ) {
            throw new LogicException('Admin Columns requires shared Definition, Query, Ability, execution-context, and AJAX route services.');
        }

        $fields = null;
        if ($services->has(self::FIELD_VALUE_READ_SERVICE)) {
            $candidate = $services->get(self::FIELD_VALUE_READ_SERVICE);
            if (!$candidate instanceof FieldValueReadConsumerInterface) {
                throw new LogicException('Admin Columns optional Fields value read service must implement the certified contract.');
            }
            $fields = $candidate;
        }

        $fieldWrites = null;
        if ($services->has(self::FIELD_VALUE_WRITE_SERVICE)) {
            $candidate = $services->get(self::FIELD_VALUE_WRITE_SERVICE);
            if (!$candidate instanceof FieldValueWriteConsumerInterface) {
                throw new LogicException('Admin Columns optional Fields value write service must implement the certified contract.');
            }
            $fieldWrites = $candidate;
        }

        foreach ([
            self::SERVICE_NORMALIZER,
            self::SERVICE_VIEWS,
            self::SERVICE_READ_ADAPTER,
            self::SERVICE_FIELD_WRITE_ADAPTER,
        ] as $serviceId) {
            if ($services->has($serviceId)) {
                throw new LogicException(sprintf('Admin Columns service "%s" is already registered.', $serviceId));
            }
        }

        $normalizer = new AdminColumnsViewDefinitionNormalizer();
        $views = new AdminColumnsViewDefinitionService($definitions, $normalizer);
        $readAdapter = new AdminColumnsReadAdapter($views, $query, $fields);
        $services->set(self::SERVICE_NORMALIZER, $normalizer);
        $services->set(self::SERVICE_VIEWS, $views);
        $services->set(self::SERVICE_READ_ADAPTER, $readAdapter);
        if ($fieldWrites instanceof FieldValueWriteConsumerInterface) {
            $services->set(
                self::SERVICE_FIELD_WRITE_ADAPTER,
                new AdminColumnsFieldValueWriteAdapter($views, $query, $fieldWrites),
            );
        }

        $actions = [
            'list-views' => [AdminColumnsViewAbilityHandler::LIST, false],
            'get-view' => [AdminColumnsViewAbilityHandler::GET, false],
            'save-view' => [AdminColumnsViewAbilityHandler::SAVE, true],
            'status-view' => [AdminColumnsViewAbilityHandler::STATUS, true],
        ];
        foreach ($actions as $action => [$handlerAction, $mutates]) {
            $descriptor = new AbilityDescriptor(
                name: 'wpessential/admin-columns/' . $action,
                ownerSurfaceId: AdminColumnsViewDefinitionNormalizer::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: $mutates,
                channels: [ExecutionChannel::Internal, ExecutionChannel::Ui],
                inputSchema: $this->abilityInputSchema($handlerAction),
                outputSchema: ['type' => 'object'],
            );
            $abilities->register($descriptor, new AdminColumnsViewAbilityHandler($views, $handlerAction));
            $ajaxRoutes->register(new AjaxRoute(
                type: 'admin-columns.' . str_replace('-', '.', $action),
                handler: new AbilityAjaxHandler($abilities, $descriptor->name, $contexts),
                operation: $mutates ? NonceOperation::Update : NonceOperation::Apply,
            ));
        }

        $readDescriptor = new AbilityDescriptor(
            name: 'wpessential/admin-columns/read-rows',
            ownerSurfaceId: AdminColumnsViewDefinitionNormalizer::OWNER_SURFACE_ID,
            capability: self::CAPABILITY,
            mutates: false,
            channels: [ExecutionChannel::Internal, ExecutionChannel::Ui],
            inputSchema: $this->readAbilityInputSchema(),
            outputSchema: ['type' => 'object'],
        );
        $abilities->register($readDescriptor, new AdminColumnsReadAbilityHandler($readAdapter));
        $ajaxRoutes->register(new AjaxRoute(
            type: 'admin-columns.read.rows',
            handler: new AbilityAjaxHandler($abilities, $readDescriptor->name, $contexts),
            operation: NonceOperation::Apply,
        ));
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        $assets = $services->get(self::ADMIN_ASSET_SERVICE);
        $dataSources = $services->get(self::DATA_SOURCE_SERVICE);
        if (!$assets instanceof AdminAssetManifest || !$dataSources instanceof DataSourceRegistryInterface) {
            throw new LogicException('Admin Columns admin requires canonical shared admin assets and Data Source Registry.');
        }
        if ($services->has(self::SERVICE_ADMIN)) {
            throw new LogicException('Admin Columns admin service is already registered.');
        }

        $ownerSources = null;
        if ($services->has(self::FIELD_SOURCE_CATALOG_SERVICE)) {
            $candidate = $services->get(self::FIELD_SOURCE_CATALOG_SERVICE);
            if (!$candidate instanceof AdminColumnsSourceCatalogInterface) {
                throw new LogicException('Admin Columns optional owner source catalog must implement the certified discovery contract.');
            }
            $ownerSources = $candidate;
        }

        $ajax = $services->get(self::AJAX_DISPATCHER_SERVICE);
        $gateway = $services->get(self::AJAX_GATEWAY_SERVICE);
        $admin = new AdminColumnsAdminController(
            new AdminColumnsAdminBootstrapProjector(
                $dataSources,
                ownerSources: $ownerSources,
            ),
            $assets,
            $ajax instanceof AjaxDispatcher ? $ajax : null,
            $gateway instanceof WordPressAjaxGateway ? $gateway->action() : null,
        );
        $services->set(self::SERVICE_ADMIN, $admin);
        $admin->register();
        // View-definition and bounded row-read AJAX are registered through the
        // shared Ability platform. The page remains read-only if the shared
        // dispatcher/gateway is unavailable. Optional Fields composition uses
        // certified owner read/write seams internally; no row-mutation Ability,
        // AJAX/UI exposure, export, or REST surface is introduced here.
    }

    /** @return array<string,mixed> */
    private function abilityInputSchema(string $action): array
    {
        $properties = [];
        $required = [];

        if ($action === AdminColumnsViewAbilityHandler::GET
            || $action === AdminColumnsViewAbilityHandler::STATUS
        ) {
            $properties['id'] = ['type' => 'string', 'pattern' => self::UUID_PATTERN];
            $required[] = 'id';
        }
        if ($action === AdminColumnsViewAbilityHandler::SAVE) {
            $properties['id'] = ['type' => ['string', 'null'], 'pattern' => self::UUID_PATTERN];
            $properties['payload'] = ['type' => 'object'];
            $properties['expected_revision'] = ['type' => ['integer', 'null'], 'minimum' => 1];
            $properties['status'] = ['type' => 'string', 'enum' => ['draft', 'published', 'disabled', 'archived']];
            $required[] = 'payload';
        }
        if ($action === AdminColumnsViewAbilityHandler::STATUS) {
            $properties['expected_revision'] = ['type' => 'integer', 'minimum' => 1];
            $properties['status'] = ['type' => 'string', 'enum' => ['draft', 'published', 'disabled', 'archived']];
            $required[] = 'expected_revision';
            $required[] = 'status';
        }

        return [
            'type' => 'object',
            'required' => $required,
            'properties' => $properties,
            'additionalProperties' => false,
        ];
    }

    /** @return array<string,mixed> */
    private function readAbilityInputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['view_id'],
            'properties' => [
                'view_id' => ['type' => 'string', 'pattern' => self::UUID_PATTERN],
                'filters' => ['type' => 'array', 'maxItems' => 16],
                'search' => ['type' => 'string', 'maxLength' => 200],
                'order_by' => ['type' => 'array', 'maxItems' => 4],
                'page_size' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
                'offset' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 10000],
            ],
            'additionalProperties' => false,
        ];
    }
}
