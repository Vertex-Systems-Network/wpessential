<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Ajax;


if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;

final class AjaxRouteRegistry
{
    /** @var array<string,AjaxRoute> */
    private array $routes = [];

    public function register(AjaxRoute $route): void
    {
        if (isset($this->routes[$route->type])) {
            throw new RuntimeException(sprintf('AJAX request type "%s" is already registered.', $route->type));
        }
        $this->routes[$route->type] = $route;
    }

    public function get(string $type): ?AjaxRoute
    {
        return $this->routes[$type] ?? null;
    }

    public function hasGuestRoutes(): bool
    {
        foreach ($this->routes as $route) {
            if ($route->allowGuests) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    public function types(): array
    {
        $types = array_keys($this->routes);
        sort($types);
        return $types;
    }
}
