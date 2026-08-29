<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;

if (!defined('ABSPATH')) {
    exit;
}

use DateTimeImmutable;

final readonly class JobLease
{
    public function __construct(
        public JobScope $scope,
        public string $attemptId,
        public string $jobId,
        public int $attemptNumber,
        public string $workerId,
        public string $token,
        public DateTimeImmutable $expiresAt,
        public int $checkpointSequence = 0,
    ) {}
}
