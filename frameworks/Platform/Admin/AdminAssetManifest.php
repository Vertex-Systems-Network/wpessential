<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class AdminAssetManifest
{
    public function __construct(
        private string $pluginRoot,
        private string $pluginUrl,
    ) {}

    /** @return array{script:string,styles:list<string>,dependencies:list<string>,version:?string}|null */
    public function entry(): ?array
    {
        $assetRoot = rtrim($this->pluginRoot, '/\\') . '/assets/admin';
        $scriptPath = $assetRoot . '/main.js';
        $metadataPath = $assetRoot . '/main.asset.php';

        if (!is_readable($scriptPath) || !is_readable($metadataPath)) {
            return null;
        }

        $metadata = require $metadataPath;
        if (!is_array($metadata)) {
            return null;
        }

        $dependencies = [];
        foreach (($metadata['dependencies'] ?? []) as $dependency) {
            if (is_string($dependency) && $dependency !== '') {
                $dependencies[] = $dependency;
            }
        }

        $version = isset($metadata['version']) && is_string($metadata['version']) && $metadata['version'] !== ''
            ? $metadata['version']
            : null;

        $styles = [];
        $stylePath = $assetRoot . '/main.css';
        if (is_readable($stylePath)) {
            $styles[] = rtrim($this->pluginUrl, '/') . '/assets/admin/main.css';
        }

        return [
            'script' => rtrim($this->pluginUrl, '/') . '/assets/admin/main.js',
            'styles' => $styles,
            'dependencies' => $dependencies,
            'version' => $version,
        ];
    }
}
