<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

interface DashboardWidgetWordPressEnvironmentInterface
{
    public function registerAction(string $hook, callable $callback): void;

    public function registerDashboardWidget(
        string $id,
        string $title,
        callable $callback,
        string $context,
        string $priority,
    ): void;

    public function currentUserId(): ?int;

    public function currentSiteId(): int;

    public function currentNetworkId(): ?int;

    public function outputTrustedHtml(string $html): void;
}
