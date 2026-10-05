<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\AuditLoggerInterface;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\InputAuthorizingAbilityHandlerInterface;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetActionAuthorizationEvaluator;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetActionInputBinder;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetFormActionExecutionAjaxHandler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetFormActionExecutionResultAdapter;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityCompiler;
use WPEssential\Modules\FormsWorkflows\FormsWorkflowsModule;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Abilities\InputValidation\AbilityInputValidator;
use WPEssential\Platform\Audit\AuditRecord;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyDecision;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\DynamicValues\DynamicValueRequest;
use WPEssential\Platform\DynamicValues\DynamicValueResult;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

final class DashboardWidgetFormActionExecutionAjaxHandlerTest extends TestCase
{
    private const DASHBOARD_ID = '11111111-1111-4111-8111-111111111111';
    private const FORMS_ID = '22222222-2222-4222-8222-222222222222';

    public function testExecutesExactlyOnceAfterAuthorizationAndRequiredAudits(): void
    {
        [$handler, $ability, $audit] = $this->harness();

        $result = $handler->handle($this->request());

        self::assertSame(
            DashboardWidgetFormActionExecutionAjaxHandler::STATE_EXECUTION_SUCCEEDED,
            $result['state'],
        );
        self::assertSame('status_changed', $result['result_code']);
        self::assertSame('none', $result['retry_mode']);
        self::assertSame(2, $ability->authorizationCalls);
        self::assertSame(1, $ability->executions);
        self::assertSame([
            'definition_id' => self::FORMS_ID,
            'enabled' => false,
            'expected_revision' => 4,
        ], $ability->lastInput);
        self::assertSame([
            'dashboard-widgets/action.authorization',
            'dashboard-widgets/action.confirmation',
            'dashboard-widgets/action.execution-attempt',
            'dashboard-widgets/action.result',
        ], array_map(
            static fn (AuditRecord $record): string => $record->action,
            $audit->records,
        ));
        self::assertSame('status_changed', $audit->records[3]->metadata['result_code']);
        self::assertSame(
            [
                'widget_key',
                'definition_revision',
                'ability_id',
                'result_code',
                'confirmation_state',
                'input_present',
            ],
            array_keys($audit->records[3]->metadata),
        );
        self::assertArrayNotHasKey('definition_id', $audit->records[3]->metadata);
        self::assertArrayNotHasKey('enabled', $audit->records[3]->metadata);
    }

    public function testAlreadyTargetStatusIsBoundedSuccessWithoutRetry(): void
    {
        [$handler, $ability] = $this->harness(ownerResult: [
            'definition_id' => self::FORMS_ID,
            'previous_revision' => 4,
            'revision' => 4,
            'status' => 'disabled',
            'enabled' => false,
            'changed' => false,
            'result_code' => 'already_target_status',
        ]);

        $result = $handler->handle($this->request());

        self::assertSame(
            DashboardWidgetFormActionExecutionAjaxHandler::STATE_EXECUTION_SUCCEEDED,
            $result['state'],
        );
        self::assertSame('already_target_status', $result['result_code']);
        self::assertStringContainsString('already in the requested state', $result['notice']);
        self::assertSame('none', $result['retry_mode']);
        self::assertSame(1, $ability->executions);
    }

    public function testMalformedOwnerResultMapsToOutcomeUnknownWithoutRetry(): void
    {
        [$handler, $ability, $audit] = $this->harness(ownerResult: [
            'definition_id' => self::FORMS_ID,
            'unexpected' => 'secret-value',
        ]);

        $result = $handler->handle($this->request());

        self::assertSame(
            DashboardWidgetFormActionExecutionAjaxHandler::STATE_EXECUTION_OUTCOME_UNKNOWN,
            $result['state'],
        );
        self::assertSame('execution_outcome_unknown', $result['result_code']);
        self::assertSame('none', $result['retry_mode']);
        self::assertSame(1, $ability->executions);
        self::assertCount(4, $audit->records);
        self::assertSame('execution_outcome_unknown', $audit->records[3]->metadata['result_code']);
        self::assertStringNotContainsString(
            'secret-value',
            json_encode($audit->records[3]->metadata, JSON_THROW_ON_ERROR),
        );
    }

    public function testExecuteExceptionMapsToOutcomeUnknownAndNeverRetries(): void
    {
        [$handler, $ability, $audit] = $this->harness(throwOnExecute: true);

        $result = $handler->handle($this->request());

        self::assertSame(
            DashboardWidgetFormActionExecutionAjaxHandler::STATE_EXECUTION_OUTCOME_UNKNOWN,
            $result['state'],
        );
        self::assertSame('none', $result['retry_mode']);
        self::assertSame(1, $ability->executions);
        self::assertCount(4, $audit->records);
        self::assertSame('dashboard-widgets/action.result', $audit->records[3]->action);
    }

