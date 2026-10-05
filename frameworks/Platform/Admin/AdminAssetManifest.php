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
        $script = $this->script($entry);
        if ($script === null) {
            return null;
        }

        $styleName = $styleEntry !== null && $this->validEntryName($styleEntry)
            ? $styleEntry
            : $entry;
        $style = $this->style($styleName);
        if ($style === null && $styleName !== $entry) {
            $style = $this->style($entry);
        }

        return [
            'script' => $script['script'],
            'styles' => $style !== null ? [$style] : [],
            'dependencies' => $script['dependencies'],
            'version' => $script['version'],
        ];
    }

    /** @return array{script:string,dependencies:list<string>,version:?string}|null */
    public function script(string $entry = 'main'): ?array
    {
        if (!$this->validEntryName($entry)) {
            return null;
        }

        $assetRoot = $this->assetRoot();
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
            if (
                is_string($dependency)
                && preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,190}$/', $dependency) === 1
            ) {
                $dependencies[] = $dependency;
            }
        }

        $version = isset($metadata['version']) && is_string($metadata['version']) && $metadata['version'] !== ''
            ? $metadata['version']
            : null;

        return [
            'script' => $this->assetUrl($entry . '.js'),
            'dependencies' => $dependencies,
            'version' => $version,
        ];
    }

    public function style(string $entry): ?string
    {
        if (!$this->validEntryName($entry)) {
            return null;
        }

        $stylePath = $this->assetRoot() . '/' . $entry . '.css';
        if (!is_readable($stylePath)) {
            return null;
        }

        return $this->assetUrl($entry . '.css');
    }

    private function assetRoot(): string
    {
        return rtrim($this->pluginRoot, '/\\') . '/assets/admin';
    }

    private function assetUrl(string $file): string
    {
        return rtrim($this->pluginUrl, '/') . '/assets/admin/' . $file;
    }

    private function validEntryName(string $entry): bool
    {
        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $entry) === 1
            && !str_contains($entry, '..');
    }
}
