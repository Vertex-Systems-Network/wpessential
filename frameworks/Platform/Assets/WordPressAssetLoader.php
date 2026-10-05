<?php

declare(strict_types=1);

namespace WPEssential\Platform\Assets;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Platform\Admin\AdminAssetManifest;

final class WordPressAssetLoader
{
    private bool $registered = false;

    public function __construct(
        private readonly AssetRegistry $assets,
        private readonly TrustedAssetBuildEntryRegistry $buildEntries,
        private readonly AdminAssetManifest $manifest,
        private readonly WordPressAssetEnvironmentInterface $environment,
    ) {
    }

    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $this->environment->registerAdminEnqueue([$this, 'enqueueForAdminHook']);
        $this->registered = true;
    }

    public function enqueueForAdminHook(string $hookSuffix): void
    {
        try {
            $route = $this->environment->routeForAdminHook($hookSuffix);
            if ($route === null) {
                return;
            }

            $descriptors = $this->assets->forAdminRoute($route);
            $plan = [];

            foreach ($descriptors as $descriptor) {
                if (!$descriptor->scope->includes(AssetScope::Admin)) {
                    return;
                }

                $mapping = $this->buildEntries->get($descriptor->handle);
                $script = $this->manifest->script($mapping->scriptEntry);
                if ($script === null) {
                    return;
                }

                $style = null;
                if ($mapping->styleEntry !== null) {
                    $style = $this->manifest->style($mapping->styleEntry);
                    if ($style === null) {
                        return;
                    }
                }

                $plan[] = [
                    'descriptor' => $descriptor,
                    'script' => $script,
                    'style' => $style,
                ];
            }

            foreach ($plan as $item) {
                $descriptor = $item['descriptor'];
                $script = $item['script'];
                $version = $script['version'] ?? $descriptor->version;

                $this->environment->enqueueScript(
                    $descriptor->handle,
                    $script['script'],
                    $script['dependencies'],
                    $version,
                );
                $this->environment->setScriptStrategy(
                    $descriptor->handle,
                    'defer',
                );

                if (is_string($item['style'])) {
                    $this->environment->enqueueStyle(
                        $descriptor->handle . '-style',
                        $item['style'],
                        $version,
                    );
                }
            }
        } catch (Throwable) {
            // Fail closed: never fall back to remote/inline/untrusted asset loading.
        }
    }
}
