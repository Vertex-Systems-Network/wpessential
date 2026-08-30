<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs\Migrations;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\MigrationInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;
use WPEssential\Platform\Jobs\JobTableNames;

final class CreateJobTablesMigration implements MigrationInterface
{
    private readonly JobTableNames $tables;

    public function __construct(private readonly DatabaseAdapterInterface $database)
    {
        $this->tables = new JobTableNames($database);
    }

    public function id(): string
    {
        return '009.create-job-persistence';
    }

    public function sequence(): int
    {
        return 90;
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

        $jobsSql = "CREATE TABLE IF NOT EXISTS `{$this->tables->jobs}` (
            network_id BIGINT UNSIGNED NOT NULL,
            site_id BIGINT UNSIGNED NOT NULL,
            id CHAR(36) NOT NULL,
            type_key VARCHAR(191) NOT NULL,
            state VARCHAR(32) NOT NULL,
            payload_json LONGTEXT NOT NULL,
            idempotency_hash CHAR(64) NULL,
            attempts INT UNSIGNED NOT NULL DEFAULT 0,
            last_failure VARCHAR(64) NULL,
            retry_after DATETIME(6) NULL,
            revision BIGINT UNSIGNED NOT NULL DEFAULT 1,
            created_at DATETIME(6) NOT NULL,
            updated_at DATETIME(6) NOT NULL,
            PRIMARY KEY (network_id, site_id, id),
            UNIQUE KEY scope_type_idempotency (network_id, site_id, type_key, idempotency_hash),
            KEY scope_state_retry (network_id, site_id, state, retry_after),
            KEY scope_type_created (network_id, site_id, type_key, created_at)
        ) ENGINE=InnoDB{$charsetSql}";

        $attemptsSql = "CREATE TABLE IF NOT EXISTS `{$this->tables->attempts}` (
            network_id BIGINT UNSIGNED NOT NULL,
            site_id BIGINT UNSIGNED NOT NULL,
            attempt_id CHAR(36) NOT NULL,
            job_id CHAR(36) NOT NULL,
            attempt_no INT UNSIGNED NOT NULL,
            state VARCHAR(32) NOT NULL,
            worker_id VARCHAR(191) NOT NULL,
            lease_token_hash CHAR(64) NOT NULL,
            lease_acquired_at DATETIME(6) NOT NULL,
            lease_expires_at DATETIME(6) NOT NULL,
            heartbeat_at DATETIME(6) NOT NULL,
            checkpoint_seq BIGINT UNSIGNED NOT NULL DEFAULT 0,
            checkpoint_json LONGTEXT NULL,
            failure_class VARCHAR(64) NULL,
            completed_at DATETIME(6) NULL,
            created_at DATETIME(6) NOT NULL,
            updated_at DATETIME(6) NOT NULL,
            PRIMARY KEY (network_id, site_id, attempt_id),
            UNIQUE KEY job_attempt_number (network_id, site_id, job_id, attempt_no),
            KEY lease_reclaim (network_id, site_id, state, lease_expires_at),
            KEY job_attempt_lookup (network_id, site_id, job_id, attempt_no)
        ) ENGINE=InnoDB{$charsetSql}";

        foreach ([$jobsSql, $attemptsSql] as $sql) {
            if ($this->database->query($sql) === false) {
                throw new RuntimeException('Failed to create durable JobService tables: ' . $this->database->lastError());
            }
        }
    }
}
