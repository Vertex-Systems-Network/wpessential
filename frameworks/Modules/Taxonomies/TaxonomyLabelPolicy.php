<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final class TaxonomyLabelPolicy
{
    public const GENERATION_COMMON = 'common';
    public const GENERATION_HIERARCHICAL = 'hierarchical';
    public const GENERATION_FLAT = 'flat';
    public const GENERATION_WORDPRESS = 'wordpress';

    /** @var list<string> */
    private const OVERRIDE_KEYS = [
        'menu_name', 'search_items', 'popular_items', 'all_items', 'parent_item', 'parent_item_colon',
        'name_field_description', 'slug_field_description', 'parent_field_description',
        'desc_field_description', 'edit_item', 'view_item', 'update_item', 'add_new_item',
        'new_item_name', 'template_name', 'separate_items_with_commas', 'add_or_remove_items',
        'choose_from_most_used', 'not_found', 'no_terms', 'filter_by_item', 'items_list_navigation',
        'items_list', 'most_used', 'back_to_items', 'item_link', 'item_link_description',
    ];

    /** @var list<string> */
    private const COMMON_GENERATED_KEYS = [
        'menu_name', 'search_items', 'all_items', 'edit_item', 'view_item', 'update_item',
        'add_new_item', 'new_item_name', 'not_found', 'no_terms', 'items_list_navigation',
        'items_list', 'back_to_items', 'item_link', 'item_link_description',
    ];

    /** @var list<string> */
    private const HIERARCHICAL_GENERATED_KEYS = ['parent_item', 'parent_item_colon'];

    /** @var list<string> */
    private const FLAT_GENERATED_KEYS = [
        'popular_items', 'separate_items_with_commas', 'add_or_remove_items', 'choose_from_most_used',
    ];

    /** @return list<string> */
    public static function overrideKeys(): array
    {
        return self::OVERRIDE_KEYS;
    }

    public static function generationMode(string $key): string
    {
        if (in_array($key, self::COMMON_GENERATED_KEYS, true)) {
            return self::GENERATION_COMMON;
        }
        if (in_array($key, self::HIERARCHICAL_GENERATED_KEYS, true)) {
            return self::GENERATION_HIERARCHICAL;
        }
        if (in_array($key, self::FLAT_GENERATED_KEYS, true)) {
            return self::GENERATION_FLAT;
        }
        return self::GENERATION_WORDPRESS;
    }

    /** @return array<string,string> */
    public static function compile(
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
            ? self::generated($name, $singularName, $hierarchical)
            : ['name' => $name, 'singular_name' => $singularName];

        foreach ($overrides as $key => $value) {
            if (!is_string($key) || !in_array($key, self::OVERRIDE_KEYS, true)) {
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
    private static function generated(string $name, string $singularName, bool $hierarchical): array
    {
        $templates = self::templates();
        $labels = [
            'name' => $name,
            'singular_name' => $singularName,
            'menu_name' => $name,
            'search_items' => sprintf($templates['search_items'], $name),
            'all_items' => sprintf($templates['all_items'], $name),
            'edit_item' => sprintf($templates['edit_item'], $singularName),
            'view_item' => sprintf($templates['view_item'], $singularName),
            'update_item' => sprintf($templates['update_item'], $singularName),
            'add_new_item' => sprintf($templates['add_new_item'], $singularName),
            'new_item_name' => sprintf($templates['new_item_name'], $singularName),
            'not_found' => sprintf($templates['not_found'], $name),
            'no_terms' => sprintf($templates['no_terms'], $name),
            'items_list_navigation' => sprintf($templates['items_list_navigation'], $name),
            'items_list' => sprintf($templates['items_list'], $name),
            'back_to_items' => sprintf($templates['back_to_items'], $name),
            'item_link' => sprintf($templates['item_link'], $singularName),
            'item_link_description' => sprintf($templates['item_link_description'], $singularName),
        ];

        if ($hierarchical) {
            $labels['parent_item'] = sprintf($templates['parent_item'], $singularName);
            $labels['parent_item_colon'] = sprintf($templates['parent_item_colon'], $singularName);
            return $labels;
        }

        $labels['popular_items'] = sprintf($templates['popular_items'], $name);
        $labels['separate_items_with_commas'] = sprintf($templates['separate_items_with_commas'], $name);
        $labels['add_or_remove_items'] = sprintf($templates['add_or_remove_items'], $name);
        $labels['choose_from_most_used'] = sprintf($templates['choose_from_most_used'], $name);
        return $labels;
    }

    /** @return array<string,string> */
    private static function templates(): array
    {
        if (!function_exists('__')) {
            return [
                'search_items' => 'Search %s',
                'all_items' => 'All %s',
                'edit_item' => 'Edit %s',
                'view_item' => 'View %s',
                'update_item' => 'Update %s',
                'add_new_item' => 'Add New %s',
                'new_item_name' => 'New %s Name',
                'not_found' => 'No %s found',
                'no_terms' => 'No %s',
                'items_list_navigation' => '%s list navigation',
                'items_list' => '%s list',
                'back_to_items' => 'Go to %s',
                'item_link' => '%s Link',
                'item_link_description' => 'A link to a %s',
                'parent_item' => 'Parent %s',
                'parent_item_colon' => 'Parent %s:',
                'popular_items' => 'Popular %s',
                'separate_items_with_commas' => 'Separate %s with commas',
                'add_or_remove_items' => 'Add or remove %s',
                'choose_from_most_used' => 'Choose from the most used %s',
            ];
        }

        return [
            'search_items' => __('Search %s', 'wpessential'),
            'all_items' => __('All %s', 'wpessential'),
            'edit_item' => __('Edit %s', 'wpessential'),
            'view_item' => __('View %s', 'wpessential'),
            'update_item' => __('Update %s', 'wpessential'),
            'add_new_item' => __('Add New %s', 'wpessential'),
            'new_item_name' => __('New %s Name', 'wpessential'),
            'not_found' => __('No %s found', 'wpessential'),
            'no_terms' => __('No %s', 'wpessential'),
            'items_list_navigation' => __('%s list navigation', 'wpessential'),
            'items_list' => __('%s list', 'wpessential'),
            'back_to_items' => __('Go to %s', 'wpessential'),
            'item_link' => __('%s Link', 'wpessential'),
            'item_link_description' => __('A link to a %s', 'wpessential'),
            'parent_item' => __('Parent %s', 'wpessential'),
            'parent_item_colon' => __('Parent %s:', 'wpessential'),
            'popular_items' => __('Popular %s', 'wpessential'),
            'separate_items_with_commas' => __('Separate %s with commas', 'wpessential'),
            'add_or_remove_items' => __('Add or remove %s', 'wpessential'),
            'choose_from_most_used' => __('Choose from the most used %s', 'wpessential'),
        ];
    }
}
