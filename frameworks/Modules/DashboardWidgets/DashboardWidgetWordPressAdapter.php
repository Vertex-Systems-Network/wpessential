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
