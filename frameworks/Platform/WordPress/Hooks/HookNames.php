<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Hooks;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final class HookNames
{
    public const FILTER_PREFIX = 'wpesential/apply_';
    public const ACTION_PREFIX = 'wpessential/hook_';

    public static function filter(string $name): string
    {
        return self::FILTER_PREFIX . self::suffix($name);
    }

    public static function action(string $name): string
    {
        return self::ACTION_PREFIX . self::suffix($name);
    }

    private static function suffix(string $name): string
    {
        $name = trim($name);
        if ($name === '' || preg_match('/^[a-z0-9][a-z0-9_]*$/', $name) !== 1) {
            throw new InvalidArgumentException('Hook suffix must use lowercase letters, numbers and underscores only.');
        }

        return $name;
    }
}
