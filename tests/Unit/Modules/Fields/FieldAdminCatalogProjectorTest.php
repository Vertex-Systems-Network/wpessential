<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldAdminCatalogProjector;
use WPEssential\Modules\Fields\FieldCatalogService;
use WPEssential\Modules\Fields\FieldPresetRegistry;
use WPEssential\Modules\Fields\FieldTypeRegistry;

final class FieldAdminCatalogProjectorTest extends TestCase
{
    public function testSimpleV1TypesAreAvailableAndOwnerOrContainerTypesFailClosed(): void
    {
        $types = new FieldTypeRegistry();
        $catalog = (new FieldCatalogService($types, new FieldPresetRegistry($types)))->catalog();
        $projected = (new FieldAdminCatalogProjector())->project($catalog);

        $byKey = [];
        foreach ($projected['types'] as $type) {
            $byKey[$type['key']] = $type;
        }

        foreach (['text', 'textarea', 'number', 'checkbox', 'date', 'time', 'datetime'] as $key) {
            self::assertTrue($byKey[$key]['admin_available']);
            self::assertNull($byKey[$key]['admin_unavailable_reason']);
        }

        foreach (['post_object', 'taxonomy', 'user', 'repeater', 'group', 'tab'] as $key) {
            self::assertFalse($byKey[$key]['admin_available']);
            self::assertIsString($byKey[$key]['admin_unavailable_reason']);
            self::assertNotSame('', $byKey[$key]['admin_unavailable_reason']);
        }

        self::assertSame('preserve_read_only', $projected['admin_policy']['unsupported_behavior']);
        self::assertTrue($projected['admin_policy']['persist_via_abilities_only']);
        self::assertFalse($projected['admin_policy']['persisted_key_mutation']);
        self::assertFalse($projected['admin_policy']['persisted_uuid_mutation']);
    }

    public function testProjectionKeepsCanonicalCatalogLabelsAndTypeMetadata(): void
    {
        $types = new FieldTypeRegistry();
        $catalog = (new FieldCatalogService($types, new FieldPresetRegistry($types)))->catalog();
        $projected = (new FieldAdminCatalogProjector())->project($catalog);

        $canonical = [];
        foreach ($catalog['types'] as $type) {
            $canonical[$type['key']] = $type;
        }

        foreach ($projected['types'] as $type) {
            $key = $type['key'];
            self::assertSame($canonical[$key]['label'], $type['label']);
            self::assertSame($canonical[$key]['category'], $type['category']);
            self::assertSame($canonical[$key]['editor_strategy'], $type['editor_strategy']);
            self::assertSame($canonical[$key]['repeatability'], $type['repeatability']);
        }
    }
}
