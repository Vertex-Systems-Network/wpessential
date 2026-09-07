<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Modules\Listings\Definition\ListingDefinitionCompiler;
use WPEssential\Platform\Assets\AssetDescriptor;
use WPEssential\Platform\Assets\AssetRegistry;
use WPEssential\Platform\Assets\AssetScope;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class ListingDefinitionCompilerTest extends TestCase
{
    private const LISTING_ID = '123e4567-e89b-42d3-a456-426614174010';
    public const BLUEPRINT_ID = '123e4567-e89b-42d3-a456-426614174011';

    public function testCompilesCanonicalPublishedListingDeterministically(): void
    {
        $assets = new AssetRegistry();
        $assets->register(new AssetDescriptor('wpe-listing-card', 9, AssetScope::Frontend));
        $compiler = new ListingDefinitionCompiler($this->blueprints(), $assets);
        $definition = $this->definition();

        $first = $compiler->compile($definition);
        $second = $compiler->compile($definition);

        self::assertSame('wordpress.posts', $first->querySourceRef);
        self::assertSame(self::BLUEPRINT_ID, $first->blueprintId);
        self::assertSame(['wpe-listing-card'], $first->assetHandles);
        self::assertSame($first->compatibilityFingerprint, $second->compatibilityFingerprint);
    }

    public function testRejectsExecutableOrPrivateBuilderPayloadKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $payload = $this->payload();
        $payload['elementor_document'] = ['html' => '<script>alert(1)</script>'];
        (new ListingDefinitionCompiler($this->blueprints(), new AssetRegistry()))->compile($this->definition($payload));
    }

    public function testRejectsUnavailableBlueprintRevision(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $registry = new class implements ComponentBlueprintRegistryInterface {
            public function get(string $blueprintId, int $revision): ?ComponentBlueprintDescriptor
            {
                return null;
            }
        };

        (new ListingDefinitionCompiler($registry, new AssetRegistry()))->compile($this->definition());
    }

    public function testRejectsUnregisteredAssetHandle(): void
    {
        $this->expectException(RuntimeException::class);

        $payload = $this->payload();
        $payload['assets'] = ['wpe-missing-asset'];
        (new ListingDefinitionCompiler($this->blueprints(), new AssetRegistry()))->compile($this->definition($payload));
    }

    public function testDraftDefinitionFailsClosed(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $definition = new Definition(
            id: self::LISTING_ID,
            slug: 'posts-grid',
            type: 'listing',
            schemaVersion: 1,
            ownerSurfaceId: 9,
            status: DefinitionStatus::Draft,
            payload: $this->payload(),
        );
        (new ListingDefinitionCompiler($this->blueprints(), new AssetRegistry()))->compile($definition);
    }

    private function blueprints(): ComponentBlueprintRegistryInterface
    {
        return new class implements ComponentBlueprintRegistryInterface {
            public function get(string $blueprintId, int $revision): ?ComponentBlueprintDescriptor
            {
                if ($blueprintId !== ListingDefinitionCompilerTest::BLUEPRINT_ID || $revision !== 2) {
                    return null;
                }

                return new ComponentBlueprintDescriptor(
                    id: ListingDefinitionCompilerTest::BLUEPRINT_ID,
                    revision: 2,
                    ownerSurfaceId: 9,
                    componentType: 'listing.item',
                    assetHandles: ['wpe-listing-card'],
                    bindingSchema: ['title' => 'string', 'post_id' => 'int'],
                );
            }
        };
    }

    /** @param array<string,mixed>|null $payload */
    private function definition(?array $payload = null): Definition
    {
        return new Definition(
            id: self::LISTING_ID,
            slug: 'posts-grid',
            type: 'listing',
            schemaVersion: 1,
            ownerSurfaceId: 9,
            status: DefinitionStatus::Published,
            payload: $payload ?? $this->payload(),
            revision: 3,
        );
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'query_source_ref' => 'wordpress.posts',
            'blueprint' => ['id' => self::BLUEPRINT_ID, 'revision' => 2],
            'layout' => ['mode' => 'grid', 'columns' => 3],
            'assets' => [],
        ];
    }
}
