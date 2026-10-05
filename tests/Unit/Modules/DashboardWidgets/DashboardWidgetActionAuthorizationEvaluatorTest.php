<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\InputAuthorizingAbilityHandlerInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetActionAuthorizationEvaluator;
use WPEssential\Modules\FormsWorkflows\FormWorkflowDefinition;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyDecision;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;

final class DashboardWidgetActionAuthorizationEvaluatorTest extends TestCase
{
    private const ABILITY_ID = 'wpessential/forms-workflows/set-enabled';

    public function testZeroInputDescriptorWithEmptyInputReachesCanonicalAllowWithoutExecution(): void
    {
        $registry = $this->registry(true);
        $handler = new DashboardAuthorizationExecutionTrackingHandler();
        $registry->register($this->descriptor(), $handler);

        $decision = $this->evaluator($registry)
            ->authorize(self::ABILITY_ID, [], $this->uiContext());

        self::assertTrue($decision->allowed);
        self::assertSame('capability_allowed', $decision->reason);
        self::assertSame(0, $handler->executions);
    }

    public function testZeroInputDescriptorRejectsNonEmptyInputBeforeCanonicalAuthorization(): void
    {
        $registry = $this->registry(true);
        $handler = new DashboardAuthorizationExecutionTrackingHandler();
        $registry->register($this->descriptor(), $handler);

        $decision = $this->evaluator($registry)
            ->authorize(self::ABILITY_ID, ['unexpected' => true], $this->uiContext());

        self::assertFalse($decision->allowed);
        self::assertSame(
            DashboardWidgetActionAuthorizationEvaluator::REASON_INVALID_INPUT,
            $decision->reason,
        );
        self::assertSame(0, $handler->executions);
    }

    public function testValidNonEmptySchemaAndInputReachOwnerAuthorizationWithoutExecution(): void
    {
        $registry = $this->registry(true);
        $handler = new DashboardInputAuthorizingTrackingHandler(
            PolicyDecision::allow('forms_workflows_set_enabled_authorized'),
        );
        $registry->register(
            $this->descriptor(['inputSchema' => $this->setEnabledSchema()]),
            $handler,
        );
        $input = $this->validInput();

        $decision = $this->evaluator($registry)
            ->authorize(self::ABILITY_ID, $input, $this->uiContext());

        self::assertTrue($decision->allowed);
        self::assertSame('forms_workflows_set_enabled_authorized', $decision->reason);
        self::assertSame(1, $handler->authorizationCalls);
        self::assertSame($input, $handler->lastInput);
        self::assertSame(0, $handler->executions);
    }

    public function testMalformedCurrentSchemaFailsClosedAsInvalidInput(): void
    {
        $registry = $this->registry(true);
        $handler = new DashboardInputAuthorizingTrackingHandler(PolicyDecision::allow());
        $registry->register(
            $this->descriptor([
                'inputSchema' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ]),
            $handler,
        );

        $decision = $this->evaluator($registry)
            ->authorize(self::ABILITY_ID, [], $this->uiContext());

        self::assertFalse($decision->allowed);
        self::assertSame(
            DashboardWidgetActionAuthorizationEvaluator::REASON_INVALID_INPUT,
            $decision->reason,
        );
        self::assertSame(0, $handler->authorizationCalls);
        self::assertSame(0, $handler->executions);
    }

    public function testInvalidBoundInputsFailClosedBeforeOwnerAuthorization(): void
    {
        $cases = [
            'missing required property' => [
                'definition_id' => '11111111-1111-4111-8111-111111111111',
                'expected_revision' => 1,
            ],
            'unknown property' => [
                'definition_id' => '11111111-1111-4111-8111-111111111111',
                'expected_revision' => 1,
                'enabled' => false,
                'unexpected' => true,
            ],
            'wrong scalar type' => [
                'definition_id' => '11111111-1111-4111-8111-111111111111',
                'expected_revision' => '1',
                'enabled' => false,
            ],
            'numeric bound violation' => [
                'definition_id' => '11111111-1111-4111-8111-111111111111',
                'expected_revision' => 0,
                'enabled' => false,
            ],
        ];

        foreach ($cases as $label => $input) {
            $registry = $this->registry(true);
            $handler = new DashboardInputAuthorizingTrackingHandler(PolicyDecision::allow());
            $registry->register(
                $this->descriptor(['inputSchema' => $this->setEnabledSchema()]),
                $handler,
            );

            $decision = $this->evaluator($registry)
                ->authorize(self::ABILITY_ID, $input, $this->uiContext());

            self::assertFalse($decision->allowed, $label);
            self::assertSame(
                DashboardWidgetActionAuthorizationEvaluator::REASON_INVALID_INPUT,
                $decision->reason,
                $label,
            );
            self::assertSame(0, $handler->authorizationCalls, $label);
            self::assertSame(0, $handler->executions, $label);
        }
    }

