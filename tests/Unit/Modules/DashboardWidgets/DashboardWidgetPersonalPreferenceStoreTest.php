<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPersonalPreferenceStore;

final class DashboardWidgetPersonalPreferenceStoreTest extends TestCase
{
    public function testDismissPersistsOnlyCanonicalWpeIdsDeterministically(): void
    {
        $meta = [];
        $store = $this->store($meta);

        self::assertSame(
            ['wpe_dashboard_widget_beta'],
            $store->dismiss(7, DashboardWidgetPersonalPreferenceStore::SCREEN_SITE, 'wpe_dashboard_widget_beta'),
        );
        self::assertSame(
            ['wpe_dashboard_widget_alpha', 'wpe_dashboard_widget_beta'],
            $store->dismiss(7, DashboardWidgetPersonalPreferenceStore::SCREEN_SITE, 'wpe_dashboard_widget_alpha'),
        );
        self::assertSame(
            ['wpe_dashboard_widget_alpha', 'wpe_dashboard_widget_beta'],
            $store->dismiss(7, DashboardWidgetPersonalPreferenceStore::SCREEN_SITE, 'wpe_dashboard_widget_alpha'),
        );

        $this->expectException(InvalidArgumentException::class);
        $store->dismiss(7, DashboardWidgetPersonalPreferenceStore::SCREEN_SITE, 'dashboard_activity');
    }

    public function testResetRemovesOnlyWpeOwnedIdsAndPreservesThirdPartyOrder(): void
    {
        $meta = [
            '7:_wpessential_dashboard_widgets_dismissed_dashboard' => [
                'wpe_dashboard_widget_sales',
                'wpe_dashboard_widget_orders',
            ],
        ];
        $options = [
            '7:metaboxhidden_dashboard' => [
                'dashboard_activity',
                'wpe_dashboard_widget_sales',
                'third_party_widget',
            ],
            '7:closedpostboxes_dashboard' => [
                'wpe_dashboard_widget_orders',
                'dashboard_quick_press',
            ],
            '7:meta-box-order_dashboard' => [
                'normal' => 'dashboard_activity,wpe_dashboard_widget_sales,third_party_widget',
                'side' => 'wpe_dashboard_widget_orders,dashboard_quick_press',
            ],
        ];

        $store = $this->store($meta, $options);
        $result = $store->reset(7, DashboardWidgetPersonalPreferenceStore::SCREEN_SITE);

        self::assertSame([
            'screen_id' => 'dashboard',
            'removed' => [
                'dismissed' => 2,
                'hidden' => 1,
                'collapsed' => 1,
                'ordered' => 2,
            ],
        ], $result);
        self::assertArrayNotHasKey('7:_wpessential_dashboard_widgets_dismissed_dashboard', $meta);
        self::assertSame(
            ['dashboard_activity', 'third_party_widget'],
            $options['7:metaboxhidden_dashboard'],
        );
        self::assertSame(
            ['dashboard_quick_press'],
            $options['7:closedpostboxes_dashboard'],
        );
        self::assertSame([
            'normal' => 'dashboard_activity,third_party_widget',
            'side' => 'dashboard_quick_press',
        ], $options['7:meta-box-order_dashboard']);
    }

    public function testMalformedNativePreferenceFailsBeforeAnyMutation(): void
    {
        $meta = [
            '7:_wpessential_dashboard_widgets_dismissed_dashboard' => [
                'wpe_dashboard_widget_sales',
            ],
        ];
        $options = [
            '7:metaboxhidden_dashboard' => [
                'dashboard_activity',
                'wpe_dashboard_widget_sales',
            ],
            '7:closedpostboxes_dashboard' => [
                'dashboard_quick_press',
            ],
            '7:meta-box-order_dashboard' => [
                'unsupported' => 'wpe_dashboard_widget_sales',
            ],
        ];
        $beforeMeta = $meta;
        $beforeOptions = $options;

        try {
            $this->store($meta, $options)->reset(7, DashboardWidgetPersonalPreferenceStore::SCREEN_SITE);
            self::fail('Expected malformed native preference to fail before mutation.');
        } catch (RuntimeException) {
            self::assertSame($beforeMeta, $meta);
            self::assertSame($beforeOptions, $options);
        }
    }

    public function testWriteBackMismatchFailsClosed(): void
    {
        $meta = [];
        $store = new DashboardWidgetPersonalPreferenceStore(
            metaReader: static fn (int $userId, string $key): mixed => $meta[$userId . ':' . $key] ?? null,
            metaWriter: static function (int $userId, string $key, array $value): void {
                // Intentionally drop the write.
            },
            metaDeleter: static function (int $userId, string $key): void {},
            optionReader: static fn (int $userId, string $key): mixed => null,
            optionWriter: static function (int $userId, string $key, mixed $value): void {},
        );

        $this->expectException(RuntimeException::class);
        $store->dismiss(7, DashboardWidgetPersonalPreferenceStore::SCREEN_SITE, 'wpe_dashboard_widget_sales');
    }

    /**
     * @param array<string,mixed> $meta
     * @param array<string,mixed> $options
     */
    private function store(array &$meta, array &$options = []): DashboardWidgetPersonalPreferenceStore
    {
        return new DashboardWidgetPersonalPreferenceStore(
            metaReader: static fn (int $userId, string $key): mixed =>
                $meta[$userId . ':' . $key] ?? null,
            metaWriter: static function (int $userId, string $key, array $value) use (&$meta): void {
                $meta[$userId . ':' . $key] = $value;
            },
            metaDeleter: static function (int $userId, string $key) use (&$meta): void {
                unset($meta[$userId . ':' . $key]);
            },
            optionReader: static fn (int $userId, string $key): mixed =>
                $options[$userId . ':' . $key] ?? null,
            optionWriter: static function (int $userId, string $key, mixed $value) use (&$options): void {
                $options[$userId . ':' . $key] = $value;
            },
        );
    }
}
