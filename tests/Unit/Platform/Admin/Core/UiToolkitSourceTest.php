<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin\Core;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Ui\Button;

final class UiToolkitSourceTest extends TestCase
{
    public function testButtonClassesFollowTheToolkitNaming(): void
    {
        self::assertSame('wpe-ui-button wpe-ui-button--primary', Button::classes('primary'));
        self::assertSame('wpe-ui-button wpe-ui-button--secondary wpe-ui-button--sm', Button::classes('secondary', true));
        self::assertSame('wpe-ui-button wpe-ui-button--ghost', Button::classes('<script>'));
    }

    public function testCompletedModuleScreensRenderInsideTheToolkitShell(): void
    {
        $root = dirname(__DIR__, 5);
        foreach (
            [
                'Taxonomies/TaxonomyAdminController.php',
                'Fields/FieldAdminController.php',
                'Relations/RelationAdminController.php',
                'Query/QueryAdminController.php',
                'AdminColumns/AdminColumnsAdminController.php',
            ] as $controller
        ) {
            $source = file_get_contents($root . '/frameworks/Modules/' . $controller);
            self::assertIsString($source);
            self::assertStringContainsString('class="wrap wpe-ui', $source, $controller);
            self::assertStringContainsString('Shell::topbar( self::PAGE_SLUG )', $source, $controller);
        }
    }

    public function testToolkitStylesheetDefinesEveryComponentClass(): void
    {
        $root = dirname(__DIR__, 5);
        $scss = '';
        foreach (glob($root . '/admin-ui/src/ui/*.scss') ?: [] as $partial) {
            $scss .= (string) file_get_contents($partial);
        }

        foreach (['wpe-ui-button', 'wpe-ui-badge', 'wpe-ui-pill', 'wpe-ui-notice', 'wpe-ui-toggle', 'wpe-ui-panel', 'wpe-ui-stat', 'wpe-ui-tabs', 'wpe-ui-empty', 'wpe-ui-topbar', 'wpe-ui-page-header', 'wpe-ui-breadcrumb'] as $block) {
            self::assertStringContainsString('.' . $block, $scss, $block);
        }
    }
}
