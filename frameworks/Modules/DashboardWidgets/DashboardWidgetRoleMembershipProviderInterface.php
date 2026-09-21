<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Auth\ExecutionContext;

interface DashboardWidgetRoleMembershipProviderInterface
{
    public function isCurrentUserContext(ExecutionContext $context): bool;

    /**
     * @param list<string> $roles
     */
    public function hasAnyRole(ExecutionContext $context, array $roles): bool;
}
