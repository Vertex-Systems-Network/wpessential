<?php

declare(strict_types=1);

namespace WPEssential\Platform\Assets;

if (!defined('ABSPATH')) {
    exit;
}

interface WordPressAssetEnvironmentInterface
{
    public function registerAdminEnqueue(callable $callback): void;

    public function routeForAdminHook(string $hookSuffix): ?string;

    /**
     * @param list<string> $dependencies
     */
    public function enqueueScript(
        string $handle,
        string $source,
        array $dependencies,
        ?string $version,
    ): void;

    public function enqueueStyle(
        string $handle,
        string $source,
        ?string $version,
    ): void;

    public function setScriptStrategy(string $handle, string $strategy): void;
}
