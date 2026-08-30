<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDERR, "FAIL: WordPress fixture unavailable for Multisite isolation integration\n");
    exit(1);
}

if (!defined('ABSPATH')) {
    define('ABSPATH', $wpDir . '/');
}

$host = trim((string) getenv('WPE_TEST_WP_HOST'));
$host = $host !== '' ? $host : 'wpessential.test';
$_SERVER['HTTP_HOST'] = $host;
$_SERVER['SERVER_NAME'] = $host;
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';

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

use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Database\NativeWpdbAdapter;
use WPEssential\Platform\Jobs\Adapters\ActionScheduler\ActionSchedulerBackend;
use WPEssential\Platform\Jobs\Adapters\ActionScheduler\NativeActionSchedulerBackendEnvironment;
use WPEssential\Platform\Jobs\JobIdempotencyMode;
use WPEssential\Platform\Jobs\JobScope;
use WPEssential\Platform\Jobs\JobState;
use WPEssential\Platform\Jobs\JobTableNames;
use WPEssential\Platform\Jobs\JobType;
use WPEssential\Platform\Jobs\Migrations\CreateJobTablesMigration;
use WPEssential\Platform\Jobs\PersistentJobService;
use WPEssential\Platform\Jobs\RetryPolicy;
use WPEssential\Platform\Jobs\WpdbJobAttemptStore;
use WPEssential\Platform\Jobs\WpdbJobPersistenceGateway;
use WPEssential\Platform\WordPress\Abilities\NativeWordPressAbilityEnvironment;
use WPEssential\Platform\WordPress\Abilities\WordPressCapabilityChecker;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AbilityAjaxHandler;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;
use WPEssential\Platform\WordPress\Ajax\AjaxRoute;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Security\NativeWordPressNonceEnvironment;
use WPEssential\Platform\WordPress\Security\NonceManager;
use WPEssential\Platform\WordPress\Security\NonceOperation;

function multisiteIsolationExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @param callable():mixed $callback */
function multisiteIsolationExpectThrows(callable $callback, string $message): void
{
    try {
        $callback();
    } catch (Throwable) {
        return;
    }

    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

multisiteIsolationExpect(is_multisite(), 'fixture must be a real WordPress Multisite installation');
$sites = get_sites(['number' => 10, 'orderby' => 'id', 'order' => 'ASC']);
multisiteIsolationExpect(count($sites) >= 2, 'fixture must contain at least two WordPress sites');
$siteA = (int) $sites[0]->blog_id;
$siteB = (int) $sites[1]->blog_id;
multisiteIsolationExpect($siteA > 0 && $siteB > 0 && $siteA !== $siteB, 'fixture site ids must be distinct and positive');

$admin = get_user_by('login', 'wpessential_admin');
multisiteIsolationExpect($admin instanceof WP_User, 'Multisite fixture must provide the network administrator');
$adminId = (int) $admin->ID;
wp_set_current_user($adminId);
multisiteIsolationExpect(is_super_admin($adminId), 'fixture administrator must be a Multisite super admin');
$networkId = max(1, (int) get_current_network_id());

$abilityEnvironment = new NativeWordPressAbilityEnvironment();
$abilities = new AbilityRegistry(new PolicyEngine(new WordPressCapabilityChecker($abilityEnvironment)));
$abilities->register(
    new AbilityDescriptor(
        name: 'wpessential/platform/multisite-fixture',
        ownerSurfaceId: 31,
        capability: 'manage_options',
        mutates: true,
        channels: [ExecutionChannel::Ui],
    ),
    new class implements AbilityHandlerInterface {
        public function handle(array $input, ExecutionContext $context): mixed
        {
            return [
                'site_id' => $context->siteId,
                'network_id' => $context->networkId,
                'user_id' => $context->principal->userId,
                'echo' => $input['echo'] ?? null,
            ];
        }
    },
);

$routes = new AjaxRouteRegistry();
$routes->register(new AjaxRoute(
    type: 'platform.multisite-fixture',
    handler: new AbilityAjaxHandler(
        $abilities,
        'wpessential/platform/multisite-fixture',
        new WordPressExecutionContextFactory($abilityEnvironment),
    ),
    operation: NonceOperation::Update,
    capability: null,
    allowGuests: false,
    requiresNonce: true,
));
$dispatcher = new AjaxDispatcher(
    $routes,
    new NonceManager(new NativeWordPressNonceEnvironment(), 'wpessential_request'),
    static fn (string $capability): bool => current_user_can($capability),
);

switch_to_blog($siteA);
wp_set_current_user($adminId);
$nonceA = $dispatcher->createNonce('platform.multisite-fixture');
$responseA = $dispatcher->dispatch([
    'type' => 'platform.multisite-fixture',
    'nonce' => $nonceA,
    'payload' => ['echo' => 'site-a'],
], true);
multisiteIsolationExpect($responseA->success, 'site A nonce must authorize on site A');
multisiteIsolationExpect((int) ($responseA->data['site_id'] ?? 0) === $siteA, 'AJAX context must resolve site A id');
multisiteIsolationExpect((int) ($responseA->data['network_id'] ?? 0) === $networkId, 'AJAX context must resolve the active network id on site A');
restore_current_blog();

switch_to_blog($siteB);
wp_set_current_user($adminId);
$replay = $dispatcher->dispatch([
    'type' => 'platform.multisite-fixture',
    'nonce' => $nonceA,
    'payload' => ['echo' => 'replay'],
], true);
multisiteIsolationExpect(!$replay->success && $replay->status === 403 && $replay->errorCode === 'invalid_nonce', 'same-user site A nonce must not replay on site B');
$nonceB = $dispatcher->createNonce('platform.multisite-fixture');
$responseB = $dispatcher->dispatch([
    'type' => 'platform.multisite-fixture',
    'nonce' => $nonceB,
    'payload' => ['echo' => 'site-b'],
], true);
multisiteIsolationExpect($responseB->success, 'site B nonce must authorize on site B');
multisiteIsolationExpect((int) ($responseB->data['site_id'] ?? 0) === $siteB, 'AJAX context must resolve site B id');
multisiteIsolationExpect((int) ($responseB->data['network_id'] ?? 0) === $networkId, 'AJAX context must preserve network id on site B');
restore_current_blog();

$database = new NativeWpdbAdapter($GLOBALS['wpdb']);
$migration = new CreateJobTablesMigration($database);
$migration->apply();
$tables = new JobTableNames($database);
multisiteIsolationExpect($tables->jobs === $GLOBALS['wpdb']->base_prefix . 'wpe_jobs', 'durable jobs must use the network base prefix');
multisiteIsolationExpect($tables->attempts === $GLOBALS['wpdb']->base_prefix . 'wpe_job_attempts', 'durable attempts must use the network base prefix');

$scopeA = new JobScope($networkId, $siteA);
$scopeB = new JobScope($networkId, $siteB);
$type = new JobType(
    key: 'wpessential/platform/multisite-durable-fixture',
    ownerSurfaceId: 31,
    handlerAbility: 'wpessential/platform/run-job',
    idempotencyMode: JobIdempotencyMode::StableKey,
    retryPolicy: new RetryPolicy(maxAttempts: 3, initialDelaySeconds: 1, backoffFactor: 2.0, maxDelaySeconds: 5),
    supportsCancellation: true,
    resourceClasses: ['db_write'],
);
$gateway = new WpdbJobPersistenceGateway($database);
$serviceA = new PersistentJobService($gateway, $scopeA);
$serviceB = new PersistentJobService($gateway, $scopeB);
$serviceA->registerType($type);
$serviceB->registerType($type);
$contextA = new ExecutionContext(new Principal($adminId), $siteA, networkId: $networkId);
$contextB = new ExecutionContext(new Principal($adminId), $siteB, networkId: $networkId);

$sharedKey = 'multisite:shared-provider-event';
$jobA = $serviceA->enqueue($type->key, ['site' => 'a'], $contextA, $sharedKey);
$jobB = $serviceB->enqueue($type->key, ['site' => 'b'], $contextB, $sharedKey);
multisiteIsolationExpect($jobA->id !== $jobB->id, 'same idempotency key must remain isolated across site scopes');
multisiteIsolationExpect($serviceB->get($jobA->id) === null, 'site B JobService must not read site A jobs');
multisiteIsolationExpect($serviceA->get($jobB->id) === null, 'site A JobService must not read site B jobs');
multisiteIsolationExpectThrows(static fn () => $serviceB->cancel($jobA->id), 'site B must not mutate a site A job');
multisiteIsolationExpectThrows(static fn () => $serviceA->start($jobB->id), 'site A must not start a site B job');

$attempts = new WpdbJobAttemptStore($database);
$t0 = new DateTimeImmutable('2026-08-30T12:00:00+00:00', new DateTimeZone('UTC'));
$leaseA = $attempts->lease($scopeA, $jobA->id, 'multisite-worker-a', 60, $t0);
multisiteIsolationExpect($leaseA !== null, 'site A worker must acquire its own job lease');
multisiteIsolationExpectThrows(
    static fn () => $attempts->lease($scopeB, $jobA->id, 'multisite-worker-b-wrong', 60, $t0),
    'site B worker must not lease a site A job id',
);
multisiteIsolationExpect($attempts->checkpoint($leaseA, 1, ['cursor' => 'site-a'], $t0->modify('+1 second')), 'site A checkpoint must persist');
$leaseB = $attempts->lease($scopeB, $jobB->id, 'multisite-worker-b', 60, $t0);
multisiteIsolationExpect($leaseB !== null, 'site B worker must acquire its own job lease');
multisiteIsolationExpect($attempts->checkpoint($leaseB, 1, ['cursor' => 'site-b'], $t0->modify('+1 second')), 'site B checkpoint must persist');
$rowsA = (int) $database->getVar($database->prepare(
    "SELECT COUNT(*) FROM `{$tables->attempts}` WHERE network_id = %d AND site_id = %d AND job_id = %s",
    $networkId,
    $siteA,
    $jobA->id,
));
$rowsBForA = (int) $database->getVar($database->prepare(
    "SELECT COUNT(*) FROM `{$tables->attempts}` WHERE network_id = %d AND site_id = %d AND job_id = %s",
    $networkId,
    $siteB,
    $jobA->id,
));
multisiteIsolationExpect($rowsA === 1 && $rowsBForA === 0, 'attempt/checkpoint rows must remain site scoped in the shared network table');

$serviceB->cancel($jobB->id);
$afterCancelB = $serviceB->get($jobB->id);
$afterCancelA = $serviceA->get($jobA->id);
multisiteIsolationExpect($afterCancelB !== null && $afterCancelB->state === JobState::Cancelled, 'site B cancellation must update its own job');
multisiteIsolationExpect($afterCancelA !== null && $afterCancelA->state === JobState::Available, 'site B cancellation must not mutate site A job state');

multisiteIsolationExpect(function_exists('as_schedule_single_action'), 'Action Scheduler public scheduling API must be loaded');
multisiteIsolationExpect(function_exists('as_get_scheduled_actions'), 'Action Scheduler public query API must be loaded');
$scheduledJobId = '323e4567-e89b-42d3-a456-426614174002';
$runAt = time() + 3600;

switch_to_blog($siteA);
$siteAPrefix = (string) $GLOBALS['wpdb']->prefix;
$siteAActionsTable = $siteAPrefix . 'actionscheduler_actions';
multisiteIsolationExpect($database->getVar($database->prepare('SHOW TABLES LIKE %s', $siteAActionsTable)) === $siteAActionsTable, 'site A Action Scheduler table must exist');
$backendA = new ActionSchedulerBackend(new NativeActionSchedulerBackendEnvironment());
$actionA = $backendA->scheduleJobAt($scheduledJobId, $runAt);
multisiteIsolationExpect($actionA > 0 && $backendA->hasJob($scheduledJobId), 'site A Action Scheduler backend must schedule and query its job');
restore_current_blog();

switch_to_blog($siteB);
$siteBPrefix = (string) $GLOBALS['wpdb']->prefix;
$siteBActionsTable = $siteBPrefix . 'actionscheduler_actions';
multisiteIsolationExpect($siteBPrefix !== $siteAPrefix, 'Multisite site table prefixes must differ');
multisiteIsolationExpect($database->getVar($database->prepare('SHOW TABLES LIKE %s', $siteBActionsTable)) === $siteBActionsTable, 'site B Action Scheduler table must exist');
$backendB = new ActionSchedulerBackend(new NativeActionSchedulerBackendEnvironment());
multisiteIsolationExpect(!$backendB->hasJob($scheduledJobId), 'site B Action Scheduler query must not see site A pending action');
$actionB = $backendB->scheduleJobAt($scheduledJobId, $runAt);
multisiteIsolationExpect($actionB > 0 && $backendB->hasJob($scheduledJobId), 'site B Action Scheduler backend must independently schedule the same job id');
restore_current_blog();

switch_to_blog($siteA);
$backendA = new ActionSchedulerBackend(new NativeActionSchedulerBackendEnvironment());
multisiteIsolationExpect($backendA->hasJob($scheduledJobId), 'site A pending action must survive site B scheduling');
multisiteIsolationExpect($backendA->cancelJob($scheduledJobId) !== null, 'site A cancellation must target its own Action Scheduler row');
multisiteIsolationExpect(!$backendA->hasJob($scheduledJobId), 'site A cancellation must remove the site A pending action');
restore_current_blog();

switch_to_blog($siteB);
$backendB = new ActionSchedulerBackend(new NativeActionSchedulerBackendEnvironment());
multisiteIsolationExpect($backendB->hasJob($scheduledJobId), 'site A cancellation must not remove site B Action Scheduler action');
multisiteIsolationExpect($backendB->cancelJob($scheduledJobId) !== null, 'site B cleanup must cancel its own pending action');
restore_current_blog();

$evidence = [
    'wordpress' => get_bloginfo('version'),
    'php' => PHP_VERSION,
    'network_id' => $networkId,
    'site_a' => $siteA,
    'site_b' => $siteB,
    'job_table' => $tables->jobs,
    'attempt_table' => $tables->attempts,
    'action_scheduler_site_a_table' => $siteAActionsTable,
    'action_scheduler_site_b_table' => $siteBActionsTable,
    'nonce_cross_site_replay_rejected' => true,
    'durable_job_scope_isolated' => true,
    'lease_checkpoint_scope_isolated' => true,
    'action_scheduler_scope_isolated' => true,
];
$evidencePath = trim((string) getenv('WPE_MULTISITE_EVIDENCE_PATH'));
if ($evidencePath !== '') {
    $json = json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    multisiteIsolationExpect(file_put_contents($evidencePath, $json . "\n") !== false, 'Multisite evidence file must be writable');
}

fwrite(STDOUT, "WPEssential real WordPress Multisite AJAX/job/Action Scheduler isolation PASS\n");
