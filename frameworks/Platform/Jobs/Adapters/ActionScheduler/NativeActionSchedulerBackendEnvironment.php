<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs\Adapters\ActionScheduler;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;

final class NativeActionSchedulerBackendEnvironment implements ActionSchedulerBackendEnvironmentInterface
{
    public function enqueueAsync(string $hook, array $args, string $group, bool $unique): int
    {
        $this->requireFunction('as_enqueue_async_action');
        return (int) as_enqueue_async_action($hook, $args, $group, $unique);
    }

    public function scheduleSingle(int $timestamp, string $hook, array $args, string $group, bool $unique): int
    {
        $this->requireFunction('as_schedule_single_action');
        return (int) as_schedule_single_action($timestamp, $hook, $args, $group, $unique);
    }

    public function firstScheduledActionId(string $hook, array $args, string $group): ?int
    {
        $this->requireFunction('as_has_scheduled_action');
        $id = as_has_scheduled_action($hook, $args, $group);
        return is_int($id) && $id > 0 ? $id : null;
    }

    public function scheduledActionIds(string $hook, array $args, string $group): array
    {
        $this->requireFunction('as_get_scheduled_actions');
        $ids = as_get_scheduled_actions([
            'hook' => $hook,
            'args' => $args,
            'group' => $group,
            'status' => 'pending',
            'per_page' => 100,
            'orderby' => 'date',
            'order' => 'ASC',
        ], 'ids');

        if (!is_array($ids)) {
            return [];
        }

        $ids = array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
        sort($ids);
        return $ids;
    }

    public function unschedule(string $hook, array $args, string $group): ?int
    {
        $this->requireFunction('as_unschedule_action');
        $id = as_unschedule_action($hook, $args, $group);
        return is_int($id) && $id > 0 ? $id : null;
    }

    private function requireFunction(string $function): void
    {
        if (!function_exists($function)) {
            throw new RuntimeException(sprintf('Action Scheduler public API "%s" is unavailable.', $function));
        }
    }
}
