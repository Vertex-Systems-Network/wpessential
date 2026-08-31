<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class FieldCatalogService
{
    public function __construct(
        private FieldTypeRegistry $types,
        private FieldPresetRegistry $presets,
    ) {}

    /**
     * @return array{
     *   types:list<array<string,mixed>>,
     *   presets:list<array<string,mixed>>,
     *   categories:array<string,int>,
     *   policies:array<string,mixed>
     * }
     */
    public function catalog(): array
    {
        $types = [];
        $categories = [];
        foreach ($this->types->all() as $type) {
            $categories[$type->category] = ($categories[$type->category] ?? 0) + 1;
            $types[] = [
                'key' => $type->key,
                'label' => $type->label,
                'category' => $type->category,
                'logical_type' => $type->logicalType,
                'editor_control' => $type->editorControl,
                'editor_strategy' => $type->enhancedControlRequired ? 'enhanced' : 'standard',
                'native_browser_picker' => false,
                'stores_value' => $type->storesValue,
                'repeatability' => [
                    'mode' => $type->repeatabilityMode,
                    'cloneable' => $type->supportsCloneableValues(),
                    'sortable' => $type->sortableClones,
                    'container_managed' => $type->managesItsOwnRows(),
                ],
                'modes' => $type->modes,
            ];
        }

        $presets = [];
        foreach ($this->presets->all() as $preset) {
            $presets[] = [
                'key' => $preset->key,
                'label' => $preset->label,
                'canonical_type' => $preset->canonicalType,
                'defaults' => $preset->defaults,
            ];
        }

        ksort($categories, SORT_STRING);

        return [
            'types' => $types,
            'presets' => $presets,
            'categories' => $categories,
            'policies' => [
                'clone_sort_model' => 'capability_driven',
                'sort_requires_clone' => true,
                'enhanced_picker_types' => ['date', 'time', 'datetime', 'range', 'color'],
                'browser_native_picker_is_product_contract' => false,
                'code_execution_allowed' => false,
            ],
        ];
    }
}
