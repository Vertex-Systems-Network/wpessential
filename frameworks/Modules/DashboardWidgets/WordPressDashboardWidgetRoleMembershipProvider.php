<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use LogicException;
use Throwable;
use WPEssential\Platform\Auth\ExecutionContext;

final class WordPressDashboardWidgetRoleMembershipProvider implements DashboardWidgetRoleMembershipProviderInterface
{
    /** @var Closure():?int */
    private Closure $currentUserId;

    /** @var Closure():int */
    private Closure $currentSiteId;

    /** @var Closure():list<string> */
    private Closure $currentUserRoles;

    /**
     * @param null|callable():?int $currentUserId
     * @param null|callable():int $currentSiteId
     * @param null|callable():list<string> $currentUserRoles
     */
    public function __construct(
        ?callable $currentUserId = null,
        ?callable $currentSiteId = null,
        ?callable $currentUserRoles = null,
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

        $this->currentUserRoles = $currentUserRoles !== null
            ? Closure::fromCallable($currentUserRoles)
            : static function (): array {
                if (!function_exists('wp_get_current_user')) {
                    throw new LogicException('WordPress current-user role API is unavailable.');
                }

                $user = wp_get_current_user();
                $roles = is_object($user) ? ($user->roles ?? null) : null;
                if (!is_array($roles)) {
                    return [];
                }

                $result = [];
                foreach ($roles as $role) {
                    if (
                        is_string($role)
                        && preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $role) === 1
                        && !in_array($role, $result, true)
                    ) {
                        $result[] = $role;
                    }
                }

                return $result;
            };
    }

    public function isCurrentUserContext(ExecutionContext $context): bool
    {
        if (!$context->principal->isAuthenticated() || $context->principal->actorType !== 'user') {
            return false;
        }

        try {
            $currentUserId = ($this->currentUserId)();
            $currentSiteId = ($this->currentSiteId)();
        } catch (Throwable) {
            return false;
        }

        return $currentUserId !== null
            && $currentUserId > 0
            && $currentSiteId > 0
            && $context->principal->userId === $currentUserId
            && $context->siteId === $currentSiteId;
    }

    public function hasAnyRole(ExecutionContext $context, array $roles): bool
    {
        if (!$this->isCurrentUserContext($context) || $roles === []) {
            return false;
        }

        foreach ($roles as $role) {
            if (!is_string($role) || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $role) !== 1) {
                return false;
            }
        }

        try {
            $currentRoles = ($this->currentUserRoles)();
        } catch (Throwable) {
            return false;
        }

        foreach ($currentRoles as $currentRole) {
            if (!is_string($currentRole)) {
                return false;
            }
        }

        foreach ($roles as $role) {
            if (in_array($role, $currentRoles, true)) {
                return true;
            }
        }

        return false;
    }
}
