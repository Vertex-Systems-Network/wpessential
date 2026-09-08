<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Run;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;

final readonly class MigrationRun
{
    public function __construct(
        public string $id,
        public string $planFingerprint,
        public string $tableKey,
        public string $targetDefinitionId,
        public int $targetRevision,
        public int $targetSchemaVersion,
        public MigrationRunState $state,
        public int $stateRevision = 1,
    ) {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $this->id) !== 1) {
            throw new InvalidArgumentException('Custom Tables migration run id must be a canonical UUID.');
        }
        if (preg_match('/^[a-f0-9]{64}$/', $this->planFingerprint) !== 1) {
            throw new InvalidArgumentException('Custom Tables migration run plan fingerprint is invalid.');
        }
        if (preg_match('/^[a-z][a-z0-9_]{0,63}$/', $this->tableKey) !== 1) {
            throw new InvalidArgumentException('Custom Tables migration run table key is invalid.');
        }
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $this->targetDefinitionId) !== 1) {
            throw new InvalidArgumentException('Custom Tables migration run target Definition id is invalid.');
        }
        if ($this->targetRevision < 1 || $this->targetSchemaVersion < 1 || $this->stateRevision < 1) {
            throw new InvalidArgumentException('Custom Tables migration run revisions must be positive.');
        }
    }

    public function canTransitionTo(MigrationRunState $next): bool
    {
        return in_array($next, self::transitions()[$this->state->value] ?? [], true);
    }

    public function transition(MigrationRunState $next): self
    {
        if (!$this->canTransitionTo($next)) {
            throw new InvalidArgumentException(sprintf(
                'Illegal Custom Tables migration run transition from %s to %s.',
                $this->state->value,
                $next->value,
            ));
        }

        return new self(
            id: $this->id,
            planFingerprint: $this->planFingerprint,
            tableKey: $this->tableKey,
            targetDefinitionId: $this->targetDefinitionId,
            targetRevision: $this->targetRevision,
            targetSchemaVersion: $this->targetSchemaVersion,
            state: $next,
            stateRevision: $this->stateRevision + 1,
        );
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        return [
            'id' => strtolower($this->id),
            'plan_fingerprint' => $this->planFingerprint,
            'table_key' => $this->tableKey,
            'target_definition_id' => strtolower($this->targetDefinitionId),
            'target_revision' => $this->targetRevision,
            'target_schema_version' => $this->targetSchemaVersion,
            'state' => $this->state->value,
            'state_revision' => $this->stateRevision,
        ];
    }

    /** @throws JsonException */
    public function fingerprint(): string
    {
        return hash('sha256', json_encode($this->canonical(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }

    /** @return array<string,list<MigrationRunState>> */
    private static function transitions(): array
    {
        return [
            MigrationRunState::Planned->value => [
                MigrationRunState::AwaitingReview,
                MigrationRunState::Superseded,
                MigrationRunState::CancelledBeforeMutation,
            ],
            MigrationRunState::AwaitingReview->value => [
                MigrationRunState::BlockedPrecondition,
                MigrationRunState::Approved,
                MigrationRunState::Superseded,
                MigrationRunState::CancelledBeforeMutation,
            ],
            MigrationRunState::BlockedPrecondition->value => [
                MigrationRunState::AwaitingReview,
                MigrationRunState::Superseded,
                MigrationRunState::CancelledBeforeMutation,
            ],
            MigrationRunState::Approved->value => [
                MigrationRunState::Queued,
                MigrationRunState::Revalidating,
                MigrationRunState::Superseded,
                MigrationRunState::CancelledBeforeMutation,
            ],
            MigrationRunState::Queued->value => [
                MigrationRunState::Revalidating,
                MigrationRunState::Superseded,
                MigrationRunState::CancelledBeforeMutation,
            ],
            MigrationRunState::Revalidating->value => [
                MigrationRunState::BlockedPrecondition,
                MigrationRunState::Running,
                MigrationRunState::Superseded,
                MigrationRunState::CancelledBeforeMutation,
            ],
            MigrationRunState::Running->value => [
                MigrationRunState::PausedSafePoint,
                MigrationRunState::Verifying,
                MigrationRunState::FailedRecoverable,
                MigrationRunState::FailedRecoveryRequired,
            ],
            MigrationRunState::PausedSafePoint->value => [
                MigrationRunState::Running,
                MigrationRunState::Verifying,
                MigrationRunState::FailedRecoverable,
                MigrationRunState::FailedRecoveryRequired,
            ],
            MigrationRunState::Verifying->value => [
                MigrationRunState::Applied,
                MigrationRunState::AppliedWithWarnings,
                MigrationRunState::FailedRecoverable,
                MigrationRunState::FailedRecoveryRequired,
            ],
        ];
    }
}
