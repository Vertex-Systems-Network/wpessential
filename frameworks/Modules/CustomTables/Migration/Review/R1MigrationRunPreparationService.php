<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Review;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Throwable;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\Execution\R1AdditiveScope;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Migration\ProviderDdlPreviewCompiler;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunRepositoryInterface;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunTransitionService;
use WPEssential\Modules\CustomTables\Migration\TableSchemaDiffPlanner;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Modules\CustomTables\Schema\ObservedSchemaReaderInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

/**
 * ADR-0225: server-side preparation of an R1 additive run and its review snapshot.
 *
 * Internal service only. It observes, plans and compiles itself; callers supply only the target
 * descriptor, its managed identity and the trusted provider profile. Nothing is executed.
 */
final readonly class R1MigrationRunPreparationService {

	public function __construct(
		private ObservedSchemaReaderInterface $schema,
		private TableSchemaDiffPlanner $planner,
		private ProviderDdlPreviewCompiler $compiler,
		private DatabaseAdapterInterface $database,
		private MigrationRunRepositoryInterface $runs,
		private MigrationRunTransitionService $transitions,
		private MigrationReviewSnapshotStoreInterface $snapshots,
	) {
	}

	public function prepare(
		TableSchemaDescriptor $desired,
		Ct1ManagedTableIdentity $identity,
		ProviderCapabilityProfile $capabilities,
	): R1MigrationRunPreparationResult {
		if ( strtolower( $identity->definitionId ) !== strtolower( $desired->definitionId ) || $identity->tableKey !== $desired->tableKey ) {
			return R1MigrationRunPreparationResult::rejected( [ 'identity_mismatch' ] );
		}

		try {
			$plan = $this->planner->plan( $desired, $this->schema->observe( $desired ) );
		} catch ( Throwable ) {
			return R1MigrationRunPreparationResult::rejected( [ 'plan_failed' ] );
		}

		$scope = R1AdditiveScope::planReasons( $plan );
		if ( $scope !== [] ) {
			return R1MigrationRunPreparationResult::rejected( $scope );
		}

		try {
			$preview    = $this->compiler->compile( $plan, $desired, $identity, $capabilities, $this->database->charsetCollate() );
			$generation = new MigrationGenerationStamp(
				planFingerprint: $plan->fingerprint,
				sourceObservedFingerprint: $plan->sourceObservedFingerprint,
				targetDefinitionId: $plan->targetDefinitionId,
				targetRevision: $plan->targetRevision,
				targetSchemaVersion: $plan->targetSchemaVersion,
				provider: $capabilities->provider,
				providerVersion: $capabilities->version,
			);
		} catch ( Throwable ) {
			return R1MigrationRunPreparationResult::rejected( [ 'preview_failed' ] );
		}

		try {
			$run = $this->runs->create(
				new MigrationRun(
					id: self::uuid(),
					planFingerprint: $plan->fingerprint,
					tableKey: $plan->tableKey,
					targetDefinitionId: $plan->targetDefinitionId,
					targetRevision: $plan->targetRevision,
					targetSchemaVersion: $plan->targetSchemaVersion,
					state: MigrationRunState::Planned,
				)
			);
		} catch ( Throwable ) {
			return R1MigrationRunPreparationResult::rejected( [ 'run_create_failed' ] );
		}

		try {
			$snapshot = $this->snapshots->record(
				new MigrationReviewSnapshot( $run->id, MigrationReviewSnapshot::previewHash( $preview ), $generation )
			);
		} catch ( Throwable ) {
			try {
				$this->transitions->transition( $run->id, $run->stateRevision, MigrationRunState::CancelledBeforeMutation );
			} catch ( Throwable ) {
				return R1MigrationRunPreparationResult::rejected( [ 'run_cancel_failed', 'snapshot_record_failed' ] );
			}

			return R1MigrationRunPreparationResult::rejected( [ 'snapshot_record_failed' ] );
		}

		return R1MigrationRunPreparationResult::prepared( $run, $preview, $snapshot, $plan );
	}

	private static function uuid(): string {
		if ( function_exists( 'wp_generate_uuid4' ) ) {
			$uuid = strtolower( (string) wp_generate_uuid4() );
			if ( preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid ) === 1 ) {
				return $uuid;
			}
		}
		$bytes    = random_bytes( 16 );
		$bytes[6] = chr( ( ord( $bytes[6] ) & 0x0f ) | 0x40 );
		$bytes[8] = chr( ( ord( $bytes[8] ) & 0x3f ) | 0x80 );
		$hex      = bin2hex( $bytes );

		return sprintf(
			'%s-%s-%s-%s-%s',
			substr( $hex, 0, 8 ),
			substr( $hex, 8, 4 ),
			substr( $hex, 12, 4 ),
			substr( $hex, 16, 4 ),
			substr( $hex, 20, 12 ),
		);
	}
}
