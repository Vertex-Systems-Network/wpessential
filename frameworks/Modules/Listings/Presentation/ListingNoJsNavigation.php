<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\Presentation;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\Listings\State\ListingPublicState;

final class ListingNoJsNavigation
{
    public function href(string $path, ListingPublicState $state): string
    {
        if ($path === '' || !str_starts_with($path, '/') || str_starts_with($path, '//') || preg_match('/[\r\n]/', $path)) {
            throw new InvalidArgumentException('Listing no-JS navigation path must be a local absolute path.');
        }

        return $state->queryString === '' ? $path : $path . '?' . $state->queryString;
    }
}