    public function testCanonicalCapabilityDenialReasonIsPreserved(): void
    {
        $registry = $this->registry(false);
        $handler = new DashboardInputAuthorizingTrackingHandler(PolicyDecision::allow());
        $registry->register(
            $this->descriptor(['inputSchema' => $this->setEnabledSchema()]),
            $handler,
        );

        $decision = $this->evaluator($registry)
            ->authorize(self::ABILITY_ID, $this->validInput(), $this->uiContext());

        self::assertFalse($decision->allowed);
        self::assertSame('capability_denied', $decision->reason);
        self::assertSame(0, $handler->authorizationCalls);
        self::assertSame(0, $handler->executions);
    }

    public function testCanonicalOwnerInputDenialReasonIsPreserved(): void
    {
        $registry = $this->registry(true);
        $handler = new DashboardInputAuthorizingTrackingHandler(
            PolicyDecision::deny('forms_workflows_set_enabled_wrong_owner'),
        );
        $registry->register(
            $this->descriptor(['inputSchema' => $this->setEnabledSchema()]),
            $handler,
        );

        $decision = $this->evaluator($registry)
            ->authorize(self::ABILITY_ID, $this->validInput(), $this->uiContext());

        self::assertFalse($decision->allowed);
        self::assertSame('forms_workflows_set_enabled_wrong_owner', $decision->reason);
        self::assertSame(1, $handler->authorizationCalls);
        self::assertSame(0, $handler->executions);
    }

    public function testFormsStyleRevisionConflictReasonIsPreserved(): void
    {
        $registry = $this->registry(true);
        $handler = new DashboardInputAuthorizingTrackingHandler(
            PolicyDecision::deny('forms_workflows_set_enabled_revision_conflict'),
        );
        $registry->register(
            $this->descriptor(['inputSchema' => $this->setEnabledSchema()]),
            $handler,
        );

        $decision = $this->evaluator($registry)
            ->authorize(self::ABILITY_ID, $this->validInput(), $this->uiContext());

        self::assertFalse($decision->allowed);
        self::assertSame('forms_workflows_set_enabled_revision_conflict', $decision->reason);
        self::assertSame(1, $handler->authorizationCalls);
        self::assertSame(0, $handler->executions);
    }

    public function testRejectsMalformedMissingWrongOwnerReadonlyAndNonUiAbilities(): void
    {
        $emptyRegistry = $this->registry(true);
        $evaluator = $this->evaluator($emptyRegistry);

        self::assertSame(
            DashboardWidgetActionAuthorizationEvaluator::REASON_INVALID_ABILITY,
            $evaluator->authorize('bad ability', [], $this->uiContext())->reason,
        );
        self::assertSame(
            DashboardWidgetActionAuthorizationEvaluator::REASON_INVALID_ABILITY,
            $evaluator->authorize(self::ABILITY_ID, [], $this->uiContext())->reason,
        );

        foreach ([
            'wrong owner' => ['ownerSurfaceId' => 10],
            'read only' => ['mutates' => false],
            'non ui' => ['channels' => [ExecutionChannel::Internal]],
        ] as $label => $overrides) {
            $registry = $this->registry(true);
            $handler = new DashboardAuthorizationExecutionTrackingHandler();
            $registry->register($this->descriptor($overrides), $handler);

            $decision = $this->evaluator($registry)
                ->authorize(self::ABILITY_ID, [], $this->uiContext());

            self::assertFalse($decision->allowed, $label);
            self::assertSame(
                DashboardWidgetActionAuthorizationEvaluator::REASON_INVALID_ABILITY,
                $decision->reason,
                $label,
            );
            self::assertSame(0, $handler->executions, $label);
        }
    }

