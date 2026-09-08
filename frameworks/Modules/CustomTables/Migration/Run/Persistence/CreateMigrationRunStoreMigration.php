<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Run\Persistence;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\MigrationInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class CreateMigrationRunStoreMigration implements MigrationInterface
{
    public const ID = '220.custom_tables_migration_runs_v1';
    public const SEQUENCE = 220;

    private string $table;

    public function __construct(private DatabaseAdapterInterface $database)
    {
        $prefix = $database->networkTablePrefix();
        if (preg_match('/^[A-Za-z0-9_]+$/', $prefix) !== 1) {
            throw new RuntimeException('Custom Tables Migration Run store prefix is invalid.');
        }
        $this->table = $prefix . 'wpe_custom_table_migration_runs';
    }

    public function id(): string
    {
        return self::ID;
    }

    public function sequence(): int
    {
        return self::SEQUENCE;
    }

    public function isDestructive(): bool
    {
        return false;
    }

    public function recoveryPlan(): ?string
    {
        return null;
    }

    public function apply(): void
    {
        $charset = trim($this->database->charsetCollate());
        $charsetSql = $charset === '' ? '' : ' ' . $charset;
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            site_id BIGINT UNSIGNED NOT NULL,
            run_id CHAR(36) NOT NULL,
            storage_version SMALLINT UNSIGNED NOT NULL,
            plan_fingerprint CHAR(64) NOT NULL,
            table_key VARCHAR(64) NOT NULL,
            target_definition_id CHAR(36) NOT NULL,
            target_revision BIGINT UNSIGNED NOT NULL,
            target_schema_version BIGINT UNSIGNED NOT NULL,
            state VARCHAR(32) NOT NULL,
            state_revision BIGINT UNSIGNED NOT NULL,
            created_at DATETIME(6) NOT NULL,
            updated_at DATETIME(6) NOT NULL,
            PRIMARY KEY (site_id, run_id),
            KEY plan_fingerprint (plan_fingerprint),
            KEY table_key (site_id, table_key)
        ) ENGINE=InnoDB{$charsetSql}";

        if ($this->database->query($sql) === false) {
            throw new RuntimeException('Unable to initialize Custom Tables Migration Run store.');
        }
    }
}
