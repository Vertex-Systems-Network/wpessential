<?php

declare(strict_types=1);

namespace WPEssential\Platform\Definitions;

use RuntimeException;
use WPEssential\Contracts\DefinitionTableGatewayInterface;

final class InMemoryDefinitionTableGateway implements DefinitionTableGatewayInterface
{
    /** @var array<string, array<string, scalar|null>> */
    private array $rows = [];

    /** @var array<string, list<string>> */
    private array $dependencies = [];

    public function find(string $id): ?array
    {
        $row = $this->rows[$id] ?? null;
        if ($row === null) {
            return null;
        }
        $row['_dependencies_json'] = json_encode($this->dependencies[$id] ?? [], JSON_THROW_ON_ERROR);
        return $row;
    }

    public function insert(array $row, array $dependencies): void
    {
        $id = (string) ($row['id'] ?? '');
        if ($id === '' || isset($this->rows[$id])) {
            throw new RuntimeException('Definition row already exists or has invalid id.');
        }
        $this->rows[$id] = $row;
        $this->dependencies[$id] = array_values($dependencies);
    }

    public function updateIfCurrentRevision(string $id, int $expectedRevision, array $row, array $dependencies): bool
    {
        $current = $this->rows[$id] ?? null;
        if ($current === null || (int) ($current['revision'] ?? 0) !== $expectedRevision) {
            return false;
        }
        $this->rows[$id] = $row;
        $this->dependencies[$id] = array_values($dependencies);
        return true;
    }

    public function findByType(string $type): array
    {
        $rows = [];
        foreach ($this->rows as $id => $row) {
            if (($row['type'] ?? null) === $type) {
                $row['_dependencies_json'] = json_encode($this->dependencies[$id] ?? [], JSON_THROW_ON_ERROR);
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public function findDependents(string $id): array
    {
        $rows = [];
        foreach ($this->rows as $definitionId => $row) {
            if (in_array($id, $this->dependencies[$definitionId] ?? [], true)) {
                $row['_dependencies_json'] = json_encode($this->dependencies[$definitionId] ?? [], JSON_THROW_ON_ERROR);
                $rows[] = $row;
            }
        }
        return $rows;
    }
}
