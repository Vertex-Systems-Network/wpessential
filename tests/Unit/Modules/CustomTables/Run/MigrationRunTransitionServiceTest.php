<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Run;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomTables\Migration\Run\InMemoryMigrationRunRepository;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunTransitionService;

final class MigrationRunTransitionServiceTest extends TestCase
{
    public function testTransitionUsesRepositoryCasAndPersistsNextStateInReferenceRepository(): void
    {
        $repository = new InMemoryMigrationRunRepository();
        $run = self::run();
        $repository->create($run);

        $next = (new MigrationRunTransitionService($repository))->transition(
            $run->id,
            1,
            MigrationRunState::AwaitingReview,
        );

        self::assertSame(MigrationRunState::AwaitingReview, $next->state);
        self::assertSame(2, $next->stateRevision);
        self::assertSame($next->fingerprint(), $repository->get($run->id)?->fingerprint());
    }

    public function testStaleRevisionFailsClosedWithoutChangingStoredRun(): void
    {
        $repository = new InMemoryMigrationRunRepository();
        $run = self::run();
        $repository->create($run);

        $this->expectException(RuntimeException::class);
        try {
            (new MigrationRunTransitionService($repository))->transition(
                $run->id,
                2,
                MigrationRunState::AwaitingReview,
            );
        } finally {
            self::assertSame(MigrationRunState::Planned, $repository->get($run->id)?->state);
        }
    }

    public function testMissingRunFailsClosed(): void
    {
        $this->expectException(RuntimeException::class);
        (new MigrationRunTransitionService(new InMemoryMigrationRunRepository()))->transition(
            '11111111-1111-4111-8111-111111111111',
            1,
            MigrationRunState::AwaitingReview,
        );
    }

    private static function run(): MigrationRun
    {
        return new MigrationRun(
            id: '11111111-1111-4111-8111-111111111111',
            planFingerprint: str_repeat('a', 64),
            tableKey: 'orders',
            targetDefinitionId: '22222222-2222-4222-8222-222222222222',
            targetRevision: 1,
            targetSchemaVersion: 1,
            state: MigrationRunState::Planned,
        );
    }
}
