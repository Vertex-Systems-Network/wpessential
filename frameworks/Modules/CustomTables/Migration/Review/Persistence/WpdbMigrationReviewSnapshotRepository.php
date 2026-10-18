<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Review\Persistence;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Review\MigrationReviewSnapshot;
use WPEssential\Modules\CustomTables\Migration\Review\MigrationReviewSnapshotStoreInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

/** ADR-0225: insert-once review snapshot store with readback verification. */
final readonly class WpdbMigrationReviewSnapshotRepository implements MigrationReviewSnapshotStoreInterface {

	private string $table;

	public function __construct(
		private DatabaseAdapterInterface $database,
		private int $siteId,
	) {
		if ( $this->siteId < 1 ) {
			throw new InvalidArgumentException( 'Custom Tables review snapshot repository site id must be positive.' );
		}

		$prefix = $database->networkTablePrefix();
		if ( preg_match( '/^[A-Za-z0-9_]+$/', $prefix ) !== 1 ) {
			throw new RuntimeException( 'Custom Tables review snapshot repository prefix is invalid.' );
		}

		$this->table = $prefix . 'wpe_custom_table_migration_reviews';
	}

	public function record( MigrationReviewSnapshot $snapshot ): MigrationReviewSnapshot {
		if ( $this->snapshotFor( $snapshot->runId ) !== null ) {
			throw new RuntimeException( 'Custom Tables review snapshot already exists for this run.' );
		}

		$generation = $snapshot->generation;
		try {
			$this->database->insert(
				$this->table,
				[
					'site_id'                     => $this->siteId,
					'run_id'                      => $snapshot->runId,
					'preview_hash'                => $snapshot->previewHash,
					'plan_fingerprint'            => $generation->planFingerprint,
					'source_observed_fingerprint' => $generation->sourceObservedFingerprint,
					'target_definition_id'        => strtolower( $generation->targetDefinitionId ),
					'target_revision'             => $generation->targetRevision,
					'target_schema_version'       => $generation->targetSchemaVersion,
					'provider'                    => $generation->provider,
					'provider_version'            => $generation->providerVersion,
					'reviewed_at'                 => gmdate( 'Y-m-d H:i:s' ),
				],
				[ '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s' ],
			);
		} catch ( Throwable ) {
			throw new RuntimeException( 'Custom Tables review snapshot could not be recorded.' );
		}

		$stored = $this->snapshotFor( $snapshot->runId );
		if ( $stored === null || $stored->canonical() !== $snapshot->canonical() ) {
			throw new RuntimeException( 'Custom Tables review snapshot readback verification failed.' );
		}

		return $stored;
	}

	/** @phpstan-impure Reads the current database row on every call. */
	public function snapshotFor( string $runId ): ?MigrationReviewSnapshot {
		if ( preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $runId ) !== 1 ) {
			throw new InvalidArgumentException( 'Custom Tables review snapshot lookup run id must be a canonical UUID.' );
		}

		$query = $this->database->prepare(
			"SELECT run_id, preview_hash, plan_fingerprint, source_observed_fingerprint, target_definition_id, target_revision, target_schema_version, provider, provider_version FROM `{$this->table}` WHERE site_id = %d AND run_id = %s LIMIT 1",
			$this->siteId,
			strtolower( $runId ),
		);
		$row   = $this->database->getRow( $query );
		if ( $row === null ) {
			return null;
		}

		return new MigrationReviewSnapshot(
			runId: $this->string( $row, 'run_id' ),
			previewHash: $this->string( $row, 'preview_hash' ),
			generation: new MigrationGenerationStamp(
				planFingerprint: $this->string( $row, 'plan_fingerprint' ),
				sourceObservedFingerprint: $this->string( $row, 'source_observed_fingerprint' ),
				targetDefinitionId: $this->string( $row, 'target_definition_id' ),
				targetRevision: $this->positiveInt( $row, 'target_revision' ),
				targetSchemaVersion: $this->positiveInt( $row, 'target_schema_version' ),
				provider: $this->string( $row, 'provider' ),
				providerVersion: $this->string( $row, 'provider_version' ),
			),
		);
	}

	/** @param array<string,mixed> $row */
	private function string( array $row, string $key ): string {
		$value = $row[ $key ] ?? null;
		if ( ! is_string( $value ) ) {
			throw new RuntimeException( 'Custom Tables stored review snapshot string field is invalid.' );
		}

		return $value;
	}

	/** @param array<string,mixed> $row */
	private function positiveInt( array $row, string $key ): int {
		$value = $row[ $key ] ?? null;
		if ( is_int( $value ) && $value > 0 ) {
			return $value;
		}
		if ( is_string( $value ) && preg_match( '/^[1-9]\d*$/', $value ) === 1 ) {
			return (int) $value;
		}

		throw new RuntimeException( 'Custom Tables stored review snapshot integer field is invalid.' );
	}
}
