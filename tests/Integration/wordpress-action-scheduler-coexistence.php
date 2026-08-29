<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDERR, "FAIL: WordPress fixture unavailable for Action Scheduler coexistence test\n");
    exit(1);
}

require $wpDir . '/wp-load.php';

$root = dirname(__DIR__, 2);
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = $root . '/frameworks/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use WPEssential\Platform\Jobs\Adapters\ActionScheduler\ActionSchedulerBackend;
use WPEssential\Platform\Jobs\Adapters\ActionScheduler\ActionSchedulerCapabilityProbe;
use WPEssential\Platform\Jobs\Adapters\ActionScheduler\ActionSchedulerProbeState;
use WPEssential\Platform\Jobs\Adapters\ActionScheduler\NativeActionSchedulerBackendEnvironment;
use WPEssential\Platform\Jobs\Adapters\ActionScheduler\NativeActionSchedulerEnvironment;

function asCoexistenceExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

asCoexistenceExpect(did_action('action_scheduler_init') > 0, 'Action Scheduler must initialize during normal WordPress bootstrap');
asCoexistenceExpect(function_exists('as_schedule_single_action'), 'Action Scheduler scheduling API must be available');
asCoexistenceExpect(function_exists('as_supports'), 'Action Scheduler capability API must be available');

$probe = (new ActionSchedulerCapabilityProbe(new NativeActionSchedulerEnvironment()))->probe();
asCoexistenceExpect($probe->state === ActionSchedulerProbeState::Ready, 'WPE Action Scheduler capability probe must report ready');
asCoexistenceExpect(($probe->features['ensure_recurring_actions_hook'] ?? null) === true, 'ensure-recurring capability must be feature-detected');

asCoexistenceExpect(class_exists('ActionScheduler_Versions'), 'Action Scheduler version registry must exist for coexistence evidence');
$versions = ActionScheduler_Versions::instance();
$registeredVersions = array_keys($versions->get_versions());
sort($registeredVersions, SORT_NATURAL);
asCoexistenceExpect(in_array('3.9.3', $registeredVersions, true), 'older 3.9.3 copy must register');
asCoexistenceExpect(in_array('4.1.0', $registeredVersions, true), 'newer 4.1.0 copy must register');
asCoexistenceExpect($versions->latest_version() === '4.1.0', 'newest registered Action Scheduler copy must win version selection');

if (class_exists('ActionScheduler_SystemInformation') && method_exists('ActionScheduler_SystemInformation', 'active_source_path')) {
    $activeSource = (string) ActionScheduler_SystemInformation::active_source_path();
    asCoexistenceExpect(str_contains(str_replace('\\', '/', $activeSource), '/action-scheduler-4.1.0'), 'active Action Scheduler source must be the 4.1.0 fixture copy');
}

global $wpdb;
$actionsTable = $wpdb->prefix . 'actionscheduler_actions';
asCoexistenceExpect($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $actionsTable)) === $actionsTable, 'Action Scheduler action table must exist after activation/bootstrap');

$backend = new ActionSchedulerBackend(new NativeActionSchedulerBackendEnvironment());
$jobId = '123e4567-e89b-42d3-a456-426614174000';
$runAt = time() + 3600;

$thirdPartyId = (int) as_schedule_single_action(
    $runAt,
    'thirdparty/hook',
    ['external_id' => 'third-party'],
    'third-party-group',
    true,
);
asCoexistenceExpect($thirdPartyId > 0, 'third-party control action must schedule successfully');

$wpeActionId = $backend->scheduleJobAt($jobId, $runAt);
asCoexistenceExpect($wpeActionId > 0, 'WPE backend must materialize a Job UUID');
asCoexistenceExpect($backend->hasJob($jobId), 'WPE backend must find its own pending action');
asCoexistenceExpect($backend->actionIds($jobId) === [$wpeActionId], 'WPE backend query must be scoped to its exact hook/group/job-id tuple');

$duplicateId = $backend->scheduleJobAt($jobId, $runAt);
asCoexistenceExpect($duplicateId === $wpeActionId, 'backend uniqueness may collapse duplicate materialization without becoming business idempotency authority');

$otherJobId = '223e4567-e89b-42d3-a456-426614174001';
$otherWpeId = $backend->scheduleJobAt($otherJobId, $runAt);
asCoexistenceExpect($otherWpeId > 0 && $otherWpeId !== $wpeActionId, 'distinct WPE Job UUIDs must materialize as distinct actions');

$cancelled = $backend->cancelJob($jobId);
asCoexistenceExpect($cancelled === $wpeActionId, 'WPE cancellation must target the exact WPE Job action');
asCoexistenceExpect(!$backend->hasJob($jobId), 'cancelled WPE Job must no longer be pending');
asCoexistenceExpect($backend->hasJob($otherJobId), 'cancelling one WPE Job must not cancel another WPE Job');
asCoexistenceExpect(as_has_scheduled_action('thirdparty/hook', ['external_id' => 'third-party'], 'third-party-group'), 'WPE cancellation must not mutate third-party Action Scheduler actions');

$backend->cancelJob($otherJobId);
as_unschedule_action('thirdparty/hook', ['external_id' => 'third-party'], 'third-party-group');

fwrite(STDOUT, "WPEssential real Action Scheduler coexistence/backend integration PASS\n");
