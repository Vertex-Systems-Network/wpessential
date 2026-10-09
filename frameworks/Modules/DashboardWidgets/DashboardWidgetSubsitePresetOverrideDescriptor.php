<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class DashboardWidgetSubsitePresetOverrideDescriptor
{
    /** @param list<string> $widgetDefinitionIds */
    public function __construct(
        public string $definitionId,
        public int $revision,
        public int $networkId,
        public int $siteId,
        public string $policyId,
        public string $presetId,
        public int $presetRevision,
        public array $widgetDefinitionIds,
    ) {
        foreach ([$this->definitionId, $this->policyId, $this->presetId] as $id) {
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id) !== 1) {
                throw new InvalidArgumentException('Subsite preset override references must be canonical lowercase UUIDs.');
            }
        }
        if ($this->revision < 1 || $this->networkId < 1 || $this->siteId < 1 || $this->presetRevision < 1) {
            throw new InvalidArgumentException('Subsite preset override identifiers and revisions must be positive.');
        }
        if (!array_is_list($this->widgetDefinitionIds) || $this->widgetDefinitionIds === []
            || count($this->widgetDefinitionIds) > DashboardWidgetPresetDescriptor::MAX_WIDGETS
            || count(array_unique($this->widgetDefinitionIds)) !== count($this->widgetDefinitionIds)
        ) {
            throw new InvalidArgumentException('Subsite preset override must reference a bounded ordered non-empty widget list.');
        }
        foreach ($this->widgetDefinitionIds as $widgetId) {
            if (!is_string($widgetId) || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $widgetId) !== 1) {
                throw new InvalidArgumentException('Subsite preset override widget reference must be a canonical UUID.');
            }
        }
    }
}
