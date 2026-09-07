<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Listings\Presentation\ListingPresentationDescriptor;

final class ListingPresentationResponsiveTest extends TestCase
{
    public function testAcceptsBoundedResponsiveColumns(): void
    {
        $descriptor = new ListingPresentationDescriptor(
            mode: 'grid',
            label: 'Posts',
            responsiveColumns: ['base' => 1, 'md' => 2, 'lg' => 3],
        );

        self::assertSame(['base' => 1, 'md' => 2, 'lg' => 3], $descriptor->responsiveColumns);
    }

    public function testRejectsUnknownBreakpoint(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ListingPresentationDescriptor(
            mode: 'grid',
            label: 'Posts',
            responsiveColumns: ['desktop-wide' => 4],
        );
    }
}
