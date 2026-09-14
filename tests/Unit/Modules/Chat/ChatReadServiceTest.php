<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Chat;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Chat\ChatReadService;
use WPEssential\Modules\Chat\ConversationDefinition;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class ChatReadServiceTest extends TestCase
{
    public function testGetAndCatalogExposeOwnedDefinitionsInDeterministicOrder(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition('22222222-2222-4222-8222-222222222222', 'secondary-conversation', ['visibility' => 'private']));
        $repository->save($this->definition('11111111-1111-4111-8111-111111111111', 'primary-conversation', ['visibility' => 'authorized'], ['33333333-3333-4333-8333-333333333333'], 2));
        $service = new ChatReadService($repository);
        $item = $service->get('11111111-1111-4111-8111-111111111111');
        self::assertNotNull($item);
        self::assertSame(ConversationDefinition::TYPE, $item['type']);
        self::assertSame(21, $item['owner_surface_id']);
        self::assertSame(['primary-conversation', 'secondary-conversation'], array_column($service->catalog(), 'slug'));
    }

    public function testMissingDefinitionReturnsNull(): void
    {
        self::assertNull((new ChatReadService(new InMemoryDefinitionRepository()))->get('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    }

    public function testWrongOwnerFailsClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition('44444444-4444-4444-8444-444444444444', 'wrong-owner', ownerSurfaceId: 20));
        $this->expectException(InvalidArgumentException::class);
        (new ChatReadService($repository))->get('44444444-4444-4444-8444-444444444444');
    }

    public function testWrongTypeFailsClosed(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition('55555555-5555-4555-8555-555555555555', 'wrong-type', type: 'email-template'));
        $this->expectException(InvalidArgumentException::class);
        (new ChatReadService($repository))->get('55555555-5555-4555-8555-555555555555');
    }

    /** @param list<string> $dependencies */
    private function definition(string $id, string $slug, array $payload = [], array $dependencies = [], int $revision = 1, int $ownerSurfaceId = ConversationDefinition::OWNER_SURFACE_ID, string $type = ConversationDefinition::TYPE): Definition
    {
        return new Definition($id, $slug, $type, 1, $ownerSurfaceId, DefinitionStatus::Published, $payload, $revision, $dependencies);
    }
}
