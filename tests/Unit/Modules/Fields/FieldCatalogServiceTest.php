<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldCatalogService;
use WPEssential\Modules\Fields\FieldPresetRegistry;
use WPEssential\Modules\Fields\FieldTypeRegistry;

final class FieldCatalogServiceTest extends TestCase
{
    public function testCatalogExposesRequestedBuilderPresetsWithoutDuplicateCanonicalTypes(): void
    {
        $types = new FieldTypeRegistry();
        $catalog = (new FieldCatalogService($types, new FieldPresetRegistry($types)))->catalog();
        $presets = [];
        foreach ($catalog['presets'] as $preset) {
            $presets[$preset['key']] = $preset;
        }

        self::assertSame('text', $presets['search']['canonical_type']);
        self::assertSame('range', $presets['slider']['canonical_type']);
        self::assertSame('select', $presets['multi_select']['canonical_type']);
        self::assertSame('date', $presets['week']['canonical_type']);
        self::assertSame('date', $presets['month']['canonical_type']);
        self::assertSame('heading', $presets['section']['canonical_type']);
        self::assertSame('wysiwyg', $presets['tinymce']['canonical_type']);
    }

    public function testCatalogPublishesCloneSortAndEnhancedPickerCapabilities(): void
    {
        $types = new FieldTypeRegistry();
        $catalog = (new FieldCatalogService($types, new FieldPresetRegistry($types)))->catalog();
        $byKey = [];
        foreach ($catalog['types'] as $type) {
            $byKey[$type['key']] = $type;
        }

        self::assertTrue($byKey['text']['repeatability']['cloneable']);
        self::assertTrue($byKey['text']['repeatability']['sortable']);
        self::assertTrue($byKey['group']['repeatability']['cloneable']);
        self::assertTrue($byKey['repeater']['repeatability']['container_managed']);
        self::assertFalse($byKey['heading']['repeatability']['cloneable']);

        foreach (['date', 'time', 'datetime', 'range', 'color'] as $key) {
            self::assertSame('enhanced', $byKey[$key]['editor_strategy']);
            self::assertFalse($byKey[$key]['native_browser_picker']);
        }
        self::assertFalse($catalog['policies']['browser_native_picker_is_product_contract']);
        self::assertFalse($catalog['policies']['code_execution_allowed']);
    }
}
