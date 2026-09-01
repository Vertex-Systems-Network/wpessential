<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class FieldGroupDefinitionNormalizer
{
    public const DEFINITION_TYPE = 'field_group';
    public const OWNER_SURFACE_ID = 3;

    private const TOP_LEVEL_KEYS = [
        'group_key', 'title', 'description', 'fields', 'locations', 'presentation',
        'storage', 'show_in_rest', 'revision_policy',
    ];

    private const LOCATION_SOURCES = [
        'post_type', 'post_status', 'page_template', 'entity_id', 'taxonomy', 'term',
        'user_role', 'user', 'media_mime', 'comment_context', 'settings_page',
        'custom_table', 'frontend_context',
    ];

    private const LOCATION_OPERATORS = ['equals', 'not_equals', 'in', 'not_in'];

    private const STORAGE_MODES = [
        'unconfigured',
        'native_post_meta',
        'native_options',
        'native_term_meta',
        'native_user_meta',
        'native_comment_meta',
        'custom_table',
        'registered_provider',
    ];

    private const REVISION_POLICIES = ['disabled', 'enabled'];

    public function __construct(private FieldDefinitionNormalizer $fields = new FieldDefinitionNormalizer()) {}

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function normalize(array $payload, bool $publishing = false): array
    {
        $this->assertKnownKeys($payload);

        $groupKey = $this->machineKey($payload['group_key'] ?? null, 'Field Group key');
        $title = $payload['title'] ?? null;
        if (!is_string($title) || trim($title) === '') {
            throw new InvalidArgumentException('Field Group title is required.');
        }

        $fieldRows = $payload['fields'] ?? [];
        if (!is_array($fieldRows) || !array_is_list($fieldRows)) {
            throw new InvalidArgumentException('Field Group fields must be a list.');
        }
        if ($publishing && $fieldRows === []) {
            throw new InvalidArgumentException('A published Field Group must contain at least one field.');
        }

        $normalizedFields = [];
        $fieldKeys = [];
        foreach ($fieldRows as $row) {
            if (!is_array($row)) {
                throw new InvalidArgumentException('Every Field Group field must be an object/map.');
            }
            $field = $this->fields->normalize($row);
            $fieldKey = $field['key'] ?? null;
            if (!is_string($fieldKey) || isset($fieldKeys[$fieldKey])) {
                throw new InvalidArgumentException('Top-level Field Group field keys must be unique.');
            }
            $fieldKeys[$fieldKey] = true;
            $normalizedFields[] = $field;
        }

        $description = $payload['description'] ?? '';
        if (!is_string($description)) {
            throw new InvalidArgumentException('Field Group description must be a string.');
        }

        $showInRest = $payload['show_in_rest'] ?? false;
        if (!is_bool($showInRest)) {
            throw new InvalidArgumentException('Field Group show_in_rest must be boolean.');
        }

        return [
            'group_key' => $groupKey,
            'title' => trim($title),
            'description' => trim($description),
            'fields' => $normalizedFields,
            'locations' => $this->locations($payload['locations'] ?? []),
            'presentation' => $this->presentation($payload['presentation'] ?? []),
            'storage' => $this->storage($payload['storage'] ?? []),
            'show_in_rest' => $showInRest,
            'revision_policy' => $this->revisionPolicy($payload['revision_policy'] ?? 'disabled'),
        ];
    }

    /** @param array<string,mixed> $payload */
    private function assertKnownKeys(array $payload): void
    {
        foreach (array_keys($payload) as $key) {
            if (!in_array($key, self::TOP_LEVEL_KEYS, true)) {
                throw new InvalidArgumentException(sprintf('Unsupported Field Group option "%s".', (string) $key));
            }
        }
    }

    /**
     * OR-list of AND-groups.
     * @return list<list<array{source:string,operator:string,value:mixed,negate:bool}>>
     */
    private function locations(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException('Field Group locations must be an OR-list of AND-rule groups.');
        }

        $groups = [];
        foreach ($value as $group) {
            if (!is_array($group) || !array_is_list($group) || $group === []) {
                throw new InvalidArgumentException('Each Field Group location group must be a non-empty rule list.');
            }
            $rules = [];
            foreach ($group as $rule) {
                if (!is_array($rule) || array_is_list($rule)) {
                    throw new InvalidArgumentException('Each Field Group location rule must be an object/map.');
                }
                foreach (array_keys($rule) as $key) {
                    if (!in_array($key, ['source', 'operator', 'value', 'negate'], true)) {
                        throw new InvalidArgumentException(sprintf('Unsupported location rule option "%s".', (string) $key));
                    }
                }
                $source = $rule['source'] ?? null;
                $operator = $rule['operator'] ?? null;
                if (!is_string($source) || !in_array($source, self::LOCATION_SOURCES, true)) {
                    throw new InvalidArgumentException('Field Group location source is not supported.');
                }
                if (!is_string($operator) || !in_array($operator, self::LOCATION_OPERATORS, true)) {
                    throw new InvalidArgumentException('Field Group location operator is not supported.');
                }
                if (!array_key_exists('value', $rule)) {
                    throw new InvalidArgumentException('Field Group location rule requires a value.');
                }
                $negate = $rule['negate'] ?? false;
                if (!is_bool($negate)) {
                    throw new InvalidArgumentException('Field Group location negate must be boolean.');
                }
                $rules[] = [
                    'source' => $source,
                    'operator' => $operator,
                    'value' => $rule['value'],
                    'negate' => $negate,
                ];
            }
            $groups[] = $rules;
        }
        return $groups;
    }

    /** @return array{panel_style:string,label_placement:string,instruction_placement:string,collapsible:bool,collapsed:bool} */
    private function presentation(mixed $value): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException('Field Group presentation must be an object/map.');
        }
        foreach (array_keys($value) as $key) {
            if (!in_array($key, ['panel_style', 'label_placement', 'instruction_placement', 'collapsible', 'collapsed'], true)) {
                throw new InvalidArgumentException(sprintf('Unsupported Field Group presentation option "%s".', (string) $key));
            }
        }

        $panel = $value['panel_style'] ?? 'standard';
        $label = $value['label_placement'] ?? 'top';
        $instruction = $value['instruction_placement'] ?? 'below_label';
        if (!is_string($panel) || !in_array($panel, ['standard', 'seamless', 'sectioned'], true)) {
            throw new InvalidArgumentException('Field Group panel_style must be standard, seamless, or sectioned.');
        }
        if (!is_string($label) || !in_array($label, ['top', 'left'], true)) {
            throw new InvalidArgumentException('Field Group label_placement must be top or left.');
        }
        if (!is_string($instruction) || !in_array($instruction, ['below_label', 'below_input'], true)) {
            throw new InvalidArgumentException('Field Group instruction_placement is invalid.');
        }
        $collapsible = $value['collapsible'] ?? false;
        $collapsed = $value['collapsed'] ?? false;
        if (!is_bool($collapsible) || !is_bool($collapsed)) {
            throw new InvalidArgumentException('Field Group collapsible/closed state must be boolean.');
        }
        if ($collapsed && !$collapsible) {
            throw new InvalidArgumentException('Field Group cannot default collapsed unless collapsible is enabled.');
        }

        return [
            'panel_style' => $panel,
            'label_placement' => $label,
            'instruction_placement' => $instruction,
            'collapsible' => $collapsible,
            'collapsed' => $collapsed,
        ];
    }

    /** @return array{mode:string} */
    private function storage(mixed $value): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException('Field Group storage must be an object/map.');
        }
        foreach (array_keys($value) as $key) {
            if ($key !== 'mode') {
                throw new InvalidArgumentException(sprintf('Unsupported Field Group storage option "%s".', (string) $key));
            }
        }

        $mode = $value['mode'] ?? 'unconfigured';
        if (!is_string($mode) || !in_array($mode, self::STORAGE_MODES, true)) {
            throw new InvalidArgumentException('Field Group storage mode is not supported.');
        }

        return ['mode' => $mode];
    }

    private function revisionPolicy(mixed $value): string
    {
        if (!is_string($value) || !in_array($value, self::REVISION_POLICIES, true)) {
            throw new InvalidArgumentException('Field Group revision_policy must be disabled or enabled.');
        }
        return $value;
    }

    private function machineKey(mixed $value, string $label): string
    {
        if (!is_string($value) || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('%s must be a lowercase machine key up to 64 characters.', $label));
        }
        return $value;
    }
}
