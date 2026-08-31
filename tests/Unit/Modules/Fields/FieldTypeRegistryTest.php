<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldPresetRegistry;
use WPEssential\Modules\Fields\FieldTypeRegistry;

final class FieldTypeRegistryTest extends TestCase
{
    public function testContainsCoreUserRequestedAndExtendedFieldFamilies(): void
    {
        $types = new FieldTypeRegistry();

        foreach ([
            'text', 'textarea', 'email', 'range', 'date', 'time', 'datetime', 'number',
            'group', 'repeater', 'color', 'code_editor', 'wysiwyg', 'block_editor', 'video',
            'oembed', 'select', 'checkbox_list', 'radio', 'button_group', 'post_object',
            'taxonomy', 'user', 'gallery', 'file', 'relationship', 'gradient', 'icon', 'phone',
        ] as $key) {
            self::assertTrue($types->has($key), $key);
        }
    }

    public function testEnhancedPickerPolicyDoesNotRelyOnBrowserNativePickers(): void
    {
        $types = new FieldTypeRegistry();

        foreach (['date', 'time', 'datetime', 'range', 'color'] as $key) {
            self::assertTrue($types->get($key)->enhancedControlRequired, $key);
            self::assertStringStartsWith('wpe-', $types->get($key)->editorControl);
        }
    }

    public function testBuilderPresetsResolveAliasesWithoutCreatingDuplicateStorageTypes(): void
    {
        $types = new FieldTypeRegistry();
        $presets = new FieldPresetRegistry($types);

        self::assertSame('text', $presets->get('search')->canonicalType);
        self::assertSame('range', $presets->get('slider')->canonicalType);
        self::assertSame('select', $presets->get('multi_select')->canonicalType);
        self::assertSame('wysiwyg', $presets->get('tinymce')->canonicalType);
        self::assertSame('date', $presets->get('week')->canonicalType);
        self::assertSame('date', $presets->get('month')->canonicalType);
        self::assertSame('heading', $presets->get('section')->canonicalType);
    }
}
