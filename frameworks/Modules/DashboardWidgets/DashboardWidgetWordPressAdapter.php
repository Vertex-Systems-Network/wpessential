<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;

final class DashboardWidgetWordPressAdapter
{
    public const WORDPRESS_ID_PREFIX = 'wpe_dashboard_widget_';

    /** @var list<string> */
    private const SITE_CORE_WIDGET_IDS = [
        'dashboard_browser_nag',
        'dashboard_php_nag',
        'dashboard_site_health',
        'dashboard_right_now',
        'dashboard_activity',
        'dashboard_quick_press',
        'dashboard_primary',
    ];

    /** @var list<string> */
    private const NETWORK_CORE_WIDGET_IDS = [
        'dashboard_browser_nag',
        'dashboard_php_nag',
        'network_dashboard_right_now',
        'dashboard_primary',
    ];

    private bool $hooksRegistered = false;
    private bool $siteRegistered = false;
    private bool $networkRegistered = false;

    public function __construct(
        private readonly DefinitionRepositoryInterface $definitions,
        private readonly DashboardWidgetRegistrationCompiler $registrationCompiler,
        private readonly DashboardWidgetRuntimeRenderExecutor $runtimeRenderExecutor,
        private readonly DashboardWidgetWordPressEnvironmentInterface $environment,
    ) {}

    public function registerHooks(): void
    {
        if ($this->hooksRegistered) {
            return;
        }
        $this->hooksRegistered = true;

        try {
            $this->environment->registerAction('wp_dashboard_setup', [$this, 'registerSiteDashboard']);
        } catch (Throwable) {
            // Fail closed without leaking WordPress hook-registration details.
        }

        try {
            $this->environment->registerAction('wp_network_dashboard_setup', [$this, 'registerNetworkDashboard']);
        } catch (Throwable) {
            // Fail closed independently so one unavailable hook cannot break the other.
        }

        try {
            $this->environment->registerFilter(
                'default_hidden_meta_boxes',
                [$this, 'filterDefaultHiddenMetaBoxes'],
                2,
            );
        } catch (Throwable) {
            // Default-hidden projection is optional and must not break registration.
        }
    }

    public function registerSiteDashboard(): void
    {
        if ($this->siteRegistered) {
            return;
        }
        $this->siteRegistered = true;
        $this->registerTarget(false);
    }

    public function registerNetworkDashboard(): void
    {
        if ($this->networkRegistered) {
            return;
        }
        $this->networkRegistered = true;
        $this->registerTarget(true);
    }

    /**
     * @return list<array{id:string,context:string,priority:string}>
     */
    public function discoverRegisteredDashboardWidgets(bool $networkDashboard = false): array
    {
        $screenId = $networkDashboard ? 'dashboard-network' : 'dashboard';

        try {
            $inventory = $this->environment->discoverRegisteredDashboardWidgets($screenId);
        } catch (Throwable) {
            return [];
        }

        $contextOrder = array_flip(['normal', 'side', 'column3', 'column4']);
        $priorityOrder = array_flip(['high', 'sorted', 'core', 'default', 'low']);
        /** @var list<array{id:string,context:string,priority:string}> $safe */
        $safe = [];
        /** @var array<string,true> $seen */
        $seen = [];

        foreach ($inventory as $entry) {
            if (!is_array($entry) || array_keys($entry) !== ['id', 'context', 'priority']) {
                return [];
            }

            $id = $entry['id'];
            $context = $entry['context'];
            $priority = $entry['priority'];
            if (
                !is_string($id)
                || preg_match('/^[A-Za-z0-9._:-]+$/D', $id) !== 1
                || !is_string($context)
                || !array_key_exists($context, $contextOrder)
                || !is_string($priority)
                || !array_key_exists($priority, $priorityOrder)
            ) {
                return [];
            }

            $dedupKey = $context . "\0" . $priority . "\0" . $id;
            if (isset($seen[$dedupKey])) {
                continue;
            }
            $seen[$dedupKey] = true;
            $safe[] = [
                'id' => $id,
                'context' => $context,
                'priority' => $priority,
            ];
        }

        usort(
            $safe,
            static fn (array $left, array $right): int =>
                ($contextOrder[$left['context']] <=> $contextOrder[$right['context']])
                ?: ($priorityOrder[$left['priority']] <=> $priorityOrder[$right['priority']])
                ?: ($left['id'] <=> $right['id']),
        );

        return $safe;
    }

    /**
     * Returns registered rows whose exact widget IDs match WordPress core dashboard IDs.
     *
     * This is canonical-ID classification only; it does not attest callback/plugin provenance.
     *
     * @return list<array{id:string,context:string,priority:string}>
     */
    public function discoverCoreDashboardWidgets(bool $networkDashboard = false): array
    {
        $coreIds = $networkDashboard
            ? self::NETWORK_CORE_WIDGET_IDS
            : self::SITE_CORE_WIDGET_IDS;

        return array_values(array_filter(
            $this->discoverRegisteredDashboardWidgets($networkDashboard),
            static fn (array $entry): bool => in_array($entry['id'], $coreIds, true),
        ));
    }

    /**
     * Returns registered rows whose exact widget IDs are not canonical WordPress core IDs.
     *
     * This is a bounded non-core classification backing the third-party inventory option;
     * it does not attest callback, plugin, package, or vendor provenance.
     *
     * @return list<array{id:string,context:string,priority:string}>
     */
    public function discoverNonCoreDashboardWidgets(bool $networkDashboard = false): array
    {
        $coreIds = $networkDashboard
            ? self::NETWORK_CORE_WIDGET_IDS
            : self::SITE_CORE_WIDGET_IDS;

        return array_values(array_filter(
            $this->discoverRegisteredDashboardWidgets($networkDashboard),
            static fn (array $entry): bool => !in_array($entry['id'], $coreIds, true),
        ));
    }

