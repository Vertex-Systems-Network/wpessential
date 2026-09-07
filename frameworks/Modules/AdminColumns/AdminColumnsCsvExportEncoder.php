<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use LengthException;

/**
 * Side-effect-free, bounded CSV encoder for already-authorized Admin Columns data.
 *
 * This class deliberately performs no source reads, authorization, transport, or
 * download orchestration. Callers must provide a finite ordered column contract
 * and rows whose keys exactly match that contract.
 */
final class AdminColumnsCsvExportEncoder
{
    public function __construct(
        private int $maxColumns = 64,
        private int $maxRows = 1000,
        private int $maxCellBytes = 32768,
        private int $maxOutputBytes = 8388608,
    ) {
        if ($this->maxColumns < 1 || $this->maxRows < 1 || $this->maxCellBytes < 1 || $this->maxOutputBytes < 1) {
            throw new InvalidArgumentException('CSV encoder budgets must be positive integers.');
        }
    }

    /**
     * @param array<int,array{key:string,label:string}> $columns
     * @param array<int,array<string,scalar|null>> $rows
     */
    public function encode(array $columns, array $rows): string
    {
        $normalizedColumns = $this->normalizeColumns($columns);

        if (!array_is_list($rows)) {
            throw new InvalidArgumentException('CSV rows must be a finite ordered list.');
        }

        if (count($rows) > $this->maxRows) {
            throw new LengthException('CSV row budget exceeded.');
        }

        $output = $this->encodeRecord(array_column($normalizedColumns, 'label'));
        $this->assertOutputBudget($output);
        $orderedKeys = array_column($normalizedColumns, 'key');

        foreach ($rows as $row) {
            if (!is_array($row) || array_is_list($row)) {
                throw new InvalidArgumentException('Each CSV row must be an associative map keyed by the declared columns.');
            }

            if (count($row) !== count($orderedKeys)) {
                throw new InvalidArgumentException('CSV row keys must exactly match the declared columns.');
            }

            foreach ($row as $key => $_value) {
                if (!is_string($key) || !in_array($key, $orderedKeys, true)) {
                    throw new InvalidArgumentException('CSV row keys must exactly match the declared columns.');
                }
            }

            $record = [];
            foreach ($orderedKeys as $key) {
                if (!array_key_exists($key, $row)) {
                    throw new InvalidArgumentException('CSV row keys must exactly match the declared columns.');
                }

                $record[] = $this->normalizeCell($row[$key]);
            }

            $output .= $this->encodeRecord($record);
            $this->assertOutputBudget($output);
        }

        return $output;
    }

    /**
     * @param array<int,array{key:string,label:string}> $columns
     * @return array<int,array{key:string,label:string}>
     */
    private function normalizeColumns(array $columns): array
    {
        if (!array_is_list($columns) || $columns === []) {
            throw new InvalidArgumentException('CSV columns must be a non-empty ordered list.');
        }

        if (count($columns) > $this->maxColumns) {
            throw new LengthException('CSV column budget exceeded.');
        }

        $seen = [];
        $normalized = [];

        foreach ($columns as $column) {
            if (!is_array($column) || array_keys($column) !== ['key', 'label']) {
                throw new InvalidArgumentException('Each CSV column must contain exactly key and label.');
            }

            $key = $column['key'];
            $label = $column['label'];
            if (!is_string($key) || $key === '' || strlen($key) > 128 || preg_match('//u', $key) !== 1) {
                throw new InvalidArgumentException('CSV column keys must be non-empty UTF-8 strings of at most 128 bytes.');
            }
            if (!is_string($label) || $label === '' || preg_match('//u', $label) !== 1) {
                throw new InvalidArgumentException('CSV column labels must be non-empty valid UTF-8 strings.');
            }
            if (isset($seen[$key])) {
                throw new InvalidArgumentException('CSV column keys must be unique.');
            }

            $seen[$key] = true;
            $normalized[] = [
                'key' => $key,
                'label' => $this->normalizeCell($label),
            ];
        }

        return $normalized;
    }

    private function normalizeCell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            $text = $value;
        } elseif (is_int($value)) {
            $text = (string) $value;
        } elseif (is_float($value)) {
            if (!is_finite($value)) {
                throw new InvalidArgumentException('CSV cells do not accept non-finite floating-point values.');
            }
            $text = (string) $value;
        } elseif (is_bool($value)) {
            $text = $value ? 'true' : 'false';
        } else {
            throw new InvalidArgumentException('CSV cells accept only scalar or null values.');
        }

        if (preg_match('//u', $text) !== 1) {
            throw new InvalidArgumentException('CSV cells must contain valid UTF-8 text.');
        }
        if (strlen($text) > $this->maxCellBytes) {
            throw new LengthException('CSV cell budget exceeded.');
        }

        // Spreadsheet applications can execute a leading formula marker even in
        // CSV. Prefixing a single apostrophe forces text treatment while keeping
        // the original value recoverable by removing that one prefix.
        if (preg_match('/^[ \t]*[=+\-@]/u', $text) === 1) {
            $text = "'" . $text;
        }

        return $text;
    }

    /** @param list<string> $cells */
    private function encodeRecord(array $cells): string
    {
        $encoded = array_map(
            static fn (string $cell): string => '"' . str_replace('"', '""', $cell) . '"',
            $cells,
        );

        return implode(',', $encoded) . "\r\n";
    }

    private function assertOutputBudget(string $output): void
    {
        if (strlen($output) > $this->maxOutputBytes) {
            throw new LengthException('CSV output budget exceeded.');
        }
    }
}
