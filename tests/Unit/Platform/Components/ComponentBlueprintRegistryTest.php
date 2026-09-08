<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Components;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;

final class ComponentBlueprintRegistryTest extends TestCase
{
    public function testLooksUpExactRevisionWithoutFallback(): void
    {
        $registry = new ComponentBlueprintRegistry();
        $v1 = $this->blueprint('11111111-1111-4111-8111-111111111111', 1);
        $v2 = $this->blueprint('11111111-1111-4111-8111-111111111111', 2);
        $registry->register($v2);
        $registry->register($v1);

        self::assertSame($v1, $registry->get($v1->id, 1));
        self::assertSame($v2, $registry->get($v2->id, 2));
        self::assertNull($registry->get($v1->id, 3));
    }

    public function testRejectsDuplicateExactRevision(): void
    {
        $registry = new ComponentBlueprintRegistry();
        $blueprint = $this->blueprint('11111111-1111-4111-8111-111111111111', 1);
        $registry->register($blueprint);

        $this->expectException(RuntimeException::class);
        $registry->register($blueprint);
    }

    public function testRejectsMissingDependencyDuringGraphValidation(): void
    {
        $registry = new ComponentBlueprintRegistry();
        $registry->register($this->blueprint(
            '11111111-1111-4111-8111-111111111111',
            1,
            ['22222222-2222-4222-8222-222222222222'],
        ));

        $this->expectException(RuntimeException::class);
        $registry->validateGraph();
    }

    public function testRejectsDependencyCycleAcrossRegisteredBlueprints(): void
    {
        $registry = new ComponentBlueprintRegistry();
        $first = '11111111-1111-4111-8111-111111111111';
        $second = '22222222-2222-4222-8222-222222222222';
        $registry->register($this->blueprint($first, 1, [$second]));
        $registry->register($this->blueprint($second, 1, [$first]));

        $this->expectException(RuntimeException::class);
        $registry->validateGraph();
    }

    /** @param list<string> $dependencies */
    private function blueprint(string $id, int $revision, array $dependencies = []): ComponentBlueprintDescriptor
    {
        return new ComponentBlueprintDescriptor(
            id: $id,
            revision: $revision,
            ownerSurfaceId: 9,
            componentType: 'listing.card',
            dependencyIds: $dependencies,
            bindingSchema: ['title' => 'string'],
        );
    }
}
