<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use InvalidArgumentException;
use Throwable;

final readonly class DashboardWidgetManualRefreshPresenter
{
    /** @var Closure():string */
    private Closure $nonceFactory;

    /** @param callable():string $nonceFactory */
    public function __construct(
        private string $ajaxAction,
        callable $nonceFactory,
    ) {
        if (preg_match('/^[a-z0-9][a-z0-9_-]{0,127}$/', $this->ajaxAction) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget manual-refresh AJAX action is invalid.');
        }

        $this->nonceFactory = Closure::fromCallable($nonceFactory);
    }

    public function render(
        DashboardWidgetRegistrationDescriptor $descriptor,
        string $ajaxUrl,
        string $initialHtml,
        string $loadingHtml,
        string $screen,
    ): string {
        if (!$descriptor->manualRefresh) {
            throw new InvalidArgumentException('Dashboard Widget manual-refresh presenter requires manualRefresh=true.');
        }
        if (!in_array($screen, ['site', 'network'], true)) {
            throw new InvalidArgumentException('Dashboard Widget manual-refresh screen is unsupported.');
        }

        $ajaxUrl = trim($ajaxUrl);
        if (
            $ajaxUrl === ''
            || strlen($ajaxUrl) > 2048
            || preg_match('/[\x00-\x1F\x7F]/', $ajaxUrl) === 1
        ) {
            throw new InvalidArgumentException('Dashboard Widget manual-refresh AJAX URL is invalid.');
        }

        if ($initialHtml === '' || strlen($initialHtml) > 65536 || $loadingHtml === '' || strlen($loadingHtml) > 16384) {
            throw new InvalidArgumentException('Dashboard Widget manual-refresh trusted HTML exceeds bounded V1 limits.');
        }

        try {
            $nonce = ($this->nonceFactory)();
        } catch (Throwable) {
            throw new InvalidArgumentException('Dashboard Widget manual-refresh nonce could not be created.');
        }
        if (
            !is_string($nonce)
            || $nonce === ''
            || strlen($nonce) > 512
            || preg_match('/[\x00-\x1F\x7F]/', $nonce) === 1
        ) {
            throw new InvalidArgumentException('Dashboard Widget manual-refresh nonce is invalid.');
        }

        return sprintf(
            '<div data-wpessential-dashboard-manual-refresh="1" data-ajax-url="%s" data-ajax-action="%s" data-route-type="%s" data-nonce="%s" data-definition-id="%s" data-definition-revision="%d" data-screen="%s">'
            . '<div data-wpessential-dashboard-refresh-content="1">%s</div>'
            . '<p><button type="button" class="button button-secondary" data-wpessential-dashboard-refresh-button="1">Refresh</button></p>'
            . '<template data-wpessential-dashboard-refresh-loading="1">%s</template>'
            . '<p role="status" aria-live="polite" data-wpessential-dashboard-refresh-status="1"></p>'
            . '</div>',
            self::escape($ajaxUrl),
            self::escape($this->ajaxAction),
            self::escape(DashboardWidgetManualRefreshAjaxHandler::ROUTE_TYPE),
            self::escape($nonce),
            self::escape($descriptor->definitionId),
            $descriptor->revision,
            self::escape($screen),
            $initialHtml,
            $loadingHtml,
        );
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
            'UTF-8',
        );
    }
}
