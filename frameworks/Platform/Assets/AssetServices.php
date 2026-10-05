<?php

declare(strict_types=1);

namespace WPEssential\Platform\Assets;

if (!defined('ABSPATH')) {
    exit;
}

final class AssetServices
{
    public const REGISTRY = 'platform.assets';
    public const BUILD_ENTRIES = 'platform.assets.build-entries';
    public const MANIFEST = 'platform.assets.manifest';
    public const WORDPRESS = 'platform.assets.wordpress';

    private function __construct()
    {
    }
}
