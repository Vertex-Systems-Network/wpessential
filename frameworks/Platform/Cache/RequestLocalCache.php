<?php

declare(strict_types=1);

namespace WPEssential\Platform\Cache;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\CacheInterface;

final class RequestLocalCache implements CacheInterface
{
    private const GENERATION_PATTERN = '/^[a-z][a-z0-9._-]{1,127}$/';

    /** @var array<string, array{expires_at:int, value:mixed}> */
    private array $entries = [];

    /** @var array<string, int> */
    private array $generations = [];

    private readonly CacheClockInterface $clock;

    public function __construct(?CacheClockInterface $clock = null)
    {
        $this->clock = $clock ?? new SystemCacheClock();
    }

    public function get(
        CacheKey $key,
        CachePolicy $policy,
        ?CacheDependencies $dependencies = null,
    ): CacheLookup {
        if (!$policy->enabled) {
            return CacheLookup::miss();
        }

        $storageKey = $this->storageKey($key, $dependencies ?? new CacheDependencies());
        $entry = $this->entries[$storageKey] ?? null;
        if ($entry === null) {
            return CacheLookup::miss();
        }

        if ($entry['expires_at'] <= $this->clock->now()) {
            unset($this->entries[$storageKey]);
            return CacheLookup::miss();
        }

        return CacheLookup::hit($entry['value']);
    }

    public function put(
        CacheKey $key,
        mixed $value,
        CachePolicy $policy,
        ?CacheDependencies $dependencies = null,
    ): void {
        if (!$policy->enabled) {
            return;
        }

        $now = $this->clock->now();
        if ($policy->ttlSeconds > PHP_INT_MAX - $now) {
            throw new RuntimeException('Cache expiry exceeds the supported integer range.');
        }

        $storageKey = $this->storageKey($key, $dependencies ?? new CacheDependencies());
        $this->entries[$storageKey] = [
            'expires_at' => $now + $policy->ttlSeconds,
            'value' => $value,
        ];
    }

    public function delete(CacheKey $key, ?CacheDependencies $dependencies = null): void
    {
        unset($this->entries[$this->storageKey($key, $dependencies ?? new CacheDependencies())]);
    }

    public function invalidateGeneration(string $generationKey): void
    {
        $this->assertGenerationKey($generationKey);
        $current = $this->generations[$generationKey] ?? 0;
        if ($current === PHP_INT_MAX) {
            throw new RuntimeException('Cache generation counter exhausted.');
        }

        $this->generations[$generationKey] = $current + 1;
    }

    private function storageKey(CacheKey $key, CacheDependencies $dependencies): string
    {
        $generationVector = [];
        foreach ($dependencies->generationKeys as $generationKey) {
            $generationVector[] = $generationKey . ':' . ($this->generations[$generationKey] ?? 0);
        }

        return hash('sha256', $key->fingerprint() . '|' . implode('|', $generationVector));
    }

    private function assertGenerationKey(string $generationKey): void
    {
        if (preg_match(self::GENERATION_PATTERN, $generationKey) !== 1) {
            throw new InvalidArgumentException('Cache generation key must be a stable lowercase semantic identifier.');
        }
    }
}
