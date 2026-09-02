<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Contracts\RelationQueryConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\DataSources\DataSourceDescriptor;

final class QueryAstValidator
{
    private const AST_VERSION = 1;

    /** @var list<string> */
    private const TOP_LEVEL_KEYS = [
        'identity',
        'ast_version',
        'source',
        'operation',
        'projection',
        'parameters',
        'filter',
        'order_by',
        'pagination',
        'distinct',
        'execution_policy',
        'cache_policy',
        'metadata',
    ];

    /** @var list<string> */
    private const REQUIRED_TOP_LEVEL_KEYS = [
        'identity',
        'ast_version',
        'source',
        'operation',
        'projection',
        'parameters',
        'order_by',
        'pagination',
        'execution_policy',
        'cache_policy',
    ];

    /** @var list<string> */
    private const FORBIDDEN_KEY_FRAGMENTS = [
        'raw_sql',
        'sql_query',
        'sql_fragment',
        'php_code',
        'callback',
        'callable',
        'function_name',
        'class_name',
        'eval',
        'table_name',
        'column_name',
        'identifier',
        'credentials',
        'credential',
        'endpoint_url',
    ];

    public function __construct(
        private readonly DataSourceRegistryInterface $dataSources,
        private readonly ?RelationQueryConsumerInterface $relations = null,
    ) {
    }

    /** @param array<string,mixed> $ast */
    public function validate(
        array $ast,
        QueryValidationBudget $budget,
        ?ExecutionContext $context = null,
    ): QueryValidationResult {
        $issues = [];
        $encoded = json_encode($ast);
        if (!is_string($encoded)) {
            $this->issue($issues, 'wpe_query_invalid_ast', '$', 'Query AST must be JSON-serializable.');
        } elseif (strlen($encoded) > $budget->maxAstBytes) {
            $this->issue($issues, 'wpe_query_cost_blocked', '$', 'Query AST exceeds the configured byte budget.');
        }

        $this->scanUnsafePayload($ast, '$', $issues);
        $this->assertAllowedKeys($ast, self::TOP_LEVEL_KEYS, '$', $issues);
        foreach (self::REQUIRED_TOP_LEVEL_KEYS as $requiredKey) {
            if (!array_key_exists($requiredKey, $ast)) {
                $this->issue($issues, 'wpe_query_invalid_ast', '$.' . $requiredKey, 'Required semantic node is missing.');
            }
        }

        $identity = $this->parseIdentity($ast['identity'] ?? null, $issues);
        $astVersion = $this->parseAstVersion($ast['ast_version'] ?? null, $issues);
        [$source, $descriptor] = $this->parseSource($ast['source'] ?? null, $issues);

        $operation = $ast['operation'] ?? null;
        if ($operation !== 'select') {
            $this->issue($issues, 'wpe_query_invalid_ast', '$.operation', 'Query operation must be select.');
            $operation = 'select';
        }

        $projection = $this->parseProjection($ast['projection'] ?? null, $descriptor, $issues);
        $parameters = $this->parseParameters($ast['parameters'] ?? null, $issues);

        $predicateCount = 0;
        $filter = null;
        if (array_key_exists('filter', $ast) && $ast['filter'] !== null) {
            $filter = $this->parsePredicate(
                $ast['filter'],
                '$.filter',
                1,
                0,
                $budget,
                $descriptor,
                $context,
                $predicateCount,
                $issues,
            );
        }

        $orderBy = $this->parseOrderBy($ast['order_by'] ?? null, $descriptor, $issues);
        $pagination = $this->parsePagination($ast['pagination'] ?? null, $budget, $descriptor, $issues);

        $distinct = $ast['distinct'] ?? false;
        if (!is_bool($distinct)) {
            $this->issue($issues, 'wpe_query_type_mismatch', '$.distinct', 'Distinct must be a boolean.');
            $distinct = false;
        }

        $executionPolicy = $this->parsePolicy($ast['execution_policy'] ?? null, '$.execution_policy', $issues);
        $cachePolicy = $this->parsePolicy($ast['cache_policy'] ?? null, '$.cache_policy', $issues);

        $metadata = $ast['metadata'] ?? null;
        if ($metadata !== null && !is_array($metadata)) {
            $this->issue($issues, 'wpe_query_type_mismatch', '$.metadata', 'Metadata must be an object when provided.');
            $metadata = null;
        }

        if ($issues !== [] || $identity === null || $source === null || $pagination === null) {
            return new QueryValidationResult(null, $issues);
        }

        return new QueryValidationResult(
            new QueryDefinition(
                identity: $identity,
                astVersion: $astVersion,
                source: $source,
                operation: $operation,
                projection: $projection,
                parameters: $parameters,
                filter: $filter,
                orderBy: $orderBy,
                pagination: $pagination,
                distinct: $distinct,
                executionPolicy: $executionPolicy,
                cachePolicy: $cachePolicy,
                metadata: $metadata,
            ),
            [],
        );
    }

