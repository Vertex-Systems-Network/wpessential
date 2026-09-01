<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\MigrationInterface;
use WPEssential\Platform\Database\Migrations\InMemoryMigrationStateStore;
use WPEssential\Platform\Database\Migrations\MigrationCoordinator;
use WPEssential\Platform\Database\Migrations\MigrationRegistry;
use WPEssential\Platform\Database\Migrations\MigrationRunner;

final class MigrationCoordinatorTest extends TestCase
{
    public function testLateRegisteredMigrationRunsExactlyOnceAfterInitialCorePass(): void
    {
        $state = new InMemoryMigrationStateStore();
        $registry = new MigrationRegistry();
        $coordinator = new MigrationCoordinator($registry, new MigrationRunner($registry, $state));
        $core = new CoordinatorTestMigration('001.core-fixture', 10);
        $contributed = new CoordinatorTestMigration('090.pro-fixture', 900);

        $coordinator->register($core);
        self::assertSame(['001.core-fixture'], $coordinator->runPending());
        self::assertSame(1, $core->applyCount);

        $coordinator->register($contributed);
        self::assertSame(['090.pro-fixture'], $coordinator->runPending());
        self::assertSame(1, $core->applyCount);
        self::assertSame(1, $contributed->applyCount);

        self::assertSame([], $coordinator->runPending());
        self::assertSame(1, $core->applyCount);
        self::assertSame(1, $contributed->applyCount);
        self::assertSame(['001.core-fixture', '090.pro-fixture'], $state->appliedIds());
    }

    public function testDuplicateMigrationIdStillFailsClosedThroughCanonicalRegistry(): void
    {
        $coordinator = $this->coordinator();
        $coordinator->register(new CoordinatorTestMigration('090.same-id', 900));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already registered');
        $coordinator->register(new CoordinatorTestMigration('090.same-id', 901));
    }

    public function testDuplicateMigrationSequenceStillFailsClosedThroughCanonicalRegistry(): void
    {
        $coordinator = $this->coordinator();
        $coordinator->register(new CoordinatorTestMigration('090.first', 900));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('sequence 900 is already registered');
        $coordinator->register(new CoordinatorTestMigration('091.second', 900));
    }

    public function testDestructiveMigrationWithoutRecoveryPlanIsRejectedByCanonicalRunner(): void
    {
        $state = new InMemoryMigrationStateStore();
        $registry = new MigrationRegistry();
        $coordinator = new MigrationCoordinator($registry, new MigrationRunner($registry, $state));
        $migration = new CoordinatorTestMigration('090.destructive', 900, destructive: true);
        $coordinator->register($migration);

        try {
            $coordinator->runPending();
            self::fail('Expected destructive migration safety rejection.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('requires an explicit recovery plan', $exception->getMessage());
        }

        self::assertSame(0, $migration->applyCount);
        self::assertSame([], $state->appliedIds());
    }

    public function testDestructiveMigrationWithRecoveryPlanRemainsRunnerControlled(): void
    {
        $state = new InMemoryMigrationStateStore();
        $registry = new MigrationRegistry();
        $coordinator = new MigrationCoordinator($registry, new MigrationRunner($registry, $state));
        $migration = new CoordinatorTestMigration(
            '090.recoverable',
            900,
            destructive: true,
            recoveryPlan: 'Restore the pre-migration snapshot before retrying.',
        );
        $coordinator->register($migration);

        self::assertSame(['090.recoverable'], $coordinator->runPending());
        self::assertSame(1, $migration->applyCount);
        self::assertSame(['090.recoverable'], $state->appliedIds());
    }

    private function coordinator(): MigrationCoordinator
    {
        $registry = new MigrationRegistry();
        return new MigrationCoordinator(
            $registry,
            new MigrationRunner($registry, new InMemoryMigrationStateStore()),
        );
    }
}

final class CoordinatorTestMigration implements MigrationInterface
{
    public int $applyCount = 0;

    public function __construct(
        private readonly string $migrationId,
        private readonly int $migrationSequence,
        private readonly bool $destructive = false,
        private readonly ?string $recoveryPlan = null,
    ) {}

    public function id(): string
    {
        return $this->migrationId;
    }

    public function sequence(): int
    {
        return $this->migrationSequence;
    }

    public function isDestructive(): bool
    {
        return $this->destructive;
    }

    public function recoveryPlan(): ?string
    {
        return $this->recoveryPlan;
    }

    public function apply(): void
    {
        ++$this->applyCount;
    }
}
