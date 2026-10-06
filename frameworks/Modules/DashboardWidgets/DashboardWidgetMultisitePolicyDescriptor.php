<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class DashboardWidgetMultisitePolicyDescriptor
{
    public const MAX_EXCLUDED_SITES = 100;

    /**
     * @param list<string> $widgetDefinitionIds
     * @param list<int> $excludeSiteIds
     */
    public function __construct(
        public string $definitionId,
        public int $revision,
        public int $blueprintSiteId,
        public string $networkPresetId,
        public int $networkPresetRevision,
        public array $widgetDefinitionIds,
        public array $excludeSiteIds,
        public bool $subsiteOverride,
        public string $settingsCapability,
    ) {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->definitionId) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget Multisite policy definition id must be a lowercase RFC 4122 UUID.');
        }
        if ($this->revision < 1 || $this->networkPresetRevision < 1) {
            throw new InvalidArgumentException('Dashboard Widget Multisite policy revisions must be positive.');
        }
        if ($this->blueprintSiteId < 1) {
            throw new InvalidArgumentException('Dashboard Widget Multisite blueprint site id must be positive.');
        }
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->networkPresetId) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget Multisite network preset id must be a lowercase RFC 4122 UUID.');
        }
        if (!array_is_list($this->widgetDefinitionIds) || $this->widgetDefinitionIds === []) {
            throw new InvalidArgumentException('Dashboard Widget Multisite network preset must contain ordered widget references.');
        }
        foreach ($this->widgetDefinitionIds as $id) {
            if (!is_string($id) || preg_match('/^[0-9a-f-]{36}$/', $id) !== 1) {
                throw new InvalidArgumentException('Dashboard Widget Multisite preset widget references are invalid.');
            }
        }
        if (!array_is_list($this->excludeSiteIds) || count($this->excludeSiteIds) > self::MAX_EXCLUDED_SITES) {
            throw new InvalidArgumentException('Dashboard Widget Multisite exclusions must be a bounded normalized list.');
        }
        $previous = 0;
        foreach ($this->excludeSiteIds as $siteId) {
            if (!is_int($siteId) || $siteId < 1 || $siteId <= $previous) {
                throw new InvalidArgumentException('Dashboard Widget Multisite exclusions must be unique positive integers sorted ascending.');
            }
            if ($siteId === $this->blueprintSiteId) {
                throw new InvalidArgumentException('Dashboard Widget Multisite blueprint site cannot exclude itself.');
            }
            $previous = $siteId;
        }
        if (preg_match('/^[a-z][a-z0-9_-]{0,63}$/', $this->settingsCapability) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget Multisite settings capability reference is invalid.');
        }
    }

    public function excludesSite(int $siteId): bool
    {
        return in_array($siteId, $this->excludeSiteIds, true);
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'definition_id' => $this->definitionId,
            'revision' => $this->revision,
            'blueprint_site_id' => $this->blueprintSiteId,
            'network_preset_id' => $this->networkPresetId,
            'network_preset_revision' => $this->networkPresetRevision,
            'widget_definition_ids' => $this->widgetDefinitionIds,
            'exclude_site_ids' => $this->excludeSiteIds,
            'subsite_override' => $this->subsiteOverride,
            'settings_capability' => $this->settingsCapability,
        ];
    }
}
