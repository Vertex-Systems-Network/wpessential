<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminMenu;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\AdminMenu\AdminMenuDefinition;
use WPEssential\Modules\AdminMenu\AdminMenuReadService;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class AdminMenuReadServiceTest extends TestCase
{
    public function testGetAndCatalogExposeOwnedDefinitionsInDeterministicOrder(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $dependencyId = '33333333-3333-4333-8333-333333333333';

        $repository->save($this->definition(
            id: '22222222-2222-4222-8222-222222222222',
            slug: 'secondary-menu',
            payload: ['scope' => 'network'],
        ));
        $repository->save($this->definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'primary-menu',
            payload: ['scope' => 'site'],
            dependencies: [$dependencyId],
            revision: 3,
        ));

        $service = new AdminMenuReadService($repository);
        $definition = $service->get('11111111-1111-4111-8111-111111111111');

        self::assertNotNull($definition);
        self::assertSame('primary-menu', $definition['slug']);
        self::assertSame(AdminMenuDefinition::TYPE, $definition['type']);
        self::assertSame(AdminMenuDefinition::OWNER_SURFACE_ID, $definition['owner_surface_id']);
        self::assertSame('published', $definition['status']);
        self::assertSame(3, $definition['revision']);
        self::assertSame([$dependencyId], $definition['dependencies']);
        self::assertSame(['scope' => 'site'], $definition['payload']);

        $catalog = $service->catalog();
        self::assertSame(['primary-menu', 'secondary-menu'], array_column($catalog, 'slug'));
    }

    public function testMissingDefinitionReturnsNull(): void
    {
        $service = new AdminMenuReadService(new InMemoryDefinitionRepository());

        self::assertNull($service->get('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    }

    public function testWrongOwnerFailsClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            id: '44444444-4444-4444-8444-444444444444',
            slug: 'foreign-owner',
            ownerSurfaceId: 12,
        ));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('owner surface is invalid');

        (new AdminMenuReadService($repository))->get('44444444-4444-4444-8444-444444444444');
    }

    public function testWrongTypeFailsClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            id: '55555555-5555-4555-8555-555555555555',
            slug: 'wrong-type',
            type: 'settings',
        ));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('definition type is invalid');

        (new AdminMenuReadService($repository))->get('55555555-5555-4555-8555-555555555555');
    }

    /** @param list<string> $dependencies */
    private function definition(
        string $id,
        string $slug,
        array $payload = ['scope' => 'site'],
        array $dependencies = [],
        int $revision = 1,
        int $ownerSurfaceId = AdminMenuDefinition::OWNER_SURFACE_ID,
        string $type = AdminMenuDefinition::TYPE,
    ): Definition {
        return new Definition(
            id: $id,
            slug: $slug,
            type: $type,
            schemaVersion: 1,
            ownerSurfaceId: $ownerSurfaceId,
            status: DefinitionStatus::Published,
            payload: $payload,
            revision: $revision,
            dependencies: $dependencies,
        );
    }
}
