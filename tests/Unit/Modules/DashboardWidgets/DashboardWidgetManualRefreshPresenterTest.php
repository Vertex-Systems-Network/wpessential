<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetManualRefreshPresenter;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationDescriptor;

final class DashboardWidgetManualRefreshPresenterTest extends TestCase
{
    public function testRendersEscapedTransportWithTrustedInitialAndLoadingHtml(): void
    {
        $presenter = new DashboardWidgetManualRefreshPresenter(
            'wpessential_dispatch',
            static fn (): string => 'refresh-nonce-"<&',
        );

        $html = $presenter->render(
            $this->descriptor(),
            'https://example.test/wp-admin/admin-ajax.php?x=1&y=2',
            '<div class="trusted-current">Current</div>',
            '<div class="trusted-loading">Refreshing…</div>',
            'site',
        );

        self::assertStringContainsString('data-wpessential-dashboard-manual-refresh="1"', $html);
        self::assertStringContainsString('data-route-type="dashboard-widgets.refresh.manual"', $html);
        self::assertStringContainsString('data-nonce="refresh-nonce-&quot;&lt;&amp;"', $html);
        self::assertStringContainsString('data-screen="site"', $html);
        self::assertStringContainsString(
            'data-definition-id="11111111-1111-4111-8111-111111111111"',
            $html,
        );
        self::assertStringContainsString('data-definition-revision="7"', $html);
        self::assertStringContainsString('<div class="trusted-current">Current</div>', $html);
        self::assertStringContainsString('<div class="trusted-loading">Refreshing…</div>', $html);
        self::assertStringContainsString('data-wpessential-dashboard-refresh-button="1"', $html);
        self::assertStringContainsString('aria-live="polite"', $html);
    }

    public function testRejectsDisabledManualRefreshInvalidScreenEndpointAndNonce(): void
    {
        $valid = new DashboardWidgetManualRefreshPresenter(
            'wpessential_dispatch',
            static fn (): string => 'nonce',
        );

        foreach ([
            fn () => $valid->render($this->descriptor(false), 'https://example.test/ajax', '<p>x</p>', '<p>y</p>', 'site'),
            fn () => $valid->render($this->descriptor(), 'https://example.test/ajax', '<p>x</p>', '<p>y</p>', 'other'),
            fn () => $valid->render($this->descriptor(), '', '<p>x</p>', '<p>y</p>', 'site'),
            fn () => $valid->render($this->descriptor(), 'https://example.test/ajax', '', '<p>y</p>', 'site'),
            fn () => (new DashboardWidgetManualRefreshPresenter(
                'wpessential_dispatch',
                static fn (): string => '',
            ))->render($this->descriptor(), 'https://example.test/ajax', '<p>x</p>', '<p>y</p>', 'site'),
        ] as $case) {
            try {
                $case();
                self::fail('Expected invalid manual-refresh presentation input to fail closed.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    private function descriptor(bool $manual = true): DashboardWidgetRegistrationDescriptor
    {
        return new DashboardWidgetRegistrationDescriptor(
            definitionId: '11111111-1111-4111-8111-111111111111',
            revision: 7,
            key: 'manual-refresh',
            title: 'Manual refresh',
            context: 'normal',
            priority: 'default',
            networkDashboard: false,
            manualRefresh: $manual,
        );
    }
}
