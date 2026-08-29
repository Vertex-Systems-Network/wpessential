<?php

declare(strict_types=1);

namespace WPEssential\Platform\Assets;


if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;

final class AssetRegistry
{
    /** @var array<string, AssetDescriptor> */
    private array $assets = [];

    public function register(AssetDescriptor $asset): void
    {
        if (isset($this->assets[$asset->handle])) {
            throw new RuntimeException(sprintf('Asset "%s" is already registered.', $asset->handle));
        }
        $this->assets[$asset->handle] = $asset;
    }

    public function get(string $handle): AssetDescriptor
    {
        return $this->assets[$handle] ?? throw new RuntimeException(sprintf('Unknown asset "%s".', $handle));
    }

    public function validateGraph(): void
    {
        foreach ($this->assets as $asset) {
            foreach ($asset->dependencies as $dependency) {
                if (!isset($this->assets[$dependency])) {
                    throw new RuntimeException(sprintf('Asset "%s" depends on unknown asset "%s".', $asset->handle, $dependency));
                }
            }
        }

        $resolved = [];
        $visiting = [];
        foreach (array_keys($this->assets) as $handle) {
            $unused = [];
            $this->visit($handle, $resolved, $visiting, $unused);
        }
    }

    /** @return list<AssetDescriptor> */
    public function resolve(string $handle): array
    {
        $resolved = [];
        $visiting = [];
        $order = [];
        $this->visit($handle, $resolved, $visiting, $order);

        return array_map(fn (string $item): AssetDescriptor => $this->assets[$item], $order);
    }

    /** @return list<AssetDescriptor> */
    public function forAdminRoute(string $route): array
    {
        $handles = [];
        foreach ($this->assets as $asset) {
            if (
                $asset->loadStrategy === AssetLoadStrategy::AdminRoute
                && $asset->scope->includes(AssetScope::Admin)
                && in_array($route, $asset->adminRoutes, true)
            ) {
                foreach ($this->resolve($asset->handle) as $resolved) {
                    $handles[$resolved->handle] = $resolved;
                }
            }
        }

        return array_values($handles);
    }

    /**
     * @param array<string, bool> $resolved
     * @param array<string, bool> $visiting
     * @param list<string> $order
     */
    private function visit(string $handle, array &$resolved, array &$visiting, array &$order): void
    {
        if (isset($resolved[$handle])) {
            return;
        }
        if (isset($visiting[$handle])) {
            throw new RuntimeException(sprintf('Circular asset dependency detected at "%s".', $handle));
        }
        $asset = $this->assets[$handle] ?? throw new RuntimeException(sprintf('Unknown asset "%s".', $handle));

        $visiting[$handle] = true;
        foreach ($asset->dependencies as $dependency) {
            $this->visit($dependency, $resolved, $visiting, $order);
        }
        unset($visiting[$handle]);

        $resolved[$handle] = true;
        $order[] = $handle;
    }
}
