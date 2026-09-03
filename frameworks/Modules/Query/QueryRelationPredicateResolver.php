<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Contracts\RelationQueryConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class QueryRelationPredicateResolver
{
    private QueryPostsResourceAuthorizer $posts;

    public function __construct(
        private RelationQueryConsumerInterface $relations,
        ?QueryPostsResourceAuthorizer $posts = null,
    ) {
        $this->posts = $posts ?? new QueryPostsResourceAuthorizer();
    }

    public function resolve(QueryDefinition $definition, ExecutionContext $context): QueryRelationResolution
    {
        if ($definition->filter === null || !$this->containsRelation($definition->filter)) {
            return new QueryRelationResolution($definition, false);
        }

        $root = $definition->filter;
        if ($root->type !== QueryPredicateType::Group || ($root->payload['boolean'] ?? null) !== 'and') {
            throw $this->unsupported('$.filter', 'Relation predicate V1 requires a root AND group with a finite post-id anchor set.');
        }

        $anchorIndex = null;
        $anchorIds = null;
        $relationIndex = null;
        $relation = null;

        foreach ($root->children as $index => $child) {
            if ($child->type === QueryPredicateType::Relation) {
                if ($relation !== null) {
                    throw $this->unsupported('$.filter', 'Relation predicate V1 supports exactly one relation predicate per query.');
                }
                $relation = $child;
                $relationIndex = $index;
                continue;
            }

            if ($this->containsRelation($child)) {
                throw $this->unsupported('$.filter.children[' . $index . ']', 'Nested relation predicates are not supported by Query relation execution V1.');
            }

            $candidate = $this->finiteAnchorIds($child);
            if ($candidate !== null) {
                if ($anchorIds !== null) {
                    throw $this->unsupported('$.filter', 'Relation predicate V1 requires exactly one finite post-id anchor predicate.');
                }
                $anchorIds = $candidate;
                $anchorIndex = $index;
            }
        }

        if (!$relation instanceof QueryPredicate || !is_int($relationIndex)) {
            throw $this->unsupported('$.filter', 'Relation predicate resolution did not find a direct relation predicate.');
        }
        if ($anchorIds === null || !is_int($anchorIndex) || $anchorIds === []) {
            throw $this->unsupported('$.filter', 'Relation predicate V1 requires an explicit non-empty post.id eq/in anchor set.');
        }
        if (count($anchorIds) > RelationQueryConsumerInterface::MAX_BATCH_SIZE) {
            throw new QueryPlanningException(
                'wpe_query_cost_blocked',
                '$.filter',
                'Relation anchor batch exceeds the public Relations contract limit.',
            );
        }

        $relationRef = $relation->payload['relation_ref'] ?? null;
        $direction = $relation->payload['direction'] ?? null;
        $mode = $relation->payload['mode'] ?? null;
        $relatedIds = $relation->payload['related_ids'] ?? null;

        if (!is_string($relationRef) || $relationRef === '') {
            throw new QueryPlanningException('wpe_query_invalid_ast', '$.filter.relation_ref', 'Relation reference is malformed.');
        }
        if (!in_array($direction, [RelationQueryConsumerInterface::DIRECTION_FROM, RelationQueryConsumerInterface::DIRECTION_TO], true)) {
            throw new QueryPlanningException('wpe_query_invalid_ast', '$.filter.direction', 'Relation direction is unsupported.');
        }
        if ($mode !== 'exists' || $relatedIds !== null || $relation->children !== []) {
            throw $this->unsupported(
                '$.filter',
                'Relation execution V1 supports only direct exists predicates without related_ids or nested filters.',
            );
        }

        try {
            $description = $this->relations->describe($relationRef, $context);
        } catch (Throwable) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.filter.relation_ref',
                'Relation reference could not be resolved through the public Relations contract.',
            );
        }

        if (($description['contract_version'] ?? null) !== RelationQueryConsumerInterface::CONTRACT_VERSION) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.filter.relation_ref',
                'Relations consumer contract version is incompatible.',
            );
        }

        $capabilities = $description['capabilities'] ?? null;
        if (!is_array($capabilities)) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.filter.relation_ref',
                'Relations consumer capabilities are unavailable.',
            );
        }

        $maxBatch = $capabilities['max_batch_size'] ?? null;
        $maxResult = $capabilities['max_result_limit'] ?? null;
        $maxDepth = $capabilities['max_traversal_depth'] ?? null;
        if (
            !is_int($maxBatch)
            || !is_int($maxResult)
            || !is_int($maxDepth)
            || $maxBatch < count($anchorIds)
            || $maxResult < count($anchorIds)
            || $maxBatch > RelationQueryConsumerInterface::MAX_BATCH_SIZE
            || $maxResult > RelationQueryConsumerInterface::MAX_RESULT_LIMIT
            || $maxDepth < 1
        ) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.filter.relation_ref',
                'Relations consumer bounds are incompatible with the certified Query V1 relation slice.',
            );
        }

        $endpointKey = $direction === RelationQueryConsumerInterface::DIRECTION_FROM ? 'from' : 'to';
        $endpoint = $description[$endpointKey] ?? null;
        if (!is_array($endpoint)) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.filter.relation_ref',
                'Relation anchor endpoint description is unavailable.',
            );
        }

        $objectType = $endpoint['object_type'] ?? null;
        $objectSubtype = $endpoint['object_subtype'] ?? null;
        if (!is_string($objectType) || ($objectSubtype !== null && !is_string($objectSubtype))) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.filter.relation_ref',
                'Relation anchor endpoint description is malformed.',
            );
        }

        $this->posts->assertCanRead(
            ['object_type' => $objectType, 'object_subtype' => $objectSubtype],
            $anchorIds,
            $context,
        );

        try {
            $matched = $this->relations->matchingAnchorObjectIds(
                $relationRef,
                $direction,
                $anchorIds,
                null,
                count($anchorIds),
                $context,
            );
        } catch (Throwable) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.filter.relation_ref',
                'Relations consumer could not resolve the bounded anchor set.',
            );
        }

        if (!is_array($matched) || !array_is_list($matched) || count($matched) > count($anchorIds)) {
            throw new QueryPlanningException(
                'wpe_query_provider_failed',
                '$.filter.relation_ref',
                'Relations consumer returned an invalid bounded result shape.',
            );
        }

        $anchorSet = array_fill_keys($anchorIds, true);
        $matchedSet = [];
        foreach ($matched as $matchedId) {
            if (!is_int($matchedId) || $matchedId < 1 || !isset($anchorSet[$matchedId])) {
                throw new QueryPlanningException(
                    'wpe_query_provider_failed',
                    '$.filter.relation_ref',
                    'Relations consumer returned an anchor outside the authorized finite set.',
                );
            }
            $matchedSet[$matchedId] = true;
        }

        $orderedMatches = [];
        foreach ($anchorIds as $anchorId) {
            if (isset($matchedSet[$anchorId])) {
                $orderedMatches[] = $anchorId;
            }
        }

        $children = $root->children;
        array_splice($children, $relationIndex, 1);
        if ($relationIndex < $anchorIndex) {
            --$anchorIndex;
        }

        $shortCircuit = $orderedMatches === [];
        if (!$shortCircuit) {
            $children[$anchorIndex] = new QueryPredicate(
                QueryPredicateType::SetMembership,
                [
                    'field_ref' => 'post.id',
                    'operator' => 'in',
                    'values' => $orderedMatches,
                ],
            );
        }

        return new QueryRelationResolution(
            $this->withFilter(
                $definition,
                new QueryPredicate(QueryPredicateType::Group, $root->payload, array_values($children)),
            ),
            $shortCircuit,
        );
    }

    /** @return null|list<int> */
    private function finiteAnchorIds(QueryPredicate $predicate): ?array
    {
        if (
            $predicate->type === QueryPredicateType::Comparison
            && ($predicate->payload['field_ref'] ?? null) === 'post.id'
            && ($predicate->payload['operator'] ?? null) === 'eq'
        ) {
            $value = $predicate->payload['value'] ?? null;
            if (!is_int($value) || $value < 1) {
                throw new QueryPlanningException('wpe_query_invalid_ast', '$.filter', 'Finite post.id anchor is invalid.');
            }
            return [$value];
        }

        if (
            $predicate->type === QueryPredicateType::SetMembership
            && ($predicate->payload['field_ref'] ?? null) === 'post.id'
            && ($predicate->payload['operator'] ?? null) === 'in'
        ) {
            $values = $predicate->payload['values'] ?? null;
            if (!is_array($values) || !array_is_list($values) || $values === []) {
                throw new QueryPlanningException('wpe_query_invalid_ast', '$.filter', 'Finite post.id anchor list is invalid.');
            }

            $ids = [];
            foreach ($values as $value) {
                if (!is_int($value) || $value < 1) {
                    throw new QueryPlanningException('wpe_query_invalid_ast', '$.filter', 'Finite post.id anchor list must contain positive integers.');
                }
                $ids[$value] = $value;
            }
            return array_values($ids);
        }

        return null;
    }

    private function containsRelation(QueryPredicate $predicate): bool
    {
        if ($predicate->type === QueryPredicateType::Relation) {
            return true;
        }
        foreach ($predicate->children as $child) {
            if ($this->containsRelation($child)) {
                return true;
            }
        }
        return false;
    }

    private function withFilter(QueryDefinition $definition, QueryPredicate $filter): QueryDefinition
    {
        return new QueryDefinition(
            identity: $definition->identity,
            astVersion: $definition->astVersion,
            source: $definition->source,
            operation: $definition->operation,
            projection: $definition->projection,
            parameters: $definition->parameters,
            filter: $filter,
            orderBy: $definition->orderBy,
            pagination: $definition->pagination,
            distinct: $definition->distinct,
            executionPolicy: $definition->executionPolicy,
            cachePolicy: $definition->cachePolicy,
            metadata: $definition->metadata,
        );
    }

    private function unsupported(string $path, string $message): QueryPlanningException
    {
        return new QueryPlanningException('wpe_query_unsupported_operator', $path, $message);
    }
}
