<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Auth\ExecutionContext;

interface FieldQueryConsumerInterface
{
    public const CONTRACT_VERSION = 1;
    public const MAX_CANDIDATE_IDS = 100;
    public const MAX_RESULT_IDS = 100;

    /**
     * @return array{
     *     contract_version:int,
     *     field_ref:string,
     *     group_revision:int,
     *     field_uuid:string,
     *     logical_type:string,
     *     operators:list<string>,
     *     max_candidate_ids:int,
     *     max_result_ids:int,
     *     storage_owner:string
     * }
     */
    public function describe(string $fieldReference, ExecutionContext $context): array;

    /**
     * @param list<int> $candidatePostIds
     * @return list<int>
     */
    public function matchingPostIds(
        string $fieldReference,
        string $operator,
        mixed $value,
        array $candidatePostIds,
        int $limit,
        ExecutionContext $context,
    ): array;
}
