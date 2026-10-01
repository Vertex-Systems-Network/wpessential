<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Modules\FormsWorkflows\FormWorkflowDefinition;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyDecision;

final readonly class DashboardWidgetActionAuthorizationEvaluator
{
    public const REASON_INVALID_ABILITY = 'dashboard_widget_action_invalid_ability';
    public const REASON_INVALID_CONTEXT = 'dashboard_widget_action_invalid_context';
    public const REASON_AUTHORIZATION_FAILURE = 'dashboard_widget_action_authorization_failure';

    public function __construct(
        private AbilityRegistry $abilities,
    ) {}

    public function authorize(string $abilityId, ExecutionContext $context): PolicyDecision
    {
        if (preg_match('#^wpessential/[a-z0-9][a-z0-9-]*/[a-z0-9][a-z0-9-]*$#', $abilityId) !== 1) {
            return PolicyDecision::deny(self::REASON_INVALID_ABILITY);
        }

        $descriptor = $this->abilities->descriptor($abilityId);
        if (
            $descriptor === null
            || $descriptor->name !== $abilityId
            || $descriptor->ownerSurfaceId !== FormWorkflowDefinition::OWNER_SURFACE_ID
            || $descriptor->mutates !== true
            || !$descriptor->allows(ExecutionChannel::Ui)
            || $descriptor->inputSchema !== []
        ) {
            return PolicyDecision::deny(self::REASON_INVALID_ABILITY);
        }

        if (
            !$context->principal->isAuthenticated()
            || $context->principal->actorType !== 'user'
            || $context->channel !== ExecutionChannel::Ui
        ) {
            return PolicyDecision::deny(self::REASON_INVALID_CONTEXT);
        }

        try {
            return $this->abilities->authorize($abilityId, $context, []);
        } catch (Throwable) {
            return PolicyDecision::deny(self::REASON_AUTHORIZATION_FAILURE);
        }
    }
}
