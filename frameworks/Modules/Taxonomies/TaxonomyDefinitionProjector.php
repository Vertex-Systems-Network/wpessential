<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinition;
use WPEssential\Platform\WordPress\Registrations\RegistrationKind;
use WPEssential\Platform\WordPress\Registrations\TaxonomyRuntimeProviderRegistry;

final class TaxonomyDefinitionProjector
{
    public const DEFINITION_TYPE = 'taxonomy';
    public const OWNER_SURFACE_ID = 2;

    /** @var list<string> */
    private const TOP_LEVEL_KEYS = [
        'taxonomy_key', 'object_types', 'name', 'singular_name', 'description', 'automatic_labels', 'labels',
        'public', 'publicly_queryable', 'hierarchical', 'show_ui', 'show_in_menu',
        'show_in_nav_menus', 'show_tagcloud', 'show_in_quick_edit', 'show_admin_column',
        'show_in_rest', 'rest_base', 'rest_namespace', 'query_var', 'rewrite', 'sort',
        'capabilities', 'default_term', 'args', 'runtime_providers',
    ];

    /** @var list<string> */
    private const LABEL_KEYS = [
        'menu_name', 'search_items', 'popular_items', 'all_items', 'parent_item', 'parent_item_colon',
        'name_field_description', 'slug_field_description', 'parent_field_description',
        'desc_field_description', 'edit_item', 'view_item', 'update_item', 'add_new_item',
        'new_item_name', 'template_name', 'separate_items_with_commas', 'add_or_remove_items',
        'choose_from_most_used', 'not_found', 'no_terms', 'filter_by_item', 'items_list_navigation',
        'items_list', 'most_used', 'back_to_items', 'item_link', 'item_link_description',
    ];

    /** @var list<string> */
    private const RESERVED_TAXONOMIES = [
        'category', 'post_tag', 'nav_menu', 'link_category', 'post_format', 'wp_theme',
        'wp_template_part_area', 'wp_pattern_category',
    ];

    /** @var list<string> */
    private const OBJECT_TERM_ARG_KEYS = ['orderby', 'order', 'fields'];

    /** @var list<string> */
    private const OBJECT_TERM_ORDERBY_VALUES = [
        'name', 'slug', 'term_group', 'term_id', 'id', 'description', 'parent', 'term_order',
        'count', 'include', 'none',
    ];

    /** @var list<string> */
    private const OBJECT_TERM_FIELDS_VALUES = [
        'all', 'all_with_object_id', 'ids', 'tt_ids', 'names', 'slugs',
    ];

    private readonly TaxonomyRuntimeProviderRegistry $runtimeProviders;

    public function __construct(?TaxonomyRuntimeProviderRegistry $runtimeProviders = null)
    {
        $this->runtimeProviders = $runtimeProviders ?? new TaxonomyRuntimeProviderRegistry();
    }

    public function project(Definition $definition): RegistrationDefinition
    {
        $this->assertDefinition($definition);
        $payload = $definition->payload;
        $this->assertKnownTopLevelKeys($payload);

        $key = $this->requiredString($payload, 'taxonomy_key');
        $this->assertMachineKey($key, 32, 'Taxonomy key');
        if (in_array($key, self::RESERVED_TAXONOMIES, true)) {
            throw new InvalidArgumentException(sprintf('Taxonomy key "%s" is reserved by WordPress.', $key));
        }

        $name = $this->requiredString($payload, 'name');
        $singularName = $this->requiredString($payload, 'singular_name');
        $objectTypes = $this->objectTypes($payload['object_types'] ?? null);
        $hierarchical = $this->boolValue($payload, 'hierarchical', false);
        $automaticLabels = $this->boolValue($payload, 'automatic_labels', true);
        $args = [
            'labels' => $this->labels(
                $name,
                $singularName,
                $hierarchical,
                $automaticLabels,
                $payload['labels'] ?? [],
            ),
            'public' => $this->boolValue($payload, 'public', true),
            'hierarchical' => $hierarchical,
            'show_in_rest' => $this->boolValue($payload, 'show_in_rest', true),
            'rewrite' => $this->rewrite($payload['rewrite'] ?? true),
            'sort' => $this->boolValue($payload, 'sort', false),
        ];

        if (array_key_exists('description', $payload)) {
            if (!is_string($payload['description'])) {
                throw new InvalidArgumentException('Taxonomy description must be a string.');
            }
            $args['description'] = trim($payload['description']);
        }

        foreach ([
            'publicly_queryable', 'show_ui', 'show_in_menu', 'show_in_nav_menus',
            'show_tagcloud', 'show_in_quick_edit', 'show_admin_column',
        ] as $field) {
            $this->copyOptionalBool($payload, $args, $field);
        }

        if (array_key_exists('rest_base', $payload) && $payload['rest_base'] !== null) {
            $args['rest_base'] = $this->routeSegment($payload['rest_base'], 'rest_base');
        }
        if (array_key_exists('rest_namespace', $payload) && $payload['rest_namespace'] !== null) {
            $args['rest_namespace'] = $this->restNamespace($payload['rest_namespace']);
        }
        if (array_key_exists('query_var', $payload)) {
            $args['query_var'] = $this->queryVar($payload['query_var']);
        }
        if (array_key_exists('capabilities', $payload)) {
            $args['capabilities'] = $this->capabilities($payload['capabilities']);
        }
        if (array_key_exists('default_term', $payload) && $payload['default_term'] !== null) {
            $args['default_term'] = $this->defaultTerm($payload['default_term']);
        }
        if (array_key_exists('args', $payload) && $payload['args'] !== null) {
            $args['args'] = $this->objectTermArgs($payload['args']);
        }

        $registrationPayload = [
            'object_types' => $objectTypes,
            'args' => $args,
        ];
        if (array_key_exists('runtime_providers', $payload)) {
            $providerIds = $this->runtimeProviderIds($payload['runtime_providers']);
            if ($providerIds !== []) {
                $registrationPayload['provider_ids'] = $providerIds;
            }
        }

        return new RegistrationDefinition(
            id: $definition->id,
            kind: RegistrationKind::Taxonomy,
            key: $key,
            payload: $registrationPayload,
            enabled: true,
            revision: $definition->revision,
        );
    }

