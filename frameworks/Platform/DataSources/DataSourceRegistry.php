<?php

declare(strict_types=1);

namespace WPEssential\Platform\DataSources;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\DataSourceRegistryInterface;

final class DataSourceRegistry implements DataSourceRegistryInterface
{
    /** @var array<string, DataSourceDescriptor> */
    private array $descriptors = [];

    public function register(DataSourceDescriptor $descriptor): void
    {
        if (isset($this->descriptors[$descriptor->id])) {
            throw new RuntimeException(sprintf('Data Source "%s" is already registered.', $descriptor->id));
        }

        $this->descriptors[$descriptor->id] = $descriptor;
    }

    public function has(string $id): bool
    {
        return isset($this->descriptors[$id]);
    }

    public function find(string $id): ?DataSourceDescriptor
    {
        return $this->descriptors[$id] ?? null;
    }

    public function require(string $id): DataSourceDescriptor
    {
        return $this->find($id) ?? throw new RuntimeException(sprintf('Unknown Data Source "%s".', $id));
    }

    public function all(): array
    {
        $descriptors = $this->descriptors;
        ksort($descriptors, SORT_STRING);

        return array_values($descriptors);
    }
}
