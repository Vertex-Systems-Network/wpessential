<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

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

final class DashboardWidgetsModule implements ModuleInterface
{
    public const SERVICE_READ = 'module.dashboard-widgets.read-service';
    public const SERVICE_REGISTRATION_COMPILER = 'module.dashboard-widgets.registration-compiler';
    public const SERVICE_VISIBILITY_COMPILER = 'module.dashboard-widgets.visibility-compiler';
    public const ABILITY_GET = 'wpessential/dashboard-widgets/get';
    public const ABILITY_CATALOG = 'wpessential/dashboard-widgets/catalog';
    public const CAPABILITY = 'manage_options';

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'dashboard-widgets',
            name: 'Dashboard Widgets',
            version: '0.1.0',
            edition: 'pro',
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $definitions = $services->get('platform.definitions');
        $abilities = $services->get('platform.abilities');
        $bridge = $services->get('platform.abilities.wordpress');

        if (!$definitions instanceof DefinitionRepositoryInterface) {
            throw new LogicException('Dashboard Widgets requires the shared Definition Repository.');
        }
        if (!$abilities instanceof AbilityRegistry) {
            throw new LogicException('Dashboard Widgets requires the shared Ability Registry.');
        }
        if (!$bridge instanceof WordPressAbilityBridge) {
            throw new LogicException('Dashboard Widgets requires the shared WordPress Ability bridge.');
        }

        $read = new DashboardWidgetsReadService($definitions);
        $visibilityCompiler = new DashboardWidgetVisibilityCompiler();
        $services->set(self::SERVICE_READ, $read);
        $services->set(self::SERVICE_VISIBILITY_COMPILER, $visibilityCompiler);
        $services->set(self::SERVICE_REGISTRATION_COMPILER, new DashboardWidgetRegistrationCompiler($visibilityCompiler));

        $channels = [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest];
        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: self::ABILITY_GET,
                ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
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
            ),
            new DashboardWidgetsReadAbilityHandler($read, DashboardWidgetsReadAbilityHandler::GET),
            'Read Dashboard Widget definition',
            'Reads one canonical Dashboard Widget definition without mutating dashboard widget or authorization state.',
        );
        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: self::ABILITY_CATALOG,
                ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: ['type' => 'object', 'additionalProperties' => false],
                outputSchema: ['type' => 'array'],
            ),
            new DashboardWidgetsReadAbilityHandler($read, DashboardWidgetsReadAbilityHandler::CATALOG),
            'Read Dashboard Widgets catalog',
            'Reads the deterministic canonical Dashboard Widgets definition catalog without mutation.',
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
