<?php

declare(strict_types=1);

namespace WPEssential\Platform\Database\Migrations;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\MigrationStateStoreInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class WpdbMigrationStateStore implements MigrationStateStoreInterface
{
    private readonly string $table;
    private bool $ready = false;

    public function __construct(private readonly DatabaseAdapterInterface $database)
    {
        $prefix = $database->networkTablePrefix();
        if (preg_match('/^[A-Za-z0-9_]+$/', $prefix) !== 1) {
            throw new RuntimeException('Migration state table prefix is invalid.');
        }
        $this->table = $prefix . 'wpe_migrations';
    }

    public function appliedIds(): array
    {
        $this->ensureTable();
        $rows = $this->database->getResults("SELECT migration_id FROM `{$this->table}` ORDER BY migration_id ASC");
        return array_values(array_map(
            static fn (array $row): string => (string) ($row['migration_id'] ?? ''),
            $rows,
        ));
    }

    public function markApplied(string $id): void
    {
        $this->assertMigrationId($id);
        $this->ensureTable();
        $this->database->query($this->database->prepare(
            "INSERT IGNORE INTO `{$this->table}` (migration_id, applied_at) VALUES (%s, UTC_TIMESTAMP(6))",
            $id,
        ));

        $stored = $this->database->getVar($this->database->prepare(
            "SELECT migration_id FROM `{$this->table}` WHERE migration_id = %s",
            $id,
        ));
        if (!is_string($stored) || $stored !== $id) {
            throw new RuntimeException('Migration state could not be persisted.');
        }
    }

    private function ensureTable(): void
    {
        if ($this->ready) {
            return;
        }

        $charset = trim($this->database->charsetCollate());
        $charsetSql = $charset === '' ? '' : ' ' . $charset;
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            migration_id VARCHAR(191) NOT NULL,
            applied_at DATETIME(6) NOT NULL,
            PRIMARY KEY (migration_id),
            KEY applied_at (applied_at)
        ) ENGINE=InnoDB{$charsetSql}";
        if ($this->database->query($sql) === false) {
            throw new RuntimeException('Unable to initialize persistent migration state table.');
        }
        $this->ready = true;
    }

    private function assertMigrationId(string $id): void
    {
        if (preg_match('/^[0-9]{3}\.[a-z0-9][a-z0-9._-]*$/', $id) !== 1) {
            throw new InvalidArgumentException('Migration id must use the canonical NNN.slug format.');
        }
    }
}
