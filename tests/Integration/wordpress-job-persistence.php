<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDERR, "FAIL: WordPress fixture unavailable for durable JobService integration\n");
    exit(1);
}

if (!defined('ABSPATH')) {
    define('ABSPATH', $wpDir . '/');
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

use DateTimeImmutable;
use DateTimeZone;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Database\NativeWpdbAdapter;
use WPEssential\Platform\Jobs\JobAttemptState;
use WPEssential\Platform\Jobs\JobFailureClass;
use WPEssential\Platform\Jobs\JobIdempotencyMode;
use WPEssential\Platform\Jobs\JobRowCodec;
use WPEssential\Platform\Jobs\JobScope;
use WPEssential\Platform\Jobs\JobState;
use WPEssential\Platform\Jobs\JobTableNames;
use WPEssential\Platform\Jobs\JobType;
use WPEssential\Platform\Jobs\Migrations\CreateJobTablesMigration;
use WPEssential\Platform\Jobs\PersistentJobService;
use WPEssential\Platform\Jobs\RetryPolicy;
use WPEssential\Platform\Jobs\WpdbJobAttemptStore;
use WPEssential\Platform\Jobs\WpdbJobPersistenceGateway;

function jobPersistenceExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

global $wpdb;
$database = new NativeWpdbAdapter($wpdb);
$migration = new CreateJobTablesMigration($database);
$migration->apply();
$migration->apply();

$tables = new JobTableNames($database);
jobPersistenceExpect($database->getVar($database->prepare('SHOW TABLES LIKE %s', $tables->jobs)) === $tables->jobs, 'durable jobs table must exist');
jobPersistenceExpect($database->getVar($database->prepare('SHOW TABLES LIKE %s', $tables->attempts)) === $tables->attempts, 'durable attempts table must exist');

$scope = new JobScope(max(1, (int) get_current_network_id()), max(1, (int) get_current_blog_id()));
$context = new ExecutionContext(
    principal: new Principal(max(1, (int) get_current_user_id())),
    siteId: $scope->siteId,
    networkId: $scope->networkId,
);
$type = new JobType(
    key: 'wpessential/platform/durable-fixture',
    ownerSurfaceId: 31,
    handlerAbility: 'wpessential/platform/run-job',
    idempotencyMode: JobIdempotencyMode::StableKey,
    retryPolicy: new RetryPolicy(maxAttempts: 3, initialDelaySeconds: 1, backoffFactor: 2.0, maxDelaySeconds: 5),
    supportsCancellation: true,
    resourceClasses: ['db_write'],
);

$gateway = new WpdbJobPersistenceGateway($database);
$service = new PersistentJobService($gateway, $scope);
$service->registerType($type);

$idempotencyKey = 'provider:event:fixture-001';
$job = $service->enqueue($type->key, ['value' => 7, 'reference' => 'fixture'], $context, $idempotencyKey);
$duplicate = $service->enqueue($type->key, ['value' => 999], $context, $idempotencyKey);
jobPersistenceExpect($duplicate->id === $job->id, 'stable-key enqueue must deduplicate to the persisted Job');

$row = $gateway->find($scope, $job->id);
jobPersistenceExpect($row !== null, 'persisted Job must be readable');
jobPersistenceExpect(($row['idempotency_hash'] ?? null) === hash('sha256', $idempotencyKey), 'persistent idempotency authority must use the SHA-256 digest');
jobPersistenceExpect(!array_key_exists('idempotency_key', $row), 'raw idempotency key must not be a persisted Job column');

$reloaded = new PersistentJobService(new WpdbJobPersistenceGateway($database), $scope);
$reloaded->registerType($type);
$afterRestart = $reloaded->get($job->id);
jobPersistenceExpect($afterRestart !== null && $afterRestart->payload['value'] === 7, 'Job payload/state must survive service re-instantiation');

$reloaded->start($job->id);
$running = $reloaded->get($job->id);
jobPersistenceExpect($running !== null && $running->state === JobState::Running && $running->attempts === 1, 'persistent Job start must advance state and attempt count');
$reloaded->fail($job->id, JobFailureClass::TransientLocal);
$retry = $reloaded->get($job->id);
jobPersistenceExpect($retry !== null && $retry->state === JobState::RetryWait && $retry->retryAfter !== null, 'retryable failure must persist retry_wait state');
$reloaded->start($job->id);
$reloaded->succeed($job->id);
$completed = $reloaded->get($job->id);
jobPersistenceExpect($completed !== null && $completed->state === JobState::Succeeded && $completed->attempts === 2, 'successful retry must persist terminal success');

$casJob = $service->enqueue($type->key, ['value' => 8], $context, 'provider:event:fixture-cas');
$staleRow = $gateway->find($scope, $casJob->id);
jobPersistenceExpect($staleRow !== null, 'CAS fixture Job must exist');
$staleSnapshot = (new JobRowCodec())->decode($staleRow);
$service->start($casJob->id);
$staleSnapshot->record->start();
$staleEncoded = (new JobRowCodec())->encode($staleSnapshot->record, $staleSnapshot->idempotencyHash, $staleSnapshot->revision + 1);
jobPersistenceExpect(!$gateway->updateIfRevision($scope, $casJob->id, $staleSnapshot->revision, $staleEncoded), 'stale Job revision must be rejected by CAS');

$leaseJob = $service->enqueue($type->key, ['value' => 9], $context, 'provider:event:fixture-lease');
$attempts = new WpdbJobAttemptStore($database);
$t0 = new DateTimeImmutable('2026-08-29T18:00:00+00:00', new DateTimeZone('UTC'));
$leaseA = $attempts->lease($scope, $leaseJob->id, 'worker-a', 30, $t0);
jobPersistenceExpect($leaseA !== null && $leaseA->attemptNumber === 1, 'first worker must acquire attempt 1 lease');
jobPersistenceExpect($attempts->lease($scope, $leaseJob->id, 'worker-b', 30, $t0) === null, 'second worker must not acquire an unexpired Job lease');

$leaseRow = $database->getRow($database->prepare(
    "SELECT lease_token_hash FROM `{$tables->attempts}` WHERE network_id = %d AND site_id = %d AND attempt_id = %s",
    $scope->networkId,
    $scope->siteId,
    $leaseA->attemptId,
));
jobPersistenceExpect($leaseRow !== null && ($leaseRow['lease_token_hash'] ?? '') === hash('sha256', $leaseA->token), 'database must persist only the lease-token hash');
jobPersistenceExpect(($leaseRow['lease_token_hash'] ?? '') !== $leaseA->token, 'raw lease token must not be persisted');

$t10 = $t0->modify('+10 seconds');
$renewed = $attempts->heartbeat($leaseA, 30, $t10);
jobPersistenceExpect($renewed !== null && $renewed->expiresAt > $leaseA->expiresAt, 'heartbeat must extend a valid lease');
jobPersistenceExpect($attempts->checkpoint($renewed, 1, ['cursor' => 10], $t10->modify('+1 second')), 'first checkpoint must persist');
jobPersistenceExpect(!$attempts->checkpoint($renewed, 1, ['cursor' => 11], $t10->modify('+2 seconds')), 'checkpoint sequence must be monotonic');
jobPersistenceExpect($attempts->checkpoint($renewed, 2, ['cursor' => 20], $t10->modify('+3 seconds')), 'higher checkpoint sequence must persist');
jobPersistenceExpect($attempts->complete($renewed, JobAttemptState::Succeeded, null, $t10->modify('+4 seconds')), 'valid leased worker must complete its attempt');
jobPersistenceExpect(!$attempts->complete($renewed, JobAttemptState::Succeeded, null, $t10->modify('+5 seconds')), 'terminal attempt must reject duplicate completion');

$expiredJob = $service->enqueue($type->key, ['value' => 10], $context, 'provider:event:fixture-expired');
$expiredLease = $attempts->lease($scope, $expiredJob->id, 'worker-expired', 5, $t0);
jobPersistenceExpect($expiredLease !== null, 'expiry fixture must acquire a lease');
$t6 = $t0->modify('+6 seconds');
jobPersistenceExpect($attempts->heartbeat($expiredLease, 30, $t6) === null, 'expired worker must not heartbeat a stale lease');
jobPersistenceExpect(!$attempts->complete($expiredLease, JobAttemptState::Succeeded, null, $t6), 'expired worker must not commit a stale completion');
jobPersistenceExpect($attempts->reclaimExpired($scope, 100, $t6) >= 1, 'expired attempts must be reclaimable as abandoned');
$replacement = $attempts->lease($scope, $expiredJob->id, 'worker-replacement', 30, $t6);
jobPersistenceExpect($replacement !== null && $replacement->attemptNumber === 2, 'replacement worker must receive a fresh attempt number after expiry');
jobPersistenceExpect($attempts->complete($replacement, JobAttemptState::Failed, JobFailureClass::TransientLocal, $t6->modify('+1 second')), 'replacement attempt must persist terminal failure evidence');

fwrite(STDOUT, "WPEssential real WordPress durable JobService persistence/lease integration PASS\n");
