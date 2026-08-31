<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinitionProviderInterface;

final class TaxonomyRegistrationProvider implements RegistrationDefinitionProviderInterface
{
    public function __construct(
        private readonly DefinitionRepositoryInterface $definitions,
        private readonly TaxonomyDefinitionProjector $projector = new TaxonomyDefinitionProjector(),
    ) {}

    public function id(): string
    {
        return 'taxonomies';
    }

    public function definitions(): iterable
    {
        foreach ($this->definitions->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE) as $definition) {
            if ($definition->status !== DefinitionStatus::Published) {
                continue;
            }
            yield $this->projector->project($definition);
        }
    }
}
