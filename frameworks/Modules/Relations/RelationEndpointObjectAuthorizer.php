<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class RelationEndpointObjectAuthorizer
{
    /** @var Closure(array{object_type:string,object_subtype:?string,label:string},int):bool */
    private Closure $exists;

    /** @var Closure(array{object_type:string,object_subtype:?string,label:string},int,int):bool */
    private Closure $canMutate;

    /** @var Closure():?int */
    private Closure $currentUserId;

    /** @var Closure():int */
    private Closure $currentSiteId;

    /** @var Closure():?int */
    private Closure $currentNetworkId;

    private bool $contextBindingAvailable;

    /**
     * @param null|Closure(array{object_type:string,object_subtype:?string,label:string},int):bool $exists
     * @param null|Closure(array{object_type:string,object_subtype:?string,label:string},int,int):bool $canMutate
     * @param null|Closure():?int $currentUserId
     * @param null|Closure():int $currentSiteId
     * @param null|Closure():?int $currentNetworkId
     */
    public function __construct(
        ?Closure $exists = null,
        ?Closure $canMutate = null,
        ?Closure $currentUserId = null,
        ?Closure $currentSiteId = null,
        ?Closure $currentNetworkId = null,
    ) {
        $this->exists = $exists ?? static function (array $endpoint, int $objectId): bool {
            return match ($endpoint['object_type']) {
                'post' => self::postMatches($objectId, $endpoint['object_subtype']),
                'media' => self::postMatches($objectId, 'attachment'),
                'term' => self::termMatches($objectId, $endpoint['object_subtype']),
                'user' => function_exists('get_user_by') && get_user_by('id', $objectId) !== false,
                'comment' => function_exists('get_comment') && get_comment($objectId) !== null,
                default => false,
            };
        };
        $this->canMutate = $canMutate ?? static function (array $endpoint, int $objectId, int $actorId): bool {
            if (!function_exists('user_can')) {
                return false;
            }

            $capability = match ($endpoint['object_type']) {
                'post', 'media' => 'edit_post',
                'term' => 'edit_term',
                'user' => 'edit_user',
                'comment' => 'edit_comment',
                default => null,
            };

            return is_string($capability) && user_can($actorId, $capability, $objectId);
        };
        $this->contextBindingAvailable = $currentUserId !== null
            || $currentSiteId !== null
            || $currentNetworkId !== null
            || (function_exists('get_current_user_id') && function_exists('get_current_blog_id'));
        $this->currentUserId = $currentUserId ?? static function (): ?int {
            if (!function_exists('get_current_user_id')) {
                throw new LogicException('WordPress current-user API is unavailable.');
            }

            $userId = (int) get_current_user_id();
            return $userId > 0 ? $userId : null;
        };
        $this->currentSiteId = $currentSiteId ?? static function (): int {
            if (!function_exists('get_current_blog_id')) {
                throw new LogicException('WordPress current-site API is unavailable.');
            }

            return (int) get_current_blog_id();
        };
        $this->currentNetworkId = $currentNetworkId ?? static function (): ?int {
            if (!function_exists('get_current_network_id')) {
                return null;
            }

            $networkId = (int) get_current_network_id();
            return $networkId > 0 ? $networkId : null;
        };
    }

    /** @param array{object_type:string,object_subtype:?string,label:string} $endpoint */
    public function assertCanMutate(array $endpoint, int $objectId, ExecutionContext $context, string $side): void
    {
        if ($objectId < 1) {
            throw new InvalidArgumentException(sprintf('Relation %s object id must be positive.', $side));
        }
        $this->assertContextBound($context);

        $actorId = $context->principal->userId;
        if (!is_int($actorId) || $actorId < 1) {
            throw new RuntimeException('Relation edge mutation requires an authenticated WordPress user.');
        }

        if (!(($this->exists)($endpoint, $objectId))) {
            throw new RuntimeException(sprintf(
                'Relation %s object %d does not exist or does not match the configured endpoint.',
                $side,
                $objectId,
            ));
        }
        if (!(($this->canMutate)($endpoint, $objectId, $actorId))) {
            throw new RuntimeException(sprintf(
                'Relation %s object %d is not authorized for mutation by the current principal.',
                $side,
                $objectId,
            ));
        }
    }

    private function assertContextBound(ExecutionContext $context): void
    {
        if (!$context->principal->isAuthenticated() || $context->principal->actorType !== 'user') {
            throw new RuntimeException('Relation edge mutation execution context requires an authenticated user.');
        }
        if (!$this->contextBindingAvailable) {
            return;
        }

        $activeUserId = ($this->currentUserId)();
        $activeSiteId = ($this->currentSiteId)();
        if ($context->principal->userId !== $activeUserId || $context->siteId !== $activeSiteId) {
            throw new RuntimeException('Relation edge mutation execution context is not bound to the active user/site.');
        }

        if ($context->networkId !== null) {
            $activeNetworkId = ($this->currentNetworkId)();
            if ($activeNetworkId !== $context->networkId) {
                throw new RuntimeException('Relation edge mutation execution context is not bound to the active network.');
            }
        }
    }

    private static function postMatches(int $objectId, ?string $postType): bool
    {
        if (!is_string($postType) || $postType === '' || !function_exists('get_post')) {
            return false;
        }
        $post = get_post($objectId);
        return is_object($post) && isset($post->post_type) && $post->post_type === $postType;
    }

    private static function termMatches(int $objectId, ?string $taxonomy): bool
    {
        if (!is_string($taxonomy) || $taxonomy === '' || !function_exists('get_term')) {
            return false;
        }
        $term = get_term($objectId, $taxonomy);
        if ($term === null || $term === false) {
            return false;
        }
        if (function_exists('is_wp_error') && is_wp_error($term)) {
            return false;
        }
        return is_object($term)
            && isset($term->term_id, $term->taxonomy)
            && (int) $term->term_id === $objectId
            && $term->taxonomy === $taxonomy;
    }
}
