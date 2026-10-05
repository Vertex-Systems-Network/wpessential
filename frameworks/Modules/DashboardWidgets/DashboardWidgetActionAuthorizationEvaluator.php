<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Modules\FormsWorkflows\FormWorkflowDefinition;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Abilities\InputValidation\AbilityInputValidator;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyDecision;

final readonly class DashboardWidgetActionAuthorizationEvaluator
{
    public const REASON_INVALID_ABILITY = 'dashboard_widget_action_invalid_ability';
    public const REASON_INVALID_INPUT = 'dashboard_widget_action_invalid_input';
    public const REASON_INVALID_CONTEXT = 'dashboard_widget_action_invalid_context';
    public const REASON_AUTHORIZATION_FAILURE = 'dashboard_widget_action_authorization_failure';

    private AbilityInputValidator $inputValidator;

    public function __construct(
        private AbilityRegistry $abilities,
        ?AbilityInputValidator $inputValidator = null,
    ) {
        $this->inputValidator = $inputValidator ?? new AbilityInputValidator();
    }

    /**
     * @param array<string,mixed> $input
     */
    public function authorize(
        string $abilityId,
        array $input,
        ExecutionContext $context,
    ): PolicyDecision {
        if (preg_match('#^wpessential/[a-z0-9][a-z0-9-]*/[a-z0-9][a-z0-9-]*$#', $abilityId) !== 1) {
            return PolicyDecision::deny(self::REASON_INVALID_ABILITY);
        }

        try {
            $descriptor = $this->abilities->descriptor($abilityId);
        } catch (Throwable) {
            return PolicyDecision::deny(self::REASON_AUTHORIZATION_FAILURE);
        }

        if (
            $descriptor === null
            || $descriptor->name !== $abilityId
            || $descriptor->ownerSurfaceId !== FormWorkflowDefinition::OWNER_SURFACE_ID
            || $descriptor->mutates !== true
            || !$descriptor->allows(ExecutionChannel::Ui)
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
            if ($descriptor->inputSchema === []) {
                if ($input !== []) {
                    return PolicyDecision::deny(self::REASON_INVALID_INPUT);
                }
            } else {
                $schemaResult = $this->inputValidator->validateSchema($descriptor->inputSchema);
                if (!$schemaResult->valid) {
                    return PolicyDecision::deny(self::REASON_INVALID_INPUT);
                }

                $inputResult = $this->inputValidator->validateInput(
                    $descriptor->inputSchema,
                    $input,
                );
                if (!$inputResult->valid) {
                    return PolicyDecision::deny(self::REASON_INVALID_INPUT);
                }
            }

            return $this->abilities->authorize($abilityId, $context, $input);
        } catch (Throwable) {
            return PolicyDecision::deny(self::REASON_AUTHORIZATION_FAILURE);
        }
    }
}
