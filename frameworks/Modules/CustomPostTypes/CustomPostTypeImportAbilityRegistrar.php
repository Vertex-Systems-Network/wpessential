<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomPostTypes;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;

final class CustomPostTypeImportAbilityRegistrar
{
    public const ABILITY = 'wpessential/cpt/import-definition';

    public static function register(ServiceRegistryInterface $services): void
    {
        $abilities = $services->get('platform.abilities');
        $definitions = $services->get('platform.definitions');
        $projector = $services->get('module.custom-post-types.projector');
        $validation = $services->get('module.custom-post-types.validation');

        if (!$abilities instanceof AbilityRegistry
            || !$definitions instanceof DefinitionRepositoryInterface
            || !$projector instanceof CustomPostTypeDefinitionProjector
            || !$validation instanceof CustomPostTypeValidationService
        ) {
            throw new LogicException('CPT portability requires canonical CPT owner services.');
        }

        if ($abilities->descriptor(self::ABILITY) instanceof AbilityDescriptor) {
            return;
        }

        $abilities->register(
            new AbilityDescriptor(
                name: self::ABILITY,
                ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
                capability: 'manage_options',
                mutates: true,
                channels: [ExecutionChannel::Internal, ExecutionChannel::Ui],
                inputSchema: [
                    'type' => 'object',
                    'required' => ['definition', 'strategy'],
                    'properties' => [
                        'definition' => ['type' => 'object'],
                        'strategy' => ['type' => 'string', 'enum' => ['create_only', 'update_existing']],
                        'expected_revision' => ['type' => 'integer', 'minimum' => 1],
                    ],
                ],
                outputSchema: ['type' => 'object'],
            ),
            new CustomPostTypeImportAbilityHandler($definitions, $projector, $validation),
        );
    }
}
