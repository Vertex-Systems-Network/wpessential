<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Composition;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\CustomTables\Migration\Execution\R1AdditiveExecutionCoordinator;
use WPEssential\Modules\CustomTables\Migration\ProviderDdlPreviewCompiler;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\WordPressMetadataPreconditionFactsProvider;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\Policy\MigrationExecutionAuthorizationPolicyAdapter;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\FailClosedRecoveryVerificationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\LocalBackupRecoveryVerificationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\TrustedRecoveryVerificationProviderInterface;
use WPEssential\Modules\CustomTables\Migration\Review\Persistence\WpdbMigrationReviewSnapshotRepository;
use WPEssential\Modules\CustomTables\Migration\Review\R1ExecutionRequestResolver;
use WPEssential\Modules\CustomTables\Migration\Review\R1MigrationRunLifecycleService;
use WPEssential\Modules\CustomTables\Migration\Review\R1MigrationRunPreparationService;
use WPEssential\Modules\CustomTables\Migration\Review\WordPressProviderCapabilityProfileResolver;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\MigrationRunRecordCodec;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunTransitionService;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\WpdbMigrationRunRepository;
use WPEssential\Modules\CustomTables\Migration\TableSchemaDiffPlanner;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class CustomTablesRuntimeCompositionFactory {

	/** ADR-0226: maximum age of the verified backup accepted as R1 recovery evidence. */
	public const R1_MAX_RECOVERY_EVIDENCE_AGE_HOURS = 24;

	private function __construct(
		private DatabaseAdapterInterface $database,
		private PolicyEngine $policy,
		private WordPressMetadataPreconditionFactsProvider $metadataFacts,
		private TrustedRecoveryVerificationProviderInterface $recoveryVerification,
		private TrustedMigrationExecutionConfirmationProviderInterface $confirmations,
		private int $networkId,
		private int $siteId,
	) {
		if ( $this->networkId < 1 || $this->siteId < 1 ) {
			throw new InvalidArgumentException( 'Custom Tables runtime composition factory scope ids must be positive.' );
		}
	}

	/**
	 * Exclusive trust root: only the final, repository-owned trusted providers are accepted,
	 * so alternate code cannot inject a custom implementation of the trusted marker interfaces.
	 */
	public static function production(
		DatabaseAdapterInterface $database,
		PolicyEngine $policy,
		WordPressMetadataPreconditionFactsProvider $metadataFacts,
		FailClosedRecoveryVerificationProvider|LocalBackupRecoveryVerificationProvider $recoveryVerification,
		WpdbMigrationExecutionConfirmationProvider $confirmations,
		int $networkId,
		int $siteId,
	): self {
		return new self(
			database: $database,
			policy: $policy,
			metadataFacts: $metadataFacts,
			recoveryVerification: $recoveryVerification,
			confirmations: $confirmations,
			networkId: $networkId,
			siteId: $siteId,
		);
	}

	public function create(): RuntimeCompositionReadinessService {
		return new RuntimeCompositionReadinessService(
			runs: $this->runs(),
			metadataFacts: $this->metadataFacts,
			recoveryVerification: $this->recoveryVerification,
			confirmations: $this->confirmations,
			authorizationPolicy: new MigrationExecutionAuthorizationPolicyAdapter( $this->policy ),
			networkId: $this->networkId,
			siteId: $this->siteId,
		);
	}

	/**
	 * ADR-0224 coordinator over the same trusted readiness composition and run repository.
	 * Only the final repository-owned introspector is accepted as the post-execution schema reader.
	 */
	public function createR1AdditiveExecutionCoordinator( WordPressCt1SchemaIntrospector $schema ): R1AdditiveExecutionCoordinator {
		return new R1AdditiveExecutionCoordinator(
			readiness: $this->create(),
			transitions: new MigrationRunTransitionService( $this->runs() ),
			database: $this->database,
			schema: $schema,
			compiler: new ProviderDdlPreviewCompiler(),
		);
	}

	/** ADR-0225 run preparation over the same run repository; only the final repository-owned introspector observes. */
	public function createR1MigrationRunPreparationService( WordPressCt1SchemaIntrospector $schema ): R1MigrationRunPreparationService {
		$runs = $this->runs();

		return new R1MigrationRunPreparationService(
			schema: $schema,
			planner: new TableSchemaDiffPlanner(),
			compiler: new ProviderDdlPreviewCompiler(),
			database: $this->database,
			runs: $runs,
			transitions: new MigrationRunTransitionService( $runs ),
			snapshots: new WpdbMigrationReviewSnapshotRepository( $this->database, $this->siteId ),
		);
	}

	/** ADR-0226 pre-execution lifecycle over the same run repository and policy engine. */
	public function createR1MigrationRunLifecycleService(): R1MigrationRunLifecycleService {
		return new R1MigrationRunLifecycleService(
			transitions: new MigrationRunTransitionService( $this->runs() ),
			snapshots: new WpdbMigrationReviewSnapshotRepository( $this->database, $this->siteId ),
			policy: $this->policy,
			siteId: $this->siteId,
		);
	}

	/** ADR-0226 request resolution; only the final repository-owned introspector and profile resolver are accepted. */
	public function createR1ExecutionRequestResolver(
		WordPressCt1SchemaIntrospector $schema,
		WordPressProviderCapabilityProfileResolver $profiles,
		DefinitionRepositoryInterface $definitions,
		string $sitePrefix,
	): R1ExecutionRequestResolver {
		$runs = $this->runs();

		return new R1ExecutionRequestResolver(
			runs: $runs,
			snapshots: new WpdbMigrationReviewSnapshotRepository( $this->database, $this->siteId ),
			definitions: $definitions,
			schema: $schema,
			profiles: $profiles,
			database: $this->database,
			transitions: new MigrationRunTransitionService( $runs ),
			sitePrefix: $sitePrefix,
			maxRecoveryEvidenceAgeHours: self::R1_MAX_RECOVERY_EVIDENCE_AGE_HOURS,
		);
	}

	/** Read-only lookup of a stored run in this site's scope. */
	public function run( string $runId ): ?MigrationRun {
		return $this->runs()->get( $runId );
	}

	private function runs(): WpdbMigrationRunRepository {
		return new WpdbMigrationRunRepository(
			$this->database,
			new MigrationRunRecordCodec(),
			$this->siteId,
		);
	}
}
