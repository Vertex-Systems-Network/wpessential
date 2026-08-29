<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__, 2) . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use WPEssential\Platform\Jobs\Adapters\ActionScheduler\ActionSchedulerCapabilityProbe;
use WPEssential\Platform\Jobs\Adapters\ActionScheduler\ActionSchedulerEnvironmentInterface;
use WPEssential\Platform\Jobs\Adapters\ActionScheduler\ActionSchedulerProbeState;

final class FakeActionSchedulerEnvironment implements ActionSchedulerEnvironmentInterface
{
    /** @param list<string> $functions */
    public function __construct(
        private readonly array $functions,
        private readonly bool $initialized,
        private readonly ?bool $featureSupport,
    ) {}

    public function hasFunction(string $function): bool
    {
        return in_array($function, $this->functions, true);
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function supports(string $feature): ?bool
    {
        return $this->featureSupport;
    }
}

function actionSchedulerExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$required = [
    'as_enqueue_async_action',
    'as_schedule_single_action',
    'as_schedule_recurring_action',
    'as_unschedule_action',
    'as_has_scheduled_action',
    'as_get_scheduled_actions',
];

$absent = (new ActionSchedulerCapabilityProbe(new FakeActionSchedulerEnvironment([], false, null)))->probe();
actionSchedulerExpect($absent->state === ActionSchedulerProbeState::Absent, 'Absent Action Scheduler must be distinguishable.');

$partial = (new ActionSchedulerCapabilityProbe(new FakeActionSchedulerEnvironment(array_slice($required, 0, -1), true, true)))->probe();
actionSchedulerExpect($partial->state === ActionSchedulerProbeState::Incompatible, 'Partial required API set must be incompatible.');
actionSchedulerExpect($partial->missingFunctions === ['as_get_scheduled_actions'], 'Probe must expose exact missing API surface.');

$notInitialized = (new ActionSchedulerCapabilityProbe(new FakeActionSchedulerEnvironment($required, false, true)))->probe();
actionSchedulerExpect($notInitialized->state === ActionSchedulerProbeState::NotInitialized, 'Loaded API before initialization must not be called ready.');

$ready = (new ActionSchedulerCapabilityProbe(new FakeActionSchedulerEnvironment($required, true, true)))->probe();
actionSchedulerExpect($ready->state === ActionSchedulerProbeState::Ready, 'Complete initialized API set should be probe-ready.');
actionSchedulerExpect($ready->features['ensure_recurring_actions_hook'] === true, 'Feature support must remain explicit.');
actionSchedulerExpect(!$ready->coexistenceCertified && !$ready->multisiteCertified, 'Capability readiness must not fabricate coexistence or Multisite certification.');

fwrite(STDOUT, "WPEssential Action Scheduler probe smoke PASS\n");