    public function testOwnerAuthorizationDenialStopsBeforeExecute(): void
    {
        [$handler, $ability, $audit] = $this->harness(
            ownerDecision: PolicyDecision::deny('forms_workflows_set_enabled_revision_conflict'),
        );

        $result = $handler->handle($this->request());

        self::assertSame(
            DashboardWidgetFormActionExecutionAjaxHandler::STATE_AUTHORIZATION_DENIED,
            $result['state'],
        );
        self::assertSame(1, $ability->authorizationCalls);
        self::assertSame(0, $ability->executions);
        self::assertCount(1, $audit->records);
        self::assertSame('authorization_denied', $audit->records[0]->metadata['result_code']);
        self::assertSame(
            'forms_workflows_set_enabled_revision_conflict',
            $audit->records[0]->reason,
        );
    }

    public function testPreExecutionAuditFailurePreventsExecute(): void
    {
        [$handler, $ability, $audit] = $this->harness(failAuditAt: 3);

        $result = $handler->handle($this->request());

        self::assertSame(
            DashboardWidgetFormActionExecutionAjaxHandler::STATE_RUNTIME_FAILURE,
            $result['state'],
        );
        self::assertSame(1, $ability->authorizationCalls);
        self::assertSame(0, $ability->executions);
        self::assertCount(2, $audit->records);
        self::assertSame('dashboard-widgets/action.confirmation', $audit->records[1]->action);
    }

    public function testKnownSuccessWithResultAuditFailureIsAuditDegraded(): void
    {
        [$handler, $ability, $audit] = $this->harness(failAuditAt: 4);

        $result = $handler->handle($this->request());

        self::assertSame(
            DashboardWidgetFormActionExecutionAjaxHandler::STATE_EXECUTION_SUCCEEDED_AUDIT_DEGRADED,
            $result['state'],
        );
        self::assertSame('execution_succeeded_audit_degraded', $result['result_code']);
        self::assertSame('none', $result['retry_mode']);
        self::assertSame(1, $ability->executions);
        self::assertCount(3, $audit->records);
        self::assertSame('dashboard-widgets/action.execution-attempt', $audit->records[2]->action);
    }

    public function testMalformedExtraAndStaleRequestsFailClosedBeforeAuthorization(): void
    {
        [$handler, $ability, $audit] = $this->harness();

        $extra = $this->request();
        $extra['ability_id'] = FormsWorkflowsModule::ABILITY_SET_ENABLED;
        self::assertSame(
            DashboardWidgetFormActionExecutionAjaxHandler::STATE_CONFIRMATION_INVALID,
            $handler->handle($extra)['state'],
        );

        $cancelled = $this->request();
        $cancelled['confirmation_state'] = 'cancelled';
        self::assertSame(
            DashboardWidgetFormActionExecutionAjaxHandler::STATE_CONFIRMATION_INVALID,
            $handler->handle($cancelled)['state'],
        );

        $stale = $this->request();
        $stale['definition_revision'] = 2;
        self::assertSame(
            DashboardWidgetFormActionExecutionAjaxHandler::STATE_STALE_DEFINITION,
            $handler->handle($stale)['state'],
        );

        self::assertSame(0, $ability->authorizationCalls);
        self::assertSame(0, $ability->executions);
        self::assertCount(0, $audit->records);
    }

