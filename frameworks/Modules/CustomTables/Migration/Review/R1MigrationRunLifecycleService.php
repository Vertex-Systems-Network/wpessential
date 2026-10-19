<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Review;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RuntimeException;
use WPEssential\Modules\CustomTables\CustomTablesCapabilities;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunTransitionService;
use WPEssential\Platform\Auth\AuthorizationRequest;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;

/**
 * ADR-0226: the only pre-execution run transitions, each guarded by revision CAS.
 *
 * Internal service only. Approval, revalidation and cancellation require an authenticated user
 * authorized for custom-tables.migration.execute; submission requires a recorded review snapshot.
 */
final readonly class R1MigrationRunLifecycleService {

	public function __construct(
		private MigrationRunTransitionService $transitions,
		private MigrationReviewSnapshotStoreInterface $snapshots,
		private PolicyEngine $policy,
		private int $siteId,
		private string $ability = 'custom-tables.migration.execute',
		private string $capability = CustomTablesCapabilities::APPLY_MIGRATIONS,
	) {
	}

	public function submit( string $runId, int $expectedStateRevision ): MigrationRun {
		if ( $this->snapshots->snapshotFor( $runId ) === null ) {
			throw new RuntimeException( 'Custom Tables run cannot be submitted without a review snapshot.' );
		}

		return $this->transitions->transition( $runId, $expectedStateRevision, MigrationRunState::AwaitingReview );
	}

	public function approve( ExecutionContext $context, string $runId, int $expectedStateRevision ): MigrationRun {
		$this->authorize( $context, $runId );

		return $this->transitions->transition( $runId, $expectedStateRevision, MigrationRunState::Approved );
	}

	public function startRevalidation( ExecutionContext $context, string $runId, int $expectedStateRevision ): MigrationRun {
		$this->authorize( $context, $runId );

		return $this->transitions->transition( $runId, $expectedStateRevision, MigrationRunState::Revalidating );
	}

	public function cancel( ExecutionContext $context, string $runId, int $expectedStateRevision ): MigrationRun {
		$this->authorize( $context, $runId );

		return $this->transitions->transition( $runId, $expectedStateRevision, MigrationRunState::CancelledBeforeMutation );
	}

	private function authorize( ExecutionContext $context, string $runId ): void {
		if ( $context->siteId !== $this->siteId ) {
			throw new RuntimeException( 'Custom Tables run lifecycle context site does not match the service site.' );
		}
		if ( $context->principal->actorType !== 'user' || ! $context->principal->isAuthenticated() ) {
			throw new RuntimeException( 'Custom Tables run lifecycle requires an authenticated user actor.' );
		}

		$decision = $this->policy->authorize(
			new AuthorizationRequest(
				context: $context,
				ability: $this->ability,
				capability: $this->capability,
				resourceType: 'custom_table_migration_run',
				resourceId: strtolower( $runId ),
			)
		);
		if ( ! $decision->allowed ) {
			throw new RuntimeException( 'Custom Tables run lifecycle action is not authorized for this actor.' );
		}
	}
}
