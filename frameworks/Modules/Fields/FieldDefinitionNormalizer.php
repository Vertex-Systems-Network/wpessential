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
        $uuid = $this->optionalUuid($field['uuid'] ?? null);
        $submittedType = $this->requiredMachineKey($field['type'] ?? null, 'Field type');
        $requestedType = $this->requestedType($submittedType, $field['preset'] ?? null);
        $key = $this->requiredMachineKey($field['key'] ?? null, 'Field key');
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
            'uuid' => $uuid,
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

    private function requestedType(string $submittedType, mixed $submittedPreset): string
    {
        if ($submittedPreset === null) {
            return $submittedType;
        }
        if (!is_string($submittedPreset) || !$this->presets->has($submittedPreset)) {
            throw new InvalidArgumentException('Field preset must reference a registered preset or be null.');
        }
        $preset = $this->presets->get($submittedPreset);
        if ($preset->canonicalType !== $submittedType) {
            throw new InvalidArgumentException(sprintf(
                'Field preset "%s" does not compile to submitted canonical type "%s".',
                $submittedPreset,
                $submittedType,
            ));
        }
        return $submittedPreset;
    }

    /**
     * @param array<string,mixed> $field
     * @return array<string,mixed>
     */
    private function repeatability(array $field, FieldTypeDescriptor $descriptor): array
    {
        $canonical = $field['repeatability'] ?? null;
        if ($canonical !== null && (!is_array($canonical) || array_is_list($canonical))) {
            throw new InvalidArgumentException('Canonical repeatability must be a named map when supplied.');
        }
        if (is_array($canonical) && isset($canonical['mode'])
            && (!is_string($canonical['mode']) || $canonical['mode'] !== ($descriptor->managesItsOwnRows() ? 'container_managed' : $descriptor->repeatabilityMode))
        ) {
            throw new InvalidArgumentException('Canonical repeatability mode does not match the field type.');
        }

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

        $enabled = array_key_exists('cloneable', $field)
            ? $field['cloneable']
            : (array_key_exists('repeatable', $field) ? $field['repeatable'] : ($canonical['enabled'] ?? false));
        if (!is_bool($enabled)) {
            throw new InvalidArgumentException('cloneable/repeatable must be boolean.');
        }
        $sortable = array_key_exists('sortable', $field) ? $field['sortable'] : ($canonical['sortable'] ?? false);
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

        $minSource = array_key_exists('min_clones', $field) ? $field['min_clones'] : ($canonical['min'] ?? 0);
        $min = $this->nonNegativeInt($minSource, 'min_clones');
        $maxSource = array_key_exists('max_clones', $field) ? $field['max_clones'] : ($canonical['max'] ?? null);
        $max = $maxSource;
        if ($max !== null) {
            $max = $this->positiveInt($max, 'max_clones');
            if ($max < $min) {
                throw new InvalidArgumentException('max_clones cannot be lower than min_clones.');
            }
        }

        $cloneDefault = array_key_exists('clone_default', $field) ? $field['clone_default'] : ($canonical['clone_default'] ?? false);
        $storeAsMultiple = array_key_exists('clone_as_multiple', $field) ? $field['clone_as_multiple'] : ($canonical['store_as_multiple'] ?? false);
        $emptyStart = array_key_exists('clone_empty_start', $field) ? $field['clone_empty_start'] : ($canonical['empty_start'] ?? false);
        $addLabel = array_key_exists('add_button_label', $field) ? $field['add_button_label'] : ($canonical['add_button_label'] ?? null);

        return [
            'mode' => $descriptor->repeatabilityMode,
            'enabled' => $enabled,
            'sortable' => $sortable,
            'clone_default' => $this->boolValue($cloneDefault, 'clone_default'),
            'store_as_multiple' => $this->boolValue($storeAsMultiple, 'clone_as_multiple'),
            'empty_start' => $this->boolValue($emptyStart, 'clone_empty_start'),
            'min' => $min,
            'max' => $max,
            'add_button_label' => $this->optionalString($addLabel),
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

    private function optionalUuid(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!is_string($value) || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException('Field uuid must be a lowercase RFC 4122 UUID or null before first save.');
        }
        return $value;
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
