<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentBlueprintCatalog;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetTrustedComponentRenderer;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Rendering\RenderFailureCode;
use WPEssential\Platform\Rendering\RenderInput;
use WPEssential\Platform\Rendering\RenderOutput;

final class DashboardWidgetTrustedComponentRendererTest extends TestCase
{
    public function testRendersAllSevenCanonicalComponentsWithNoAssets(): void
    {
        $cases = [
            'rich_text' => ['content' => 'Hello <b>world</b>'],
            'kpi' => ['label' => 'Orders', 'value' => '42'],
            'chart' => ['labels' => ['A', 'B'], 'values' => [2, 5]],
            'quick_links' => ['labels' => ['Docs', 'Home'], 'urls' => ['https://example.com/docs', '/admin.php?page=wpe']],
            'announcement' => ['title' => 'Notice', 'text' => 'Scheduled maintenance'],
            'support_onboarding' => ['title' => 'Need help?', 'text' => 'Read the guide'],
            'icon_link' => ['icon' => '<gear>', 'label' => 'Settings', 'url' => '/admin.php?page=settings'],
        ];

        foreach ($cases as $contentType => $bindings) {
            $output = $this->render($contentType, $bindings);
            self::assertTrue($output->success, $contentType);
            self::assertSame([], $output->assetHandles);
            self::assertNull($output->failure);
            self::assertNotSame('', $output->html);
        }
    }

    public function testEscapesAuthoredMarkupInsteadOfTrustingHtml(): void
    {
        $output = $this->render('rich_text', ['content' => '<b>Hello & goodbye</b>']);

        self::assertTrue($output->success);
        self::assertStringNotContainsString('<b>Hello', $output->html);
        self::assertStringContainsString('&lt;b&gt;Hello &amp; goodbye&lt;/b&gt;', $output->html);
    }

    public function testChartRequiresBoundedMatchingIntegerSeries(): void
    {
        foreach ([
            ['labels' => [], 'values' => []],
            ['labels' => ['A'], 'values' => [1, 2]],
            ['labels' => ['A'], 'values' => ['1']],
            ['labels' => array_fill(0, 51, 'A'), 'values' => array_fill(0, 51, 1)],
        ] as $bindings) {
            $this->assertInvalid($this->render('chart', $bindings));
        }
    }

    public function testQuickLinksRequireMatchingBoundedLabelsAndSafeUrls(): void
    {
        foreach ([
            ['labels' => ['A'], 'urls' => []],
            ['labels' => [], 'urls' => []],
            ['labels' => array_fill(0, 21, 'A'), 'urls' => array_fill(0, 21, '/safe')],
            ['labels' => ['A'], 'urls' => ['http://example.com']],
            ['labels' => ['A'], 'urls' => ['javascript:alert(1)']],
            ['labels' => ['A'], 'urls' => ['data:text/html,x']],
            ['labels' => ['A'], 'urls' => ['//example.com']],
            ['labels' => ['A'], 'urls' => ['https://user:pass@example.com/']],
        ] as $bindings) {
            $this->assertInvalid($this->render('quick_links', $bindings));
        }

        $safe = $this->render('quick_links', [
            'labels' => ['External', 'Local'],
            'urls' => ['https://example.com/path?x=1&y=2', '/wp-admin/admin.php?page=wpe'],
        ]);
        self::assertTrue($safe->success);
        self::assertStringContainsString('https://example.com/path?x=1&amp;y=2', $safe->html);
    }

    public function testIconLinkRejectsUnsafeUrlAndEscapesIconAndLabel(): void
    {
        $this->assertInvalid($this->render('icon_link', [
            'icon' => 'gear',
            'label' => 'Settings',
            'url' => 'http://example.com',
        ]));

        $safe = $this->render('icon_link', [
            'icon' => '<gear>',
            'label' => '<Settings>',
            'url' => '/settings',
        ]);

        self::assertTrue($safe->success);
        self::assertStringContainsString('&lt;gear&gt;', $safe->html);
        self::assertStringContainsString('&lt;Settings&gt;', $safe->html);
    }

    public function testRejectsUnknownBlueprintRevisionAndBindingShape(): void
    {
        $renderer = new DashboardWidgetTrustedComponentRenderer(new DashboardWidgetComponentBlueprintCatalog());

        $unknown = $renderer->render(
            new RenderInput('22222222-2222-4222-8222-222222222222', 1, ['content' => 'x']),
            $this->context(),
        );
        $this->assertInvalid($unknown);

        $wrongRevision = $renderer->render(
            new RenderInput('31000000-0000-4000-8000-000000000001', 2, ['content' => 'x']),
            $this->context(),
        );
        $this->assertInvalid($wrongRevision);

        $extraBinding = $this->render('rich_text', ['content' => 'x', 'extra' => 'y']);
        $this->assertInvalid($extraBinding);
    }

    /**
     * @param array<string, scalar|list<scalar>|null> $bindings
     */
    private function render(string $contentType, array $bindings): RenderOutput
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $blueprint = $catalog->forContentType($contentType);
        self::assertNotNull($blueprint);

        return (new DashboardWidgetTrustedComponentRenderer($catalog))->render(
            new RenderInput($blueprint->id, $blueprint->revision, $bindings),
            $this->context(),
        );
    }

    private function assertInvalid(RenderOutput $output): void
    {
        self::assertFalse($output->success);
        self::assertSame('', $output->html);
        self::assertSame([], $output->assetHandles);
        self::assertSame(RenderFailureCode::InvalidInput, $output->failure);
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}
