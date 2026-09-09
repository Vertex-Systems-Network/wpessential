<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Run\Persistence;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\CreateMigrationRunStoreMigration;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\MigrationRunRecordCodec;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\WpdbMigrationRunRepository;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Database\Migrations\MigrationRegistry;

final class WpdbMigrationRunRepositoryTest extends TestCase
{
    public function testInternalStoreMigrationUsesCanonicalPlatformRegistryContract(): void
    {
        $database = new MigrationRunDatabaseFake();
        $migration = new CreateMigrationRunStoreMigration($database);
        $registry = new MigrationRegistry();
        $registry->register($migration);

        self::assertSame([CreateMigrationRunStoreMigration::ID], array_map(
            static fn ($item): string => $item->id(),
            $registry->ordered(),
        ));
        self::assertFalse($migration->isDestructive());

        $migration->apply();
        self::assertStringContainsString('wpe_custom_table_migration_runs', $database->lastQuery);
        self::assertStringContainsString('site_id', $database->lastQuery);
    }

    public function testRepositoryPersistsSiteScopedRecordAndUsesCasRevision(): void
    {
        $database = new MigrationRunDatabaseFake();
        $repository = new WpdbMigrationRunRepository($database, new MigrationRunRecordCodec(), 7);
        $run = self::run();

        $created = $repository->create($run);
        self::assertSame($run->fingerprint(), $created->fingerprint());
        self::assertSame(7, $database->inserted['site_id'] ?? null);

        $next = $run->transition(MigrationRunState::AwaitingReview);
        $stored = $repository->compareAndSwap($next, 1);
        self::assertSame(MigrationRunState::AwaitingReview, $stored->state);
        self::assertSame(2, $stored->stateRevision);
    }

    public function testStaleCasFailsClosed(): void
    {
        $database = new MigrationRunDatabaseFake();
        $repository = new WpdbMigrationRunRepository($database, new MigrationRunRecordCodec(), 7);
        $run = self::run();
        $repository->create($run);
        $database->casResult = 0;

        $this->expectException(RuntimeException::class);
        $repository->compareAndSwap($run->transition(MigrationRunState::AwaitingReview), 1);
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

final class MigrationRunDatabaseFake implements DatabaseAdapterInterface
{
    /** @var array<string,mixed> */
    public array $inserted = [];
    /** @var array<string,mixed>|null */
    private ?array $row = null;
    /** @var list<mixed> */
    private array $preparedArgs = [];
    public string $lastQuery = '';
    public int|bool $casResult = 1;

    public function networkTablePrefix(): string { return 'wp_'; }
    public function charsetCollate(): string { return 'DEFAULT CHARACTER SET utf8mb4'; }
    public function prepare(string $query, mixed ...$args): string
    {
        $this->preparedArgs = $args;
        return $query;
    }
    public function getRow(string $query): ?array { return $this->row; }
    public function getResults(string $query): array { return []; }
    public function getVar(string $query): mixed { return null; }
    public function query(string $query): int|bool
    {
        $this->lastQuery = $query;
        if (str_starts_with($query, 'UPDATE ')) {
            if ($this->casResult !== 1 || $this->row === null) {
                return $this->casResult;
            }
            $args = $this->preparedArgs;
            $this->row['storage_version'] = $args[0];
            $this->row['plan_fingerprint'] = $args[1];
            $this->row['table_key'] = $args[2];
            $this->row['target_definition_id'] = $args[3];
            $this->row['target_revision'] = $args[4];
            $this->row['target_schema_version'] = $args[5];
            $this->row['state'] = $args[6];
            $this->row['state_revision'] = $args[7];
        }
        return $this->casResult;
    }
    public function insert(string $table, array $data, array $formats = []): bool
    {
        $this->inserted = $data;
        $this->row = [
            'storage_version' => $data['storage_version'],
            'run_id' => $data['run_id'],
            'plan_fingerprint' => $data['plan_fingerprint'],
            'table_key' => $data['table_key'],
            'target_definition_id' => $data['target_definition_id'],
            'target_revision' => $data['target_revision'],
            'target_schema_version' => $data['target_schema_version'],
            'state' => $data['state'],
            'state_revision' => $data['state_revision'],
        ];
        return true;
    }
    public function lastError(): string { return ''; }
    public function beginTransaction(): void {}
    public function commit(): void {}
    public function rollBack(): void {}
}
