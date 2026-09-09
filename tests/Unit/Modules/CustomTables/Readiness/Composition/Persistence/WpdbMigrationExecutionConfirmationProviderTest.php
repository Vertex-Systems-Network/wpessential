<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Readiness\Composition\Persistence;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\ExecutionActorType;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\CreateMigrationExecutionConfirmationStoreMigration;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationProvider;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Database\Migrations\MigrationRegistry;

final class WpdbMigrationExecutionConfirmationProviderTest extends TestCase
{
    public function testInternalConfirmationStoreUsesCanonicalNonDestructiveMigration(): void
    {
        $database = new ConfirmationDatabaseFake();
        $migration = new CreateMigrationExecutionConfirmationStoreMigration($database);
        $registry = new MigrationRegistry();
        $registry->register($migration);

        self::assertSame(
            [CreateMigrationExecutionConfirmationStoreMigration::ID],
            array_map(static fn ($item): string => $item->id(), $registry->ordered()),
        );
        self::assertFalse($migration->isDestructive());

        $migration->apply();
        self::assertStringContainsString('wpe_custom_table_migration_confirmations', $database->lastQuery);
        self::assertStringContainsString('site_id', $database->lastQuery);
        self::assertStringNotContainsString('DROP TABLE', $database->lastQuery);
        self::assertStringNotContainsString('ALTER TABLE', $database->lastQuery);
    }

    public function testProviderReadsOnlyCurrentSiteAndReturnsRevisionBoundConfirmation(): void
    {
        $database = new ConfirmationDatabaseFake();
        $database->row = [
            'run_id' => '11111111-1111-4111-8111-111111111111',
            'plan_fingerprint' => str_repeat('a', 64),
            'readiness_state_revision' => '4',
            'actor_type' => 'user',
            'actor_user_id' => '7',
        ];
        $provider = new WpdbMigrationExecutionConfirmationProvider($database, 9);

        $confirmation = $provider->confirmationFor('11111111-1111-4111-8111-111111111111');

        self::assertNotNull($confirmation);
        self::assertSame(ExecutionActorType::User, $confirmation->actorType);
        self::assertSame(7, $confirmation->actorUserId);
        self::assertSame(4, $confirmation->readinessStateRevision);
        self::assertSame([9, '11111111-1111-4111-8111-111111111111'], $database->preparedArgs);
        self::assertStringStartsWith('SELECT ', $database->lastPreparedQuery);
        self::assertStringNotContainsString('INSERT ', $database->lastPreparedQuery);
        self::assertStringNotContainsString('UPDATE ', $database->lastPreparedQuery);
        self::assertStringNotContainsString('DELETE ', $database->lastPreparedQuery);
    }

    public function testMalformedStoredConfirmationFailsClosed(): void
    {
        $database = new ConfirmationDatabaseFake();
        $database->row = [
            'run_id' => '11111111-1111-4111-8111-111111111111',
            'plan_fingerprint' => str_repeat('a', 64),
            'readiness_state_revision' => '4',
            'actor_type' => 'invalid',
            'actor_user_id' => null,
        ];

        $this->expectException(RuntimeException::class);
        (new WpdbMigrationExecutionConfirmationProvider($database, 9))
            ->confirmationFor('11111111-1111-4111-8111-111111111111');
    }
}

final class ConfirmationDatabaseFake implements DatabaseAdapterInterface
{
    /** @var array<string,mixed>|null */
    public ?array $row = null;
    /** @var list<mixed> */
    public array $preparedArgs = [];
    public string $lastPreparedQuery = '';
    public string $lastQuery = '';

    public function networkTablePrefix(): string { return 'wp_'; }
    public function charsetCollate(): string { return 'DEFAULT CHARACTER SET utf8mb4'; }
    public function prepare(string $query, mixed ...$args): string
    {
        $this->lastPreparedQuery = $query;
        $this->preparedArgs = $args;
        return $query;
    }
    public function getRow(string $query): ?array { return $this->row; }
    public function getResults(string $query): array { return []; }
    public function getVar(string $query): mixed { return null; }
    public function query(string $query): int|bool
    {
        $this->lastQuery = $query;
        return 1;
    }
    public function insert(string $table, array $data, array $formats = []): bool { return false; }
    public function lastError(): string { return ''; }
    public function beginTransaction(): void {}
    public function commit(): void {}
    public function rollBack(): void {}
}
