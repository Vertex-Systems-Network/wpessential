<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Listings\Presentation\ListingNoJsNavigation;
use WPEssential\Modules\Listings\State\ListingPublicState;

final class ListingNoJsNavigationTest extends TestCase
{
    public function testRejectsBasePathWithExistingQuery(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ListingNoJsNavigation())->href(
            '/archive?other=1',
            new ListingPublicState('posts', [], ''),
        );
    }

    public function testRejectsProtocolRelativePath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ListingNoJsNavigation())->href(
            '//example.test/archive',
            new ListingPublicState('posts', [], ''),
        );
    }
}
