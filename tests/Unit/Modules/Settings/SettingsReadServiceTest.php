<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Settings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Settings\SettingsDefinition;
use WPEssential\Modules\Settings\SettingsReadService;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class SettingsReadServiceTest extends TestCase
{
    public function testGetAndCatalogExposeOwnedDefinitionsInDeterministicOrder(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $dependencyId = '33333333-3333-4333-8333-333333333333';
        $repository->save($this->definition('22222222-2222-4222-8222-222222222222', 'secondary-settings', ['scope' => 'network']));
        $repository->save($this->definition('11111111-1111-4111-8111-111111111111', 'primary-settings', ['scope' => 'site'], [$dependencyId], 3));

        $service = new SettingsReadService($repository);
        $definition = $service->get('11111111-1111-4111-8111-111111111111');

        self::assertNotNull($definition);
        self::assertSame('primary-settings', $definition['slug']);
        self::assertSame(SettingsDefinition::TYPE, $definition['type']);
        self::assertSame(SettingsDefinition::OWNER_SURFACE_ID, $definition['owner_surface_id']);
        self::assertSame('published', $definition['status']);
        self::assertSame(3, $definition['revision']);
        self::assertSame([$dependencyId], $definition['dependencies']);
        self::assertSame(['scope' => 'site'], $definition['payload']);
        self::assertSame(['primary-settings', 'secondary-settings'], array_column($service->catalog(), 'slug'));
    }

    public function testMissingDefinitionReturnsNull(): void
    {
        self::assertNull((new SettingsReadService(new InMemoryDefinitionRepository()))->get('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    }

    public function testWrongOwnerFailsClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition('44444444-4444-4444-8444-444444444444', 'foreign-owner', ownerSurfaceId: 11));
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('owner surface is invalid');
        (new SettingsReadService($repository))->get('44444444-4444-4444-8444-444444444444');
    }

    public function testWrongTypeFailsClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition('55555555-5555-4555-8555-555555555555', 'wrong-type', type: 'admin-menu'));
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('definition type is invalid');
        (new SettingsReadService($repository))->get('55555555-5555-4555-8555-555555555555');
    }

    /** @param list<string> $dependencies */
    private function definition(
        string $id,
        string $slug,
        array $payload = ['scope' => 'site'],
        array $dependencies = [],
        int $revision = 1,
        int $ownerSurfaceId = SettingsDefinition::OWNER_SURFACE_ID,
        string $type = SettingsDefinition::TYPE,
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
