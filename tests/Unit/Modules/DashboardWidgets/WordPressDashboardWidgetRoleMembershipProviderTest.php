<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\WordPressDashboardWidgetRoleMembershipProvider;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

final class WordPressDashboardWidgetRoleMembershipProviderTest extends TestCase
{
    public function testMatchesAnyRoleForCurrentAuthenticatedUserAndSite(): void
    {
        $provider = new WordPressDashboardWidgetRoleMembershipProvider(
            currentUserId: static fn (): ?int => 7,
            currentSiteId: static fn (): int => 3,
            currentUserRoles: static fn (): array => ['author', 'editor'],
        );

        $context = new ExecutionContext(new Principal(7), 3);

        self::assertTrue($provider->isCurrentUserContext($context));
        self::assertTrue($provider->hasAnyRole($context, ['subscriber', 'editor']));
        self::assertFalse($provider->hasAnyRole($context, ['administrator']));
    }

    public function testUserOrSiteMismatchFailsBeforeRoleLookup(): void
    {
        $roleLookups = 0;
        $provider = new WordPressDashboardWidgetRoleMembershipProvider(
            currentUserId: static fn (): ?int => 7,
            currentSiteId: static fn (): int => 3,
            currentUserRoles: static function () use (&$roleLookups): array {
                ++$roleLookups;
                return ['editor'];
            },
        );

        self::assertFalse(
            $provider->hasAnyRole(new ExecutionContext(new Principal(8), 3), ['editor']),
        );
        self::assertFalse(
            $provider->hasAnyRole(new ExecutionContext(new Principal(7), 4), ['editor']),
        );
        self::assertSame(0, $roleLookups);
    }

    public function testAnonymousAndNonUserActorsFailClosed(): void
    {
        $provider = new WordPressDashboardWidgetRoleMembershipProvider(
            currentUserId: static fn (): ?int => 7,
            currentSiteId: static fn (): int => 3,
            currentUserRoles: static fn (): array => ['editor'],
        );

        self::assertFalse(
            $provider->isCurrentUserContext(new ExecutionContext(new Principal(null), 3)),
        );
        self::assertFalse(
            $provider->isCurrentUserContext(new ExecutionContext(new Principal(7, 'service'), 3)),
        );
    }

    public function testUnavailableWordPressBoundaryFailsClosed(): void
    {
        $provider = new WordPressDashboardWidgetRoleMembershipProvider(
            currentUserId: static function (): ?int {
                throw new \LogicException('unavailable');
            },
            currentSiteId: static fn (): int => 3,
            currentUserRoles: static fn (): array => ['editor'],
        );

        self::assertFalse($provider->isCurrentUserContext(new ExecutionContext(new Principal(7), 3)));
        self::assertFalse($provider->hasAnyRole(new ExecutionContext(new Principal(7), 3), ['editor']));
    }

    public function testUnavailableOrMalformedRolePayloadFailsClosed(): void
    {
        $context = new ExecutionContext(new Principal(7), 3);

        $unavailable = new WordPressDashboardWidgetRoleMembershipProvider(
            currentUserId: static fn (): ?int => 7,
            currentSiteId: static fn (): int => 3,
            currentUserRoles: static function (): array {
                throw new \LogicException('unavailable');
            },
        );
        self::assertFalse($unavailable->hasAnyRole($context, ['editor']));

        $malformed = new WordPressDashboardWidgetRoleMembershipProvider(
            currentUserId: static fn (): ?int => 7,
            currentSiteId: static fn (): int => 3,
            currentUserRoles: static fn (): array => ['editor', 9],
        );
        self::assertFalse($malformed->hasAnyRole($context, ['editor']));
    }
}
