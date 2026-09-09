<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\MigrationInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class CreateMigrationExecutionConfirmationStoreMigration implements MigrationInterface
{
    public const ID = '221.custom_tables_migration_execution_confirmations_v1';
    public const SEQUENCE = 221;

    private string $table;

    public function __construct(private DatabaseAdapterInterface $database)
    {
        $prefix = $database->networkTablePrefix();
        if (preg_match('/^[A-Za-z0-9_]+$/', $prefix) !== 1) {
            throw new RuntimeException('Custom Tables execution confirmation store prefix is invalid.');
        }

        $this->table = $prefix . 'wpe_custom_table_migration_confirmations';
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
            plan_fingerprint CHAR(64) NOT NULL,
            readiness_state_revision BIGINT UNSIGNED NOT NULL,
            actor_type VARCHAR(16) NOT NULL,
            actor_user_id BIGINT UNSIGNED NULL,
            confirmed_at DATETIME(6) NOT NULL,
            PRIMARY KEY (site_id, run_id),
            KEY plan_fingerprint (site_id, plan_fingerprint),
            KEY readiness_revision (site_id, readiness_state_revision)
        ) ENGINE=InnoDB{$charsetSql}";

        if ($this->database->query($sql) === false) {
            throw new RuntimeException('Unable to initialize Custom Tables execution confirmation store.');
        }
    }
}
