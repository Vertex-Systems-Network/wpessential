<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Components\ComponentBlueprintDescriptor;

final class DashboardWidgetComponentBlueprintCatalog
{
    public const REVISION = 1;

    /**
     * @var array<string,array{
     *   id:string,
     *   component_type:string,
     *   binding_schema:array<string,string>
     * }>
     */
    private const DEFINITIONS = [
        'rich_text' => [
            'id' => '31000000-0000-4000-8000-000000000001',
            'component_type' => 'dashboard-widgets.rich-text',
            'binding_schema' => ['content' => 'string'],
        ],
        'kpi' => [
            'id' => '31000000-0000-4000-8000-000000000002',
            'component_type' => 'dashboard-widgets.kpi',
            'binding_schema' => ['label' => 'string', 'value' => 'string'],
        ],
        'chart' => [
            'id' => '31000000-0000-4000-8000-000000000003',
            'component_type' => 'dashboard-widgets.chart',
            'binding_schema' => ['labels' => 'string_list', 'values' => 'int_list'],
        ],
        'quick_links' => [
            'id' => '31000000-0000-4000-8000-000000000004',
            'component_type' => 'dashboard-widgets.quick-links',
            'binding_schema' => ['labels' => 'string_list', 'urls' => 'string_list'],
        ],
        'announcement' => [
            'id' => '31000000-0000-4000-8000-000000000005',
            'component_type' => 'dashboard-widgets.announcement',
            'binding_schema' => ['title' => 'string', 'text' => 'string'],
        ],
        'support_onboarding' => [
            'id' => '31000000-0000-4000-8000-000000000006',
            'component_type' => 'dashboard-widgets.support-onboarding',
            'binding_schema' => ['title' => 'string', 'text' => 'string'],
        ],
        'icon_link' => [
            'id' => '31000000-0000-4000-8000-000000000007',
            'component_type' => 'dashboard-widgets.icon-link',
            'binding_schema' => ['icon' => 'string', 'label' => 'string', 'url' => 'string'],
        ],
    ];

    /** @return list<ComponentBlueprintDescriptor> */
    public function all(): array
    {
        $blueprints = [];
        foreach (array_keys(self::DEFINITIONS) as $contentType) {
            $blueprint = $this->forContentType($contentType);
            if ($blueprint !== null) {
                $blueprints[] = $blueprint;
            }
        }
        return $blueprints;
    }

    public function forContentType(string $contentType): ?ComponentBlueprintDescriptor
    {
        $definition = self::DEFINITIONS[$contentType] ?? null;
        if ($definition === null) {
            return null;
        }

        return new ComponentBlueprintDescriptor(
            id: $definition['id'],
            revision: self::REVISION,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            componentType: $definition['component_type'],
            dependencyIds: [],
            assetHandles: [],
            bindingSchema: $definition['binding_schema'],
        );
    }

    public function contentTypeForBlueprint(string $blueprintId, int $revision): ?string
    {
        if ($revision !== self::REVISION) {
            return null;
        }

        foreach (self::DEFINITIONS as $contentType => $definition) {
            if ($definition['id'] === $blueprintId) {
                return $contentType;
            }
        }
        return null;
    }

    /** @return list<string> */
    public function componentTypes(): array
    {
        return array_values(array_map(
            static fn (array $definition): string => $definition['component_type'],
            self::DEFINITIONS,
        ));
    }
}
