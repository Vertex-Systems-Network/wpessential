<?php

declare(strict_types=1);

namespace WPEssential\Platform\Database;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;

final class NativeWpdbAdapter implements DatabaseAdapterInterface
{
    public function __construct(private readonly object $wpdb)
    {
        foreach (['prepare', 'get_row', 'get_results', 'get_var', 'query', 'insert'] as $method) {
            if (!method_exists($this->wpdb, $method)) {
                throw new InvalidArgumentException(sprintf('wpdb adapter requires method %s().', $method));
            }
        }
    }

    public function networkTablePrefix(): string
    {
        $prefix = (string) ($this->wpdb->base_prefix ?? '');
        if ($prefix === '' || preg_match('/^[A-Za-z0-9_]+$/', $prefix) !== 1) {
            throw new RuntimeException('WordPress database base prefix is invalid.');
        }
        return $prefix;
    }

    public function charsetCollate(): string
    {
        if (!method_exists($this->wpdb, 'get_charset_collate')) {
            return '';
        }
        return trim((string) $this->wpdb->get_charset_collate());
    }

    public function prepare(string $query, mixed ...$args): string
    {
        if ($args === []) {
            return $query;
        }
        $prepared = $this->wpdb->prepare($query, ...$args);
        if (!is_string($prepared) || $prepared === '') {
            throw new RuntimeException('wpdb failed to prepare a query.');
        }
        return $prepared;
    }

    public function getRow(string $query): ?array
    {
        $row = $this->wpdb->get_row($query, 'ARRAY_A');
        $this->assertNoError('read row');
        if ($row === null) {
            return null;
        }
        if (is_object($row)) {
            $row = (array) $row;
        }
        if (!is_array($row)) {
            throw new RuntimeException('wpdb returned an invalid row shape.');
        }
        return $row;
    }

    public function getResults(string $query): array
    {
        $rows = $this->wpdb->get_results($query, 'ARRAY_A');
        $this->assertNoError('read rows');
        if (!is_array($rows)) {
            throw new RuntimeException('wpdb returned an invalid result-set shape.');
        }

        $result = [];
        foreach ($rows as $row) {
            if (is_object($row)) {
                $row = (array) $row;
            }
            if (!is_array($row)) {
                throw new RuntimeException('wpdb returned an invalid result row shape.');
            }
            $result[] = $row;
        }
        return $result;
    }

    public function getVar(string $query): mixed
    {
        $value = $this->wpdb->get_var($query);
        $this->assertNoError('read scalar');
        return $value;
    }

    public function query(string $query): int|bool
    {
        $result = $this->wpdb->query($query);
        if ($result === false) {
            throw new RuntimeException('wpdb query failed: ' . $this->lastError());
        }
        return $result;
    }

    public function insert(string $table, array $data, array $formats = []): bool
    {
        $result = $formats === []
            ? $this->wpdb->insert($table, $data)
            : $this->wpdb->insert($table, $data, $formats);
        if ($result === false) {
            throw new RuntimeException('wpdb insert failed: ' . $this->lastError());
        }
        return true;
    }

    public function lastError(): string
    {
        return trim((string) ($this->wpdb->last_error ?? ''));
    }

    public function beginTransaction(): void
    {
        $this->query('START TRANSACTION');
    }

    public function commit(): void
    {
        $this->query('COMMIT');
    }

    public function rollBack(): void
    {
        $this->query('ROLLBACK');
    }

    private function assertNoError(string $operation): void
    {
        $error = $this->lastError();
        if ($error !== '') {
            throw new RuntimeException(sprintf('wpdb failed to %s: %s', $operation, $error));
        }
    }
}
