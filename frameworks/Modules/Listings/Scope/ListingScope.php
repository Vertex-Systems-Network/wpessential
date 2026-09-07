<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\Scope;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class ListingScope
{
    public function __construct(
        public int $siteId,
        public ?int $networkId = null,
    ) {
        if ($this->siteId < 1) {
            throw new InvalidArgumentException('Listing scope site id must be positive.');
        }
        if ($this->networkId !== null && $this->networkId < 1) {
            throw new InvalidArgumentException('Listing scope network id must be positive when provided.');
        }
    }
}
