<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Listings\Presentation\ListingPresentationDescriptor;

final class ListingPresentationPublicTextTest extends TestCase
{
    public function testRejectsSqlDiagnosticText(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ListingPresentationDescriptor(
            mode: 'list',
            label: 'Posts',
            errorMessage: 'SELECT secret FROM provider_table',
        );
    }
}
