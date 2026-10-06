<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class DashboardWidgetPresetResolver
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private DashboardWidgetPresetCompiler $compiler,
        private DashboardWidgetRoleMembershipProviderInterface $roles,
    ) {}

    public function resolve(ExecutionContext $context): DashboardWidgetPresetResolution
    {
        if (!$this->roles->isCurrentUserContext($context)) {
            return DashboardWidgetPresetResolution::unavailable();
        }

        $roleMatches = [];
        $networkDefaults = [];

        try {
            foreach ($this->definitions->byType(DashboardWidgetPresetDefinition::TYPE) as $definition) {
                if (!$definition instanceof Definition || $definition->status !== DefinitionStatus::Published) {
                    continue;
                }

                $preset = $this->compiler->compile($definition);
                if ($preset->isRoleDefault() && $this->roles->hasAnyRole($context, $preset->roles)) {
                    $roleMatches[] = $preset;
                }
                if ($preset->networkDefault) {
                    $networkDefaults[] = $preset;
                }
            }
        } catch (Throwable) {
            return DashboardWidgetPresetResolution::invalidCatalog();
        }

        if (count($roleMatches) > 1) {
            return DashboardWidgetPresetResolution::conflict();
        }
        if (count($roleMatches) === 1) {
            return DashboardWidgetPresetResolution::resolved(
                $roleMatches[0],
                DashboardWidgetPresetResolution::SOURCE_ROLE_DEFAULT,
            );
        }

        if (count($networkDefaults) > 1) {
            return DashboardWidgetPresetResolution::conflict();
        }
        if (count($networkDefaults) === 1) {
            return DashboardWidgetPresetResolution::resolved(
                $networkDefaults[0],
                DashboardWidgetPresetResolution::SOURCE_NETWORK_DEFAULT,
            );
        }

        return DashboardWidgetPresetResolution::none();
    }
}
