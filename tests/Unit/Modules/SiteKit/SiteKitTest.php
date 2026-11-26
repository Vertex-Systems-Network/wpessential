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


    public function testCustomColourTokensValidateAndPreserveStableSlugs(): void
    {
        $raw = ['tokens' => [
            ['name' => 'Brand blue', 'slug' => 'brand-blue', 'color' => '#3458C9', 'dark' => '#99AAFF'],
            ['name' => 'Duplicate', 'slug' => 'wpe-custom-brand-blue', 'color' => '#fff'],
            ['name' => 'Invalid', 'slug' => 'bad;}body', 'color' => '#fff'],
            ['name' => 'Unsafe colour', 'slug' => 'unsafe', 'color' => 'red;}body{display:none'],
            ['name' => 'Primary', 'slug' => 'primary', 'color' => '#123'],
        ]];
        $clean = SiteKitSanitizer::clean(['type' => 'colour_tokens'], json_encode($raw));
        self::assertSame(['tokens' => [
            ['name' => 'Brand blue', 'slug' => 'wpe-custom-brand-blue', 'color' => '#3458c9', 'dark' => '#99aaff'],
            ['name' => 'Primary', 'slug' => 'wpe-custom-primary', 'color' => '#123', 'dark' => ''],
        ]], $clean);
        self::assertSame($clean, SiteKitSanitizer::clean(['type' => 'colour_tokens'], $clean));
    }

    public function testCustomColoursProjectIntoPaletteAndDarkCss(): void
    {
        $store = new SiteKitStore();
        $store->update(['custom_colours' => ['tokens' => [
            ['name' => 'Brand blue', 'slug' => 'brand-blue', 'color' => '#3458c9', 'dark' => '#99aaff'],
        ]]]);
        $values = $store->values();
        $json = SiteKitCompiler::themeJson([], $values, static fn (array $path): array => $path === ['color', 'palette'] ? [['slug' => 'primary', 'color' => '#000']] : []);
        self::assertSame([
            ['slug' => 'primary', 'color' => '#000'],
            ['slug' => 'wpe-custom-brand-blue', 'name' => 'Brand blue', 'color' => '#3458c9'],
        ], $json['settings']['color']['palette']);
        self::assertStringContainsString('--wp--preset--color--wpe-custom-brand-blue:#99aaff;', SiteKitCompiler::css($values));
        self::assertSame(['custom_colours'], $store->resetSection('colors'));
        self::assertSame([], $store->values());
    }


    public function testCustomTypographyTokensProjectResponsiveSafeValues(): void
    {
        $store = new SiteKitStore();
        $store->update(['custom_typography' => ['tokens' => [
            ['name' => 'Display', 'slug' => 'display', 'typography' => [
                'family' => 'google:Inter',
                'size' => ['desktop' => ['size' => 48, 'unit' => 'px'], 'mobile' => ['size' => 28, 'unit' => 'px']],
                'weight' => '700',
                'style' => 'bad;}body',
            ]],
        ]]]);
        $values = $store->values();
        self::assertArrayNotHasKey('style', $values['custom_typography']['tokens'][0]['typography']);
        $json = SiteKitCompiler::themeJson([], $values, static fn (array $path): array => []);
        self::assertSame('48px', $json['settings']['typography']['fontSizes'][0]['size']);
        self::assertSame('wpe-custom-display', $json['settings']['typography']['fontSizes'][0]['slug']);
        $css = SiteKitCompiler::css($values);
        self::assertStringContainsString('--wpe-typography-wpe-custom-display-font-weight:700;', $css);
        self::assertStringContainsString('--wp--preset--font-size--wpe-custom-display:28px;', $css);
        self::assertStringNotContainsString('bad;}body', $css);
    }

    public function testTypographyReferenceInheritsAndLocalOverrideWins(): void
    {
        $values = [
            'custom_typography' => ['tokens' => [
                ['name' => 'Display', 'slug' => 'display', 'typography' => ['size' => 48, 'weight' => '700']],
            ]],
            'typo_h1' => ['preset' => 'wpe-custom-display', 'weight' => '600'],
        ];
        $css = SiteKitCompiler::css($values);
        self::assertStringContainsString('h1,.wp-block-post-title{font-size:48px;font-weight:600;', $css);
        self::assertStringContainsString(':where(.is-style-wpe-custom-display)', $css);
        self::assertStringContainsString('--wpe-typography-wpe-custom-display-font-size:48px;', SiteKitCompiler::tokenCss($values));
        $values['custom_typography']['tokens'][0]['typography']['size'] = 56;
        self::assertStringContainsString('h1,.wp-block-post-title{font-size:56px;font-weight:600;', SiteKitCompiler::css($values));
    }

    public function testElementorGlobalMergePreservesUserEntriesAndProperties(): void
    {
        $existing = [
            ['_id' => 'user', 'title' => 'User colour', 'color' => '#123456'],
            ['_id' => 'wpe123', 'title' => 'Old', 'color' => '#ffffff', 'extra' => 'keep'],
        ];
        $result = \WPEssential\Modules\SiteKit\SiteKitElementor::mergeGlobals($existing, [
            ['_id' => 'wpe123', 'title' => 'New', 'color' => '#000000'],
        ]);
        self::assertSame($existing[0], $result[0]);
        self::assertSame(['_id' => 'wpe123', 'title' => 'New', 'color' => '#000000', 'extra' => 'keep'], $result[1]);
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
