<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Modules\Status\Abilities\StatusTransitionAbilityHandler;
use WPEssential\Modules\Status\Execution\StatusTransitionExecutor;
use WPEssential\Modules\Status\Registration\StatusNativeRegistrar;
use WPEssential\Modules\Status\Transition\Definition\PublishedStatusTransitionPolicyResolver;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\WordPress\Auth\WordPressAuthorizationServices;
use WPEssential\Platform\WordPress\Auth\WordPressPostResourceAuthorizer;

final class StatusModule implements ModuleInterface
{
    public const SERVICE_TRANSITION_EXECUTOR = 'module.status.transition-executor';
    public const ABILITY_TRANSITION = 'wpessential/status/transition';

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
        $this->requireSharedServices($services);
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
        $registrar = new StatusNativeRegistrar($definitions);
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

        // Install the native registrar before publishing execution surfaces. Immediate hook
        // installation failure is inspectable and must fail module boot without partial services.
        $registrar->register();
        if ($registrar->errors() !== []) {
            throw new LogicException('Status native registration hook could not be installed.');
        }

        $abilities->register($descriptor, $handler);
        $services->set(self::SERVICE_TRANSITION_EXECUTOR, $executor);
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
