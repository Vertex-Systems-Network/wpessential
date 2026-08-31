<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomPostTypes;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinition;
use WPEssential\Platform\WordPress\Registrations\RegistrationKind;

final class CustomPostTypeDefinitionProjector
{
    public const DEFINITION_TYPE = 'post_type';
    public const OWNER_SURFACE_ID = 1;

    /** @var list<string> */
    private const TOP_LEVEL_KEYS = [
        'post_type_key', 'name', 'singular_name', 'description', 'automatic_labels', 'labels',
        'public', 'hierarchical', 'exclude_from_search', 'publicly_queryable', 'show_ui',
        'show_in_menu', 'show_in_nav_menus', 'show_in_admin_bar', 'show_in_rest', 'rest_base',
        'rest_namespace', 'supports', 'menu_position', 'menu_icon', 'capability_type', 'capabilities',
        'map_meta_cap', 'taxonomies', 'has_archive', 'rewrite', 'query_var', 'can_export',
        'delete_with_user',
    ];

    /** @var list<string> */
    private const SUPPORT_KEYS = [
        'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields',
        'comments', 'revisions', 'page-attributes', 'post-formats', 'autosave',
    ];

    /** @var list<string> */
    private const LABEL_KEYS = [
        'add_new', 'add_new_item', 'edit_item', 'new_item', 'view_item', 'view_items', 'search_items',
        'not_found', 'not_found_in_trash', 'parent_item_colon', 'all_items', 'archives', 'attributes',
        'insert_into_item', 'uploaded_to_this_item', 'featured_image', 'set_featured_image',
        'remove_featured_image', 'use_featured_image', 'menu_name', 'name_admin_bar', 'filter_items_list',
        'filter_by_date', 'items_list_navigation', 'items_list', 'item_published',
        'item_published_privately', 'item_reverted_to_draft', 'item_trashed', 'item_scheduled',
        'item_updated', 'item_link', 'item_link_description', 'template_name',
    ];

    /** @var list<string> */
    private const RESERVED_POST_TYPES = [
        'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css',
        'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template',
        'wp_template_part', 'wp_global_styles', 'wp_navigation', 'wp_font_family', 'wp_font_face',
    ];

    public function project(Definition $definition): RegistrationDefinition
    {
        $this->assertDefinition($definition);
        $payload = $definition->payload;
        $this->assertKnownTopLevelKeys($payload);

        $key = $this->requiredString($payload, 'post_type_key');
        $this->assertMachineKey($key, 20, 'Post type key');
        if (in_array($key, self::RESERVED_POST_TYPES, true)) {
            throw new InvalidArgumentException(sprintf('Post type key "%s" is reserved by WordPress.', $key));
        }

        $name = $this->requiredString($payload, 'name');
        $singularName = $this->requiredString($payload, 'singular_name');
        $args = [
            'labels' => $this->labels($name, $singularName, $payload['labels'] ?? []),
            'public' => $this->boolValue($payload, 'public', true),
            'hierarchical' => $this->boolValue($payload, 'hierarchical', false),
            'show_in_rest' => $this->boolValue($payload, 'show_in_rest', true),
            'supports' => $this->supports($payload['supports'] ?? ['title', 'editor']),
            'menu_icon' => $this->menuIcon($payload['menu_icon'] ?? 'dashicons-admin-post'),
            'has_archive' => $this->archive($payload['has_archive'] ?? false),
            'rewrite' => $this->rewrite($payload['rewrite'] ?? true),
            'can_export' => $this->boolValue($payload, 'can_export', true),
        ];

        if (array_key_exists('description', $payload)) {
            if (!is_string($payload['description'])) {
                throw new InvalidArgumentException('CPT description must be a string.');
            }
            $args['description'] = trim($payload['description']);
        }
        if (array_key_exists('automatic_labels', $payload) && !is_bool($payload['automatic_labels'])) {
            throw new InvalidArgumentException('automatic_labels must be boolean.');
        }

        foreach (['exclude_from_search', 'publicly_queryable', 'show_ui', 'show_in_nav_menus', 'show_in_admin_bar', 'delete_with_user'] as $field) {
            $this->copyOptionalBool($payload, $args, $field);
        }
        if (array_key_exists('show_in_menu', $payload) && $payload['show_in_menu'] !== null) {
            $args['show_in_menu'] = $this->showInMenu($payload['show_in_menu']);
        }
        if (array_key_exists('rest_base', $payload) && $payload['rest_base'] !== null) {
            $args['rest_base'] = $this->routeSegment($payload['rest_base'], 'rest_base');
        }
        if (array_key_exists('rest_namespace', $payload) && $payload['rest_namespace'] !== null) {
            $args['rest_namespace'] = $this->restNamespace($payload['rest_namespace']);
        }
        if (array_key_exists('menu_position', $payload) && $payload['menu_position'] !== null) {
            if (!is_int($payload['menu_position']) || $payload['menu_position'] < 0 || $payload['menu_position'] > 999) {
                throw new InvalidArgumentException('menu_position must be an integer from 0 to 999.');
            }
            $args['menu_position'] = $payload['menu_position'];
        }
        if (array_key_exists('capability_type', $payload)) {
            $args['capability_type'] = $this->capabilityType($payload['capability_type']);
        }
        if (array_key_exists('capabilities', $payload)) {
            $args['capabilities'] = $this->capabilities($payload['capabilities']);
        }
        $this->copyOptionalBool($payload, $args, 'map_meta_cap');
        if (array_key_exists('taxonomies', $payload)) {
            $args['taxonomies'] = $this->taxonomies($payload['taxonomies']);
        }
        if (array_key_exists('query_var', $payload)) {
            $args['query_var'] = $this->queryVar($payload['query_var']);
        }

        return new RegistrationDefinition(
            id: $definition->id,
            kind: RegistrationKind::PostType,
            key: $key,
            payload: $args,
            enabled: true,
            revision: $definition->revision,
        );
    }

