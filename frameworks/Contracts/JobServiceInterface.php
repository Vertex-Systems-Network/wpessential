<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Jobs\JobFailureClass;
use WPEssential\Platform\Jobs\JobRecord;
use WPEssential\Platform\Jobs\JobType;

interface JobServiceInterface
{
    public function registerType(JobType $type): void;

    /** @param array<string, mixed> $payload */
    public function enqueue(string $typeKey, array $payload, ExecutionContext $context, ?string $idempotencyKey = null): JobRecord;

    public function get(string $jobId): ?JobRecord;

    public function start(string $jobId): void;

    public function succeed(string $jobId): void;

    public function fail(string $jobId, JobFailureClass $failure): void;

    public function cancel(string $jobId): void;

    public function confirmCancellation(string $jobId): void;
}
