<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Execution;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Throwable;
use WPEssential\Modules\CustomTables\Definition\TableColumnDescriptor;
use WPEssential\Modules\CustomTables\Definition\TableIndexDescriptor;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
use WPEssential\Modules\CustomTables\Migration\ProviderDdlPreviewCompiler;
use WPEssential\Modules\CustomTables\Migration\ProviderStatementPreview;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\RuntimeCompositionReadinessService;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunTransitionService;
use WPEssential\Modules\CustomTables\Schema\ObservedSchemaReaderInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

/**
 * ADR-0224: the first managed-table execution boundary, limited to R1 additive plans.
 *
 * Internal service only. It re-evaluates readiness itself, recompiles the reviewed plan, executes
 * the identical statements, and verifies the result by re-introspection before reporting success.
 */
final readonly class R1AdditiveExecutionCoordinator {

	private const ADDITIVE_OPERATIONS = [ 'create_table', 'add_column', 'add_index' ];

	public function __construct(
		private RuntimeCompositionReadinessService $readiness,
		private MigrationRunTransitionService $transitions,
		private DatabaseAdapterInterface $database,
		private ObservedSchemaReaderInterface $schema,
		private ProviderDdlPreviewCompiler $compiler,
	) {
	}

	public function execute( R1AdditiveExecutionRequest $request ): R1AdditiveExecutionResult {
		$scope = $this->scopeReasons( $request );
		if ( $scope !== [] ) {
			return R1AdditiveExecutionResult::rejected( $scope );
		}

		try {
			$recompiled = $this->compiler->compile( $request->plan, $request->desired, $request->identity, $request->capabilities );
		} catch ( Throwable ) {
			return R1AdditiveExecutionResult::rejected( [ 'recompile_failed' ] );
		}
		if ( $recompiled->canonical() !== $request->reviewedPreview->canonical() ) {
			return R1AdditiveExecutionResult::rejected( [ 'reviewed_preview_mismatch' ] );
		}

		try {
			$package = $this->readiness->evaluate(
				run: $request->run,
				requirements: $request->requirements,
				descriptor: $request->desired,
				reviewedGeneration: $request->reviewedGeneration,
				currentGeneration: $request->currentGeneration,
				recoveryRequirement: $request->recoveryRequirement,
				recoveryArtifactId: $request->recoveryArtifactId,
				maxRecoveryEvidenceAgeHours: $request->maxRecoveryEvidenceAgeHours,
				preview: $request->reviewedPreview,
				context: $request->context,
				risk: $request->plan->risk,
			);
		} catch ( Throwable ) {
			return R1AdditiveExecutionResult::rejected( [ 'readiness_evaluation_failed' ] );
		}
		if ( ! $package->compositionReady ) {
			return R1AdditiveExecutionResult::rejected( $package->reasons );
		}

		try {
			$running = $this->transitions->transition( $request->run->id, $request->run->stateRevision, MigrationRunState::Running );
		} catch ( Throwable ) {
			return R1AdditiveExecutionResult::rejected( [ 'run_state_transition_rejected' ] );
		}

		$revision = $running->stateRevision;
		foreach ( $recompiled->statements as $statement ) {
			if ( ! $statement instanceof ProviderStatementPreview ) {
				return $this->fail( $request->run->id, $revision, 'statement_invalid' );
			}
			try {
				$result = $this->database->query( $statement->sql );
			} catch ( Throwable ) {
				$result = false;
			}
			if ( $result === false ) {
				return $this->fail( $request->run->id, $revision, 'statement_failed' );
			}
		}

		try {
			$verifying = $this->transitions->transition( $request->run->id, $revision, MigrationRunState::Verifying );
			$revision  = $verifying->stateRevision;
		} catch ( Throwable ) {
			return R1AdditiveExecutionResult::failedAfterMutation( MigrationRunState::Running, [ 'run_state_transition_rejected' ] );
		}

		if ( ! $this->observedMatchesDesired( $request ) ) {
			return $this->fail( $request->run->id, $revision, 'post_execution_verification_failed' );
		}

		try {
			$this->transitions->transition( $request->run->id, $revision, MigrationRunState::Applied );
		} catch ( Throwable ) {
			return R1AdditiveExecutionResult::failedAfterMutation( MigrationRunState::Verifying, [ 'run_state_transition_rejected' ] );
		}

		return R1AdditiveExecutionResult::applied();
	}

	/** @return list<string> */
	private function scopeReasons( R1AdditiveExecutionRequest $request ): array {
		$plan    = $request->plan;
		$reasons = [];
		if ( $plan->operations === [] ) {
			$reasons[] = 'plan_empty';
		}
		if ( $plan->blocked || $plan->recoveryRequired || $plan->findings !== [] ) {
			$reasons[] = 'plan_not_clean';
		}
		if ( $plan->risk->value > MigrationRisk::R1->value ) {
			$reasons[] = 'plan_risk_above_r1';
		}
		foreach ( $plan->operations as $operation ) {
			if ( ! in_array( $operation->type, self::ADDITIVE_OPERATIONS, true ) ) {
				$reasons[] = 'operation_not_additive';
			}
			if ( $operation->risk->value > MigrationRisk::R1->value || $operation->blocked || $operation->recoveryRequired ) {
				$reasons[] = 'operation_risk_above_r1';
			}
		}
		if ( $request->run->state !== MigrationRunState::Revalidating ) {
			$reasons[] = 'run_not_revalidating';
		}

		return $reasons;
	}

	private function observedMatchesDesired( R1AdditiveExecutionRequest $request ): bool {
		try {
			$observed = $this->schema->observe( $request->desired );
		} catch ( Throwable ) {
			return false;
		}
		if ( ! $observed->exists || $observed->isBlockedByDrift() ) {
			return false;
		}

		$columns = array_map( static fn ( TableColumnDescriptor $column ): string => $column->key, $observed->columns );
		foreach ( $request->desired->columns as $column ) {
			if ( ! in_array( $column->key, $columns, true ) ) {
				return false;
			}
		}

		$indexes = array_map( static fn ( TableIndexDescriptor $index ): string => $index->key, $observed->indexes );
		foreach ( $request->desired->indexes as $index ) {
			if ( ! in_array( $index->key, $indexes, true ) ) {
				return false;
			}
		}

		return true;
	}

	private function fail( string $runId, int $revision, string $reason ): R1AdditiveExecutionResult {
		try {
			$this->transitions->transition( $runId, $revision, MigrationRunState::FailedRecoverable );
		} catch ( Throwable ) {
			return R1AdditiveExecutionResult::failedAfterMutation( MigrationRunState::Running, [ $reason, 'run_state_transition_rejected' ] );
		}

		return R1AdditiveExecutionResult::failedAfterMutation( MigrationRunState::FailedRecoverable, [ $reason ] );
	}
}
