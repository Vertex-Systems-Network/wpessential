<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Listings\Scope\ListingScope;
use WPEssential\Modules\Listings\Scope\ListingScopeGuard;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

final class ListingScopeNetworkOptionalTest extends TestCase
{
    public function testSiteOnlyRequirementDoesNotInventNetworkRestriction(): void
    {
        (new ListingScopeGuard())->assertMatches(
            new ListingScope(12),
            new ExecutionContext(new Principal(7), 12, networkId: 4),
        );

        self::assertTrue(true);
    }
}
