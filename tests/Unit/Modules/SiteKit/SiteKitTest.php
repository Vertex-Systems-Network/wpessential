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
    public function testSnippetMetadataAndRestrictedReplacement(): void
    {
        $css = ['id' => 'css1', 'type' => 'css', 'code' => 'body{color:red}', 'enabled' => true];
        $js = ['id' => 'js1', 'type' => 'js', 'code' => 'console.log(1);', 'enabled' => true];
        $clean = \WPEssential\Modules\SiteKit\SiteKitSnippets::clean($css + [
            'condition' => 'pages', 'page_ids' => '3, 7, 3, bad, -5', 'priority' => 2000,
        ], false);
        self::assertSame([3, 7], $clean['page_ids']);
        self::assertSame(1000, $clean['priority']);
        self::assertTrue(\WPEssential\Modules\SiteKit\SiteKitSnippets::matches($clean, false, 7));
        self::assertFalse(\WPEssential\Modules\SiteKit\SiteKitSnippets::matches($clean, true, 8));
        self::assertFalse(\WPEssential\Modules\SiteKit\SiteKitSnippets::matches(['condition' => 'unknown'], true, 7));
        self::assertNull(\WPEssential\Modules\SiteKit\SiteKitSnippets::clean(['type' => 'php', 'code' => 'return 1;'], true));
        self::assertNull(\WPEssential\Modules\SiteKit\SiteKitSnippets::clean(['type' => 'css', 'code' => ''], false));
        $result = \WPEssential\Modules\SiteKit\SiteKitSnippets::mergeRows([$js], [$css, array_replace($css, ['id' => 'js1'])], false);
        self::assertCount(2, $result);
        self::assertSame($js, $result[1]);
        self::assertSame([$js], \WPEssential\Modules\SiteKit\SiteKitSnippets::mergeRows([$js], [], false));
        self::assertNull(\WPEssential\Modules\SiteKit\SiteKitSnippets::clean($css + ['location' => 'admin'], false));
        self::assertNull(\WPEssential\Modules\SiteKit\SiteKitSnippets::clean($css + ['location' => 'bad'], true));
        self::assertSame('body_open', \WPEssential\Modules\SiteKit\SiteKitSnippets::clean($js + ['location' => 'body_open'], true)['location']);
        self::assertNull(\WPEssential\Modules\SiteKit\SiteKitSnippets::clean(['type' => 'html', 'code' => '<p>Hi</p>'], false));
        self::assertTrue(\WPEssential\Modules\SiteKit\SiteKitSnippets::privileged($css + ['location' => 'login']));
        self::assertSame(['js1', 'css1'], array_column(\WPEssential\Modules\SiteKit\SiteKitSnippets::ordered([$clean, $js]), 'id'));
    }

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
        self::assertStringContainsString(':root{--wp--preset--color--wpe-custom-brand-blue:#3458c9;}', SiteKitCompiler::tokenCss($values));
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
        self::assertStringContainsString('.is-style-wpe-custom-display{', $css);
        self::assertStringContainsString('--wpe-typography-wpe-custom-display-font-size:48px;', SiteKitCompiler::tokenCss($values));
        $values['custom_typography']['tokens'][0]['typography']['size'] = 56;
        self::assertStringContainsString('h1,.wp-block-post-title{font-size:56px;font-weight:600;', SiteKitCompiler::css($values));
    }

    public function testElementorSyncFailsClosedWithoutPremiumOptIn(): void
    {
        self::assertFalse(\WPEssential\Modules\SiteKit\SiteKitElementor::enabled());
        self::assertFalse(\WPEssential\Modules\SiteKit\SiteKitElementor::sync());
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

    public function testElementorTypographyProjectionKeepsStableIdAndSupportedFields(): void
    {
        $raw = ['tokens' => [
            ['name' => 'Display', 'slug' => 'display', 'typography' => [
                'family' => 'google:Inter', 'weight' => '700',
                'size' => ['desktop' => ['size' => 48, 'unit' => 'px'], 'mobile' => ['size' => 28, 'unit' => 'px']],
            ]],
        ]];
        $row = \WPEssential\Modules\SiteKit\SiteKitElementor::customTypography($raw)[0];
        self::assertSame('Inter', $row['typography_font_family']);
        self::assertSame('700', $row['typography_font_weight']);
        self::assertSame(['size' => 28.0, 'unit' => 'px'], $row['typography_font_size_mobile']);
        $raw['tokens'][0]['name'] = 'New name';
        self::assertSame($row['_id'], \WPEssential\Modules\SiteKit\SiteKitElementor::customTypography($raw)[0]['_id']);
    }

    public function testGlobalImageAndLinkStylesAreOptInAndResponsive(): void
    {
        $store = new SiteKitStore();
        $store->update([
            'image_round' => true,
            'image_radius' => ['desktop' => ['size' => 0, 'unit' => 'px'], 'mobile' => ['size' => 8, 'unit' => 'px']],
            'image_border_width' => 2,
            'image_border_color' => '#123456',
            'image_fit' => 'cover',
            'image_caption_color' => '#345678',
            'link_focus' => '#006039',
            'link_active' => '#112233',
            'link_visited' => '#654321',
            'link_decoration_style' => 'dotted',
        ]);
        $css = SiteKitCompiler::css($store->values());
        self::assertStringContainsString(SiteKitCompiler::IMAGE . '{border-radius:0px;}', $css);
        self::assertStringContainsString(SiteKitCompiler::IMAGE . '{border-radius:8px;}', $css);
        self::assertStringContainsString('border-width:2px;border-style:solid;border-color:#123456;object-fit:cover;', $css);
        self::assertStringContainsString(SiteKitCompiler::CAPTION . '{color:#345678;}', $css);
        self::assertStringContainsString(SiteKitCompiler::LINK . ':focus-visible{color:#006039;}', $css);
        self::assertStringContainsString(SiteKitCompiler::LINK . ':active{color:#112233;}', $css);
        self::assertStringContainsString(SiteKitCompiler::LINK . ':visited{color:#654321;}', $css);
        self::assertStringContainsString('text-decoration-style:dotted;', $css);
        $store->update(['image_fit' => 'invalid;display:none', 'link_decoration_style' => 'invalid']);
        self::assertArrayNotHasKey('image_fit', $store->values());
        self::assertArrayNotHasKey('link_decoration_style', $store->values());
        self::assertStringNotContainsString('object-fit:', SiteKitCompiler::css([]));
        self::assertStringNotContainsString('text-decoration-style:', SiteKitCompiler::css([]));
    }

    public function testExpandedFormAndButtonStatesAreOptIn(): void
    {
        $store = new SiteKitStore();
        $store->update([
            'field_label' => '#123456',
            'field_placeholder' => '#555555',
            'field_disabled_bg' => '#eeeeee',
            'field_error' => '#aa0000',
            'field_focus' => '#006039',
            'btn_focus_bg' => '#123456',
            'btn_active_text' => '#ffffff',
            'btn_disabled_border' => '#888888',
            'btn_focus_ring' => '#006039',
            'btn_focus_width' => 3,
        ]);
        $css = SiteKitCompiler::css($store->values());
        self::assertStringContainsString(SiteKitCompiler::LABEL . '{color:#123456;}', $css);
        self::assertStringContainsString('::placeholder{color:#555555;}', $css);
        self::assertStringContainsString(':is(:disabled,[aria-disabled="true"]){background-color:#eeeeee;}', $css);
        self::assertStringContainsString('[aria-invalid="true"]{border-color:#aa0000;}', $css);
        self::assertStringContainsString(':focus-visible{background-color:#123456;}', $css);
        self::assertStringContainsString(':active{color:#ffffff;}', $css);
        self::assertStringContainsString(':is(:disabled,[aria-disabled="true"]){border-color:#888888;}', $css);
        self::assertStringContainsString('outline:3px solid #006039;outline-offset:2px;', $css);
        self::assertStringNotContainsString('::placeholder{', SiteKitCompiler::css([]));
    }

    public function testNamedShadowTokensAreSafeAndProjected(): void
    {
        $store = new SiteKitStore();
        $store->update(['custom_shadows' => ['tokens' => [
            ['name' => 'Card', 'slug' => 'card', 'shadow' => ['x' => 4, 'y' => 8, 'blur' => 16, 'spread' => -2, 'color' => '#123456']],
            ['name' => 'Invalid', 'slug' => 'bad', 'shadow' => ['color' => 'red;display:none']],
        ]]]);
        $values = $store->values();
        self::assertCount(1, $values['custom_shadows']['tokens']);
        $json = SiteKitCompiler::themeJson([], $values, static fn (array $path): array => []);
        self::assertSame('wpe-custom-card', $json['settings']['shadow']['presets'][0]['slug']);
        self::assertSame('4px 8px 16px -2px #123456', $json['settings']['shadow']['presets'][0]['shadow']);
        self::assertStringContainsString('--wp--preset--shadow--wpe-custom-card:4px 8px 16px -2px #123456;', SiteKitCompiler::css($values));
        $store->update(['custom_shadows' => '']);
        self::assertStringNotContainsString('--wp--preset--shadow--wpe-custom-card', SiteKitCompiler::css($store->values()));
    }

    public function testButtonVariantsAndBackgroundAdvancedControls(): void
    {
        $store = new SiteKitStore();
        $store->update([
            'btn_variant_primary_bg' => '#123456',
            'btn_variant_outline_border' => '#234567',
            'btn_scale_sm_height' => ['desktop' => ['size' => 40, 'unit' => 'px'], 'mobile' => ['size' => 32, 'unit' => 'px']],
            'site_bg' => ['color' => '#ffffff', 'origin' => 'content-box', 'clip' => 'padding-box', 'blend' => 'multiply'],
        ]);
        $css = SiteKitCompiler::css($store->values());
        self::assertStringContainsString('--wpe-btn-primary-bg:#123456;', $css);
        self::assertStringContainsString('--wpe-btn-outline-border:#234567;', $css);
        self::assertStringContainsString('--wpe-btn-sm-height:40px;', $css);
        self::assertStringContainsString('@media (max-width: 767px){:root{--wpe-btn-sm-height:32px;}}', $css);
        self::assertStringContainsString('background-origin:content-box;background-clip:padding-box;background-blend-mode:multiply;', $css);
        self::assertSame([], SiteKitCompiler::background(['clip' => 'invalid', 'blend' => 'bad']));
        self::assertSame('', SiteKitCompiler::buttonVariantCss([]));
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
