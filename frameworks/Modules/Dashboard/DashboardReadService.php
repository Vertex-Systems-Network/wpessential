<?php

declare(strict_types=1);

namespace WPEssential\Modules\Dashboard;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;

final readonly class DashboardReadService
{
    public function __construct(private DefinitionRepositoryInterface $definitions) {}

    /** @return array<string,mixed>|null */
    public function get(string $id): ?array
    {
        $definition = $this->definitions->get($id);
        return $definition instanceof Definition ? DashboardDefinition::view($definition) : null;
    }

    /** @return list<array<string,mixed>> */
    public function catalog(): array
    {
        $definitions = $this->definitions->byType(DashboardDefinition::TYPE);
        usort($definitions, static fn (Definition $left, Definition $right): int => ($left->slug <=> $right->slug) ?: ($left->id <=> $right->id));
        return array_map(DashboardDefinition::view(...), $definitions);
    }
}
