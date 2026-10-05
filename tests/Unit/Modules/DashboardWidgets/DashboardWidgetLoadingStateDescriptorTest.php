<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetLoadingStateDescriptor;

final class DashboardWidgetLoadingStateDescriptorTest extends TestCase
{
    public function testBuildsTrustedRichTextAndAnnouncementInputs(): void
    {
        $richText = new DashboardWidgetLoadingStateDescriptor(
            blueprintId: '31000000-0000-4000-8000-000000000001',
            blueprintRevision: 1,
            bindings: ['content' => 'Refreshing widget…'],
        );
        self::assertSame(
            ['content' => 'Refreshing widget…'],
            $richText->toRenderInput()->bindings,
        );

        $announcement = new DashboardWidgetLoadingStateDescriptor(
            blueprintId: '31000000-0000-4000-8000-000000000005',
            blueprintRevision: 1,
            bindings: ['text' => 'Fetching the latest data.', 'title' => 'Refreshing'],
        );
        self::assertSame(
            ['text' => 'Fetching the latest data.', 'title' => 'Refreshing'],
            $announcement->toRenderInput()->bindings,
        );
    }

    public function testRejectsUnsupportedSchemaUnsafeAndOversizedBindings(): void
    {
        $cases = [
            fn () => new DashboardWidgetLoadingStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000003',
                blueprintRevision: 1,
                bindings: ['labels' => 'Loading'],
            ),
            fn () => new DashboardWidgetLoadingStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 2,
                bindings: ['content' => 'Loading'],
            ),
            fn () => new DashboardWidgetLoadingStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 1,
                bindings: ['wrong' => 'Loading'],
            ),
            fn () => new DashboardWidgetLoadingStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 1,
                bindings: ['content' => '   '],
            ),
            fn () => new DashboardWidgetLoadingStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 1,
                bindings: ['content' => '<script>alert(1)</script>'],
            ),
            fn () => new DashboardWidgetLoadingStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 1,
                bindings: ['content' => str_repeat('x', DashboardWidgetLoadingStateDescriptor::MAX_STRING_BYTES + 1)],
            ),
        ];

        foreach ($cases as $case) {
            try {
                $case();
                self::fail('Expected invalid bounded loading-state descriptor to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }
}
