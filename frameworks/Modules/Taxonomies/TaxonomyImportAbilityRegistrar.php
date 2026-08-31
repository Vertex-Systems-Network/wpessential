<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;

final class TaxonomyImportAbilityRegistrar
{
    public const ABILITY = 'wpessential/taxonomy/import-definition';

    public static function register(ServiceRegistryInterface $services): void
    {
        $abilities = $services->get('platform.abilities');
        $definitions = $services->get('platform.definitions');
        $projector = $services->get('module.taxonomies.projector');
        $validation = $services->get('module.taxonomies.validation');

        if (!$abilities instanceof AbilityRegistry
            || !$definitions instanceof DefinitionRepositoryInterface
            || !$projector instanceof TaxonomyDefinitionProjector
            || !$validation instanceof TaxonomyValidationService
        ) {
            throw new LogicException('Taxonomy portability requires canonical Taxonomy owner services.');
        }

        if ($abilities->descriptor(self::ABILITY) instanceof AbilityDescriptor) {
            return;
        }

        $abilities->register(
            new AbilityDescriptor(
                name: self::ABILITY,
                ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
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
            new TaxonomyImportAbilityHandler($definitions, $projector, $validation),
        );
    }
}
