<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Listings\Presentation\ListingNoJsNavigation;
use WPEssential\Modules\Listings\Presentation\ListingPresentationDescriptor;
use WPEssential\Modules\Listings\State\ListingPublicState;

final class ListingPresentationTest extends TestCase
{
    public function testTableRequiresExplicitSemanticHeaders(): void
    {
        $descriptor = new ListingPresentationDescriptor(
            mode: 'table',
            label: 'Posts',
            tableHeaders: ['title' => 'Title'],
        );

        self::assertSame(['title' => 'Title'], $descriptor->tableHeaders);
    }

    public function testRejectsTableWithoutHeaders(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ListingPresentationDescriptor('table', 'Posts');
    }

    public function testRejectsUnsafePublicErrorText(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ListingPresentationDescriptor('list', 'Posts', errorMessage: '<script>alert(1)</script>');
    }

    public function testPreservesCanonicalPublicStateInNoJsHref(): void
    {
        $state = new ListingPublicState('posts', ['offset' => 20], 'wpe_posts_offset=20');
        $href = (new ListingNoJsNavigation())->href('/archive', $state);

        self::assertSame('/archive?wpe_posts_offset=20', $href);
    }
}
