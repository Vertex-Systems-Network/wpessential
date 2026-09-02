<?php

declare(strict_types=1);

namespace WPEssential\Platform\Cache;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class CacheKey
{
    private const NAMESPACE_PATTERN = '/^[a-z][a-z0-9._-]{1,127}$/';
    private const TOKEN_PATTERN = '/^[a-z0-9][a-z0-9._:-]{0,191}$/';

    public function __construct(
        public string $namespace,
        public string $key,
        public int $networkId,
        public int $siteId,
        public ?int $principalId,
        public string $revision,
    ) {
        if (preg_match(self::NAMESPACE_PATTERN, $this->namespace) !== 1) {
            throw new InvalidArgumentException('Cache namespace must be a stable lowercase semantic identifier.');
        }
        if (preg_match(self::TOKEN_PATTERN, $this->key) !== 1) {
            throw new InvalidArgumentException('Cache key must be a stable lowercase token.');
        }
        if ($this->networkId < 1 || $this->siteId < 1) {
            throw new InvalidArgumentException('Cache network and site ids must be positive.');
        }
        if ($this->principalId !== null && $this->principalId < 1) {
            throw new InvalidArgumentException('Cache principal id must be null or positive.');
        }
        if (preg_match(self::TOKEN_PATTERN, $this->revision) !== 1) {
            throw new InvalidArgumentException('Cache revision must be a stable lowercase token.');
        }
    }

    public function fingerprint(): string
    {
        return hash('sha256', implode('|', [
            $this->namespace,
            $this->key,
            (string) $this->networkId,
            (string) $this->siteId,
            $this->principalId === null ? 'anonymous' : (string) $this->principalId,
            $this->revision,
        ]));
    }
}
