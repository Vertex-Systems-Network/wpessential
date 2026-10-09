<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class DashboardWidgetMultisitePolicyResolution
{
    public const RESOLVED = 'resolved';
    public const NONE = 'none';
    public const CONFLICT = 'conflict';
    public const INVALID_CATALOG = 'invalid_catalog';
    public const UNAVAILABLE = 'unavailable';
    public const EXCLUDED = 'excluded';

    private function __construct(
        public string $status,
        public ?DashboardWidgetMultisitePolicyDescriptor $policy = null,
        public bool $canManageOverride = false,
    ) {
        if (!in_array($status, [
            self::RESOLVED, self::NONE, self::CONFLICT, self::INVALID_CATALOG, self::UNAVAILABLE, self::EXCLUDED,
        ], true)) {
            throw new InvalidArgumentException('Invalid Dashboard Multisite policy resolution status.');
        }
        if (($status === self::RESOLVED) !== ($policy !== null)) {
            throw new InvalidArgumentException('Only a resolved Dashboard Multisite policy may reveal policy details.');
        }
        if ($status !== self::RESOLVED && $canManageOverride) {
            throw new InvalidArgumentException('Unresolved Dashboard Multisite policy cannot allow override.');
        }
    }

    public static function resolved(DashboardWidgetMultisitePolicyDescriptor $policy, bool $canManageOverride): self
    {
        return new self(self::RESOLVED, $policy, $canManageOverride);
    }
    public static function none(): self { return new self(self::NONE); }
    public static function conflict(): self { return new self(self::CONFLICT); }
    public static function invalidCatalog(): self { return new self(self::INVALID_CATALOG); }
    public static function unavailable(): self { return new self(self::UNAVAILABLE); }
    public static function excluded(): self { return new self(self::EXCLUDED); }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        $data = [
            'status' => $this->status,
            'policy_id' => null,
            'policy_revision' => null,
            'blueprint_site_id' => null,
            'network_preset_id' => null,
            'network_preset_revision' => null,
            'widget_definition_ids' => [],
            'subsite_override' => false,
            'settings_capability' => null,
            'can_manage_override' => false,
        ];
        if ($this->policy !== null) {
            $data['policy_id'] = $this->policy->definitionId;
            $data['policy_revision'] = $this->policy->revision;
            $data['blueprint_site_id'] = $this->policy->blueprintSiteId;
            $data['network_preset_id'] = $this->policy->networkPresetId;
            $data['network_preset_revision'] = $this->policy->networkPresetRevision;
            $data['widget_definition_ids'] = $this->policy->widgetDefinitionIds;
            $data['subsite_override'] = $this->policy->subsiteOverride;
            $data['settings_capability'] = $this->policy->settingsCapability;
            $data['can_manage_override'] = $this->canManageOverride;
        }
        return $data;
    }
}
