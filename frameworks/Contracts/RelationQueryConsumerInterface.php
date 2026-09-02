<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Auth\ExecutionContext;

/**
 * Public cross-surface read contract for Query consumers of Surface 4 Relations.
 *
 * Implementations own Relation definition/scope/storage validation. Callers remain
 * responsible for their Data Source row/resource authorization before supplying
 * anchor ids and after receiving related ids.
 */
interface RelationQueryConsumerInterface
{
    public const CONTRACT_VERSION = 1;
    public const DIRECTION_FROM = 'from';
    public const DIRECTION_TO = 'to';
    public const MAX_BATCH_SIZE = 100;
    public const MAX_RESULT_LIMIT = 100;

    /**
     * @return array{
     *   contract_version:int,
     *   relation_definition_id:string,
     *   relation_key:string,
     *   definition_revision:int,
     *   mutation_revision:int,
     *   cardinality:string,
     *   direction:array{reciprocal:bool,bidirectional_traversal:bool},
     *   from:array{object_type:string,object_subtype:?string},
     *   to:array{object_type:string,object_subtype:?string},
     *   capabilities:array{exists:bool,related_ids:bool,count_distinct:bool,batch_exists:bool,max_batch_size:int,max_result_limit:int,max_traversal_depth:int}
     * }
     */
    public function describe(string $relationDefinitionId, ExecutionContext $context): array;

    /** @return list<int> */
    public function relatedObjectIds(
        string $relationDefinitionId,
        string $direction,
        int $anchorObjectId,
        int $limit,
        ExecutionContext $context,
    ): array;

    /**
     * Return authorized-caller supplied anchor ids that have at least one matching
     * related object. relatedObjectIds=null means any related object.
     *
     * @param list<int> $anchorObjectIds
     * @param null|list<int> $relatedObjectIds
     * @return list<int>
     */
    public function matchingAnchorObjectIds(
        string $relationDefinitionId,
        string $direction,
        array $anchorObjectIds,
        ?array $relatedObjectIds,
        int $limit,
        ExecutionContext $context,
    ): array;

    public function countRelatedObjects(
        string $relationDefinitionId,
        string $direction,
        int $anchorObjectId,
        ExecutionContext $context,
    ): int;
}
