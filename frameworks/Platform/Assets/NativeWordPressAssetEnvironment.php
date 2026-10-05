<?php

declare(strict_types=1);

namespace WPEssential\Platform\Assets;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use RuntimeException;

final class NativeWordPressAssetEnvironment implements WordPressAssetEnvironmentInterface
{
    /** @var Closure(callable):void */
    private Closure $registerAdminEnqueue;

    /** @var Closure(string):?string */
    private Closure $routeForAdminHook;

    /** @var Closure(string,string,list<string>,?string):void */
    private Closure $enqueueScript;

    /** @var Closure(string,string,?string):void */
    private Closure $enqueueStyle;

    /** @var Closure(string,string):void */
    private Closure $setScriptStrategy;

    /**
     * @param null|callable(callable):void $registerAdminEnqueue
     * @param null|callable(string):?string $routeForAdminHook
     * @param null|callable(string,string,list<string>,?string):void $enqueueScript
     * @param null|callable(string,string,?string):void $enqueueStyle
     * @param null|callable(string,string):void $setScriptStrategy
     */
    public function __construct(
        ?callable $registerAdminEnqueue = null,
        ?callable $routeForAdminHook = null,
        ?callable $enqueueScript = null,
        ?callable $enqueueStyle = null,
        ?callable $setScriptStrategy = null,
    ) {
        $this->registerAdminEnqueue = $registerAdminEnqueue !== null
            ? Closure::fromCallable($registerAdminEnqueue)
            : static function (callable $callback): void {
                if (!function_exists('add_action')) {
                    throw new RuntimeException('WordPress action API is unavailable.');
                }

                add_action('admin_enqueue_scripts', $callback);
            };

        $this->routeForAdminHook = $routeForAdminHook !== null
            ? Closure::fromCallable($routeForAdminHook)
            : static function (string $hookSuffix): ?string {
                if ($hookSuffix !== 'index.php') {
                    return null;
                }

                if (
                    (defined('REST_REQUEST') && REST_REQUEST)
                    || (defined('WP_CLI') && WP_CLI)
                    || (defined('DOING_AJAX') && DOING_AJAX)
                    || (defined('DOING_CRON') && DOING_CRON)
                    || (function_exists('wp_doing_ajax') && wp_doing_ajax())
                    || (function_exists('wp_doing_cron') && wp_doing_cron())
                    || !function_exists('is_admin')
                    || !is_admin()
                ) {
                    return null;
                }

                return function_exists('is_network_admin') && is_network_admin()
                    ? '/wp-admin/network/index.php'
                    : '/wp-admin/index.php';
            };

        $this->enqueueScript = $enqueueScript !== null
            ? Closure::fromCallable($enqueueScript)
            : static function (
                string $handle,
                string $source,
                array $dependencies,
                ?string $version,
            ): void {
                if (!function_exists('wp_enqueue_script')) {
                    throw new RuntimeException('WordPress script enqueue API is unavailable.');
                }

                wp_enqueue_script(
                    $handle,
                    $source,
                    $dependencies,
                    $version,
                    true,
                );
            };

        $this->enqueueStyle = $enqueueStyle !== null
            ? Closure::fromCallable($enqueueStyle)
            : static function (
                string $handle,
                string $source,
                ?string $version,
            ): void {
                if (!function_exists('wp_enqueue_style')) {
                    throw new RuntimeException('WordPress style enqueue API is unavailable.');
                }

                wp_enqueue_style($handle, $source, [], $version);
            };

        $this->setScriptStrategy = $setScriptStrategy !== null
            ? Closure::fromCallable($setScriptStrategy)
            : static function (string $handle, string $strategy): void {
                if (!in_array($strategy, ['defer'], true)) {
                    throw new RuntimeException('WordPress asset strategy is unsupported.');
                }

                if (function_exists('wp_script_add_data')) {
                    wp_script_add_data($handle, 'strategy', $strategy);
                }
            };
    }

    public function registerAdminEnqueue(callable $callback): void
    {
        ($this->registerAdminEnqueue)($callback);
    }

    public function routeForAdminHook(string $hookSuffix): ?string
    {
        return ($this->routeForAdminHook)($hookSuffix);
    }

    public function enqueueScript(
        string $handle,
        string $source,
        array $dependencies,
        ?string $version,
    ): void {
        ($this->enqueueScript)($handle, $source, $dependencies, $version);
    }

    public function enqueueStyle(
        string $handle,
        string $source,
        ?string $version,
    ): void {
        ($this->enqueueStyle)($handle, $source, $version);
    }

    public function setScriptStrategy(string $handle, string $strategy): void
    {
        ($this->setScriptStrategy)($handle, $strategy);
    }
}
