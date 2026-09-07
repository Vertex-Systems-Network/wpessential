<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Listings\Scope\ListingScope;
use WPEssential\Modules\Listings\Scope\ListingScopeGuard;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

final class ListingScopeGuardTest extends TestCase
{
    public function testDerivesScopeOnlyFromExecutionContext(): void
    {
        $scope = (new ListingScopeGuard())->fromContext(new ExecutionContext(new Principal(7), 12, networkId: 3));

        self::assertSame(12, $scope->siteId);
        self::assertSame(3, $scope->networkId);
    }

    public function testRejectsPublicSiteScopeInjection(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ListingScopeGuard())->fromContext(
            new ExecutionContext(new Principal(7), 12),
            ['site_id' => 99],
        );
    }

    public function testRejectsCrossSiteExecution(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ListingScopeGuard())->assertMatches(
            new ListingScope(11, 3),
            new ExecutionContext(new Principal(7), 12, networkId: 3),
        );
    }

    public function testRejectsNetworkMismatch(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ListingScopeGuard())->assertMatches(
            new ListingScope(12, 2),
            new ExecutionContext(new Principal(7), 12, networkId: 3),
        );
    }
}
