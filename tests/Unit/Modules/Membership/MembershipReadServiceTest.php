<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Membership;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Membership\MembershipDefinition;
use WPEssential\Modules\Membership\MembershipReadService;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class MembershipReadServiceTest extends TestCase
{
    public function testGetAndCatalogExposeOwnedDefinitionsInDeterministicOrder(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition('22222222-2222-4222-8222-222222222222', 'secondary-membership', ['tier' => 'pro']));
        $repository->save($this->definition('11111111-1111-4111-8111-111111111111', 'primary-membership', ['tier' => 'basic'], ['33333333-3333-4333-8333-333333333333'], 2));
        $service = new MembershipReadService($repository);
        $item = $service->get('11111111-1111-4111-8111-111111111111');
        self::assertNotNull($item);
        self::assertSame(MembershipDefinition::TYPE, $item['type']);
        self::assertSame(15, $item['owner_surface_id']);
        self::assertSame(['tier' => 'basic'], $item['payload']);
        self::assertSame(['primary-membership', 'secondary-membership'], array_column($service->catalog(), 'slug'));
    }

    public function testMissingDefinitionReturnsNull(): void
    {
        self::assertNull((new MembershipReadService(new InMemoryDefinitionRepository()))->get('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    }

    public function testWrongOwnerFailsClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition('44444444-4444-4444-8444-444444444444', 'wrong-owner', ownerSurfaceId: 14));
        $this->expectException(InvalidArgumentException::class);
        (new MembershipReadService($repository))->get('44444444-4444-4444-8444-444444444444');
    }

    public function testWrongTypeFailsClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition('55555555-5555-4555-8555-555555555555', 'wrong-type', type: 'profile'));
        $this->expectException(InvalidArgumentException::class);
        (new MembershipReadService($repository))->get('55555555-5555-4555-8555-555555555555');
    }

    /** @param list<string> $dependencies */
    private function definition(
        string $id,
        string $slug,
        array $payload = [],
        array $dependencies = [],
        int $revision = 1,
        int $ownerSurfaceId = MembershipDefinition::OWNER_SURFACE_ID,
        string $type = MembershipDefinition::TYPE,
    ): Definition {
        return new Definition($id, $slug, $type, 1, $ownerSurfaceId, DefinitionStatus::Published, $payload, $revision, $dependencies);
    }
}
