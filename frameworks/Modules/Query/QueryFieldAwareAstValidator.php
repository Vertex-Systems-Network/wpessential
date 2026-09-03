<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use RuntimeException;
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
        }

        if ($issues !== []) {
            return new QueryValidationResult(null, $issues);
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

final readonly class QueryFieldValidationRegistry implements DataSourceRegistryInterface
{
    public function __construct(
        private DataSourceRegistryInterface $inner,
        private string $sourceRef,
        private DataSourceDescriptor $projected,
    ) {
    }

    public function register(DataSourceDescriptor $descriptor): void
    {
        throw new LogicException('Query Field validation registry is read-only.');
    }

    public function has(string $id): bool
    {
        return $id === $this->sourceRef || $this->inner->has($id);
    }

    public function find(string $id): ?DataSourceDescriptor
    {
        return $id === $this->sourceRef ? $this->projected : $this->inner->find($id);
    }

    public function require(string $id): DataSourceDescriptor
    {
        return $this->find($id) ?? throw new RuntimeException(sprintf('Data Source "%s" is not registered.', $id));
    }

    public function all(): array
    {
        $all = $this->inner->all();
        $found = false;
        foreach ($all as $index => $descriptor) {
            if ($descriptor->id === $this->sourceRef) {
                $all[$index] = $this->projected;
                $found = true;
            }
        }
        if (!$found) {
            $all[] = $this->projected;
        }
        return array_values($all);
    }
}
