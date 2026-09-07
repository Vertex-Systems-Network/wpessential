<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Listings\Portability\ListingPortablePackage;

final class ListingPortabilityPayloadShapeTest extends TestCase
{
    public function testRejectsListShapedPayload(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ListingPortablePackage(
            definitionId: '11111111-1111-4111-8111-111111111111',
            revision: 1,
            schemaVersion: 1,
            payload: ['unexpected-list-item'],
            querySourceRef: 'wordpress.posts',
            blueprintId: '22222222-2222-4222-8222-222222222222',
            blueprintRevision: 1,
            sourceSiteId: 1,
        );
    }
}
