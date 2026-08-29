<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use DateTimeImmutable;
use WPEssential\Platform\Jobs\JobAttemptState;
use WPEssential\Platform\Jobs\JobFailureClass;
use WPEssential\Platform\Jobs\JobLease;
use WPEssential\Platform\Jobs\JobScope;

interface JobAttemptStoreInterface
{
    public function lease(JobScope $scope, string $jobId, string $workerId, int $leaseSeconds, ?DateTimeImmutable $now = null): ?JobLease;

    public function heartbeat(JobLease $lease, int $leaseSeconds, ?DateTimeImmutable $now = null): ?JobLease;

    /** @param array<string, mixed> $checkpoint */
    public function checkpoint(JobLease $lease, int $sequence, array $checkpoint, ?DateTimeImmutable $now = null): bool;

    public function complete(JobLease $lease, JobAttemptState $state, ?JobFailureClass $failure = null, ?DateTimeImmutable $now = null): bool;

    public function reclaimExpired(JobScope $scope, int $limit = 100, ?DateTimeImmutable $now = null): int;
}
