<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__, 2) . '/frameworks/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use WPEssential\Platform\Jobs\Adapters\ActionScheduler\ActionSchedulerBackend;
use WPEssential\Platform\Jobs\Adapters\ActionScheduler\ActionSchedulerBackendEnvironmentInterface;
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

final class FakeActionSchedulerBackendEnvironment implements ActionSchedulerBackendEnvironmentInterface
{
    /** @var array<int, array{hook:string,args:array<string,scalar|null>,group:string}> */
    private array $actions = [];
    private int $nextId = 100;

    public function enqueueAsync(string $hook, array $args, string $group, bool $unique): int
    {
        return $this->scheduleSingle(time(), $hook, $args, $group, $unique);
    }

    public function scheduleSingle(int $timestamp, string $hook, array $args, string $group, bool $unique): int
    {
        if ($unique && $this->firstScheduledActionId($hook, $args, $group) !== null) {
            return 0;
        }
        $id = $this->nextId++;
        $this->actions[$id] = ['hook' => $hook, 'args' => $args, 'group' => $group];
        return $id;
    }

    public function firstScheduledActionId(string $hook, array $args, string $group): ?int
    {
        return $this->scheduledActionIds($hook, $args, $group)[0] ?? null;
    }

    public function scheduledActionIds(string $hook, array $args, string $group): array
    {
        $ids = [];
        foreach ($this->actions as $id => $action) {
            if ($action['hook'] === $hook && $action['args'] === $args && $action['group'] === $group) {
                $ids[] = $id;
            }
        }
        sort($ids);
        return $ids;
    }

    public function unschedule(string $hook, array $args, string $group): ?int
    {
        $id = $this->firstScheduledActionId($hook, $args, $group);
        if ($id !== null) {
            unset($this->actions[$id]);
        }
        return $id;
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
actionSchedulerExpect(!$ready->coexistenceCertified && !$ready->multisiteCertified, 'Runtime capability readiness must not fabricate environment-specific certification.');

$backend = new ActionSchedulerBackend(new FakeActionSchedulerBackendEnvironment());
$jobA = '123e4567-e89b-42d3-a456-426614174000';
$jobB = '223e4567-e89b-42d3-a456-426614174001';
$actionA = $backend->scheduleJobAt($jobA, time() + 60);
actionSchedulerExpect($actionA > 0, 'Backend must materialize a valid WPE Job UUID.');
actionSchedulerExpect($backend->scheduleJobAt($jobA, time() + 60) === $actionA, 'Backend uniqueness may collapse duplicate materialization.');
$actionB = $backend->enqueueJob($jobB);
actionSchedulerExpect($actionB > 0 && $actionB !== $actionA, 'Distinct WPE Job UUIDs must remain isolated.');
actionSchedulerExpect($backend->actionIds($jobA) === [$actionA], 'Backend lookup must use exact WPE hook/group/job-id ownership.');
actionSchedulerExpect($backend->cancelJob($jobA) === $actionA, 'Exact WPE Job cancellation must return its backend action id.');
actionSchedulerExpect(!$backend->hasJob($jobA) && $backend->hasJob($jobB), 'Cancelling one WPE Job must not cancel another.');

fwrite(STDOUT, "WPEssential Action Scheduler probe/backend smoke PASS\n");
