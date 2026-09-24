<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetErrorStateDescriptor;

final class DashboardWidgetErrorStateDescriptorTest extends TestCase
{
    public function testBuildsTrustedRichTextAndAnnouncementInputs(): void
    {
        $richText = new DashboardWidgetErrorStateDescriptor(
            blueprintId: '31000000-0000-4000-8000-000000000001',
            blueprintRevision: 1,
            bindings: ['content' => 'Unable to render this widget.'],
        );
        self::assertSame(
            ['content' => 'Unable to render this widget.'],
            $richText->toRenderInput()->bindings,
        );

        $announcement = new DashboardWidgetErrorStateDescriptor(
            blueprintId: '31000000-0000-4000-8000-000000000005',
            blueprintRevision: 1,
            bindings: ['text' => 'Please try again later.', 'title' => 'Widget unavailable'],
        );
        self::assertSame(
            ['text' => 'Please try again later.', 'title' => 'Widget unavailable'],
            $announcement->toRenderInput()->bindings,
        );
    }

    public function testRejectsUnsupportedSchemaUnsafeAndOversizedBindings(): void
    {
        $cases = [
            fn () => new DashboardWidgetErrorStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000003',
                blueprintRevision: 1,
                bindings: ['labels' => 'Unavailable'],
            ),
            fn () => new DashboardWidgetErrorStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 2,
                bindings: ['content' => 'Unavailable'],
            ),
            fn () => new DashboardWidgetErrorStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 1,
                bindings: ['wrong' => 'Unavailable'],
            ),
            fn () => new DashboardWidgetErrorStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 1,
                bindings: ['content' => '   '],
            ),
            fn () => new DashboardWidgetErrorStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 1,
                bindings: ['content' => '<script>alert(1)</script>'],
            ),
            fn () => new DashboardWidgetErrorStateDescriptor(
                blueprintId: '31000000-0000-4000-8000-000000000001',
                blueprintRevision: 1,
                bindings: ['content' => str_repeat('x', DashboardWidgetErrorStateDescriptor::MAX_STRING_BYTES + 1)],
            ),
        ];

        foreach ($cases as $case) {
            try {
                $case();
                self::fail('Expected invalid bounded error-state descriptor to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }
}
