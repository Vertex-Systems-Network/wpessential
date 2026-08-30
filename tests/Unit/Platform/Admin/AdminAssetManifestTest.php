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
