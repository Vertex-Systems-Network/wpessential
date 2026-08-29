<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin;

if (!defined('ABSPATH')) {
    exit;
}

use JsonException;

final readonly class AdminAssetManifest
{
    public function __construct(
        private string $pluginRoot,
        private string $pluginUrl,
    ) {}

    /** @return array{script:string,styles:list<string>}|null */
    public function entry(): ?array
    {
        $manifestPath = rtrim($this->pluginRoot, '/\\') . '/assets/admin/.vite/manifest.json';
        if (!is_readable($manifestPath)) {
            return null;
        }

        try {
            $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (!is_array($manifest)) {
            return null;
        }

        $entry = $manifest['admin-ui/src/main.tsx'] ?? null;
        if (!is_array($entry) || !isset($entry['file']) || !is_string($entry['file'])) {
            return null;
        }

        $styles = [];
        foreach (($entry['css'] ?? []) as $style) {
            if (is_string($style) && $style !== '') {
                $styles[] = rtrim($this->pluginUrl, '/') . '/assets/admin/' . ltrim($style, '/');
            }
        }

        return [
            'script' => rtrim($this->pluginUrl, '/') . '/assets/admin/' . ltrim($entry['file'], '/'),
            'styles' => $styles,
        ];
    }
}
