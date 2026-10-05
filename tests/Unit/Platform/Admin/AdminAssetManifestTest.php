<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\AdminAssetManifest;

final class AdminAssetManifestTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir() . '/wpessential-admin-assets-' . bin2hex(random_bytes(6));
        mkdir($this->root . '/assets/admin', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->root);
        parent::tearDown();
    }

    public function testReadsWordPressScriptsAssetMetadata(): void
    {
        file_put_contents($this->root . '/assets/admin/main.js', 'window.WPEAdmin=true;');
        file_put_contents($this->root . '/assets/admin/main.css', '.wpessential-admin-wrap{}');
        file_put_contents(
            $this->root . '/assets/admin/main.asset.php',
            "<?php return ['dependencies' => ['wp-i18n', 'wp-element'], 'version' => 'asset-hash'];",
        );

        $manifest = new AdminAssetManifest($this->root, 'https://example.test/wpessential');
        $entry = $manifest->entry();

        self::assertNotNull($entry);
        self::assertSame('https://example.test/wpessential/assets/admin/main.js', $entry['script']);
        self::assertSame(['https://example.test/wpessential/assets/admin/main.css'], $entry['styles']);
        self::assertSame(['wp-i18n', 'wp-element'], $entry['dependencies']);
        self::assertSame('asset-hash', $entry['version']);
    }

    public function testReadsNamedEntryAndFallsBackToMatchingEntryStyle(): void
    {
        file_put_contents($this->root . '/assets/admin/taxonomy.js', 'window.WPETaxonomy=true;');
        file_put_contents($this->root . '/assets/admin/taxonomy.css', '.wpessential-taxonomy{}');
        file_put_contents(
            $this->root . '/assets/admin/taxonomy.asset.php',
            "<?php return ['dependencies' => ['wp-i18n'], 'version' => 'taxonomy-hash'];",
        );

        $manifest = new AdminAssetManifest($this->root, 'https://example.test/wpessential');
        $entry = $manifest->entry('taxonomy', 'taxonomy-admin');

        self::assertNotNull($entry);
        self::assertSame('https://example.test/wpessential/assets/admin/taxonomy.js', $entry['script']);
        self::assertSame(['https://example.test/wpessential/assets/admin/taxonomy.css'], $entry['styles']);
        self::assertSame(['wp-i18n'], $entry['dependencies']);
        self::assertSame('taxonomy-hash', $entry['version']);
    }

    public function testScriptLookupDoesNotRequireOrImplicitlyLoadStyle(): void
    {
        file_put_contents($this->root . '/assets/admin/main.js', 'window.WPEAdmin=true;');
        file_put_contents(
            $this->root . '/assets/admin/main.asset.php',
            "<?php return ['dependencies' => ['wp-i18n'], 'version' => 'script-only'];",
        );

        $manifest = new AdminAssetManifest($this->root, 'https://example.test/wpessential');
        $script = $manifest->script('main');

        self::assertNotNull($script);
        self::assertSame(
            'https://example.test/wpessential/assets/admin/main.js',
            $script['script'],
        );
        self::assertSame(['wp-i18n'], $script['dependencies']);
        self::assertSame('script-only', $script['version']);
        self::assertNull($manifest->style('main'));
    }

    public function testScriptLookupDropsMalformedDependencyHandles(): void
    {
        file_put_contents($this->root . '/assets/admin/main.js', 'window.WPEAdmin=true;');
        file_put_contents(
            $this->root . '/assets/admin/main.asset.php',
            "<?php return ['dependencies' => ['wp-i18n', '../bad', '', str_repeat('x', 192)], 'version' => 'hash'];",
        );

        $manifest = new AdminAssetManifest($this->root, 'https://example.test/wpessential');
        $script = $manifest->script('main');

        self::assertNotNull($script);
        self::assertSame(['wp-i18n'], $script['dependencies']);
    }

    public function testStyleLookupIsExactAndRejectsUnsafeEntry(): void
    {
        file_put_contents($this->root . '/assets/admin/dashboard.css', '.dashboard{}');

        $manifest = new AdminAssetManifest($this->root, 'https://example.test/wpessential');

        self::assertSame(
            'https://example.test/wpessential/assets/admin/dashboard.css',
            $manifest->style('dashboard'),
        );
        self::assertNull($manifest->style('../dashboard'));
        self::assertNull($manifest->script('../dashboard'));
        self::assertNull($manifest->script('bad..entry'));
    }

    public function testRejectsUnsafeNamedEntry(): void
    {
        $manifest = new AdminAssetManifest($this->root, 'https://example.test/wpessential');

        self::assertNull($manifest->entry('../taxonomy'));
    }

    public function testReturnsNullWhenBuildArtifactsAreIncomplete(): void
    {
        $manifest = new AdminAssetManifest($this->root, 'https://example.test/wpessential');

        self::assertNull($manifest->entry());
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
