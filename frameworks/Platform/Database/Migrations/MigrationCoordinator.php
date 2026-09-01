<?php

declare(strict_types=1);

namespace WPEssential\Platform\Database\Migrations;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\MigrationInterface;

final readonly class MigrationCoordinator
{
    public function __construct(
        private MigrationRegistry $registry,
        private MigrationRunner $runner,
    ) {}

    public function register(MigrationInterface $migration): void
    {
        $this->registry->register($migration);
    }

    /** @return list<string> applied migration ids */
    public function runPending(): array
    {
        return $this->runner->runPending();
    }
}