    private function assertDefinition(Definition $definition): void
    {
        if ($definition->type !== self::DEFINITION_TYPE) {
            throw new InvalidArgumentException('Taxonomy projector only accepts taxonomy definitions.');
        }
        if ($definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Taxonomy definitions must be owned by canonical Surface 2.');
        }
        if ($definition->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Only published Taxonomy definitions can be compiled for runtime registration.');
        }
    }

    /** @param array<string,mixed> $payload */
    private function assertKnownTopLevelKeys(array $payload): void
    {
        foreach (array_keys($payload) as $key) {
            if (!in_array($key, self::TOP_LEVEL_KEYS, true)) {
                throw new InvalidArgumentException(sprintf('Unsupported Taxonomy definition field "%s".', $key));
            }
        }
    }

    /** @param array<string,mixed> $payload */
    private function requiredString(array $payload, string $field): string
    {
        $value = $payload[$field] ?? null;
        if (!is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException(sprintf('%s is required and must be a non-empty string.', $field));
        }
        return trim($value);
    }

    private function assertMachineKey(string $value, int $maximum, string $label): void
    {
        if (strlen($value) > $maximum || preg_match('/^[a-z0-9_-]+$/', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('%s must be lowercase alphanumeric/dash/underscore and at most %d characters.', $label, $maximum));
        }
    }

