<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use LengthException;
use RuntimeException;
use WPEssential\Platform\Auth\ExecutionContext;

/**
 * Bounded internal CSV export orchestration over the certified Admin Columns read path.
 *
 * This service owns neither row/source selection nor authorization. Every page is
 * resolved through AdminColumnsReadAdapter and all CSV serialization is delegated
 * to AdminColumnsCsvExportEncoder.
 */
final readonly class AdminColumnsCsvExportService
{
    private const PAGE_SIZE = 100;
    private const MAX_ROWS = 1000;

    /** @var list<string> */
    private const CONTROL_KEYS = ['filters', 'search', 'order_by'];

    public function __construct(
        private AdminColumnsReadAdapter $reads,
        private AdminColumnsCsvExportEncoder $encoder,
        private int $maxRows = self::MAX_ROWS,
    ) {
        if ($this->maxRows < 1 || $this->maxRows > self::MAX_ROWS) {
            throw new InvalidArgumentException(sprintf(
                'Admin Columns CSV export maxRows must be between 1 and %d.',
                self::MAX_ROWS,
            ));
        }
    }

    /**
     * @param array<string,mixed> $controls
     */
    public function export(string $viewId, array $controls, ExecutionContext $context): string
    {
        $this->assertControls($controls);

        $offset = 0;
        $rows = [];
        $baselineRevision = null;
        $baselineColumns = null;
        $encoderColumns = null;

        while ($offset < $this->maxRows) {
            $pageSize = min(self::PAGE_SIZE, $this->maxRows - $offset);
            $page = $this->readPage($viewId, $controls, $pageSize, $offset, $context);

            [$baselineRevision, $baselineColumns, $encoderColumns] = $this->assertStablePage(
                $page,
                $viewId,
                $pageSize,
                $baselineRevision,
                $baselineColumns,
                $encoderColumns,
            );

            /** @var list<array<string,scalar|null>> $pageRows */
            $pageRows = $page['rows'];
            foreach ($pageRows as $row) {
                $rows[] = $row;
            }

            $returned = $page['returned'];
            $offset += $returned;
            if ($returned < $pageSize) {
                break;
            }
        }

        if ($offset === $this->maxRows) {
            $probe = $this->readPage($viewId, $controls, 1, $offset, $context);
            $this->assertStablePage(
                $probe,
                $viewId,
                1,
                $baselineRevision,
                $baselineColumns,
                $encoderColumns,
            );
            if (($probe['returned'] ?? null) !== 0) {
                throw new LengthException(sprintf(
                    'Admin Columns CSV export exceeds the V1 maximum of %d rows.',
                    $this->maxRows,
                ));
            }
        }

        if (!is_array($encoderColumns) || $encoderColumns === []) {
            throw new RuntimeException('Admin Columns CSV export did not establish a stable column contract.');
        }

        return $this->encoder->encode($encoderColumns, $rows);
    }

    /**
     * @param array<string,mixed> $controls
     * @return array<string,mixed>
     */
    private function readPage(
        string $viewId,
        array $controls,
        int $pageSize,
        int $offset,
        ExecutionContext $context,
    ): array {
        $input = $controls;
        $input['page_size'] = $pageSize;
        $input['offset'] = $offset;

        $page = $this->reads->read($viewId, $input, $context);
        if (($page['ok'] ?? null) !== true) {
            $code = is_array($page['error'] ?? null) && is_string($page['error']['code'] ?? null)
                ? $page['error']['code']
                : 'wpe_admin_columns_export_read_failed';
            throw new RuntimeException(sprintf('Admin Columns CSV export read failed: %s.', $code));
        }

        return $page;
    }

    /**
     * @param array<string,mixed> $page
     * @param int|null $baselineRevision
     * @param list<array<string,mixed>>|null $baselineColumns
     * @param list<array{key:string,label:string}>|null $encoderColumns
     * @return array{0:int,1:list<array<string,mixed>>,2:list<array{key:string,label:string}>}
     */
    private function assertStablePage(
        array $page,
        string $viewId,
        int $requestedPageSize,
        ?int $baselineRevision,
        ?array $baselineColumns,
        ?array $encoderColumns,
    ): array {
        if (($page['contract_version'] ?? null) !== AdminColumnsReadAdapter::CONTRACT_VERSION
            || ($page['view_id'] ?? null) !== $viewId
        ) {
            throw new RuntimeException('Admin Columns CSV export received malformed read contract identity.');
        }

        $revision = $page['view_revision'] ?? null;
        if (!is_int($revision) || $revision < 1) {
            throw new RuntimeException('Admin Columns CSV export received malformed View revision evidence.');
        }

        $columns = $page['columns'] ?? null;
        if (!is_array($columns) || !array_is_list($columns) || $columns === []) {
            throw new RuntimeException('Admin Columns CSV export received malformed column metadata.');
        }

        $pageRows = $page['rows'] ?? null;
        $returned = $page['returned'] ?? null;
        if (!is_array($pageRows)
            || !array_is_list($pageRows)
            || !is_int($returned)
            || $returned < 0
            || $returned > $requestedPageSize
            || $returned !== count($pageRows)
        ) {
            throw new RuntimeException('Admin Columns CSV export received malformed bounded row evidence.');
        }

        if ($baselineRevision !== null && $revision !== $baselineRevision) {
            throw new RuntimeException('Admin Columns CSV export View revision changed between pages.');
        }
        if ($baselineColumns !== null && $columns !== $baselineColumns) {
            throw new RuntimeException('Admin Columns CSV export column contract changed between pages.');
        }

        if ($encoderColumns === null) {
            $encoderColumns = [];
            $seen = [];
            foreach ($columns as $column) {
                if (!is_array($column)) {
                    throw new RuntimeException('Admin Columns CSV export column metadata is malformed.');
                }
                $key = $column['key'] ?? null;
                $label = $column['label'] ?? null;
                if (!is_string($key) || $key === '' || !is_string($label) || $label === '' || isset($seen[$key])) {
                    throw new RuntimeException('Admin Columns CSV export column identity is malformed.');
                }
                $seen[$key] = true;
                $encoderColumns[] = ['key' => $key, 'label' => $label];
            }
        }

        return [
            $baselineRevision ?? $revision,
            $baselineColumns ?? $columns,
            $encoderColumns,
        ];
    }

    /** @param array<string,mixed> $controls */
    private function assertControls(array $controls): void
    {
        if (array_is_list($controls) && $controls !== []) {
            throw new InvalidArgumentException('Admin Columns CSV export controls must be an object/map.');
        }

        foreach (array_keys($controls) as $key) {
            if (!is_string($key) || !in_array($key, self::CONTROL_KEYS, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Admin Columns CSV export control "%s" is not supported.',
                    is_scalar($key) ? (string) $key : 'unknown',
                ));
            }
        }
    }
}
