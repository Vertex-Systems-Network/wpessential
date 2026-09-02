<?php

declare(strict_types=1);

namespace WPEssential\Platform\Cache;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class CacheDependencies
{
    private const GENERATION_PATTERN = '/^[a-z][a-z0-9._-]{1,127}$/';

    /** @var list<string> */
    public array $generationKeys;

    /** @param list<string> $generationKeys */
    public function __construct(array $generationKeys = [])
    {
        $seen = [];
        foreach ($generationKeys as $generationKey) {
            if (!is_string($generationKey) || preg_match(self::GENERATION_PATTERN, $generationKey) !== 1) {
                throw new InvalidArgumentException('Cache generation keys must be stable lowercase semantic identifiers.');
            }
            if (isset($seen[$generationKey])) {
                throw new InvalidArgumentException('Cache generation keys must be unique.');
            }
            $seen[$generationKey] = true;
        }

        $normalized = array_keys($seen);
        sort($normalized, SORT_STRING);
        $this->generationKeys = $normalized;
    }
}
