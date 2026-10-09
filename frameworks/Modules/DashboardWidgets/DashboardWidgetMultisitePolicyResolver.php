<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use Throwable;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class DashboardWidgetMultisitePolicyResolver
{
    /** @var Closure():bool */
    private Closure $isMultisite;
    /** @var Closure():?int */
    private Closure $currentNetworkId;

    /** @param null|callable():bool $isMultisite @param null|callable():?int $currentNetworkId */
    public function __construct(
        private readonly DefinitionRepositoryInterface $definitions,
        private readonly DashboardWidgetMultisitePolicyCompiler $compiler,
        private readonly DashboardWidgetRoleMembershipProviderInterface $currentUser,
        private readonly CapabilityCheckerInterface $capabilities,
        ?callable $isMultisite = null,
        ?callable $currentNetworkId = null,
    ) {
        $this->isMultisite = Closure::fromCallable($isMultisite ?? static fn (): bool =>
            function_exists('is_multisite') && is_multisite());
        $this->currentNetworkId = Closure::fromCallable($currentNetworkId ?? static fn (): ?int =>
            function_exists('get_current_network_id') ? (int) get_current_network_id() : null);
    }

    public function resolve(ExecutionContext $context): DashboardWidgetMultisitePolicyResolution
    {
        try {
            if (
                $context->networkId === null
                || !$context->principal->isAuthenticated()
                || $context->principal->actorType !== 'user'
                || !$this->currentUser->isCurrentUserContext($context)
                || !($this->isMultisite)()
                || ($this->currentNetworkId)() !== $context->networkId
            ) {
                return DashboardWidgetMultisitePolicyResolution::unavailable();
            }
        } catch (Throwable) {
            return DashboardWidgetMultisitePolicyResolution::unavailable();
        }

        $policies = [];
        try {
            foreach ($this->definitions->byType(DashboardWidgetMultisitePolicyDefinition::TYPE) as $definition) {
                if (!$definition instanceof Definition || $definition->status !== DefinitionStatus::Published) {
                    continue;
                }
                $policies[] = $this->compiler->compile($definition);
            }
        } catch (Throwable) {
            return DashboardWidgetMultisitePolicyResolution::invalidCatalog();
        }
        if (count($policies) > 1) {
            return DashboardWidgetMultisitePolicyResolution::conflict();
        }
        if ($policies === []) {
            return DashboardWidgetMultisitePolicyResolution::none();
        }
        $policy = $policies[0];
        if ($policy->excludesSite($context->siteId)) {
            return DashboardWidgetMultisitePolicyResolution::excluded();
        }
        try {
            $canManage = $policy->subsiteOverride && $this->capabilities->can($context, $policy->settingsCapability);
        } catch (Throwable) {
            $canManage = false;
        }
        return DashboardWidgetMultisitePolicyResolution::resolved($policy, $canManage);
    }
}
