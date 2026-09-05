<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Auth\ExecutionContext;

interface FieldValueReadConsumerInterface
{
    public const CONTRACT_VERSION = 1;
    public const MAX_POST_IDS = 100;

    /**
     * Read one canonical Fields-owned scalar value across a bounded ordered list
     * of WordPress post ids. Implementations must fail the whole call when any
     * requested target is unauthorized or outside the certified Field contract.
     *
     * @param list<int> $postIds
     * @return array{
     *     contract_version:int,
     *     field_ref:string,
     *     group_revision:int,
     *     field_uuid:string,
     *     logical_type:string,
     *     storage_owner:string,
     *     rows:list<array{post_id:int,value:mixed}>
     * }
     */
    public function readValues(
        string $fieldReference,
        array $postIds,
        ExecutionContext $context,
    ): array;
}
