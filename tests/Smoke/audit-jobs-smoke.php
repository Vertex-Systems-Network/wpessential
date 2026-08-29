<?php

declare(strict_types=1);

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

use WPEssential\Platform\Audit\AuditOutcome;
use WPEssential\Platform\Audit\AuditRecord;
use WPEssential\Platform\Audit\InMemoryAuditLogger;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Jobs\InMemoryJobService;
use WPEssential\Platform\Jobs\JobFailureClass;
use WPEssential\Platform\Jobs\JobIdempotencyMode;
use WPEssential\Platform\Jobs\JobState;
use WPEssential\Platform\Jobs\JobType;
use WPEssential\Platform\Jobs\RetryPolicy;

function trancheThreeExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$context = new ExecutionContext(new Principal(7), 2, ExecutionChannel::Internal, 1, 'corr-1');
$audit = new AuditRecord(
    '123e4567-e89b-42d3-a456-426614174111',
    $context,
    3,
    'wpessential/fields/update',
    AuditOutcome::Success,
    'field-group',
    'fg1',
    metadata: [
        'changed' => ['label'],
        'api_token' => 'super-secret',
        'nested' => ['Authorization' => 'Bearer nope', 'safe' => 'yes'],
    ],
);
$logger = new InMemoryAuditLogger();
$logger->record($audit);
$encodedMetadata = json_encode($logger->all()[0]->metadata);
trancheThreeExpect(!str_contains((string) $encodedMetadata, 'super-secret'), 'Audit metadata must not retain token values.');
trancheThreeExpect(!str_contains((string) $encodedMetadata, 'Bearer nope'), 'Audit metadata must not retain Authorization values.');
trancheThreeExpect($audit->context->correlationId === 'corr-1', 'Audit must preserve correlation identity.');
try {
    $logger->record($audit);
    trancheThreeExpect(false, 'Committed Audit UUID must be append-only.');
} catch (RuntimeException) {
    // expected
}

$jobs = new InMemoryJobService();
$type = new JobType(
    'wpessential/sync/pull',
    41,
    'wpessential/sync/pull',
    JobIdempotencyMode::StableKey,
    new RetryPolicy(2, 10, 2.0, 60),
    true,
    ['network_io'],
);
$jobs->registerType($type);

$first = $jobs->enqueue($type->key, ['cursor' => 1], $context, 'logical-op-1');
$duplicate = $jobs->enqueue($type->key, ['cursor' => 999], $context, 'logical-op-1');
trancheThreeExpect($first->id === $duplicate->id, 'Stable idempotency key must deduplicate logical enqueue.');

$jobs->start($first->id);
trancheThreeExpect($first->attempts === 1 && $first->state === JobState::Running, 'Job start must create an execution attempt.');
$jobs->fail($first->id, JobFailureClass::ProviderTransient);
trancheThreeExpect($first->state === JobState::RetryWait, 'Retryable failure below ceiling must enter retry_wait.');
$jobs->start($first->id);
$jobs->fail($first->id, JobFailureClass::ProviderTransient);
trancheThreeExpect($first->state === JobState::FailedFinal, 'Retry ceiling must terminate repeated retryable failure.');

$unknown = $jobs->enqueue($type->key, [], $context, 'logical-op-unknown');
$jobs->start($unknown->id);
$jobs->fail($unknown->id, JobFailureClass::UnknownExternalOutcome);
trancheThreeExpect($unknown->state === JobState::Blocked, 'Unknown external outcome must block for reconciliation instead of blind retry.');

$cancel = $jobs->enqueue($type->key, [], $context, 'logical-op-cancel');
$jobs->start($cancel->id);
$jobs->cancel($cancel->id);
trancheThreeExpect($cancel->state === JobState::CancelRequested, 'Running cancellation must be cooperative.');
$jobs->confirmCancellation($cancel->id);
trancheThreeExpect($cancel->state === JobState::Cancelled, 'Cooperative cancellation must explicitly confirm completion.');

$pending = $jobs->enqueue($type->key, [], $context, 'logical-op-pending');
$jobs->cancel($pending->id);
trancheThreeExpect($pending->state === JobState::Cancelled, 'Pending cancellable job may cancel before execution.');

try {
    $jobs->enqueue($type->key, [], $context, null);
    trancheThreeExpect(false, 'Stable-key job must reject missing idempotency identity.');
} catch (InvalidArgumentException) {
    // expected
}

fwrite(STDOUT, "WPEssential audit/jobs smoke PASS\n");
