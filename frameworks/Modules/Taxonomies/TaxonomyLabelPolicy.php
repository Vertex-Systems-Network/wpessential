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
        $labels = [
            'name' => $name,
            'singular_name' => $singularName,
            'menu_name' => $name,
            'search_items' => self::format('Search %s', $name),
            'all_items' => self::format('All %s', $name),
            'edit_item' => self::format('Edit %s', $singularName),
            'view_item' => self::format('View %s', $singularName),
            'update_item' => self::format('Update %s', $singularName),
            'add_new_item' => self::format('Add New %s', $singularName),
            'new_item_name' => self::format('New %s Name', $singularName),
            'not_found' => self::format('No %s found', $name),
            'no_terms' => self::format('No %s', $name),
            'items_list_navigation' => self::format('%s list navigation', $name),
            'items_list' => self::format('%s list', $name),
            'back_to_items' => self::format('Go to %s', $name),
            'item_link' => self::format('%s Link', $singularName),
            'item_link_description' => self::format('A link to a %s', $singularName),
        ];

        if ($hierarchical) {
            $labels['parent_item'] = self::format('Parent %s', $singularName);
            $labels['parent_item_colon'] = self::format('Parent %s:', $singularName);
            return $labels;
        }

        $labels['popular_items'] = self::format('Popular %s', $name);
        $labels['separate_items_with_commas'] = self::format('Separate %s with commas', $name);
        $labels['add_or_remove_items'] = self::format('Add or remove %s', $name);
        $labels['choose_from_most_used'] = self::format('Choose from the most used %s', $name);
        return $labels;
    }

    private static function format(string $template, string $value): string
    {
        $translated = function_exists('__') ? __($template, 'wpessential') : $template;
        return sprintf($translated, $value);
    }
}
