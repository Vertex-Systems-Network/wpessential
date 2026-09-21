<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;

final readonly class DashboardWidgetsReadService
{
    public function __construct(private DefinitionRepositoryInterface $definitions)
    {
    }

    /**
     * @return array{
     *   id:string,
     *   slug:string,
     *   type:string,
     *   schema_version:int,
     *   owner_surface_id:int,
     *   status:string,
     *   revision:int,
     *   dependencies:list<string>,
     *   payload:array<string,mixed>
     * }|null
     */
    public function get(string $id): ?array
    {
        $definition = $this->definitions->get($id);
        if (!$definition instanceof Definition) {
            return null;
        }

        return DashboardWidgetDefinition::view($definition);
    }

    /**
     * @return list<array{
     *   id:string,
     *   slug:string,
     *   type:string,
     *   schema_version:int,
     *   owner_surface_id:int,
     *   status:string,
     *   revision:int,
     *   dependencies:list<string>,
     *   payload:array<string,mixed>
     * }>
     */
    public function catalog(): array
    {
        $definitions = $this->definitions->byType(DashboardWidgetDefinition::TYPE);
        usort(
            $definitions,
            static fn (Definition $left, Definition $right): int =>
                ($left->slug <=> $right->slug) ?: ($left->id <=> $right->id),
        );

        $catalog = [];
        foreach ($definitions as $definition) {
            $catalog[] = DashboardWidgetDefinition::view($definition);
        }

        return $catalog;
    }
}
