<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\State;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class ListingPublicState
{
    /** @param array<string, scalar|null> $parameters */
    public function __construct(
        public string $namespace,
        public array $parameters,
        public string $queryString,
    ) {
        if (!preg_match('/^[a-z][a-z0-9_-]{0,31}$/', $this->namespace)) {
            throw new InvalidArgumentException('Listing public-state namespace is invalid.');
        }
    }
}