    public function testRejectsUnauthenticatedServiceRestAndInternalContexts(): void
    {
        $contexts = [
            'unauthenticated' => new ExecutionContext(
                new Principal(null),
                1,
                ExecutionChannel::Ui,
            ),
            'service actor' => new ExecutionContext(
                new Principal(1, 'service'),
                1,
                ExecutionChannel::Ui,
            ),
            'rest' => new ExecutionContext(
                new Principal(1),
                1,
                ExecutionChannel::Rest,
            ),
            'internal' => new ExecutionContext(
                new Principal(1),
                1,
                ExecutionChannel::Internal,
            ),
        ];

        foreach ($contexts as $label => $context) {
            $registry = $this->registry(true);
            $handler = new DashboardInputAuthorizingTrackingHandler(PolicyDecision::allow());
            $registry->register(
                $this->descriptor(['inputSchema' => $this->setEnabledSchema()]),
                $handler,
            );

            $decision = $this->evaluator($registry)
                ->authorize(self::ABILITY_ID, $this->validInput(), $context);

            self::assertFalse($decision->allowed, $label);
            self::assertSame(
                DashboardWidgetActionAuthorizationEvaluator::REASON_INVALID_CONTEXT,
                $decision->reason,
                $label,
            );
            self::assertSame(0, $handler->authorizationCalls, $label);
            self::assertSame(0, $handler->executions, $label);
        }
    }

    public function testFailsClosedWhenOwnerAuthorizationThrowsWithoutExecution(): void
    {
        $registry = $this->registry(true);
        $handler = new DashboardInputAuthorizingTrackingHandler(
            PolicyDecision::allow(),
            true,
        );
        $registry->register(
            $this->descriptor(['inputSchema' => $this->setEnabledSchema()]),
            $handler,
        );

        $decision = $this->evaluator($registry)
            ->authorize(self::ABILITY_ID, $this->validInput(), $this->uiContext());

        self::assertFalse($decision->allowed);
        self::assertSame(
            DashboardWidgetActionAuthorizationEvaluator::REASON_AUTHORIZATION_FAILURE,
            $decision->reason,
        );
        self::assertSame(1, $handler->authorizationCalls);
        self::assertSame(0, $handler->executions);
    }

    private function evaluator(AbilityRegistry $registry): DashboardWidgetActionAuthorizationEvaluator
    {
        return new DashboardWidgetActionAuthorizationEvaluator($registry);
    }

    private function registry(bool $capabilityAllowed): AbilityRegistry
    {
        $checker = new class($capabilityAllowed) implements CapabilityCheckerInterface {
            public function __construct(private readonly bool $allowed) {}

            public function can(ExecutionContext $context, string $capability): bool
            {
                return $this->allowed;
            }
        };

        return new AbilityRegistry(new PolicyEngine($checker));
    }

    /** @param array<string,mixed> $overrides */
    private function descriptor(array $overrides = []): AbilityDescriptor
    {
        return new AbilityDescriptor(
            name: self::ABILITY_ID,
            ownerSurfaceId: $overrides['ownerSurfaceId'] ?? FormWorkflowDefinition::OWNER_SURFACE_ID,
            capability: 'manage_options',
            mutates: $overrides['mutates'] ?? true,
            channels: $overrides['channels'] ?? [ExecutionChannel::Ui],
            inputSchema: $overrides['inputSchema'] ?? [],
            outputSchema: [],
        );
    }

    /** @return array<string,mixed> */
    private function setEnabledSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['definition_id', 'expected_revision', 'enabled'],
            'properties' => [
                'definition_id' => [
                    'type' => 'string',
                    'minLength' => 36,
                    'maxLength' => 36,
                ],
                'expected_revision' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
                'enabled' => [
                    'type' => 'boolean',
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    /** @return array{definition_id:string,expected_revision:int,enabled:bool} */
    private function validInput(): array
    {
        return [
            'definition_id' => '11111111-1111-4111-8111-111111111111',
            'expected_revision' => 1,
            'enabled' => false,
        ];
    }

    private function uiContext(): ExecutionContext
    {
        return new ExecutionContext(
            new Principal(1),
            1,
            ExecutionChannel::Ui,
        );
    }
}

final class DashboardAuthorizationExecutionTrackingHandler implements AbilityHandlerInterface
{
    public int $executions = 0;

    public function handle(array $input, ExecutionContext $context): mixed
    {
        ++$this->executions;

        return ['ok' => true];
    }
}

final class DashboardInputAuthorizingTrackingHandler implements InputAuthorizingAbilityHandlerInterface
{
    public int $authorizationCalls = 0;
    public int $executions = 0;

    /** @var array<string,mixed> */
    public array $lastInput = [];

    public function __construct(
        private readonly PolicyDecision $decision,
        private readonly bool $throwOnAuthorization = false,
    ) {}

    public function authorizeInput(array $input, ExecutionContext $context): PolicyDecision
    {
        ++$this->authorizationCalls;
        $this->lastInput = $input;

        if ($this->throwOnAuthorization) {
            throw new RuntimeException('authorization backend unavailable');
        }

        return $this->decision;
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        ++$this->executions;

        return ['ok' => true];
    }
}
