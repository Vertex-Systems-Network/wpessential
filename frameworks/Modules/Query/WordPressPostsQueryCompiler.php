<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

final class WordPressPostsQueryCompiler implements QueryProviderCompilerInterface
{
    public const PROVIDER = 'wordpress.wp_query.posts';
    public const SOURCE_REF = 'wordpress.posts';

    /** @var list<string> */
    private const PROJECTION_FIELDS = [
        'post.id',
        'post.title',
        'post.slug',
        'post.date',
        'post.modified',
        'post.status',
        'post.type',
        'post.author_id',
        'post.parent_id',
        'post.excerpt',
        'post.content',
    ];

    /** @var array<string,string> */
    private const ORDER_FIELDS = [
        'post.id' => 'ID',
        'post.title' => 'title',
        'post.slug' => 'name',
        'post.date' => 'date',
        'post.modified' => 'modified',
        'post.author_id' => 'author',
        'post.parent_id' => 'parent',
        'post.type' => 'type',
    ];

    public function supports(QueryDefinition $definition): bool
    {
        return $definition->source->sourceRef === self::SOURCE_REF
            && $definition->source->sourceType === self::SOURCE_REF
            && $definition->operation === 'select';
    }

    public function compile(QueryDefinition $definition): QueryProviderPlan
    {
        if (!$this->supports($definition)) {
            throw $this->unsupported('$.source', 'Compiler supports only select queries for wordpress.posts.');
        }

        if ($definition->distinct) {
            throw $this->unsupported('$.distinct', 'Generic DISTINCT semantics are not supported by the native posts compiler V1.');
        }

        if ($definition->parameters !== []) {
            throw $this->unsupported('$.parameters', 'Parameter binding is a later Query execution tranche.');
        }

        $projection = $this->compileProjection($definition->projection);
        $arguments = [
            'ignore_sticky_posts' => true,
            'suppress_filters' => true,
        ];

        if ($definition->filter !== null) {
            $arguments = $this->mergeArguments(
                $arguments,
                $this->compilePredicate($definition->filter, '$.filter'),
                '$.filter',
            );
        }

        $arguments = $this->mergeArguments(
            $arguments,
            $this->compileOrdering($definition->orderBy),
            '$.order_by',
        );
        $arguments = $this->mergeArguments(
            $arguments,
            $this->compilePagination($definition->pagination),
            '$.pagination',
        );

        if ($projection === ['post.id']) {
            $arguments['fields'] = 'ids';
        }

        ksort($arguments);

        return new QueryProviderPlan(
            provider: self::PROVIDER,
            sourceRef: self::SOURCE_REF,
            arguments: $arguments,
            projection: $projection,
        );
    }

    /**
     * @param list<string> $projection
     * @return list<string>
     */
    private function compileProjection(array $projection): array
    {
        if ($projection === []) {
            throw $this->invalid('$.projection', 'Projection must explicitly name at least one supported post field.');
        }

        foreach ($projection as $index => $fieldRef) {
            if (!in_array($fieldRef, self::PROJECTION_FIELDS, true)) {
                throw $this->unsupported(
                    '$.projection[' . $index . ']',
                    'Projection field is not supported by the native posts compiler V1.',
                );
            }
        }

        return $projection;
    }

    /** @return array<string,mixed> */
    private function compilePredicate(QueryPredicate $predicate, string $path): array
    {
        return match ($predicate->type) {
            QueryPredicateType::Group => $this->compileGroup($predicate, $path),
            QueryPredicateType::Comparison => $this->compileComparison($predicate, $path),
            QueryPredicateType::SetMembership => $this->compileSetMembership($predicate, $path),
            QueryPredicateType::Text => $this->compileText($predicate, $path),
            QueryPredicateType::Existence,
            QueryPredicateType::Range,
            QueryPredicateType::Taxonomy,
            QueryPredicateType::DateTime,
            QueryPredicateType::Field,
            QueryPredicateType::Relation,
            QueryPredicateType::ProviderExtension => throw $this->unsupported(
                $path,
                sprintf('Predicate type %s is not supported by the native posts compiler V1.', $predicate->type->value),
            ),
        };
    }

    /** @return array<string,mixed> */
    private function compileGroup(QueryPredicate $predicate, string $path): array
    {
        if (($predicate->payload['boolean'] ?? null) !== 'and') {
            throw $this->unsupported($path . '.boolean', 'Cross-clause OR compilation requires a later bounded provider tranche.');
        }

        if ($predicate->children === []) {
            throw $this->invalid($path . '.children', 'AND group must contain at least one predicate.');
        }

        $arguments = [];
        foreach ($predicate->children as $index => $child) {
            $childPath = $path . '.children[' . $index . ']';
            $arguments = $this->mergeArguments(
                $arguments,
                $this->compilePredicate($child, $childPath),
                $childPath,
            );
        }

        return $arguments;
    }

