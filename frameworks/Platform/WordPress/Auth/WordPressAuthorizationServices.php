<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Auth;

if (!defined('ABSPATH')) {
    exit;
}

final class WordPressAuthorizationServices
{
    public const CAPABILITY_CHECKER = 'platform.wordpress.capabilities';
    public const POST_RESOURCES = 'platform.wordpress.post-resources';

    private function __construct() {}
}
