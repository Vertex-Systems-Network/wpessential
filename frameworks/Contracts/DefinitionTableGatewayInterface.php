<?php

declare(strict_types=1);

namespace WPEssential\Contracts;


if (!defined('ABSPATH')) {
    exit;
}

interface DefinitionTableGatewayInterface
{
    /** @return array<string, scalar|null>|null */
    public function find(string $id): ?array;

    /** @param array<string, scalar|null> $row @param list<string> $dependencies */
    public function insert(array $row, array $dependencies): void;

    /** @param array<string, scalar|null> $row @param list<string> $dependencies */
    public function updateIfCurrentRevision(string $id, int $expectedRevision, array $row, array $dependencies): bool;

    /** @return list<array<string, scalar|null>> */
    public function findByType(string $type): array;

    /** @return list<array<string, scalar|null>> */
    public function findDependents(string $id): array;
}
