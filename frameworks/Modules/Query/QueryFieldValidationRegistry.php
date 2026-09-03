<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use RuntimeException;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Platform\DataSources\DataSourceDescriptor;

final readonly class QueryFieldValidationRegistry implements DataSourceRegistryInterface
{
    public function __construct(
        private DataSourceRegistryInterface $inner,
        private string $sourceRef,
        private DataSourceDescriptor $projected,
    ) {
    }

    public function register(DataSourceDescriptor $descriptor): void
    {
        throw new LogicException('Query Field validation registry is read-only.');
    }

    public function has(string $id): bool
    {
        return $id === $this->sourceRef || $this->inner->has($id);
    }

    public function find(string $id): ?DataSourceDescriptor
    {
        return $id === $this->sourceRef ? $this->projected : $this->inner->find($id);
    }

    public function require(string $id): DataSourceDescriptor
    {
        return $this->find($id) ?? throw new RuntimeException(sprintf('Data Source "%s" is not registered.', $id));
    }

    public function all(): array
    {
        $all = $this->inner->all();
        $found = false;
        foreach ($all as $index => $descriptor) {
            if ($descriptor->id === $this->sourceRef) {
                $all[$index] = $this->projected;
                $found = true;
            }
        }
        if (!$found) {
            $all[] = $this->projected;
        }
        return array_values($all);
    }
}
