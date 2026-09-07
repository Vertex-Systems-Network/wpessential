<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryBinding;
use WPEssential\Modules\Listings\State\ListingPublicStateCodec;

final class ListingPublicStateCodecTest extends TestCase
{
    public function testNormalizesOnlyOneListingNamespaceDeterministically(): void
    {
        $binding = new ListingQueryBinding(
            sourceRef: 'wordpress.posts',
            projection: ['post_id', 'title'],
            filterParameters: ['status' => 'post_status'],
            searchParameter: 'q',
        );
        $codec = new ListingPublicStateCodec();

        $state = $codec->normalize($binding, 'news', [
            'wpe_other_status' => 'draft',
            'wpe_news_status' => 'publish',
            'wpe_news_q' => '  hello  ',
            'wpe_news_offset' => '20',
        ]);

        self::assertSame(['status' => 'publish', 'q' => 'hello', 'offset' => 20], $state->parameters);
        self::assertSame('wpe_news_status=publish&wpe_news_q=hello&wpe_news_offset=20', $state->queryString);
    }

    public function testOmitsEmptyDefaultsAndZeroOffset(): void
    {
        $binding = new ListingQueryBinding(
            sourceRef: 'wordpress.posts',
            projection: ['post_id'],
            filterParameters: ['status' => 'post_status'],
            searchParameter: 'q',
        );

        $state = (new ListingPublicStateCodec())->normalize($binding, 'posts', [
            'wpe_posts_status' => '',
            'wpe_posts_q' => '   ',
            'wpe_posts_offset' => '0',
        ]);

        self::assertSame([], $state->parameters);
        self::assertSame('', $state->queryString);
    }

    public function testRejectsUndeclaredParameterInsideOwnedNamespace(): void
    {
        $binding = new ListingQueryBinding('wordpress.posts', ['post_id']);
        $this->expectException(InvalidArgumentException::class);

        (new ListingPublicStateCodec())->normalize($binding, 'posts', [
            'wpe_posts_raw_sql' => 'SELECT 1',
        ]);
    }

    public function testRejectsOffsetBeyondQueryContract(): void
    {
        $binding = new ListingQueryBinding('wordpress.posts', ['post_id']);
        $this->expectException(InvalidArgumentException::class);

        (new ListingPublicStateCodec())->normalize($binding, 'posts', [
            'wpe_posts_offset' => '10001',
        ]);
    }

    public function testRejectsStateBeyondQueryRequestSizeContract(): void
    {
        $binding = new ListingQueryBinding(
            sourceRef: 'wordpress.posts',
            projection: ['post_id'],
            filterParameters: ['status' => 'post_status'],
        );
        $this->expectException(InvalidArgumentException::class);

        (new ListingPublicStateCodec())->normalize($binding, 'posts', [
            'wpe_posts_status' => str_repeat('x', QueryReadConsumerInterface::MAX_REQUEST_BYTES),
        ]);
    }
}
