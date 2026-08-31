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
    public function entry(string $entry = 'main', ?string $styleEntry = null): ?array
    {
        if (!$this->validEntryName($entry)) {
            return null;
        }

        $assetRoot = rtrim($this->pluginRoot, '/\\') . '/assets/admin';
        $scriptPath = $assetRoot . '/' . $entry . '.js';
        $metadataPath = $assetRoot . '/' . $entry . '.asset.php';

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

        $styleName = $styleEntry !== null && $this->validEntryName($styleEntry) ? $styleEntry : $entry;
        $stylePath = $assetRoot . '/' . $styleName . '.css';
        if (!is_readable($stylePath) && $styleName !== $entry) {
            $styleName = $entry;
            $stylePath = $assetRoot . '/' . $styleName . '.css';
        }

        $styles = [];
        if (is_readable($stylePath)) {
            $styles[] = rtrim($this->pluginUrl, '/') . '/assets/admin/' . $styleName . '.css';
        }

        return [
            'script' => rtrim($this->pluginUrl, '/') . '/assets/admin/' . $entry . '.js',
            'styles' => $styles,
            'dependencies' => $dependencies,
            'version' => $version,
        ];
    }

    private function validEntryName(string $entry): bool
    {
        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $entry) === 1;
    }
}
