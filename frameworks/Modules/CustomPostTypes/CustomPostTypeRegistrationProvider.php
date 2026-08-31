<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomPostTypes;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinitionProviderInterface;

final class CustomPostTypeRegistrationProvider implements RegistrationDefinitionProviderInterface
{
    public function __construct(
        private readonly DefinitionRepositoryInterface $definitions,
        private readonly CustomPostTypeDefinitionProjector $projector = new CustomPostTypeDefinitionProjector(),
    ) {}

    public function id(): string
    {
        return 'custom-post-types';
    }

    public function definitions(): iterable
    {
        foreach ($this->definitions->byType(CustomPostTypeDefinitionProjector::DEFINITION_TYPE) as $definition) {
            if ($definition->status !== DefinitionStatus::Published) {
                continue;
            }
            yield $this->projector->project($definition);
        }
    }
}
