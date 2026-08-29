<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs\Adapters\ActionScheduler;

interface ActionSchedulerEnvironmentInterface
{
    public function hasFunction(string $function): bool;

    public function isInitialized(): bool;

    public function supports(string $feature): ?bool;
}
