<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Jobs\JobScope;

interface JobPersistenceGatewayInterface
{
    /** @return array<string, scalar|null>|null */
    public function find(JobScope $scope, string $jobId): ?array;

    /** @return array<string, scalar|null>|null */
    public function findByIdempotency(JobScope $scope, string $typeKey, string $idempotencyHash): ?array;

    /** @param array<string, scalar|null> $row */
    public function insert(JobScope $scope, array $row): void;

    /** @param array<string, scalar|null> $row */
    public function updateIfRevision(JobScope $scope, string $jobId, int $expectedRevision, array $row): bool;
}
