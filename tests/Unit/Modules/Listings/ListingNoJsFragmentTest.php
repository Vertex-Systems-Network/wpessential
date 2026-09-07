<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Listings\Presentation\ListingNoJsNavigation;
use WPEssential\Modules\Listings\State\ListingPublicState;

final class ListingNoJsFragmentTest extends TestCase
{
    public function testRejectsFragmentBearingBasePath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ListingNoJsNavigation())->href(
            '/archive#results',
            new ListingPublicState('posts', [], ''),
        );
    }
}
