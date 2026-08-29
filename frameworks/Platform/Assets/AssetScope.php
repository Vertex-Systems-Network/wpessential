<?php

declare(strict_types=1);

namespace WPEssential\Platform\Assets;


if (!defined('ABSPATH')) {
    exit;
}

enum AssetScope: string
{
    case Admin = 'admin';
    case Frontend = 'frontend';
    case Both = 'both';

    public function includes(self $requested): bool
    {
        return $this === self::Both || $this === $requested;
    }
}
