<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\AuditLoggerInterface;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Contracts\InputAuthorizingAbilityHandlerInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetActionAuthorizationEvaluator;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetActionInputBinder;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetFormActionPreflightAjaxHandler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityCompiler;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Abilities\InputValidation\AbilityInputValidator;
use WPEssential\Platform\Audit\AuditRecord;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyDecision;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\DynamicValues\DynamicValueRequest;
use WPEssential\Platform\DynamicValues\DynamicValueResult;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

final class DashboardWidgetFormActionPreflightAjaxHandlerTest extends TestCase
{
    private const DASHBOARD_ID = '11111111-1111-4111-8111-111111111111';
    private const FORMS_ID = '22222222-2222-4222-8222-222222222222';
    private const ABILITY_ID = 'wpessential/forms-workflows/set-enabled';

    public function testAcceptedPreflightBindsAuthorizesAuditsAndNeverExecutes(): void
    {
        [$handler, $abilityHandler, $audit] = $this->harness();

        $result = $handler->handle($this->request('accepted'));

        self::assertSame(
            DashboardWidgetFormActionPreflightAjaxHandler::STATE_CONFIRMATION_READY,
            $result['state'],
        );
        self::assertSame(1, $abilityHandler->authorizationCalls);
        self::assertSame(0, $abilityHandler->executions);
        self::assertSame([
            'definition_id' => self::FORMS_ID,
            'enabled' => false,
            'expected_revision' => 4,
        ], $abilityHandler->lastInput);
        self::assertCount(2, $audit->records);
        self::assertSame('dashboard-widgets/action.authorization', $audit->records[0]->action);
        self::assertSame('dashboard-widgets/action.confirmation', $audit->records[1]->action);
        self::assertSame('confirmation_ready', $audit->records[1]->metadata['result_code']);
    }

    public function testCancellationAuditsWithoutAuthorizingOrExecuting(): void
    {
        [$handler, $abilityHandler, $audit] = $this->harness();

        $result = $handler->handle($this->request('cancelled'));

        self::assertSame(
            DashboardWidgetFormActionPreflightAjaxHandler::STATE_CONFIRMATION_CANCELLED,
            $result['state'],
        );
        self::assertSame(0, $abilityHandler->authorizationCalls);
        self::assertSame(0, $abilityHandler->executions);
        self::assertCount(1, $audit->records);
        self::assertSame('dashboard-widgets/action.confirmation', $audit->records[0]->action);
        self::assertSame('cancelled', $audit->records[0]->metadata['confirmation_state']);
    }

    public function testMalformedExtraAndStaleRequestsFailClosed(): void
    {
        [$handler, $abilityHandler, $audit] = $this->harness();

        $extra = $this->request('accepted');
        $extra['ability_id'] = self::ABILITY_ID;
        self::assertSame(
            DashboardWidgetFormActionPreflightAjaxHandler::STATE_CONFIRMATION_INVALID,
            $handler->handle($extra)['state'],
        );

        $stale = $this->request('accepted');
        $stale['definition_revision'] = 2;
        self::assertSame(
            DashboardWidgetFormActionPreflightAjaxHandler::STATE_STALE_DEFINITION,
            $handler->handle($stale)['state'],
        );

        self::assertSame(0, $abilityHandler->authorizationCalls);
        self::assertSame(0, $abilityHandler->executions);
        self::assertCount(0, $audit->records);
    }

    public function testOwnerAuthorizationDenialIsAuditedAndNeverConfirmed(): void
    {
        [$handler, $abilityHandler, $audit] = $this->harness(
            ownerDecision: PolicyDecision::deny('forms_workflows_set_enabled_revision_conflict'),
        );

        $result = $handler->handle($this->request('accepted'));

        self::assertSame(
            DashboardWidgetFormActionPreflightAjaxHandler::STATE_AUTHORIZATION_DENIED,
            $result['state'],
        );
        self::assertSame(1, $abilityHandler->authorizationCalls);
        self::assertSame(0, $abilityHandler->executions);
        self::assertCount(1, $audit->records);
        self::assertSame('authorization_denied', $audit->records[0]->metadata['result_code']);
        self::assertSame(
            'forms_workflows_set_enabled_revision_conflict',
            $audit->records[0]->reason,
        );
    }

