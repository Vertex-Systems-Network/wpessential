<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Assets;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Assets\AssetDescriptor;
use WPEssential\Platform\Assets\AssetLoadStrategy;
use WPEssential\Platform\Assets\AssetRegistry;
use WPEssential\Platform\Assets\AssetScope;
use WPEssential\Platform\Assets\TrustedAssetBuildEntry;
use WPEssential\Platform\Assets\TrustedAssetBuildEntryRegistry;
use WPEssential\Platform\Assets\WordPressAssetEnvironmentInterface;
use WPEssential\Platform\Assets\WordPressAssetLoader;

final class WordPressAssetLoaderTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir() . '/wpessential-asset-loader-' . bin2hex(random_bytes(6));
        mkdir($this->root . '/assets/admin', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->root);
        parent::tearDown();
    }

    public function testLoadsDependencyOrderOnceForExactDashboardRouteWithoutImplicitStyle(): void
    {
        $this->writeScript('dependency', ['wp-i18n'], 'dep-hash');
        $this->writeScript('main', ['wp-element'], 'main-hash');

        $assets = new AssetRegistry();
        $assets->register(new AssetDescriptor(
            handle: 'wpe-dashboard-support',
            ownerSurfaceId: 10,
            scope: AssetScope::Admin,
        ));
        $assets->register(new AssetDescriptor(
            handle: 'wpe-dashboard-form-action',
            ownerSurfaceId: 10,
            scope: AssetScope::Admin,
            loadStrategy: AssetLoadStrategy::AdminRoute,
            dependencies: ['wpe-dashboard-support'],
            adminRoutes: ['/wp-admin/index.php'],
        ));

        $mappings = new TrustedAssetBuildEntryRegistry($assets);
        $mappings->register(new TrustedAssetBuildEntry(
            'wpe-dashboard-support',
            'dependency',
        ));
        $mappings->register(new TrustedAssetBuildEntry(
            'wpe-dashboard-form-action',
            'main',
        ));

        $environment = new AssetLoaderEnvironmentProbe('/wp-admin/index.php');
        $loader = new WordPressAssetLoader(
            $assets,
            $mappings,
            new AdminAssetManifest($this->root, 'https://example.test/wpessential'),
            $environment,
        );

        $loader->register();
        $loader->register();
        self::assertCount(1, $environment->registered);

        $loader->enqueueForAdminHook('index.php');

        self::assertSame(
            ['wpe-dashboard-support', 'wpe-dashboard-form-action'],
            array_column($environment->scripts, 0),
        );
        self::assertSame(['wp-i18n'], $environment->scripts[0][2]);
        self::assertSame(['wp-element'], $environment->scripts[1][2]);
        self::assertSame('dep-hash', $environment->scripts[0][3]);
        self::assertSame('main-hash', $environment->scripts[1][3]);
        self::assertSame([], $environment->styles);
        self::assertSame([
            ['wpe-dashboard-support', 'defer'],
            ['wpe-dashboard-form-action', 'defer'],
        ], $environment->strategies);
    }

    public function testLoadsExplicitStyleOnlyWhenMappingRequestsIt(): void
    {
        $this->writeScript('main', [], 'hash');
        file_put_contents($this->root . '/assets/admin/dashboard.css', '.dashboard{}');

        [$assets, $mappings] = $this->singleAsset(
            new TrustedAssetBuildEntry(
                'wpe-dashboard-form-action',
                'main',
                'dashboard',
            ),
        );
        $environment = new AssetLoaderEnvironmentProbe('/wp-admin/network/index.php');
        $loader = new WordPressAssetLoader(
            $assets,
            $mappings,
            new AdminAssetManifest($this->root, 'https://example.test/wpessential'),
            $environment,
        );

        $loader->enqueueForAdminHook('index.php');

        self::assertCount(1, $environment->scripts);
        self::assertSame([
            [
                'wpe-dashboard-form-action-style',
                'https://example.test/wpessential/assets/admin/dashboard.css',
                'hash',
            ],
        ], $environment->styles);
    }

    public function testUnrelatedAdminRouteLoadsNothing(): void
    {
        $this->writeScript('main', [], 'hash');
        [$assets, $mappings] = $this->singleAsset(
            new TrustedAssetBuildEntry('wpe-dashboard-form-action', 'main'),
        );
        $environment = new AssetLoaderEnvironmentProbe(null);
        $loader = new WordPressAssetLoader(
            $assets,
            $mappings,
            new AdminAssetManifest($this->root, 'https://example.test/wpessential'),
            $environment,
        );

        $loader->enqueueForAdminHook('edit.php');

        self::assertSame([], $environment->scripts);
        self::assertSame([], $environment->styles);
    }

    public function testMissingMappingOrManifestFailsClosedBeforeAnyPartialEnqueue(): void
    {
        $this->writeScript('dependency', [], 'hash');

        $assets = new AssetRegistry();
        $assets->register(new AssetDescriptor(
            handle: 'wpe-dashboard-support',
            ownerSurfaceId: 10,
            scope: AssetScope::Admin,
        ));
        $assets->register(new AssetDescriptor(
            handle: 'wpe-dashboard-form-action',
            ownerSurfaceId: 10,
            scope: AssetScope::Admin,
            loadStrategy: AssetLoadStrategy::AdminRoute,
            dependencies: ['wpe-dashboard-support'],
            adminRoutes: ['/wp-admin/index.php'],
        ));

        $mappings = new TrustedAssetBuildEntryRegistry($assets);
        $mappings->register(new TrustedAssetBuildEntry(
            'wpe-dashboard-support',
            'dependency',
        ));
        // The dependent mapping is intentionally missing.

        $environment = new AssetLoaderEnvironmentProbe('/wp-admin/index.php');
        $loader = new WordPressAssetLoader(
            $assets,
            $mappings,
            new AdminAssetManifest($this->root, 'https://example.test/wpessential'),
            $environment,
        );

        $loader->enqueueForAdminHook('index.php');

        self::assertSame([], $environment->scripts);
        self::assertSame([], $environment->styles);
        self::assertSame([], $environment->strategies);
    }

    public function testMissingExplicitStyleFailsClosedBeforeScriptEnqueue(): void
    {
        $this->writeScript('main', [], 'hash');
        [$assets, $mappings] = $this->singleAsset(
            new TrustedAssetBuildEntry(
                'wpe-dashboard-form-action',
                'main',
                'missing-style',
            ),
        );
        $environment = new AssetLoaderEnvironmentProbe('/wp-admin/index.php');
        $loader = new WordPressAssetLoader(
            $assets,
            $mappings,
            new AdminAssetManifest($this->root, 'https://example.test/wpessential'),
            $environment,
        );

        $loader->enqueueForAdminHook('index.php');

        self::assertSame([], $environment->scripts);
        self::assertSame([], $environment->styles);
    }

    /**
     * @return array{AssetRegistry,TrustedAssetBuildEntryRegistry}
     */
    private function singleAsset(
        TrustedAssetBuildEntry $mapping,
    ): array {
        $assets = new AssetRegistry();
        $assets->register(new AssetDescriptor(
            handle: 'wpe-dashboard-form-action',
            ownerSurfaceId: 10,
            scope: AssetScope::Admin,
            loadStrategy: AssetLoadStrategy::AdminRoute,
            adminRoutes: [
                '/wp-admin/index.php',
                '/wp-admin/network/index.php',
            ],
        ));
        $mappings = new TrustedAssetBuildEntryRegistry($assets);
        $mappings->register($mapping);

        return [$assets, $mappings];
    }

    /**
     * @param list<string> $dependencies
     */
    private function writeScript(
        string $entry,
        array $dependencies,
        string $version,
    ): void {
        file_put_contents(
            $this->root . '/assets/admin/' . $entry . '.js',
            'window.WPE=true;',
        );
        file_put_contents(
            $this->root . '/assets/admin/' . $entry . '.asset.php',
            '<?php return ' . var_export([
                'dependencies' => $dependencies,
                'version' => $version,
            ], true) . ';',
        );
    }

    private function removeTree(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if (!is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $path . '/' . $item;
            if (is_dir($itemPath)) {
                $this->removeTree($itemPath);
            } else {
                unlink($itemPath);
            }
        }

        rmdir($path);
    }
}

final class AssetLoaderEnvironmentProbe implements WordPressAssetEnvironmentInterface
{
    /** @var list<callable> */
    public array $registered = [];

    /** @var list<array{string,string,list<string>,?string}> */
    public array $scripts = [];

    /** @var list<array{string,string,?string}> */
    public array $styles = [];

    /** @var list<array{string,string}> */
    public array $strategies = [];

    public function __construct(private readonly ?string $route)
    {
    }

    public function registerAdminEnqueue(callable $callback): void
    {
        $this->registered[] = $callback;
    }

    public function routeForAdminHook(string $hookSuffix): ?string
    {
        return $this->route;
    }

    public function enqueueScript(
        string $handle,
        string $source,
        array $dependencies,
        ?string $version,
    ): void {
        $this->scripts[] = [$handle, $source, $dependencies, $version];
    }

    public function enqueueStyle(
        string $handle,
        string $source,
        ?string $version,
    ): void {
        $this->styles[] = [$handle, $source, $version];
    }

    public function setScriptStrategy(string $handle, string $strategy): void
    {
        $this->strategies[] = [$handle, $strategy];
    }
}
