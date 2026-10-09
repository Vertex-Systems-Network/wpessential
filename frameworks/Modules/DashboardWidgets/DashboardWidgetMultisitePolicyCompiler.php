<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class DashboardWidgetMultisitePolicyCompiler
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private DashboardWidgetPresetCompiler $presetCompiler,
    ) {}

    public function compile(Definition $definition): DashboardWidgetMultisitePolicyDescriptor
    {
        DashboardWidgetMultisitePolicyDefinition::assertOwned($definition);
        if ($definition->schemaVersion !== 1 || array_keys($definition->payload) !== ['multisite']) {
            throw new InvalidArgumentException('Dashboard Multisite policy requires schema V1 and only a multisite payload.');
        }
        $data = $definition->payload['multisite'];
        if (!is_array($data) || array_is_list($data)) {
            throw new InvalidArgumentException('Dashboard Multisite policy must be an object.');
        }
        $allowed = ['blueprint_site_id', 'network_preset_id', 'exclude_site_ids', 'subsite_override', 'settings_capability'];
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new InvalidArgumentException('Unsupported Dashboard Multisite policy field.');
            }
        }
        foreach (['blueprint_site_id', 'network_preset_id', 'subsite_override', 'settings_capability'] as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException('Dashboard Multisite policy missing required field.');
            }
        }
        $blueprintId = $data['blueprint_site_id'];
        $presetId = $data['network_preset_id'];
        $override = $data['subsite_override'];
        $capability = $data['settings_capability'];
        if (!is_int($blueprintId) || $blueprintId < 1 || !is_bool($override) || !is_string($capability)) {
            throw new InvalidArgumentException('Dashboard Multisite policy has invalid typed fields.');
        }
        if (!is_string($presetId) || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $presetId) !== 1) {
            throw new InvalidArgumentException('Dashboard Multisite policy preset reference must be a lowercase RFC 4122 UUID.');
        }
        $excluded = $data['exclude_site_ids'] ?? [];
        if (!is_array($excluded) || !array_is_list($excluded) || count($excluded) > DashboardWidgetMultisitePolicyDescriptor::MAX_EXCLUDED_SITES) {
            throw new InvalidArgumentException('Dashboard Multisite excluded sites must be a bounded list.');
        }
        $unique = [];
        foreach ($excluded as $siteId) {
            if (!is_int($siteId) || $siteId < 1 || $siteId === $blueprintId || isset($unique[$siteId])) {
                throw new InvalidArgumentException('Dashboard Multisite excluded sites must be unique positive non-blueprint ids.');
            }
            $unique[$siteId] = true;
        }
        sort($excluded, SORT_NUMERIC);

        $presetDefinition = $this->definitions->get($presetId);
        if (
            !$presetDefinition instanceof Definition
            || $presetDefinition->status !== DefinitionStatus::Published
            || $presetDefinition->ownerSurfaceId !== DashboardWidgetPresetDefinition::OWNER_SURFACE_ID
            || $presetDefinition->type !== DashboardWidgetPresetDefinition::TYPE
        ) {
            throw new InvalidArgumentException('Dashboard Multisite policy must reference a Published Surface-10 preset.');
        }
        $preset = $this->presetCompiler->compile($presetDefinition);
        if (!$preset->networkDefault) {
            throw new InvalidArgumentException('Dashboard Multisite policy preset must be network-default.');
        }
        return new DashboardWidgetMultisitePolicyDescriptor(
            definitionId: $definition->id,
            revision: $definition->revision,
            blueprintSiteId: $blueprintId,
            networkPresetId: $presetId,
            networkPresetRevision: $preset->revision,
            widgetDefinitionIds: $preset->widgetDefinitionIds,
            excludeSiteIds: $excluded,
            subsiteOverride: $override,
            settingsCapability: $capability,
        );
    }
}
