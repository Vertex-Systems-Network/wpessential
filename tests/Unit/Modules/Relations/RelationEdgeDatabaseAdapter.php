<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class RelationEdgeDatabaseAdapter implements DatabaseAdapterInterface
{
    /** @var list<array{query:string,args:list<mixed>}> */
    public array $prepared = [];
    /** @var list<string> */
    public array $queries = [];
    /** @var list<array{table:string,data:array<string,mixed>,formats:list<string>}> */
    public array $inserts = [];
    /** @var list<array<string,mixed>|null> */
    public array $rowQueue = [];
    /** @var list<list<array<string,mixed>>> */
    public array $resultsQueue = [];
    /** @var list<int|bool> */
    public array $queryResultQueue = [];
    public bool $insertResult = true;
    public string $error = '';
    public int $begins = 0;
    public int $commits = 0;
    public int $rollbacks = 0;

    public function __construct(
        private string $prefix = 'wp_',
        private string $charset = 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
    ) {}

    public function networkTablePrefix(): string
    {
        return $this->prefix;
    }

    public function charsetCollate(): string
    {
        return $this->charset;
    }

    public function prepare(string $query, mixed ...$args): string
    {
        $this->prepared[] = ['query' => $query, 'args' => $args];
        return $query;
    }

    public function getRow(string $query): ?array
    {
        $this->queries[] = $query;
        return array_shift($this->rowQueue);
    }

    public function getResults(string $query): array
    {
        $this->queries[] = $query;
        return array_shift($this->resultsQueue) ?? [];
    }

    public function getVar(string $query): mixed
    {
        $this->queries[] = $query;
        return null;
    }

    public function query(string $query): int|bool
    {
        $this->queries[] = $query;
        return array_shift($this->queryResultQueue) ?? 1;
    }

    public function insert(string $table, array $data, array $formats = []): bool
    {
        $this->inserts[] = ['table' => $table, 'data' => $data, 'formats' => $formats];
        return $this->insertResult;
    }

    public function lastError(): string
    {
        return $this->error;
    }

    public function beginTransaction(): void
    {
        ++$this->begins;
    }

    public function commit(): void
    {
        ++$this->commits;
    }

    public function rollBack(): void
    {
        ++$this->rollbacks;
    }
}
