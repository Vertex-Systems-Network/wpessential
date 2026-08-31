<?php

declare(strict_types=1);


if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Bootstrap\Plugin;
use WPEssential\Platform\WordPress\Hooks\HookNames;

if (!function_exists('wpessential_kernel')) {
    function wpessential_kernel(): ?\WPEssential\Kernel\Kernel
    {
        return Plugin::kernel();
    }
}

if (!function_exists('wpessential_service')) {
    function wpessential_service(string $id): ?object
    {
        $kernel = wpessential_kernel();
        if ($kernel === null || !$kernel->services()->has($id)) {
            return null;
        }

        return $kernel->services()->get($id);
    }
}

if (!function_exists('wpessential_apply_filter')) {
    function wpessential_apply_filter(string $name, mixed $value, mixed ...$args): mixed
    {
        if (!function_exists('apply_filters')) {
            return $value;
        }

        return apply_filters(HookNames::filter($name), $value, ...$args);
    }
}

if (!function_exists('wpessential_do_action')) {
    function wpessential_do_action(string $name, mixed ...$args): void
    {
        if (function_exists('do_action')) {
            do_action(HookNames::action($name), ...$args);
        }
    }
}
