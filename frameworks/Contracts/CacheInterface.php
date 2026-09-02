<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Cache\CacheDependencies;
use WPEssential\Platform\Cache\CacheKey;
use WPEssential\Platform\Cache\CacheLookup;
use WPEssential\Platform\Cache\CachePolicy;

interface CacheInterface
{
    public function get(
        CacheKey $key,
        CachePolicy $policy,
        ?CacheDependencies $dependencies = null,
    ): CacheLookup;

    public function put(
        CacheKey $key,
        mixed $value,
        CachePolicy $policy,
        ?CacheDependencies $dependencies = null,
    ): void;

    public function delete(CacheKey $key, ?CacheDependencies $dependencies = null): void;

    public function invalidateGeneration(string $generationKey): void;
}
