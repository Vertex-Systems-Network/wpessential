<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;

use InvalidArgumentException;

final readonly class JobType
{
    /** @param list<string> $resourceClasses */
    public function __construct(
        public string $key,
        public int $ownerSurfaceId,
        public string $handlerAbility,
        public JobIdempotencyMode $idempotencyMode,
        public RetryPolicy $retryPolicy = new RetryPolicy(),
        public bool $supportsCancellation = false,
        public array $resourceClasses = [],
    ) {
        if (!preg_match('#^wpessential/[a-z0-9][a-z0-9-]*/[a-z0-9][a-z0-9-]*$#', $this->key)) {
            throw new InvalidArgumentException('Job type key must use wpessential/<domain>/<job>.');
        }
        if ($this->ownerSurfaceId < 1 || $this->ownerSurfaceId > 56) {
            throw new InvalidArgumentException('Job type owner must be a canonical surface id 1..56.');
        }
        if (!preg_match('#^wpessential/[a-z0-9][a-z0-9-]*/[a-z0-9][a-z0-9-]*$#', $this->handlerAbility)) {
            throw new InvalidArgumentException('Job handler must reference a stable WPEssential Ability key.');
        }

        $validResources = [
            'db_read', 'db_write', 'cpu', 'filesystem_io', 'network_io',
            'provider_rate_limited', 'memory_heavy', 'destructive_exclusive',
        ];
        foreach ($this->resourceClasses as $resourceClass) {
            if (!is_string($resourceClass) || !in_array($resourceClass, $validResources, true)) {
                throw new InvalidArgumentException('Unknown JobService resource class.');
            }
        }
        if (count($this->resourceClasses) !== count(array_unique($this->resourceClasses))) {
            throw new InvalidArgumentException('JobService resource classes must be unique.');
        }
    }
}
