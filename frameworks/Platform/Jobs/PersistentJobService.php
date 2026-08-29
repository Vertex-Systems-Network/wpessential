<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\JobPersistenceGatewayInterface;
use WPEssential\Contracts\JobServiceInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final class PersistentJobService implements JobServiceInterface
{
    /** @var array<string, JobType> */
    private array $types = [];

    public function __construct(
        private readonly JobPersistenceGatewayInterface $gateway,
        private readonly JobScope $scope,
        private readonly JobRowCodec $codec = new JobRowCodec(),
    ) {}

    public function registerType(JobType $type): void
    {
        if (isset($this->types[$type->key])) {
            throw new RuntimeException(sprintf('Job type "%s" is already registered.', $type->key));
        }
        $this->types[$type->key] = $type;
    }

    public function enqueue(string $typeKey, array $payload, ExecutionContext $context, ?string $idempotencyKey = null): JobRecord
    {
        $this->assertContextScope($context);
        $type = $this->type($typeKey);
        $idempotencyHash = null;

        if ($type->idempotencyMode === JobIdempotencyMode::StableKey) {
            $idempotencyKey = trim((string) $idempotencyKey);
            if ($idempotencyKey === '') {
                throw new InvalidArgumentException('Stable-key job types require a non-empty idempotency key.');
            }
            if (strlen($idempotencyKey) > 191) {
                throw new InvalidArgumentException('Job idempotency key is too long.');
            }
            $idempotencyHash = hash('sha256', $idempotencyKey);
            $existing = $this->gateway->findByIdempotency($this->scope, $typeKey, $idempotencyHash);
            if ($existing !== null) {
                return $this->codec->decode($existing)->record;
            }
        }

        $record = new JobRecord(
            id: self::uuid(),
            typeKey: $typeKey,
            siteId: $this->scope->siteId,
            networkId: $this->scope->networkId,
            payload: $payload,
            idempotencyKey: $idempotencyKey,
        );

        try {
            $this->gateway->insert($this->scope, $this->codec->encode($record, $idempotencyHash, 1));
            return $record;
        } catch (Throwable $exception) {
            if ($idempotencyHash !== null) {
                $existing = $this->gateway->findByIdempotency($this->scope, $typeKey, $idempotencyHash);
                if ($existing !== null) {
                    return $this->codec->decode($existing)->record;
                }
            }
            throw new RuntimeException('Persistent Job enqueue failed.', 0, $exception);
        }
    }

    public function get(string $jobId): ?JobRecord
    {
        $row = $this->gateway->find($this->scope, $jobId);
        return $row === null ? null : $this->codec->decode($row)->record;
    }

    public function start(string $jobId): void
    {
        $this->mutate($jobId, static function (JobRecord $record): void {
            $record->start();
        });
    }

    public function succeed(string $jobId): void
    {
        $this->mutate($jobId, static function (JobRecord $record): void {
            $record->succeed();
        });
    }

    public function fail(string $jobId, JobFailureClass $failure): void
    {
        $this->mutate($jobId, function (JobRecord $record) use ($failure): void {
            $record->fail($failure, $this->type($record->typeKey)->retryPolicy);
        });
    }

    public function cancel(string $jobId): void
    {
        $this->mutate($jobId, function (JobRecord $record): void {
            $record->requestCancellation($this->type($record->typeKey)->supportsCancellation);
        });
    }

    public function confirmCancellation(string $jobId): void
    {
        $this->mutate($jobId, static function (JobRecord $record): void {
            $record->confirmCancellation();
        });
    }

    /** @param callable(JobRecord):mixed $mutation */
    private function mutate(string $jobId, callable $mutation): void
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $row = $this->gateway->find($this->scope, $jobId);
            if ($row === null) {
                throw new RuntimeException(sprintf('Job "%s" is not persisted in the active scope.', $jobId));
            }

            $snapshot = $this->codec->decode($row);
            $mutation($snapshot->record);
            $nextRevision = $snapshot->revision + 1;
            $encoded = $this->codec->encode($snapshot->record, $snapshot->idempotencyHash, $nextRevision);

            if ($this->gateway->updateIfRevision($this->scope, $jobId, $snapshot->revision, $encoded)) {
                return;
            }
        }

        throw new RuntimeException(sprintf('Job "%s" mutation lost a concurrent revision race.', $jobId));
    }

    private function type(string $typeKey): JobType
    {
        $type = $this->types[$typeKey] ?? null;
        if (!$type instanceof JobType) {
            throw new RuntimeException(sprintf('Job type "%s" is not registered.', $typeKey));
        }
        return $type;
    }

    private function assertContextScope(ExecutionContext $context): void
    {
        if ($context->networkId !== $this->scope->networkId || $context->siteId !== $this->scope->siteId) {
            throw new InvalidArgumentException('Job execution context does not match the persistent JobService scope.');
        }
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
