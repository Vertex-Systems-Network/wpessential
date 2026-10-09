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

final class DefinitionCreateOnlyRepositoryTest extends TestCase
{
    public function testBothRepositoriesInsertRevisionOneAndPreserveExistingId(): void
    {
        foreach ($this->repositories() as $repo) {
            $original = $this->definition(self::ID_A);
            $repo->create($original);
            self::assertSame('Safe import candidate', $repo->get(self::ID_A)?->payload['preset']['label']);
            self::assertSame(1, $repo->get(self::ID_A)?->revision);

            // Even a larger revision may NOT convert create into an update.
            $this->assertRejected(fn () => $repo->create($this->definition(self::ID_A, revision: 2)));
            $this->assertRejected(fn () => $repo->create($this->definition(self::ID_A)));
            self::assertSame(1, $repo->get(self::ID_A)?->revision);
            self::assertCount(1, $repo->byType('dashboard-widget-preset'));
        }
    }

    public function testBothRepositoriesRejectDuplicateTypeAndSlugButPermitDifferentType(): void
    {
        foreach ($this->repositories() as $repo) {
            $repo->create($this->definition(self::ID_A));
            $this->assertRejected(fn () => $repo->create($this->definition(self::ID_B)));
            self::assertNull($repo->get(self::ID_B));
            $repo->create($this->definition(self::ID_B, type: 'dashboard-widget'));
            self::assertSame('dashboard-widget', $repo->get(self::ID_B)?->type);
            self::assertSame('dashboard-widget-preset', $repo->get(self::ID_A)?->type);
        }
    }

    public function testBothRepositoriesRejectNonInitialRevisionAndWrongChecksumWithoutMutating(): void
    {
        foreach ($this->repositories() as $repo) {
            $this->assertRejected(fn () => $repo->create($this->definition(self::ID_A, revision: 3)));
            $this->assertRejected(fn () => $repo->create($this->definition(
                self::ID_A, checksum: str_repeat('0', 64),
            )));
            self::assertNull($repo->get(self::ID_A));
            $repo->create($this->definition(self::ID_A));
            self::assertNotNull($repo->get(self::ID_A));
        }
    }

    public function testPersistentCreateDelegatesDirectlyToAtomicInsertWithoutFindOrUpdate(): void
    {
        $gateway = new class implements DefinitionTableGatewayInterface {
            public int $insertCount = 0;
            public int $findCount = 0;
            public int $updateCount = 0;

            public function find(string $id): ?array
            {
                ++$this->findCount;
                throw new RuntimeException('Create-only must never preflight with a find.');
            }

            public function insert(array $row, array $dependencies): void
            {
                ++$this->insertCount;
                if ($this->insertCount > 1) {
                    throw new RuntimeException('Duplicate insert rejected atomically.');
                }
            }

            public function updateIfCurrentRevision(
                string $id, int $expectedRevision, array $row, array $dependencies,
            ): bool {
                ++$this->updateCount;
                throw new RuntimeException('Create-only cannot update.');
            }

            public function findByType(string $type): array { return []; }
            public function findDependents(string $id): array { return []; }
        };

        $repo = new PersistentDefinitionRepository($gateway);
        $repo->create($this->definition(self::ID_A));
        $this->assertRejected(fn () => $repo->create($this->definition(self::ID_B, slug: 'another')));
        self::assertSame(2, $gateway->insertCount);
        self::assertSame(0, $gateway->findCount);
        self::assertSame(0, $gateway->updateCount);
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
