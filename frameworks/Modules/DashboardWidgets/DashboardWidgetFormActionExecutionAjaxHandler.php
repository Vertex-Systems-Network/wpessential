<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Contracts\AuditLoggerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\FormsWorkflows\FormsWorkflowsModule;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Audit\AuditOutcome;
use WPEssential\Platform\Audit\AuditRecord;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxHandlerInterface;

final readonly class DashboardWidgetFormActionExecutionAjaxHandler implements AjaxHandlerInterface
{
    private DashboardWidgetRuntimeClock $clock;

    public const ROUTE_TYPE = 'dashboard-widgets.form-action.execute';

    public const STATE_EXECUTION_SUCCEEDED = 'execution_succeeded';
    public const STATE_EXECUTION_SUCCEEDED_AUDIT_DEGRADED = 'execution_succeeded_audit_degraded';
    public const STATE_EXECUTION_OUTCOME_UNKNOWN = 'execution_outcome_unknown';
    public const STATE_AUTHORIZATION_DENIED = 'authorization_denied';
    public const STATE_CONFIRMATION_INVALID = 'confirmation_invalid';
    public const STATE_STALE_DEFINITION = 'stale_definition';
    public const STATE_LIFECYCLE_INACTIVE = 'lifecycle_inactive';
    public const STATE_RUNTIME_FAILURE = 'runtime_failure';

    private const AUDIT_AUTHORIZATION = 'dashboard-widgets/action.authorization';
    private const AUDIT_CONFIRMATION = 'dashboard-widgets/action.confirmation';
    private const AUDIT_EXECUTION_ATTEMPT = 'dashboard-widgets/action.execution-attempt';
    private const AUDIT_RESULT = 'dashboard-widgets/action.result';
    private const RESOURCE_TYPE = 'dashboard-widget-action';

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private DashboardWidgetContentClassCompiler $contentClasses,
        private DashboardWidgetRegistrationCompiler $registrations,
        private DashboardWidgetActionInputBinder $inputBinder,
        private DashboardWidgetActionAuthorizationEvaluator $authorization,
        private WordPressExecutionContextFactory $contexts,
        private AuditLoggerInterface $audit,
        private AbilityRegistry $abilities,
        private DashboardWidgetFormActionExecutionResultAdapter $results,
        ?DashboardWidgetRuntimeClock $clock = null,
    ) {
        $this->clock = $clock ?? new DashboardWidgetRuntimeClock();
    }

    public function handle(array $payload): mixed
    {
        $request = $this->validRequest($payload);
        if ($request === null) {
            return $this->response(
                self::STATE_CONFIRMATION_INVALID,
                'The confirmed action is no longer valid. Refresh before trying again.',
            );
        }

        try {
            $current = $this->contexts->current();
            $context = new ExecutionContext(
                principal: $current->principal,
                siteId: $current->siteId,
                channel: ExecutionChannel::Ui,
                networkId: $current->networkId,
                correlationId: $current->correlationId,
            );
        } catch (Throwable) {
            return $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The action could not be executed. Refresh before trying again.',
            );
        }

        if (!$context->principal->isAuthenticated() || $context->principal->actorType !== 'user') {
            return $this->response(
                self::STATE_AUTHORIZATION_DENIED,
                'The action is not currently authorized.',
            );
        }

        try {
            $definition = $this->definitions->get($request['definition_id']);
        } catch (Throwable) {
            return $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The action could not be executed. Refresh before trying again.',
            );
        }

        if ($definition === null || $definition->revision !== $request['definition_revision']) {
            return $this->response(
                self::STATE_STALE_DEFINITION,
                'This widget changed. Refresh before taking any further action.',
            );
        }

        try {
            $contentClass = $this->contentClasses->compile($definition);
            $descriptor = $this->registrations->compile($definition);
        } catch (Throwable) {
            return $this->response(
                self::STATE_CONFIRMATION_INVALID,
                'The confirmed action is no longer valid. Refresh before trying again.',
            );
        }

        if (
            $contentClass->contentType !== DashboardWidgetContentClassDescriptor::TYPE_FORM_ACTION
            || $descriptor->actionAbilityId !== FormsWorkflowsModule::ABILITY_SET_ENABLED
            || $descriptor->actionConfirmation === null
            || $descriptor->actionInput === null
        ) {
            return $this->response(
                self::STATE_CONFIRMATION_INVALID,
                'The confirmed action is no longer valid. Refresh before trying again.',
            );
        }

        try {
            if (!$descriptor->isActiveAt($this->clock->now())) {
                return $this->response(
                    self::STATE_LIFECYCLE_INACTIVE,
                    'This widget is not currently active. Refresh before trying again.',
                );
            }
        } catch (Throwable) {
            return $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The action could not be executed. Refresh before trying again.',
            );
        }

        try {
            $input = $this->inputBinder->bind($descriptor->actionInput, $context);
            $decision = $this->authorization->authorize(
                $descriptor->actionAbilityId,
                $input,
                $context,
            );
        } catch (Throwable) {
            return $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The action could not be executed. Refresh before trying again.',
            );
        }

        try {
            $this->recordAudit(
                self::AUDIT_AUTHORIZATION,
                $decision->allowed ? AuditOutcome::Success : AuditOutcome::Denied,
                $context,
                $descriptor,
                $decision->allowed ? 'authorization_allowed' : 'authorization_denied',
                $this->auditReason($decision->reason),
            );
        } catch (Throwable) {
            return $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The action could not be executed. Refresh before trying again.',
            );
        }

        if (!$decision->allowed) {
            return $this->response(
                self::STATE_AUTHORIZATION_DENIED,
                'The action is not currently authorized.',
            );
        }

        try {
            $this->recordAudit(
                self::AUDIT_CONFIRMATION,
                AuditOutcome::Success,
                $context,
                $descriptor,
                'confirmation_accepted',
            );
            $this->recordAudit(
                self::AUDIT_EXECUTION_ATTEMPT,
                AuditOutcome::Success,
                $context,
                $descriptor,
                'execution_attempt',
            );
        } catch (Throwable) {
            return $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The action could not be executed. Refresh before trying again.',
            );
        }

        try {
            $ownerResult = $this->abilities->execute(
                $descriptor->actionAbilityId,
                $input,
                $context,
            );
        } catch (Throwable) {
            $this->recordUnknownResultAudit($context, $descriptor);

            return $this->response(
                self::STATE_EXECUTION_OUTCOME_UNKNOWN,
                'The action may have been applied, but the outcome could not be confirmed. Refresh before taking any further action.',
                'execution_outcome_unknown',
            );
        }

        $adapted = $this->results->adapt($ownerResult, $input);
        if ($adapted === null) {
            $this->recordUnknownResultAudit($context, $descriptor);

            return $this->response(
                self::STATE_EXECUTION_OUTCOME_UNKNOWN,
                'The action may have been applied, but the outcome could not be confirmed. Refresh before taking any further action.',
                'execution_outcome_unknown',
            );
        }

        try {
            $this->recordAudit(
                self::AUDIT_RESULT,
                AuditOutcome::Success,
                $context,
                $descriptor,
                $adapted['result_code'],
            );
        } catch (Throwable) {
            return $this->response(
                self::STATE_EXECUTION_SUCCEEDED_AUDIT_DEGRADED,
                'The action completed, but its final audit record could not be confirmed. Refresh to view the current state.',
                'execution_succeeded_audit_degraded',
            );
        }

        return $this->response(
            self::STATE_EXECUTION_SUCCEEDED,
            $adapted['notice'],
            $adapted['result_code'],
        );
    }

    /**
     * @param array<string,mixed> $payload
     * @return array{definition_id:string,definition_revision:int,confirmation_state:string}|null
     */
    private function validRequest(array $payload): ?array
    {
        $keys = array_keys($payload);
        sort($keys, SORT_STRING);
        if ($keys !== ['confirmation_state', 'definition_id', 'definition_revision']) {
            return null;
        }

        $definitionId = $payload['definition_id'] ?? null;
        $revision = $payload['definition_revision'] ?? null;
        $state = $payload['confirmation_state'] ?? null;

        if (
            !is_string($definitionId)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $definitionId) !== 1
            || !is_int($revision)
            || $revision < 1
            || $state !== 'accepted'
        ) {
            return null;
        }

        return [
            'definition_id' => $definitionId,
            'definition_revision' => $revision,
            'confirmation_state' => 'accepted',
        ];
    }

    private function recordUnknownResultAudit(
        ExecutionContext $context,
        DashboardWidgetRegistrationDescriptor $descriptor,
    ): void {
        try {
            $this->recordAudit(
                self::AUDIT_RESULT,
                AuditOutcome::Unknown,
                $context,
                $descriptor,
                'execution_outcome_unknown',
            );
        } catch (Throwable) {
            // The execution outcome is already unknown. Never retry execution because terminal audit also failed.
        }
    }

    private function recordAudit(
        string $action,
        AuditOutcome $outcome,
        ExecutionContext $context,
        DashboardWidgetRegistrationDescriptor $descriptor,
        string $resultCode,
        ?string $reason = null,
    ): void {
        $this->audit->record(new AuditRecord(
            id: $this->uuid(),
            context: $context,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            action: $action,
            outcome: $outcome,
            resourceType: self::RESOURCE_TYPE,
            resourceId: $descriptor->definitionId,
            reason: $reason,
            metadata: [
                'widget_key' => $descriptor->key,
                'definition_revision' => $descriptor->revision,
                'ability_id' => $descriptor->actionAbilityId,
                'result_code' => $resultCode,
                'confirmation_state' => 'accepted',
                'input_present' => $descriptor->actionInput?->hasInput() ?? false,
            ],
        ));
    }

    private function auditReason(?string $reason): ?string
    {
        if (
            $reason === null
            || strlen($reason) > 160
            || preg_match('/^[a-z0-9][a-z0-9_.:-]*$/', $reason) !== 1
        ) {
            return null;
        }

        return $reason;
    }

    /** @return array{state:string,notice:string,result_code:string,retry_mode:string} */
    private function response(
        string $state,
        string $notice,
        ?string $resultCode = null,
    ): array {
        return [
            'state' => $state,
            'notice' => $notice,
            'result_code' => $resultCode ?? $state,
            'retry_mode' => 'none',
        ];
    }

    private function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20),
        );
    }
}
