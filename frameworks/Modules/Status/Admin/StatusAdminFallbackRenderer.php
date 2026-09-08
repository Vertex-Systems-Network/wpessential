<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Admin;

if (!defined('ABSPATH')) {
    exit;
}

final class StatusAdminFallbackRenderer
{
    /** @var list<string> */
    private const RESERVED_CORE_LIFECYCLE = [
        'future',
        'trash',
        'auto-draft',
        'inherit',
        'request-pending',
        'request-confirmed',
        'request-failed',
        'request-completed',
    ];

    /**
     * Server-safe, JS-independent authoring shell. Mutations remain Ability-owned;
     * this renderer only provides semantic form controls and truthful guidance.
     *
     * @param list<string> $errors
     */
    public function render(array $errors = []): string
    {
        $errorHtml = '';
        if ($errors !== []) {
            $items = '';
            foreach ($errors as $error) {
                if (is_string($error) && $error !== '') {
                    $items .= '<li>' . $this->escape($error) . '</li>';
                }
            }
            if ($items !== '') {
                $errorHtml = '<div role="alert" tabindex="-1" aria-labelledby="wpe-status-errors-title">'
                    . '<h2 id="wpe-status-errors-title">Status authoring errors</h2><ul>' . $items . '</ul></div>';
            }
        }

        $reserved = implode(', ', self::RESERVED_CORE_LIFECYCLE);

        return '<main id="wpe-status-admin" aria-labelledby="wpe-status-title">'
            . '<h1 id="wpe-status-title">Status Manager</h1>'
            . $errorHtml
            . '<form method="post" aria-describedby="wpe-status-guidance">'
            . '<fieldset><legend>Status definition</legend>'
            . '<label for="wpe-status-key">Status key</label>'
            . '<input id="wpe-status-key" name="status_key" type="text" maxlength="20" required autocomplete="off">'
            . '<label for="wpe-status-label">Display label</label>'
            . '<input id="wpe-status-label" name="status_label" type="text" maxlength="160" required>'
            . '<label for="wpe-status-post-types">Post types</label>'
            . '<input id="wpe-status-post-types" name="post_types" type="text" aria-describedby="wpe-status-post-types-help">'
            . '<p id="wpe-status-post-types-help">Enter only explicitly supported post-type keys.</p>'
            . '</fieldset>'
            . '<fieldset><legend>Transition policy</legend>'
            . '<label for="wpe-status-from">From status</label><input id="wpe-status-from" name="from_status" maxlength="20">'
            . '<label for="wpe-status-to">To status</label><input id="wpe-status-to" name="to_status" maxlength="20">'
            . '<p id="wpe-status-guidance">Core lifecycle statuses are not authorable as generic transition edges: '
            . $this->escape($reserved) . '.</p>'
            . '</fieldset>'
            . '<button type="submit">Validate and save</button>'
            . '</form></main>';
    }

    /** @return list<string> */
    public function reservedCoreLifecycleStatuses(): array
    {
        return self::RESERVED_CORE_LIFECYCLE;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