    /** @return array<string,mixed> */
    private function compileComparison(QueryPredicate $predicate, string $path): array
    {
        $fieldRef = $predicate->payload['field_ref'] ?? null;
        $operator = $predicate->payload['operator'] ?? null;
        $value = $predicate->payload['value'] ?? null;

        if (!is_string($fieldRef) || !is_string($operator)) {
            throw $this->invalid($path, 'Comparison predicate is missing its validated field/operator semantics.');
        }

        return match ($fieldRef . ':' . $operator) {
            'post.id:eq' => ['p' => $this->positiveInt($value, $path . '.value')],
            'post.id:neq' => ['post__not_in' => [$this->positiveInt($value, $path . '.value')]],
            'post.parent_id:eq' => ['post_parent' => $this->nonNegativeInt($value, $path . '.value')],
            'post.author_id:eq' => ['author' => $this->positiveInt($value, $path . '.value')],
            'post.status:eq' => ['post_status' => $this->slug($value, $path . '.value')],
            'post.type:eq' => ['post_type' => $this->slug($value, $path . '.value')],
            'post.slug:eq' => ['name' => $this->slug($value, $path . '.value')],
            'post.title:eq' => ['title' => $this->nonEmptyString($value, $path . '.value')],
            default => throw $this->unsupported(
                $path . '.operator',
                sprintf('Comparison %s:%s cannot be represented safely by the native posts compiler V1.', $fieldRef, $operator),
            ),
        };
    }

    /** @return array<string,mixed> */
    private function compileSetMembership(QueryPredicate $predicate, string $path): array
    {
        $fieldRef = $predicate->payload['field_ref'] ?? null;
        $operator = $predicate->payload['operator'] ?? null;
        $values = $predicate->payload['values'] ?? null;

        if (!is_string($fieldRef) || !is_string($operator) || !is_array($values) || !array_is_list($values)) {
            throw $this->invalid($path, 'Set predicate is missing its validated field/operator/value-list semantics.');
        }
        if ($values === []) {
            throw $this->invalid($path . '.values', 'Set predicate values cannot be empty.');
        }

        return match ($fieldRef . ':' . $operator) {
            'post.id:in' => ['post__in' => $this->positiveIntList($values, $path . '.values')],
            'post.id:not_in' => ['post__not_in' => $this->positiveIntList($values, $path . '.values')],
            'post.parent_id:in' => ['post_parent__in' => $this->nonNegativeIntList($values, $path . '.values')],
            'post.parent_id:not_in' => ['post_parent__not_in' => $this->nonNegativeIntList($values, $path . '.values')],
            'post.author_id:in' => ['author__in' => $this->positiveIntList($values, $path . '.values')],
            'post.author_id:not_in' => ['author__not_in' => $this->positiveIntList($values, $path . '.values')],
            'post.status:in' => ['post_status' => $this->slugList($values, $path . '.values')],
            'post.type:in' => ['post_type' => $this->slugList($values, $path . '.values')],
            default => throw $this->unsupported(
                $path . '.operator',
                sprintf('Set membership %s:%s cannot be represented safely by the native posts compiler V1.', $fieldRef, $operator),
            ),
        };
    }

    /** @return array<string,mixed> */
    private function compileText(QueryPredicate $predicate, string $path): array
    {
        $fieldRef = $predicate->payload['field_ref'] ?? null;
        $searchScope = $predicate->payload['search_scope'] ?? null;
        $mode = $predicate->payload['mode'] ?? null;
        $value = $predicate->payload['value'] ?? null;

        if ($fieldRef !== null) {
            throw $this->unsupported(
                $path . '.field_ref',
                'WP_Query search is not field-exact, so a field-scoped text predicate would be an unsafe approximation.',
            );
        }
        if ($searchScope !== 'posts.default' || $mode !== 'contains') {
            throw $this->unsupported(
                $path,
                'Only posts.default contains search maps exactly to the bounded native WP_Query search plan.',
            );
        }

        return ['s' => $this->nonEmptyString($value, $path . '.value')];
    }

