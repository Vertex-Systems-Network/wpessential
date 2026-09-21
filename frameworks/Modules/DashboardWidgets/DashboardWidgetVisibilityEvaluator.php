<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class DashboardWidgetVisibilityEvaluator
{
    public function __construct(
        private CapabilityCheckerInterface $capabilities,
        private DashboardWidgetRoleMembershipProviderInterface $roles,
    ) {}

    public function evaluate(
        DashboardWidgetVisibilityDescriptor $visibility,
        ExecutionContext $context,
    ): DashboardWidgetVisibilityDecision {
        if (!$context->principal->isAuthenticated()) {
            return DashboardWidgetVisibilityDecision::deny(
                DashboardWidgetVisibilityDecision::REASON_UNAUTHENTICATED,
            );
        }

        if ($context->principal->actorType !== 'user') {
            return DashboardWidgetVisibilityDecision::deny(
                DashboardWidgetVisibilityDecision::REASON_NON_USER_ACTOR,
            );
        }

        if (!$this->roles->isCurrentUserContext($context)) {
            return DashboardWidgetVisibilityDecision::deny(
                DashboardWidgetVisibilityDecision::REASON_CONTEXT_MISMATCH,
            );
        }

        if ($visibility->roles !== [] && !$this->roles->hasAnyRole($context, $visibility->roles)) {
            return DashboardWidgetVisibilityDecision::deny(
                DashboardWidgetVisibilityDecision::REASON_ROLE_MISMATCH,
            );
        }

        if ($visibility->capabilities !== [] && !$this->hasAnyCapability($context, $visibility->capabilities)) {
            return DashboardWidgetVisibilityDecision::deny(
                DashboardWidgetVisibilityDecision::REASON_CAPABILITY_MISMATCH,
            );
        }

        $userId = $context->principal->userId;
        if ($visibility->users !== [] && (!is_int($userId) || !in_array($userId, $visibility->users, true))) {
            return DashboardWidgetVisibilityDecision::deny(
                DashboardWidgetVisibilityDecision::REASON_USER_MISMATCH,
            );
        }

        return DashboardWidgetVisibilityDecision::allow();
    }

    /**
     * @param list<string> $capabilities
     */
    private function hasAnyCapability(ExecutionContext $context, array $capabilities): bool
    {
        try {
            foreach ($capabilities as $capability) {
                if ($this->capabilities->can($context, $capability)) {
                    return true;
                }
            }
        } catch (Throwable) {
            return false;
        }

        return false;
    }
}
