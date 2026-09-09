<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Composition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\ExecutionActorType;

final readonly class BoundMigrationExecutionConfirmation
{
    public function __construct(
        public string $runId,
        public string $planFingerprint,
        public int $readinessStateRevision,
        public ExecutionActorType $actorType,
        public ?int $actorUserId,
    ) {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $this->runId) !== 1) {
            throw new InvalidArgumentException('Custom Tables execution confirmation run id must be a canonical UUID.');
        }
        if (preg_match('/^[a-f0-9]{64}$/', $this->planFingerprint) !== 1) {
            throw new InvalidArgumentException('Custom Tables execution confirmation plan fingerprint is invalid.');
        }
        if ($this->readinessStateRevision < 1) {
            throw new InvalidArgumentException('Custom Tables execution confirmation readiness revision must be positive.');
        }
        if ($this->actorType === ExecutionActorType::User && ($this->actorUserId === null || $this->actorUserId < 1)) {
            throw new InvalidArgumentException('Custom Tables execution confirmation user actor requires a positive user id.');
        }
        if ($this->actorType === ExecutionActorType::System && $this->actorUserId !== null) {
            throw new InvalidArgumentException('Custom Tables execution confirmation system actor cannot carry a user id.');
        }
    }

    /** @return array<string,int|string|null> */
    public function canonical(): array
    {
        return [
            'run_id' => strtolower($this->runId),
            'plan_fingerprint' => $this->planFingerprint,
            'readiness_state_revision' => $this->readinessStateRevision,
            'actor_type' => $this->actorType->value,
            'actor_user_id' => $this->actorUserId,
        ];
    }
}
