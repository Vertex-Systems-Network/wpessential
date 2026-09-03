<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Cache\CacheDependencies;
use WPEssential\Platform\Cache\CacheKey;
use WPEssential\Platform\Cache\CachePolicy;

final readonly class QueryCacheDecision
{
    private const REASONS = [
        'eligible',
        'source_mismatch',
        'source_not_cacheable',
        'missing_generation_keys',
        'principal_unavailable',
        'network_scope_unavailable',
        'invalid_ttl',
        'definition_not_serializable',
    ];

    private function __construct(
        public bool $eligible,
        public string $reason,
        public ?CacheKey $key,
        public CachePolicy $policy,
        public CacheDependencies $dependencies,
    ) {
        if (!in_array($this->reason, self::REASONS, true)) {
            throw new InvalidArgumentException('Query cache decision reason is not part of the V1 taxonomy.');
        }
        if ($this->eligible !== ($this->reason === 'eligible')) {
            throw new InvalidArgumentException('Query cache decision eligibility must match its reason.');
        }
        if ($this->eligible && ($this->key === null || !$this->policy->enabled)) {
            throw new InvalidArgumentException('Eligible Query cache decisions require a key and enabled policy.');
        }
        if (!$this->eligible && ($this->key !== null || $this->policy->enabled)) {
            throw new InvalidArgumentException('Disabled Query cache decisions cannot carry an active key or policy.');
        }
    }

    public static function disabled(string $reason): self
    {
        return new self(
            eligible: false,
            reason: $reason,
            key: null,
            policy: CachePolicy::disabled(),
            dependencies: new CacheDependencies(),
        );
    }

    public static function eligible(
        CacheKey $key,
        CachePolicy $policy,
        CacheDependencies $dependencies,
    ): self {
        return new self(
            eligible: true,
            reason: 'eligible',
            key: $key,
            policy: $policy,
            dependencies: $dependencies,
        );
    }
}
