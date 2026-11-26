<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\SiteKit;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\SiteKit\SiteKitCompiler;
use WPEssential\Modules\SiteKit\SiteKitSanitizer;
use WPEssential\Modules\SiteKit\SiteKitSchema;
use WPEssential\Modules\SiteKit\SiteKitStore;

final class SiteKitTest extends TestCase
{
    public function testSchemaCoversEveryPortedGroup(): void
    {
        $groups = array_keys(SiteKitSchema::sections());
        foreach (['colors', 'dark', 'type', 'gtype', 'bg', 'button', 'forms', 'shape', 'layout', 'gradients', 'links', 'shadows', 'header', 'a11y', 'motion', 'seo'] as $group) {
            self::assertContains($group, $groups);
        }
        self::assertArrayHasKey('color_primary', SiteKitSchema::fields());
        self::assertArrayHasKey('dark_primary', SiteKitSchema::fields());
        self::assertSame('hover', SiteKitSchema::field('btn_hover_bg')['tab'] ?? null);
    }

    public function testSanitizerKeepsSafeValuesOnly(): void
    {
        self::assertSame('#7c3aed', SiteKitSanitizer::clean(['type' => 'color'], '#7C3AED'));
        self::assertSame('', SiteKitSanitizer::clean(['type' => 'color'], 'red;}body{display:none'));
        self::assertSame(60, SiteKitSanitizer::clean(['type' => 'range', 'min' => 0, 'max' => 60], '999'));
        self::assertSame('', SiteKitSanitizer::clean(['type' => 'select', 'choices' => ['' => 'Default', 'off' => 'Off']], 'evil'));
        self::assertSame(['desktop' => '24', 'mobile' => '12'], SiteKitSanitizer::clean(['type' => 'range', 'responsive' => true], '{"desktop":"24","mobile":"12"}'));
        self::assertSame('google:Inter', SiteKitSanitizer::clean(['type' => 'font'], 'Inter'));
        self::assertSame('', SiteKitSanitizer::clean(['type' => 'font'], 'Not A Real Font Family'));
        $typography = SiteKitSanitizer::clean(['type' => 'typography'], '{"family":"google:Inter","weight":"700","x":"<script>"}');
        self::assertSame('google:Inter', $typography['family']);
        self::assertSame('script', $typography['x']);
    }

    public function testStoreReportsChangesAndResetsGroups(): void
    {
        $store = new SiteKitStore();
        $changed = $store->update(['color_primary' => '#ff0000', 'unknown_key' => 'x', 'btn_radius' => '8']);
        self::assertSame(['from' => '', 'to' => '#ff0000'], $changed['color_primary']);
        self::assertArrayNotHasKey('unknown_key', $changed);
        self::assertSame(8, $store->values()['btn_radius']);

        self::assertSame([], $store->update(['color_primary' => '#ff0000']));
        self::assertSame(['color_primary'], $store->resetSection('colors'));
        self::assertArrayNotHasKey('color_primary', $store->values());
    }

    public function testCompilerWritesPaletteDarkColoursAndResponsiveCss(): void
    {
        $values = [
            'color_primary' => '#ff0000',
            'dark_surface' => '#000000',
            'btn_padding' => ['desktop' => ['top' => 10, 'right' => 20, 'bottom' => 10, 'left' => 20, 'unit' => 'px']],
            'size_body' => ['desktop' => '18', 'mobile' => '16'],
            'motion_off' => true,
        ];
        $json = SiteKitCompiler::themeJson([], $values, static fn (array $path): array => $path === ['color', 'palette'] ? [['slug' => 'primary', 'color' => '#000']] : []);
        self::assertSame('#ff0000', $json['settings']['color']['palette'][0]['color']);
        self::assertSame('10px', $json['styles']['elements']['button']['spacing']['padding']['top']);

        $css = SiteKitCompiler::css($values);
        self::assertStringContainsString(':root[data-wpe-scheme="dark"]{color-scheme:dark;', $css);
        self::assertStringContainsString('--wp--preset--color--primary:#ff0000;', $css, 'brand colours carry into dark mode');
        self::assertStringContainsString('--wp--preset--color--surface:#000000;', $css);
        self::assertStringContainsString('@media (max-width: 767px){body{font-size:16px;}}', $css);
        self::assertStringContainsString('animation:none!important', $css);
    }
}
