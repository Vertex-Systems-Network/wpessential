<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Run;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;

final class InMemoryMigrationRunRepository implements MigrationRunRepositoryInterface
{
    /** @var array<string,MigrationRun> */
    private array $runs = [];

    public function create(MigrationRun $run): MigrationRun
    {
        $key = strtolower($run->id);
        if (isset($this->runs[$key])) {
            throw new RuntimeException('Custom Tables migration run already exists.');
        }

        $this->runs[$key] = $run;

        return $run;
    }

    public function get(string $id): ?MigrationRun
    {
        return $this->runs[strtolower($id)] ?? null;
    }

    public function compareAndSwap(MigrationRun $next, int $expectedStateRevision): MigrationRun
    {
        if ($expectedStateRevision < 1) {
            throw new InvalidArgumentException('Custom Tables migration run expected state revision must be positive.');
        }

        $key = strtolower($next->id);
        $stored = $this->runs[$key] ?? null;
        if (!$stored instanceof MigrationRun) {
            throw new RuntimeException('Custom Tables migration run does not exist.');
        }

        if ($stored->stateRevision !== $expectedStateRevision) {
            throw new RuntimeException('Custom Tables migration run state revision conflict.');
        }

        if ($next->stateRevision !== $expectedStateRevision + 1) {
            throw new InvalidArgumentException('Custom Tables migration run replacement state revision is invalid.');
        }

        if ($stored->planFingerprint !== $next->planFingerprint
            || $stored->tableKey !== $next->tableKey
            || strtolower($stored->targetDefinitionId) !== strtolower($next->targetDefinitionId)
            || $stored->targetRevision !== $next->targetRevision
            || $stored->targetSchemaVersion !== $next->targetSchemaVersion
        ) {
            throw new InvalidArgumentException('Custom Tables migration run immutable identity changed.');
        }

        if (!$stored->canTransitionTo($next->state)) {
            throw new InvalidArgumentException('Custom Tables migration run replacement transition is illegal.');
        }

        $this->runs[$key] = $next;

        return $next;
    }
}
