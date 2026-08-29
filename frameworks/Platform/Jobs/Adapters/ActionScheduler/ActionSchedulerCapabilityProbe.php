<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs\Adapters\ActionScheduler;


if (!defined('ABSPATH')) {
    exit;
}

final readonly class ActionSchedulerCapabilityProbe
{
    private const REQUIRED_FUNCTIONS = [
        'as_enqueue_async_action',
        'as_schedule_single_action',
        'as_schedule_recurring_action',
        'as_unschedule_action',
        'as_has_scheduled_action',
        'as_get_scheduled_actions',
    ];

    public function __construct(private ActionSchedulerEnvironmentInterface $environment) {}

    public function probe(): ActionSchedulerProbeReport
    {
        $missing = [];
        foreach (self::REQUIRED_FUNCTIONS as $function) {
            if (!$this->environment->hasFunction($function)) {
                $missing[] = $function;
            }
        }

        $features = [
            'ensure_recurring_actions_hook' => $this->environment->supports('ensure_recurring_actions_hook'),
        ];

        if (count($missing) === count(self::REQUIRED_FUNCTIONS)) {
            return new ActionSchedulerProbeReport(ActionSchedulerProbeState::Absent, $missing, $features);
        }

        if ($missing !== []) {
            return new ActionSchedulerProbeReport(ActionSchedulerProbeState::Incompatible, $missing, $features);
        }

        if (!$this->environment->isInitialized()) {
            return new ActionSchedulerProbeReport(ActionSchedulerProbeState::NotInitialized, [], $features);
        }

        return new ActionSchedulerProbeReport(ActionSchedulerProbeState::Ready, [], $features);
    }
}
