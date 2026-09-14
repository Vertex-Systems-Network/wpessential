<?php

declare(strict_types=1);

namespace WPEssential\Modules\Settings;

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

final class SettingsModule implements ModuleInterface
{
    public const SERVICE_READ = 'module.settings.read-service';
    public const ABILITY_GET = 'wpessential/settings/get';
    public const ABILITY_CATALOG = 'wpessential/settings/catalog';
    public const CAPABILITY = 'manage_options';

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(id: 'settings', name: 'Settings', version: '0.1.0', edition: 'free');
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $definitions = $services->get('platform.definitions');
        $abilities = $services->get('platform.abilities');
        $bridge = $services->get('platform.abilities.wordpress');

        if (!$definitions instanceof DefinitionRepositoryInterface) {
            throw new LogicException('Settings requires the shared Definition Repository.');
        }
        if (!$abilities instanceof AbilityRegistry) {
            throw new LogicException('Settings requires the shared Ability Registry.');
        }
        if (!$bridge instanceof WordPressAbilityBridge) {
            throw new LogicException('Settings requires the shared WordPress Ability bridge.');
        }

        $read = new SettingsReadService($definitions);
        $services->set(self::SERVICE_READ, $read);
        $channels = [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest];

        $this->registerAbility($abilities, $bridge, new AbilityDescriptor(
            name: self::ABILITY_GET,
            ownerSurfaceId: SettingsDefinition::OWNER_SURFACE_ID,
            capability: self::CAPABILITY,
            mutates: false,
            channels: $channels,
            inputSchema: [
                'type' => 'object',
                'required' => ['id'],
                'properties' => ['id' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 191]],
                'additionalProperties' => false,
            ],
            outputSchema: ['type' => ['object', 'null']],
        ), new SettingsReadAbilityHandler($read, SettingsReadAbilityHandler::GET), 'Read Settings definition', 'Reads one canonical Settings definition without mutating settings/options/secrets state.');

        $this->registerAbility($abilities, $bridge, new AbilityDescriptor(
            name: self::ABILITY_CATALOG,
            ownerSurfaceId: SettingsDefinition::OWNER_SURFACE_ID,
            capability: self::CAPABILITY,
            mutates: false,
            channels: $channels,
            inputSchema: ['type' => 'object', 'additionalProperties' => false],
            outputSchema: ['type' => 'array'],
        ), new SettingsReadAbilityHandler($read, SettingsReadAbilityHandler::CATALOG), 'Read Settings catalog', 'Reads the deterministic canonical Settings definition catalog without mutation.');
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
