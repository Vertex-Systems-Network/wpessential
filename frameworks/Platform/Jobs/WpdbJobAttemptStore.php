<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;

if (!defined('ABSPATH')) {
    exit;
}

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\JobAttemptStoreInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class WpdbJobAttemptStore implements JobAttemptStoreInterface
{
    private readonly JobTableNames $tables;

    public function __construct(private readonly DatabaseAdapterInterface $database)
    {
        $this->tables = new JobTableNames($database);
    }

    public function lease(JobScope $scope, string $jobId, string $workerId, int $leaseSeconds, ?DateTimeImmutable $now = null): ?JobLease
    {
        $this->assertJobId($jobId);
        $workerId = trim($workerId);
        if ($workerId === '' || strlen($workerId) > 191 || preg_match('/^[A-Za-z0-9._:-]+$/', $workerId) !== 1) {
            throw new InvalidArgumentException('Job worker id is invalid.');
        }
        $this->assertLeaseSeconds($leaseSeconds);

        $now = $this->utc($now);
        $expiresAt = $now->modify(sprintf('+%d seconds', $leaseSeconds));
        $nowSql = $this->sqlTime($now);
        $expiresSql = $this->sqlTime($expiresAt);

        $this->database->beginTransaction();
        try {
            $job = $this->database->getRow($this->database->prepare(
                "SELECT id FROM `{$this->tables->jobs}` WHERE network_id = %d AND site_id = %d AND id = %s FOR UPDATE",
                $scope->networkId,
                $scope->siteId,
                $jobId,
            ));
            if ($job === null) {
                $this->database->rollBack();
                throw new RuntimeException(sprintf('Cannot lease unknown Job "%s".', $jobId));
            }

            $this->database->query($this->database->prepare(
                "UPDATE `{$this->tables->attempts}` SET state = %s, completed_at = %s, updated_at = %s WHERE network_id = %d AND site_id = %d AND job_id = %s AND state = %s AND lease_expires_at <= %s",
                JobAttemptState::Abandoned->value,
                $nowSql,
                $nowSql,
                $scope->networkId,
                $scope->siteId,
                $jobId,
                JobAttemptState::Leased->value,
                $nowSql,
            ));

            $active = $this->database->getRow($this->database->prepare(
                "SELECT attempt_id FROM `{$this->tables->attempts}` WHERE network_id = %d AND site_id = %d AND job_id = %s AND state = %s AND lease_expires_at > %s ORDER BY attempt_no DESC LIMIT 1",
                $scope->networkId,
                $scope->siteId,
                $jobId,
                JobAttemptState::Leased->value,
                $nowSql,
            ));
            if ($active !== null) {
                $this->database->commit();
                return null;
            }

            $attemptNumber = 1 + (int) ($this->database->getVar($this->database->prepare(
                "SELECT COALESCE(MAX(attempt_no), 0) FROM `{$this->tables->attempts}` WHERE network_id = %d AND site_id = %d AND job_id = %s",
                $scope->networkId,
                $scope->siteId,
                $jobId,
            )) ?? 0);

            $attemptId = self::uuid();
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);

            $this->database->insert($this->tables->attempts, [
                'network_id' => $scope->networkId,
                'site_id' => $scope->siteId,
                'attempt_id' => $attemptId,
                'job_id' => $jobId,
                'attempt_no' => $attemptNumber,
                'state' => JobAttemptState::Leased->value,
                'worker_id' => $workerId,
                'lease_token_hash' => $tokenHash,
                'lease_acquired_at' => $nowSql,
                'lease_expires_at' => $expiresSql,
                'heartbeat_at' => $nowSql,
                'checkpoint_seq' => 0,
                'checkpoint_json' => null,
                'failure_class' => null,
                'completed_at' => null,
                'created_at' => $nowSql,
                'updated_at' => $nowSql,
            ], ['%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s']);

            $this->database->commit();

            return new JobLease(
                scope: $scope,
                attemptId: $attemptId,
                jobId: $jobId,
                attemptNumber: $attemptNumber,
                workerId: $workerId,
                token: $token,
                expiresAt: $expiresAt,
            );
        } catch (Throwable $exception) {
            $this->safeRollback();
            throw $exception;
        }
    }

    public function heartbeat(JobLease $lease, int $leaseSeconds, ?DateTimeImmutable $now = null): ?JobLease
    {
        $this->assertLeaseSeconds($leaseSeconds);
        $now = $this->utc($now);
        $expiresAt = $now->modify(sprintf('+%d seconds', $leaseSeconds));
        $nowSql = $this->sqlTime($now);

        $affected = $this->database->query($this->database->prepare(
            "UPDATE `{$this->tables->attempts}` SET heartbeat_at = %s, lease_expires_at = %s, updated_at = %s WHERE network_id = %d AND site_id = %d AND attempt_id = %s AND job_id = %s AND worker_id = %s AND lease_token_hash = %s AND state = %s AND lease_expires_at > %s",
            $nowSql,
            $this->sqlTime($expiresAt),
            $nowSql,
            $lease->scope->networkId,
            $lease->scope->siteId,
            $lease->attemptId,
            $lease->jobId,
            $lease->workerId,
            hash('sha256', $lease->token),
            JobAttemptState::Leased->value,
            $nowSql,
        ));

        if ($affected !== 1) {
            return null;
        }

        return new JobLease(
            scope: $lease->scope,
            attemptId: $lease->attemptId,
            jobId: $lease->jobId,
            attemptNumber: $lease->attemptNumber,
            workerId: $lease->workerId,
            token: $lease->token,
            expiresAt: $expiresAt,
            checkpointSequence: $lease->checkpointSequence,
        );
    }

    public function checkpoint(JobLease $lease, int $sequence, array $checkpoint, ?DateTimeImmutable $now = null): bool
    {
        if ($sequence < 1) {
            throw new InvalidArgumentException('Checkpoint sequence must be positive.');
        }
        try {
            $json = json_encode($checkpoint, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to encode Job checkpoint.', 0, $exception);
        }

        $now = $this->utc($now);
        $nowSql = $this->sqlTime($now);
        $affected = $this->database->query($this->database->prepare(
            "UPDATE `{$this->tables->attempts}` SET checkpoint_seq = %d, checkpoint_json = %s, updated_at = %s WHERE network_id = %d AND site_id = %d AND attempt_id = %s AND job_id = %s AND worker_id = %s AND lease_token_hash = %s AND state = %s AND lease_expires_at > %s AND checkpoint_seq < %d",
            $sequence,
            $json,
            $nowSql,
            $lease->scope->networkId,
            $lease->scope->siteId,
            $lease->attemptId,
            $lease->jobId,
            $lease->workerId,
            hash('sha256', $lease->token),
            JobAttemptState::Leased->value,
            $nowSql,
            $sequence,
        ));

        return $affected === 1;
    }

    public function complete(JobLease $lease, JobAttemptState $state, ?JobFailureClass $failure = null, ?DateTimeImmutable $now = null): bool
    {
        if (!$state->isTerminal()) {
            throw new InvalidArgumentException('A leased attempt cannot complete into the leased state.');
        }
        if ($state === JobAttemptState::Failed && $failure === null) {
            throw new InvalidArgumentException('Failed Job attempts require a failure class.');
        }
        if ($state !== JobAttemptState::Failed && $failure !== null) {
            throw new InvalidArgumentException('Failure class is only valid for failed Job attempts.');
        }

        $now = $this->utc($now);
        $nowSql = $this->sqlTime($now);
        $failureValue = $failure?->value ?? '';
        $affected = $this->database->query($this->database->prepare(
            "UPDATE `{$this->tables->attempts}` SET state = %s, failure_class = NULLIF(%s, ''), completed_at = %s, updated_at = %s WHERE network_id = %d AND site_id = %d AND attempt_id = %s AND job_id = %s AND worker_id = %s AND lease_token_hash = %s AND state = %s AND lease_expires_at > %s",
            $state->value,
            $failureValue,
            $nowSql,
            $nowSql,
            $lease->scope->networkId,
            $lease->scope->siteId,
            $lease->attemptId,
            $lease->jobId,
            $lease->workerId,
            hash('sha256', $lease->token),
            JobAttemptState::Leased->value,
            $nowSql,
        ));

        return $affected === 1;
    }

    public function reclaimExpired(JobScope $scope, int $limit = 100, ?DateTimeImmutable $now = null): int
    {
        if ($limit < 1 || $limit > 1000) {
            throw new InvalidArgumentException('Expired Job reclaim limit must be between 1 and 1000.');
        }
        $now = $this->utc($now);
        $nowSql = $this->sqlTime($now);
        $affected = $this->database->query($this->database->prepare(
            "UPDATE `{$this->tables->attempts}` SET state = %s, completed_at = %s, updated_at = %s WHERE network_id = %d AND site_id = %d AND state = %s AND lease_expires_at <= %s ORDER BY lease_expires_at ASC LIMIT %d",
            JobAttemptState::Abandoned->value,
            $nowSql,
            $nowSql,
            $scope->networkId,
            $scope->siteId,
            JobAttemptState::Leased->value,
            $nowSql,
            $limit,
        ));

        return is_int($affected) ? $affected : 0;
    }

    private function assertJobId(string $jobId): void
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $jobId) !== 1) {
            throw new InvalidArgumentException('Job lease requires a canonical UUIDv4 Job ID.');
        }
    }

    private function assertLeaseSeconds(int $leaseSeconds): void
    {
        if ($leaseSeconds < 5 || $leaseSeconds > 3600) {
            throw new InvalidArgumentException('Job lease duration must be between 5 and 3600 seconds.');
        }
    }

    private function utc(?DateTimeImmutable $now): DateTimeImmutable
    {
        return ($now ?? new DateTimeImmutable('now', new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('UTC'));
    }

    private function sqlTime(DateTimeImmutable $time): string
    {
        return $time->format('Y-m-d H:i:s.u');
    }

    private function safeRollback(): void
    {
        try {
            $this->database->rollBack();
        } catch (Throwable) {
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
