<?php

declare(strict_types=1);

namespace WPEssential\Platform\Definitions;


if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;

final class InMemoryDefinitionRepository implements DefinitionRepositoryInterface
{
    /** @var array<string, Definition> */
    private array $definitions = [];

    public function save(Definition $definition): void
    {
        $existing = $this->definitions[$definition->id] ?? null;
        if ($existing instanceof Definition && $definition->revision <= $existing->revision) {
            throw new RuntimeException('Definition revision must advance monotonically.');
        }
        if ($definition->checksum !== null && !hash_equals($definition->checksum, $definition->computedChecksum())) {
            throw new RuntimeException('Definition checksum does not match canonical payload.');
        }
        $this->definitions[$definition->id] = $definition;
    }

    public function get(string $id): ?Definition
    {
        return $this->definitions[$id] ?? null;
    }

    public function byType(string $type): array
    {
        return array_values(array_filter(
            $this->definitions,
            static fn (Definition $definition): bool => $definition->type === $type,
        ));
    }

    public function dependentsOf(string $id): array
    {
        return array_values(array_filter(
            $this->definitions,
            static fn (Definition $definition): bool => in_array($id, $definition->dependencies, true),
        ));
    }
}