    /**
     * @param list<QueryOrderClause> $orderBy
     * @return array<string,mixed>
     */
    private function compileOrdering(array $orderBy): array
    {
        if ($orderBy === []) {
            return [];
        }

        $compiled = [];
        foreach ($orderBy as $index => $clause) {
            $providerField = self::ORDER_FIELDS[$clause->fieldRef] ?? null;
            if ($providerField === null) {
                throw $this->unsupported(
                    '$.order_by[' . $index . '].field_ref',
                    'Sort field is not supported by the native posts compiler V1.',
                );
            }
            if (array_key_exists($providerField, $compiled)) {
                throw $this->invalid(
                    '$.order_by[' . $index . ']',
                    'Duplicate provider sort field is ambiguous.',
                );
            }

            $compiled[$providerField] = match ($clause->direction) {
                'asc' => 'ASC',
                'desc' => 'DESC',
                default => throw $this->invalid(
                    '$.order_by[' . $index . '].direction',
                    'Sort direction must already be normalized to asc or desc.',
                ),
            };
        }

        return ['orderby' => $compiled];
    }

    /** @return array<string,mixed> */
    private function compilePagination(QueryPagination $pagination): array
    {
        if ($pagination->mode !== 'offset') {
            throw $this->unsupported(
                '$.pagination.mode',
                'Cursor compilation/signing is a later bounded Query tranche.',
            );
        }
        if ($pagination->cursor !== null) {
            throw $this->invalid('$.pagination.cursor', 'Offset pagination cannot carry a cursor token.');
        }
        if ($pagination->pageSize < 1 || $pagination->offset < 0) {
            throw $this->invalid('$.pagination', 'Pagination values are outside the validated range.');
        }

        return [
            'posts_per_page' => $pagination->pageSize,
            'offset' => $pagination->offset,
        ];
    }

    /**
     * @param array<string,mixed> $current
     * @param array<string,mixed> $next
     * @return array<string,mixed>
     */
    private function mergeArguments(array $current, array $next, string $path): array
    {
        foreach ($next as $key => $value) {
            if (array_key_exists($key, $current) && $current[$key] !== $value) {
                throw $this->unsupported(
                    $path,
                    sprintf('Multiple semantic clauses compete for the same WP_Query argument %s.', $key),
                );
            }
            $current[$key] = $value;
        }

        return $current;
    }

    private function positiveInt(mixed $value, string $path): int
    {
        if (!is_int($value) || $value < 1) {
            throw $this->typeMismatch($path, 'Value must be a positive integer.');
        }

        return $value;
    }

    private function nonNegativeInt(mixed $value, string $path): int
    {
        if (!is_int($value) || $value < 0) {
            throw $this->typeMismatch($path, 'Value must be a non-negative integer.');
        }

        return $value;
    }

    private function nonEmptyString(mixed $value, string $path): string
    {
        if (!is_string($value) || trim($value) === '') {
            throw $this->typeMismatch($path, 'Value must be a non-empty string.');
        }

        return $value;
    }

    private function slug(mixed $value, string $path): string
    {
        if (!is_string($value) || preg_match('/^[a-z0-9][a-z0-9_-]{0,199}$/', $value) !== 1) {
            throw $this->typeMismatch($path, 'Value must be a normalized WordPress slug.');
        }

        return $value;
    }

    /**
     * @param list<mixed> $values
     * @return list<int>
     */
    private function positiveIntList(array $values, string $path): array
    {
        $normalized = [];
        foreach ($values as $index => $value) {
            $normalized[] = $this->positiveInt($value, $path . '[' . $index . ']');
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @param list<mixed> $values
     * @return list<int>
     */
    private function nonNegativeIntList(array $values, string $path): array
    {
        $normalized = [];
        foreach ($values as $index => $value) {
            $normalized[] = $this->nonNegativeInt($value, $path . '[' . $index . ']');
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @param list<mixed> $values
     * @return list<string>
     */
    private function slugList(array $values, string $path): array
    {
        $normalized = [];
        foreach ($values as $index => $value) {
            $normalized[] = $this->slug($value, $path . '[' . $index . ']');
        }

        return array_values(array_unique($normalized));
    }

    private function invalid(string $path, string $message): QueryProviderCompilationException
    {
        return new QueryProviderCompilationException('wpe_query_invalid_ast', $path, $message);
    }

    private function typeMismatch(string $path, string $message): QueryProviderCompilationException
    {
        return new QueryProviderCompilationException('wpe_query_type_mismatch', $path, $message);
    }

    private function unsupported(string $path, string $message): QueryProviderCompilationException
    {
        return new QueryProviderCompilationException('wpe_query_unsupported_operator', $path, $message);
    }
}
