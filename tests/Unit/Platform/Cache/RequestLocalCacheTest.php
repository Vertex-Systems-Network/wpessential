<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Cache;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Cache\CacheClockInterface;
use WPEssential\Platform\Cache\CacheDependencies;
use WPEssential\Platform\Cache\CacheKey;
use WPEssential\Platform\Cache\CachePolicy;
use WPEssential\Platform\Cache\RequestLocalCache;

final class RequestLocalCacheTest extends TestCase
{
    public function testSameContextPutAndGetReturnsHit(): void
    {
        $clock = new MutableCacheClock(1000);
        $cache = new RequestLocalCache($clock);
        $key = $this->key();
        $policy = CachePolicy::ttl(60);

        $cache->put($key, ['id' => 7], $policy);
        $lookup = $cache->get($key, $policy);

        self::assertTrue($lookup->hit);
        self::assertSame(['id' => 7], $lookup->value);
    }

    public function testCachedNullIsDistinctFromMiss(): void
    {
        $cache = new RequestLocalCache(new MutableCacheClock(1000));
        $key = $this->key();
        $policy = CachePolicy::ttl(60);

        $cache->put($key, null, $policy);
        $lookup = $cache->get($key, $policy);

        self::assertTrue($lookup->hit);
        self::assertNull($lookup->value);
    }

    public function testDisabledPolicyAlwaysMissesAndDoesNotWrite(): void
    {
        $cache = new RequestLocalCache(new MutableCacheClock(1000));
        $key = $this->key();
        $disabled = CachePolicy::disabled();

        $cache->put($key, 'secret', $disabled);

        self::assertFalse($cache->get($key, $disabled)->hit);
        self::assertFalse($cache->get($key, CachePolicy::ttl(60))->hit);
    }

    public function testTtlExpiresDeterministically(): void
    {
        $clock = new MutableCacheClock(1000);
        $cache = new RequestLocalCache($clock);
        $key = $this->key();
        $policy = CachePolicy::ttl(10);

        $cache->put($key, 'value', $policy);
        self::assertTrue($cache->get($key, $policy)->hit);

        $clock->set(1010);
        self::assertFalse($cache->get($key, $policy)->hit);
    }

    public function testGenerationInvalidationMakesPreviousEntryUnreachable(): void
    {
        $cache = new RequestLocalCache(new MutableCacheClock(1000));
        $key = $this->key();
        $policy = CachePolicy::ttl(60);
        $dependencies = new CacheDependencies(['query.source.generation']);

        $cache->put($key, 'value', $policy, $dependencies);
        self::assertTrue($cache->get($key, $policy, $dependencies)->hit);

        $cache->invalidateGeneration('query.source.generation');

        self::assertFalse($cache->get($key, $policy, $dependencies)->hit);
    }

    public function testDependencyOrderNormalizesToSameGenerationIdentity(): void
    {
        $cache = new RequestLocalCache(new MutableCacheClock(1000));
        $key = $this->key();
        $policy = CachePolicy::ttl(60);

        $cache->put($key, 'value', $policy, new CacheDependencies(['zeta.generation', 'alpha.generation']));

        self::assertTrue($cache->get(
            $key,
            $policy,
            new CacheDependencies(['alpha.generation', 'zeta.generation']),
        )->hit);
    }

    public function testContextDimensionsCannotReuseAnotherContextsEntry(): void
    {
        $cache = new RequestLocalCache(new MutableCacheClock(1000));
        $policy = CachePolicy::ttl(60);
        $canonical = $this->key();
        $cache->put($canonical, 'private', $policy);

        $variants = [
            new CacheKey('query.result', 'customers', 2, 1, 42, 'rev-1'),
            new CacheKey('query.result', 'customers', 1, 2, 42, 'rev-1'),
            new CacheKey('query.result', 'customers', 1, 1, 43, 'rev-1'),
            new CacheKey('query.result', 'customers', 1, 1, null, 'rev-1'),
            new CacheKey('query.result', 'customers', 1, 1, 42, 'rev-2'),
        ];

        foreach ($variants as $variant) {
            self::assertFalse($cache->get($variant, $policy)->hit);
        }

        self::assertSame('private', $cache->get($canonical, $policy)->value);
    }

    public function testDeleteRemovesOnlyCurrentContextEntry(): void
    {
        $cache = new RequestLocalCache(new MutableCacheClock(1000));
        $policy = CachePolicy::ttl(60);
        $first = $this->key();
        $second = new CacheKey('query.result', 'customers', 1, 2, 42, 'rev-1');
        $cache->put($first, 'first', $policy);
        $cache->put($second, 'second', $policy);

        $cache->delete($first);

        self::assertFalse($cache->get($first, $policy)->hit);
        self::assertSame('second', $cache->get($second, $policy)->value);
    }

    public function testInvalidIdentityAndGenerationInputsFailClosed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CacheKey('Query Result', 'customers', 1, 1, 42, 'rev-1');
    }

    public function testDuplicateGenerationDependencyFailsClosed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CacheDependencies(['query.generation', 'query.generation']);
    }

    private function key(): CacheKey
    {
        return new CacheKey(
            namespace: 'query.result',
            key: 'customers',
            networkId: 1,
            siteId: 1,
            principalId: 42,
            revision: 'rev-1',
        );
    }
}

final class MutableCacheClock implements CacheClockInterface
{
    public function __construct(private int $now)
    {
    }

    public function now(): int
    {
        return $this->now;
    }

    public function set(int $now): void
    {
        $this->now = $now;
    }
}
