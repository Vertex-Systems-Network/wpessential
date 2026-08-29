<?php

declare(strict_types=1);

namespace WPEssential\Platform\Assets;

use InvalidArgumentException;

final readonly class AssetDescriptor
{
    /**
     * @param list<string> $dependencies
     * @param list<string> $adminRoutes
     */
    public function __construct(
        public string $handle,
        public int $ownerSurfaceId,
        public AssetScope $scope,
        public AssetLoadStrategy $loadStrategy = AssetLoadStrategy::OnDemand,
        public array $dependencies = [],
        public string $version = 'dev',
        public array $adminRoutes = [],
    ) {
        if (!preg_match('/^wpe-[a-z0-9][a-z0-9-]{1,127}$/', $this->handle)) {
            throw new InvalidArgumentException('Asset handle must use the wpe- prefix and stable lowercase characters.');
        }
        if ($this->ownerSurfaceId < 1 || $this->ownerSurfaceId > 56) {
            throw new InvalidArgumentException('Asset owner must be a canonical surface id 1..56.');
        }
        if ($this->version === '') {
            throw new InvalidArgumentException('Asset version/hash cannot be empty.');
        }
        if (count($this->dependencies) !== count(array_unique($this->dependencies))) {
            throw new InvalidArgumentException('Asset dependencies must be unique.');
        }
        if (in_array($this->handle, $this->dependencies, true)) {
            throw new InvalidArgumentException('Asset cannot depend on itself.');
        }
        if ($this->loadStrategy === AssetLoadStrategy::AdminRoute) {
            if (!$this->scope->includes(AssetScope::Admin) || $this->adminRoutes === []) {
                throw new InvalidArgumentException('Admin-route assets require admin scope and at least one exact admin route.');
            }
        }
        foreach ($this->adminRoutes as $route) {
            if (!str_starts_with($route, '/')) {
                throw new InvalidArgumentException('Admin asset routes must be canonical absolute route paths.');
            }
        }
    }
}
