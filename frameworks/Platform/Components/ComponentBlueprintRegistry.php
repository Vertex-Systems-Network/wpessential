<?php

declare(strict_types=1);

namespace WPEssential\Platform\Components;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;

final class ComponentBlueprintRegistry implements ComponentBlueprintRegistryInterface
{
    /** @var array<string,array<int,ComponentBlueprintDescriptor>> */
    private array $blueprints = [];

    public function register(ComponentBlueprintDescriptor $blueprint): void
    {
        if (isset($this->blueprints[$blueprint->id][$blueprint->revision])) {
            throw new RuntimeException('Component Blueprint id/revision is already registered.');
        }

        $this->blueprints[$blueprint->id][$blueprint->revision] = $blueprint;
        ksort($this->blueprints[$blueprint->id], SORT_NUMERIC);
    }

    public function get(string $blueprintId, int $revision): ?ComponentBlueprintDescriptor
    {
        return $this->blueprints[$blueprintId][$revision] ?? null;
    }

    public function validateGraph(): void
    {
        /** @var array<string,list<string>> $graph */
        $graph = [];
        foreach ($this->blueprints as $id => $revisions) {
            $dependencies = [];
            foreach ($revisions as $blueprint) {
                foreach ($blueprint->dependencyIds as $dependencyId) {
                    if (!isset($this->blueprints[$dependencyId])) {
                        throw new RuntimeException('Component Blueprint dependency is not registered.');
                    }
                    $dependencies[$dependencyId] = true;
                }
            }
            $graph[$id] = array_keys($dependencies);
            sort($graph[$id], SORT_STRING);
        }

        /** @var array<string,true> $resolved */
        $resolved = [];
        /** @var array<string,true> $visiting */
        $visiting = [];
        foreach (array_keys($graph) as $id) {
            $this->visit($id, $graph, $resolved, $visiting);
        }
    }

    /**
     * @param array<string,list<string>> $graph
     * @param array<string,true> $resolved
     * @param array<string,true> $visiting
     */
    private function visit(string $id, array $graph, array &$resolved, array &$visiting): void
    {
        if (isset($resolved[$id])) {
            return;
        }
        if (isset($visiting[$id])) {
            throw new RuntimeException('Circular Component Blueprint dependency detected.');
        }

        $visiting[$id] = true;
        foreach ($graph[$id] ?? [] as $dependencyId) {
            $this->visit($dependencyId, $graph, $resolved, $visiting);
        }
        unset($visiting[$id]);
        $resolved[$id] = true;
    }
}
