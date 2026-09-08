<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use LogicException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Modules\Status\Abilities\StatusTransitionAbilityHandler;
use WPEssential\Modules\Status\Admin\StatusAdminAuthoringHandler;
use WPEssential\Modules\Status\Admin\StatusAdminFallbackRenderer;
use WPEssential\Modules\Status\Definition\StatusDefinitionCompiler;
use WPEssential\Modules\Status\Execution\StatusTransitionExecutor;
use WPEssential\Modules\Status\Registration\StatusNativeRegistrar;
use WPEssential\Modules\Status\Transition\Definition\PublishedStatusTransitionPolicyResolver;
use WPEssential\Modules\Status\Transition\Definition\StatusTransitionPolicyDefinitionCompiler;
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
use WPEssential\Platform\WordPress\Auth\WordPressAuthorizationServices;
use WPEssential\Platform\WordPress\Auth\WordPressPostResourceAuthorizer;
use WPEssential\Platform\WordPress\Security\NonceOperation;

final class StatusModule implements ModuleInterface
{
    public const SERVICE_TRANSITION_EXECUTOR = 'module.status.transition-executor';
    public const SERVICE_ADMIN_FALLBACK = 'module.status.admin.fallback-renderer';
    public const ABILITY_TRANSITION = 'wpessential/status/transition';

    private const ADMIN_CAPABILITY = 'manage_options';
    private const UUID_PATTERN = '^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$';

    /** @var Closure(DefinitionRepositoryInterface):StatusNativeRegistrar */
    private Closure $registrarFactory;

    /** @param null|callable(DefinitionRepositoryInterface):StatusNativeRegistrar $registrarFactory */
    public function __construct(?callable $registrarFactory = null)
    {
        $this->registrarFactory = $registrarFactory !== null
            ? Closure::fromCallable($registrarFactory)
            : static fn (DefinitionRepositoryInterface $definitions): StatusNativeRegistrar => new StatusNativeRegistrar($definitions);
    }

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'status',
            name: 'Status Manager',
            version: '0.1.0',
            edition: 'pro',
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $this->assertOutputAvailable($services);
        [$definitions, $abilities] = $this->requireSharedServices($services);
        $this->registerAdminAbilities($services, $definitions, $abilities);
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        $this->assertOutputAvailable($services);
        [$definitions, $abilities, $capabilities, $resources] = $this->requireSharedServices($services);

        if ($abilities->descriptor(self::ABILITY_TRANSITION) !== null) {
            throw new LogicException('Status transition Ability is already registered.');
        }

        $policy = (new PublishedStatusTransitionPolicyResolver($definitions))->resolve();
        $executor = new StatusTransitionExecutor(
            definitions: $definitions,
            policy: $policy,
            resources: $resources,
            capabilities: $capabilities,
        );
        $registrar = ($this->registrarFactory)($definitions);
        if (!$registrar instanceof StatusNativeRegistrar) {
            throw new LogicException('Status registrar factory must return the canonical native registrar.');
        }
        $handler = new StatusTransitionAbilityHandler($executor);
        $descriptor = new AbilityDescriptor(
            name: self::ABILITY_TRANSITION,
            ownerSurfaceId: 5,
            capability: 'edit_posts',
            mutates: true,
            channels: [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest],
            inputSchema: [
                'type' => 'object',
                'additionalProperties' => false,
                'required' => ['post_id', 'expected_current_status', 'target_status'],
                'properties' => [
                    'post_id' => ['type' => 'integer', 'minimum' => 1],
                    'expected_current_status' => ['type' => 'string', 'maxLength' => 20],
                    'target_status' => ['type' => 'string', 'maxLength' => 20],
                    'reason' => ['type' => ['string', 'null'], 'maxLength' => 500],
                    'programmatic' => ['type' => 'boolean'],
                ],
            ],
            outputSchema: [
                'type' => 'object',
                'additionalProperties' => false,
                'required' => ['post_id', 'from_status', 'to_status', 'post_type', 'programmatic'],
            ],
        );

        $registrar->register();
        if ($registrar->errors() !== []) {
            throw new LogicException('Status native registration hook could not be installed.');
        }

