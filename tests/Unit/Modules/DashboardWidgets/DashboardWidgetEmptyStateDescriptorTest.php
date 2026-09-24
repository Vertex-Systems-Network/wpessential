<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetEmptyStateDescriptor;

final class DashboardWidgetEmptyStateDescriptorTest extends TestCase
{
    public function testBuildsTrustedRichTextAndAnnouncementRenderInputs(): void
    {
        $richText = new DashboardWidgetEmptyStateDescriptor(
            blueprintId: '31000000-0000-4000-8000-000000000001',
            blueprintRevision: 1,
            bindings: ['content' => 'Nothing to display yet.'],
        );
        self::assertSame(
            ['content' => 'Nothing to display yet.'],
            $richText->toRenderInput()->bindings,
        );

        $announcement = new DashboardWidgetEmptyStateDescriptor(
            blueprintId: '31000000-0000-4000-8000-000000000005',
            blueprintRevision: 1,
            bindings: ['text' => 'Try changing the filters.', 'title' => 'No results'],
        );
        self::assertSame(
            ['text' => 'Try changing the filters.', 'title' => 'No results'],
            $announcement->toRenderInput()->bindings,
        );
    }

    public function testRejectsUnsupportedBlueprintSchemaUnsafeAndOversizedBindings(): void
    {
        $cases = [
            fn () => new DashboardWidgetEmptyStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000003',
                blueprintRevision: 1,
                bindings: ['labels' => 'No results'],
            ),
            fn () => new DashboardWidgetEmptyStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 2,
                bindings: ['content' => 'No results'],
            ),
            fn () => new DashboardWidgetEmptyStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 1,
                bindings: ['wrong' => 'No results'],
            ),
            fn () => new DashboardWidgetEmptyStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 1,
                bindings: ['content' => '   '],
            ),
            fn () => new DashboardWidgetEmptyStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 1,
                bindings: ['content' => '<script>alert(1)</script>'],
            ),
            fn () => new DashboardWidgetEmptyStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 1,
                bindings: ['content' => str_repeat('x', DashboardWidgetEmptyStateDescriptor::MAX_STRING_BYTES + 1)],
            ),
        ];

        foreach ($cases as $case) {
            try {
                $case();
                self::fail('Expected invalid bounded empty-state descriptor to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }
}
