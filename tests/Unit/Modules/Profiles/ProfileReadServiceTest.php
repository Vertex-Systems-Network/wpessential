<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Profiles;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Profiles\ProfileDefinition;
use WPEssential\Modules\Profiles\ProfileReadService;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class ProfileReadServiceTest extends TestCase
{
    public function testGetAndCatalogExposeOwnedDefinitionsInDeterministicOrder(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition('22222222-2222-4222-8222-222222222222', 'secondary-profile', ['privacy' => 'private']));
        $repository->save($this->definition('11111111-1111-4111-8111-111111111111', 'primary-profile', ['privacy' => 'public'], ['33333333-3333-4333-8333-333333333333'], 2));
        $service = new ProfileReadService($repository);
        $item = $service->get('11111111-1111-4111-8111-111111111111');
        self::assertNotNull($item);
        self::assertSame(ProfileDefinition::TYPE, $item['type']);
        self::assertSame(14, $item['owner_surface_id']);
        self::assertSame(2, $item['revision']);
        self::assertSame(['primary-profile', 'secondary-profile'], array_column($service->catalog(), 'slug'));
    }

    public function testMissingDefinitionReturnsNull(): void
    {
        self::assertNull((new ProfileReadService(new InMemoryDefinitionRepository()))->get('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    }

    public function testWrongOwnerFailsClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition('44444444-4444-4444-8444-444444444444', 'wrong-owner', ownerSurfaceId: 13));
        $this->expectException(InvalidArgumentException::class);
        (new ProfileReadService($repository))->get('44444444-4444-4444-8444-444444444444');
    }

    public function testWrongTypeFailsClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition('55555555-5555-4555-8555-555555555555', 'wrong-type', type: 'dashboard'));
        $this->expectException(InvalidArgumentException::class);
        (new ProfileReadService($repository))->get('55555555-5555-4555-8555-555555555555');
    }

    /** @param list<string> $dependencies */
    private function definition(
        string $id,
        string $slug,
        array $payload = [],
        array $dependencies = [],
        int $revision = 1,
        int $ownerSurfaceId = ProfileDefinition::OWNER_SURFACE_ID,
        string $type = ProfileDefinition::TYPE,
    ): Definition {
        return new Definition($id, $slug, $type, 1, $ownerSurfaceId, DefinitionStatus::Published, $payload, $revision, $dependencies);
    }
}
