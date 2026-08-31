<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final class FieldTypeRegistry
{
    /** @var array<string,FieldTypeDescriptor> */
    private array $types = [];

    public function __construct()
    {
        foreach (self::defaults() as $descriptor) {
            $this->register($descriptor);
        }
    }

    public function register(FieldTypeDescriptor $descriptor): void
    {
        if (isset($this->types[$descriptor->key])) {
            throw new InvalidArgumentException(sprintf('Field type "%s" is already registered.', $descriptor->key));
        }
        $this->types[$descriptor->key] = $descriptor;
    }

    public function has(string $key): bool
    {
        return isset($this->types[$key]);
    }

    public function get(string $key): FieldTypeDescriptor
    {
        if (!isset($this->types[$key])) {
            throw new InvalidArgumentException(sprintf('Unknown Fields type "%s".', $key));
        }
        return $this->types[$key];
    }

    /** @return list<FieldTypeDescriptor> */
    public function all(): array
    {
        return array_values($this->types);
    }

    /** @return list<FieldTypeDescriptor> */
    private static function defaults(): array
    {
        $d = static fn (
            string $key,
            string $label,
            string $category,
            string $logicalType,
            string $editorControl,
            string $repeatabilityMode = 'supported',
            bool $sortableClones = true,
            bool $storesValue = true,
            bool $enhanced = false,
            array $modes = [],
        ): FieldTypeDescriptor => new FieldTypeDescriptor(
            key: $key,
            label: $label,
            category: $category,
            logicalType: $logicalType,
            editorControl: $editorControl,
            repeatabilityMode: $repeatabilityMode,
            sortableClones: $sortableClones,
            storesValue: $storesValue,
            enhancedControlRequired: $enhanced,
            modes: $modes,
        );

        return [
            $d('text', 'Text', 'basic', 'string', 'wpe-text', modes: ['default', 'search']),
            $d('textarea', 'Textarea', 'basic', 'string', 'wpe-textarea'),
            $d('email', 'Email', 'basic', 'email', 'wpe-email'),
            $d('url', 'URL', 'basic', 'url', 'wpe-url'),
            $d('password', 'Password / Secret Reference', 'basic', 'secret_reference', 'wpe-secret'),
            $d('hidden', 'Hidden', 'basic', 'mixed', 'wpe-hidden'),
            $d('number', 'Number', 'numeric', 'number', 'wpe-number', modes: ['decimal', 'integer', 'days']),
            $d('range', 'Range / Slider', 'numeric', 'number', 'wpe-slider', enhanced: true, modes: ['range', 'slider']),
            $d('true_false', 'True / False', 'choice', 'boolean', 'wpe-toggle'),
            $d('switcher', 'Switcher', 'choice', 'boolean', 'wpe-switch'),
            $d('checkbox', 'Checkbox', 'choice', 'boolean', 'wpe-checkbox'),
            $d('checkbox_list', 'Checkbox List', 'choice', 'list<scalar>', 'wpe-checkbox-list'),
            $d('radio', 'Radio', 'choice', 'scalar', 'wpe-radio-group'),
            $d('button_group', 'Button Set', 'choice', 'scalar|list<scalar>', 'wpe-button-set'),
            $d('select', 'Select', 'choice', 'scalar|list<scalar>', 'wpe-select', enhanced: true, modes: ['single', 'multiple']),
            $d('select_advanced', 'Advanced Select', 'choice', 'scalar|list<scalar>', 'wpe-combobox', enhanced: true, modes: ['single', 'multiple']),
            $d('autocomplete', 'Autocomplete', 'choice', 'scalar', 'wpe-autocomplete', enhanced: true),
            $d('combobox', 'Combobox', 'choice', 'scalar|list<scalar>', 'wpe-combobox', enhanced: true),
            $d('text_list', 'Text List', 'choice', 'list<string>', 'wpe-text-list'),
            $d('key_value', 'Key / Value', 'structured', 'list<object>', 'wpe-key-value'),
            $d('date', 'Date', 'date_time', 'date_or_period', 'wpe-date-picker', enhanced: true, modes: ['day', 'week', 'month']),
            $d('time', 'Time', 'date_time', 'local_time', 'wpe-time-picker', enhanced: true),
            $d('datetime', 'Date & Time', 'date_time', 'instant', 'wpe-datetime-picker', enhanced: true),
            $d('advanced_date', 'Advanced Date / Range', 'date_time', 'date_range_or_schedule', 'wpe-date-range-picker', enhanced: true),
            $d('wysiwyg', 'WYSIWYG', 'content', 'rich_text', 'wordpress-editor', enhanced: true, modes: ['visual_text', 'visual', 'text']),
            $d('block_editor', 'Gutenberg / Block Content', 'content', 'block_content', 'wordpress-block-editor', enhanced: true),
            $d('code_editor', 'Code Editor', 'content', 'source_text', 'wpe-code-editor', enhanced: true, modes: ['html', 'css', 'javascript', 'json', 'php', 'text']),
            $d('markdown', 'Markdown', 'content', 'markdown', 'wpe-markdown-editor', enhanced: true),
            $d('oembed', 'oEmbed', 'content', 'url', 'wpe-oembed'),
            $d('link', 'Link', 'content', 'link_object', 'wpe-link-editor'),
            $d('page_link', 'Page Link', 'content', 'entity_reference', 'wpe-entity-picker', enhanced: true),
            $d('image', 'Image', 'media', 'media_reference', 'wordpress-media-picker', enhanced: true),
            $d('gallery', 'Gallery', 'media', 'list<media_reference>', 'wpe-gallery', 'container_managed', true, true, true),
            $d('file', 'File', 'media', 'media_reference', 'wordpress-media-picker', enhanced: true),
            $d('file_advanced', 'Files', 'media', 'list<media_reference>', 'wpe-media-list', 'container_managed', true, true, true),
            $d('media', 'Media', 'media', 'media_reference', 'wordpress-media-picker', enhanced: true),
            $d('video', 'Video', 'media', 'media_reference', 'wpe-video-picker', enhanced: true, modes: ['library', 'upload']),
            $d('post_object', 'Post Object', 'entity', 'entity_reference', 'wpe-entity-picker', enhanced: true, modes: ['select', 'radio']),
            $d('posts', 'Posts', 'entity', 'list<entity_reference>', 'wpe-entity-picker', enhanced: true, modes: ['select', 'multiselect', 'checkboxes']),
            $d('relationship', 'Relationship', 'entity', 'relation_reference', 'wpe-relation-picker', enhanced: true),
            $d('taxonomy', 'Taxonomy', 'entity', 'entity_reference|list<entity_reference>', 'wpe-term-picker', enhanced: true, modes: ['select', 'multiselect', 'checkboxes', 'radio']),
            $d('user', 'User', 'entity', 'entity_reference|list<entity_reference>', 'wpe-user-picker', enhanced: true, modes: ['select', 'multiselect', 'checkboxes', 'radio']),
            $d('nav_menu', 'Navigation Menu', 'entity', 'entity_reference', 'wpe-nav-menu-picker', enhanced: true),
            $d('sidebar', 'Sidebar', 'entity', 'entity_reference', 'wpe-sidebar-picker', enhanced: true),
            $d('group', 'Group', 'composition', 'object|list<object>', 'wpe-group', modes: ['block', 'table', 'row']),
            $d('repeater', 'Repeater', 'composition', 'list<object>', 'wpe-repeater', 'container_managed', true, true, true, ['block', 'table', 'row']),
            $d('flexible_content', 'Flexible Content', 'composition', 'list<layout>', 'wpe-flexible-content', 'container_managed', true, true, true),
            $d('clone', 'Clone', 'composition', 'schema_reference', 'wpe-clone', 'container_managed', true, true, true),
            $d('heading', 'Heading', 'layout', 'none', 'wpe-heading', 'inapplicable', false, false),
            $d('separator', 'Divider / Separator', 'layout', 'none', 'wpe-divider', 'inapplicable', false, false),
            $d('tab', 'Tab', 'layout', 'none', 'wpe-tab', 'inapplicable', false, false),
            $d('accordion', 'Accordion', 'layout', 'none', 'wpe-accordion', 'inapplicable', false, false),
            $d('message', 'Message', 'layout', 'none', 'wpe-message', 'inapplicable', false, false),
            $d('color', 'Color', 'visual', 'color', 'wpe-color-picker', enhanced: true, modes: ['hex', 'hexa', 'rgb', 'rgba', 'hsl', 'hsla']),
            $d('gradient', 'Gradient', 'visual', 'gradient', 'wpe-gradient-editor', enhanced: true),
            $d('icon', 'Icon', 'visual', 'icon_reference', 'wpe-icon-picker', enhanced: true),
            $d('image_select', 'Image Select', 'visual', 'scalar|list<scalar>', 'wpe-image-choice', enhanced: true),
            $d('background', 'Background', 'visual', 'background_object', 'wpe-background-editor', enhanced: true),
            $d('dimensions', 'Dimensions', 'visual', 'dimensions_object', 'wpe-dimensions'),
            $d('spacing', 'Spacing', 'visual', 'spacing_object', 'wpe-spacing'),
            $d('border', 'Border', 'visual', 'border_object', 'wpe-border-editor'),
            $d('box_shadow', 'Box Shadow', 'visual', 'shadow_object', 'wpe-shadow-editor'),
            $d('typography', 'Typography', 'visual', 'typography_object', 'wpe-typography-editor'),
            $d('palette', 'Palette', 'visual', 'list<color>', 'wpe-palette', enhanced: true),
            $d('phone', 'Phone', 'specialized', 'phone', 'wpe-phone'),
            $d('currency', 'Currency', 'specialized', 'money', 'wpe-currency'),
            $d('unit', 'Unit Value', 'specialized', 'quantity', 'wpe-unit'),
            $d('angle', 'Angle', 'specialized', 'number', 'wpe-angle'),
        ];
    }
}
