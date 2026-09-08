<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Authorization;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;

final readonly class MigrationExecutionAuthorizationRequest
{
    public function __construct(
        public string $runId,
        public int $readinessStateRevision,
        public ExecutionActorType $actorType,
        public ?int $actorUserId,
        public bool $capabilityAllowed,
        public bool $confirmationProvided,
        public MigrationRisk $risk,
    ) {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $this->runId) !== 1) {
            throw new InvalidArgumentException('Custom Tables execution authorization run id must be a canonical UUID.');
        }
        if ($this->readinessStateRevision < 1) {
            throw new InvalidArgumentException('Custom Tables execution authorization readiness revision must be positive.');
        }
        if ($this->actorType === ExecutionActorType::User && ($this->actorUserId === null || $this->actorUserId < 1)) {
            throw new InvalidArgumentException('Custom Tables execution authorization user actor requires a positive user id.');
        }
        if ($this->actorType === ExecutionActorType::System && $this->actorUserId !== null) {
            throw new InvalidArgumentException('Custom Tables execution authorization system actor cannot carry a user id.');
        }
    }

    /** @return array<string,int|string|bool|null> */
    public function canonical(): array
    {
        return [
            'run_id' => strtolower($this->runId),
            'readiness_state_revision' => $this->readinessStateRevision,
            'actor_type' => $this->actorType->value,
            'actor_user_id' => $this->actorUserId,
            'capability_allowed' => $this->capabilityAllowed,
            'confirmation_provided' => $this->confirmationProvided,
            'risk' => $this->risk->value,
        ];
    }
}
