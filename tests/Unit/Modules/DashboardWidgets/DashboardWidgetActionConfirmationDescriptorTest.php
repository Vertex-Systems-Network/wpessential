<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetActionConfirmationDescriptor;

final class DashboardWidgetActionConfirmationDescriptorTest extends TestCase
{
    public function testNormalizesAndCarriesBoundedPlainTextMetadata(): void
    {
        $descriptor = new DashboardWidgetActionConfirmationDescriptor(
            '  Confirm action  ',
            "  This change will be applied.\nContinue?  ",
            '  Confirm  ',
            '  Cancel  ',
        );

        self::assertSame('Confirm action', $descriptor->title);
        self::assertSame("This change will be applied.\nContinue?", $descriptor->message);
        self::assertSame('Confirm', $descriptor->confirmLabel);
        self::assertSame('Cancel', $descriptor->cancelLabel);
    }

    public function testRejectsEmptyControlOrOverBoundText(): void
    {
        $cases = [
            ['', 'Message', 'Confirm', 'Cancel'],
            ['Title', "Bad\x00message", 'Confirm', 'Cancel'],
            [str_repeat('a', DashboardWidgetActionConfirmationDescriptor::MAX_TITLE_BYTES + 1), 'Message', 'Confirm', 'Cancel'],
            ['Title', str_repeat('m', DashboardWidgetActionConfirmationDescriptor::MAX_MESSAGE_BYTES + 1), 'Confirm', 'Cancel'],
            ['Title', 'Message', str_repeat('c', DashboardWidgetActionConfirmationDescriptor::MAX_LABEL_BYTES + 1), 'Cancel'],
            ['Title', 'Message', 'Confirm', str_repeat('x', DashboardWidgetActionConfirmationDescriptor::MAX_LABEL_BYTES + 1)],
            [str_repeat('é', 61), 'Message', 'Confirm', 'Cancel'],
        ];

        foreach ($cases as [$title, $message, $confirmLabel, $cancelLabel]) {
            try {
                new DashboardWidgetActionConfirmationDescriptor($title, $message, $confirmLabel, $cancelLabel);
                self::fail('Expected malformed or over-bound confirmation text to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testMetadataIsDataOnlyAndDoesNotInterpretExecutableLookingText(): void
    {
        $descriptor = new DashboardWidgetActionConfirmationDescriptor(
            '<b>Confirm</b>',
            '[shortcode] <?php echo "x"; ?> {{token}}',
            'Run callback()',
            'Cancel',
        );

        self::assertSame('<b>Confirm</b>', $descriptor->title);
        self::assertSame('[shortcode] <?php echo "x"; ?> {{token}}', $descriptor->message);
        self::assertSame('Run callback()', $descriptor->confirmLabel);
        self::assertSame('Cancel', $descriptor->cancelLabel);
    }
}
