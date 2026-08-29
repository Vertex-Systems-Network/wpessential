<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs\Adapters\ActionScheduler;


if (!defined('ABSPATH')) {
    exit;
}

final class NativeActionSchedulerEnvironment implements ActionSchedulerEnvironmentInterface
{
    public function hasFunction(string $function): bool
    {
        return function_exists($function);
    }

    public function isInitialized(): bool
    {
        if (class_exists('Action_Scheduler') && method_exists('Action_Scheduler', 'is_initialized')) {
            return (bool) \Action_Scheduler::is_initialized();
        }
        if (function_exists('did_action')) {
            return did_action('action_scheduler_init') > 0;
        }

        return false;
    }

    public function supports(string $feature): ?bool
    {
        if (!function_exists('as_supports')) {
            return null;
        }

        return (bool) as_supports($feature);
    }
}
