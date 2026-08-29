<?php

declare(strict_types=1);

namespace WPEssential\Platform\Database\Migrations;

use WPEssential\Contracts\MigrationStateStoreInterface;

final class InMemoryMigrationStateStore implements MigrationStateStoreInterface
{
    /** @var array<string, true> */
    private array $applied = [];

    public function appliedIds(): array
    {
        return array_keys($this->applied);
    }

    public function markApplied(string $id): void
    {
        $this->applied[$id] = true;
    }
}
