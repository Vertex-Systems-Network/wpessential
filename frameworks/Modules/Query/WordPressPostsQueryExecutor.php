<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use RuntimeException;
use Throwable;

final class WordPressPostsQueryExecutor implements QueryProviderExecutorInterface
{
    private const MAX_PAGE_SIZE = 100;

    /** @var list<string> */
    private const ALLOWED_ARGUMENTS = [
        'author',
        'author__in',
        'author__not_in',
        'fields',
        'ignore_sticky_posts',
        'name',
        'offset',
        'orderby',
        'p',
        'post__in',
        'post__not_in',
        'post_parent',
        'post_parent__in',
        'post_parent__not_in',
        'post_status',
        'post_type',
        'posts_per_page',
        's',
        'suppress_filters',
        'title',
    ];

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

    /** @var list<string> */
    private const ORDER_FIELDS = [
        'ID',
        'title',
        'name',
        'date',
        'modified',
        'author',
        'parent',
        'type',
    ];

    /** @var Closure(array<string,mixed>):object */
    private readonly Closure $queryFactory;

    /** @param null|Closure(array<string,mixed>):object $queryFactory */
    public function __construct(?Closure $queryFactory = null)
    {
        $this->queryFactory = $queryFactory ?? static function (array $arguments): object {
            if (!class_exists('WP_Query')) {
                throw new RuntimeException('WordPress WP_Query is unavailable.');
            }

            return new \WP_Query($arguments);
        };
    }

    public function supports(QueryProviderPlan $plan): bool
    {
        return $plan->provider === WordPressPostsQueryCompiler::PROVIDER
            && $plan->sourceRef === WordPressPostsQueryCompiler::SOURCE_REF;
    }

    public function execute(QueryProviderPlan $plan): QueryExecutionResult|QueryExecutionError
    {
        $validationError = $this->validatePlan($plan);
        if ($validationError !== null) {
            return $validationError;
        }

        try {
            $query = ($this->queryFactory)($plan->arguments);
        } catch (Throwable) {
            return $this->providerFailure(
                '$.source',
                'Native WordPress posts provider execution failed.',
            );
        }

        $native = get_object_vars($query);
        $posts = $native['posts'] ?? null;
        $foundPosts = $this->nonNegativeIntLike($native['found_posts'] ?? null);

        if (!is_array($posts) || !array_is_list($posts) || $foundPosts === null) {
            return $this->providerFailure(
                '$.execution',
                'Native WordPress posts provider returned an invalid result shape.',
            );
        }

        $rows = [];
        foreach ($posts as $index => $post) {
            $row = $this->normalizeRow($post, $plan->projection, '$.execution.posts[' . $index . ']');
            if ($row instanceof QueryExecutionError) {
                return $row;
            }
            $rows[] = $row;
        }

        if ($foundPosts < count($rows)) {
            return $this->providerFailure(
                '$.execution.found_posts',
                'Native WordPress posts provider returned an inconsistent total count.',
            );
        }

        return new QueryExecutionResult(
            provider: $plan->provider,
            sourceRef: $plan->sourceRef,
            projection: $plan->projection,
            rows: $rows,
            total: $foundPosts,
            returned: count($rows),
        );
    }

