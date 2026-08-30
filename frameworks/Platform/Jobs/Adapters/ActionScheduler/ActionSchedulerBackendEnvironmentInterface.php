<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs\Adapters\ActionScheduler;

if (!defined('ABSPATH')) {
    exit;
}

interface ActionSchedulerBackendEnvironmentInterface
{
    /** @param array<string, scalar|null> $args */
    public function enqueueAsync(string $hook, array $args, string $group, bool $unique): int;

    /** @param array<string, scalar|null> $args */
    public function scheduleSingle(int $timestamp, string $hook, array $args, string $group, bool $unique): int;

    /** @param array<string, scalar|null> $args */
    public function firstScheduledActionId(string $hook, array $args, string $group): ?int;

    /** @param array<string, scalar|null> $args @return list<int> */
    public function scheduledActionIds(string $hook, array $args, string $group): array;

    /** @param array<string, scalar|null> $args */
    public function unschedule(string $hook, array $args, string $group): ?int;
}
