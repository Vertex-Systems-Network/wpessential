<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Auth;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use LogicException;
use RuntimeException;
use WPEssential\Platform\Auth\ExecutionContext;

final class WordPressPostResourceAuthorizer
{
    /** @var Closure():?int */
    private Closure $currentUserId;

    /** @var Closure():int */
    private Closure $currentSiteId;

    /** @var Closure():?int */
    private Closure $currentNetworkId;

    /** @var Closure(string,int):bool */
    private Closure $currentUserCan;

    /**
     * @param null|callable():?int $currentUserId
     * @param null|callable():int $currentSiteId
     * @param null|callable():?int $currentNetworkId
     * @param null|callable(string,int):bool $currentUserCan
     */
    public function __construct(
        ?callable $currentUserId = null,
        ?callable $currentSiteId = null,
        ?callable $currentNetworkId = null,
        ?callable $currentUserCan = null,
    ) {
        $this->currentUserId = $currentUserId !== null
            ? Closure::fromCallable($currentUserId)
            : static function (): ?int {
                if (!function_exists('get_current_user_id')) {
                    throw new LogicException('WordPress current-user API is unavailable.');
                }

                $userId = (int) get_current_user_id();
                return $userId > 0 ? $userId : null;
            };

        $this->currentSiteId = $currentSiteId !== null
            ? Closure::fromCallable($currentSiteId)
            : static function (): int {
                if (!function_exists('get_current_blog_id')) {
                    throw new LogicException('WordPress current-site API is unavailable.');
                }

                return (int) get_current_blog_id();
            };

        $this->currentNetworkId = $currentNetworkId !== null
            ? Closure::fromCallable($currentNetworkId)
            : static function (): ?int {
                if (!function_exists('get_current_network_id')) {
                    return null;
                }

                $networkId = (int) get_current_network_id();
                return $networkId > 0 ? $networkId : null;
            };

        $this->currentUserCan = $currentUserCan !== null
            ? Closure::fromCallable($currentUserCan)
            : static function (string $capability, int $postId): bool {
                if (!function_exists('current_user_can')) {
                    throw new LogicException('WordPress capability API is unavailable.');
                }

                return current_user_can($capability, $postId);
            };
    }

    public function assertCanRead(ExecutionContext $context, int $postId): void
    {
        $this->assertCan($context, $postId, 'read_post', 'read');
    }

    public function assertCanEdit(ExecutionContext $context, int $postId): void
    {
        $this->assertCan($context, $postId, 'edit_post', 'edit');
    }

    public function assertCanDelete(ExecutionContext $context, int $postId): void
    {
        $this->assertCan($context, $postId, 'delete_post', 'delete');
    }

    private function assertCan(
        ExecutionContext $context,
        int $postId,
        string $capability,
        string $operation,
    ): void {
        $this->assertContextBound($context, $postId);
        if (!($this->currentUserCan)($capability, $postId)) {
            throw new RuntimeException('WordPress post resource ' . $operation . ' access denied.');
        }
    }

    private function assertContextBound(ExecutionContext $context, int $postId): void
    {
        if ($postId < 1) {
            throw new RuntimeException('WordPress post resource id must be positive.');
        }
        if (!$context->principal->isAuthenticated() || $context->principal->actorType !== 'user') {
            throw new RuntimeException('WordPress post resource execution context requires an authenticated user.');
        }

        $activeUserId = ($this->currentUserId)();
        $activeSiteId = ($this->currentSiteId)();
        if ($context->principal->userId !== $activeUserId || $context->siteId !== $activeSiteId) {
            throw new RuntimeException('WordPress post resource execution context is not bound to the active user/site.');
        }

        if ($context->networkId !== null) {
            $activeNetworkId = ($this->currentNetworkId)();
            if ($activeNetworkId !== $context->networkId) {
                throw new RuntimeException('WordPress post resource execution context is not bound to the active network.');
            }
        }
    }
}
