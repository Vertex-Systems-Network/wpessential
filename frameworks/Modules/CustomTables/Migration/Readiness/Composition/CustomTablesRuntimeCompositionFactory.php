<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Composition;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Modules\CustomTables\Migration\Execution\R1AdditiveExecutionCoordinator;
use WPEssential\Modules\CustomTables\Migration\ProviderDdlPreviewCompiler;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\WordPressMetadataPreconditionFactsProvider;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\Policy\MigrationExecutionAuthorizationPolicyAdapter;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\FailClosedRecoveryVerificationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\LocalBackupRecoveryVerificationProvider;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\TrustedRecoveryVerificationProviderInterface;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\MigrationRunRecordCodec;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunTransitionService;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\WpdbMigrationRunRepository;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class CustomTablesRuntimeCompositionFactory {

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

	private function runs(): WpdbMigrationRunRepository {
		return new WpdbMigrationRunRepository(
			$this->database,
			new MigrationRunRecordCodec(),
			$this->siteId,
		);
	}
}
