<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Run;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomTables\Migration\Run\InMemoryMigrationRunRepository;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;

final class MigrationRunRepositoryTest extends TestCase
{
    public function testCreateAndGetPreserveCanonicalRun(): void
    {
        $repository = new InMemoryMigrationRunRepository();
        $run = $this->run();

        self::assertSame($run, $repository->create($run));
        self::assertSame($run, $repository->get($run->id));
    }

    public function testDuplicateCreateFailsClosed(): void
    {
        $repository = new InMemoryMigrationRunRepository();
        $run = $this->run();
        $repository->create($run);

        $this->expectException(RuntimeException::class);
        $repository->create($run);
    }

    public function testCompareAndSwapAcceptsLegalNextRevision(): void
    {
        $repository = new InMemoryMigrationRunRepository();
        $run = $repository->create($this->run());
        $next = $run->transition(MigrationRunState::AwaitingReview);

        self::assertSame($next, $repository->compareAndSwap($next, 1));
        self::assertSame(2, $repository->get($run->id)?->stateRevision);
    }

    public function testCompareAndSwapRejectsStaleExpectedRevision(): void
    {
        $repository = new InMemoryMigrationRunRepository();
        $run = $repository->create($this->run());
        $next = $run->transition(MigrationRunState::AwaitingReview);
        $repository->compareAndSwap($next, 1);

        $this->expectException(RuntimeException::class);
        $repository->compareAndSwap($next->transition(MigrationRunState::Approved), 1);
    }

    public function testCompareAndSwapRejectsImmutableIdentityMutation(): void
    {
        $repository = new InMemoryMigrationRunRepository();
        $run = $repository->create($this->run());

        $mutated = new MigrationRun(
            id: $run->id,
            planFingerprint: str_repeat('b', 64),
            tableKey: $run->tableKey,
            targetDefinitionId: $run->targetDefinitionId,
            targetRevision: $run->targetRevision,
            targetSchemaVersion: $run->targetSchemaVersion,
            state: MigrationRunState::AwaitingReview,
            stateRevision: 2,
        );

        $this->expectException(InvalidArgumentException::class);
        $repository->compareAndSwap($mutated, 1);
    }

    private function run(): MigrationRun
    {
        return new MigrationRun(
            id: '123e4567-e89b-42d3-a456-426614174000',
            planFingerprint: str_repeat('a', 64),
            tableKey: 'orders',
            targetDefinitionId: '123e4567-e89b-42d3-a456-426614174001',
            targetRevision: 3,
            targetSchemaVersion: 4,
            state: MigrationRunState::Planned,
        );
    }
}
