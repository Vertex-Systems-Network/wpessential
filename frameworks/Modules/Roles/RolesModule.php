<?php

declare(strict_types=1);

namespace WPEssential\Modules\Roles;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityExposure;

final class RolesModule implements ModuleInterface
{
    public const SERVICE_READ = 'module.roles.read-service';
    public const SERVICE_ENVIRONMENT = 'module.roles.runtime-environment';

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'roles',
            name: 'Roles & Capabilities',
            version: '0.1.0',
            edition: 'free',
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $abilities = $services->get('platform.abilities');
        $bridge = $services->get('platform.abilities.wordpress');
        $policy = $services->get('platform.abilities.policy');

        if (!$abilities instanceof AbilityRegistry) {
            throw new LogicException('Roles requires the shared Ability Registry.');
        }
        if (!$bridge instanceof WordPressAbilityBridge) {
            throw new LogicException('Roles requires the shared WordPress Ability bridge.');
        }
        if (!$policy instanceof PolicyEngine) {
            throw new LogicException('Roles requires the shared Policy Engine.');
        }

        $environment = new WordPressRoleRuntimeEnvironment();
        $read = new RolesReadService($policy, $environment);
        $services->set(self::SERVICE_ENVIRONMENT, $environment);
        $services->set(self::SERVICE_READ, $read);

        $channels = [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest];
        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: RolesReadService::ABILITY_CATALOG,
                ownerSurfaceId: RolesReadService::OWNER_SURFACE_ID,
                capability: RolesReadService::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: ['type' => 'object'],
                outputSchema: ['type' => 'object'],
            ),
            new RolesReadAbilityHandler($read, RolesReadAbilityHandler::CATALOG),
            'Read role catalog',
            'Reads the canonical site-scoped Roles & Capabilities catalog without mutating WordPress role state.',
        );
        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: RolesReadService::ABILITY_IMPACT,
                ownerSurfaceId: RolesReadService::OWNER_SURFACE_ID,
                capability: RolesReadService::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: [
                    'type' => 'object',
                    'required' => ['capability'],
                    'properties' => [
                        'capability' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 191],
                    ],
                ],
                outputSchema: ['type' => 'object'],
            ),
            new RolesReadAbilityHandler($read, RolesReadAbilityHandler::IMPACT),
            'Read capability role impact',
            'Returns explicit allow, explicit deny, and absent role-entry truth for one capability in the current site context.',
        );
    }

    public function boot(ServiceRegistryInterface $services): void {}

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
}
