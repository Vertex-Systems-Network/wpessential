<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Review;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Modules\CustomTables\Migration\ProviderMigrationPreview;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;

/** ADR-0225: what was reviewed for a run, recorded once at preparation time. */
final readonly class MigrationReviewSnapshot {

	public string $runId;

	public function __construct(
		string $runId,
		public string $previewHash,
		public MigrationGenerationStamp $generation,
	) {
		if ( preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $runId ) !== 1 ) {
			throw new InvalidArgumentException( 'Custom Tables review snapshot run id must be a canonical UUID.' );
		}
		if ( preg_match( '/^[a-f0-9]{64}$/', $this->previewHash ) !== 1 ) {
			throw new InvalidArgumentException( 'Custom Tables review snapshot preview hash is invalid.' );
		}

		$this->runId = strtolower( $runId );
	}

	public static function previewHash( ProviderMigrationPreview $preview ): string {
		return hash( 'sha256', json_encode( $preview->canonical(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES ) );
	}

	public function matchesPreview( ProviderMigrationPreview $preview ): bool {
		return hash_equals( $this->previewHash, self::previewHash( $preview ) );
	}

	/** @return array<string,mixed> */
	public function canonical(): array {
		return [
			'run_id'       => $this->runId,
			'preview_hash' => $this->previewHash,
			'generation'   => $this->generation->canonical(),
		];
	}
}