    public function testRequiredAuditFailurePreventsConfirmationReady(): void
    {
        [$handler, $abilityHandler, $audit] = $this->harness(failAuditAt: 1);

        $result = $handler->handle($this->request('accepted'));

        self::assertSame(
            DashboardWidgetFormActionPreflightAjaxHandler::STATE_RUNTIME_FAILURE,
            $result['state'],
        );
        self::assertSame(1, $abilityHandler->authorizationCalls);
        self::assertSame(0, $abilityHandler->executions);
        self::assertCount(0, $audit->records);
    }

    public function testUnauthenticatedContextFailsClosedWithoutEvidence(): void
    {
        [$handler, $abilityHandler, $audit] = $this->harness(userId: null);

        $result = $handler->handle($this->request('accepted'));

        self::assertSame(
            DashboardWidgetFormActionPreflightAjaxHandler::STATE_AUTHORIZATION_DENIED,
            $result['state'],
        );
        self::assertSame(0, $abilityHandler->authorizationCalls);
        self::assertSame(0, $abilityHandler->executions);
        self::assertCount(0, $audit->records);
    }

    /**
     * @return array{
     *   DashboardWidgetFormActionPreflightAjaxHandler,
     *   PreflightAbilityHandler,
     *   PreflightAuditProbe
     * }
     */
    private function harness(
        ?PolicyDecision $ownerDecision = null,
        ?int $userId = 7,
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
        $abilityHandler = new PreflightAbilityHandler(
            $ownerDecision ?? PolicyDecision::allow('forms_workflows_set_enabled_authorized'),
        );
        $abilities->register(
            new AbilityDescriptor(
                name: self::ABILITY_ID,
                ownerSurfaceId: 17,
                capability: 'manage_options',
                mutates: true,
                channels: [ExecutionChannel::Ui],
                inputSchema: $this->inputSchema(),
                outputSchema: [],
            ),
            $abilityHandler,
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
        $environment = new PreflightAbilityEnvironment($userId);
        $contexts = new WordPressExecutionContextFactory($environment);
        $audit = new PreflightAuditProbe($failAuditAt);

        return [
            new DashboardWidgetFormActionPreflightAjaxHandler(
                $definitions,
                $contentClasses,
                $registrations,
                $binder,
                $authorization,
                $contexts,
                $audit,
            ),
            $abilityHandler,
            $audit,
        ];
    }

    /** @return array{definition_id:string,definition_revision:int,confirmation_state:string} */
    private function request(string $state): array
    {
        return [
            'definition_id' => self::DASHBOARD_ID,
            'definition_revision' => 3,
            'confirmation_state' => $state,
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
                        'ability_id' => self::ABILITY_ID,
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

final class PreflightAbilityHandler implements InputAuthorizingAbilityHandlerInterface
{
    public int $authorizationCalls = 0;
    public int $executions = 0;

    /** @var array<string,mixed> */
    public array $lastInput = [];

    public function __construct(private readonly PolicyDecision $decision) {}

    public function authorizeInput(array $input, ExecutionContext $context): PolicyDecision
    {
        ++$this->authorizationCalls;
        $this->lastInput = $input;

        return $this->decision;
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        ++$this->executions;

        return ['unexpected' => true];
    }
}

final class PreflightAuditProbe implements AuditLoggerInterface
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

final class PreflightAbilityEnvironment implements WordPressAbilityEnvironmentInterface
{
    public function __construct(private readonly ?int $userId) {}

    public function abilitiesApiAvailable(): bool { return false; }
    public function doingAction(string $hook): bool { return false; }
    public function currentUserId(): ?int { return $this->userId; }
    public function currentSiteId(): int { return 11; }
    public function currentNetworkId(): ?int { return 13; }
    public function currentUserCan(string $capability): bool { return true; }
    public function isRestRequest(): bool { return false; }
    public function isCli(): bool { return false; }
    public function registerCategory(string $slug, array $args): bool { return false; }
    public function registerAbility(string $name, array $args): bool { return false; }
}
