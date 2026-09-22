<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentBlueprintCatalog;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;

final class DashboardWidgetComponentBlueprintCatalogTest extends TestCase
{
    public function testProvidesExactCanonicalSevenBlueprints(): void
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $blueprints = $catalog->all();

        self::assertCount(7, $blueprints);
        self::assertSame(
            [
                '31000000-0000-4000-8000-000000000001',
                '31000000-0000-4000-8000-000000000002',
                '31000000-0000-4000-8000-000000000003',
                '31000000-0000-4000-8000-000000000004',
                '31000000-0000-4000-8000-000000000005',
                '31000000-0000-4000-8000-000000000006',
                '31000000-0000-4000-8000-000000000007',
            ],
            array_map(static fn ($blueprint): string => $blueprint->id, $blueprints),
        );
        self::assertSame(
            [
                'dashboard-widgets.rich-text',
                'dashboard-widgets.kpi',
                'dashboard-widgets.chart',
                'dashboard-widgets.quick-links',
                'dashboard-widgets.announcement',
                'dashboard-widgets.support-onboarding',
                'dashboard-widgets.icon-link',
            ],
            array_map(static fn ($blueprint): string => $blueprint->componentType, $blueprints),
        );

        foreach ($blueprints as $blueprint) {
            self::assertSame(DashboardWidgetComponentBlueprintCatalog::REVISION, $blueprint->revision);
            self::assertSame(DashboardWidgetDefinition::OWNER_SURFACE_ID, $blueprint->ownerSurfaceId);
            self::assertSame([], $blueprint->dependencyIds);
            self::assertSame([], $blueprint->assetHandles);
        }

        self::assertSame(['content' => 'string'], $catalog->forContentType('rich_text')?->bindingSchema);
        self::assertSame(['label' => 'string', 'value' => 'string'], $catalog->forContentType('kpi')?->bindingSchema);
        self::assertSame(['labels' => 'string_list', 'values' => 'int_list'], $catalog->forContentType('chart')?->bindingSchema);
        self::assertSame(['labels' => 'string_list', 'urls' => 'string_list'], $catalog->forContentType('quick_links')?->bindingSchema);
        self::assertSame(['title' => 'string', 'text' => 'string'], $catalog->forContentType('announcement')?->bindingSchema);
        self::assertSame(['title' => 'string', 'text' => 'string'], $catalog->forContentType('support_onboarding')?->bindingSchema);
        self::assertSame(['icon' => 'string', 'label' => 'string', 'url' => 'string'], $catalog->forContentType('icon_link')?->bindingSchema);
    }

    public function testLookupFailsClosedForUnknownContentTypeBlueprintOrRevision(): void
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();

        self::assertNull($catalog->forContentType('iframe'));
        self::assertNull($catalog->contentTypeForBlueprint('22222222-2222-4222-8222-222222222222', 1));
        self::assertNull($catalog->contentTypeForBlueprint('31000000-0000-4000-8000-000000000001', 2));
        self::assertSame(
            'rich_text',
            $catalog->contentTypeForBlueprint('31000000-0000-4000-8000-000000000001', 1),
        );
    }
}
