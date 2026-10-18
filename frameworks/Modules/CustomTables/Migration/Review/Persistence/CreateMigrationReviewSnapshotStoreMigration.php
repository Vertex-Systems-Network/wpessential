<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Review\Persistence;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RuntimeException;
use WPEssential\Contracts\MigrationInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class CreateMigrationReviewSnapshotStoreMigration implements MigrationInterface {

	public const ID       = '222.custom_tables_migration_review_snapshots_v1';
	public const SEQUENCE = 222;

	private string $table;

	public function __construct( private DatabaseAdapterInterface $database ) {
		$prefix = $database->networkTablePrefix();
		if ( preg_match( '/^[A-Za-z0-9_]+$/', $prefix ) !== 1 ) {
			throw new RuntimeException( 'Custom Tables review snapshot store prefix is invalid.' );
		}

		$this->table = $prefix . 'wpe_custom_table_migration_reviews';
	}

	public function id(): string {
		return self::ID;
	}

	public function sequence(): int {
		return self::SEQUENCE;
	}

	public function isDestructive(): bool {
		return false;
	}

	public function recoveryPlan(): ?string {
		return null;
	}

	public function apply(): void {
		$charset    = trim( $this->database->charsetCollate() );
		$charsetSql = $charset === '' ? '' : ' ' . $charset;
		$sql        = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            site_id BIGINT UNSIGNED NOT NULL,
            run_id CHAR(36) NOT NULL,
            preview_hash CHAR(64) NOT NULL,
            plan_fingerprint CHAR(64) NOT NULL,
            source_observed_fingerprint CHAR(64) NOT NULL,
            target_definition_id CHAR(36) NOT NULL,
            target_revision BIGINT UNSIGNED NOT NULL,
            target_schema_version BIGINT UNSIGNED NOT NULL,
            provider VARCHAR(16) NOT NULL,
            provider_version VARCHAR(32) NOT NULL,
            reviewed_at DATETIME(6) NOT NULL,
            PRIMARY KEY (site_id, run_id),
            KEY plan_fingerprint (site_id, plan_fingerprint)
        ) ENGINE=InnoDB{$charsetSql}";

		if ( $this->database->query( $sql ) === false ) {
			throw new RuntimeException( 'Unable to initialize Custom Tables review snapshot store.' );
		}
	}
}
