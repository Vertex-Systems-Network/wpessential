<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRuntimeClock;

final class DashboardWidgetRuntimeClockTest extends TestCase
{
    public function testInjectedClockReturnsDeterministicUnixTimestamp(): void
    {
        $clock = new DashboardWidgetRuntimeClock(static fn (): int => 1735689600);

        self::assertSame(1735689600, $clock->now());
    }

    public function testInvalidClockResultFailsClosed(): void
    {
        foreach ([
            static fn (): string => '1735689600',
            static fn (): int => -1,
        ] as $source) {
            try {
                (new DashboardWidgetRuntimeClock($source))->now();
                self::fail('Expected invalid Dashboard runtime clock value to fail closed.');
            } catch (RuntimeException) {
                self::assertTrue(true);
            }
        }
    }
}
