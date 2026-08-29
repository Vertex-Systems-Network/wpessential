<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\JobServiceInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final class InMemoryJobService implements JobServiceInterface
{
    /** @var array<string, JobType> */
    private array $types = [];

    /** @var array<string, JobRecord> */
    private array $jobs = [];

    /** @var array<string, string> dedupe key => job id */
    private array $idempotencyIndex = [];

    public function registerType(JobType $type): void
    {
        if (isset($this->types[$type->key])) {
            throw new RuntimeException(sprintf('Job type "%s" is already registered.', $type->key));
        }
        $this->types[$type->key] = $type;
    }

    public function enqueue(string $typeKey, array $payload, ExecutionContext $context, ?string $idempotencyKey = null): JobRecord
    {
        $type = $this->types[$typeKey] ?? null;
        if (!$type instanceof JobType) {
            throw new RuntimeException(sprintf('Job type "%s" is not registered.', $typeKey));
        }

        $dedupeKey = null;
        if ($type->idempotencyMode === JobIdempotencyMode::StableKey) {
            $idempotencyKey = trim((string) $idempotencyKey);
            if ($idempotencyKey === '') {
                throw new InvalidArgumentException('Stable-key job types require a non-empty idempotency key.');
            }
            if (strlen($idempotencyKey) > 191) {
                throw new InvalidArgumentException('Job idempotency key is too long.');
            }
            $dedupeKey = implode(':', [
                (string) ($context->networkId ?? 0),
                (string) $context->siteId,
                $typeKey,
                hash('sha256', $idempotencyKey),
            ]);
            $existingId = $this->idempotencyIndex[$dedupeKey] ?? null;
            if ($existingId !== null) {
                return $this->jobs[$existingId];
            }
        }

        $record = new JobRecord(
            id: self::uuid(),
            typeKey: $typeKey,
            siteId: $context->siteId,
            networkId: $context->networkId,
            payload: $payload,
            idempotencyKey: $idempotencyKey,
        );
        $this->jobs[$record->id] = $record;
        if ($dedupeKey !== null) {
            $this->idempotencyIndex[$dedupeKey] = $record->id;
        }

        return $record;
    }

    public function get(string $jobId): ?JobRecord
    {
        return $this->jobs[$jobId] ?? null;
    }

    public function start(string $jobId): void
    {
        $this->job($jobId)->start();
    }

    public function succeed(string $jobId): void
    {
        $this->job($jobId)->succeed();
    }

    public function fail(string $jobId, JobFailureClass $failure): void
    {
        $record = $this->job($jobId);
        $record->fail($failure, $this->type($record->typeKey)->retryPolicy);
    }

    public function cancel(string $jobId): void
    {
        $record = $this->job($jobId);
        $record->requestCancellation($this->type($record->typeKey)->supportsCancellation);
    }

    public function confirmCancellation(string $jobId): void
    {
        $this->job($jobId)->confirmCancellation();
    }

    private function job(string $jobId): JobRecord
    {
        $job = $this->jobs[$jobId] ?? null;
        if (!$job instanceof JobRecord) {
            throw new RuntimeException(sprintf('Job "%s" is not registered.', $jobId));
        }
        return $job;
    }

    private function type(string $typeKey): JobType
    {
        $type = $this->types[$typeKey] ?? null;
        if (!$type instanceof JobType) {
            throw new RuntimeException(sprintf('Job type "%s" is not registered.', $typeKey));
        }
        return $type;
    }

    private static function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20));
    }
}
