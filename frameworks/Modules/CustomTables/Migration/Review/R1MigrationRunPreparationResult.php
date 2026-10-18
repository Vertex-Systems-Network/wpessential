<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Review;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Modules\CustomTables\Migration\ProviderMigrationPreview;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;

final readonly class R1MigrationRunPreparationResult {

	/** @param list<string> $reasons */
	private function __construct(
		public bool $prepared,
		public ?MigrationRun $run,
		public ?ProviderMigrationPreview $preview,
		public ?MigrationReviewSnapshot $snapshot,
		public array $reasons,
	) {
	}

	/** @param list<string> $reasons */
	public static function rejected( array $reasons ): self {
		$reasons = array_values( array_unique( $reasons ) );
		sort( $reasons );

		return new self( false, null, null, null, $reasons );
	}

	public static function prepared( MigrationRun $run, ProviderMigrationPreview $preview, MigrationReviewSnapshot $snapshot ): self {
		return new self( true, $run, $preview, $snapshot, [] );
	}
}
