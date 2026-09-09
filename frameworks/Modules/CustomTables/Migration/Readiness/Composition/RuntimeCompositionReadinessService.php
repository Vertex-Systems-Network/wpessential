<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Composition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionEvaluation;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionKind;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionOutcome;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionProbeRegistry;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionReport;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionRequirement;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\MetadataPreconditionFacts;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\MetadataPreconditionProbe;
use WPEssential\Modules\CustomTables\Migration\ProviderMigrationPreview;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\ExecutionActorType;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\MigrationExecutionAuthorizationDecision;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\Policy\MigrationExecutionAuthorizationPolicyAdapter;
use WPEssential\Modules\CustomTables\Migration\Readiness\MigrationExecutionReadinessDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\Binding\RecoveryEvidenceBindingDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationRevalidationDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\RecoveryVerificationProviderInterface;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryReadinessDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\RecoveryRequirement;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class RuntimeCompositionReadinessService
{
    public function __construct(
        private MigrationRunRepositoryInterface $runs,
        private RecoveryVerificationProviderInterface $recoveryVerification,
        private MigrationExecutionConfirmationProviderInterface $confirmations,
        private MigrationExecutionAuthorizationPolicyAdapter $authorizationPolicy,
        private int $networkId,
        private int $siteId,
    ) {
        if ($this->networkId < 1 || $this->siteId < 1) {
            throw new InvalidArgumentException('Custom Tables runtime composition scope ids must be positive.');
        }
    }

    /**
     * @param list<PreconditionRequirement> $requirements
     */
    public function evaluate(
        MigrationRun $run,
        array $requirements,
        MetadataPreconditionFacts $metadataFacts,
        MigrationGenerationStamp $reviewedGeneration,
        MigrationGenerationStamp $currentGeneration,
        RecoveryRequirement $recoveryRequirement,
        string $recoveryArtifactId,
        int $maxRecoveryEvidenceAgeHours,
        ProviderMigrationPreview $preview,
        ExecutionContext $context,
        MigrationRisk $risk,
    ): RuntimeCompositionReadinessPackage {
        if ($maxRecoveryEvidenceAgeHours < 0) {
            throw new InvalidArgumentException('Custom Tables runtime composition recovery evidence age cannot be negative.');
        }

        $reasons = [];
        $stored = $this->runs->get($run->id);
        if (!$stored instanceof MigrationRun) {
            $reasons[] = 'run_not_persisted';
        } elseif ($stored->fingerprint() !== $run->fingerprint()) {
            $reasons[] = 'persisted_run_mismatch';
        }

        if ($context->siteId !== $this->siteId) {
            $reasons[] = 'site_scope_mismatch';
        }
        if ($context->networkId === null) {
            $reasons[] = 'network_scope_missing';
        } elseif ($context->networkId !== $this->networkId) {
            $reasons[] = 'network_scope_mismatch';
        }

        if (!$this->generationMatchesRun($reviewedGeneration, $run)) {
            $reasons[] = 'reviewed_generation_run_mismatch';
        }
        if (!$this->generationMatchesRun($currentGeneration, $run)) {
            $reasons[] = 'current_generation_run_mismatch';
        }
        if ($preview->planFingerprint !== $run->planFingerprint
            || $preview->capabilities->provider !== $currentGeneration->provider
            || $preview->capabilities->version !== $currentGeneration->providerVersion
        ) {
            $reasons[] = 'preview_generation_mismatch';
        }
        if ($preview->executionAllowed) {
            $reasons[] = 'preview_execution_enabled';
        }

        $preconditions = $this->evaluateMetadataPreconditions($requirements, $metadataFacts, $reasons);
        $revalidation = MigrationRevalidationDecision::evaluate($reviewedGeneration, $currentGeneration);

        $boundRecovery = null;
        try {
            $boundRecovery = $this->recoveryVerification->verify($recoveryArtifactId, $run->planFingerprint);
        } catch (RuntimeException) {
            $reasons[] = 'recovery_verification_failed';
        }

        $recoveryBinding = RecoveryEvidenceBindingDecision::evaluate(
            $reviewedGeneration,
            $boundRecovery,
            $maxRecoveryEvidenceAgeHours,
        );
        foreach ($recoveryBinding->reasons as $reason) {
            $reasons[] = 'recovery_binding_' . $reason;
        }

        $recovery = null;
        if ($boundRecovery !== null) {
            $recovery = RecoveryReadinessDecision::evaluate(
                $recoveryRequirement,
                $boundRecovery->evidence,
                $run->planFingerprint,
            );
        } else {
            $reasons[] = 'recovery_evidence_missing';
        }

        $readiness = null;
        if ($recovery instanceof RecoveryReadinessDecision) {
            $readiness = MigrationExecutionReadinessDecision::evaluate(
                $run,
                $preconditions,
                $revalidation,
                $recovery,
                $preview,
            );
            foreach ($readiness->reasons as $reason) {
                $reasons[] = 'readiness_' . $reason;
            }
        } else {
            $reasons[] = 'readiness_incomplete';
        }

        $confirmation = $this->confirmations->confirmationFor($run->id);
        $confirmationValid = $this->confirmationMatches($confirmation, $run, $context, $reasons);

        $authorizationRequest = $this->authorizationPolicy->request(
            $context,
            $run->id,
            $run->stateRevision,
            $confirmationValid,
            $risk,
        );

        if ($readiness instanceof MigrationExecutionReadinessDecision) {
            $authorization = MigrationExecutionAuthorizationDecision::evaluate(
                $run,
                $readiness,
                $authorizationRequest,
            );
            foreach ($authorization->reasons as $reason) {
                $reasons[] = 'authorization_' . $reason;
            }
        } else {
            if (!$authorizationRequest->capabilityAllowed) {
                $reasons[] = 'authorization_capability_denied';
            }
            if (!$authorizationRequest->confirmationProvided) {
                $reasons[] = 'authorization_confirmation_missing';
            }
            if (in_array($risk, [MigrationRisk::R3, MigrationRisk::R4], true)) {
                $reasons[] = 'authorization_unsupported_risk';
            }
        }

        $reasons = array_values(array_unique($reasons));
        sort($reasons);

        return new RuntimeCompositionReadinessPackage(
            runId: $run->id,
            planFingerprint: $run->planFingerprint,
            readinessStateRevision: $run->stateRevision,
            compositionReady: $reasons === [],
            reasons: $reasons,
        );
    }

    /**
     * @param list<PreconditionRequirement> $requirements
     * @param list<string> $reasons
     */
    private function evaluateMetadataPreconditions(
        array $requirements,
        MetadataPreconditionFacts $facts,
        array &$reasons,
    ): PreconditionReport {
        $probe = new MetadataPreconditionProbe($facts);
        $registry = new PreconditionProbeRegistry();
        foreach ($this->allowedMetadataKinds() as $kind) {
            $registry->register($kind, $probe);
        }

        $evaluations = [];
        $ids = [];
        foreach ($requirements as $requirement) {
            if (!$requirement instanceof PreconditionRequirement) {
                throw new InvalidArgumentException('Custom Tables runtime composition requires typed preconditions.');
            }
            if (isset($ids[$requirement->id])) {
                throw new InvalidArgumentException('Custom Tables runtime composition contains duplicate precondition ids.');
            }
            $ids[$requirement->id] = true;

            if (!$registry->has($requirement->kind)) {
                $reasons[] = 'precondition_kind_not_allowlisted';
                $evaluations[] = new PreconditionEvaluation(
                    $requirement->id,
                    PreconditionOutcome::Unsupported,
                );
                continue;
            }

            $evaluations[] = $registry->evaluate($requirement);
        }

        return new PreconditionReport($evaluations);
    }

    /** @return list<PreconditionKind> */
    private function allowedMetadataKinds(): array
    {
        return [
            PreconditionKind::TableExists,
            PreconditionKind::TableMissing,
            PreconditionKind::ColumnMatchesFingerprint,
            PreconditionKind::DatabaseFeatureAvailable,
        ];
    }

    private function generationMatchesRun(MigrationGenerationStamp $generation, MigrationRun $run): bool
    {
        return $generation->planFingerprint === $run->planFingerprint
            && strtolower($generation->targetDefinitionId) === strtolower($run->targetDefinitionId)
            && $generation->targetRevision === $run->targetRevision
            && $generation->targetSchemaVersion === $run->targetSchemaVersion;
    }

    /** @param list<string> $reasons */
    private function confirmationMatches(
        ?BoundMigrationExecutionConfirmation $confirmation,
        MigrationRun $run,
        ExecutionContext $context,
        array &$reasons,
    ): bool {
        if (!$confirmation instanceof BoundMigrationExecutionConfirmation) {
            $reasons[] = 'confirmation_missing';
            return false;
        }

        $valid = true;
        if (strtolower($confirmation->runId) !== strtolower($run->id)) {
            $reasons[] = 'confirmation_run_mismatch';
            $valid = false;
        }
        if ($confirmation->planFingerprint !== $run->planFingerprint) {
            $reasons[] = 'confirmation_plan_mismatch';
            $valid = false;
        }
        if ($confirmation->readinessStateRevision !== $run->stateRevision) {
            $reasons[] = 'confirmation_stale';
            $valid = false;
        }

        $expectedActorType = $context->principal->actorType === 'user'
            ? ExecutionActorType::User
            : ExecutionActorType::System;
        $expectedActorUserId = $expectedActorType === ExecutionActorType::User
            ? $context->principal->userId
            : null;

        if ($confirmation->actorType !== $expectedActorType
            || $confirmation->actorUserId !== $expectedActorUserId
        ) {
            $reasons[] = 'confirmation_actor_mismatch';
            $valid = false;
        }

        return $valid;
    }
}
