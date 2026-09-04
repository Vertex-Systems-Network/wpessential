<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Auth\ExecutionContext;

/**
 * Public cross-surface read contract owned by Query.
 *
 * Callers provide only bounded semantic references. Implementations remain
 * responsible for canonical Query validation, authorization, provider planning
 * and execution. This contract exposes no raw provider arguments or mutation
 * surface.
 */
interface QueryReadConsumerInterface
{
    public const CONTRACT_VERSION = 1;
    public const MAX_REQUEST_BYTES = 16384;
    public const MAX_PROJECTION_FIELDS = 32;
    public const MAX_FILTERS = 16;
    public const MAX_FILTER_VALUES = 50;
    public const MAX_ORDER_FIELDS = 4;
    public const MAX_PAGE_SIZE = 100;
    public const MAX_OFFSET = 10000;
    public const MAX_SEARCH_LENGTH = 200;

    /**
     * @return array{
     *   contract_version:int,
     *   source_ref:string,
     *   source_type:string,
     *   capability_version:int,
     *   available:bool,
     *   field_schema:array<string,string>,
     *   predicates:list<string>,
     *   sort_modes:list<string>,
     *   pagination_modes:list<string>,
     *   max_page_size:int
     * }
     */
    public function describe(string $sourceRef, ExecutionContext $context): array;

    /**
     * Execute one bounded read request through the canonical Query runtime.
     *
     * V1 request keys are strictly limited to contract_version, source_ref,
     * projection, filters, search, order_by, page_size and offset.
     *
     * @param array<string,mixed> $request
     * @return array<string,mixed>
     */
    public function read(array $request, ExecutionContext $context): array;
}