    /** @return list<string> */
    private function objectTypes(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value) || $value === []) {
            throw new InvalidArgumentException('Taxonomy object_types must be a non-empty list.');
        }
        $normalized = [];
        foreach ($value as $objectType) {
            if (!is_string($objectType)) {
                throw new InvalidArgumentException('Taxonomy object_types entries must be strings.');
            }
            $objectType = trim($objectType);
            $this->assertMachineKey($objectType, 20, 'Taxonomy object type');
            if (!in_array($objectType, $normalized, true)) {
                $normalized[] = $objectType;
            }
        }
        return $normalized;
    }

    /** @param array<string,mixed> $payload */
    private function boolValue(array $payload, string $field, bool $default): bool
    {
        if (!array_key_exists($field, $payload)) {
            return $default;
        }
        if (!is_bool($payload[$field])) {
            throw new InvalidArgumentException(sprintf('%s must be boolean.', $field));
        }
        return $payload[$field];
    }

    /** @param array<string,mixed> $source @param array<string,mixed> $target */
    private function copyOptionalBool(array $source, array &$target, string $field): void
    {
        if (!array_key_exists($field, $source) || $source[$field] === null) {
            return;
        }
        if (!is_bool($source[$field])) {
            throw new InvalidArgumentException(sprintf('%s must be boolean or inherit/null.', $field));
        }
        $target[$field] = $source[$field];
    }

    /** @return array<string,string> */
    private function labels(
        string $name,
        string $singularName,
        bool $hierarchical,
        bool $automaticLabels,
        mixed $overrides,
    ): array {
        if (!is_array($overrides) || array_is_list($overrides) && $overrides !== []) {
            throw new InvalidArgumentException('Taxonomy labels must be an object/map.');
        }

        $labels = $automaticLabels
            ? $this->generatedLabels($name, $singularName, $hierarchical)
            : ['name' => $name, 'singular_name' => $singularName];
        foreach ($overrides as $key => $value) {
            if (!is_string($key) || !in_array($key, self::LABEL_KEYS, true)) {
                throw new InvalidArgumentException(sprintf('Unsupported Taxonomy label "%s".', (string) $key));
            }
            if (!is_string($value)) {
                throw new InvalidArgumentException(sprintf('Taxonomy label "%s" must be a string.', $key));
            }
            $value = trim($value);
            if ($value !== '') {
                $labels[$key] = $value;
            }
        }
        return $labels;
    }

    /** @return array<string,string> */
    private function generatedLabels(string $name, string $singularName, bool $hierarchical): array
    {
        $labels = [
            'name' => $name,
            'singular_name' => $singularName,
            'menu_name' => $name,
            'search_items' => sprintf('Search %s', $name),
            'all_items' => sprintf('All %s', $name),
            'edit_item' => sprintf('Edit %s', $singularName),
            'view_item' => sprintf('View %s', $singularName),
            'update_item' => sprintf('Update %s', $singularName),
            'add_new_item' => sprintf('Add New %s', $singularName),
            'new_item_name' => sprintf('New %s Name', $singularName),
            'not_found' => sprintf('No %s found', $name),
            'no_terms' => sprintf('No %s', $name),
            'items_list_navigation' => sprintf('%s list navigation', $name),
            'items_list' => sprintf('%s list', $name),
            'back_to_items' => sprintf('Go to %s', $name),
            'item_link' => sprintf('%s Link', $singularName),
            'item_link_description' => sprintf('A link to a %s', $singularName),
        ];

        if ($hierarchical) {
            $labels['parent_item'] = sprintf('Parent %s', $singularName);
            $labels['parent_item_colon'] = sprintf('Parent %s:', $singularName);
            return $labels;
        }

        $labels['popular_items'] = sprintf('Popular %s', $name);
        $labels['separate_items_with_commas'] = sprintf('Separate %s with commas', $name);
        $labels['add_or_remove_items'] = sprintf('Add or remove %s', $name);
        $labels['choose_from_most_used'] = sprintf('Choose from the most used %s', $name);
        return $labels;
    }

    /** @return bool|array<string,bool|int|string> */
    private function rewrite(mixed $value): bool|array
    {
        if (is_bool($value)) {
            return $value;
        }
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException('rewrite must be boolean or a typed rewrite map.');
        }
        $allowed = ['slug', 'with_front', 'hierarchical', 'ep_mask'];
        $normalized = [];
        foreach ($value as $key => $item) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException(sprintf('Unsupported rewrite option "%s".', (string) $key));
            }
            if ($key === 'slug') {
                $normalized[$key] = $this->rewriteSlug($item);
                continue;
            }
            if ($key === 'ep_mask') {
                if (!is_int($item) || $item < 0) {
                    throw new InvalidArgumentException('rewrite.ep_mask must be a non-negative integer.');
                }
                $normalized[$key] = $item;
                continue;
            }
            if (!is_bool($item)) {
                throw new InvalidArgumentException(sprintf('rewrite.%s must be boolean.', $key));
            }
            $normalized[$key] = $item;
        }
        return $normalized;
    }

    private function rewriteSlug(mixed $value): string
    {
        if (!is_string($value) || trim($value) === '' || str_contains($value, '..') || preg_match('/^[a-z0-9_\/-]+$/', $value) !== 1) {
            throw new InvalidArgumentException('rewrite.slug must be a safe lowercase route path.');
        }
        return trim($value, '/');
    }

    private function routeSegment(mixed $value, string $field): string
    {
        if (!is_string($value) || preg_match('/^[a-z0-9._-]+$/', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('%s must be a safe route segment.', $field));
        }
        return $value;
    }

    private function restNamespace(mixed $value): string
    {
        if (!is_string($value) || preg_match('/^[a-z0-9._-]+(?:\/[a-z0-9._-]+)+$/', $value) !== 1) {
            throw new InvalidArgumentException('rest_namespace must be a versioned REST namespace path.');
        }
        return $value;
    }

    private function queryVar(mixed $value): bool|string
    {
        if (is_bool($value)) {
            return $value;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException('query_var must be boolean or a safe query variable name.');
        }
        $this->assertMachineKey($value, 64, 'query_var');
        return $value;
    }

    /** @return array<string,string> */
    private function capabilities(mixed $value): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException('capabilities must be a named capability map.');
        }
        $allowed = ['manage_terms', 'edit_terms', 'delete_terms', 'assign_terms'];
        $normalized = [];
        foreach ($value as $key => $capability) {
            if (!is_string($key) || !in_array($key, $allowed, true) || !is_string($capability)) {
                throw new InvalidArgumentException('Taxonomy capabilities must use supported named string entries.');
            }
            $this->assertMachineKey($capability, 64, 'capability name');
            $normalized[$key] = $capability;
        }
        return $normalized;
    }

    /** @return array{name:string,slug?:string,description?:string} */
    private function defaultTerm(mixed $value): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException('default_term must be a typed object/map.');
        }
        foreach (array_keys($value) as $key) {
            if (!is_string($key) || !in_array($key, ['name', 'slug', 'description'], true)) {
                throw new InvalidArgumentException(sprintf('Unsupported default_term option "%s".', (string) $key));
            }
        }

        $name = $value['name'] ?? null;
        if (!is_string($name) || trim($name) === '') {
            throw new InvalidArgumentException('default_term.name is required and must be a non-empty string.');
        }
        $normalized = ['name' => trim($name)];

        if (array_key_exists('slug', $value) && $value['slug'] !== null && $value['slug'] !== '') {
            if (!is_string($value['slug'])) {
                throw new InvalidArgumentException('default_term.slug must be a string.');
            }
            $this->assertMachineKey($value['slug'], 200, 'default_term.slug');
            $normalized['slug'] = $value['slug'];
        }
        if (array_key_exists('description', $value) && $value['description'] !== null) {
            if (!is_string($value['description'])) {
                throw new InvalidArgumentException('default_term.description must be a string.');
            }
            $normalized['description'] = trim($value['description']);
        }

        return $normalized;
    }

    /** @return array<string,string> */
    private function objectTermArgs(mixed $value): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException('args must be a bounded object/map.');
        }
        foreach (array_keys($value) as $key) {
            if (!is_string($key) || !in_array($key, self::OBJECT_TERM_ARG_KEYS, true)) {
                throw new InvalidArgumentException(sprintf('Unsupported object-term argument "%s".', (string) $key));
            }
        }

        $normalized = [];
        if (array_key_exists('orderby', $value)) {
            if (!is_string($value['orderby']) || !in_array($value['orderby'], self::OBJECT_TERM_ORDERBY_VALUES, true)) {
                throw new InvalidArgumentException('args.orderby must be an allowlisted term-order field.');
            }
            $normalized['orderby'] = $value['orderby'];
        }
        if (array_key_exists('order', $value)) {
            if (!is_string($value['order'])) {
                throw new InvalidArgumentException('args.order must be ASC or DESC.');
            }
            $order = strtoupper($value['order']);
            if (!in_array($order, ['ASC', 'DESC'], true)) {
                throw new InvalidArgumentException('args.order must be ASC or DESC.');
            }
            $normalized['order'] = $order;
        }
        if (array_key_exists('fields', $value)) {
            if (!is_string($value['fields']) || !in_array($value['fields'], self::OBJECT_TERM_FIELDS_VALUES, true)) {
                throw new InvalidArgumentException('args.fields must be an allowlisted wp_get_object_terms fields mode.');
            }
            $normalized['fields'] = $value['fields'];
        }

        return $normalized;
    }

    /** @return array<string,string> */
    private function runtimeProviderIds(mixed $value): array
    {
        if (!is_array($value) || array_is_list($value) && $value !== []) {
            throw new InvalidArgumentException('runtime_providers must be an object/map of registered provider IDs.');
        }

        $allowed = ['rest_controller', 'meta_box', 'meta_box_sanitize', 'term_count'];
        $normalized = [];
        foreach ($value as $slot => $providerId) {
            if (!is_string($slot) || !in_array($slot, $allowed, true) || !is_string($providerId) || trim($providerId) === '') {
                throw new InvalidArgumentException('runtime_providers contains an unsupported slot or invalid provider ID.');
            }
            $providerId = trim($providerId);
            $available = match ($slot) {
                'rest_controller' => $this->runtimeProviders->hasRestController($providerId),
                'meta_box' => $this->runtimeProviders->hasMetaBoxProvider($providerId),
                'meta_box_sanitize' => $this->runtimeProviders->hasMetaBoxSanitizeProvider($providerId),
                'term_count' => $this->runtimeProviders->hasTermCountProvider($providerId),
            };
            if (!$available) {
                throw new InvalidArgumentException(sprintf('Taxonomy runtime provider "%s" is not registered for slot "%s".', $providerId, $slot));
            }
            $normalized[$slot] = $providerId;
        }
        return $normalized;
    }
}
