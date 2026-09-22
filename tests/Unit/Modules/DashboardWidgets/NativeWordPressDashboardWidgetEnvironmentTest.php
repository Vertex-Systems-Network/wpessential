<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\NativeWordPressDashboardWidgetEnvironment;

final class NativeWordPressDashboardWidgetEnvironmentTest extends TestCase
{
    public function testForwardsHooksAndDashboardRegistrationWithNullControlAndArgs(): void
    {
        $hooks = [];
        $registrations = [];
        $environment = new NativeWordPressDashboardWidgetEnvironment(
            registerAction: static function (string $hook, callable $callback) use (&$hooks): void {
                $hooks[] = [$hook, $callback];
            },
            registerDashboardWidget: static function (
                string $id,
                string $title,
                callable $callback,
                ?callable $controlCallback,
                ?array $callbackArgs,
                string $context,
                string $priority,
            ) use (&$registrations): void {
                $registrations[] = compact(
                    'id',
                    'title',
                    'callback',
                    'controlCallback',
                    'callbackArgs',
                    'context',
                    'priority',
                );
            },
        );

        $callback = static function (): void {};
        $environment->registerAction('wp_dashboard_setup', $callback);
        $environment->registerDashboardWidget('wpe_dashboard_widget_demo', 'Demo', $callback, 'side', 'high');

        self::assertSame('wp_dashboard_setup', $hooks[0][0]);
        self::assertSame('wpe_dashboard_widget_demo', $registrations[0]['id']);
        self::assertSame('Demo', $registrations[0]['title']);
        self::assertNull($registrations[0]['controlCallback']);
        self::assertNull($registrations[0]['callbackArgs']);
        self::assertSame('side', $registrations[0]['context']);
        self::assertSame('high', $registrations[0]['priority']);
    }

    public function testProjectsCurrentRequestIdsAndTrustedOutput(): void
    {
        $outputs = [];
        $environment = new NativeWordPressDashboardWidgetEnvironment(
            currentUserId: static fn (): ?int => 7,
            currentSiteId: static fn (): int => 11,
            currentNetworkId: static fn (): ?int => 13,
            outputTrustedHtml: static function (string $html) use (&$outputs): void {
                $outputs[] = $html;
            },
        );

        self::assertSame(7, $environment->currentUserId());
        self::assertSame(11, $environment->currentSiteId());
        self::assertSame(13, $environment->currentNetworkId());

        $environment->outputTrustedHtml('<p>Trusted</p>');
        self::assertSame(['<p>Trusted</p>'], $outputs);
    }
}
