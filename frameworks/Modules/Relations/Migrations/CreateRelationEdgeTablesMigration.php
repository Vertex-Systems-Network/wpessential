<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations\Migrations;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\MigrationInterface;
use WPEssential\Modules\Relations\RelationEdgeTableNames;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class CreateRelationEdgeTablesMigration implements MigrationInterface
{
    private readonly RelationEdgeTableNames $tables;

    public function __construct(private readonly DatabaseAdapterInterface $database)
    {
        $this->tables = new RelationEdgeTableNames($database);
    }

    public function id(): string
    {
        return '010.create-relation-edge-persistence';
    }

    public function sequence(): int
    {
        return 100;
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

        $edgesSql = "CREATE TABLE IF NOT EXISTS `{$this->tables->edges}` (
            network_id BIGINT UNSIGNED NOT NULL,
            site_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            edge_id CHAR(36) NOT NULL,
            relation_definition_id CHAR(36) NOT NULL,
            from_object_id BIGINT UNSIGNED NOT NULL,
            to_object_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME(6) NOT NULL,
            updated_at DATETIME(6) NOT NULL,
            PRIMARY KEY (network_id, site_id, edge_id),
            UNIQUE KEY scope_relation_edge (network_id, site_id, relation_definition_id, from_object_id, to_object_id),
            KEY relation_source_lookup (network_id, site_id, relation_definition_id, from_object_id, to_object_id),
            KEY relation_target_lookup (network_id, site_id, relation_definition_id, to_object_id, from_object_id)
        ) ENGINE=InnoDB{$charsetSql}";

        $stateSql = "CREATE TABLE IF NOT EXISTS `{$this->tables->state}` (
            network_id BIGINT UNSIGNED NOT NULL,
            site_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            relation_definition_id CHAR(36) NOT NULL,
            mutation_revision BIGINT UNSIGNED NOT NULL DEFAULT 0,
            updated_at DATETIME(6) NOT NULL,
            PRIMARY KEY (network_id, site_id, relation_definition_id)
        ) ENGINE=InnoDB{$charsetSql}";

        foreach ([$edgesSql, $stateSql] as $sql) {
            if ($this->database->query($sql) === false) {
                throw new RuntimeException('Failed to create Relation edge persistence tables: ' . $this->database->lastError());
            }
        }
    }
}
