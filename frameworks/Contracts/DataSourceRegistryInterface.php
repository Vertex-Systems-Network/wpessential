<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\DataSources\DataSourceDescriptor;

interface DataSourceRegistryInterface
{
    public function register(DataSourceDescriptor $descriptor): void;

    public function has(string $id): bool;

    public function find(string $id): ?DataSourceDescriptor;

    public function require(string $id): DataSourceDescriptor;

    /** @return list<DataSourceDescriptor> */
    public function all(): array;
}
