<?php

declare(strict_types=1);

namespace WPEssential\Modules\Profiles;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;

final readonly class ProfileReadService
{
    public function __construct(private DefinitionRepositoryInterface $definitions) {}

    /** @return array<string,mixed>|null */
    public function get(string $id): ?array
    {
        $definition = $this->definitions->get($id);
        return $definition instanceof Definition ? ProfileDefinition::view($definition) : null;
    }

    /** @return list<array<string,mixed>> */
    public function catalog(): array
    {
        $definitions = $this->definitions->byType(ProfileDefinition::TYPE);
        usort($definitions, static fn (Definition $left, Definition $right): int => ($left->slug <=> $right->slug) ?: ($left->id <=> $right->id));
        $catalog = [];
        foreach ($definitions as $definition) {
            $catalog[] = ProfileDefinition::view($definition);
        }
        return $catalog;
    }
}
