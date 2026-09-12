<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Roles;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\Roles\RoleRuntimeEnvironmentInterface;
use WPEssential\Modules\Roles\RolesReadService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;

final class RolesReadServiceTest extends TestCase
{
    public function testCatalogAndImpactPreserveExplicitAllowDenyAndAbsence(): void
    {
        $environment = new FakeRoleRuntimeEnvironment(
            roles: [
                'administrator' => [
                    'name' => 'Administrator',
                    'capabilities' => [
                        'delete_books' => false,
                        'edit_books' => true,
                        'manage_options' => true,
                    ],
                ],
                'editor' => [
                    'name' => 'Editor',
                    'capabilities' => ['edit_books' => true],
                ],
                'subscriber' => [
                    'name' => 'Subscriber',
                    'capabilities' => ['read' => true],
                ],
            ],
            networkId: 2,
            multisite: true,
        );
        $service = $this->service($environment, true);
        $context = new ExecutionContext(new Principal(11), 7, networkId: 2);

        $catalog = $service->roleCatalog($context);
        self::assertSame('healthy', $catalog['state']);
        self::assertTrue($catalog['scope_context']['super_admin_special_authority']);
        self::assertSame(false, $catalog['roles'][0]['capabilities']['delete_books']);
        self::assertSame(true, $catalog['roles'][0]['capabilities']['edit_books']);
        self::assertArrayNotHasKey('delete_books', $catalog['roles'][1]['capabilities']);
        self::assertSame('unknown', $catalog['roles'][0]['provenance']);

        $editImpact = $service->capabilityImpact('edit_books', $context);
        self::assertSame(['administrator', 'editor'], $editImpact['explicit_allow_roles']);
        self::assertSame([], $editImpact['explicit_deny_roles']);
        self::assertSame(['subscriber'], $editImpact['absent_roles']);

        $deleteImpact = $service->capabilityImpact('delete_books', $context);
        self::assertSame([], $deleteImpact['explicit_allow_roles']);
        self::assertSame(['administrator'], $deleteImpact['explicit_deny_roles']);
        self::assertSame(['editor', 'subscriber'], $deleteImpact['absent_roles']);
        self::assertSame('primitive_or_provider', $deleteImpact['classification']);
    }

    public function testContextualMetaCapabilityIsMarkedAsContextRequired(): void
    {
        $environment = new FakeRoleRuntimeEnvironment(
            roles: [
                'administrator' => [
                    'name' => 'Administrator',
                    'capabilities' => ['edit_posts' => true],
                ],
            ],
        );
        $impact = $this->service($environment, true)->capabilityImpact(
            'edit_post',
            new ExecutionContext(new Principal(9), 1),
        );

        self::assertTrue($impact['meta_capability']);
        self::assertTrue($impact['context_required']);
        self::assertSame('contextual_meta', $impact['classification']);
        self::assertSame(['administrator'], $impact['absent_roles']);
        self::assertStringContainsString('contextual/meta', implode(' ', $impact['caveats']));
    }

    public function testUnavailableAndNetworkMismatchReturnExplicitNonHealthyStates(): void
    {
        $unavailable = new FakeRoleRuntimeEnvironment(roles: [], available: false);
        $catalog = $this->service($unavailable, true)->roleCatalog(
            new ExecutionContext(new Principal(3), 1),
        );
        self::assertSame('unavailable', $catalog['state']);
        self::assertSame([], $catalog['roles']);

        $mismatch = new FakeRoleRuntimeEnvironment(
            roles: [
                'administrator' => [
                    'name' => 'Administrator',
                    'capabilities' => ['manage_options' => true],
                ],
            ],
            networkId: 4,
            multisite: true,
        );
        $catalog = $this->service($mismatch, true)->roleCatalog(
            new ExecutionContext(new Principal(3), 1, networkId: 5),
        );
        self::assertSame('degraded', $catalog['state']);
        self::assertSame([], $catalog['roles']);
    }

    public function testPolicyDenialBlocksDirectServiceAccess(): void
    {
        $environment = new FakeRoleRuntimeEnvironment(roles: []);
        $service = $this->service($environment, false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('capability_denied');
        $service->roleCatalog(new ExecutionContext(new Principal(15), 1));
    }

    private function service(RoleRuntimeEnvironmentInterface $environment, bool $allowed): RolesReadService
    {
        $checker = new class ($allowed) implements CapabilityCheckerInterface {
            public function __construct(private readonly bool $allowed) {}

            public function can(ExecutionContext $context, string $capability): bool
            {
                return $this->allowed;
            }
        };

        return new RolesReadService(new PolicyEngine($checker), $environment);
    }
}

final class FakeRoleRuntimeEnvironment implements RoleRuntimeEnvironmentInterface
{
    /**
     * @param array<string,array{name:string,capabilities:array<string,bool>}> $roles
     */
    public function __construct(
        private readonly array $roles,
        private readonly bool $available = true,
        private readonly bool $siteExists = true,
        private readonly int $networkId = 1,
        private readonly bool $multisite = false,
    ) {}

    public function available(): bool
    {
        return $this->available;
    }

    public function siteExists(int $siteId): bool
    {
        return $this->siteExists;
    }

    public function networkIdForSite(int $siteId): ?int
    {
        return $this->siteExists ? $this->networkId : null;
    }

    public function isMultisite(): bool
    {
        return $this->multisite;
    }

    public function rolesForSite(int $siteId): array
    {
        return $this->roles;
    }
}
