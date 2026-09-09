<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\Policy;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\ExecutionActorType;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\MigrationExecutionAuthorizationRequest;
use WPEssential\Platform\Auth\AuthorizationRequest;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;

final readonly class MigrationExecutionAuthorizationPolicyAdapter
{
    public function __construct(
        private PolicyEngine $policy,
        private string $ability = 'custom-tables.migration.execute',
        private string $capability = 'manage_options',
    ) {
    }

    public function request(
        ExecutionContext $context,
        string $runId,
        int $readinessStateRevision,
        bool $confirmationProvided,
        MigrationRisk $risk,
    ): MigrationExecutionAuthorizationRequest {
        $decision = $this->policy->authorize(new AuthorizationRequest(
            context: $context,
            ability: $this->ability,
            capability: $this->capability,
            resourceType: 'custom_table_migration_run',
            resourceId: $runId,
        ));

        $actorType = $context->principal->actorType === 'user'
            ? ExecutionActorType::User
            : ExecutionActorType::System;

        return new MigrationExecutionAuthorizationRequest(
            runId: $runId,
            readinessStateRevision: $readinessStateRevision,
            actorType: $actorType,
            actorUserId: $actorType === ExecutionActorType::User ? $context->principal->userId : null,
            capabilityAllowed: $decision->allowed,
            confirmationProvided: $confirmationProvided,
            risk: $risk,
        );
    }
}
