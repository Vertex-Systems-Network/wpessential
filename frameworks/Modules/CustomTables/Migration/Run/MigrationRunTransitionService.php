<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Run;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;

final readonly class MigrationRunTransitionService
{
    public function __construct(private MigrationRunRepositoryInterface $repository)
    {
    }

    public function transition(
        string $runId,
        int $expectedStateRevision,
        MigrationRunState $nextState,
    ): MigrationRun {
        if ($expectedStateRevision < 1) {
            throw new InvalidArgumentException('Custom Tables migration run expected state revision must be positive.');
        }

        $current = $this->repository->get($runId);
        if ($current === null) {
            throw new RuntimeException('Custom Tables migration run was not found.');
        }

        if ($current->stateRevision !== $expectedStateRevision) {
            throw new RuntimeException('Custom Tables migration run state revision is stale.');
        }

        $next = $current->transition($nextState);

        return $this->repository->compareAndSwap($next, $expectedStateRevision);
    }
}
