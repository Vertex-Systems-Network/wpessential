<?php

declare(strict_types=1);

namespace WPEssential\Modules\FormsWorkflows;

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
use WPEssential\Platform\Abilities\InputValidation\AbilityInputValidator;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityExposure;

final class FormsWorkflowsModule implements ModuleInterface
{
    public const SERVICE_READ = 'module.forms-workflows.read-service';

    public const ABILITY_GET = 'wpessential/forms-workflows/get';
    public const ABILITY_CATALOG = 'wpessential/forms-workflows/catalog';
    public const ABILITY_SET_ENABLED = 'wpessential/forms-workflows/set-enabled';

    public const CAPABILITY = 'manage_options';

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'forms-workflows',
            name: 'Forms & Workflows',
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
            throw new LogicException('Forms & Workflows requires the shared Definition Repository.');
        }
        if (!$abilities instanceof AbilityRegistry) {
            throw new LogicException('Forms & Workflows requires the shared Ability Registry.');
        }
        if (!$bridge instanceof WordPressAbilityBridge) {
            throw new LogicException('Forms & Workflows requires the shared WordPress Ability bridge.');
        }

        $read = new FormsWorkflowsReadService($definitions);
        $services->set(self::SERVICE_READ, $read);

        $readChannels = [
            ExecutionChannel::Internal,
            ExecutionChannel::Ui,
            ExecutionChannel::Rest,
        ];

        $this->registerAbility(
            abilities: $abilities,
            bridge: $bridge,
            descriptor: new AbilityDescriptor(
                name: self::ABILITY_GET,
                ownerSurfaceId: FormWorkflowDefinition::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $readChannels,
                inputSchema: [
                    'type' => 'object',
                    'required' => ['id'],
                    'properties' => [
                        'id' => [
                            'type' => 'string',
                            'minLength' => 1,
                            'maxLength' => 191,
                        ],
                    ],
                    'additionalProperties' => false,
                ],
                outputSchema: ['type' => ['object', 'null']],
            ),
            handler: new FormsWorkflowsReadAbilityHandler(
                $read,
                FormsWorkflowsReadAbilityHandler::GET,
            ),
            label: 'Read Forms & Workflows definition',
            description: 'Reads one canonical Forms & Workflows definition without mutating submission/entry/action/job/provider state.',
        );

        $this->registerAbility(
            abilities: $abilities,
            bridge: $bridge,
            descriptor: new AbilityDescriptor(
                name: self::ABILITY_CATALOG,
                ownerSurfaceId: FormWorkflowDefinition::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $readChannels,
                inputSchema: [
                    'type' => 'object',
                    'additionalProperties' => false,
                ],
                outputSchema: ['type' => 'array'],
            ),
            handler: new FormsWorkflowsReadAbilityHandler(
                $read,
                FormsWorkflowsReadAbilityHandler::CATALOG,
            ),
            label: 'Read Forms & Workflows catalog',
            description: 'Reads the deterministic canonical Forms & Workflows definition catalog without mutation.',
        );

        $setEnabledHandler = new FormsWorkflowsSetEnabledAbilityHandler(
            $definitions,
            new AbilityInputValidator(),
        );

        $this->registerAbility(
            abilities: $abilities,
            bridge: $bridge,
            descriptor: new AbilityDescriptor(
                name: self::ABILITY_SET_ENABLED,
                ownerSurfaceId: FormWorkflowDefinition::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: true,
                channels: [
                    ExecutionChannel::Internal,
                    ExecutionChannel::Ui,
                ],
                inputSchema: FormsWorkflowsSetEnabledAbilityHandler::inputSchema(),
                outputSchema: FormsWorkflowsSetEnabledAbilityHandler::outputSchema(),
            ),
            handler: $setEnabledHandler,
            label: 'Set Forms & Workflows enabled state',
            description: 'Toggles one existing Forms & Workflows definition between Published and Disabled using optimistic revision safety.',
            showInRest: false,
        );
    }

    public function boot(ServiceRegistryInterface $services): void
    {
    }

    private function registerAbility(
        AbilityRegistry $abilities,
        WordPressAbilityBridge $bridge,
        AbilityDescriptor $descriptor,
        AbilityHandlerInterface $handler,
        string $label,
        string $description,
        bool $showInRest = true,
    ): void {
        $abilities->register($descriptor, $handler);
        $bridge->expose(new WordPressAbilityExposure(
            internalName: $descriptor->name,
            label: $label,
            description: $description,
            showInRest: $showInRest,
        ));
    }
}
