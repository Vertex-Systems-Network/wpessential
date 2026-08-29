<?php

declare(strict_types=1);

namespace WPEssential\Platform\Modules;

use RuntimeException;
use WPEssential\Contracts\ModuleInterface;

final class ModuleRegistry
{
    /** @var array<string, ModuleInterface> */
    private array $modules = [];

    /** @var array<string, ModuleState> */
    private array $states = [];

    public function register(ModuleInterface $module): void
    {
        $id = $module->manifest()->id;
        if (isset($this->modules[$id])) {
            throw new RuntimeException(sprintf('Module "%s" is already registered.', $id));
        }

        $this->modules[$id] = $module;
        $this->states[$id] = ModuleState::Registered;
    }

    public function has(string $id): bool
    {
        return isset($this->modules[$id]);
    }

    public function state(string $id): ?ModuleState
    {
        return $this->states[$id] ?? null;
    }

    public function markBooted(string $id): void
    {
        if (!$this->has($id)) {
            throw new RuntimeException(sprintf('Cannot boot unknown module "%s".', $id));
        }
        $this->states[$id] = ModuleState::Booted;
    }

    /** @return list<ModuleInterface> */
    public function bootOrder(): array
    {
        $resolved = [];
        $visiting = [];
        $order = [];

        foreach (array_keys($this->modules) as $id) {
            $this->visit($id, $resolved, $visiting, $order);
        }

        return array_map(fn (string $id): ModuleInterface => $this->modules[$id], $order);
    }

    /**
     * @param array<string, bool> $resolved
     * @param array<string, bool> $visiting
     * @param list<string> $order
     */
    private function visit(string $id, array &$resolved, array &$visiting, array &$order): void
    {
        if (isset($resolved[$id])) {
            return;
        }
        if (isset($visiting[$id])) {
            throw new RuntimeException(sprintf('Circular module dependency detected at "%s".', $id));
        }

        $visiting[$id] = true;
        $module = $this->modules[$id];

        foreach ($module->manifest()->dependencies as $dependency) {
            if (!$this->has($dependency)) {
                $this->states[$id] = ModuleState::Degraded;
                unset($visiting[$id]);
                $resolved[$id] = true;
                return;
            }
            $this->visit($dependency, $resolved, $visiting, $order);
        }

        unset($visiting[$id]);
        $resolved[$id] = true;
        $order[] = $id;
    }
}
