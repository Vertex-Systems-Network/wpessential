<?php

declare(strict_types=1);

namespace WPEssential\Platform\Audit\Migrations;


if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\MigrationInterface;
use WPEssential\Platform\Audit\AuditTableNames;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class CreateAuditEventsTableMigration implements MigrationInterface
{
    private readonly AuditTableNames $tables;

    public function __construct(private readonly DatabaseAdapterInterface $database)
    {
        $this->tables = new AuditTableNames($database);
    }

    public function id(): string
    {
        return '008.create-audit-ptd-store';
    }

    public function sequence(): int
    {
        return 80;
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

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->tables->events}` (
            event_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_uuid CHAR(36) NOT NULL,
            network_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            site_id BIGINT UNSIGNED NOT NULL,
            occurred_at DATETIME(6) NOT NULL,
            recorded_at DATETIME(6) NOT NULL,
            actor_type VARCHAR(64) NOT NULL,
            actor_user_id BIGINT UNSIGNED NULL DEFAULT NULL,
            channel VARCHAR(32) NOT NULL,
            correlation_id VARCHAR(191) NULL DEFAULT NULL,
            owner_surface_id TINYINT UNSIGNED NOT NULL,
            action VARCHAR(191) NOT NULL,
            outcome VARCHAR(32) NOT NULL,
            resource_type VARCHAR(191) NULL DEFAULT NULL,
            resource_id VARCHAR(255) NULL DEFAULT NULL,
            reason TEXT NULL,
            metadata_json LONGTEXT NOT NULL,
            retention_class VARCHAR(8) NOT NULL,
            privacy_class VARCHAR(64) NOT NULL,
            schema_version SMALLINT UNSIGNED NOT NULL,
            content_hash CHAR(64) NOT NULL,
            PRIMARY KEY (event_id),
            UNIQUE KEY event_uuid (event_uuid),
            KEY scope_occurred (network_id, site_id, occurred_at, event_id),
            KEY scope_action_occurred (network_id, site_id, action, occurred_at),
            KEY scope_actor_occurred (network_id, site_id, actor_user_id, actor_type, occurred_at),
            KEY scope_resource_occurred (network_id, site_id, resource_type, resource_id, occurred_at),
            KEY scope_outcome_occurred (network_id, site_id, outcome, occurred_at),
            KEY scope_correlation (network_id, site_id, correlation_id),
            KEY retention_occurred (retention_class, occurred_at)
        ) ENGINE=InnoDB{$charsetSql}";

        if ($this->database->query($sql) === false) {
            throw new RuntimeException('Failed to create audit persistence table: ' . $this->database->lastError());
        }
    }
}
