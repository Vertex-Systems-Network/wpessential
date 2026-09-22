<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\RendererInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Rendering\RenderFailureCode;
use WPEssential\Platform\Rendering\RenderInput;
use WPEssential\Platform\Rendering\RenderOutput;

final readonly class DashboardWidgetTrustedComponentRenderer implements RendererInterface
{
    public function __construct(
        private DashboardWidgetComponentBlueprintCatalog $catalog = new DashboardWidgetComponentBlueprintCatalog(),
    ) {
    }

    public function render(RenderInput $input, ExecutionContext $context): RenderOutput
    {
        $contentType = $this->catalog->contentTypeForBlueprint($input->blueprintId, $input->blueprintRevision);
        if ($contentType === null) {
            return $this->failure();
        }

        return match ($contentType) {
            'rich_text' => $this->renderRichText($input->bindings),
            'kpi' => $this->renderKpi($input->bindings),
            'chart' => $this->renderChart($input->bindings),
            'quick_links' => $this->renderQuickLinks($input->bindings),
            'announcement' => $this->renderTextPanel($input->bindings, 'announcement'),
            'support_onboarding' => $this->renderTextPanel($input->bindings, 'support-onboarding'),
            'icon_link' => $this->renderIconLink($input->bindings),
            default => $this->failure(),
        };
    }

    /** @param array<string, scalar|list<scalar>|null> $bindings */
    private function renderRichText(array $bindings): RenderOutput
    {
        if (!$this->hasExactKeys($bindings, ['content']) || !is_string($bindings['content'])) {
            return $this->failure();
        }

        return $this->success(
            '<div class="wpe-dashboard-widget wpe-dashboard-widget--rich-text"><p>'
            . $this->escape($bindings['content'])
            . '</p></div>',
        );
    }

    /** @param array<string, scalar|list<scalar>|null> $bindings */
    private function renderKpi(array $bindings): RenderOutput
    {
        if (
            !$this->hasExactKeys($bindings, ['label', 'value'])
            || !is_string($bindings['label'])
            || !is_string($bindings['value'])
        ) {
            return $this->failure();
        }

        return $this->success(
            '<div class="wpe-dashboard-widget wpe-dashboard-widget--kpi">'
            . '<span class="wpe-dashboard-widget__label">' . $this->escape($bindings['label']) . '</span>'
            . '<strong class="wpe-dashboard-widget__value">' . $this->escape($bindings['value']) . '</strong>'
            . '</div>',
        );
    }

    /** @param array<string, scalar|list<scalar>|null> $bindings */
    private function renderChart(array $bindings): RenderOutput
    {
        if (!$this->hasExactKeys($bindings, ['labels', 'values'])) {
            return $this->failure();
        }

        $labels = $bindings['labels'];
        $values = $bindings['values'];
        if (!$this->isStringList($labels) || !$this->isIntList($values)) {
            return $this->failure();
        }

        /** @var list<string> $labels */
        /** @var list<int> $values */
        $count = count($labels);
        if ($count < 1 || $count > 50 || $count !== count($values)) {
            return $this->failure();
        }

        $rows = [];
        foreach ($labels as $index => $label) {
            $rows[] = '<tr><th scope="row">' . $this->escape($label) . '</th><td>'
                . (string) $values[$index]
                . '</td></tr>';
        }

        return $this->success(
            '<div class="wpe-dashboard-widget wpe-dashboard-widget--chart">'
            . '<table><tbody>' . implode('', $rows) . '</tbody></table>'
            . '</div>',
        );
    }

    /** @param array<string, scalar|list<scalar>|null> $bindings */
    private function renderQuickLinks(array $bindings): RenderOutput
    {
        if (!$this->hasExactKeys($bindings, ['labels', 'urls'])) {
            return $this->failure();
        }

        $labels = $bindings['labels'];
        $urls = $bindings['urls'];
        if (!$this->isStringList($labels) || !$this->isStringList($urls)) {
            return $this->failure();
        }

        /** @var list<string> $labels */
        /** @var list<string> $urls */
        $count = count($labels);
        if ($count < 1 || $count > 20 || $count !== count($urls)) {
            return $this->failure();
        }

        $items = [];
        foreach ($labels as $index => $label) {
            $url = $urls[$index];
            if (!$this->isSafeUrl($url)) {
                return $this->failure();
            }
            $items[] = '<li><a href="' . $this->escape($url) . '">'
                . $this->escape($label)
                . '</a></li>';
        }

        return $this->success(
            '<div class="wpe-dashboard-widget wpe-dashboard-widget--quick-links"><ul>'
            . implode('', $items)
            . '</ul></div>',
        );
    }

    /** @param array<string, scalar|list<scalar>|null> $bindings */
    private function renderTextPanel(array $bindings, string $modifier): RenderOutput
    {
        if (
            !$this->hasExactKeys($bindings, ['title', 'text'])
            || !is_string($bindings['title'])
            || !is_string($bindings['text'])
        ) {
            return $this->failure();
        }

        return $this->success(
            '<section class="wpe-dashboard-widget wpe-dashboard-widget--' . $modifier . '">'
            . '<h3>' . $this->escape($bindings['title']) . '</h3>'
            . '<p>' . $this->escape($bindings['text']) . '</p>'
            . '</section>',
        );
    }

    /** @param array<string, scalar|list<scalar>|null> $bindings */
    private function renderIconLink(array $bindings): RenderOutput
    {
        if (
            !$this->hasExactKeys($bindings, ['icon', 'label', 'url'])
            || !is_string($bindings['icon'])
            || !is_string($bindings['label'])
            || !is_string($bindings['url'])
            || !$this->isSafeUrl($bindings['url'])
        ) {
            return $this->failure();
        }

        return $this->success(
            '<div class="wpe-dashboard-widget wpe-dashboard-widget--icon-link"><a href="'
            . $this->escape($bindings['url'])
            . '"><span class="wpe-dashboard-widget__icon" aria-hidden="true">'
            . $this->escape($bindings['icon'])
            . '</span><span class="wpe-dashboard-widget__label">'
            . $this->escape($bindings['label'])
            . '</span></a></div>',
        );
    }

    /**
     * @param array<string, scalar|list<scalar>|null> $bindings
     * @param list<string> $expected
     */
    private function hasExactKeys(array $bindings, array $expected): bool
    {
        $actual = array_keys($bindings);
        sort($actual, SORT_STRING);
        sort($expected, SORT_STRING);
        return $actual === $expected;
    }

    private function isStringList(mixed $value): bool
    {
        if (!is_array($value) || !array_is_list($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!is_string($item)) {
                return false;
            }
        }
        return true;
    }

    private function isIntList(mixed $value): bool
    {
        if (!is_array($value) || !array_is_list($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!is_int($item)) {
                return false;
            }
        }
        return true;
    }

    private function isSafeUrl(string $url): bool
    {
        if (
            $url === ''
            || preg_match('/[\x00-\x20\x7F]/', $url) === 1
            || str_contains($url, '\\')
        ) {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return !str_starts_with($url, '//');
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }

        return isset($parts['scheme'], $parts['host'])
            && strtolower((string) $parts['scheme']) === 'https'
            && (string) $parts['host'] !== ''
            && !isset($parts['user'])
            && !isset($parts['pass']);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function success(string $html): RenderOutput
    {
        return new RenderOutput(true, $html, []);
    }

    private function failure(): RenderOutput
    {
        return new RenderOutput(false, '', [], RenderFailureCode::InvalidInput);
    }
}
