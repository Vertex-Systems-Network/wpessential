<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Ajax;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;

final readonly class AjaxNonceScope
{
    public function forRoute(string $routeType): string
    {
        $routeType = trim($routeType);
        if (preg_match('/^[a-z0-9][a-z0-9._-]*$/', $routeType) !== 1) {
            throw new InvalidArgumentException('AJAX nonce route type is invalid.');
        }

        if (!function_exists('is_multisite') || !is_multisite()) {
            return $routeType;
        }

        $networkId = function_exists('get_current_network_id') ? (int) get_current_network_id() : 0;
        $siteId = function_exists('get_current_blog_id') ? (int) get_current_blog_id() : 0;
        if ($networkId < 1 || $siteId < 1) {
            throw new RuntimeException('AJAX nonce scope requires a valid active Multisite network and site.');
        }

        return sprintf('network:%d:site:%d:route:%s', $networkId, $siteId, $routeType);
    }
}
