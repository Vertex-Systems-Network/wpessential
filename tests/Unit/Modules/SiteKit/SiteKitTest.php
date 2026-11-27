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
    public function testCustomizerUsesCanonicalOptionAndSanitizer(): void
    {
        $class = \WPEssential\Modules\SiteKit\SiteKitCustomizer::class;
        self::assertSame(SiteKitStore::OPTION . '[values][color_primary]', $class::settingId('color_primary'));
        $colour = SiteKitSchema::field('color_primary');
        self::assertTrue($class::supports($colour));
        self::assertSame('#123abc', $class::sanitize($colour, '#123ABC'));
        self::assertSame('', $class::sanitize($colour, ''));
        self::assertNull($class::sanitize($colour, 'invalid'));
        self::assertNull($class::sanitize($colour, ['invalid']));
        self::assertTrue($class::supports(SiteKitSchema::field('size_body')));
        self::assertTrue($class::supports(SiteKitSchema::field('custom_colours')));
        self::assertSame(['desktop' => ['size' => 18, 'unit' => 'px']], $class::sanitize(SiteKitSchema::field('size_body'), '{"desktop":{"size":18,"unit":"px"}}'));
        self::assertSame(['size' => 2, 'unit' => 'rem'], $class::sanitize(SiteKitSchema::field('btn_radius'), '{"size":2,"unit":"rem"}'));
        self::assertNull($class::sanitize(SiteKitSchema::field('size_body'), '{broken'));
        self::assertFalse($class::supports(['type' => 'heading']));
        $store = new SiteKitStore();
        $store->update(['color_primary' => '#123abc', 'color_secondary' => '#ffffff']);
        $store->normalizeCustomizerSave();
        self::assertSame(['color_primary' => '#123abc', 'color_secondary' => '#ffffff'], $store->values());
    }

    public function testLegacyScalarAssessmentIsLosslessAndReadOnly(): void
    {
        $legacy = [
            'btn_radius' => '0',
            'field_radius' => '12.5',
            'radius_sm' => '8',
            'radius_md' => '999',
            'hover_duration' => '250',
            'hover_lift' => '{"desktop":4}',
            'font_body' => 'Inter',
            'back_to_top' => 'off',
            'breakpoints' => '768|560',
        ];
        $canonical = ['radius_sm' => 10, 'hover_duration' => 250];
        $review = \WPEssential\Modules\SiteKit\SiteKitLegacyScalarPreview::assess($legacy, $canonical);
        self::assertSame(['btn_radius' => 0, 'field_radius' => 12.5], $review['ready']);
        self::assertSame(['radius_sm'], $review['conflicts']);
        self::assertSame(['hover_duration'], $review['unchanged']);
        self::assertSame(['radius_md', 'hover_lift', 'font_body', 'back_to_top', 'breakpoints'], $review['deferred']);
        self::assertSame('0', $legacy['btn_radius']);
        self::assertSame(10, $canonical['radius_sm']);
    }

    public function testScalarHandoffOnlyAcknowledgesPersistedKeysAndRetainsResetOwnership(): void
    {
        $class = \WPEssential\Modules\SiteKit\SiteKitLegacyScalarPreview::class;
        $legacy = ['btn_radius' => '0', 'field_radius' => '12', 'hover_duration' => '9999', 'font_body' => 'Inter'];
        self::assertSame(['btn_radius', 'field_radius'], $class::handoffKeys($legacy));
        self::assertSame(['btn_radius'], $class::confirmedKeys($legacy, ['btn_radius' => 0], []));
        self::assertSame(['btn_radius', 'field_radius'], $class::confirmedKeys($legacy, ['field_radius' => 8], ['btn_radius']));
        self::assertSame(['btn_radius'], $class::confirmedKeys($legacy, [], ['btn_radius', 'font_body']));
        self::assertSame([], $class::confirmedKeys($legacy, ['hover_duration' => 500, 'font_body' => 'Inter'], []));
    }

    public function testHandoffReadbackRequiresExpectedValueAndKeepsCanonicalConflicts(): void
    {
        $class = \WPEssential\Modules\SiteKit\SiteKitLegacyPreview::class;
        $keys = ['btn_radius', 'field_radius', 'color_primary', 'font_body', 'color_secondary'];
        $expected = ['btn_radius' => 0, 'field_radius' => 8, 'color_primary' => '#123456', 'font_body' => 'theme:hi-wpessential-inter', 'color_secondary' => '#112233'];
        $stored = ['btn_radius' => 0, 'field_radius' => 8, 'color_primary' => '#abcdef', 'font_body' => 'theme:hi-wpessential-heading'];
        self::assertSame(['btn_radius', 'field_radius'], $class::confirmedReadback($keys, $expected, $stored));
        self::assertSame($keys, $class::confirmedReadback($keys, $expected, $expected));
        self::assertSame([], $class::confirmedReadback($keys, $expected, []));
    }

    public function testLegacyThemePreviewDoesNotOverwriteCanonicalValues(): void
    {
        $legacy = ['color_primary' => '#ABCDEF', 'color_secondary' => '#123456', 'dark_primary' => 'bad', 'font_body' => 'Inter', 'breakpoints' => '768|560', 'btn_radius' => '12', 'color_accent' => ''];
        $canonical = ['color_primary' => '#ffffff'];
        $before = $legacy;
        $review = \WPEssential\Modules\SiteKit\SiteKitLegacyPreview::assess($legacy, $canonical);
        self::assertSame(['color_secondary' => '#123456'], $review['ready']);
        self::assertSame(['color_primary'], $review['conflicts']);
        self::assertSame(['dark_primary', 'font_body', 'breakpoints', 'btn_radius'], $review['deferred']);
        self::assertSame($before, $legacy);
        self::assertSame(['color_primary' => '#ffffff'], $canonical);
        self::assertSame(['color_primary', 'color_secondary'], array_values(array_intersect(\WPEssential\Modules\SiteKit\SiteKitLegacyPreview::handoffKeys($legacy), ['color_primary', 'color_secondary', 'dark_primary'])));
        $owned = \WPEssential\Modules\SiteKit\SiteKitLegacyPreview::ownedKeys($legacy, ['color_primary']);
        self::assertContains('color_primary', $owned);
        self::assertContains('color_accent', $owned);
        self::assertNotContains('color_secondary', $owned);
        self::assertNotContains('dark_primary', $owned);
        self::assertNotContains('font_body', $owned);
    }

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

    public function testSnippetDeletionIsExplicitAndPermissionBound(): void
    {
        $css = ['id' => 'css1', 'type' => 'css', 'code' => 'body{color:red}'];
        $js = ['id' => 'js1', 'type' => 'js', 'code' => 'console.log(1);'];
        $existing = [$css, $js];
        self::assertSame($existing, \WPEssential\Modules\SiteKit\SiteKitSnippets::mergeRows($existing, [], true));
        self::assertSame($existing, \WPEssential\Modules\SiteKit\SiteKitSnippets::mergeRows($existing, [
            ['id' => 'css1', 'type' => 'invalid', 'code' => 'bad'],
        ], true));
        self::assertSame([$js], \WPEssential\Modules\SiteKit\SiteKitSnippets::mergeRows($existing, [
            ['id' => 'css1', 'remove' => '1'], ['id' => 'js1', 'remove' => '1'],
        ], false));
        self::assertSame([$css], \WPEssential\Modules\SiteKit\SiteKitSnippets::mergeRows($existing, [
            ['id' => 'js1', 'remove' => '1'],
        ], true));
        self::assertSame([], \WPEssential\Modules\SiteKit\SiteKitSnippets::mergeRows($existing, [
            ['id' => 'css1', 'remove' => '1'], ['id' => 'js1', 'remove' => '1'],
        ], true));
    }

    public function testSnippetConditionAndLocationValidation(): void
    {
        $css = ['type' => 'css', 'code' => 'body{color:red}'];
        self::assertNull(\WPEssential\Modules\SiteKit\SiteKitSnippets::clean($css + ['condition' => 'pages', 'page_ids' => 'bad,0,-3'], true));
        foreach (['admin', 'login'] as $location) {
            self::assertNull(\WPEssential\Modules\SiteKit\SiteKitSnippets::clean($css + ['location' => $location, 'condition' => 'front'], true));
            self::assertNull(\WPEssential\Modules\SiteKit\SiteKitSnippets::clean($css + ['location' => $location, 'condition' => 'pages', 'page_ids' => '7'], true));
            self::assertSame($location, \WPEssential\Modules\SiteKit\SiteKitSnippets::clean($css + ['location' => $location], true)['location']);
        }
        self::assertSame('all', \WPEssential\Modules\SiteKit\SiteKitSnippets::clean($css + ['scope' => ['invalid']], true)['scope']);
    }

    public function testScssLiteCompilesOnlyBoundedFlatRules(): void
    {
        $source = '$accent: #123456; $space: 12px; .card { color: $accent; padding: $space; }';
        $expected = '.card { color: #123456; padding: 12px; }';
        self::assertSame($expected, \WPEssential\Modules\SiteKit\SiteKitSnippets::compileScssLite($source));
        self::assertSame($source, \WPEssential\Modules\SiteKit\SiteKitSnippets::clean(['type' => 'scss', 'code' => $source], false)['code']);
        foreach (['.card { color: $undefined; }', '@import "remote";', '.a { .b { color: red; } }', '$a: red; $a: blue; .a { color: $a; }', '.a { color: red; } trailing'] as $invalid) {
            self::assertNull(\WPEssential\Modules\SiteKit\SiteKitSnippets::compileScssLite($invalid));
            self::assertNull(\WPEssential\Modules\SiteKit\SiteKitSnippets::clean(['type' => 'scss', 'code' => $invalid], false));
        }
        self::assertNull(\WPEssential\Modules\SiteKit\SiteKitSnippets::clean(['type' => 'scss', 'code' => $source, 'location' => 'admin'], false));
    }

    public function testFreeCannotReplaceOrDeleteUnsupportedLanguageRows(): void
    {
        $php = ['id' => 'php1', 'type' => 'php', 'code' => 'return 1;', 'enabled' => true];
        $unknown = ['id' => 'other1', 'type' => 'future', 'code' => 'opaque', 'enabled' => true];
        $existing = [$php, $unknown];
        foreach ([true, false] as $canJs) {
            self::assertFalse(\WPEssential\Modules\SiteKit\SiteKitSnippets::isFreeType('php'));
            self::assertFalse(\WPEssential\Modules\SiteKit\SiteKitSnippets::isFreeType('future'));
            self::assertNull(\WPEssential\Modules\SiteKit\SiteKitSnippets::clean($php, $canJs));
            self::assertSame($existing, \WPEssential\Modules\SiteKit\SiteKitSnippets::mergeRows($existing, [
                ['id' => 'php1', 'remove' => '1'],
                ['id' => 'other1', 'type' => 'css', 'code' => '.test{color:red}'],
            ], $canJs));
        }
        self::assertTrue(\WPEssential\Modules\SiteKit\SiteKitSnippets::isFreeType('scss'));
    }

    public function testHtmlSnippetRequiresWordPressSanitizer(): void
    {
        if (function_exists('wp_kses_post')) {
            self::markTestSkipped('This test covers the isolated non-WordPress fallback.');
        }

        self::assertNull(\WPEssential\Modules\SiteKit\SiteKitSnippets::clean([
            'type' => 'html', 'code' => '<p>Safe text</p>', 'enabled' => true,
        ], true));
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

    public function testExtraGoogleFontsReadOldPrefixesAndPersistBareNames(): void
    {
        $raw = 'google:Inter|Roboto|Inter|theme:body|Unknown Invalid Family';
        $fonts = \WPEssential\Modules\SiteKit\SiteKitFonts::class;
        self::assertSame(['Inter', 'Roboto'], $fonts::extraFamilies($raw));
        self::assertSame('Inter|Roboto', SiteKitSanitizer::clean(['type' => 'fonts'], $raw));
        self::assertSame(['Inter', 'Roboto'], $fonts::used(['fonts_extra' => $raw]));
        $json = SiteKitCompiler::themeJson([], ['fonts_extra' => $raw, 'fonts_delivery' => 'google'], static fn (array $path): array => []);
        self::assertSame(['g-inter', 'g-roboto'], array_column($json['settings']['typography']['fontFamilies'], 'slug'));
    }

    public function testThemeFontRolesResolveOriginalPresetsWithoutLosingFaces(): void
    {
        $faces = [['fontFamily' => 'Heading', 'src' => ['file:./heading.woff2']]];
        $original = [
            ['slug' => 'body', 'name' => 'Body', 'fontFamily' => 'Body, sans-serif'],
            ['slug' => 'heading', 'name' => 'Heading', 'fontFamily' => 'Heading, serif', 'fontFace' => $faces],
            ['slug' => 'mono', 'name' => 'Code', 'fontFamily' => 'monospace'],
        ];
        $preset = static fn (array $path): array => $path === ['typography', 'fontFamilies'] ? $original : [];
        $result = SiteKitCompiler::themeJson([], ['font_body' => 'theme:heading', 'font_heading' => 'theme:body'], $preset);
        $families = array_column($result['settings']['typography']['fontFamilies'], null, 'slug');
        self::assertSame('Heading, serif', $families['body']['fontFamily']);
        self::assertSame($faces, $families['body']['fontFace']);
        self::assertSame('Body, sans-serif', $families['heading']['fontFamily']);
        self::assertSame('monospace', $families['mono']['fontFamily']);
        self::assertSame('var(--wp--preset--font-family--body)', $result['styles']['typography']['fontFamily']);
        $missing = SiteKitCompiler::themeJson([], ['font_body' => 'theme:unknown'], $preset);
        self::assertSame($original, $missing['settings']['typography']['fontFamilies']);
        self::assertArrayNotHasKey('fontFamily', $missing['styles']['typography'] ?? []);
    }

    public function testLegacyBreakpointPreviewPreservesMediaBoundariesAndRejectsOverflow(): void
    {
        $class = \WPEssential\Modules\SiteKit\SiteKitLegacyBreakpointPreview::class;
        $breakpoints = \WPEssential\Modules\SiteKit\SiteKitBreakpoints::class;
        $result = $class::assess(['breakpoints' => '1920|1280|1024|768|560|480|360|320']);
        self::assertSame('ready', $result['status']);
        self::assertSame(['', '@media (min-width: 1920px)', '@media (max-width: 1439px)', '@media (max-width: 1279px)', '@media (max-width: 1023px)', '@media (max-width: 767px)', '@media (max-width: 559px)', '@media (max-width: 479px)', '@media (max-width: 359px)'], array_column($breakpoints::enabled($result['values']), 'media'));
        $default = $class::assess([]);
        self::assertSame(['desktop', 'tablet', 'mobile'], array_keys($breakpoints::enabled($default['values'])));
        self::assertSame(['desktop'], array_keys($breakpoints::enabled($class::assess(['breakpoints' => '-'])['values'])));
        self::assertSame('@media (max-width: 359px)', $breakpoints::media('mobile_tiny', $class::assess(['breakpoints' => '320'])['values']));
        $both = $class::assess(['breakpoints' => '360|320']);
        self::assertSame('ready', $both['status']);
        self::assertSame(['desktop', 'mobile_small', 'mobile_tiny'], array_keys($breakpoints::enabled($both['values'])));
        self::assertSame('responsive_values', $class::assess(['breakpoints' => '360|320', 'size_body' => ['360' => '18', '320' => '16']])['status']);
        self::assertSame('unknown_format', $class::assess(['breakpoints' => '768|unknown'])['status']);
        self::assertSame('responsive_values', $class::assess(['breakpoints' => '768|560', 'size_body' => '{"desktop":"18","mobile":"14"}'])['status']);
        self::assertSame('responsive_values', $class::assess(['breakpoints' => '-', 'typo_h1' => ['size' => ['320' => '12']]])['status']);
        self::assertSame('ready', $class::assess(['breakpoints' => '768|560', 'size_body' => '{"desktop":"18"}'])['status']);
    }

    public function testLegacyFontPreviewRequiresExactBundledReferencesAndCompatibleDelivery(): void
    {
        $class = \WPEssential\Modules\SiteKit\SiteKitLegacyFontPreview::class;
        $legacy = ['font_body' => 'Inter', 'font_heading' => 'google:Roboto', 'font_mono' => 'System UI', 'fonts_extra' => 'Roboto|Inter'];
        $review = $class::assess($legacy, [], ['Inter' => 'theme:body']);
        self::assertSame(['font_body' => 'theme:body', 'font_heading' => 'google:Roboto', 'fonts_extra' => 'Roboto|Inter'], $review['ready']);
        self::assertSame(['font_mono'], $review['deferred']);
        $review = $class::assess($legacy, ['font_body' => 'theme:heading', 'fonts_delivery' => 'google'], ['Inter' => 'theme:body']);
        self::assertSame([], $review['ready']);
        self::assertSame(['font_body'], $review['conflicts']);
        self::assertSame(['font_heading', 'font_mono', 'fonts_extra'], $review['deferred']);
        $review = $class::assess(['font_body' => 'Inter', 'fonts_extra' => 'Roboto|Unknown Invalid Family'], [], []);
        self::assertSame([], $review['ready']);
        self::assertSame(['font_body', 'fonts_extra'], $review['deferred']);
        $review = $class::assess(['fonts_extra' => 'Roboto|Inter'], ['fonts_extra' => 'google:Roboto|Inter'], []);
        self::assertSame(['fonts_extra'], $review['unchanged']);
        self::assertSame(['fonts_extra'], $class::assess(['fonts_extra' => 'Roboto|Inter'], ['fonts_extra' => 'Roboto'], [])['conflicts']);
    }

    public function testRemoteFontHandoffRequiresMatchingRemoteDeliveryAndCompleteLists(): void
    {
        $class = \WPEssential\Modules\SiteKit\SiteKitLegacyFontPreview::class;
        $legacy = ['fonts_delivery' => 'google', 'font_body' => 'Inter', 'font_heading' => 'google:Roboto', 'fonts_extra' => 'Roboto|Inter'];
        self::assertSame(['font_heading' => 'google:Roboto', 'fonts_extra' => 'Roboto|Inter'], $class::remoteCandidates($legacy, ['fonts_delivery' => 'google']));
        self::assertSame([], $class::remoteCandidates($legacy, []));
        self::assertSame([], $class::remoteCandidates(['font_heading' => 'google:Roboto'], ['fonts_delivery' => 'google']));
        self::assertSame([], $class::remoteCandidates(['font_heading' => 'google:Roboto'], []));
        $legacy['fonts_extra'] = 'Roboto|Unknown Invalid Family';
        self::assertSame(['font_heading' => 'google:Roboto'], $class::remoteCandidates($legacy, ['fonts_delivery' => 'google']));
    }

    public function testLocalFontHandoffRequiresEveryWinningFamilyAndValidFaces(): void
    {
        $class = \WPEssential\Modules\SiteKit\SiteKitLegacyFontPreview::class;
        $candidates = ['font_body' => 'google:Roboto', 'font_heading' => 'google:Inter', 'fonts_extra' => 'Inter|Roboto'];
        self::assertSame(['font_heading' => 'google:Inter'], $class::cachedCandidates($candidates, [], static fn (string $family): bool => $family === 'Inter'));
        self::assertSame(['font_body' => 'google:Roboto', 'font_heading' => 'google:Inter'], $class::cachedCandidates($candidates, ['font_body' => 'google:Inter'], static fn (string $family): bool => $family === 'Inter'));
        $face = ['fontFamily' => 'Inter', 'fontStyle' => 'normal', 'fontWeight' => '100 900', 'fontDisplay' => 'swap', 'unicodeRange' => 'U+0000-00FF', 'src' => ['https://example.test/uploads/inter.woff2']];
        $valid = static fn (string $src): bool => $src === 'https://example.test/uploads/inter.woff2';
        self::assertSame([$face], $class::validatedFaces('Inter', [$face], $valid));
        self::assertSame([], $class::validatedFaces('Inter', [$face, array_replace($face, ['src' => ['https://fonts.gstatic.com/a.woff2']])], $valid));
        self::assertSame([], $class::validatedFaces('Roboto', [$face], $valid));
        self::assertSame([], $class::validatedFaces('Inter', [array_replace($face, ['extra' => 'unexpected'])], $valid));
    }

    public function testFontHandoffDefersUnresolvedCanonicalConflict(): void
    {
        $class = \WPEssential\Modules\SiteKit\SiteKitLegacyFontPreview::class;
        $candidates = ['font_body' => 'theme:hi-wpessential-inter', 'font_heading' => 'google:Roboto', 'font_mono' => 'theme:hi-wpessential-georgia', 'fonts_extra' => 'Roboto|Inter'];
        $canonical = ['font_body' => 'theme:missing', 'font_heading' => 'google:Inter', 'font_mono' => 'theme:mono', 'fonts_extra' => 'Roboto|Unknown Invalid Family'];
        self::assertSame(['font_heading' => 'google:Roboto', 'font_mono' => 'theme:hi-wpessential-georgia'], $class::resolvableCandidates($candidates, $canonical, ['mono']));
        self::assertSame($candidates, $class::resolvableCandidates($candidates, [], []));
        self::assertSame(['fonts_extra' => 'Roboto|Inter'], $class::resolvableCandidates(['fonts_extra' => 'Roboto|Inter'], ['fonts_extra' => 'google:Roboto|Inter'], []));
    }

    public function testExplicitFalseBreakpointSurvivesCustomizerAndStore(): void
    {
        $field = \WPEssential\Modules\SiteKit\SiteKitSchema::field('breakpoints');
        self::assertNotNull($field);
        $raw = '{"mobile":{"on":false},"mobile_small":{"on":true,"w":420}}';
        $value = \WPEssential\Modules\SiteKit\SiteKitCustomizer::sanitize($field, $raw);
        self::assertSame(['mobile' => ['on' => false], 'mobile_small' => ['on' => true, 'w' => 420]], $value);
        $store = new SiteKitStore();
        $store->update(['breakpoints' => $raw]);
        self::assertSame($value, $store->values()['breakpoints']);
        $enabled = \WPEssential\Modules\SiteKit\SiteKitBreakpoints::enabled($value);
        self::assertArrayNotHasKey('mobile', $enabled);
        self::assertSame(420, $enabled['mobile_small']['width']);
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