    /**
     * @param mixed $raw
     * @param list<QueryValidationIssue> $issues
     * @return array{uuid:string,key:string,name:string,revision:int,lifecycle:string}|null
     */
    private function parseIdentity(mixed $raw, array &$issues): ?array
    {
        $path = '$.identity';
        if (!is_array($raw)) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path, 'Identity must be an object.');
            return null;
        }

        $this->assertAllowedKeys($raw, ['uuid', 'key', 'name', 'revision', 'lifecycle'], $path, $issues);
        foreach (['uuid', 'key', 'name', 'revision', 'lifecycle'] as $key) {
            if (!array_key_exists($key, $raw)) {
                $this->issue($issues, 'wpe_query_invalid_ast', $path . '.' . $key, 'Required identity value is missing.');
            }
        }

        $uuid = $raw['uuid'] ?? null;
        $key = $raw['key'] ?? null;
        $name = $raw['name'] ?? null;
        $revision = $raw['revision'] ?? null;
        $lifecycle = $raw['lifecycle'] ?? null;

        if (!is_string($uuid) || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid) !== 1) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.uuid', 'Query identity UUID is malformed.');
        }
        if (!$this->isSemanticIdentifier($key)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.key', 'Query key must be a stable semantic identifier.');
        }
        if (!is_string($name) || trim($name) === '') {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.name', 'Query name must be a non-empty string.');
        }
        if (!is_int($revision) || $revision < 1) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.revision', 'Query revision must be a positive integer.');
        }
        if (!$this->isSemanticIdentifier($lifecycle)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.lifecycle', 'Lifecycle must be a stable semantic identifier.');
        }

        if (!is_string($uuid) || !is_string($key) || !is_string($name) || !is_int($revision) || !is_string($lifecycle)) {
            return null;
        }

        return [
            'uuid' => $uuid,
            'key' => $key,
            'name' => $name,
            'revision' => $revision,
            'lifecycle' => $lifecycle,
        ];
    }

    /** @param list<QueryValidationIssue> $issues */
    private function parseAstVersion(mixed $raw, array &$issues): int
    {
        if (!is_int($raw)) {
            $this->issue($issues, 'wpe_query_type_mismatch', '$.ast_version', 'AST version must be an integer.');
            return self::AST_VERSION;
        }
        if ($raw !== self::AST_VERSION) {
            $this->issue($issues, 'wpe_query_invalid_ast', '$.ast_version', 'Unknown Query AST version.');
        }

        return $raw;
    }

    /**
     * @param mixed $raw
     * @param list<QueryValidationIssue> $issues
     * @return array{0:?QuerySourceReference,1:?DataSourceDescriptor}
     */
    private function parseSource(mixed $raw, array &$issues): array
    {
        $path = '$.source';
        if (!is_array($raw)) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path, 'Source must be an object.');
            return [null, null];
        }

        $this->assertAllowedKeys($raw, ['source_ref', 'source_type', 'capability_version'], $path, $issues);
        $sourceRef = $raw['source_ref'] ?? null;
        $sourceType = $raw['source_type'] ?? null;
        $capabilityVersion = $raw['capability_version'] ?? null;

        if (!$this->isSemanticIdentifier($sourceRef)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.source_ref', 'Source reference must be a stable semantic identifier.');
        }
        if (!$this->isSemanticIdentifier($sourceType)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.source_type', 'Source type must be a stable semantic identifier.');
        }
        if (!is_int($capabilityVersion) || $capabilityVersion < 1) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.capability_version', 'Capability version must be a positive integer.');
        }

        if (!is_string($sourceRef) || !is_string($sourceType) || !is_int($capabilityVersion)) {
            return [null, null];
        }

        $descriptor = $this->dataSources->find($sourceRef);
        if ($descriptor === null) {
            $this->issue($issues, 'wpe_query_unknown_source', $path . '.source_ref', 'Data Source is not registered in the canonical registry.');
            return [new QuerySourceReference($sourceRef, $sourceType, $capabilityVersion), null];
        }

        if ($descriptor->sourceType !== $sourceType || $descriptor->capabilityVersion !== $capabilityVersion) {
            $this->issue($issues, 'wpe_query_dependency_unavailable', $path, 'Data Source type or capability version does not match the registered descriptor.');
        }
        if (!$descriptor->isAvailable()) {
            $this->issue($issues, 'wpe_query_dependency_unavailable', $path, 'Data Source is currently degraded and unavailable for execution.');
        }

        return [new QuerySourceReference($sourceRef, $sourceType, $capabilityVersion), $descriptor];
    }

    /**
     * @param mixed $raw
     * @param list<QueryValidationIssue> $issues
     * @return list<string>
     */
    private function parseProjection(mixed $raw, ?DataSourceDescriptor $descriptor, array &$issues): array
    {
        if (!is_array($raw) || !array_is_list($raw)) {
            $this->issue($issues, 'wpe_query_type_mismatch', '$.projection', 'Projection must be a list of field references.');
            return [];
        }

        $projection = [];
        foreach ($raw as $index => $fieldRef) {
            $path = '$.projection[' . $index . ']';
            if (!$this->isSemanticIdentifier($fieldRef)) {
                $this->issue($issues, 'wpe_query_invalid_ast', $path, 'Projection field must be a stable semantic identifier.');
                continue;
            }
            if ($descriptor !== null && !array_key_exists($fieldRef, $descriptor->fieldSchema)) {
                $this->issue($issues, 'wpe_query_invalid_ast', $path, 'Projection field is not declared by the Data Source.');
                continue;
            }
            $projection[] = $fieldRef;
        }

        return $projection;
    }

    /**
     * @param mixed $raw
     * @param list<QueryValidationIssue> $issues
     * @return array<string,array<string,mixed>>
     */
    private function parseParameters(mixed $raw, array &$issues): array
    {
        if (!is_array($raw)) {
            $this->issue($issues, 'wpe_query_type_mismatch', '$.parameters', 'Parameters must be an object.');
            return [];
        }

        $parameters = [];
        foreach ($raw as $name => $definition) {
            $path = '$.parameters.' . (string) $name;
            if (!is_string($name) || !$this->isSemanticIdentifier($name)) {
                $this->issue($issues, 'wpe_query_invalid_ast', $path, 'Parameter name must be a stable semantic identifier.');
                continue;
            }
            if (!is_array($definition)) {
                $this->issue($issues, 'wpe_query_type_mismatch', $path, 'Parameter definition must be an object.');
                continue;
            }

            $allowed = ['type', 'nullable', 'cardinality', 'bounds', 'enum', 'normalization', 'sensitivity', 'value_source'];
            $this->assertAllowedKeys($definition, $allowed, $path, $issues);
            foreach (['type', 'nullable', 'cardinality', 'normalization', 'sensitivity', 'value_source'] as $required) {
                if (!array_key_exists($required, $definition)) {
                    $this->issue($issues, 'wpe_query_invalid_ast', $path . '.' . $required, 'Required parameter semantic is missing.');
                }
            }
            if (isset($definition['type']) && !$this->isSemanticIdentifier($definition['type'])) {
                $this->issue($issues, 'wpe_query_invalid_ast', $path . '.type', 'Parameter type must be a stable semantic identifier.');
            }
            if (isset($definition['nullable']) && !is_bool($definition['nullable'])) {
                $this->issue($issues, 'wpe_query_type_mismatch', $path . '.nullable', 'Parameter nullable must be boolean.');
            }
            if (isset($definition['cardinality']) && !in_array($definition['cardinality'], ['one', 'many'], true)) {
                $this->issue($issues, 'wpe_query_invalid_ast', $path . '.cardinality', 'Parameter cardinality must be one or many.');
            }
            if (isset($definition['normalization']) && !$this->isSemanticIdentifier($definition['normalization'])) {
                $this->issue($issues, 'wpe_query_invalid_ast', $path . '.normalization', 'Parameter normalization must be a stable semantic identifier.');
            }
            if (isset($definition['sensitivity']) && !$this->isSemanticIdentifier($definition['sensitivity'])) {
                $this->issue($issues, 'wpe_query_invalid_ast', $path . '.sensitivity', 'Parameter sensitivity must be a stable semantic identifier.');
            }
            if (isset($definition['value_source']) && !$this->isSemanticIdentifier($definition['value_source'])) {
                $this->issue($issues, 'wpe_query_invalid_ast', $path . '.value_source', 'Parameter value source must be a stable semantic identifier.');
            }
            if (isset($definition['enum']) && (!is_array($definition['enum']) || !array_is_list($definition['enum']))) {
                $this->issue($issues, 'wpe_query_type_mismatch', $path . '.enum', 'Parameter enum must be a list.');
            }
            if (isset($definition['bounds']) && !is_array($definition['bounds'])) {
                $this->issue($issues, 'wpe_query_type_mismatch', $path . '.bounds', 'Parameter bounds must be an object.');
            }

            $parameters[$name] = $definition;
        }

        return $parameters;
    }

    /**
     * @param mixed $raw
     * @param list<QueryValidationIssue> $issues
     */
    private function parsePredicate(
        mixed $raw,
        string $path,
        int $depth,
        int $relationDepth,
        QueryValidationBudget $budget,
        ?DataSourceDescriptor $descriptor,
        ?ExecutionContext $context,
        int &$predicateCount,
        array &$issues,
    ): ?QueryPredicate {
        if (!is_array($raw)) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path, 'Predicate node must be an object.');
            return null;
        }

        ++$predicateCount;
        if ($predicateCount > $budget->maxPredicateCount) {
            $this->issue($issues, 'wpe_query_cost_blocked', $path, 'Predicate count exceeds the configured budget.');
        }
        if ($depth > $budget->maxGroupDepth) {
            $this->issue($issues, 'wpe_query_cost_blocked', $path, 'Predicate depth exceeds the configured budget.');
        }

        $typeValue = $raw['type'] ?? null;
        if (!is_string($typeValue)) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.type', 'Predicate type must be a string.');
            return null;
        }

        $type = QueryPredicateType::tryFrom($typeValue);
        if ($type === null) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.type', 'Unknown semantic predicate node.');
            return null;
        }

        return match ($type) {
            QueryPredicateType::Group => $this->parseGroupPredicate($raw, $path, $depth, $relationDepth, $budget, $descriptor, $context, $predicateCount, $issues),
            QueryPredicateType::Comparison => $this->parseComparisonPredicate($raw, $path, $descriptor, $issues),
            QueryPredicateType::Existence => $this->parseExistencePredicate($raw, $path, $descriptor, $issues),
            QueryPredicateType::Range => $this->parseRangePredicate($raw, $path, $descriptor, $issues),
            QueryPredicateType::SetMembership => $this->parseSetPredicate($raw, $path, $budget, $descriptor, $issues),
            QueryPredicateType::Text => $this->parseTextPredicate($raw, $path, $descriptor, $issues),
            QueryPredicateType::Taxonomy => $this->parseTaxonomyPredicate($raw, $path, $issues),
            QueryPredicateType::DateTime => $this->parseDateTimePredicate($raw, $path, $descriptor, $issues),
            QueryPredicateType::Field => $this->parseFieldPredicate($raw, $path, $descriptor, $issues),
            QueryPredicateType::Relation => $this->parseRelationPredicate($raw, $path, $depth, $relationDepth, $budget, $descriptor, $context, $predicateCount, $issues),
            QueryPredicateType::ProviderExtension => $this->parseProviderExtensionPredicate($raw, $path, $descriptor, $issues),
        };
    }

    /** @param list<QueryValidationIssue> $issues */
    private function parseGroupPredicate(
        array $raw,
        string $path,
        int $depth,
        int $relationDepth,
        QueryValidationBudget $budget,
        ?DataSourceDescriptor $descriptor,
        ?ExecutionContext $context,
        int &$predicateCount,
        array &$issues,
    ): QueryPredicate {
        $this->assertAllowedKeys($raw, ['type', 'boolean', 'children'], $path, $issues);
        $boolean = $raw['boolean'] ?? null;
        if (!in_array($boolean, ['and', 'or'], true)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.boolean', 'Group boolean must be and or or.');
        }
        $childrenRaw = $raw['children'] ?? null;
        if (!is_array($childrenRaw) || !array_is_list($childrenRaw) || $childrenRaw === []) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.children', 'Group children must be a non-empty list.');
            $childrenRaw = [];
        }

        $children = [];
        foreach ($childrenRaw as $index => $childRaw) {
            $child = $this->parsePredicate(
                $childRaw,
                $path . '.children[' . $index . ']',
                $depth + 1,
                $relationDepth,
                $budget,
                $descriptor,
                $context,
                $predicateCount,
                $issues,
            );
            if ($child !== null) {
                $children[] = $child;
            }
        }

        return new QueryPredicate(QueryPredicateType::Group, ['boolean' => $boolean], $children);
    }

    /** @param list<QueryValidationIssue> $issues */
    private function parseComparisonPredicate(array $raw, string $path, ?DataSourceDescriptor $descriptor, array &$issues): QueryPredicate
    {
        $this->assertAllowedKeys($raw, ['type', 'field_ref', 'operator', 'value'], $path, $issues);
        $fieldRef = $raw['field_ref'] ?? null;
        $operator = $raw['operator'] ?? null;
        $this->validateField($fieldRef, $path . '.field_ref', $descriptor, $issues);
        $this->validateOperator($operator, $path . '.operator', $descriptor, $issues);
        if (!array_key_exists('value', $raw)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.value', 'Comparison value is required.');
        }

        return new QueryPredicate(QueryPredicateType::Comparison, [
            'field_ref' => $fieldRef,
            'operator' => $operator,
            'value' => $raw['value'] ?? null,
        ]);
    }

    /** @param list<QueryValidationIssue> $issues */
    private function parseExistencePredicate(array $raw, string $path, ?DataSourceDescriptor $descriptor, array &$issues): QueryPredicate
    {
        $this->assertAllowedKeys($raw, ['type', 'field_ref', 'operator'], $path, $issues);
        $fieldRef = $raw['field_ref'] ?? null;
        $operator = $raw['operator'] ?? null;
        $this->validateField($fieldRef, $path . '.field_ref', $descriptor, $issues);
        if (!in_array($operator, ['exists', 'not_exists'], true)) {
            $this->issue($issues, 'wpe_query_unsupported_operator', $path . '.operator', 'Existence operator must be exists or not_exists.');
        } else {
            $this->validateOperator($operator, $path . '.operator', $descriptor, $issues);
        }

        return new QueryPredicate(QueryPredicateType::Existence, ['field_ref' => $fieldRef, 'operator' => $operator]);
    }

    /** @param list<QueryValidationIssue> $issues */
    private function parseRangePredicate(array $raw, string $path, ?DataSourceDescriptor $descriptor, array &$issues): QueryPredicate
    {
        $this->assertAllowedKeys($raw, ['type', 'field_ref', 'lower', 'upper', 'inclusive'], $path, $issues);
        $fieldRef = $raw['field_ref'] ?? null;
        $this->validateField($fieldRef, $path . '.field_ref', $descriptor, $issues);
        if (!array_key_exists('lower', $raw) && !array_key_exists('upper', $raw)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path, 'Range requires a lower or upper bound.');
        }
        if (isset($raw['inclusive']) && !is_bool($raw['inclusive'])) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.inclusive', 'Range inclusive must be boolean.');
        }
        if ($descriptor !== null && !in_array('between', $descriptor->predicates, true)) {
            $this->issue($issues, 'wpe_query_unsupported_operator', $path, 'Data Source does not advertise range predicates.');
        }

        return new QueryPredicate(QueryPredicateType::Range, [
            'field_ref' => $fieldRef,
            'lower' => $raw['lower'] ?? null,
            'upper' => $raw['upper'] ?? null,
            'inclusive' => $raw['inclusive'] ?? true,
        ]);
    }

    /** @param list<QueryValidationIssue> $issues */
    private function parseSetPredicate(array $raw, string $path, QueryValidationBudget $budget, ?DataSourceDescriptor $descriptor, array &$issues): QueryPredicate
    {
        $this->assertAllowedKeys($raw, ['type', 'field_ref', 'operator', 'values'], $path, $issues);
        $fieldRef = $raw['field_ref'] ?? null;
        $operator = $raw['operator'] ?? null;
        $values = $raw['values'] ?? null;
        $this->validateField($fieldRef, $path . '.field_ref', $descriptor, $issues);
        if (!in_array($operator, ['in', 'not_in'], true)) {
            $this->issue($issues, 'wpe_query_unsupported_operator', $path . '.operator', 'Set operator must be in or not_in.');
        } else {
            $this->validateOperator($operator, $path . '.operator', $descriptor, $issues);
        }
        if (!is_array($values) || !array_is_list($values)) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.values', 'Set values must be a list.');
            $values = [];
        } elseif (count($values) > $budget->maxInListSize) {
            $this->issue($issues, 'wpe_query_cost_blocked', $path . '.values', 'Set values exceed the configured IN-list budget.');
        }

        return new QueryPredicate(QueryPredicateType::SetMembership, [
            'field_ref' => $fieldRef,
            'operator' => $operator,
            'values' => $values,
        ]);
    }

    /** @param list<QueryValidationIssue> $issues */
    private function parseTextPredicate(array $raw, string $path, ?DataSourceDescriptor $descriptor, array &$issues): QueryPredicate
    {
        $this->assertAllowedKeys($raw, ['type', 'field_ref', 'search_scope', 'mode', 'value'], $path, $issues);
        $fieldRef = $raw['field_ref'] ?? null;
        $searchScope = $raw['search_scope'] ?? null;
        $mode = $raw['mode'] ?? null;
        if ($fieldRef === null && $searchScope === null) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path, 'Text predicate requires field_ref or search_scope.');
        }
        if ($fieldRef !== null) {
            $this->validateField($fieldRef, $path . '.field_ref', $descriptor, $issues);
        }
        if ($searchScope !== null && !$this->isSemanticIdentifier($searchScope)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.search_scope', 'Search scope must be a stable semantic identifier.');
        }
        if (!$this->isSemanticIdentifier($mode)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.mode', 'Text mode must be a stable semantic identifier.');
        } else {
            $this->validateOperator($mode, $path . '.mode', $descriptor, $issues);
        }
        if (!array_key_exists('value', $raw)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.value', 'Text value is required.');
        }

        return new QueryPredicate(QueryPredicateType::Text, [
            'field_ref' => $fieldRef,
            'search_scope' => $searchScope,
            'mode' => $mode,
            'value' => $raw['value'] ?? null,
        ]);
    }

    /** @param list<QueryValidationIssue> $issues */
    private function parseTaxonomyPredicate(array $raw, string $path, array &$issues): QueryPredicate
    {
        $this->assertAllowedKeys($raw, ['type', 'taxonomy_ref', 'field', 'operator', 'terms', 'include_children'], $path, $issues);
        foreach (['taxonomy_ref', 'field', 'operator'] as $key) {
            if (!$this->isSemanticIdentifier($raw[$key] ?? null)) {
                $this->issue($issues, 'wpe_query_invalid_ast', $path . '.' . $key, 'Taxonomy semantic reference must be a stable identifier.');
            }
        }
        $terms = $raw['terms'] ?? null;
        if (!is_array($terms) || !array_is_list($terms) || $terms === []) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.terms', 'Taxonomy terms must be a non-empty list.');
            $terms = [];
        }
        if (isset($raw['include_children']) && !is_bool($raw['include_children'])) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.include_children', 'include_children must be boolean.');
        }

        return new QueryPredicate(QueryPredicateType::Taxonomy, [
            'taxonomy_ref' => $raw['taxonomy_ref'] ?? null,
            'field' => $raw['field'] ?? null,
            'operator' => $raw['operator'] ?? null,
            'terms' => $terms,
            'include_children' => $raw['include_children'] ?? false,
        ]);
    }

    /** @param list<QueryValidationIssue> $issues */
    private function parseDateTimePredicate(array $raw, string $path, ?DataSourceDescriptor $descriptor, array &$issues): QueryPredicate
    {
        $this->assertAllowedKeys($raw, ['type', 'field_ref', 'constraint'], $path, $issues);
        $fieldRef = $raw['field_ref'] ?? null;
        $constraint = $raw['constraint'] ?? null;
        $this->validateField($fieldRef, $path . '.field_ref', $descriptor, $issues);
        if (!is_array($constraint) || $constraint === []) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.constraint', 'DateTime constraint must be a non-empty object.');
            $constraint = [];
        }

        return new QueryPredicate(QueryPredicateType::DateTime, ['field_ref' => $fieldRef, 'constraint' => $constraint]);
    }

    /** @param list<QueryValidationIssue> $issues */
    private function parseFieldPredicate(array $raw, string $path, ?DataSourceDescriptor $descriptor, array &$issues): QueryPredicate
    {
        $this->assertAllowedKeys($raw, ['type', 'field_ref', 'operator', 'value'], $path, $issues);
        $fieldRef = $raw['field_ref'] ?? null;
        $operator = $raw['operator'] ?? null;
        $this->validateField($fieldRef, $path . '.field_ref', $descriptor, $issues);
        $this->validateOperator($operator, $path . '.operator', $descriptor, $issues);
        if (!array_key_exists('value', $raw)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.value', 'Field predicate value is required.');
        }

        return new QueryPredicate(QueryPredicateType::Field, [
            'field_ref' => $fieldRef,
            'operator' => $operator,
            'value' => $raw['value'] ?? null,
        ]);
    }

    /** @param list<QueryValidationIssue> $issues */
    private function parseRelationPredicate(
        array $raw,
        string $path,
        int $depth,
        int $relationDepth,
        QueryValidationBudget $budget,
        ?DataSourceDescriptor $descriptor,
        ?ExecutionContext $context,
        int &$predicateCount,
        array &$issues,
    ): QueryPredicate {
        $this->assertAllowedKeys($raw, ['type', 'relation_ref', 'direction', 'mode', 'related_ids', 'filter'], $path, $issues);
        $relationRef = $raw['relation_ref'] ?? null;
        $direction = $raw['direction'] ?? null;
        $mode = $raw['mode'] ?? null;
        $nextRelationDepth = $relationDepth + 1;

        if (!$this->isSemanticIdentifier($relationRef)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.relation_ref', 'Relation reference must be a stable semantic identifier.');
        }
        if (!in_array($direction, [RelationQueryConsumerInterface::DIRECTION_FROM, RelationQueryConsumerInterface::DIRECTION_TO], true)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.direction', 'Relation direction must be from or to.');
        }
        if (!in_array($mode, ['exists', 'any', 'all', 'count', 'exclude'], true)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.mode', 'Relation mode is unsupported by the V1 grammar.');
        }
        if ($nextRelationDepth > $budget->maxRelationDepth) {
            $this->issue($issues, 'wpe_query_cost_blocked', $path, 'Relation traversal exceeds the configured depth budget.');
        }
        if ($descriptor !== null && !$descriptor->supportsRelations) {
            $this->issue($issues, 'wpe_query_dependency_unavailable', $path, 'Data Source does not advertise Relations support.');
        }
        if ($this->relations === null || $context === null) {
            $this->issue($issues, 'wpe_query_dependency_unavailable', $path, 'Relation validation requires the public Relations consumer contract and execution context.');
        } elseif (is_string($relationRef)) {
            try {
                $relationDescription = $this->relations->describe($relationRef, $context);
                if (($relationDescription['contract_version'] ?? null) !== RelationQueryConsumerInterface::CONTRACT_VERSION) {
                    $this->issue($issues, 'wpe_query_dependency_unavailable', $path, 'Relations consumer contract version is incompatible.');
                }
            } catch (Throwable) {
                $this->issue($issues, 'wpe_query_dependency_unavailable', $path . '.relation_ref', 'Relation reference could not be resolved through the public Relations contract.');
            }
        }

        $relatedIds = $raw['related_ids'] ?? null;
        if ($relatedIds !== null && (!is_array($relatedIds) || !array_is_list($relatedIds))) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.related_ids', 'Related ids must be a list when provided.');
            $relatedIds = null;
        } elseif (is_array($relatedIds) && count($relatedIds) > RelationQueryConsumerInterface::MAX_BATCH_SIZE) {
            $this->issue($issues, 'wpe_query_cost_blocked', $path . '.related_ids', 'Related id batch exceeds the Relations public contract limit.');
        }

        $children = [];
        if (array_key_exists('filter', $raw) && $raw['filter'] !== null) {
            $nested = $this->parsePredicate(
                $raw['filter'],
                $path . '.filter',
                $depth + 1,
                $nextRelationDepth,
                $budget,
                $descriptor,
                $context,
                $predicateCount,
                $issues,
            );
            if ($nested !== null) {
                $children[] = $nested;
            }
        }

        return new QueryPredicate(QueryPredicateType::Relation, [
            'relation_ref' => $relationRef,
            'direction' => $direction,
            'mode' => $mode,
            'related_ids' => $relatedIds,
        ], $children);
    }

    /** @param list<QueryValidationIssue> $issues */
    private function parseProviderExtensionPredicate(array $raw, string $path, ?DataSourceDescriptor $descriptor, array &$issues): QueryPredicate
    {
        $this->assertAllowedKeys($raw, ['type', 'namespaced_type', 'payload'], $path, $issues);
        $namespacedType = $raw['namespaced_type'] ?? null;
        if (!$this->isSemanticIdentifier($namespacedType) || !is_string($namespacedType) || !str_contains($namespacedType, '.')) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.namespaced_type', 'Provider extension type must be a namespaced semantic identifier.');
        } elseif ($descriptor !== null && !in_array($namespacedType, $descriptor->predicates, true)) {
            $this->issue($issues, 'wpe_query_unsupported_operator', $path . '.namespaced_type', 'Provider extension is not advertised by the Data Source.');
        }
        $payload = $raw['payload'] ?? null;
        if (!is_array($payload)) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.payload', 'Provider extension payload must be an object.');
            $payload = [];
        }

        return new QueryPredicate(QueryPredicateType::ProviderExtension, [
            'namespaced_type' => $namespacedType,
            'payload' => $payload,
        ]);
    }

    /**
     * @param mixed $raw
     * @param list<QueryValidationIssue> $issues
     * @return list<QueryOrderClause>
     */
    private function parseOrderBy(mixed $raw, ?DataSourceDescriptor $descriptor, array &$issues): array
    {
        if (!is_array($raw) || !array_is_list($raw)) {
            $this->issue($issues, 'wpe_query_type_mismatch', '$.order_by', 'order_by must be a list.');
            return [];
        }

        if ($raw !== [] && $descriptor !== null && !in_array('field', $descriptor->sortModes, true)) {
            $this->issue($issues, 'wpe_query_unsupported_operator', '$.order_by', 'Data Source does not advertise field sorting.');
        }

        $orderBy = [];
        foreach ($raw as $index => $item) {
            $path = '$.order_by[' . $index . ']';
            if (!is_array($item)) {
                $this->issue($issues, 'wpe_query_type_mismatch', $path, 'Order clause must be an object.');
                continue;
            }
            $this->assertAllowedKeys($item, ['field_ref', 'direction'], $path, $issues);
            $fieldRef = $item['field_ref'] ?? null;
            $direction = $item['direction'] ?? null;
            $this->validateField($fieldRef, $path . '.field_ref', $descriptor, $issues);
            if (!in_array($direction, ['asc', 'desc'], true)) {
                $this->issue($issues, 'wpe_query_invalid_ast', $path . '.direction', 'Sort direction must be asc or desc.');
                continue;
            }
            if (is_string($fieldRef)) {
                $orderBy[] = new QueryOrderClause($fieldRef, $direction);
            }
        }

        return $orderBy;
    }

    /**
     * @param mixed $raw
     * @param list<QueryValidationIssue> $issues
     */
    private function parsePagination(
        mixed $raw,
        QueryValidationBudget $budget,
        ?DataSourceDescriptor $descriptor,
        array &$issues,
    ): ?QueryPagination {
        $path = '$.pagination';
        if (!is_array($raw)) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path, 'Pagination must be an object.');
            return null;
        }
        $this->assertAllowedKeys($raw, ['mode', 'page_size', 'offset', 'cursor'], $path, $issues);

        $mode = $raw['mode'] ?? null;
        $pageSize = $raw['page_size'] ?? null;
        $offset = $raw['offset'] ?? 0;
        $cursor = $raw['cursor'] ?? null;
        if (!$this->isSemanticIdentifier($mode)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path . '.mode', 'Pagination mode must be a stable semantic identifier.');
        } elseif ($descriptor !== null && !in_array($mode, $descriptor->paginationModes, true)) {
            $this->issue($issues, 'wpe_query_unsupported_operator', $path . '.mode', 'Pagination mode is not advertised by the Data Source.');
        }
        if (!is_int($pageSize) || $pageSize < 1) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.page_size', 'Page size must be a positive integer.');
        } else {
            $sourceLimit = $descriptor?->maxPageSize ?? $budget->maxPageSize;
            if ($pageSize > $budget->maxPageSize || $pageSize > $sourceLimit) {
                $this->issue($issues, 'wpe_query_cost_blocked', $path . '.page_size', 'Page size exceeds the configured or Data Source limit.');
            }
        }
        if (!is_int($offset) || $offset < 0) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.offset', 'Offset must be a non-negative integer.');
        }
        if ($cursor !== null && !is_string($cursor)) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path . '.cursor', 'Cursor must be a string when provided.');
        }
        if ($mode === 'cursor' && (!is_string($cursor) || $cursor === '')) {
            $this->issue($issues, 'wpe_query_cursor_invalid', $path . '.cursor', 'Cursor pagination requires a non-empty cursor token.');
        }

        if (!is_string($mode) || !is_int($pageSize) || !is_int($offset) || ($cursor !== null && !is_string($cursor))) {
            return null;
        }

        return new QueryPagination($mode, $pageSize, $offset, $cursor);
    }

    /**
     * @param mixed $raw
     * @param list<QueryValidationIssue> $issues
     * @return array<string,mixed>
     */
    private function parsePolicy(mixed $raw, string $path, array &$issues): array
    {
        if (!is_array($raw)) {
            $this->issue($issues, 'wpe_query_type_mismatch', $path, 'Policy must be an object.');
            return [];
        }

        return $raw;
    }

    /** @param list<QueryValidationIssue> $issues */
    private function validateField(mixed $fieldRef, string $path, ?DataSourceDescriptor $descriptor, array &$issues): void
    {
        if (!$this->isSemanticIdentifier($fieldRef)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path, 'Field reference must be a stable semantic identifier.');
            return;
        }
        if ($descriptor !== null && !array_key_exists($fieldRef, $descriptor->fieldSchema)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path, 'Field reference is not declared by the Data Source.');
        }
    }

    /** @param list<QueryValidationIssue> $issues */
    private function validateOperator(mixed $operator, string $path, ?DataSourceDescriptor $descriptor, array &$issues): void
    {
        if (!$this->isSemanticIdentifier($operator)) {
            $this->issue($issues, 'wpe_query_invalid_ast', $path, 'Operator must be a stable semantic identifier.');
            return;
        }
        if ($descriptor !== null && !in_array($operator, $descriptor->predicates, true)) {
            $this->issue($issues, 'wpe_query_unsupported_operator', $path, 'Operator is not advertised by the Data Source.');
        }
    }

    private function isSemanticIdentifier(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[a-z][a-z0-9._-]{0,127}$/', $value) === 1;
    }

    /**
     * @param array<mixed> $node
     * @param list<string> $allowed
     * @param list<QueryValidationIssue> $issues
     */
    private function assertAllowedKeys(array $node, array $allowed, string $path, array &$issues): void
    {
        foreach (array_keys($node) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                $this->issue($issues, 'wpe_query_invalid_ast', $path . '.' . (string) $key, 'Unknown semantic node or property.');
            }
        }
    }

    /** @param list<QueryValidationIssue> $issues */
    private function scanUnsafePayload(mixed $value, string $path, array &$issues): void
    {
        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $childPath = $path . '.' . (string) $key;
                if (is_string($key)) {
                    $normalizedKey = strtolower($key);
                    foreach (self::FORBIDDEN_KEY_FRAGMENTS as $fragment) {
                        if (str_contains($normalizedKey, $fragment)) {
                            $this->issue($issues, 'wpe_query_invalid_ast', $childPath, 'Executable, raw SQL, credential, endpoint, or unchecked identifier payloads are forbidden.');
                            break;
                        }
                    }
                }
                $this->scanUnsafePayload($child, $childPath, $issues);
            }
            return;
        }

        if (!is_string($value)) {
            return;
        }

        $unsafePatterns = [
            '/<\?(?:php|=)?/i',
            '/\beval\s*\(/i',
            '/\b(?:shell_exec|system|exec|passthru)\s*\(/i',
            '/\b(?:select|insert|update|delete|drop|alter|union)\b[\s\S]{0,80}\b(?:from|into|table|set|select)\b/i',
            '/;\s*(?:select|insert|update|delete|drop|alter)\b/i',
        ];
        foreach ($unsafePatterns as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                $this->issue($issues, 'wpe_query_invalid_ast', $path, 'Executable or raw SQL payload is forbidden.');
                return;
            }
        }
    }

    /** @param list<QueryValidationIssue> $issues */
    private function issue(array &$issues, string $code, string $path, string $message): void
    {
        $issues[] = new QueryValidationIssue($code, $path, $message);
    }
}
