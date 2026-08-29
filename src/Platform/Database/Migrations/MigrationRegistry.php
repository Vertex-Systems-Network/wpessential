<?php

declare(strict_types=1);

namespace WPEssential\Platform\Database\Migrations;

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\MigrationInterface;

final class MigrationRegistry
{
    /** @var array<string, MigrationInterface> */
    private array $migrations = [];

    /** @var array<int, string> */
    private array $sequences = [];

    public function register(MigrationInterface $migration): void
    {
        if (!preg_match('/^[a-z0-9][a-z0-9._-]{2,191}$/', $migration->id())) {
            throw new InvalidArgumentException('Migration id must be a stable lowercase identifier.');
        }
        if ($migration->sequence() < 1) {
            throw new InvalidArgumentException('Migration sequence must be positive.');
        }
        if (isset($this->migrations[$migration->id()])) {
            throw new RuntimeException(sprintf('Migration "%s" is already registered.', $migration->id()));
        }
        if (isset($this->sequences[$migration->sequence()])) {
            throw new RuntimeException(sprintf('Migration sequence %d is already registered.', $migration->sequence()));
        }

        $this->migrations[$migration->id()] = $migration;
        $this->sequences[$migration->sequence()] = $migration->id();
    }

    /** @return list<MigrationInterface> */
    public function ordered(): array
    {
        ksort($this->sequences, SORT_NUMERIC);
        return array_map(fn (string $id): MigrationInterface => $this->migrations[$id], array_values($this->sequences));
    }
}
