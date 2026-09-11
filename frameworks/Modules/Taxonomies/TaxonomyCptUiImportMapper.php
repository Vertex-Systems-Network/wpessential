<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

final class TaxonomyCptUiImportMapper
{
    /** @var list<string> */
    private const EXECUTABLE_FIELDS = [
        'rest_controller_class',
        'meta_box_cb',
        'meta_box_sanitize_cb',
        'update_count_callback',
    ];

    /** @var list<string> */
    private const OPTIONAL_BOOLEAN_FIELDS = [
        'public',
        'publicly_queryable',
        'hierarchical',
        'show_ui',
        'show_in_menu',
        'show_in_nav_menus',
        'show_tagcloud',
        'show_admin_column',
        'show_in_rest',
        'show_in_quick_edit',
        'sort',
    ];

    /** @var list<string> */
    private const KNOWN_FIELDS = [
        'name', 'label', 'singular_label', 'description', 'object_types', 'labels',
        'public', 'publicly_queryable', 'hierarchical', 'show_ui', 'show_in_menu',
        'show_in_nav_menus', 'show_tagcloud', 'show_admin_column', 'show_in_rest',
        'show_in_quick_edit', 'sort', 'rewrite', 'rewrite_slug', 'rewrite_withfront',
        'query_var', 'query_var_slug', 'rest_base', 'rest_namespace', 'default_term',
        'rest_controller_class', 'meta_box_cb', 'meta_box_sanitize_cb', 'update_count_callback',
    ];

    /**
     * @param array<string,mixed> $source
     * @return array{
     *   payload:array<string,mixed>,
     *   rejected_fields:list<string>,
     *   unsupported_fields:list<string>,
     *   issues:list<array{id:string,severity:string,field:string,message:string}>
     * }
     */
    public function map(array $source): array
    {
        $issues = [];
        $payload = ['automatic_labels' => false];

        $key = $this->requiredString($source, 'name', 'taxonomy_key', $issues);
        if ($key !== null) {
            $payload['taxonomy_key'] = $key;
        }
        $name = $this->requiredString($source, 'label', 'name', $issues);
        if ($name !== null) {
            $payload['name'] = $name;
        }
        $singular = $this->requiredString($source, 'singular_label', 'singular_name', $issues);
        if ($singular !== null) {
            $payload['singular_name'] = $singular;
        }

        $objectTypes = $this->objectTypes($source['object_types'] ?? null);
        if ($objectTypes === []) {
            $issues[] = $this->issue(
                'cptui_object_types_missing',
                'blocked',
                'object_types',
                'CPT UI taxonomy import requires at least one associated object type.',
            );
        } else {
            $payload['object_types'] = $objectTypes;
        }

        if (array_key_exists('description', $source) && is_string($source['description'])) {
            $payload['description'] = trim($source['description']);
        }

        foreach (self::OPTIONAL_BOOLEAN_FIELDS as $field) {
            if (!array_key_exists($field, $source) || $source[$field] === '') {
                continue;
            }
            $value = $this->booleanValue($source[$field]);
            if ($value === null) {
                $issues[] = $this->issue(
                    'cptui_boolean_invalid',
                    'blocked',
                    $field,
                    sprintf('CPT UI field "%s" must be a boolean-like true/false or 1/0 value.', $field),
                );
                continue;
            }
            $payload[$field] = $value;
        }

        $labels = $this->labels($source['labels'] ?? null);
        if ($labels['values'] !== []) {
            $payload['labels'] = $labels['values'];
        }

        $rewrite = $this->rewrite($source, $issues);
        if ($rewrite !== null) {
            $payload['rewrite'] = $rewrite;
        }
        $queryVar = $this->queryVar($source, $issues);
        if ($queryVar !== null) {
            $payload['query_var'] = $queryVar;
        }

        foreach (['rest_base', 'rest_namespace'] as $field) {
            $value = $source[$field] ?? null;
            if (is_string($value) && trim($value) !== '') {
                $payload[$field] = trim($value);
            }
        }

        $defaultTerm = $this->defaultTerm($source['default_term'] ?? null);
        if ($defaultTerm !== null) {
            $payload['default_term'] = $defaultTerm;
        }

        $rejectedFields = [];
        foreach (self::EXECUTABLE_FIELDS as $field) {
            if ($this->hasMeaningfulValue($source[$field] ?? null)) {
                $rejectedFields[] = $field;
                $issues[] = $this->issue(
                    'cptui_executable_field_rejected',
                    'blocked',
                    $field,
                    sprintf('CPT UI field "%s" contains executable callback/class input and cannot be imported. Use a registered WPEssential provider ID instead.', $field),
                );
            }
        }

        $unsupportedFields = [];
        foreach (array_keys($source) as $field) {
            if (is_string($field) && !in_array($field, self::KNOWN_FIELDS, true)) {
                $unsupportedFields[] = $field;
            }
        }
        foreach ($labels['unsupported'] as $label) {
            $unsupportedFields[] = 'labels.' . $label;
        }
        sort($unsupportedFields, SORT_STRING);
        sort($rejectedFields, SORT_STRING);

        return [
            'payload' => $payload,
            'rejected_fields' => $rejectedFields,
            'unsupported_fields' => array_values(array_unique($unsupportedFields)),
            'issues' => $issues,
        ];
    }

