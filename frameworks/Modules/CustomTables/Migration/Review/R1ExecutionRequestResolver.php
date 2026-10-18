<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Review;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Migration\Execution\R1AdditiveExecutionRequest;
use WPEssential\Modules\CustomTables\Migration\ProviderDdlPreviewCompiler;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryClass;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryRequirement;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunRepositoryInterface;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunTransitionService;
use WPEssential\Modules\CustomTables\Migration\TableSchemaDiffPlanner;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Modules\CustomTables\Schema\ObservedSchemaReaderInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

/**
 * ADR-0226: builds an R1 execution request from a stored run and freshly observed facts only.
 *
 * Callers supply the run id, the acting context and a backup artifact id; the artifact is verified
 * later by the trusted recovery provider, so a caller cannot forge recovery evidence.
 */
final readonly class R1ExecutionRequestResolver {

	public function __construct(
		private MigrationRunRepositoryInterface $runs,
		private MigrationReviewSnapshotStoreInterface $snapshots,
		private DefinitionRepositoryInterface $definitions,
		private ObservedSchemaReaderInterface $schema,
		private WordPressProviderCapabilityProfileResolver $profiles,
		private DatabaseAdapterInterface $database,
		private MigrationRunTransitionService $transitions,
		private string $sitePrefix,
		private int $maxRecoveryEvidenceAgeHours,
	) {
		if ( $this->maxRecoveryEvidenceAgeHours < 0 ) {
			throw new InvalidArgumentException( 'Custom Tables recovery evidence age limit cannot be negative.' );
		}
	}

	public function resolve( string $runId, ExecutionContext $context, string $backupArtifactId ): R1ExecutionRequestResolution {
		try {
			$run = $this->runs->get( $runId );
		} catch ( Throwable ) {
			return R1ExecutionRequestResolution::rejected( [ 'run_lookup_failed' ] );
		}
		if ( $run === null ) {
			return R1ExecutionRequestResolution::rejected( [ 'run_not_found' ] );
		}
		if ( $run->state !== MigrationRunState::Revalidating ) {
			return R1ExecutionRequestResolution::rejected( [ 'run_not_revalidating' ] );
		}

		$snapshot = $this->snapshots->snapshotFor( $run->id );
		if ( $snapshot === null ) {
			return R1ExecutionRequestResolution::rejected( [ 'review_snapshot_missing' ] );
		}

		$definition = $this->definitions->get( $run->targetDefinitionId );
		if ( $definition === null ) {
			return R1ExecutionRequestResolution::rejected( [ 'definition_missing' ] );
		}
		if ( $definition->revision !== $run->targetRevision ) {
			try {
				$this->transitions->transition( $run->id, $run->stateRevision, MigrationRunState::Superseded );
			} catch ( Throwable ) {
				return R1ExecutionRequestResolution::rejected( [ 'definition_revision_changed', 'run_supersede_failed' ] );
			}

			return R1ExecutionRequestResolution::superseded();
		}

		try {
			$desired      = ( new TableDefinitionCompiler() )->compile( $definition );
			$capabilities = $this->profiles->resolve();
			$identity     = Ct1ManagedTableIdentity::derive( strtolower( $run->targetDefinitionId ), $run->tableKey, $this->sitePrefix );
			$plan         = ( new TableSchemaDiffPlanner() )->plan( $desired, $this->schema->observe( $desired ) );
			$preview      = ( new ProviderDdlPreviewCompiler() )->compile( $plan, $desired, $identity, $capabilities, $this->database->charsetCollate() );
		} catch ( Throwable ) {
			return R1ExecutionRequestResolution::rejected( [ 'recompile_failed' ] );
		}

		if ( $desired->tableKey !== $run->tableKey || $desired->desiredSchemaVersion !== $run->targetSchemaVersion ) {
			return R1ExecutionRequestResolution::rejected( [ 'definition_does_not_match_run' ] );
		}
		if ( $plan->fingerprint !== $run->planFingerprint || ! $snapshot->matchesPreview( $preview ) ) {
			return R1ExecutionRequestResolution::rejected( [ 'reviewed_plan_changed' ] );
		}

		return R1ExecutionRequestResolution::resolved(
			new R1AdditiveExecutionRequest(
				run: $run,
				plan: $plan,
				desired: $desired,
				identity: $identity,
				capabilities: $capabilities,
				reviewedPreview: $preview,
				requirements: [],
				reviewedGeneration: $snapshot->generation,
				currentGeneration: new MigrationGenerationStamp(
					planFingerprint: $plan->fingerprint,
					sourceObservedFingerprint: $plan->sourceObservedFingerprint,
					targetDefinitionId: $plan->targetDefinitionId,
					targetRevision: $plan->targetRevision,
					targetSchemaVersion: $plan->targetSchemaVersion,
					provider: $capabilities->provider,
					providerVersion: $capabilities->version,
				),
				recoveryRequirement: new RecoveryRequirement( RecoveryClass::VerifiedBackupRequired, verifiedBackupRequired: true ),
				recoveryArtifactId: $backupArtifactId,
				maxRecoveryEvidenceAgeHours: $this->maxRecoveryEvidenceAgeHours,
				context: $context,
			)
		);
	}
}
