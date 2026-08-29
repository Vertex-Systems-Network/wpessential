<?php

declare(strict_types=1);

namespace WPEssential\Bootstrap;

use WPEssential\Kernel\Kernel;

final class Plugin
{
    public const VERSION = '0.1.0-dev';
    public const MINIMUM_WORDPRESS = '6.9';
    public const MINIMUM_PHP = '8.2';

    private static ?Kernel $kernel = null;

    public static function boot(): ?Kernel
    {
        if (self::$kernel instanceof Kernel) {
            return self::$kernel;
        }

        if (!self::environmentSupported()) {
            return null;
        }

        self::$kernel = new Kernel();
        self::$kernel->boot();

        return self::$kernel;
    }

    public static function kernel(): ?Kernel
    {
        return self::$kernel;
    }

    private static function environmentSupported(): bool
    {
        if (PHP_VERSION_ID < 80200) {
            return false;
        }

        if (function_exists('get_bloginfo')) {
            $version = (string) get_bloginfo('version');
            if ($version !== '' && version_compare($version, self::MINIMUM_WORDPRESS, '<')) {
                return false;
            }
        }

        return true;
    }
}
