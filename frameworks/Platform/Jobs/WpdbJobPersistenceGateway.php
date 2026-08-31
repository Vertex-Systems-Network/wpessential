<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\JobPersistenceGatewayInterface;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class WpdbJobPersistenceGateway implements JobPersistenceGatewayInterface
{
    private readonly JobTableNames $tables;

    public function __construct(private readonly DatabaseAdapterInterface $database)
    {
        $this->tables = new JobTableNames($database);
    }

    public function find(JobScope $scope, string $jobId): ?array
    {
        return $this->database->getRow($this->database->prepare(
            "SELECT network_id, site_id, id, type_key, state, payload_json, idempotency_hash, attempts, last_failure, retry_after, revision, created_at FROM `{$this->tables->jobs}` WHERE network_id = %d AND site_id = %d AND id = %s",
            $scope->networkId,
            $scope->siteId,
            $jobId,
        ));
    }

    public function findByIdempotency(JobScope $scope, string $typeKey, string $idempotencyHash): ?array
    {
        return $this->database->getRow($this->database->prepare(
            "SELECT network_id, site_id, id, type_key, state, payload_json, idempotency_hash, attempts, last_failure, retry_after, revision, created_at FROM `{$this->tables->jobs}` WHERE network_id = %d AND site_id = %d AND type_key = %s AND idempotency_hash = %s",
            $scope->networkId,
            $scope->siteId,
            $typeKey,
            $idempotencyHash,
        ));
    }

    public function insert(JobScope $scope, array $row): void
    {
        $now = gmdate('Y-m-d H:i:s.u');
        $this->database->insert($this->tables->jobs, [
            'network_id' => $scope->networkId,
            'site_id' => $scope->siteId,
            'id' => (string) ($row['id'] ?? ''),
            'type_key' => (string) ($row['type_key'] ?? ''),
            'state' => (string) ($row['state'] ?? ''),
            'payload_json' => (string) ($row['payload_json'] ?? ''),
            'idempotency_hash' => $row['idempotency_hash'] ?? null,
            'attempts' => (int) ($row['attempts'] ?? 0),
            'last_failure' => $row['last_failure'] ?? null,
            'retry_after' => $row['retry_after'] ?? null,
            'revision' => (int) ($row['revision'] ?? 1),
            'created_at' => (string) ($row['created_at'] ?? $now),
            'updated_at' => $now,
        ], ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s']);
    }

    public function updateIfRevision(JobScope $scope, string $jobId, int $expectedRevision, array $row): bool
    {
        $affected = $this->database->query($this->database->prepare(
            "UPDATE `{$this->tables->jobs}` SET state = %s, attempts = %d, last_failure = %s, retry_after = %s, revision = %d, updated_at = UTC_TIMESTAMP(6) WHERE network_id = %d AND site_id = %d AND id = %s AND revision = %d",
            (string) ($row['state'] ?? ''),
            (int) ($row['attempts'] ?? 0),
            $row['last_failure'] ?? null,
            $row['retry_after'] ?? null,
            (int) ($row['revision'] ?? ($expectedRevision + 1)),
            $scope->networkId,
            $scope->siteId,
            $jobId,
            $expectedRevision,
        ));

        return $affected === 1;
    }
}