    private function validatePlan(QueryProviderPlan $plan): ?QueryExecutionError
    {
        if (!$this->supports($plan)) {
            return new QueryExecutionError(
                errorCode: 'wpe_query_dependency_unavailable',
                path: '$.source',
                message: 'Native WordPress posts executor does not support this provider plan.',
            );
        }

        if ($plan->projection === []) {
            return $this->providerFailure('$.projection', 'Provider plan projection is empty.');
        }

        foreach ($plan->projection as $index => $fieldRef) {
            if (!in_array($fieldRef, self::PROJECTION_FIELDS, true)) {
                return $this->providerFailure(
                    '$.projection[' . $index . ']',
                    'Provider plan contains an unsupported posts projection field.',
                );
            }
        }

        foreach ($plan->arguments as $key => $_value) {
            if (!is_string($key) || !in_array($key, self::ALLOWED_ARGUMENTS, true)) {
                return $this->providerFailure(
                    '$.execution.arguments',
                    'Provider plan contains a non-certified WP_Query argument.',
                );
            }
        }

        if (($plan->arguments['ignore_sticky_posts'] ?? null) !== true) {
            return $this->providerFailure(
                '$.execution.arguments.ignore_sticky_posts',
                'Provider plan must disable sticky-post widening.',
            );
        }
        if (($plan->arguments['suppress_filters'] ?? null) !== true) {
            return $this->providerFailure(
                '$.execution.arguments.suppress_filters',
                'Provider plan must suppress third-party query filters.',
            );
        }

        $pageSize = $plan->arguments['posts_per_page'] ?? null;
        if (!is_int($pageSize) || $pageSize < 1 || $pageSize > self::MAX_PAGE_SIZE) {
            return $this->providerFailure(
                '$.execution.arguments.posts_per_page',
                'Provider plan page size is outside the certified native bound.',
            );
        }

        $offset = $plan->arguments['offset'] ?? null;
        if (!is_int($offset) || $offset < 0) {
            return $this->providerFailure(
                '$.execution.arguments.offset',
                'Provider plan offset must be a non-negative integer.',
            );
        }

        $idsOnly = $plan->projection === ['post.id'];
        $fields = $plan->arguments['fields'] ?? null;
        if ($idsOnly && $fields !== 'ids') {
            return $this->providerFailure(
                '$.execution.arguments.fields',
                'ID-only projection must use the bounded WP_Query ids field mode.',
            );
        }
        if (!$idsOnly && array_key_exists('fields', $plan->arguments)) {
            return $this->providerFailure(
                '$.execution.arguments.fields',
                'Projected post rows cannot use a partial native WP_Query fields mode.',
            );
        }

        if (isset($plan->arguments['p']) && !$this->isPositiveInt($plan->arguments['p'])) {
            return $this->providerFailure('$.execution.arguments.p', 'Post id argument is invalid.');
        }
        if (isset($plan->arguments['author']) && !$this->isPositiveInt($plan->arguments['author'])) {
            return $this->providerFailure('$.execution.arguments.author', 'Author id argument is invalid.');
        }
        if (isset($plan->arguments['post_parent']) && !$this->isNonNegativeInt($plan->arguments['post_parent'])) {
            return $this->providerFailure('$.execution.arguments.post_parent', 'Parent id argument is invalid.');
        }

        foreach (['post__in', 'post__not_in', 'author__in', 'author__not_in'] as $key) {
            if (isset($plan->arguments[$key]) && !$this->isIntegerList($plan->arguments[$key], false)) {
                return $this->providerFailure('$.execution.arguments.' . $key, 'Provider id list argument is invalid.');
            }
        }
        foreach (['post_parent__in', 'post_parent__not_in'] as $key) {
            if (isset($plan->arguments[$key]) && !$this->isIntegerList($plan->arguments[$key], true)) {
                return $this->providerFailure('$.execution.arguments.' . $key, 'Provider parent id list argument is invalid.');
            }
        }

        foreach (['post_status', 'post_type'] as $key) {
            if (isset($plan->arguments[$key]) && !$this->isSlugOrSlugList($plan->arguments[$key])) {
                return $this->providerFailure('$.execution.arguments.' . $key, 'Provider slug argument is invalid.');
            }
        }

        if (isset($plan->arguments['name']) && !$this->isSlug($plan->arguments['name'])) {
            return $this->providerFailure('$.execution.arguments.name', 'Provider post slug argument is invalid.');
        }
        foreach (['s', 'title'] as $key) {
            if (isset($plan->arguments[$key]) && !$this->isNonEmptyString($plan->arguments[$key])) {
                return $this->providerFailure('$.execution.arguments.' . $key, 'Provider text argument is invalid.');
            }
        }

        if (isset($plan->arguments['orderby']) && !$this->isOrdering($plan->arguments['orderby'])) {
            return $this->providerFailure('$.execution.arguments.orderby', 'Provider ordering argument is invalid.');
        }

        return null;
    }