    /**
     * @return array{
     *   DashboardWidgetFormActionExecutionAjaxHandler,
     *   ExecutionAbilityHandler,
     *   ExecutionAuditProbe
     * }
     */
    private function harness(
        mixed $ownerResult = null,
        ?PolicyDecision $ownerDecision = null,
        bool $throwOnExecute = false,
        ?int $failAuditAt = null,
    ): array {
        $definitions = new InMemoryDefinitionRepository();
        $definitions->save($this->dashboardDefinition());

        $capabilities = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return true;
            }
        };
        $abilities = new AbilityRegistry(new PolicyEngine($capabilities));
        $ability = new ExecutionAbilityHandler(
            $ownerDecision ?? PolicyDecision::allow('forms_workflows_set_enabled_authorized'),
            $ownerResult ?? [
                'definition_id' => self::FORMS_ID,
                'previous_revision' => 4,
                'revision' => 5,
                'status' => 'disabled',
                'enabled' => false,
                'changed' => true,
                'result_code' => 'status_changed',
            ],
            $throwOnExecute,
        );
        $abilities->register(
            new AbilityDescriptor(
                name: FormsWorkflowsModule::ABILITY_SET_ENABLED,
                ownerSurfaceId: 17,
                capability: 'manage_options',
                mutates: true,
                channels: [ExecutionChannel::Ui],
                inputSchema: $this->inputSchema(),
                outputSchema: [],
            ),
            $ability,
        );

        $abilityResolver = static function (string $name) use ($abilities): ?array {
            $descriptor = $abilities->descriptor($name);
            if ($descriptor === null) {
                return null;
            }

            return [
                'name' => $descriptor->name,
                'owner_surface_id' => $descriptor->ownerSurfaceId,
                'mutates' => $descriptor->mutates,
                'ui_allowed' => $descriptor->allows(ExecutionChannel::Ui),
                'input_schema' => $descriptor->inputSchema,
            ];
        };

        $contentClasses = new DashboardWidgetContentClassCompiler();
        $registrations = new DashboardWidgetRegistrationCompiler(
            new DashboardWidgetVisibilityCompiler(),
            $contentClasses,
            null,
            null,
            $abilityResolver,
        );
        $dynamicValues = new class implements DynamicValueResolverInterface {
            public function resolve(
                DynamicValueRequest $request,
                ExecutionContext $context,
            ): DynamicValueResult {
                throw new RuntimeException('Dynamic values are not expected in this literal-input fixture.');
            }
        };
        $binder = new DashboardWidgetActionInputBinder(
            new AbilityInputValidator(),
            $dynamicValues,
            $abilityResolver,
        );
        $authorization = new DashboardWidgetActionAuthorizationEvaluator($abilities);
        $contexts = new WordPressExecutionContextFactory(new ExecutionAbilityEnvironment());
        $audit = new ExecutionAuditProbe($failAuditAt);

        return [
            new DashboardWidgetFormActionExecutionAjaxHandler(
                $definitions,
                $contentClasses,
                $registrations,
                $binder,
                $authorization,
                $contexts,
                $audit,
                $abilities,
                new DashboardWidgetFormActionExecutionResultAdapter(),
            ),
            $ability,
            $audit,
        ];
    }

    /** @return array{definition_id:string,definition_revision:int,confirmation_state:string} */
    private function request(): array
    {
        return [
            'definition_id' => self::DASHBOARD_ID,
            'definition_revision' => 3,
            'confirmation_state' => 'accepted',
        ];
    }

    /** @return array<string,mixed> */
    private function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['definition_id', 'expected_revision', 'enabled'],
            'properties' => [
                'definition_id' => ['type' => 'string', 'minLength' => 36, 'maxLength' => 36],
                'expected_revision' => ['type' => 'integer', 'minimum' => 1],
                'enabled' => ['type' => 'boolean'],
            ],
            'additionalProperties' => false,
        ];
    }

    private function dashboardDefinition(): Definition
    {
        return new Definition(
            id: self::DASHBOARD_ID,
            slug: 'workflow-control',
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: [
                'widget' => [
                    'key' => 'workflow-control',
                    'title' => 'Workflow control',
                    'type' => 'form_action',
                    'context' => 'normal',
                    'priority' => 'default',
                    'network_dashboard' => false,
                    'action' => [
                        'ability_id' => FormsWorkflowsModule::ABILITY_SET_ENABLED,
                        'confirmation' => [
                            'title' => 'Disable workflow?',
                            'message' => 'This changes workflow availability.',
                            'confirm_label' => 'Disable',
                            'cancel_label' => 'Cancel',
                        ],
                        'input' => [
                            'definition_id' => ['source' => 'literal', 'value' => self::FORMS_ID],
                            'expected_revision' => ['source' => 'literal', 'value' => 4],
                            'enabled' => ['source' => 'literal', 'value' => false],
                        ],
                    ],
                ],
            ],
            revision: 3,
            dependencies: [],
        );
    }
}

final class ExecutionAbilityHandler implements InputAuthorizingAbilityHandlerInterface
{
    public int $authorizationCalls = 0;
    public int $executions = 0;

    /** @var array<string,mixed> */
    public array $lastInput = [];

    public function __construct(
        private readonly PolicyDecision $decision,
        private readonly mixed $result,
        private readonly bool $throwOnExecute,
    ) {}

    public function authorizeInput(array $input, ExecutionContext $context): PolicyDecision
    {
        ++$this->authorizationCalls;
        $this->lastInput = $input;

        return $this->decision;
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        ++$this->executions;
        $this->lastInput = $input;

        if ($this->throwOnExecute) {
            throw new RuntimeException('Synthetic owner execution failure with secret detail.');
        }

        return $this->result;
    }
}

final class ExecutionAuditProbe implements AuditLoggerInterface
{
    /** @var list<AuditRecord> */
    public array $records = [];

    private int $attempts = 0;

    public function __construct(private readonly ?int $failAt = null) {}

    public function record(AuditRecord $record): void
    {
        ++$this->attempts;
        if ($this->failAt !== null && $this->attempts === $this->failAt) {
            throw new RuntimeException('Synthetic audit persistence failure.');
        }

        $this->records[] = $record;
    }
}

final class ExecutionAbilityEnvironment implements WordPressAbilityEnvironmentInterface
{
    public function abilitiesApiAvailable(): bool { return false; }
    public function doingAction(string $hook): bool { return false; }
    public function currentUserId(): ?int { return 7; }
    public function currentSiteId(): int { return 11; }
    public function currentNetworkId(): ?int { return 13; }
    public function currentUserCan(string $capability): bool { return true; }
    public function isRestRequest(): bool { return false; }
    public function isCli(): bool { return false; }
    public function registerCategory(string $slug, array $args): bool { return false; }
    public function registerAbility(string $name, array $args): bool { return false; }
}
