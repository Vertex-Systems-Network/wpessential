<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Contracts\AuditLoggerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Audit\AuditOutcome;
use WPEssential\Platform\Audit\AuditRecord;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxHandlerInterface;

final readonly class DashboardWidgetFormActionPreflightAjaxHandler implements AjaxHandlerInterface
{
    public const STATE_CONFIRMATION_READY = 'confirmation_ready';
    public const STATE_CONFIRMATION_CANCELLED = 'confirmation_cancelled';
    public const STATE_AUTHORIZATION_DENIED = 'authorization_denied';
    public const STATE_CONFIRMATION_INVALID = 'confirmation_invalid';
    public const STATE_STALE_DEFINITION = 'stale_definition';
    public const STATE_RUNTIME_FAILURE = 'runtime_failure';

    private const AUDIT_AUTHORIZATION = 'dashboard-widgets/action.authorization';
    private const AUDIT_CONFIRMATION = 'dashboard-widgets/action.confirmation';
    private const RESOURCE_TYPE = 'dashboard-widget-action';

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private DashboardWidgetContentClassCompiler $contentClasses,
        private DashboardWidgetRegistrationCompiler $registrations,
        private DashboardWidgetActionInputBinder $inputBinder,
        private DashboardWidgetActionAuthorizationEvaluator $authorization,
        private WordPressExecutionContextFactory $contexts,
        private AuditLoggerInterface $audit,
    ) {}

    public function handle(array $payload): mixed
    {
        $request = $this->validRequest($payload);
        if ($request === null) {
            return $this->response(
                self::STATE_CONFIRMATION_INVALID,
                'The action confirmation is no longer valid. Refresh and try again.',
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
                'The action could not be prepared. Refresh and try again.',
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
                'The action could not be prepared. Refresh and try again.',
            );
        }

        if ($definition === null || $definition->revision !== $request['definition_revision']) {
            return $this->response(
                self::STATE_STALE_DEFINITION,
                'This widget changed. Refresh before confirming the action.',
            );
        }

        try {
            $contentClass = $this->contentClasses->compile($definition);
            $descriptor = $this->registrations->compile($definition);
        } catch (Throwable) {
            return $this->response(
                self::STATE_CONFIRMATION_INVALID,
                'The action confirmation is no longer valid. Refresh and try again.',
            );
        }

        if (
            $contentClass->contentType !== DashboardWidgetContentClassDescriptor::TYPE_FORM_ACTION
            || $descriptor->actionAbilityId === null
            || $descriptor->actionConfirmation === null
            || $descriptor->actionInput === null
        ) {
            return $this->response(
                self::STATE_CONFIRMATION_INVALID,
                'The action confirmation is no longer valid. Refresh and try again.',
            );
        }

        if ($request['confirmation_state'] === 'cancelled') {
            try {
                $this->recordAudit(
                    self::AUDIT_CONFIRMATION,
                    AuditOutcome::Denied,
                    $context,
                    $descriptor,
                    'confirmation_cancelled',
                    'cancelled',
                    $descriptor->actionInput->hasInput(),
                );
            } catch (Throwable) {
                return $this->response(
                    self::STATE_RUNTIME_FAILURE,
                    'The action could not be prepared. Refresh and try again.',
                );
            }

            return $this->response(
                self::STATE_CONFIRMATION_CANCELLED,
                'Action confirmation was cancelled. No change was performed.',
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
                'The action could not be prepared. Refresh and try again.',
            );
        }

        try {
            $this->recordAudit(
                self::AUDIT_AUTHORIZATION,
                $decision->allowed ? AuditOutcome::Success : AuditOutcome::Denied,
                $context,
                $descriptor,
                $decision->allowed ? 'authorization_allowed' : 'authorization_denied',
                'accepted',
                $input !== [],
                $decision->reason,
            );
        } catch (Throwable) {
            return $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The action could not be prepared. Refresh and try again.',
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
                'confirmation_ready',
                'accepted',
                $input !== [],
            );
        } catch (Throwable) {
            return $this->response(
                self::STATE_RUNTIME_FAILURE,
                'The action could not be prepared. Refresh and try again.',
            );
        }

        return $this->response(
            self::STATE_CONFIRMATION_READY,
            'Action confirmed and authorized. Execution has not been performed.',
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
            || !is_string($state)
            || !in_array($state, ['accepted', 'cancelled'], true)
        ) {
            return null;
        }

        return [
            'definition_id' => $definitionId,
            'definition_revision' => $revision,
            'confirmation_state' => $state,
        ];
    }

    private function recordAudit(
        string $action,
        AuditOutcome $outcome,
        ExecutionContext $context,
        DashboardWidgetRegistrationDescriptor $descriptor,
        string $resultCode,
        string $confirmationState,
        bool $inputPresent,
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
                'confirmation_state' => $confirmationState,
                'input_present' => $inputPresent,
            ],
        ));
    }

    /** @return array{state:string,notice:string} */
    private function response(string $state, string $notice): array
    {
        return [
            'state' => $state,
            'notice' => $notice,
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
            substr($hex, 20, 12),
        );
    }
}
