<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Readiness\Authorization\Policy;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\ExecutionActorType;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\Policy\MigrationExecutionAuthorizationPolicyAdapter;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;

final class MigrationExecutionAuthorizationPolicyAdapterTest extends TestCase
{
    public function testCanonicalPolicyDecisionPopulatesCapabilityFact(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return $context->principal->userId === 7 && $capability === 'manage_options';
            }
        };

        $request = (new MigrationExecutionAuthorizationPolicyAdapter(new PolicyEngine($checker)))->request(
            new ExecutionContext(new Principal(7), 1),
            '11111111-1111-4111-8111-111111111111',
            4,
            true,
            MigrationRisk::R2,
        );

        self::assertTrue($request->capabilityAllowed);
        self::assertSame(ExecutionActorType::User, $request->actorType);
        self::assertSame(7, $request->actorUserId);
    }

    public function testDeniedCapabilityFailsClosedInProducedFacts(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return false;
            }
        };

        $request = (new MigrationExecutionAuthorizationPolicyAdapter(new PolicyEngine($checker)))->request(
            new ExecutionContext(new Principal(9), 1),
            '11111111-1111-4111-8111-111111111111',
            2,
            false,
            MigrationRisk::R1,
        );

        self::assertFalse($request->capabilityAllowed);
        self::assertFalse($request->confirmationProvided);
    }

    public function testUnauthenticatedPrincipalCannotBecomeAllowedUserActor(): void
    {
        $checker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return true;
            }
        };

        $request = (new MigrationExecutionAuthorizationPolicyAdapter(new PolicyEngine($checker)))->request(
            new ExecutionContext(new Principal(null, 'system'), 1),
            '11111111-1111-4111-8111-111111111111',
            1,
            true,
            MigrationRisk::R1,
        );

        self::assertFalse($request->capabilityAllowed);
        self::assertSame(ExecutionActorType::System, $request->actorType);
        self::assertNull($request->actorUserId);
    }
}