    /**
     * @param list<string> $projection
     * @return array<string,mixed>|QueryExecutionError
     */
    private function normalizeRow(mixed $nativePost, array $projection, string $path): array|QueryExecutionError
    {
        if ($projection === ['post.id']) {
            $id = $this->positiveIntLike($nativePost);
            if ($id === null) {
                return $this->providerFailure($path, 'Native ID projection returned a non-positive post id.');
            }

            return ['post.id' => $id];
        }

        if (!is_object($nativePost)) {
            return $this->providerFailure($path, 'Native posts provider returned a non-object post row.');
        }

        $values = get_object_vars($nativePost);
        $row = [];
        foreach ($projection as $fieldRef) {
            $normalized = $this->normalizeProjectedField($fieldRef, $values, $path);
            if ($normalized instanceof QueryExecutionError) {
                return $normalized;
            }
            $row[$fieldRef] = $normalized;
        }

        return $row;
    }

    /** @param array<string,mixed> $values */
    private function normalizeProjectedField(
        string $fieldRef,
        array $values,
        string $path,
    ): int|string|QueryExecutionError {
        $property = match ($fieldRef) {
            'post.id' => 'ID',
            'post.title' => 'post_title',
            'post.slug' => 'post_name',
            'post.date' => 'post_date',
            'post.modified' => 'post_modified',
            'post.status' => 'post_status',
            'post.type' => 'post_type',
            'post.author_id' => 'post_author',
            'post.parent_id' => 'post_parent',
            'post.excerpt' => 'post_excerpt',
            'post.content' => 'post_content',
            default => null,
        };

        if ($property === null || !array_key_exists($property, $values)) {
            return $this->providerFailure($path . '.' . $fieldRef, 'Native post row is missing a projected field.');
        }

        $value = $values[$property];
        if ($fieldRef === 'post.id') {
            $normalizedId = $this->positiveIntLike($value);
            return $normalizedId ?? $this->providerFailure($path . '.' . $fieldRef, 'Projected post id is invalid.');
        }
        if ($fieldRef === 'post.author_id' || $fieldRef === 'post.parent_id') {
            $normalizedId = $this->nonNegativeIntLike($value);
            return $normalizedId ?? $this->providerFailure($path . '.' . $fieldRef, 'Projected post reference id is invalid.');
        }
        if (!is_string($value)) {
            return $this->providerFailure($path . '.' . $fieldRef, 'Projected post scalar is not a string.');
        }

        return $value;
    }

    private function isPositiveInt(mixed $value): bool
    {
        return is_int($value) && $value > 0;
    }

    private function isNonNegativeInt(mixed $value): bool
    {
        return is_int($value) && $value >= 0;
    }

    private function isIntegerList(mixed $value, bool $allowZero): bool
    {
        if (!is_array($value) || !array_is_list($value) || $value === []) {
            return false;
        }

        foreach ($value as $item) {
            if (!is_int($item) || ($allowZero ? $item < 0 : $item < 1)) {
                return false;
            }
        }

        return true;
    }

    private function isSlugOrSlugList(mixed $value): bool
    {
        if (is_string($value)) {
            return $this->isSlug($value);
        }
        if (!is_array($value) || !array_is_list($value) || $value === []) {
            return false;
        }

        foreach ($value as $item) {
            if (!$this->isSlug($item)) {
                return false;
            }
        }

        return true;
    }

    private function isSlug(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/^[a-z0-9][a-z0-9_-]{0,199}$/', $value) === 1;
    }

    private function isNonEmptyString(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    private function isOrdering(mixed $value): bool
    {
        if (!is_array($value) || $value === [] || array_is_list($value)) {
            return false;
        }

        foreach ($value as $field => $direction) {
            if (
                !is_string($field)
                || !in_array($field, self::ORDER_FIELDS, true)
                || ($direction !== 'ASC' && $direction !== 'DESC')
            ) {
                return false;
            }
        }

        return true;
    }

    private function positiveIntLike(mixed $value): ?int
    {
        $normalized = $this->nonNegativeIntLike($value);
        return $normalized !== null && $normalized > 0 ? $normalized : null;
    }

    private function nonNegativeIntLike(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }
        if (is_string($value) && preg_match('/^(0|[1-9][0-9]*)$/', $value) === 1) {
            $normalized = (int) $value;
            return $normalized >= 0 ? $normalized : null;
        }

        return null;
    }

    private function providerFailure(string $path, string $message): QueryExecutionError
    {
        return new QueryExecutionError(
            errorCode: 'wpe_query_provider_failed',
            path: $path,
            message: $message,
        );
    }
}
