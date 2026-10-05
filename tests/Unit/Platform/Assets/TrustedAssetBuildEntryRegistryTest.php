<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Assets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Platform\Assets\AssetDescriptor;
use WPEssential\Platform\Assets\AssetRegistry;
use WPEssential\Platform\Assets\AssetScope;
use WPEssential\Platform\Assets\TrustedAssetBuildEntry;
use WPEssential\Platform\Assets\TrustedAssetBuildEntryRegistry;

final class TrustedAssetBuildEntryRegistryTest extends TestCase
{
    public function testMapsOnlyAlreadyRegisteredLogicalAssetHandle(): void
    {
        $assets = new AssetRegistry();
        $assets->register(new AssetDescriptor(
            handle: 'wpe-dashboard-form-action',
            ownerSurfaceId: 10,
            scope: AssetScope::Admin,
        ));
        $mappings = new TrustedAssetBuildEntryRegistry($assets);
        $entry = new TrustedAssetBuildEntry(
            'wpe-dashboard-form-action',
            'main',
        );

        $mappings->register($entry);

        self::assertSame($entry, $mappings->get('wpe-dashboard-form-action'));
    }

    public function testRejectsMappingBeforeLogicalAssetRegistration(): void
    {
        $mappings = new TrustedAssetBuildEntryRegistry(new AssetRegistry());

        $this->expectException(RuntimeException::class);
        $mappings->register(new TrustedAssetBuildEntry(
            'wpe-dashboard-form-action',
            'main',
        ));
    }

    public function testRejectsDuplicateMapping(): void
    {
        $assets = new AssetRegistry();
        $assets->register(new AssetDescriptor(
            handle: 'wpe-dashboard-form-action',
            ownerSurfaceId: 10,
            scope: AssetScope::Admin,
        ));
        $mappings = new TrustedAssetBuildEntryRegistry($assets);
        $entry = new TrustedAssetBuildEntry(
            'wpe-dashboard-form-action',
            'main',
        );
        $mappings->register($entry);

        $this->expectException(RuntimeException::class);
        $mappings->register($entry);
    }

    public function testRejectsUnsafeBuildEntriesAndUnknownLookup(): void
    {
        foreach ([
            ['wpe-dashboard-form-action', '../main', null],
            ['wpe-dashboard-form-action', 'https://cdn.test/main', null],
            ['wpe-dashboard-form-action', 'bad..entry', null],
            ['wpe-dashboard-form-action', 'main', '../style'],
        ] as [$handle, $script, $style]) {
            try {
                new TrustedAssetBuildEntry($handle, $script, $style);
                self::fail('Unsafe trusted build entry must fail closed.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }

        $mappings = new TrustedAssetBuildEntryRegistry(new AssetRegistry());
        $this->expectException(RuntimeException::class);
        $mappings->get('wpe-dashboard-form-action');
    }
}
