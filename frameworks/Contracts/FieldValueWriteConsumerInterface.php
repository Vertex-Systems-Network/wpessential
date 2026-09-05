<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Auth\ExecutionContext;

interface FieldValueWriteConsumerInterface
{
    public const CONTRACT_VERSION = 1;

    /**
     * Mutate one canonical Fields-owned scalar value through the owning module.
     * Implementations must preserve the owner authorization, target-resolution,
     * revision-conflict, normalization, persistence, and verification boundary.
     *
     * @return array{
     *     contract_version:int,
     *     field_ref:string,
     *     group_revision:int,
     *     field_uuid:string,
     *     logical_type:string,
     *     storage_owner:string,
     *     post_id:int,
     *     post_type:string,
     *     status:string,
     *     changed:bool,
     *     value:mixed
     * }
     */
    public function writeValue(
        string $fieldReference,
        int $postId,
        int $expectedGroupRevision,
        mixed $value,
        ExecutionContext $context,
    ): array;
}
