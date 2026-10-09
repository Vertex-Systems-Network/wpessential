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

final readonly class DashboardWidgetMultisiteEffectivePresetResolver
{
    public function __construct(
        private DashboardWidgetMultisitePolicyResolver $policies,
        private DefinitionRepositoryInterface $definitions,
        private DashboardWidgetSubsitePresetOverrideCompiler $overrides,
    ) {}

    /** @return array<string,mixed> */
    public function resolve(ExecutionContext $context): array
    {
        $policyResolution = $this->policies->resolve($context);
        if ($policyResolution->status !== DashboardWidgetMultisitePolicyResolution::RESOLVED
            || $policyResolution->policy === null
        ) {
            return $this->unresolved($policyResolution->status);
        }

        $policy = $policyResolution->policy;
        $selected = null;
        if ($policy->subsiteOverride && $context->siteId !== $policy->blueprintSiteId) {
            try {
                foreach ($this->definitions->byType(DashboardWidgetSubsitePresetOverrideDefinition::TYPE) as $definition) {
                    if (!$definition instanceof Definition || $definition->status !== DefinitionStatus::Published) {
                        continue;
                    }
                    $candidate = $this->overrides->compile($definition);
                    if (
                        $candidate->networkId !== $context->networkId
                        || $candidate->siteId !== $context->siteId
                        || $candidate->policyId !== $policy->definitionId
                    ) {
                        continue;
                    }
                    if ($selected !== null) {
                        return $this->unresolved(DashboardWidgetMultisitePolicyResolution::CONFLICT);
                    }
                    $selected = $candidate;
                }
            } catch (Throwable) {
                return $this->unresolved(DashboardWidgetMultisitePolicyResolution::INVALID_CATALOG);
            }
        }

        return [
            'status' => 'resolved',
            'source' => $selected !== null ? 'subsite_override' : 'network_inherited',
            'policy_id' => $policy->definitionId,
            'policy_revision' => $policy->revision,
            'network_id' => $context->networkId,
            'site_id' => $context->siteId,
            'preset_id' => $selected?->presetId ?? $policy->networkPresetId,
            'preset_revision' => $selected?->presetRevision ?? $policy->networkPresetRevision,
            'widget_definition_ids' => $selected?->widgetDefinitionIds ?? $policy->widgetDefinitionIds,
            'can_manage_override' => $policyResolution->canManageOverride,
        ];
    }

    /** @return array<string,mixed> */
    private function unresolved(string $status): array
    {
        return [
            'status' => $status,
            'source' => null,
            'policy_id' => null,
            'policy_revision' => null,
            'network_id' => null,
            'site_id' => null,
            'preset_id' => null,
            'preset_revision' => null,
            'widget_definition_ids' => [],
            'can_manage_override' => false,
        ];
    }
}
