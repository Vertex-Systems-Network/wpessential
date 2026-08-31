<?php

declare(strict_types=1);

namespace WPEssential\Platform\Database;


if (!defined('ABSPATH')) {
    exit;
}

interface DatabaseAdapterInterface
{
    public function networkTablePrefix(): string;

    public function charsetCollate(): string;

    public function prepare(string $query, mixed ...$args): string;

    /** @return array<string, mixed>|null */
    public function getRow(string $query): ?array;

    /** @return list<array<string, mixed>> */
    public function getResults(string $query): array;

    public function getVar(string $query): mixed;

    public function query(string $query): int|bool;

    /**
     * @param array<string, mixed> $data
     * @param list<string> $formats
     */
    public function insert(string $table, array $data, array $formats = []): bool;

    public function lastError(): string;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollBack(): void;
}
