<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;


if (!defined('ABSPATH')) {
    exit;
}

use DateTimeImmutable;
use LogicException;

final class JobRecord
{
    public JobState $state = JobState::Available;
    public int $attempts = 0;
    public ?JobFailureClass $lastFailure = null;
    public ?DateTimeImmutable $retryAfter = null;

    /** @param array<string, mixed> $payload */
    public function __construct(
        public readonly string $id,
        public readonly string $typeKey,
        public readonly int $siteId,
        public readonly ?int $networkId,
        public readonly array $payload,
        public readonly ?string $idempotencyKey,
        public readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {}

    public function start(): void
    {
        if (!in_array($this->state, [JobState::Available, JobState::RetryWait], true)) {
            throw new LogicException(sprintf('Job cannot start from state "%s".', $this->state->value));
        }
        $this->attempts++;
        $this->retryAfter = null;
        $this->state = JobState::Running;
    }

    public function succeed(): void
    {
        if ($this->state !== JobState::Running) {
            throw new LogicException('Only a running job can succeed.');
        }
        $this->state = JobState::Succeeded;
    }

    public function fail(JobFailureClass $failure, RetryPolicy $retryPolicy, ?DateTimeImmutable $now = null): void
    {
        if ($this->state !== JobState::Running) {
            throw new LogicException('Only a running job can fail.');
        }
        $this->lastFailure = $failure;

        if ($failure->requiresReconciliation()) {
            $this->state = JobState::Blocked;
            return;
        }

        if ($failure->isRetryable() && $this->attempts < $retryPolicy->maxAttempts) {
            $now ??= new DateTimeImmutable();
            $this->retryAfter = $now->modify(sprintf('+%d seconds', $retryPolicy->delayAfterAttempt($this->attempts)));
            $this->state = JobState::RetryWait;
            return;
        }

        $this->state = JobState::FailedFinal;
    }

    public function requestCancellation(bool $supportsCancellation): void
    {
        if (!$supportsCancellation) {
            throw new LogicException('Job type does not support cancellation.');
        }
        if ($this->state->isTerminal()) {
            return;
        }
        if ($this->state === JobState::Running) {
            $this->state = JobState::CancelRequested;
            return;
        }
        $this->state = JobState::Cancelled;
    }

    public function confirmCancellation(): void
    {
        if ($this->state !== JobState::CancelRequested) {
            throw new LogicException('Cooperative cancellation can only complete after cancel_requested.');
        }
        $this->state = JobState::Cancelled;
    }
}