    /**
     * Returns the current user's explicitly saved hidden Dashboard Widget IDs.
     *
     * Default-hidden and effective hidden state are intentionally excluded.
     *
     * @return list<string>
     */
    public function currentUserHiddenDashboardWidgetIds(bool $networkDashboard = false): array
    {
        $screenId = $networkDashboard ? 'dashboard-network' : 'dashboard';

        try {
            $ids = $this->environment->currentUserHiddenDashboardWidgetIds($screenId);
        } catch (Throwable) {
            return [];
        }

        $safe = [];
        foreach ($ids as $id) {
            if (!is_string($id) || preg_match('/^[A-Za-z0-9._:-]+$/D', $id) !== 1) {
                return [];
            }
            $safe[$id] = true;
        }

        $result = array_keys($safe);
        sort($result, SORT_STRING);
        return $result;
    }

    private function registerTarget(bool $networkDashboard): void
    {
        foreach ($this->planTarget($networkDashboard) as $entry) {
            $descriptor = $entry['descriptor'];
            $definitionId = $descriptor->definitionId;
            $callback = function () use ($definitionId): void {
                $this->renderDefinition($definitionId);
            };

            try {
                $this->environment->registerDashboardWidget(
                    $entry['id'],
                    $descriptor->title,
                    $callback,
                    $descriptor->context,
                    $descriptor->priority,
                );
            } catch (Throwable) {
                // Registration failure for one safe planned entry is isolated.
                continue;
            }

            if (!$descriptor->defaultCollapsed) {
                continue;
            }

            $screenId = $networkDashboard ? 'dashboard-network' : 'dashboard';
            $hook = 'postbox_classes_' . $screenId . '_' . $entry['id'];
            try {
                $this->environment->registerFilter(
                    $hook,
                    function (array $classes) use ($screenId): array {
                        try {
                            if ($this->environment->hasClosedPostboxPreference($screenId)) {
                                return $classes;
                            }
                        } catch (Throwable) {
                            return $classes;
                        }

                        if (!in_array('closed', $classes, true)) {
                            $classes[] = 'closed';
                        }

                        return $classes;
                    },
                );
            } catch (Throwable) {
                // Default-collapsed projection is optional and must not break registration.
            }
        }
    }

    /**
     * @param array<int,mixed> $hidden
     * @return array<int,mixed>
     */
    public function filterDefaultHiddenMetaBoxes(array $hidden, mixed $screen): array
    {
        try {
            $screenId = $this->environment->screenId($screen);
        } catch (Throwable) {
            return $hidden;
        }

        $networkDashboard = match ($screenId) {
            'dashboard' => false,
            'dashboard-network' => true,
            default => null,
        };
        if ($networkDashboard === null) {
            return $hidden;
        }

        foreach ($this->planTarget($networkDashboard) as $entry) {
            if (!$entry['descriptor']->defaultHidden) {
                continue;
            }
            if (!in_array($entry['id'], $hidden, true)) {
                $hidden[] = $entry['id'];
            }
        }

        return $hidden;
    }

    /**
     * @return list<array{id:string,descriptor:DashboardWidgetRegistrationDescriptor}>
     */
    private function planTarget(bool $networkDashboard): array
    {
        $currentSiteId = null;
        if (!$networkDashboard) {
            try {
                $currentSiteId = $this->environment->currentSiteId();
            } catch (Throwable) {
                return [];
            }
            if ($currentSiteId < 1) {
                return [];
            }
        }

        try {
            $definitions = $this->definitions->byType(DashboardWidgetDefinition::TYPE);
            usort(
                $definitions,
                static fn (Definition $left, Definition $right): int =>
                    ($left->slug <=> $right->slug) ?: ($left->id <=> $right->id),
            );

            /** @var list<array{id:string,descriptor:DashboardWidgetRegistrationDescriptor}> $plan */
            $plan = [];
            foreach ($definitions as $definition) {
                try {
                    $descriptor = $this->registrationCompiler->compile($definition);
                } catch (InvalidArgumentException) {
                    continue;
                }

                if ($descriptor->networkDashboard !== $networkDashboard) {
                    continue;
                }
                if (!$networkDashboard && ($currentSiteId === null || !$descriptor->isEligibleForSite($currentSiteId))) {
                    continue;
                }

                $plan[] = [
                    'id' => self::WORDPRESS_ID_PREFIX . $descriptor->key,
                    'descriptor' => $descriptor,
                ];
            }
        } catch (Throwable) {
            return [];
        }

        /** @var array<string,int> $counts */
        $counts = [];
        foreach ($plan as $entry) {
            $counts[$entry['id']] = ($counts[$entry['id']] ?? 0) + 1;
        }

        return array_values(array_filter(
            $plan,
            static fn (array $entry): bool => ($counts[$entry['id']] ?? 0) === 1,
        ));
    }

    private function renderDefinition(string $definitionId): void
    {
        try {
            $context = new ExecutionContext(
                principal: new Principal($this->environment->currentUserId(), 'user'),
                siteId: $this->environment->currentSiteId(),
                channel: ExecutionChannel::Ui,
                networkId: $this->environment->currentNetworkId(),
            );
            $result = $this->runtimeRenderExecutor->render($definitionId, $context);

            if (!in_array(
                $result->status,
                [
                    DashboardWidgetRuntimeRenderResult::STATUS_RENDERED,
                    DashboardWidgetRuntimeRenderResult::STATUS_RENDERED_ERROR,
                ],
                true,
            )) {
                return;
            }

            $this->environment->outputTrustedHtml($result->html);
        } catch (Throwable) {
            // Fail closed without leaking environment/runtime/output details into wp-admin.
        }
    }
}
