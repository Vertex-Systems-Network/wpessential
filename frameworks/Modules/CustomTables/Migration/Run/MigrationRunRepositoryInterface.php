<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Run;

if (!defined('ABSPATH')) {
    exit;
}

interface MigrationRunRepositoryInterface
{
    public function create(MigrationRun $run): MigrationRun;

    public function get(string $id): ?MigrationRun;

    public function compareAndSwap(MigrationRun $next, int $expectedStateRevision): MigrationRun;
}
