<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

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
                    throw new LogicException('WordPress get_current_user_id() is unavailable.');
                }
                $id = (int) get_current_user_id();
                return $id > 0 ? $id : null;
            };
        $this->currentSiteId = $currentSiteId !== null
            ? Closure::fromCallable($currentSiteId)
            : static function (): int {
                if (!function_exists('get_current_blog_id')) {
                    throw new LogicException('WordPress get_current_blog_id() is unavailable.');
                }
                return (int) get_current_blog_id();
            };
        $this->currentNetworkId = $currentNetworkId !== null
            ? Closure::fromCallable($currentNetworkId)
            : static function (): ?int {
                if (!function_exists('get_current_network_id')) {
                    return null;
                }
                $id = (int) get_current_network_id();
                return $id > 0 ? $id : null;
            };
        $this->currentUserCan = $currentUserCan !== null
            ? Closure::fromCallable($currentUserCan)
            : static function (string $capability, int $postId): bool {
                if (!function_exists('current_user_can')) {
                    throw new LogicException('WordPress current_user_can() is unavailable.');
                }
                return current_user_can($capability, $postId);
            };
    }

    public function assertCanRead(ExecutionContext $context, int $postId): void
    {
        $this->assertContextBound($context, $postId);
        if (!($this->currentUserCan)('read_post', $postId)) {
            throw new RuntimeException('Field value resource access denied.');
        }
    }

    public function assertCanWrite(ExecutionContext $context, int $postId): void
    {
        $this->assertContextBound($context, $postId);
        if (!($this->currentUserCan)('edit_post', $postId)) {
            throw new RuntimeException('Field value resource mutation denied.');
        }
    }

    private function assertContextBound(ExecutionContext $context, int $postId): void
    {
        if ($postId < 1
            || !$context->principal->isAuthenticated()
            || $context->principal->actorType !== 'user'
            || $context->principal->userId !== ($this->currentUserId)()
            || $context->siteId !== ($this->currentSiteId)()
        ) {
            throw new RuntimeException('Field value execution context is not bound to the active WordPress user/site.');
        }

        $activeNetwork = ($this->currentNetworkId)();
        if ($context->networkId !== null && $activeNetwork !== $context->networkId) {
            throw new RuntimeException('Field value execution context is not bound to the active WordPress network.');
        }
    }
}
