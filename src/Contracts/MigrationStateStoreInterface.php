<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

interface MigrationStateStoreInterface
{
    /** @return list<string> */
    public function appliedIds(): array;

    public function markApplied(string $id): void;
}
