<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\QueryReadConsumerInterface;

final readonly class DashboardWidgetQueryBindingDescriptor
{
    public const MAX_REQUEST_BYTES = 8192;

    /**
     * @param list<string> $projection
     * @param list<array{field_ref:string,operator:string,value:mixed}> $filters
     * @param list<array{field_ref:string,direction:string}> $orderBy
     * @param array<string,array{field_ref:string,mode:string,binding_type:string}> $bindings
     */
    public function __construct(
        public string $sourceRef,
        public array $projection,
        public array $filters,
        public array $orderBy,
        public int $pageSize,
        public int $offset,
        public array $bindings,
    ) {
        if (preg_match('/^[a-z][a-z0-9._-]{1,127}$/', $this->sourceRef) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget Query source reference is invalid.');
        }
        if ($this->projection === [] || count($this->projection) > QueryReadConsumerInterface::MAX_PROJECTION_FIELDS) {
            throw new InvalidArgumentException('Dashboard Widget Query projection must be non-empty and bounded.');
        }

        $sorted = $this->projection;
        sort($sorted, SORT_STRING);
        if ($sorted !== $this->projection || count(array_unique($this->projection)) !== count($this->projection)) {
            throw new InvalidArgumentException('Dashboard Widget Query projection must be unique and lexically sorted.');
        }
        foreach ($this->projection as $fieldRef) {
            if (!is_string($fieldRef) || preg_match('/^[a-z][a-z0-9._-]{0,127}$/', $fieldRef) !== 1) {
                throw new InvalidArgumentException('Dashboard Widget Query projection contains an invalid field reference.');
            }
        }

        if (count($this->filters) > 8 || count($this->orderBy) > 2) {
            throw new InvalidArgumentException('Dashboard Widget Query filters or order exceed Surface 10 V1 bounds.');
        }
        if ($this->pageSize < 1 || $this->pageSize > 50 || $this->offset < 0 || $this->offset > 1000) {
            throw new InvalidArgumentException('Dashboard Widget Query page/offset bounds are invalid.');
        }
        if ($this->bindings === []) {
            throw new InvalidArgumentException('Dashboard Widget Query descriptor requires at least one Query binding.');
        }

        foreach ($this->bindings as $key => $binding) {
            if (!is_string($key) || preg_match('/^[a-z][a-z0-9_.-]{0,127}$/', $key) !== 1) {
                throw new InvalidArgumentException('Dashboard Widget Query binding key is invalid.');
            }
            if (array_keys($binding) !== ['field_ref', 'mode', 'binding_type']) {
                throw new InvalidArgumentException('Dashboard Widget Query binding descriptor shape is invalid.');
            }
            if (!in_array($binding['field_ref'], $this->projection, true)) {
                throw new InvalidArgumentException('Dashboard Widget Query binding field must be in the derived projection.');
            }
            if (!in_array($binding['mode'], ['first', 'column'], true)) {
                throw new InvalidArgumentException('Dashboard Widget Query binding mode is invalid.');
            }
            if (!is_string($binding['binding_type']) || $binding['binding_type'] === '') {
                throw new InvalidArgumentException('Dashboard Widget Query binding type is invalid.');
            }
        }

        $encoded = json_encode($this->request(), JSON_UNESCAPED_SLASHES);
        if (!is_string($encoded) || strlen($encoded) > self::MAX_REQUEST_BYTES) {
            throw new InvalidArgumentException('Dashboard Widget Query request exceeds the 8192-byte Surface 10 bound.');
        }
    }

    /**
     * @return array{
     *   contract_version:int,
     *   source_ref:string,
     *   projection:list<string>,
     *   filters:list<array{field_ref:string,operator:string,value:mixed}>,
     *   order_by:list<array{field_ref:string,direction:string}>,
     *   page_size:int,
     *   offset:int
     * }
     */
    public function request(): array
    {
        return [
            'contract_version' => QueryReadConsumerInterface::CONTRACT_VERSION,
            'source_ref' => $this->sourceRef,
            'projection' => $this->projection,
            'filters' => $this->filters,
            'order_by' => $this->orderBy,
            'page_size' => $this->pageSize,
            'offset' => $this->offset,
        ];
    }
}
