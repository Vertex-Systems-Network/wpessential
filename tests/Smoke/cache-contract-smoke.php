<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__, 2) . '/frameworks/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use WPEssential\Platform\Cache\CacheClockInterface;
use WPEssential\Platform\Cache\CacheDependencies;
use WPEssential\Platform\Cache\CacheKey;
use WPEssential\Platform\Cache\CachePolicy;
use WPEssential\Platform\Cache\RequestLocalCache;

function cacheExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$clock = new class implements CacheClockInterface {
    public int $timestamp = 1000;

    public function now(): int
    {
        return $this->timestamp;
    }
};
$cache = new RequestLocalCache($clock);
$policy = CachePolicy::ttl(30);
$key = new CacheKey('query.result', 'customers', 1, 1, 42, 'rev-1');
$dependencies = new CacheDependencies(['query.source.generation']);

$cache->put($key, null, $policy, $dependencies);
$lookup = $cache->get($key, $policy, $dependencies);
cacheExpect($lookup->hit && $lookup->value === null, 'cached null must be distinguishable from a miss');

$otherPrincipal = new CacheKey('query.result', 'customers', 1, 1, 43, 'rev-1');
cacheExpect(!$cache->get($otherPrincipal, $policy, $dependencies)->hit, 'cache must isolate principal context');
$otherSite = new CacheKey('query.result', 'customers', 1, 2, 42, 'rev-1');
cacheExpect(!$cache->get($otherSite, $policy, $dependencies)->hit, 'cache must isolate site context');

$cache->invalidateGeneration('query.source.generation');
cacheExpect(!$cache->get($key, $policy, $dependencies)->hit, 'generation invalidation must make prior entry unreachable');

$cache->put($key, 'disabled-write', CachePolicy::disabled());
cacheExpect(!$cache->get($key, CachePolicy::disabled())->hit, 'disabled cache policy must always miss');

$cache->put($key, 'short-lived', CachePolicy::ttl(5));
$clock->timestamp = 1005;
cacheExpect(!$cache->get($key, CachePolicy::ttl(5))->hit, 'expired cache entry must miss');

$bootstrap = file_get_contents(dirname(__DIR__, 2) . '/frameworks/Bootstrap/Plugin.php');
cacheExpect(is_string($bootstrap), 'bootstrap source must be readable');
cacheExpect(str_contains($bootstrap, 'new RequestLocalCache()'), 'bootstrap must construct canonical cache service');
cacheExpect(str_contains($bootstrap, "set('platform.cache', \$cache)"), 'bootstrap must expose canonical cache service');

fwrite(STDOUT, "WPEssential shared cache contract smoke PASS\n");
