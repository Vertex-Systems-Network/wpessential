<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Review;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Review\MigrationReviewSnapshot;
use WPEssential\Modules\CustomTables\Migration\Review\Persistence\CreateMigrationReviewSnapshotStoreMigration;
use WPEssential\Modules\CustomTables\Migration\Review\Persistence\WpdbMigrationReviewSnapshotRepository;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class WpdbMigrationReviewSnapshotRepositoryTest extends TestCase
{
    private const RUN_ID = '11111111-1111-4111-8111-111111111111';

    public function testRecordInsertsOnceAndReadsBackTheSameSnapshot(): void
    {
        $database = new SnapshotRowDatabase();
        $repository = new WpdbMigrationReviewSnapshotRepository($database, 3);

        $stored = $repository->record(self::snapshot());

        self::assertSame(self::snapshot()->canonical(), $stored->canonical());
        self::assertSame('wp_wpe_custom_table_migration_reviews', $database->table);
        self::assertSame(3, $database->row['site_id'] ?? null);
        self::assertSame(self::snapshot()->canonical(), $repository->snapshotFor(strtoupper(self::RUN_ID))?->canonical());
    }

    public function testSecondRecordForTheSameRunIsRejected(): void
    {
        $repository = new WpdbMigrationReviewSnapshotRepository(new SnapshotRowDatabase(), 3);
        $repository->record(self::snapshot());

        $this->expectException(RuntimeException::class);
        $repository->record(self::snapshot());
    }

    public function testReadbackMismatchFailsClosed(): void
    {
        $database = new SnapshotRowDatabase();
        $database->tamper = true;

        $this->expectException(RuntimeException::class);
        (new WpdbMigrationReviewSnapshotRepository($database, 3))->record(self::snapshot());
    }

    public function testMigrationCreatesNonDestructiveSequencedStore(): void
    {
        $database = new SnapshotRowDatabase();
        $migration = new CreateMigrationReviewSnapshotStoreMigration($database);
        $migration->apply();

        self::assertSame('222.custom_tables_migration_review_snapshots_v1', $migration->id());
        self::assertSame(222, $migration->sequence());
        self::assertFalse($migration->isDestructive());
        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS `wp_wpe_custom_table_migration_reviews`', $database->queries[0] ?? '');
        self::assertStringContainsString('PRIMARY KEY (site_id, run_id)', $database->queries[0] ?? '');
    }

    private static function snapshot(): MigrationReviewSnapshot
    {
        return new MigrationReviewSnapshot(
            self::RUN_ID,
            str_repeat('a', 64),
            new MigrationGenerationStamp(
                planFingerprint: str_repeat('b', 64),
                sourceObservedFingerprint: str_repeat('c', 64),
                targetDefinitionId: '78888888-8888-4888-8888-888888888888',
                targetRevision: 2,
                targetSchemaVersion: 1,
                provider: 'mysql',
                providerVersion: '8.0.36',
            ),
        );
    }
}

final class SnapshotRowDatabase implements DatabaseAdapterInterface
{
    /** @var array<string,mixed>|null */
    public ?array $row = null;
    public string $table = '';
    public bool $tamper = false;
    /** @var list<string> */
    public array $queries = [];

    public function networkTablePrefix(): string { return 'wp_'; }
    public function charsetCollate(): string { return ''; }
    public function prepare(string $query, mixed ...$args): string { return $query; }
    public function getRow(string $query): ?array
    {
        if ($this->row === null) {
            return null;
        }
        $row = array_map(static fn (mixed $value): mixed => is_int($value) ? (string) $value : $value, $this->row);
        if ($this->tamper) {
            $row['preview_hash'] = str_repeat('d', 64);
        }

        return $row;
    }
    public function getResults(string $query): array { return []; }
    public function getVar(string $query): mixed { return null; }
    public function query(string $query): int|bool
    {
        $this->queries[] = $query;
        return 0;
    }
    public function insert(string $table, array $data, array $formats = []): bool
    {
        $this->table = $table;
        $this->row = $data;
        return true;
    }
    public function lastError(): string { return ''; }
    public function beginTransaction(): void {}
    public function commit(): void {}
    public function rollBack(): void {}
}
