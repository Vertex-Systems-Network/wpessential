<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class DashboardWidgetRegistrationCompiler
{
    public const SCHEMA_VERSION = 1;

    /** @var list<string> */
    private const PAYLOAD_KEYS = ['widget'];

    /** @var list<string> */
    private const WIDGET_KEYS = ['key', 'title', 'type', 'context', 'priority', 'network_dashboard', 'target', 'visibility', 'render_source'];

    /** @var list<string> */
    private const TARGET_KEYS = ['scope', 'site_ids', 'network_dashboard'];

    private DashboardWidgetVisibilityCompiler $visibilityCompiler;
    private DashboardWidgetContentClassCompiler $contentClassCompiler;

    public function __construct(
        ?DashboardWidgetVisibilityCompiler $visibilityCompiler = null,
        ?DashboardWidgetContentClassCompiler $contentClassCompiler = null,
        private ?DashboardWidgetRenderSourceCompiler $renderSourceCompiler = null,
    ) {
        $this->visibilityCompiler = $visibilityCompiler ?? new DashboardWidgetVisibilityCompiler();
        $this->contentClassCompiler = $contentClassCompiler ?? new DashboardWidgetContentClassCompiler();
    }

    public function compile(Definition $definition): DashboardWidgetRegistrationDescriptor
    {
        DashboardWidgetDefinition::assertOwned($definition);

        if ($definition->schemaVersion !== self::SCHEMA_VERSION) {
            throw new InvalidArgumentException('Dashboard Widget definition schema version is unsupported.');
        }
        if ($definition->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Only Published Dashboard Widget definitions may compile for registration.');
        }

        $payload = $definition->payload;
        if (array_is_list($payload)) {
            throw new InvalidArgumentException('Dashboard Widget definition payload must be an object/map.');
        }
        $this->assertKnownKeys($payload, self::PAYLOAD_KEYS, 'Dashboard Widget definition payload');

        $widget = $payload['widget'] ?? null;
        if (!is_array($widget) || array_is_list($widget)) {
            throw new InvalidArgumentException('Dashboard Widget registration metadata must be an object/map.');
        }
        $this->assertKnownKeys($widget, self::WIDGET_KEYS, 'Dashboard Widget registration metadata');

        $this->contentClassCompiler->compile($definition);
        $this->visibilityCompiler->compile($definition);
        if ($this->renderSourceCompiler === null) {
            throw new InvalidArgumentException('Dashboard Widget trusted render-source compiler is required for registration compilation.');
        }
        $this->renderSourceCompiler->compile($definition);

        $key = $widget['key'] ?? null;
        if (!is_string($key) || !preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $key)) {
            throw new InvalidArgumentException('Dashboard Widget key must be bounded canonical WordPress-safe text.');
        }

        $title = $widget['title'] ?? null;
        if (
            !is_string($title)
            || $title === ''
            || strlen($title) > 160
            || str_contains($title, '<')
            || str_contains($title, '>')
            || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $title)
        ) {
            throw new InvalidArgumentException('Dashboard Widget title must be bounded plain text.');
        }

        $context = $widget['context'] ?? null;
        if (!is_string($context) || !in_array($context, DashboardWidgetRegistrationDescriptor::CONTEXTS, true)) {
            throw new InvalidArgumentException('Dashboard Widget context is unsupported.');
        }

        $priority = $widget['priority'] ?? null;
        if (!is_string($priority) || !in_array($priority, DashboardWidgetRegistrationDescriptor::PRIORITIES, true)) {
            throw new InvalidArgumentException('Dashboard Widget priority is unsupported.');
        }

        [$networkDashboard, $siteScope, $siteIds] = $this->compileTarget($widget);

        return new DashboardWidgetRegistrationDescriptor(
            definitionId: $definition->id,
            revision: $definition->revision,
            key: $key,
            title: $title,
            context: $context,
            priority: $priority,
            networkDashboard: $networkDashboard,
            siteScope: $siteScope,
            siteIds: $siteIds,
        );
    }

    /**
     * @param array<string,mixed> $widget
     * @return array{0:bool,1:?string,2:list<int>}
     */
    private function compileTarget(array $widget): array
    {
        $legacyNetworkPresent = array_key_exists('network_dashboard', $widget);
        $legacyNetworkDashboard = $legacyNetworkPresent ? $widget['network_dashboard'] : false;
        if (!is_bool($legacyNetworkDashboard)) {
            throw new InvalidArgumentException('Dashboard Widget network_dashboard target must be boolean.');
        }

        if (!array_key_exists('target', $widget)) {
            return [$legacyNetworkDashboard, null, []];
        }

        $target = $widget['target'];
        if (!is_array($target) || array_is_list($target)) {
            throw new InvalidArgumentException('Dashboard Widget target must be an object/map.');
        }
        $this->assertKnownKeys($target, self::TARGET_KEYS, 'Dashboard Widget target');

        $targetNetworkPresent = array_key_exists('network_dashboard', $target);
        $targetNetworkDashboard = $targetNetworkPresent ? $target['network_dashboard'] : $legacyNetworkDashboard;
        if (!is_bool($targetNetworkDashboard)) {
            throw new InvalidArgumentException('Dashboard Widget target.network_dashboard must be boolean.');
        }
        if ($legacyNetworkPresent && $targetNetworkPresent && $legacyNetworkDashboard !== $targetNetworkDashboard) {
            throw new InvalidArgumentException('Dashboard Widget network target declarations conflict.');
        }

        $scopePresent = array_key_exists('scope', $target);
        $scope = $scopePresent ? $target['scope'] : null;
        if ($scopePresent && (!is_string($scope) || !in_array($scope, DashboardWidgetRegistrationDescriptor::SITE_SCOPES, true))) {
            throw new InvalidArgumentException('Dashboard Widget target.scope is unsupported.');
        }

        $siteIdsPresent = array_key_exists('site_ids', $target);
        $authoredSiteIds = $siteIdsPresent ? $target['site_ids'] : [];
        if ($siteIdsPresent && (!is_array($authoredSiteIds) || !array_is_list($authoredSiteIds))) {
            throw new InvalidArgumentException('Dashboard Widget target.site_ids must be a list.');
        }
        if ($siteIdsPresent && count($authoredSiteIds) > DashboardWidgetRegistrationDescriptor::MAX_SITE_IDS) {
            throw new InvalidArgumentException('Dashboard Widget target.site_ids exceeds the bounded maximum.');
        }

        /** @var array<int,true> $siteIdSet */
        $siteIdSet = [];
        if ($siteIdsPresent) {
            foreach ($authoredSiteIds as $siteId) {
                if (!is_int($siteId) || $siteId < 1) {
                    throw new InvalidArgumentException('Dashboard Widget target.site_ids must contain positive integers only.');
                }
                $siteIdSet[$siteId] = true;
            }
        }

        /** @var list<int> $siteIds */
        $siteIds = array_keys($siteIdSet);
        sort($siteIds, SORT_NUMERIC);

        if ($siteIdsPresent && $scope !== DashboardWidgetRegistrationDescriptor::SITE_SCOPE_SITE_IDS) {
            throw new InvalidArgumentException('Dashboard Widget target.site_ids requires scope=site_ids.');
        }
        if ($scope === DashboardWidgetRegistrationDescriptor::SITE_SCOPE_SITE_IDS && $siteIds === []) {
            throw new InvalidArgumentException('Dashboard Widget target.scope=site_ids requires a non-empty site_ids list.');
        }
        if ($scope === DashboardWidgetRegistrationDescriptor::SITE_SCOPE_ALL_SITES && $siteIdsPresent) {
            throw new InvalidArgumentException('Dashboard Widget target.scope=all_sites forbids site_ids.');
        }
        if ($targetNetworkDashboard && ($scopePresent || $siteIds !== [])) {
            throw new InvalidArgumentException('Dashboard Widget network target cannot include site targeting intent.');
        }

        return [$targetNetworkDashboard, $scope, $siteIds];
    }

    /**
     * @param array<string,mixed> $value
     * @param list<string> $allowed
     */
    private function assertKnownKeys(array $value, array $allowed, string $label): void
    {
        foreach (array_keys($value) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException($label . ' contains an unsupported key.');
            }
        }
    }
}
