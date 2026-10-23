<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Migration\MigrationOperation;
use WPEssential\Modules\CustomTables\Migration\MigrationPlan;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Migration\ProviderMigrationPreview;
use WPEssential\Modules\CustomTables\Migration\ProviderStatementPreview;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\CustomTablesRuntimeCompositionFactory;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationIssuer;
use WPEssential\Modules\CustomTables\Migration\Review\WordPressProviderCapabilityProfileResolver;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;
use WPEssential\Platform\Audit\AuditOutcome;
use WPEssential\Platform\Auth\ExecutionContext;

/**
 * ADR-0227: thin internal ability handler over the ADR-0224/0225/0226 services.
 *
 * Accepts identifiers and expected revisions only; every trust decision stays in the services.
 */
final readonly class CustomTablesMigrationAbilityHandler implements AbilityHandlerInterface {

	public const ACTIONS = [ 'prepare', 'submit', 'approve', 'revalidate', 'cancel', 'confirm', 'execute' ];

	private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

	public function __construct(
		private string $action,
		private CustomTablesRuntimeCompositionFactory $factory,
		private WordPressCt1SchemaIntrospector $schema,
		private WordPressProviderCapabilityProfileResolver $profiles,
		private DefinitionRepositoryInterface $definitions,
		private WpdbMigrationExecutionConfirmationIssuer $issuer,
		private string $sitePrefix,
		private CustomTablesMigrationAudit $audit,
	) {
		if ( ! in_array( $this->action, self::ACTIONS, true ) ) {
			throw new InvalidArgumentException( 'Unknown Custom Tables migration ability action.' );
		}
	}

	/**
	 * Every outcome is audited (ADR-0228): failures are recorded and rethrown unchanged.
	 *
	 * @param array<string,mixed> $input
	 * @return array<string,mixed>
	 */
	public function handle( array $input, ExecutionContext $context ): array {
		try {
			$result = match ( $this->action ) {
				'prepare' => $this->prepare( $input ),
				'confirm' => $this->confirm( $input, $context ),
				'execute' => $this->execute( $input, $context ),
				default   => $this->transition( $input, $context ),
			};
		} catch ( Throwable $exception ) {
			$this->audit->record( $context, $this->action, AuditOutcome::Failed, $input, $exception::class );

			throw $exception;
		}

		$succeeded = ( $result['prepared'] ?? $result['applied'] ?? true ) === true;
		$run       = $result['run'] ?? null;

		$result['audit_recorded'] = $this->audit->record(
			$context,
			$this->action,
			$succeeded ? AuditOutcome::Success : AuditOutcome::Failed,
			$input,
			metadata: [
				'state'          => is_array( $run ) ? ( $run['state'] ?? null ) : ( $result['state'] ?? null ),
				'state_revision' => is_array( $run ) ? ( $run['state_revision'] ?? null ) : null,
				'reasons'        => $result['reasons'] ?? [],
			],
		);

		return $result;
	}

	/**
	 * @param array<string,mixed> $input
	 * @return array<string,mixed>
	 */
	private function prepare( array $input ): array {
		$definition = $this->definitions->get( $this->uuid( $input, 'definition_id' ) );
		if ( $definition === null ) {
			throw new RuntimeException( 'Custom Tables Definition was not found.' );
		}

		$desired      = ( new TableDefinitionCompiler() )->compile( $definition );
		$identity     = Ct1ManagedTableIdentity::derive( strtolower( $desired->definitionId ), $desired->tableKey, $this->sitePrefix );
		$capabilities = $this->profiles->resolve();
		$result       = $this->factory->createR1MigrationRunPreparationService( $this->schema )->prepare( $desired, $identity, $capabilities );

		return [
			'prepared' => $result->prepared,
			'run'      => $result->run === null ? null : self::run( $result->run ),
			'preview'  => $result->plan === null || $result->preview === null
				? null
				: $this->evidence( $result->plan, $result->preview, $identity, $capabilities ),
			'reasons'  => $result->reasons,
		];
	}

	/**
	 * ADR-0228 / spec §17: the migration evidence an administrator reviews for an R1 additive plan.
	 *
	 * @return array<string,mixed>
	 */
	private function evidence(
		MigrationPlan $plan,
		ProviderMigrationPreview $preview,
		Ct1ManagedTableIdentity $identity,
		ProviderCapabilityProfile $capabilities,
	): array {
		$operations   = array_map( static fn ( MigrationOperation $operation ): string => $operation->type, $plan->operations );
		$createsTable = in_array( 'create_table', $operations, true );

		$lockNotes = [];
		foreach ( array_unique( $operations ) as $type ) {
			$lockNotes[] = match ( $type ) {
				'create_table' => 'Creates a new table; no existing data is locked.',
				'add_column'   => $capabilities->instantAddColumnCandidate
					? 'The server reports INSTANT ADD COLUMN support; expect only a brief metadata lock.'
					: 'The server may rebuild the table to add the column; writes can block for its duration.',
				'add_index'    => 'Adds a secondary index; InnoDB builds it online with brief metadata locks at start and end.',
				default        => 'Operation is outside the R1 additive scope.',
			};
		}

		return [
			'risk'                   => $plan->risk->name,
			'operations'             => array_values( $operations ),
			'schema_diff'            => array_map(
				static fn ( ProviderStatementPreview $statement ): string => $statement->sql,
				$preview->statements,
			),
			'affected_rows_estimate' => $createsTable ? 0 : $this->factory->affectedRowsEstimate( $identity ),
			'lock_notes'             => $lockNotes,
			'backup_requirement'     => sprintf(
				'A verified database backup bound to this plan, at most %d hours old, is required before execution.',
				CustomTablesRuntimeCompositionFactory::R1_MAX_RECOVERY_EVIDENCE_AGE_HOURS,
			),
			'rollback_strategy'      => $createsTable
				? 'While the run is failed_recoverable, drop the newly created table; no existing data is involved.'
				: 'While the run is failed_recoverable, drop the added columns or indexes; existing rows are not rewritten.',
			'data_compatibility'     => 'Not applicable to additive changes: no existing value is converted.',
			'dependency_impact'      => 'Not applicable to additive changes: no existing column, index or table is removed or renamed.',
			'provider'               => $capabilities->provider . ' ' . $capabilities->version,
		];
	}

	/**
	 * @param array<string,mixed> $input
	 * @return array<string,mixed>
	 */
	private function transition( array $input, ExecutionContext $context ): array {
		$runId     = $this->uuid( $input, 'run_id' );
		$revision  = $this->revision( $input );
		$lifecycle = $this->factory->createR1MigrationRunLifecycleService();

		$run = match ( $this->action ) {
			'submit'     => $lifecycle->submit( $runId, $revision ),
			'approve'    => $lifecycle->approve( $context, $runId, $revision ),
			'revalidate' => $lifecycle->startRevalidation( $context, $runId, $revision ),
			default      => $lifecycle->cancel( $context, $runId, $revision ),
		};

		return [ 'run' => self::run( $run ) ];
	}

	/**
	 * @param array<string,mixed> $input
	 * @return array<string,mixed>
	 */
	private function confirm( array $input, ExecutionContext $context ): array {
		$run = $this->factory->run( $this->uuid( $input, 'run_id' ) );
		if ( $run === null ) {
			throw new RuntimeException( 'Custom Tables migration run was not found.' );
		}
		if ( $run->stateRevision !== $this->revision( $input ) ) {
			throw new RuntimeException( 'Custom Tables migration run state revision is stale.' );
		}

		$this->issuer->issue( $context, $run->id, $run->planFingerprint, $run->stateRevision );

		return [
			'run'       => self::run( $run ),
			'confirmed' => true,
		];
	}

	/**
	 * @param array<string,mixed> $input
	 * @return array<string,mixed>
	 */
	private function execute( array $input, ExecutionContext $context ): array {
		$backupId = $input['backup_id'] ?? null;
		if ( ! is_string( $backupId ) || $backupId === '' ) {
			throw new InvalidArgumentException( 'Custom Tables migration execution requires a backup_id.' );
		}

		$resolution = $this->factory
			->createR1ExecutionRequestResolver( $this->schema, $this->profiles, $this->definitions, $this->sitePrefix )
			->resolve( $this->uuid( $input, 'run_id' ), $context, $backupId );
		if ( $resolution->request === null ) {
			return [
				'applied'            => false,
				'mutation_attempted' => false,
				'superseded'         => $resolution->superseded,
				'state'              => null,
				'reasons'            => $resolution->reasons,
			];
		}

		$result = $this->factory->createR1AdditiveExecutionCoordinator( $this->schema )->execute( $resolution->request );

		return [
			'applied'            => $result->applied,
			'mutation_attempted' => $result->mutationAttempted,
			'superseded'         => false,
			'state'              => $result->finalState?->value,
			'reasons'            => $result->reasons,
		];
	}

	/** @param array<string,mixed> $input */
	private function uuid( array $input, string $key ): string {
		$value = $input[ $key ] ?? null;
		if ( ! is_string( $value ) || preg_match( self::UUID_PATTERN, $value ) !== 1 ) {
			throw new InvalidArgumentException( sprintf( 'Custom Tables migration ability requires a UUID %s.', $key ) );
		}

		return strtolower( $value );
	}

	/** @param array<string,mixed> $input */
	private function revision( array $input ): int {
		$value = $input['expected_state_revision'] ?? null;
		if ( ! is_int( $value ) || $value < 1 ) {
			throw new InvalidArgumentException( 'Custom Tables migration ability requires a positive expected_state_revision.' );
		}

		return $value;
	}

	/** @return array<string,mixed> */
	private static function run( MigrationRun $run ): array {
		return [
			'run_id'         => $run->id,
			'state'          => $run->state->value,
			'state_revision' => $run->stateRevision,
		];
	}
}
