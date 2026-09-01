<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\InputAuthorizingAbilityHandlerInterface;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyDecision;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;

final class AbilityRegistryInputAuthorizationTest extends TestCase
{
    public function testOptInInputAuthorizationDenialBlocksHandlerExecution(): void
    {
        $handler = new class implements InputAuthorizingAbilityHandlerInterface {
            public int $executions = 0;

            public function authorizeInput(array $input, ExecutionContext $context): PolicyDecision
            {
                unset($context);
                return ($input['post_id'] ?? null) === 7
                    ? PolicyDecision::allow('resource_allowed')
                    : PolicyDecision::deny('resource_denied');
            }

            public function handle(array $input, ExecutionContext $context): mixed
            {
                unset($input, $context);
                $this->executions++;
                return ['ok' => true];
            }
        };

        $registry = $this->registry();
        $registry->register($this->descriptor(), $handler);

        $denied = $registry->authorize('wpessential/test/resource', $this->context(), ['post_id' => 8]);
        self::assertFalse($denied->allowed);
        self::assertSame('resource_denied', $denied->reason);

        try {
            $registry->execute('wpessential/test/resource', ['post_id' => 8], $this->context());
            self::fail('Input authorization denial must block Ability execution.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('resource_denied', $error->getMessage());
        }
        self::assertSame(0, $handler->executions);

        self::assertSame(['ok' => true], $registry->execute(
            'wpessential/test/resource',
            ['post_id' => 7],
            $this->context(),
        ));
        self::assertSame(1, $handler->executions);
    }

    public function testLegacyHandlerRetainsCapabilityOnlyAuthorization(): void
    {
        $handler = new class implements AbilityHandlerInterface {
            public function handle(array $input, ExecutionContext $context): mixed
            {
                unset($context);
                return $input;
            }
        };

        $registry = $this->registry();
        $registry->register($this->descriptor(), $handler);

        $decision = $registry->authorize('wpessential/test/resource', $this->context(), ['ignored' => true]);
        self::assertTrue($decision->allowed);
        self::assertSame(['ignored' => true], $registry->execute(
            'wpessential/test/resource',
            ['ignored' => true],
            $this->context(),
        ));
    }

    private function registry(): AbilityRegistry
    {
        $capabilities = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                unset($context, $capability);
                return true;
            }
        };
        return new AbilityRegistry(new PolicyEngine($capabilities));
    }

    private function descriptor(): AbilityDescriptor
    {
        return new AbilityDescriptor(
            name: 'wpessential/test/resource',
            ownerSurfaceId: 3,
            capability: 'read',
            mutates: false,
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}
