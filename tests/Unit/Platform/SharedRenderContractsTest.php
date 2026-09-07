<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;
use WPEssential\Platform\DynamicValues\DynamicValueResult;
use WPEssential\Platform\Rendering\RenderFailureCode;
use WPEssential\Platform\Rendering\RenderInput;
use WPEssential\Platform\Rendering\RenderOutput;

final class SharedRenderContractsTest extends TestCase
{
    private const BLUEPRINT_ID = '123e4567-e89b-42d3-a456-426614174000';

    public function testRenderInputRejectsExecutableAuthoredChannel(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RenderInput(self::BLUEPRINT_ID, 1, ['title' => '<script>alert(1)</script>']);
    }

    public function testFailedRenderOutputFailsClosedWithoutHtml(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RenderOutput(false, '<div>partial</div>', [], RenderFailureCode::MissingBlueprint);
    }

    public function testUnresolvedDynamicValueCannotLeakValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DynamicValueResult(false, 'secret', RenderFailureCode::UnsupportedValueSource);
    }

    public function testBlueprintUsesStableReferencesAndRegisteredAssetHandles(): void
    {
        $descriptor = new ComponentBlueprintDescriptor(
            id: self::BLUEPRINT_ID,
            revision: 2,
            ownerSurfaceId: 9,
            componentType: 'listing.item',
            dependencyIds: ['123e4567-e89b-42d3-a456-426614174001'],
            assetHandles: ['wpe-listing-card'],
            bindingSchema: ['title' => 'string', 'post_id' => 'int'],
        );

        self::assertSame(2, $descriptor->revision);
        self::assertSame(['wpe-listing-card'], $descriptor->assetHandles);
    }

    public function testBlueprintRejectsBuilderOrExecutableBindingTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ComponentBlueprintDescriptor(
            id: self::BLUEPRINT_ID,
            revision: 1,
            ownerSurfaceId: 9,
            componentType: 'listing.item',
            bindingSchema: ['template' => 'php'],
        );
    }
}