        $abilities->register($descriptor, $handler);
        $services->set(self::SERVICE_TRANSITION_EXECUTOR, $executor);
    }

    private function registerAdminAbilities(
        ServiceRegistryInterface $services,
        DefinitionRepositoryInterface $definitions,
        AbilityRegistry $abilities,
    ): void {
        foreach (['platform.abilities.wordpress', 'platform.abilities.contexts', 'platform.ajax.routes'] as $serviceId) {
            if (!$services->has($serviceId)) {
                throw new LogicException(sprintf('Status admin requires canonical shared service "%s".', $serviceId));
            }
        }
        $bridge = $services->get('platform.abilities.wordpress');
        $contexts = $services->get('platform.abilities.contexts');
        $ajaxRoutes = $services->get('platform.ajax.routes');
        if (!$bridge instanceof WordPressAbilityBridge
            || !$contexts instanceof WordPressExecutionContextFactory
            || !$ajaxRoutes instanceof AjaxRouteRegistry
        ) {
            throw new LogicException('Status admin requires canonical WordPress Ability/AJAX infrastructure.');
        }

        $statusCompiler = new StatusDefinitionCompiler();
        $policyCompiler = new StatusTransitionPolicyDefinitionCompiler();
        $actions = [
            StatusAdminAuthoringHandler::LIST,
            StatusAdminAuthoringHandler::GET,
            StatusAdminAuthoringHandler::SAVE,
            StatusAdminAuthoringHandler::STATUS,
        ];
        foreach ($actions as $action) {
            $name = 'wpessential/status/admin/' . $action;
            if ($abilities->descriptor($name) !== null) {
                throw new LogicException('Status admin Ability is already registered.');
            }
            $mutates = in_array($action, [StatusAdminAuthoringHandler::SAVE, StatusAdminAuthoringHandler::STATUS], true);
            $descriptor = new AbilityDescriptor(
                name: $name,
                ownerSurfaceId: 5,
                capability: self::ADMIN_CAPABILITY,
                mutates: $mutates,
                channels: [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest],
                inputSchema: $this->adminInputSchema($action),
                outputSchema: ['type' => 'object'],
            );
            $abilities->register(
                $descriptor,
                new StatusAdminAuthoringHandler($definitions, $statusCompiler, $policyCompiler, $action),
            );
            $bridge->expose(new WordPressAbilityExposure(
                internalName: $name,
                label: 'Status Manager: ' . $action,
                description: 'Canonical Surface 5 Status definition authoring operation.',
                showInRest: true,
            ));
            $ajaxRoutes->register(new AjaxRoute(
                type: 'status.admin.' . $action,
                handler: new AbilityAjaxHandler($abilities, $name, $contexts),
                operation: $mutates ? NonceOperation::Update : NonceOperation::Apply,
            ));
        }

        if ($services->has(self::SERVICE_ADMIN_FALLBACK)) {
            throw new LogicException('Status admin fallback renderer is already registered.');
        }
        $services->set(self::SERVICE_ADMIN_FALLBACK, new StatusAdminFallbackRenderer());
    }

    /** @return array<string,mixed> */
    private function adminInputSchema(string $action): array
    {
        $uuid = ['type' => 'string', 'pattern' => self::UUID_PATTERN];
        return match ($action) {
            StatusAdminAuthoringHandler::LIST => [
                'type' => 'object',
                'additionalProperties' => false,
                'properties' => [
                    'definition_type' => ['type' => 'string', 'enum' => ['status', 'status-transition-policy']],
                ],
            ],
            StatusAdminAuthoringHandler::GET => [
                'type' => 'object',
                'additionalProperties' => false,
                'required' => ['id'],
                'properties' => ['id' => $uuid],
            ],
            StatusAdminAuthoringHandler::SAVE => [
                'type' => 'object',
                'additionalProperties' => false,
                'required' => ['definition_type', 'payload'],
                'properties' => [
                    'definition_type' => ['type' => 'string', 'enum' => ['status', 'status-transition-policy']],
                    'id' => $uuid,
                    'expected_revision' => ['type' => 'integer', 'minimum' => 1],
                    'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'disabled', 'archived']],
                    'slug' => ['type' => 'string', 'pattern' => '^[a-z0-9][a-z0-9_-]{0,63}$'],
                    'payload' => ['type' => 'object'],
                ],
            ],
            StatusAdminAuthoringHandler::STATUS => [
                'type' => 'object',
                'additionalProperties' => false,
                'required' => ['id', 'expected_revision', 'status'],
                'properties' => [
                    'id' => $uuid,
                    'expected_revision' => ['type' => 'integer', 'minimum' => 1],
                    'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'disabled', 'archived']],
                ],
            ],
            default => throw new LogicException('Unsupported Status admin schema action.'),
        };
    }

    private function assertOutputAvailable(ServiceRegistryInterface $services): void
    {
        if ($services->has(self::SERVICE_TRANSITION_EXECUTOR)) {
            throw new LogicException('Status transition-executor service is already registered.');
        }
    }

    /**
     * @return array{
     *   DefinitionRepositoryInterface,
     *   AbilityRegistry,
     *   CapabilityCheckerInterface,
     *   WordPressPostResourceAuthorizer
     * }
     */
    private function requireSharedServices(ServiceRegistryInterface $services): array
    {
        foreach (
            [
                'platform.definitions',
                'platform.abilities',
                WordPressAuthorizationServices::CAPABILITY_CHECKER,
                WordPressAuthorizationServices::POST_RESOURCES,
            ] as $serviceId
        ) {
            if (!$services->has($serviceId)) {
                throw new LogicException(sprintf('Status requires canonical shared service "%s".', $serviceId));
            }
        }

        $definitions = $services->get('platform.definitions');
        $abilities = $services->get('platform.abilities');
        $capabilities = $services->get(WordPressAuthorizationServices::CAPABILITY_CHECKER);
        $resources = $services->get(WordPressAuthorizationServices::POST_RESOURCES);

        if (!$definitions instanceof DefinitionRepositoryInterface) {
            throw new LogicException('Status requires the canonical Definition repository contract.');
        }
        if (!$abilities instanceof AbilityRegistry) {
            throw new LogicException('Status requires the canonical Ability registry.');
        }
        if (!$capabilities instanceof CapabilityCheckerInterface) {
            throw new LogicException('Status requires the canonical WordPress capability checker contract.');
        }
        if (!$resources instanceof WordPressPostResourceAuthorizer) {
            throw new LogicException('Status requires the canonical WordPress post-resource authorizer.');
        }

        return [$definitions, $abilities, $capabilities, $resources];
    }
}
