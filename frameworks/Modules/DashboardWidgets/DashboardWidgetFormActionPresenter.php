<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use InvalidArgumentException;
use Throwable;

final readonly class DashboardWidgetFormActionPresenter
{
    public const ROUTE_TYPE = 'dashboard-widgets.form-action.confirm';

    /** @var Closure():string */
    private Closure $nonceFactory;

    /** @param callable():string $nonceFactory */
    public function __construct(
        private string $ajaxAction,
        callable $nonceFactory,
    ) {
        if (preg_match('/^[a-z0-9][a-z0-9_-]{0,127}$/', $this->ajaxAction) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget form-action AJAX action is invalid.');
        }

        $this->nonceFactory = Closure::fromCallable($nonceFactory);
    }

    public function render(
        DashboardWidgetRegistrationDescriptor $descriptor,
        string $ajaxUrl,
    ): string {
        if (
            $descriptor->actionAbilityId === null
            || $descriptor->actionConfirmation === null
            || $descriptor->actionInput === null
        ) {
            throw new InvalidArgumentException(
                'Dashboard Widget form-action presenter requires canonical action, confirmation and input descriptors.',
            );
        }

        $ajaxUrl = trim($ajaxUrl);
        if (
            $ajaxUrl === ''
            || strlen($ajaxUrl) > 2048
            || preg_match('/[\x00-\x1F\x7F]/', $ajaxUrl) === 1
        ) {
            throw new InvalidArgumentException('Dashboard Widget form-action AJAX URL is invalid.');
        }

        try {
            $nonce = ($this->nonceFactory)();
        } catch (Throwable) {
            throw new InvalidArgumentException('Dashboard Widget form-action nonce could not be created.');
        }

        if (
            !is_string($nonce)
            || $nonce === ''
            || strlen($nonce) > 512
            || preg_match('/[\x00-\x1F\x7F]/', $nonce) === 1
        ) {
            throw new InvalidArgumentException('Dashboard Widget form-action nonce is invalid.');
        }

        $confirmation = $descriptor->actionConfirmation;

        return sprintf(
            '<div data-wpessential-dashboard-form-action="1" data-ajax-url="%s" data-ajax-action="%s" data-route-type="%s" data-nonce="%s" data-definition-id="%s" data-definition-revision="%d">'
            . '<button type="button" class="button button-primary" data-wpessential-form-action-open="1">%s</button>'
            . '<div data-wpessential-form-action-confirmation="1" hidden>'
            . '<p><strong>%s</strong></p>'
            . '<p>%s</p>'
            . '<p>'
            . '<button type="button" class="button button-primary" data-wpessential-form-action-confirm="1">%s</button> '
            . '<button type="button" class="button" data-wpessential-form-action-cancel="1">%s</button>'
            . '</p>'
            . '</div>'
            . '<p role="status" aria-live="polite" data-wpessential-form-action-status="1"></p>'
            . '</div>',
            self::escape($ajaxUrl),
            self::escape($this->ajaxAction),
            self::escape(self::ROUTE_TYPE),
            self::escape($nonce),
            self::escape($descriptor->definitionId),
            $descriptor->revision,
            self::escape($confirmation->confirmLabel),
            self::escape($confirmation->title),
            self::escape($confirmation->message),
            self::escape($confirmation->confirmLabel),
            self::escape($confirmation->cancelLabel),
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
