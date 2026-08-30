<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomPostTypes;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityExposure;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinitionProviderRegistry;

final class CustomPostTypeModule implements ModuleInterface
{
    private const CAPABILITY = 'manage_options';

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'custom-post-types',
            name: 'Custom Post Types Builder',
            version: '0.1.0',
            edition: 'free',
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $definitions = $services->get('platform.definitions');
        $providers = $services->get('platform.registrations.providers');
        $abilities = $services->get('platform.abilities');
        $abilityBridge = $services->get('platform.abilities.wordpress');
        if (!$definitions instanceof DefinitionRepositoryInterface) {
            throw new LogicException('Custom Post Types requires the shared Definition Repository.');
        }
        if (!$providers instanceof RegistrationDefinitionProviderRegistry) {
            throw new LogicException('Custom Post Types requires the shared registration provider registry.');
        }
        if (!$abilities instanceof AbilityRegistry) {
            throw new LogicException('Custom Post Types requires the shared Ability Registry.');
        }
        if (!$abilityBridge instanceof WordPressAbilityBridge) {
            throw new LogicException('Custom Post Types requires the shared WordPress Ability bridge.');
        }

        $projector = new CustomPostTypeDefinitionProjector();
        $provider = new CustomPostTypeRegistrationProvider($definitions, $projector);
        $providers->register($provider);
        $services->set('module.custom-post-types.projector', $projector);
        $services->set('module.custom-post-types.registration-provider', $provider);

        $this->registerAbilities($abilities, $abilityBridge, $definitions, $projector);
    }

    public function boot(ServiceRegistryInterface $services): void
    {
    }

    private function registerAbilities(
        AbilityRegistry $abilities,
        WordPressAbilityBridge $bridge,
        DefinitionRepositoryInterface $definitions,
        CustomPostTypeDefinitionProjector $projector,
    ): void {
        $channels = [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest];
        $outputSchema = ['type' => 'object'];

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/cpt/list',
                ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: ['type' => 'object'],
                outputSchema: $outputSchema,
            ),
            new CustomPostTypeAbilityHandler(
                $definitions,
                $projector,
                CustomPostTypeAbilityHandler::LIST,
            ),
            'List custom post types',
            'Lists canonical WPEssential Custom Post Type definitions for the current site scope.',
        );

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/cpt/get',
                ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: false,
                channels: $channels,
                inputSchema: [
                    'type' => 'object',
                    'required' => ['id'],
                    'properties' => ['id' => ['type' => 'string']],
                ],
                outputSchema: $outputSchema,
            ),
            new CustomPostTypeAbilityHandler(
                $definitions,
                $projector,
                CustomPostTypeAbilityHandler::GET,
            ),
            'Get custom post type',
            'Reads one canonical WPEssential Custom Post Type definition by immutable definition id.',
        );

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/cpt/save',
                ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: true,
                channels: $channels,
                inputSchema: [
                    'type' => 'object',
                    'required' => ['payload'],
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'expected_revision' => ['type' => 'integer', 'minimum' => 1],
                        'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'disabled', 'archived']],
                        'payload' => ['type' => 'object'],
                    ],
                ],
                outputSchema: $outputSchema,
            ),
            new CustomPostTypeAbilityHandler(
                $definitions,
                $projector,
                CustomPostTypeAbilityHandler::SAVE,
            ),
            'Save custom post type',
            'Creates or revision-safely updates a canonical Custom Post Type definition through Surface 1.',
        );

        $this->registerAbility(
            $abilities,
            $bridge,
            new AbilityDescriptor(
                name: 'wpessential/cpt/status',
                ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
                capability: self::CAPABILITY,
                mutates: true,
                channels: $channels,
                inputSchema: [
                    'type' => 'object',
                    'required' => ['id', 'expected_revision', 'status'],
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'expected_revision' => ['type' => 'integer', 'minimum' => 1],
                        'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'disabled', 'archived']],
                    ],
                ],
                outputSchema: $outputSchema,
            ),
            new CustomPostTypeAbilityHandler(
                $definitions,
                $projector,
                CustomPostTypeAbilityHandler::STATUS,
            ),
            'Change custom post type status',
            'Changes CPT lifecycle status without deleting its canonical persisted definition.',
        );
    }

    private function registerAbility(
        AbilityRegistry $abilities,
        WordPressAbilityBridge $bridge,
        AbilityDescriptor $descriptor,
        CustomPostTypeAbilityHandler $handler,
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
