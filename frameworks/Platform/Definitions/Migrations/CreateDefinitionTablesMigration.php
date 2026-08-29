<?php

declare(strict_types=1);

namespace WPEssential\Platform\Definitions\Migrations;

use RuntimeException;
use WPEssential\Contracts\MigrationInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Definitions\DefinitionTableNames;

final class CreateDefinitionTablesMigration implements MigrationInterface
{
    private readonly DefinitionTableNames $tables;

    public function __construct(private readonly DatabaseAdapterInterface $database)
    {
        $this->tables = new DefinitionTableNames($database);
    }

    public function id(): string
    {
        return '007.create-definition-persistence';
    }

    public function sequence(): int
    {
        return 70;
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

        $definitionsSql = "CREATE TABLE IF NOT EXISTS `{$this->tables->definitions}` (
            network_id BIGINT UNSIGNED NOT NULL,
            site_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            id CHAR(36) NOT NULL,
            slug VARCHAR(191) NOT NULL,
            type VARCHAR(191) NOT NULL,
            schema_version INT UNSIGNED NOT NULL,
            owner_surface_id TINYINT UNSIGNED NOT NULL,
            status VARCHAR(32) NOT NULL,
            payload_json LONGTEXT NOT NULL,
            revision BIGINT UNSIGNED NOT NULL,
            checksum CHAR(64) NOT NULL,
            created_at DATETIME(6) NOT NULL,
            updated_at DATETIME(6) NOT NULL,
            PRIMARY KEY (network_id, site_id, id),
            UNIQUE KEY scope_type_slug (network_id, site_id, type, slug),
            KEY scope_type_status (network_id, site_id, type, status),
            KEY scope_updated (network_id, site_id, updated_at)
        ) ENGINE=InnoDB{$charsetSql}";

        $dependenciesSql = "CREATE TABLE IF NOT EXISTS `{$this->tables->dependencies}` (
            network_id BIGINT UNSIGNED NOT NULL,
            site_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            definition_id CHAR(36) NOT NULL,
            dependency_id CHAR(36) NOT NULL,
            created_at DATETIME(6) NOT NULL,
            PRIMARY KEY (network_id, site_id, definition_id, dependency_id),
            KEY dependency_lookup (network_id, site_id, dependency_id, definition_id)
        ) ENGINE=InnoDB{$charsetSql}";

        foreach ([$definitionsSql, $dependenciesSql] as $sql) {
            if ($this->database->query($sql) === false) {
                throw new RuntimeException('Failed to create definition persistence tables: ' . $this->database->lastError());
            }
        }
    }
}
