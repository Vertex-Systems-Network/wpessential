<?php

declare(strict_types=1);

namespace WPEssential\Contracts;


if (!defined('ABSPATH')) {
    exit;
}

interface ServiceRegistryInterface
{
    public function has(string $id): bool;

    public function set(string $id, object $service): void;

    public function get(string $id): object;
}
