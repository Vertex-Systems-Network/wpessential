<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRoleMembershipProviderInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityDecision;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityDescriptor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityEvaluator;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

final class DashboardWidgetVisibilityTestCapabilityChecker implements CapabilityCheckerInterface
{
    /** @param list<string> $allowed */
    public function __construct(private array $allowed) {}

    public function can(ExecutionContext $context, string $capability): bool
    {
        return in_array($capability, $this->allowed, true);
    }
}

final class DashboardWidgetVisibilityTestRoleProvider implements DashboardWidgetRoleMembershipProviderInterface
{
    /** @param list<string> $currentRoles */
    public function __construct(
        private bool $current,
        private array $currentRoles,
    ) {}

    public function isCurrentUserContext(ExecutionContext $context): bool
    {
        return $this->current;
    }

    public function hasAnyRole(ExecutionContext $context, array $roles): bool
    {
        foreach ($roles as $role) {
            if (in_array($role, $this->currentRoles, true)) {
                return true;
            }
        }

        return false;
    }
}

final class DashboardWidgetVisibilityEvaluatorTest extends TestCase
{
    public function testAllowsWhenEveryPopulatedDimensionMatchesAndAnyValueWithinEachMatches(): void
    {
        $decision = $this->evaluator(
            currentRoles: ['editor'],
            allowedCapabilities: ['manage_options'],
        )->evaluate(
            $this->descriptor(
                roles: ['administrator', 'editor'],
                capabilities: ['read', 'manage_options'],
                users: [3, 7],
            ),
            $this->context(),
        );

        self::assertTrue($decision->allowed);
        self::assertSame(DashboardWidgetVisibilityDecision::REASON_ALLOWED, $decision->reason);
    }

    public function testEmptyPolicyIsUnconstrainedOnlyForCurrentAuthenticatedUserContext(): void
    {
        $decision = $this->evaluator()->evaluate($this->descriptor(), $this->context());

        self::assertTrue($decision->allowed);
        self::assertSame(DashboardWidgetVisibilityDecision::REASON_ALLOWED, $decision->reason);
    }

    public function testAnonymousActorDenies(): void
    {
        $decision = $this->evaluator()->evaluate(
            $this->descriptor(),
            new ExecutionContext(new Principal(null), 3),
        );

        self::assertFalse($decision->allowed);
        self::assertSame(DashboardWidgetVisibilityDecision::REASON_UNAUTHENTICATED, $decision->reason);
    }

    public function testNonUserActorDenies(): void
    {
        $decision = $this->evaluator()->evaluate(
            $this->descriptor(),
            new ExecutionContext(new Principal(7, 'service'), 3),
        );

        self::assertFalse($decision->allowed);
        self::assertSame(DashboardWidgetVisibilityDecision::REASON_NON_USER_ACTOR, $decision->reason);
    }

    public function testCurrentWordPressContextMismatchDenies(): void
    {
        $decision = $this->evaluator(current: false)->evaluate(
            $this->descriptor(),
            $this->context(),
        );

        self::assertFalse($decision->allowed);
        self::assertSame(DashboardWidgetVisibilityDecision::REASON_CONTEXT_MISMATCH, $decision->reason);
    }

    public function testRoleMismatchDenies(): void
    {
        $decision = $this->evaluator(currentRoles: ['subscriber'])->evaluate(
            $this->descriptor(roles: ['editor']),
            $this->context(),
        );

        self::assertFalse($decision->allowed);
        self::assertSame(DashboardWidgetVisibilityDecision::REASON_ROLE_MISMATCH, $decision->reason);
    }

    public function testCapabilityMismatchDenies(): void
    {
        $decision = $this->evaluator(allowedCapabilities: ['read'])->evaluate(
            $this->descriptor(capabilities: ['manage_options']),
            $this->context(),
        );

        self::assertFalse($decision->allowed);
        self::assertSame(DashboardWidgetVisibilityDecision::REASON_CAPABILITY_MISMATCH, $decision->reason);
    }

    public function testUserMismatchDenies(): void
    {
        $decision = $this->evaluator()->evaluate(
            $this->descriptor(users: [8, 9]),
            $this->context(),
        );

        self::assertFalse($decision->allowed);
        self::assertSame(DashboardWidgetVisibilityDecision::REASON_USER_MISMATCH, $decision->reason);
    }

    /**
     * @param list<string> $currentRoles
     * @param list<string> $allowedCapabilities
     */
    private function evaluator(
        bool $current = true,
        array $currentRoles = [],
        array $allowedCapabilities = [],
    ): DashboardWidgetVisibilityEvaluator {
        return new DashboardWidgetVisibilityEvaluator(
            new DashboardWidgetVisibilityTestCapabilityChecker($allowedCapabilities),
            new DashboardWidgetVisibilityTestRoleProvider($current, $currentRoles),
        );
    }

    /**
     * @param list<string> $roles
     * @param list<string> $capabilities
     * @param list<int> $users
     */
    private function descriptor(
        array $roles = [],
        array $capabilities = [],
        array $users = [],
    ): DashboardWidgetVisibilityDescriptor {
        return new DashboardWidgetVisibilityDescriptor(
            definitionId: '11111111-1111-4111-8111-111111111111',
            revision: 1,
            roles: $roles,
            capabilities: $capabilities,
            users: $users,
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 3);
    }
}