    /**
     * @param array<string,mixed> $source
     * @param list<array{id:string,severity:string,field:string,message:string}> $issues
     */
    private function requiredString(array $source, string $sourceField, string $targetField, array &$issues): ?string
    {
        $value = $source[$sourceField] ?? null;
        if (!is_string($value) || trim($value) === '') {
            $issues[] = $this->issue(
                'cptui_required_field_missing',
                'blocked',
                $targetField,
                sprintf('CPT UI field "%s" is required for canonical Taxonomy import preview.', $sourceField),
            );
            return null;
        }
        return trim($value);
    }

    /** @return list<string> */
    private function objectTypes(mixed $value): array
    {
        if (is_string($value)) {
            $value = array_map('trim', explode(',', $value));
        }
        if (!is_array($value) || !array_is_list($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (!is_string($item) || trim($item) === '') {
                continue;
            }
            $item = trim($item);
            if (!in_array($item, $result, true)) {
                $result[] = $item;
            }
        }
        return $result;
    }

    private function booleanValue(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === 1 || $value === '1' || $value === 'true') {
            return true;
        }
        if ($value === 0 || $value === '0' || $value === 'false') {
            return false;
        }
        return null;
    }

    /** @return array{values:array<string,string>,unsupported:list<string>} */
    private function labels(mixed $value): array
    {
        if (!is_array($value) || array_is_list($value)) {
            return ['values' => [], 'unsupported' => []];
        }

        $allowed = TaxonomyLabelPolicy::overrideKeys();
        $labels = [];
        $unsupported = [];
        foreach ($value as $key => $label) {
            if (!is_string($key)) {
                continue;
            }
            if (!in_array($key, $allowed, true)) {
                if ($this->hasMeaningfulValue($label)) {
                    $unsupported[] = $key;
                }
                continue;
            }
            if (is_string($label) && trim($label) !== '') {
                $labels[$key] = trim($label);
            }
        }
        ksort($labels, SORT_STRING);
        sort($unsupported, SORT_STRING);
        return ['values' => $labels, 'unsupported' => $unsupported];
    }

    /**
     * @param array<string,mixed> $source
     * @param list<array{id:string,severity:string,field:string,message:string}> $issues
     * @return bool|array<string,bool|string>|null
     */
    private function rewrite(array $source, array &$issues): bool|array|null
    {
        if (!array_key_exists('rewrite', $source) || $source['rewrite'] === '') {
            return null;
        }
        $enabled = $this->booleanValue($source['rewrite']);
        if ($enabled === null) {
            $issues[] = $this->issue('cptui_boolean_invalid', 'blocked', 'rewrite', 'CPT UI rewrite must be a boolean-like true/false or 1/0 value.');
            return null;
        }
        if (!$enabled) {
            return false;
        }

        $rewrite = [];
        $slug = $source['rewrite_slug'] ?? null;
        if (is_string($slug) && trim($slug) !== '') {
            $rewrite['slug'] = trim($slug, '/');
        }
        if (array_key_exists('rewrite_withfront', $source) && $source['rewrite_withfront'] !== '') {
            $withFront = $this->booleanValue($source['rewrite_withfront']);
            if ($withFront === null) {
                $issues[] = $this->issue('cptui_boolean_invalid', 'blocked', 'rewrite_withfront', 'CPT UI rewrite_withfront must be a boolean-like true/false or 1/0 value.');
            } else {
                $rewrite['with_front'] = $withFront;
            }
        }
        return $rewrite === [] ? true : $rewrite;
    }

    /**
     * @param array<string,mixed> $source
     * @param list<array{id:string,severity:string,field:string,message:string}> $issues
     * @return bool|string|null
     */
    private function queryVar(array $source, array &$issues): bool|string|null
    {
        if (!array_key_exists('query_var', $source) || $source['query_var'] === '') {
            return null;
        }
        $enabled = $this->booleanValue($source['query_var']);
        if ($enabled === null) {
            $issues[] = $this->issue('cptui_boolean_invalid', 'blocked', 'query_var', 'CPT UI query_var must be a boolean-like true/false or 1/0 value.');
            return null;
        }
        if (!$enabled) {
            return false;
        }
        $slug = $source['query_var_slug'] ?? null;
        return is_string($slug) && trim($slug) !== '' ? trim($slug) : true;
    }

    /** @return array{name:string,slug?:string,description?:string}|null */
    private function defaultTerm(mixed $value): ?array
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }
        $parts = array_map('trim', explode(',', $value, 3));
        if (($parts[0] ?? '') === '') {
            return null;
        }

        $result = ['name' => $parts[0]];
        if (($parts[1] ?? '') !== '') {
            $result['slug'] = $parts[1];
        }
        if (($parts[2] ?? '') !== '') {
            $result['description'] = $parts[2];
        }
        return $result;
    }

    private function hasMeaningfulValue(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === false) {
            return false;
        }
        if (is_array($value)) {
            return $value !== [];
        }
        return true;
    }

    /** @return array{id:string,severity:string,field:string,message:string} */
    private function issue(string $id, string $severity, string $field, string $message): array
    {
        return [
            'id' => $id,
            'severity' => $severity,
            'field' => $field,
            'message' => $message,
        ];
    }
}
