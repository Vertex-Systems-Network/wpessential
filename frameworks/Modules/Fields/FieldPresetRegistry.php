<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final class FieldPresetRegistry
{
    /** @var array<string,FieldPresetDescriptor> */
    private array $presets = [];

    public function __construct(private readonly FieldTypeRegistry $types)
    {
        foreach (self::defaults() as $preset) {
            $this->register($preset);
        }
    }

    public function register(FieldPresetDescriptor $preset): void
    {
        if (!$this->types->has($preset->canonicalType)) {
            throw new InvalidArgumentException(sprintf(
                'Field preset "%s" references unknown canonical type "%s".',
                $preset->key,
                $preset->canonicalType,
            ));
        }
        if (isset($this->presets[$preset->key])) {
            throw new InvalidArgumentException(sprintf('Field preset "%s" is already registered.', $preset->key));
        }
        $this->presets[$preset->key] = $preset;
    }

    public function has(string $key): bool
    {
        return isset($this->presets[$key]);
    }

    public function get(string $key): FieldPresetDescriptor
    {
        if (!isset($this->presets[$key])) {
            throw new InvalidArgumentException(sprintf('Unknown Fields preset "%s".', $key));
        }
        return $this->presets[$key];
    }

    /** @return list<FieldPresetDescriptor> */
    public function all(): array
    {
        return array_values($this->presets);
    }

    /** @return list<FieldPresetDescriptor> */
    private static function defaults(): array
    {
        return [
            new FieldPresetDescriptor('search', 'Search', 'text', ['control_mode' => 'search']),
            new FieldPresetDescriptor('slider', 'Slider', 'range', ['control_mode' => 'slider']),
            new FieldPresetDescriptor('multi_select', 'Multi Select', 'select', ['control_mode' => 'multiple', 'multiple' => true, 'searchable' => true]),
            new FieldPresetDescriptor('week', 'Week', 'date', ['control_mode' => 'week', 'precision' => 'week', 'storage_format' => 'ISO-8601-week']),
            new FieldPresetDescriptor('month', 'Month', 'date', ['control_mode' => 'month', 'precision' => 'month', 'storage_format' => 'YYYY-MM']),
            new FieldPresetDescriptor('duration_days', 'Duration (Days)', 'number', ['control_mode' => 'days', 'integer' => true, 'step' => 1, 'unit' => 'day']),
            new FieldPresetDescriptor('weekdays', 'Weekdays', 'checkbox_list', ['choice_source' => 'site_weekdays']),
            new FieldPresetDescriptor('section', 'Section', 'heading', ['control_mode' => 'section']),
            new FieldPresetDescriptor('tinymce', 'TinyMCE / WordPress Editor', 'wysiwyg', ['editor_adapter' => 'wordpress_editor', 'control_mode' => 'visual_text']),
            new FieldPresetDescriptor('color_alpha', 'Color + Alpha', 'color', ['alpha' => true, 'return_format' => 'rgba']),
            new FieldPresetDescriptor('video_upload', 'Video — Media Library / Upload', 'video', ['control_mode' => 'upload']),
            new FieldPresetDescriptor('video_social', 'Video — Social / Embed URL', 'oembed', ['provider_family' => 'video']),
            new FieldPresetDescriptor('wp_post_select', 'WordPress Posts — Select', 'post_object', ['control_mode' => 'select', 'multiple' => false]),
            new FieldPresetDescriptor('wp_post_multiselect', 'WordPress Posts — Multi Select', 'posts', ['control_mode' => 'multiselect', 'multiple' => true]),
            new FieldPresetDescriptor('wp_post_checkboxes', 'WordPress Posts — Checkboxes', 'posts', ['control_mode' => 'checkboxes', 'multiple' => true]),
            new FieldPresetDescriptor('wp_post_radio', 'WordPress Posts — Radio', 'post_object', ['control_mode' => 'radio', 'multiple' => false]),
            new FieldPresetDescriptor('wp_term_multiselect', 'WordPress Terms — Multi Select', 'taxonomy', ['control_mode' => 'multiselect', 'multiple' => true]),
            new FieldPresetDescriptor('wp_user_multiselect', 'WordPress Users — Multi Select', 'user', ['control_mode' => 'multiselect', 'multiple' => true]),
        ];
    }
}
