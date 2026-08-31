<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final class FieldDefinitionNormalizer
{
    private readonly FieldPresetRegistry $presets;

    public function __construct(
        private readonly FieldTypeRegistry $types = new FieldTypeRegistry(),
        ?FieldPresetRegistry $presets = null,
    ) {
        $this->presets = $presets ?? new FieldPresetRegistry($this->types);
    }

    /**
     * @param array<string,mixed> $field
     * @return array<string,mixed>
     */
    public function normalize(array $field): array
    {
        $key = $this->requiredMachineKey($field['key'] ?? null, 'Field key');
        $requestedType = $this->requiredMachineKey($field['type'] ?? null, 'Field type');
        $label = $field['label'] ?? '';
        if (!is_string($label)) {
            throw new InvalidArgumentException('Field label must be a string.');
        }

        $preset = null;
        if ($this->presets->has($requestedType)) {
            $preset = $this->presets->get($requestedType);
            $type = $preset->canonicalType;
        } else {
            $type = $requestedType;
        }
        $descriptor = $this->types->get($type);

        $settings = $field['settings'] ?? [];
        if (!is_array($settings) || array_is_list($settings)) {
            throw new InvalidArgumentException('Field settings must be a named map.');
        }
        if ($preset instanceof FieldPresetDescriptor) {
            $settings = array_replace($preset->defaults, $settings);
        }

        if ($descriptor->key === 'code_editor' && (($settings['execute'] ?? false) === true || ($settings['eval'] ?? false) === true)) {
            throw new InvalidArgumentException('Code fields store text only and can never enable PHP/JS execution.');
        }

        $repeat = $this->repeatability($field, $descriptor);
        $subfields = $this->subfields($field, $descriptor);

        return [
            'key' => $key,
            'label' => trim($label),
            'type' => $descriptor->key,
            'preset' => $preset?->key,
            'logical_type' => $descriptor->logicalType,
            'editor_control' => $descriptor->editorControl,
            'editor_strategy' => $descriptor->enhancedControlRequired ? 'enhanced' : 'standard',
            'native_browser_picker' => false,
            'stores_value' => $descriptor->storesValue,
            'settings' => $settings,
            'repeatability' => $repeat,
            'subfields' => $subfields,
        ];
    }

    /**
     * @param array<string,mixed> $field
     * @return array<string,mixed>
     */
    private function repeatability(array $field, FieldTypeDescriptor $descriptor): array
    {
        if ($descriptor->managesItsOwnRows()) {
            if (($field['cloneable'] ?? false) === true || ($field['repeatable'] ?? false) === true) {
                throw new InvalidArgumentException(sprintf(
                    'Field type "%s" manages row repeatability internally; use its row settings instead of common cloneable.',
                    $descriptor->key,
                ));
            }
            return [
                'mode' => 'container_managed',
                'enabled' => true,
                'sortable' => $descriptor->sortableClones,
            ];
        }

        $enabled = $field['cloneable'] ?? ($field['repeatable'] ?? false);
        if (!is_bool($enabled)) {
            throw new InvalidArgumentException('cloneable/repeatable must be boolean.');
        }
        $sortable = $field['sortable'] ?? false;
        if (!is_bool($sortable)) {
            throw new InvalidArgumentException('sortable must be boolean.');
        }

        if ($enabled && !$descriptor->supportsCloneableValues()) {
            throw new InvalidArgumentException(sprintf('Field type "%s" does not store repeatable values.', $descriptor->key));
        }
        if ($sortable && !$enabled) {
            throw new InvalidArgumentException('Sortable clones require cloneable/repeatable to be enabled first.');
        }
        if ($sortable && !$descriptor->sortableClones) {
            throw new InvalidArgumentException(sprintf('Field type "%s" does not support sortable clones.', $descriptor->key));
        }

        $min = $this->nonNegativeInt($field['min_clones'] ?? 0, 'min_clones');
        $max = $field['max_clones'] ?? null;
        if ($max !== null) {
            $max = $this->positiveInt($max, 'max_clones');
            if ($max < $min) {
                throw new InvalidArgumentException('max_clones cannot be lower than min_clones.');
            }
        }

        return [
            'mode' => $descriptor->repeatabilityMode,
            'enabled' => $enabled,
            'sortable' => $sortable,
            'clone_default' => $this->boolValue($field['clone_default'] ?? false, 'clone_default'),
            'store_as_multiple' => $this->boolValue($field['clone_as_multiple'] ?? false, 'clone_as_multiple'),
            'empty_start' => $this->boolValue($field['clone_empty_start'] ?? false, 'clone_empty_start'),
            'min' => $min,
            'max' => $max,
            'add_button_label' => $this->optionalString($field['add_button_label'] ?? null),
        ];
    }

    /**
     * @param array<string,mixed> $field
     * @return list<array<string,mixed>>
     */
    private function subfields(array $field, FieldTypeDescriptor $descriptor): array
    {
        $value = $field['subfields'] ?? [];
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException('subfields must be a list.');
        }
        $supportsSubfields = in_array($descriptor->key, ['group', 'repeater'], true);
        if (!$supportsSubfields && $value !== []) {
            throw new InvalidArgumentException(sprintf('Field type "%s" cannot contain subfields.', $descriptor->key));
        }

        $normalized = [];
        $keys = [];
        foreach ($value as $subfield) {
            if (!is_array($subfield)) {
                throw new InvalidArgumentException('Each subfield must be an object/map.');
            }
            $item = $this->normalize($subfield);
            $subKey = $item['key'];
            if (!is_string($subKey) || isset($keys[$subKey])) {
                throw new InvalidArgumentException('Subfield keys must be unique within their container.');
            }
            $keys[$subKey] = true;
            $normalized[] = $item;
        }
        return $normalized;
    }

    private function requiredMachineKey(mixed $value, string $label): string
    {
        if (!is_string($value) || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('%s must be a lowercase machine key up to 64 characters.', $label));
        }
        return $value;
    }

    private function boolValue(mixed $value, string $label): bool
    {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(sprintf('%s must be boolean.', $label));
        }
        return $value;
    }

    private function nonNegativeInt(mixed $value, string $label): int
    {
        if (!is_int($value) || $value < 0) {
            throw new InvalidArgumentException(sprintf('%s must be a non-negative integer.', $label));
        }
        return $value;
    }

    private function positiveInt(mixed $value, string $label): int
    {
        if (!is_int($value) || $value < 1) {
            throw new InvalidArgumentException(sprintf('%s must be a positive integer.', $label));
        }
        return $value;
    }

    private function optionalString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException('add_button_label must be a string or null.');
        }
        $value = trim($value);
        return $value === '' ? null : $value;
    }
}
