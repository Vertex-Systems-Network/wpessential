<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Definitions;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\DefinitionCreateOnlyRepositoryInterface;
use WPEssential\Contracts\DefinitionTableGatewayInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionRowCodec;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\Definitions\InMemoryDefinitionTableGateway;
use WPEssential\Platform\Definitions\PersistentDefinitionRepository;

final class DefinitionCreateOnlyGatewayTest extends TestCase
{
    public function testDisposableGatewayMirrorsPhysicalUuidAndTypeSlugUniqueConstraints(): void
    {
        $gateway = new InMemoryDefinitionTableGateway();
        $codec = new DefinitionRowCodec();
        $a = $codec->encode($this->definition(self::ID_A));
        $gateway->insert($a, []);
        self::assertSame(self::ID_A, $gateway->find(self::ID_A)['id']);

        $this->assertRejected(fn () => $gateway->insert($a, []));
        $b = $codec->encode($this->definition(self::ID_B));
        $this->assertRejected(fn () => $gateway->insert($b, []));
        self::assertNull($gateway->find(self::ID_B));
        self::assertSame(self::ID_A, $gateway->find(self::ID_A)['id']);

        $otherType = $codec->encode($this->definition(self::ID_B, type: 'dashboard-widget'));
        $gateway->insert($otherType, []);
        self::assertSame('dashboard-widget', $gateway->find(self::ID_B)['type']);
    }

    public function testInsertConflictDoesNotPartiallyPersistDependencyProjection(): void
    {
        $gateway = new InMemoryDefinitionTableGateway();
        $codec = new DefinitionRowCodec();
        $gateway->insert($codec->encode($this->definition(self::ID_A)), []);
        $this->assertRejected(fn () => $gateway->insert(
            $codec->encode($this->definition(self::ID_B)),
            [self::ID_A],
        ));
        self::assertSame([], $gateway->findDependents(self::ID_A));
        self::assertNull($gateway->find(self::ID_B));
    }

    private const ID_A = '11111111-1111-4111-8111-111111111111';
    private const ID_B = '22222222-2222-4222-8222-222222222222';

    private function definition(
        string $id,
        string $slug = 'preset-alpha',
        string $type = 'dashboard-widget-preset',
        int $revision = 1,
        ?string $checksum = null,
    ): Definition {
        return new Definition(
            id: $id,
            slug: $slug,
            type: $type,
            schemaVersion: 1,
            ownerSurfaceId: 10,
            status: DefinitionStatus::Published,
            payload: ['preset' => ['label' => 'Safe import candidate']],
            revision: $revision,
            checksum: $checksum,
        );
    }

    /** @return list<DefinitionCreateOnlyRepositoryInterface> */
    private function repositories(): array
    {
        return [
            new InMemoryDefinitionRepository(),
            new PersistentDefinitionRepository(new InMemoryDefinitionTableGateway()),
        ];
    }

    /** @param callable():void $operation */
    private function assertRejected(callable $operation): void
    {
        try {
            $operation();
            self::fail('Create-only repository accepted an invalid/overwriting insert.');
        } catch (RuntimeException) {
            self::assertTrue(true);
        }
    }
}
