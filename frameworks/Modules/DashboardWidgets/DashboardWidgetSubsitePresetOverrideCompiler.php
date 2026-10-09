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

final readonly class DashboardWidgetSubsitePresetOverrideCompiler
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private DashboardWidgetPresetCompiler $presetCompiler,
    ) {}

    public function compile(Definition $definition): DashboardWidgetSubsitePresetOverrideDescriptor
    {
        DashboardWidgetSubsitePresetOverrideDefinition::assertOwned($definition);
        if ($definition->schemaVersion !== 1 || array_keys($definition->payload) !== ['subsite_preset_override']) {
            throw new InvalidArgumentException('Subsite preset override expects exactly the V1 payload.');
        }
        $data = $definition->payload['subsite_preset_override'];
        if (!is_array($data) || array_is_list($data)) {
            throw new InvalidArgumentException('Subsite preset override payload must be an object.');
        }
        $keys = ['network_id', 'site_id', 'policy_id', 'preset_id'];
        if (count($data) !== count($keys)) {
            throw new InvalidArgumentException('Subsite preset override must contain exactly the required fields.');
        }
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException('Subsite preset override missing required field.');
            }
        }
        $networkId = $data['network_id'];
        $siteId = $data['site_id'];
        $policyId = $data['policy_id'];
        $presetId = $data['preset_id'];
        if (!is_int($networkId) || $networkId < 1 || !is_int($siteId) || $siteId < 1) {
            throw new InvalidArgumentException('Subsite preset override network and site ids must be positive integers.');
        }
        foreach ([$policyId, $presetId] as $id) {
            if (!is_string($id) || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id) !== 1) {
                throw new InvalidArgumentException('Subsite preset override policy and preset refs must be lowercase RFC4122 UUIDs.');
            }
        }
        $presetDefinition = $this->definitions->get($presetId);
        if (
            !$presetDefinition instanceof Definition
            || $presetDefinition->status !== DefinitionStatus::Published
            || $presetDefinition->ownerSurfaceId !== DashboardWidgetPresetDefinition::OWNER_SURFACE_ID
            || $presetDefinition->type !== DashboardWidgetPresetDefinition::TYPE
        ) {
            throw new InvalidArgumentException('Subsite override must reference a Published Surface-10 Dashboard preset.');
        }
        $preset = $this->presetCompiler->compile($presetDefinition);
        if ($preset->networkDefault || $preset->roles !== []) {
            throw new InvalidArgumentException('Subsite override must reference a reusable unassigned preset.');
        }
        return new DashboardWidgetSubsitePresetOverrideDescriptor(
            definitionId: $definition->id,
            revision: $definition->revision,
            networkId: $networkId,
            siteId: $siteId,
            policyId: $policyId,
            presetId: $preset->definitionId,
            presetRevision: $preset->revision,
            widgetDefinitionIds: $preset->widgetDefinitionIds,
        );
    }
}
