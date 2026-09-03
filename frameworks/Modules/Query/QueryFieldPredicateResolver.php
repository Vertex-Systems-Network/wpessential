<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Contracts\FieldQueryConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class QueryFieldPredicateResolver
{
    public function __construct(private FieldQueryConsumerInterface $fields)
    {
    }

    public function resolve(QueryDefinition $definition, ExecutionContext $context): QueryFieldResolution
    {
        if ($definition->filter === null || !$this->containsField($definition->filter)) {
            return new QueryFieldResolution($definition, false);
        }

        $root = $definition->filter;
        if ($root->type !== QueryPredicateType::Group || ($root->payload['boolean'] ?? null) !== 'and') {
            throw $this->unsupported('$.filter', 'Field predicate V1 requires a root AND group with a finite post-id anchor set.');
        }

        $anchorIndex = null;
        $anchorIds = null;
        $fieldIndex = null;
        $field = null;

        foreach ($root->children as $index => $child) {
            if ($child->type === QueryPredicateType::Field) {
                if ($field !== null) {
                    throw $this->unsupported('$.filter', 'Field predicate V1 supports exactly one direct Field predicate per query.');
                }
                if ($child->children !== []) {
                    throw $this->unsupported('$.filter.children[' . $index . ']', 'Field predicate V1 does not support nested Field children.');
                }
                $field = $child;
                $fieldIndex = $index;
                continue;
            }

            if ($this->containsField($child)) {
                throw $this->unsupported('$.filter.children[' . $index . ']', 'Nested Field predicates are not supported by Query Field execution V1.');
            }

            $candidate = $this->finiteAnchorIds($child);
            if ($candidate !== null) {
                if ($anchorIds !== null) {
                    throw $this->unsupported('$.filter', 'Field predicate V1 requires exactly one finite post.id anchor predicate.');
                }
                $anchorIds = $candidate;
                $anchorIndex = $index;
            }
        }

        if (!$field instanceof QueryPredicate || !is_int($fieldIndex)) {
            throw $this->unsupported('$.filter', 'Field predicate resolution did not find a direct Field predicate.');
        }
        if ($anchorIds === null || !is_int($anchorIndex) || $anchorIds === []) {
            throw $this->unsupported('$.filter', 'Field predicate V1 requires an explicit non-empty post.id eq/in anchor set.');
        }
        if (count($anchorIds) > FieldQueryConsumerInterface::MAX_CANDIDATE_IDS) {
            throw new QueryPlanningException(
                'wpe_query_cost_blocked',
                '$.filter',
                'Field candidate batch exceeds the public Fields contract limit.',
            );
        }

        $fieldRef = $field->payload['field_ref'] ?? null;
        $operator = $field->payload['operator'] ?? null;
        if (!is_string($fieldRef) || $fieldRef === '') {
            throw new QueryPlanningException('wpe_query_invalid_ast', '$.filter.field_ref', 'Field reference is malformed.');
        }
        if (!is_string($operator) || $operator === '') {
            throw new QueryPlanningException('wpe_query_invalid_ast', '$.filter.operator', 'Field operator is malformed.');
        }
        if (!array_key_exists('value', $field->payload)) {
            throw new QueryPlanningException('wpe_query_invalid_ast', '$.filter.value', 'Field predicate value is missing.');
        }

        try {
            $description = $this->fields->describe($fieldRef, $context);
        } catch (Throwable) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.filter.field_ref',
                'Field reference could not be resolved through the public Fields contract.',
            );
        }

        $this->assertDescription($description, $fieldRef, $operator, count($anchorIds));

        try {
            $matched = $this->fields->matchingPostIds(
                $fieldRef,
                $operator,
                $field->payload['value'],
                $anchorIds,
                count($anchorIds),
                $context,
            );
        } catch (Throwable) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.filter.field_ref',
                'Fields consumer could not resolve the bounded candidate set.',
            );
        }

        if (!is_array($matched) || !array_is_list($matched) || count($matched) > count($anchorIds)) {
            throw new QueryPlanningException(
                'wpe_query_provider_failed',
                '$.filter.field_ref',
                'Fields consumer returned an invalid bounded result shape.',
            );
        }

        $anchorSet = array_fill_keys($anchorIds, true);
        $matchedSet = [];
        foreach ($matched as $matchedId) {
            if (!is_int($matchedId) || $matchedId < 1 || !isset($anchorSet[$matchedId]) || isset($matchedSet[$matchedId])) {
                throw new QueryPlanningException(
                    'wpe_query_provider_failed',
                    '$.filter.field_ref',
                    'Fields consumer returned duplicate, malformed, or foreign candidate ids.',
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
        array_splice($children, $fieldIndex, 1);
        if ($fieldIndex < $anchorIndex) {
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

        return new QueryFieldResolution(
            $this->withFilter(
                $definition,
                new QueryPredicate(QueryPredicateType::Group, $root->payload, array_values($children)),
            ),
            $shortCircuit,
        );
    }

    /** @param array<string,mixed> $description */
    private function assertDescription(array $description, string $fieldRef, string $operator, int $candidateCount): void
    {
        if (($description['contract_version'] ?? null) !== FieldQueryConsumerInterface::CONTRACT_VERSION) {
            throw $this->dependency('Fields consumer contract version is incompatible.');
        }
        if (($description['field_ref'] ?? null) !== $fieldRef) {
            throw $this->dependency('Fields consumer did not echo the requested canonical Field reference.');
        }

        $operators = $description['operators'] ?? null;
        if (!is_array($operators) || !array_is_list($operators)) {
            throw $this->dependency('Fields consumer operator capabilities are unavailable.');
        }
        foreach ($operators as $supportedOperator) {
            if (!is_string($supportedOperator) || $supportedOperator === '') {
                throw $this->dependency('Fields consumer operator capabilities are malformed.');
            }
        }
        if (!in_array($operator, $operators, true)) {
            throw new QueryPlanningException(
                'wpe_query_unsupported_operator',
                '$.filter.operator',
                'Field operator is not supported by the owner contract.',
            );
        }

        $maxCandidateIds = $description['max_candidate_ids'] ?? null;
        $maxResultIds = $description['max_result_ids'] ?? null;
        if (
            !is_int($maxCandidateIds)
            || !is_int($maxResultIds)
            || $maxCandidateIds < $candidateCount
            || $maxResultIds < $candidateCount
            || $maxCandidateIds > FieldQueryConsumerInterface::MAX_CANDIDATE_IDS
            || $maxResultIds > FieldQueryConsumerInterface::MAX_RESULT_IDS
        ) {
            throw $this->dependency('Fields consumer bounds are incompatible with the certified Query V1 Field slice.');
        }

        $logicalType = $description['logical_type'] ?? null;
        $storageOwner = $description['storage_owner'] ?? null;
        if (!is_string($logicalType) || $logicalType === '' || !is_string($storageOwner) || $storageOwner === '') {
            throw $this->dependency('Fields consumer descriptor is missing canonical logical-type or storage-owner metadata.');
        }
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
                if (!is_int($value) || $value < 1 || isset($ids[$value])) {
                    throw new QueryPlanningException(
                        'wpe_query_invalid_ast',
                        '$.filter',
                        'Finite post.id anchor list must contain unique positive integers.',
                    );
                }
                $ids[$value] = true;
            }
            return array_keys($ids);
        }

        return null;
    }

    private function containsField(QueryPredicate $predicate): bool
    {
        if ($predicate->type === QueryPredicateType::Field) {
            return true;
        }
        foreach ($predicate->children as $child) {
            if ($this->containsField($child)) {
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

    private function dependency(string $message): QueryPlanningException
    {
        return new QueryPlanningException('wpe_query_dependency_unavailable', '$.filter.field_ref', $message);
    }

    private function unsupported(string $path, string $message): QueryPlanningException
    {
        return new QueryPlanningException('wpe_query_unsupported_operator', $path, $message);
    }
}
