<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
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
    private const ABILITY_ID = 'wpessential/forms-workflows/submit';

    public function testAllowsCanonicalFormsActionWhenPolicyAllows(): void
    {
        $registry = $this->registry(true);
        $this->registerAbility($registry);

        $decision = (new DashboardWidgetActionAuthorizationEvaluator($registry))
            ->authorize(self::ABILITY_ID, $this->uiContext());

        self::assertTrue($decision->allowed);
    }

    public function testReturnsCanonicalPolicyDenialWithoutExecutingAbility(): void
    {
        $registry = $this->registry(false);
        $executions = 0;
        $registry->register(
            $this->descriptor(),
            new class($executions) implements AbilityHandlerInterface {
                public function __construct(private int &$executions) {}

                public function handle(array $input, ExecutionContext $context): mixed
                {
                    ++$this->executions;
                    return ['ok' => true];
                }
            },
        );

        $decision = (new DashboardWidgetActionAuthorizationEvaluator($registry))
            ->authorize(self::ABILITY_ID, $this->uiContext());

        self::assertFalse($decision->allowed);
        self::assertSame(0, $executions);
    }

    public function testRejectsMalformedOrUnsafeAbilityDescriptors(): void
    {
        $emptyRegistry = $this->registry(true);
        $evaluator = new DashboardWidgetActionAuthorizationEvaluator($emptyRegistry);

        self::assertSame(
            DashboardWidgetActionAuthorizationEvaluator::REASON_INVALID_ABILITY,
            $evaluator->authorize('bad ability', $this->uiContext())->reason,
        );
        self::assertSame(
            DashboardWidgetActionAuthorizationEvaluator::REASON_INVALID_ABILITY,
            $evaluator->authorize(self::ABILITY_ID, $this->uiContext())->reason,
        );

        foreach ([
            ['ownerSurfaceId' => 10],
            ['mutates' => false],
            ['channels' => [ExecutionChannel::Internal]],
            ['inputSchema' => ['type' => 'object']],
        ] as $overrides) {
            $registry = $this->registry(true);
            $this->registerAbility($registry, $overrides);

            $decision = (new DashboardWidgetActionAuthorizationEvaluator($registry))
                ->authorize(self::ABILITY_ID, $this->uiContext());

            self::assertFalse($decision->allowed);
            self::assertSame(
                DashboardWidgetActionAuthorizationEvaluator::REASON_INVALID_ABILITY,
                $decision->reason,
            );
        }
    }

    public function testRejectsUnauthenticatedNonUserAndNonUiContexts(): void
    {
        $registry = $this->registry(true);
        $this->registerAbility($registry);
        $evaluator = new DashboardWidgetActionAuthorizationEvaluator($registry);

        foreach ([
            new ExecutionContext(new Principal(null), 1, ExecutionChannel::Ui),
            new ExecutionContext(new Principal(1, 'service'), 1, ExecutionChannel::Ui),
            new ExecutionContext(new Principal(1), 1, ExecutionChannel::Rest),
        ] as $context) {
            $decision = $evaluator->authorize(self::ABILITY_ID, $context);
            self::assertFalse($decision->allowed);
            self::assertSame(
                DashboardWidgetActionAuthorizationEvaluator::REASON_INVALID_CONTEXT,
                $decision->reason,
            );
        }
    }

    public function testFailsClosedWhenCanonicalInputAuthorizationThrows(): void
    {
        $registry = $this->registry(true);
        $registry->register(
            $this->descriptor(),
            new class implements InputAuthorizingAbilityHandlerInterface {
                public function authorizeInput(array $input, ExecutionContext $context): PolicyDecision
                {
                    throw new \RuntimeException('authorization backend unavailable');
                }

                public function handle(array $input, ExecutionContext $context): mixed
                {
                    self::fail('Action handler must never execute during authorization evaluation.');
                }
            },
        );

        $decision = (new DashboardWidgetActionAuthorizationEvaluator($registry))
            ->authorize(self::ABILITY_ID, $this->uiContext());

        self::assertFalse($decision->allowed);
        self::assertSame(
            DashboardWidgetActionAuthorizationEvaluator::REASON_AUTHORIZATION_FAILURE,
            $decision->reason,
        );
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
    private function registerAbility(AbilityRegistry $registry, array $overrides = []): void
    {
        $registry->register(
            $this->descriptor($overrides),
            new class implements AbilityHandlerInterface {
                public function handle(array $input, ExecutionContext $context): mixed
                {
                    return ['ok' => true];
                }
            },
        );
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

    private function uiContext(): ExecutionContext
    {
        return new ExecutionContext(
            new Principal(1),
            1,
            ExecutionChannel::Ui,
        );
    }
}
