<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Listings\Portability\ListingPortablePackage;

final class ListingPortabilityCrossSiteTest extends TestCase
{
    public function testSourceScopeIsPreservedAsMetadata(): void
    {
        $package = new ListingPortablePackage(
            definitionId: '11111111-1111-4111-8111-111111111111',
            revision: 1,
            schemaVersion: 1,
            payload: ['query_source_ref' => 'wordpress.posts'],
            querySourceRef: 'wordpress.posts',
            blueprintId: '22222222-2222-4222-8222-222222222222',
            blueprintRevision: 1,
            sourceSiteId: 7,
            sourceNetworkId: 2,
        );

        self::assertSame(7, $package->toArray()['source_scope']['site_id']);
        self::assertSame(2, $package->toArray()['source_scope']['network_id']);
    }
}
