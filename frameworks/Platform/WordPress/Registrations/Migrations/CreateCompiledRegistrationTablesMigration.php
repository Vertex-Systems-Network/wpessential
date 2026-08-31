<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations\Migrations;


if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\MigrationInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationTableNames;

final class CreateCompiledRegistrationTablesMigration implements MigrationInterface
{
    private readonly CompiledRegistrationTableNames $tables;

    public function __construct(private readonly DatabaseAdapterInterface $database)
    {
        $this->tables = new CompiledRegistrationTableNames($database);
    }

    public function id(): string
    {
        return '006.create-compiled-registration-atomic-store';
    }

    public function sequence(): int
    {
        return 60;
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

        $generationSql = "CREATE TABLE IF NOT EXISTS `{$this->tables->generations}` (
            network_id BIGINT UNSIGNED NOT NULL,
            site_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            generation BIGINT UNSIGNED NOT NULL,
            checksum CHAR(64) NOT NULL,
            manifest_json LONGTEXT NOT NULL,
            created_at DATETIME(6) NOT NULL,
            corrupt_at DATETIME(6) NULL DEFAULT NULL,
            corrupt_reason VARCHAR(191) NULL DEFAULT NULL,
            PRIMARY KEY (network_id, site_id, generation),
            KEY scope_corrupt_generation (network_id, site_id, corrupt_at, generation)
        ) ENGINE=InnoDB{$charsetSql}";

        $stateSql = "CREATE TABLE IF NOT EXISTS `{$this->tables->state}` (
            network_id BIGINT UNSIGNED NOT NULL,
            site_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            active_generation BIGINT UNSIGNED NULL DEFAULT NULL,
            fallback_generation BIGINT UNSIGNED NULL DEFAULT NULL,
            updated_at DATETIME(6) NOT NULL,
            PRIMARY KEY (network_id, site_id)
        ) ENGINE=InnoDB{$charsetSql}";

        foreach ([$generationSql, $stateSql] as $sql) {
            if ($this->database->query($sql) === false) {
                throw new RuntimeException('Failed to create compiled registration persistence tables: ' . $this->database->lastError());
            }
        }
    }
}
