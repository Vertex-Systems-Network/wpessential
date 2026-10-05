<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Assets;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Assets\NativeWordPressAssetEnvironment;

final class NativeWordPressAssetEnvironmentTest extends TestCase
{
    public function testProjectsBoundedRoutesAndEnqueueOperations(): void
    {
        $registered = [];
        $scripts = [];
        $styles = [];
        $strategies = [];

        $environment = new NativeWordPressAssetEnvironment(
            registerAdminEnqueue: static function (callable $callback) use (&$registered): void {
                $registered[] = $callback;
            },
            routeForAdminHook: static fn (string $hook): ?string => match ($hook) {
                'site-dashboard' => '/wp-admin/index.php',
                'network-dashboard' => '/wp-admin/network/index.php',
                default => null,
            },
            enqueueScript: static function (
                string $handle,
                string $source,
                array $dependencies,
                ?string $version,
            ) use (&$scripts): void {
                $scripts[] = [$handle, $source, $dependencies, $version];
            },
            enqueueStyle: static function (
                string $handle,
                string $source,
                ?string $version,
            ) use (&$styles): void {
                $styles[] = [$handle, $source, $version];
            },
            setScriptStrategy: static function (
                string $handle,
                string $strategy,
            ) use (&$strategies): void {
                $strategies[] = [$handle, $strategy];
            },
        );

        $environment->registerAdminEnqueue(static function (): void {});
        self::assertCount(1, $registered);
        self::assertSame('/wp-admin/index.php', $environment->routeForAdminHook('site-dashboard'));
        self::assertSame(
            '/wp-admin/network/index.php',
            $environment->routeForAdminHook('network-dashboard'),
        );
        self::assertNull($environment->routeForAdminHook('edit.php'));

        $environment->enqueueScript(
            'wpe-dashboard-form-action',
            'https://example.test/assets/admin/main.js',
            ['wp-i18n'],
            'hash',
        );
        $environment->enqueueStyle(
            'wpe-dashboard-form-action-style',
            'https://example.test/assets/admin/main.css',
            'hash',
        );
        $environment->setScriptStrategy('wpe-dashboard-form-action', 'defer');

        self::assertSame([
            [
                'wpe-dashboard-form-action',
                'https://example.test/assets/admin/main.js',
                ['wp-i18n'],
                'hash',
            ],
        ], $scripts);
        self::assertSame([
            [
                'wpe-dashboard-form-action-style',
                'https://example.test/assets/admin/main.css',
                'hash',
            ],
        ], $styles);
        self::assertSame([['wpe-dashboard-form-action', 'defer']], $strategies);
    }
}
