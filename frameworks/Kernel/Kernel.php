<?php

declare(strict_types=1);

namespace WPEssential\Kernel;


if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Platform\Modules\ModuleRegistry;
use WPEssential\Platform\Modules\ModuleState;

final class Kernel
{
    private bool $booted = false;

    public function __construct(
        private readonly ServiceRegistry $services = new ServiceRegistry(),
        private readonly ModuleRegistry $modules = new ModuleRegistry(),
    ) {
        $this->services->set('platform.modules', $this->modules);
    }

    public function services(): ServiceRegistry
    {
        return $this->services;
    }

    public function modules(): ModuleRegistry
    {
        return $this->modules;
    }

    public function registerModule(ModuleInterface $module): void
    {
        if ($this->booted) {
            throw new LogicException('Modules cannot be registered after the kernel has booted.');
        }
        $this->modules->register($module);
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->modules->bootOrder() as $module) {
            if ($this->modules->state($module->manifest()->id) === ModuleState::Degraded) {
                continue;
            }
            $module->register($this->services);
        }

        foreach ($this->modules->bootOrder() as $module) {
            if ($this->modules->state($module->manifest()->id) === ModuleState::Degraded) {
                continue;
            }
            $module->boot($this->services);
            $this->modules->markBooted($module->manifest()->id);
        }

        $this->booted = true;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }
}
