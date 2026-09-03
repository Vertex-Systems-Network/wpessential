<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Contracts\FieldQueryConsumerInterface;
use WPEssential\Contracts\RelationQueryConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\DataSources\DataSourceDescriptor;

final readonly class QueryFieldAwareAstValidator
{
    public function __construct(
        private DataSourceRegistryInterface $dataSources,
        private ?RelationQueryConsumerInterface $relations,
        private FieldQueryConsumerInterface $fields,
    ) {
    }

    /** @param array<string,mixed> $ast */
    public function validate(
        array $ast,
        QueryValidationBudget $budget,
        ?ExecutionContext $context = null,
    ): QueryValidationResult {
        $fieldNodes = [];
        $this->collectFieldNodes($ast['filter'] ?? null, '$.filter', $fieldNodes);
        if ($fieldNodes === []) {
            return $this->canonical($this->dataSources)->validate($ast, $budget, $context);
        }

        if ($context === null) {
            return new QueryValidationResult(null, [new QueryValidationIssue(
                'wpe_query_dependency_unavailable',
                '$.filter',
                'Field predicate validation requires an authenticated execution context for the owner contract.',
            )]);
        }

        $source = $ast['source'] ?? null;
        $sourceRef = is_array($source) ? ($source['source_ref'] ?? null) : null;
        if (!is_string($sourceRef)) {
            return $this->canonical($this->dataSources)->validate($ast, $budget, $context);
        }

        $descriptor = $this->dataSources->find($sourceRef);
        if (!$descriptor instanceof DataSourceDescriptor) {
            return $this->canonical($this->dataSources)->validate($ast, $budget, $context);
        }

        $fieldSchema = $descriptor->fieldSchema;
        $ownerRefs = [];
        $issues = [];
        foreach ($fieldNodes as $node) {
            $fieldRef = $node['field_ref'];
            $operator = $node['operator'];
            if (!is_string($fieldRef) || preg_match('/^[a-z][a-z0-9._-]{0,127}$/', $fieldRef) !== 1) {
                continue;
            }

            try {
                $description = $this->fields->describe($fieldRef, $context);
            } catch (Throwable) {
                $issues[] = new QueryValidationIssue(
                    'wpe_query_dependency_unavailable',
                    $node['path'] . '.field_ref',
                    'Field reference could not be described through the public Fields contract.',
                );
                continue;
            }

            if (($description['contract_version'] ?? null) !== FieldQueryConsumerInterface::CONTRACT_VERSION
                || ($description['field_ref'] ?? null) !== $fieldRef
            ) {
                $issues[] = new QueryValidationIssue(
                    'wpe_query_dependency_unavailable',
                    $node['path'] . '.field_ref',
                    'Fields consumer identity or contract version is incompatible.',
                );
                continue;
            }

            $logicalType = $description['logical_type'] ?? null;
            $operators = $description['operators'] ?? null;
            $maxCandidateIds = $description['max_candidate_ids'] ?? null;
            $maxResultIds = $description['max_result_ids'] ?? null;
            $storageOwner = $description['storage_owner'] ?? null;
            if (
                !is_string($logicalType)
                || preg_match('/^[a-z][a-z0-9._-]{1,127}$/', $logicalType) !== 1
                || !is_array($operators)
                || !array_is_list($operators)
                || !is_int($maxCandidateIds)
                || !is_int($maxResultIds)
                || $maxCandidateIds < 1
                || $maxResultIds < 1
                || $maxCandidateIds > FieldQueryConsumerInterface::MAX_CANDIDATE_IDS
                || $maxResultIds > FieldQueryConsumerInterface::MAX_RESULT_IDS
                || !is_string($storageOwner)
                || $storageOwner === ''
            ) {
                $issues[] = new QueryValidationIssue(
                    'wpe_query_dependency_unavailable',
                    $node['path'] . '.field_ref',
                    'Fields consumer descriptor is malformed or outside bounded V1 limits.',
                );
                continue;
            }

            foreach ($operators as $supportedOperator) {
                if (!is_string($supportedOperator) || $supportedOperator === '') {
                    $issues[] = new QueryValidationIssue(
                        'wpe_query_dependency_unavailable',
                        $node['path'] . '.operator',
                        'Fields consumer operator capabilities are malformed.',
                    );
                    continue 2;
                }
            }

            if (is_string($operator) && !in_array($operator, $operators, true)) {
                $issues[] = new QueryValidationIssue(
                    'wpe_query_unsupported_operator',
                    $node['path'] . '.operator',
                    'Field operator is not supported by the owner contract.',
                );
                continue;
            }

            $fieldSchema[$fieldRef] = $logicalType;
            $ownerRefs[$fieldRef] = true;
        }

        if ($issues !== []) {
            return new QueryValidationResult(null, $issues);
        }

        $escapeIssues = $this->ownerReferenceEscapeIssues($ast, $ownerRefs);
        if ($escapeIssues !== []) {
            return new QueryValidationResult(null, $escapeIssues);
        }

        try {
            $projectedDescriptor = $this->withFieldSchema($descriptor, $fieldSchema);
        } catch (Throwable) {
            return new QueryValidationResult(null, [new QueryValidationIssue(
                'wpe_query_dependency_unavailable',
                '$.filter',
                'Fields owner metadata could not be projected into the bounded validation view.',
            )]);
        }

        $registry = new QueryFieldValidationRegistry($this->dataSources, $sourceRef, $projectedDescriptor);
        return $this->canonical($registry)->validate($ast, $budget, $context);
    }

    /**
     * @param mixed $node
     * @param list<array{path:string,field_ref:mixed,operator:mixed}> $fieldNodes
     */
    private function collectFieldNodes(mixed $node, string $path, array &$fieldNodes): void
    {
        if (!is_array($node)) {
            return;
        }

        if (($node['type'] ?? null) === 'field') {
            $fieldNodes[] = [
                'path' => $path,
                'field_ref' => $node['field_ref'] ?? null,
                'operator' => $node['operator'] ?? null,
            ];
        }

        $children = $node['children'] ?? null;
        if (!is_array($children) || !array_is_list($children)) {
            return;
        }
        foreach ($children as $index => $child) {
            $this->collectFieldNodes($child, $path . '.children[' . $index . ']', $fieldNodes);
        }
    }

    /**
     * @param array<string,mixed> $ast
     * @param array<string,bool> $ownerRefs
     * @return list<QueryValidationIssue>
     */
    private function ownerReferenceEscapeIssues(array $ast, array $ownerRefs): array
    {
        if ($ownerRefs === []) {
            return [];
        }

        $issues = [];
        $projection = $ast['projection'] ?? null;
        if (is_array($projection) && array_is_list($projection)) {
            foreach ($projection as $index => $fieldRef) {
                if (is_string($fieldRef) && isset($ownerRefs[$fieldRef])) {
                    $issues[] = new QueryValidationIssue(
                        'wpe_query_unsupported_operator',
                        '$.projection[' . $index . ']',
                        'Fields owner references are filter-only in Query Field V1.',
                    );
                }
            }
        }

        $orderBy = $ast['order_by'] ?? null;
        if (is_array($orderBy) && array_is_list($orderBy)) {
            foreach ($orderBy as $index => $clause) {
                $fieldRef = is_array($clause) ? ($clause['field_ref'] ?? null) : null;
                if (is_string($fieldRef) && isset($ownerRefs[$fieldRef])) {
                    $issues[] = new QueryValidationIssue(
                        'wpe_query_unsupported_operator',
                        '$.order_by[' . $index . '].field_ref',
                        'Fields owner references are not sortable in Query Field V1.',
                    );
                }
            }
        }

        $this->scanFilterForEscapes($ast['filter'] ?? null, '$.filter', $ownerRefs, $issues);
        return $issues;
    }

    /**
     * @param mixed $node
     * @param array<string,bool> $ownerRefs
     * @param list<QueryValidationIssue> $issues
     */
    private function scanFilterForEscapes(mixed $node, string $path, array $ownerRefs, array &$issues): void
    {
        if (!is_array($node)) {
            return;
        }

        $type = $node['type'] ?? null;
        $fieldRef = $node['field_ref'] ?? null;
        if ($type !== 'field' && is_string($fieldRef) && isset($ownerRefs[$fieldRef])) {
            $issues[] = new QueryValidationIssue(
                'wpe_query_unsupported_operator',
                $path . '.field_ref',
                'Fields owner references must use the typed Field predicate in Query Field V1.',
            );
        }

        $children = $node['children'] ?? null;
        if (!is_array($children) || !array_is_list($children)) {
            return;
        }
        foreach ($children as $index => $child) {
            $this->scanFilterForEscapes($child, $path . '.children[' . $index . ']', $ownerRefs, $issues);
        }
    }

    /** @param array<string,string> $fieldSchema */
    private function withFieldSchema(DataSourceDescriptor $descriptor, array $fieldSchema): DataSourceDescriptor
    {
        return new DataSourceDescriptor(
            id: $descriptor->id,
            sourceType: $descriptor->sourceType,
            capabilityVersion: $descriptor->capabilityVersion,
            fieldSchema: $fieldSchema,
            predicates: $descriptor->predicates,
            sortModes: $descriptor->sortModes,
            paginationModes: $descriptor->paginationModes,
            aggregationModes: $descriptor->aggregationModes,
            supportsRelations: $descriptor->supportsRelations,
            policyRequired: $descriptor->policyRequired,
            scopes: $descriptor->scopes,
            maxPageSize: $descriptor->maxPageSize,
            maxBatchSize: $descriptor->maxBatchSize,
            cacheable: $descriptor->cacheable,
            cacheGenerationKeys: $descriptor->cacheGenerationKeys,
            diagnosticsAvailable: $descriptor->diagnosticsAvailable,
            availability: $descriptor->availability,
            degradedReason: $descriptor->degradedReason,
            authorization: $descriptor->authorization,
        );
    }

    private function canonical(DataSourceRegistryInterface $registry): QueryAstValidator
    {
        return new QueryAstValidator($registry, $this->relations);
    }
}
