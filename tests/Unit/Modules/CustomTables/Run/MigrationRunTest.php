<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Run;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;

final class MigrationRunTest extends TestCase
{
    private const RUN_ID = '11111111-1111-4111-8111-111111111111';
    private const DEFINITION_ID = '22222222-2222-4222-8222-222222222222';

    public function testReviewedLifecycleCanAdvanceDeterministically(): void
    {
        $run = $this->run();

        $run = $run->transition(MigrationRunState::AwaitingReview)
            ->transition(MigrationRunState::Approved)
            ->transition(MigrationRunState::Revalidating)
            ->transition(MigrationRunState::Running)
            ->transition(MigrationRunState::Verifying)
            ->transition(MigrationRunState::Applied);

        self::assertSame(MigrationRunState::Applied, $run->state);
        self::assertSame(7, $run->stateRevision);
        self::assertTrue($run->state->terminal());
        self::assertSame($run->fingerprint(), $run->fingerprint());
    }

    public function testCancellationIsUnavailableAfterMutationBoundary(): void
    {
        $run = $this->run()
            ->transition(MigrationRunState::AwaitingReview)
            ->transition(MigrationRunState::Approved)
            ->transition(MigrationRunState::Revalidating)
            ->transition(MigrationRunState::Running);

        self::assertFalse($run->canTransitionTo(MigrationRunState::CancelledBeforeMutation));

        $this->expectException(InvalidArgumentException::class);
        $run->transition(MigrationRunState::CancelledBeforeMutation);
    }

    public function testTerminalStateCannotTransition(): void
    {
        $run = $this->run()->transition(MigrationRunState::CancelledBeforeMutation);

        self::assertTrue($run->state->terminal());
        self::assertFalse($run->canTransitionTo(MigrationRunState::AwaitingReview));

        $this->expectException(InvalidArgumentException::class);
        $run->transition(MigrationRunState::AwaitingReview);
    }

    public function testInvalidPlanFingerprintFailsClosed(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MigrationRun(
            id: self::RUN_ID,
            planFingerprint: 'not-a-fingerprint',
            tableKey: 'settings',
            targetDefinitionId: self::DEFINITION_ID,
            targetRevision: 2,
            targetSchemaVersion: 3,
            state: MigrationRunState::Planned,
        );
    }

    private function run(): MigrationRun
    {
        return new MigrationRun(
            id: self::RUN_ID,
            planFingerprint: str_repeat('a', 64),
            tableKey: 'settings',
            targetDefinitionId: self::DEFINITION_ID,
            targetRevision: 2,
            targetSchemaVersion: 3,
            state: MigrationRunState::Planned,
        );
    }
}