    private function assertDefinition(Definition $definition): void
    {
        if ($definition->type !== self::DEFINITION_TYPE) {
            throw new InvalidArgumentException('CPT projector only accepts post_type definitions.');
        }
        if ($definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('CPT definitions must be owned by canonical Surface 1.');
        }
        if ($definition->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Only published CPT definitions can be compiled for runtime registration.');
        }
    }

    /** @param array<string,mixed> $payload */
    private function assertKnownTopLevelKeys(array $payload): void
    {
        foreach (array_keys($payload) as $key) {
            if (!in_array($key, self::TOP_LEVEL_KEYS, true)) {
                throw new InvalidArgumentException(sprintf('Unsupported CPT definition field "%s".', $key));
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
    private function labels(string $name, string $singularName, mixed $overrides): array
    {
        if (!is_array($overrides)) {
            throw new InvalidArgumentException('CPT labels must be an object/map.');
        }
        $labels = ['name' => $name, 'singular_name' => $singularName];
        foreach ($overrides as $key => $value) {
            if (!is_string($key) || !in_array($key, self::LABEL_KEYS, true)) {
                throw new InvalidArgumentException(sprintf('Unsupported CPT label "%s".', (string) $key));
            }
            if (!is_string($value)) {
                throw new InvalidArgumentException(sprintf('CPT label "%s" must be a string.', $key));
            }
            $value = trim($value);
            if ($value !== '') {
                $labels[$key] = $value;
            }
        }
        return $labels;
    }

    /** @return list<string> */
    private function supports(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException('CPT supports must be a list.');
        }
        $supports = [];
        foreach ($value as $support) {
            if (!is_string($support) || !in_array($support, self::SUPPORT_KEYS, true)) {
                throw new InvalidArgumentException(sprintf('Unsupported CPT editor support "%s".', is_scalar($support) ? (string) $support : get_debug_type($support)));
            }
            if (!in_array($support, $supports, true)) {
                $supports[] = $support;
            }
        }
        return $supports;
    }

    private function menuIcon(mixed $value): string
    {
        if (!is_string($value) || ($value !== 'none' && preg_match('/^dashicons-[a-z0-9-]+$/', $value) !== 1)) {
            throw new InvalidArgumentException('menu_icon must be none or a Dashicons identifier in this runtime slice.');
        }
        return $value;
    }

    private function archive(mixed $value): bool|string
    {
        if (is_bool($value)) {
            return $value;
        }
        return $this->routeSegment($value, 'has_archive');
    }

    /** @return bool|array<string,bool|int|string> */
    private function rewrite(mixed $value): bool|array
    {
        if (is_bool($value)) {
            return $value;
        }
        if (!is_array($value)) {
            throw new InvalidArgumentException('rewrite must be boolean or a typed rewrite map.');
        }
        $allowed = ['slug', 'with_front', 'feeds', 'pages', 'ep_mask'];
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

    private function showInMenu(mixed $value): bool|string
    {
        if (is_bool($value)) {
            return $value;
        }
        if (!is_string($value) || preg_match('/^[A-Za-z0-9_.?=&%-]{1,191}$/', $value) !== 1) {
            throw new InvalidArgumentException('show_in_menu parent must be a validated admin parent path.');
        }
        return $value;
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

    private function capabilityType(mixed $value): string|array
    {
        if (is_string($value)) {
            $this->assertMachineKey($value, 64, 'capability_type');
            return $value;
        }
        if (!is_array($value) || !array_is_list($value) || count($value) !== 2 || !is_string($value[0]) || !is_string($value[1])) {
            throw new InvalidArgumentException('capability_type must be a string or singular/plural pair.');
        }
        $this->assertMachineKey($value[0], 64, 'capability_type singular');
        $this->assertMachineKey($value[1], 64, 'capability_type plural');
        return [$value[0], $value[1]];
    }

    /** @return array<string,string> */
    private function capabilities(mixed $value): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException('capabilities must be a named capability map.');
        }
        $normalized = [];
        foreach ($value as $key => $capability) {
            if (!is_string($key) || !is_string($capability)) {
                throw new InvalidArgumentException('capabilities keys and values must be strings.');
            }
            $this->assertMachineKey($key, 64, 'capability map key');
            $this->assertMachineKey($capability, 64, 'capability name');
            $normalized[$key] = $capability;
        }
        return $normalized;
    }

    /** @return list<string> */
    private function taxonomies(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException('taxonomies must be a list of runtime taxonomy keys.');
        }
        $taxonomies = [];
        foreach ($value as $taxonomy) {
            if (!is_string($taxonomy)) {
                throw new InvalidArgumentException('taxonomy keys must be strings.');
            }
            $this->assertMachineKey($taxonomy, 32, 'taxonomy key');
            if (!in_array($taxonomy, $taxonomies, true)) {
                $taxonomies[] = $taxonomy;
            }
        }
        return $taxonomies;
    }

    private function queryVar(mixed $value): bool|string
    {
        if (is_bool($value)) {
            return $value;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException('query_var must be boolean or a custom query variable.');
        }
        $this->assertMachineKey($value, 64, 'query_var');
        return $value;
    }
}
