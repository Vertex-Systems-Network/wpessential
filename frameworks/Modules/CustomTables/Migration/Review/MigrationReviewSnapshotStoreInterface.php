<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Review;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface MigrationReviewSnapshotStoreInterface {

	/** Insert-once; returns the verified stored snapshot or throws. */
	public function record( MigrationReviewSnapshot $snapshot ): MigrationReviewSnapshot;

	public function snapshotFor( string $runId ): ?MigrationReviewSnapshot;
}
