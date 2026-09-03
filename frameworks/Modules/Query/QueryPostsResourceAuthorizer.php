<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use RuntimeException;
use Throwable;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class QueryPostsResourceAuthorizer
{
    /** @var Closure(int):?object */
    private Closure $loadPost;

    /** @var Closure(int,int):bool */
    private Closure $canRead;

    /**
     * @param null|Closure(int):?object $loadPost
     * @param null|Closure(int,int):bool $canRead
     */
    public function __construct(?Closure $loadPost = null, ?Closure $canRead = null)
    {
        $this->loadPost = $loadPost ?? static function (int $postId): ?object {
            if (!function_exists('get_post')) {
                throw new RuntimeException('WordPress post API is unavailable.');
            }
            $post = get_post($postId);
            return is_object($post) ? $post : null;
        };
        $this->canRead = $canRead ?? static function (int $actorId, int $postId): bool {
            if (!function_exists('user_can')) {
                throw new RuntimeException('WordPress capability API is unavailable.');
            }
            return user_can($actorId, 'read_post', $postId);
        };
    }

    /**
     * @param array{object_type:string,object_subtype:?string} $endpoint
     * @param list<int> $postIds
     */
    public function assertCanRead(array $endpoint, array $postIds, ExecutionContext $context): void
    {
        if (($endpoint['object_type'] ?? null) !== 'post') {
            throw new QueryPlanningException(
                'wpe_query_unsupported_operator',
                '$.filter.relation_ref',
                'Relation predicate V1 supports only post anchor endpoints.',
            );
        }

        $subtype = $endpoint['object_subtype'] ?? null;
        if ($subtype !== null && (!is_string($subtype) || $subtype === '')) {
            throw new QueryPlanningException(
                'wpe_query_dependency_unavailable',
                '$.filter.relation_ref',
                'Relation anchor endpoint subtype is malformed.',
            );
        }

        $actorId = $context->principal->userId;
        if (!is_int($actorId) || $actorId < 1) {
            throw new QueryPlanningException(
                'wpe_query_policy_denied',
                '$.source',
                'Query relation execution requires an authenticated WordPress principal.',
            );
        }

        foreach ($postIds as $postId) {
            if (!is_int($postId) || $postId < 1) {
                throw new QueryPlanningException(
                    'wpe_query_invalid_ast',
                    '$.filter',
                    'Relation anchor ids must be positive integers.',
                );
            }

            try {
                $post = ($this->loadPost)($postId);
                $allowed = ($this->canRead)($actorId, $postId);
            } catch (Throwable) {
                throw new QueryPlanningException(
                    'wpe_query_dependency_unavailable',
                    '$.source',
                    'WordPress post resource authorization is unavailable.',
                );
            }

            $postType = is_object($post) && isset($post->post_type) && is_string($post->post_type)
                ? $post->post_type
                : null;
            if ($post === null || ($subtype !== null && $postType !== $subtype) || !$allowed) {
                throw new QueryPlanningException(
                    'wpe_query_policy_denied',
                    '$.source',
                    'Canonical post resource authorization denied a relation anchor.',
                );
            }
        }
    }
}
