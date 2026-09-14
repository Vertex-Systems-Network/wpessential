<?php

declare(strict_types=1);

namespace WPEssential\Modules\Settings;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;

final readonly class SettingsReadService
{
    public function __construct(private DefinitionRepositoryInterface $definitions)
    {
    }

    /** @return array<string,mixed>|null */
    public function get(string $id): ?array
    {
        $definition = $this->definitions->get($id);
        if (!$definition instanceof Definition) {
            return null;
        }

        return SettingsDefinition::view($definition);
    }

    /** @return list<array<string,mixed>> */
    public function catalog(): array
    {
        $definitions = $this->definitions->byType(SettingsDefinition::TYPE);
        usort(
            $definitions,
            static fn (Definition $left, Definition $right): int =>
                ($left->slug <=> $right->slug) ?: ($left->id <=> $right->id),
        );

        $catalog = [];
        foreach ($definitions as $definition) {
            $catalog[] = SettingsDefinition::view($definition);
        }

        return $catalog;
    }
}
