<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Execution;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\MigrationPlan;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionRequirement;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Migration\ProviderMigrationPreview;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryRequirement;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Platform\Auth\ExecutionContext;

/** Everything the R1 coordinator needs to re-evaluate readiness and recompile the reviewed plan. */
final readonly class R1AdditiveExecutionRequest {

	/** @param list<PreconditionRequirement> $requirements */
	public function __construct(
		public MigrationRun $run,
		public MigrationPlan $plan,
		public TableSchemaDescriptor $desired,
		public Ct1ManagedTableIdentity $identity,
		public ProviderCapabilityProfile $capabilities,
		public ProviderMigrationPreview $reviewedPreview,
		public array $requirements,
		public MigrationGenerationStamp $reviewedGeneration,
		public MigrationGenerationStamp $currentGeneration,
		public RecoveryRequirement $recoveryRequirement,
		public string $recoveryArtifactId,
		public int $maxRecoveryEvidenceAgeHours,
		public ExecutionContext $context,
	) {
	}
}
