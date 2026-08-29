<?php

declare(strict_types=1);

namespace WPEssential\Kernel;

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\ServiceRegistryInterface;

final class ServiceRegistry implements ServiceRegistryInterface
{
    /** @var array<string, object> */
    private array $services = [];

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    public function set(string $id, object $service): void
    {
        $id = trim($id);
        if ($id === '') {
            throw new InvalidArgumentException('Service id cannot be empty.');
        }

        if ($this->has($id)) {
            throw new RuntimeException(sprintf('Service "%s" is already registered.', $id));
        }

        $this->services[$id] = $service;
    }

    public function get(string $id): object
    {
        if (!$this->has($id)) {
            throw new RuntimeException(sprintf('Service "%s" is not registered.', $id));
        }

        return $this->services[$id];
    }
}
