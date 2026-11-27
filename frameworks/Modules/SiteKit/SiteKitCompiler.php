<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Closure;

/**
 * Turns Site Kit values into theme.json user-layer data and front-end CSS
 * (ported from hi-wpessential inc/customizer*.php, with theme-agnostic selectors).
 */
final class SiteKitCompiler {

	/** @var array<string,mixed> The `units` setting of the values being compiled (custom unit conversion). */
	private static array $units = [];

	public const BUTTON = '.wp-element-button,.wp-block-button__link,.wpe-btn,button.button,a.button';
	public const LABEL = ':where(form,.wpe-form,.elementor-form) label';
	public const FIELD  = 'input:is([type="text"],[type="email"],[type="url"],[type="password"],[type="search"],[type="number"],[type="tel"],[type="date"]),select,textarea,.wpe-input';
	public const IMAGE = ':where(.wp-block-post-content,.entry-content) :where(.wp-block-image img,.wp-block-post-featured-image img,figure img)';
	public const CAPTION = ':where(.wp-block-post-content,.entry-content) :where(.wp-block-image figcaption,.wp-block-post-featured-image figcaption)';
	public const LINK = ':where(.wp-block-post-content,.entry-content) a';
	public const HEADER = ':is(header.wp-block-template-part,.wp-site-blocks>header,.site-header,#masthead,.wpe-header)';
	public const FOOTER = ':is(footer.wp-block-template-part,.wp-site-blocks>footer,.site-footer,#colophon,.wpe-footer)';

	/** Responsive and composite settings that write CSS directly: key => [selector, property]. */
	private const TARGETS = [
		'size_body'     => [ 'body', 'font-size' ],
		'section_space' => [ ':root', '--wpe-site-section-space' ],
		'header_height' => [ ':root', '--wpe-site-header-height' ],
		'menu_size'     => [ self::HEADER . ' .wp-block-navigation,' . self::HEADER . ' nav', 'font-size' ],
		'menu_gap'      => [ self::HEADER . ' .wp-block-navigation', 'gap|--navigation-layout-gap' ],
		'btn_size'      => [ self::BUTTON, 'font-size' ],
		'btn_padding'   => [ self::BUTTON, 'padding' ],
		'btn_min_height' => [ self::BUTTON, 'min-height' ],
		'field_text_size' => [ self::FIELD, 'font-size' ],
		'field_label_size' => [ self::LABEL, 'font-size' ],
		'field_height'  => [ self::FIELD, 'min-height' ],
		'field_padding' => [ self::FIELD, 'padding' ],
		'image_radius' => [ self::IMAGE, 'border-radius' ],
		'image_caption_size' => [ self::CAPTION, 'font-size' ],
		'gutter'        => [ ':root', '--wp--style--root--padding-left|--wp--style--root--padding-right' ],
		'typo_body'     => [ 'body', '' ],
		'typo_h1'       => [ 'h1,.wp-block-post-title', '' ],
		'typo_h2'       => [ 'h2', '' ],
		'typo_h3'       => [ 'h3', '' ],
		'typo_h4'       => [ 'h4', '' ],
		'typo_h5'       => [ 'h5', '' ],
		'typo_h6'       => [ 'h6', '' ],
		'typo_button'   => [ self::BUTTON, '' ],
		'typo_menu'     => [ self::HEADER . ' .wp-block-navigation-item__content,' . self::HEADER . ' nav a', '' ],
		'typo_small'    => [ 'small,.has-small-font-size,figcaption', '' ],
		'site_bg'       => [ 'body', '' ],
		'site_bg_dark'  => [ ':root[data-wpe-scheme="dark"] body', '' ],
	];

	/**
	 * Merges the values into theme.json user data.
	 *
	 * @param array<string,mixed>                         $data   theme.json user data (version, settings, styles).
	 * @param array<string,mixed>                         $v      Site Kit values.
	 * @param Closure(list<string>):list<array<string,mixed>> $preset Theme preset list for a path (fallback when the user layer has none).
	 * @return array<string,mixed>
	 */
	public static function themeJson( array $data, array $v, Closure $preset ): array {
		self::$units = is_array( $v['units'] ?? null ) ? $v['units'] : [];
		$settings    = is_array( $data['settings'] ?? null ) ? $data['settings'] : [];
		$styles   = is_array( $data['styles'] ?? null ) ? $data['styles'] : [];
		$list     = static function ( array $path ) use ( $settings, $preset ): array {
			$own = $settings;
			foreach ( $path as $step ) {
				$own = is_array( $own ) ? ( $own[ $step ] ?? [] ) : [];
			}
			if ( is_array( $own ) && ( isset( $own['custom'] ) || isset( $own['theme'] ) ) ) {
				$own = $own['custom'] ?? $own['theme'];
			}

			return is_array( $own ) && $own !== [] ? array_values( $own ) : $preset( $path );
		};

		// Palette: overwrite the theme's slugs, add the ones it does not have.
		$palette = [];
		$seen    = [];
		foreach ( $list( [ 'color', 'palette' ] ) as $colour ) {
			$slug = (string) ( $colour['slug'] ?? '' );
			if ( ! empty( $v[ 'color_' . $slug ] ) ) {
				$colour['color'] = $v[ 'color_' . $slug ];
			}
			$seen[ $slug ] = true;
			$palette[]     = $colour;
		}
		foreach ( SiteKitSchema::COLOURS as $slug ) {
			if ( ! isset( $seen[ $slug ] ) && ! empty( $v[ 'color_' . $slug ] ) ) {
				$palette[] = [
					'slug'  => $slug,
					'name'  => ucwords( str_replace( '-', ' ', $slug ) ),
					'color' => $v[ 'color_' . $slug ],
				];
			}
		}
		foreach ( SiteKitTokens::colours( $v['custom_colours'] ?? [] ) as $token ) {
			$entry = [
				'slug'  => $token['slug'],
				'name'  => $token['name'],
				'color' => $token['color'],
			];
			foreach ( $palette as $index => $colour ) {
				if ( ( $colour['slug'] ?? '' ) === $token['slug'] ) {
					$palette[ $index ] = $entry;
					continue 2;
				}
			}
			$palette[] = $entry;
		}
		if ( $palette !== [] ) {
			$settings['color']['palette'] = $palette;
		}

		// Fonts: body / heading / mono slugs, plus the extra families for the editors.
		$families = [];
		$present  = [];
		foreach ( $list( [ 'typography', 'fontFamilies' ] ) as $family ) {
			$slug = (string) ( $family['slug'] ?? '' );
			$pick = (string) ( $v[ 'font_' . $slug ] ?? '' );
			if ( str_starts_with( $pick, 'google:' ) ) {
				$family = SiteKitFonts::entry( $slug, substr( $pick, 7 ), $v );
			}
			$present[ $slug ] = true;
			$families[]       = $family;
		}
		foreach ( [ 'body', 'heading', 'mono' ] as $slug ) {
			$pick = (string) ( $v[ 'font_' . $slug ] ?? '' );
			if ( ! isset( $present[ $slug ] ) && str_starts_with( $pick, 'google:' ) ) {
				$families[] = SiteKitFonts::entry( $slug, substr( $pick, 7 ), $v );
			}
		}
		foreach ( array_filter( array_map( 'trim', explode( '|', (string) ( $v['fonts_extra'] ?? '' ) ) ) ) as $family ) {
			if ( SiteKitFonts::exists( $family ) ) {
				$families[] = SiteKitFonts::entry( 'g-' . strtolower( (string) preg_replace( '/[^a-z0-9]+/i', '-', $family ) ), $family, $v );
			}
		}
		if ( $families !== [] ) {
			$settings['typography']['fontFamilies'] = $families;
		}
		foreach ( [
			'body'    => [ 'typography' ],
			'heading' => [ 'elements', 'heading', 'typography' ],
		] as $slug => $path ) {
			if ( ! empty( $v[ 'font_' . $slug ] ) ) {
				$font = 'var(--wp--preset--font-family--' . $slug . ')';
				if ( $slug === 'body' ) {
					$styles['typography']['fontFamily'] = $font;
				} else {
					$styles['elements']['heading']['typography']['fontFamily'] = $font;
				}
			}
		}

		// Font sizes: fluid between the phone and the desktop value.
		$sizes = [];
		foreach ( $list( [ 'typography', 'fontSizes' ] ) as $size ) {
			$slug = (string) ( $size['slug'] ?? '' );
			$min  = self::len( $v, 'size_' . $slug . '_min' );
			$max  = self::len( $v, 'size_' . $slug . '_max' );
			if ( $slug === 'small' && self::len( $v, 'size_small' ) !== '' ) {
				$size['size']  = self::len( $v, 'size_small' );
				$size['fluid'] = false;
			} elseif ( $min !== '' || $max !== '' ) {
				$size['size']  = $max !== '' ? $max : ( $size['size'] ?? $min );
				$size['fluid'] = [
					'min' => $min !== '' ? $min : ( $size['fluid']['min'] ?? $size['size'] ),
					'max' => $max !== '' ? $max : ( $size['fluid']['max'] ?? $size['size'] ),
				];
			}
			$sizes[] = $size;
		}
		foreach ( SiteKitTokens::typography( $v['custom_typography'] ?? [] ) as $token ) {
			$style = self::typography( $token['typography'] )['desktop'];
			if ( isset( $style['font-size'] ) ) {
				$entry = [
					'slug'  => $token['slug'],
					'name'  => $token['name'],
					'size'  => $style['font-size'],
					'fluid' => false,
				];
				foreach ( $sizes as $index => $size ) {
					if ( ( $size['slug'] ?? '' ) === $token['slug'] ) {
						$sizes[ $index ] = $entry;
						continue 2;
					}
				}
				$sizes[] = $entry;
			}
		}
		if ( $sizes !== [] ) {
			$settings['typography']['fontSizes'] = $sizes;
		}

		// Layout and custom tokens.
		if ( self::len( $v, 'content_width' ) !== '' ) {
			$settings['layout']['contentSize'] = self::len( $v, 'content_width' );
		}
		if ( self::len( $v, 'wide_width' ) !== '' ) {
			$settings['layout']['wideSize'] = self::len( $v, 'wide_width' );
		}
		foreach ( [ 'sm', 'md', 'lg', 'xl' ] as $radius ) {
			if ( self::len( $v, 'radius_' . $radius ) !== '' ) {
				$settings['custom']['radius'][ $radius ] = self::len( $v, 'radius_' . $radius );
			}
		}
		$hover = array_filter(
			[
				'duration'   => self::len( $v, 'hover_duration', 'ms' ),
				'ease'       => (string) ( $v['hover_ease'] ?? '' ),
				'lift'       => self::len( $v, 'hover_lift' ) !== '' ? '-' . self::len( $v, 'hover_lift' ) : '',
				'buttonLift' => self::len( $v, 'btn_lift' ) !== '' ? '-' . self::len( $v, 'btn_lift' ) : '',
				'zoom'       => self::len( $v, 'image_zoom', '' ),
			]
		);
		foreach ( $hover as $key => $value ) {
			$settings['custom']['hover'][ $key ] = $value;
		}

		// Headings and body text.
		$heading = array_filter(
			[
				'fontWeight'    => (string) ( $v['heading_weight'] ?? '' ),
				'letterSpacing' => self::len( $v, 'heading_spacing', 'em' ),
				'lineHeight'    => self::len( $v, 'heading_line', '' ),
				'textTransform' => (string) ( $v['heading_case'] ?? '' ),
			]
		);
		if ( $heading !== [] ) {
			foreach ( [ 'heading', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ] as $element ) {
				$styles['elements'][ $element ]['typography'] = array_merge( (array) ( $styles['elements'][ $element ]['typography'] ?? [] ), $heading );
			}
		}
		foreach ( [
			'h5' => 'size_h5',
			'h6' => 'size_h6',
		] as $element => $key ) {
			if ( self::len( $v, $key ) !== '' ) {
				$styles['elements'][ $element ]['typography']['fontSize'] = self::len( $v, $key );
			}
		}
		if ( ! empty( $v['heading_color'] ) ) {
			$styles['elements']['heading']['color']['text'] = $v['heading_color'];
		}
		if ( self::len( $v, 'line_body', '' ) !== '' ) {
			$styles['typography']['lineHeight'] = self::len( $v, 'line_body', '' );
		}
		if ( ! empty( $v['body_weight'] ) ) {
			$styles['typography']['fontWeight'] = $v['body_weight'];
		}
		if ( self::len( $v, 'block_gap' ) !== '' ) {
			$styles['spacing']['blockGap'] = self::len( $v, 'block_gap' );
		}

		// Buttons.
		$button = (array) ( $styles['elements']['button'] ?? [] );
		if ( self::len( $v, 'btn_radius' ) !== '' ) {
			$button['border']['radius'] = self::len( $v, 'btn_radius' );
		}
		$padding = is_array( $v['btn_padding'] ?? null ) ? self::dims( (array) ( $v['btn_padding']['desktop'] ?? [] ) ) : '';
		if ( $padding !== '' ) {
			foreach ( array_combine( [ 'top', 'right', 'bottom', 'left' ], explode( ' ', $padding ) ) as $side => $value ) {
				$button['spacing']['padding'][ $side ] = $value;
			}
		}
		$button['typography'] = array_merge(
			(array) ( $button['typography'] ?? [] ),
			array_filter(
				[
					'fontSize'      => self::len( $v, 'btn_size' ),
					'fontWeight'    => (string) ( $v['btn_weight'] ?? '' ),
					'textTransform' => (string) ( $v['btn_case'] ?? '' ),
					'letterSpacing' => self::len( $v, 'btn_spacing', 'em' ),
				]
			)
		);
		if ( ! empty( $v['btn_bg'] ) ) {
			$button['color']['background'] = $v['btn_bg'];
		}
		if ( ! empty( $v['btn_text'] ) ) {
			$button['color']['text'] = $v['btn_text'];
		}
		if ( ! empty( $v['btn_hover_bg'] ) ) {
			$button[':hover']['color']['background'] = $v['btn_hover_bg'];
		}
		if ( ! empty( $v['btn_hover_text'] ) ) {
			$button[':hover']['color']['text'] = $v['btn_hover_text'];
		}
		if ( self::len( $v, 'btn_border_w' ) !== '' ) {
			$button['border']['width'] = self::len( $v, 'btn_border_w' );
			$button['border']['style'] = 'solid';
		}
		if ( ! empty( $v['btn_border'] ) ) {
			$button['border']['color'] = $v['btn_border'];
		}
		if ( ! empty( $v['btn_shadow'] ) ) {
			$button['shadow'] = $v['btn_shadow'] === 'none' ? 'none' : 'var(--wp--preset--shadow--' . $v['btn_shadow'] . ')';
		}
		if ( array_filter( $button ) !== [] ) {
			$styles['elements']['button'] = $button;
		}

		// Gradients and shadows.
		$gradients = [];
		foreach ( $list( [ 'color', 'gradients' ] ) as $gradient ) {
			$css = self::gradient( (string) ( $gradient['slug'] ?? '' ), $v );
			if ( $css !== '' ) {
				$gradient['gradient'] = $css;
			}
			$gradients[] = $gradient;
		}
		if ( $gradients !== [] ) {
			$settings['color']['gradients'] = $gradients;
		}
		$shadows = [];
		foreach ( $list( [ 'shadow', 'presets' ] ) as $shadow ) {
			$css = self::shadow( $shadow, $v );
			if ( $css !== '' ) {
				$shadow['shadow'] = $css;
			}
			$shadows[] = $shadow;
		}
		foreach ( SiteKitTokens::shadows( $v['custom_shadows'] ?? [] ) as $token ) {
			$shadows = array_values( array_filter( $shadows, static fn ( array $p ): bool => ( $p['slug'] ?? '' ) !== $token['slug'] ) );
			$shadows[] = [
				'name'   => $token['name'],
				'slug'   => $token['slug'],
				'shadow' => self::namedShadow( $token['shadow'] ),
			];
		}
		if ( $shadows !== [] ) {
			$settings['shadow']['presets'] = $shadows;
		}

		// Links.
		$link = (array) ( $styles['elements']['link'] ?? [] );
		if ( ! empty( $v['link_color'] ) ) {
			$link['color']['text'] = $v['link_color'];
		}
		if ( ! empty( $v['link_hover'] ) ) {
			$link[':hover']['color']['text'] = $v['link_hover'];
		}
		if ( ! empty( $v['link_weight'] ) ) {
			$link['typography']['fontWeight'] = $v['link_weight'];
		}
		$underline = (string) ( $v['link_underline'] ?? '' );
		if ( $underline === 'always' || $underline === 'never' ) {
			$link['typography']['textDecoration']           = $underline === 'always' ? 'underline' : 'none';
			$link[':hover']['typography']['textDecoration'] = $underline === 'always' ? 'underline' : 'none';
		}
		if ( $link !== [] ) {
			$styles['elements']['link'] = $link;
		}

		return [
			'version'  => 3,
			'settings' => $settings,
			'styles'   => $styles,
		];
	}

	/**
	 * CSS for what theme.json cannot express (dark colours, forms, header/footer, responsive values).
	 *
	 * @param array<string,mixed> $v
	 */
	public static function css( array $v ): string {
		self::$units = is_array( $v['units'] ?? null ) ? $v['units'] : [];
		$css         = '';
		$dark = '';
		// Brand colours stay the same in dark mode unless a dark value is set.
		foreach ( [ 'primary', 'primary-hover', 'secondary', 'accent', 'success', 'warning', 'danger' ] as $slug ) {
			if ( ! empty( $v[ 'color_' . $slug ] ) && empty( $v[ 'dark_' . $slug ] ) ) {
				$v[ 'dark_' . $slug ] = $v[ 'color_' . $slug ];
			}
		}
		foreach ( $v as $key => $value ) {
			if ( str_starts_with( (string) $key, 'dark_' ) && is_string( $value ) ) {
				$dark .= '--wp--preset--color--' . substr( (string) $key, 5 ) . ':' . $value . ';';
			}
		}
		foreach ( SiteKitTokens::colours( $v['custom_colours'] ?? [] ) as $token ) {
			if ( $token['dark'] !== '' ) {
				$dark .= '--wp--preset--color--' . $token['slug'] . ':' . $token['dark'] . ';';
			}
		}
		if ( $dark !== '' ) {
			$css .= ':root[data-wpe-scheme="dark"]{color-scheme:dark;' . $dark . '}';
		}

		$rule  = static function ( string $selector, array $declarations ): string {
			$declarations = array_filter( $declarations, static fn ( mixed $x ): bool => $x !== '' && $x !== null );
			if ( $declarations === [] ) {
				return '';
			}
			$out = '';
			foreach ( $declarations as $property => $value ) {
				$out .= $property . ':' . $value . ';';
			}

			return $selector . '{' . $out . '}';
		};
		$field = array_filter(
			[
				'border-radius'    => self::len( $v, 'field_radius' ),
				'border-color'     => (string) ( $v['field_border'] ?? '' ),
				'background-color' => (string) ( $v['field_bg'] ?? '' ),
			]
		);
		$css  .= $rule( self::FIELD, $field );
		// Keep new defaults opt-in and low specificity so local styles win.
		$input = ':where(' . self::FIELD . ')';
		$css .= $rule(
			$input,
			[
				'color'        => SiteKitSanitizer::colour( $v['field_text'] ?? '' ),
				'border-width' => self::len( $v, 'field_border_width' ),
				'border-style' => self::len( $v, 'field_border_width' ) !== '' ? 'solid' : '',
			]
		);
		$css .= $rule( $input . '::placeholder', [ 'color' => SiteKitSanitizer::colour( $v['field_placeholder'] ?? '' ) ] );
		$css .= $rule( self::LABEL, [ 'color' => SiteKitSanitizer::colour( $v['field_label'] ?? '' ) ] );
		$css .= $rule(
			$input . ':focus',
			[
				'background-color' => SiteKitSanitizer::colour( $v['field_focus_bg'] ?? '' ),
				'color'            => SiteKitSanitizer::colour( $v['field_focus_text'] ?? '' ),
			]
		);
		$css .= $rule(
			$input . ':is(:disabled,[aria-disabled="true"])',
			[
				'background-color' => SiteKitSanitizer::colour( $v['field_disabled_bg'] ?? '' ),
				'color'            => SiteKitSanitizer::colour( $v['field_disabled_text'] ?? '' ),
				'border-color'     => SiteKitSanitizer::colour( $v['field_disabled_border'] ?? '' ),
			]
		);
		$css .= $rule( $input . '[aria-invalid="true"]', [ 'border-color' => SiteKitSanitizer::colour( $v['field_error'] ?? '' ) ] );
		$css .= $rule( ':where(.wpcf7-not-valid-tip,.elementor-message-danger,.wpe-field-error)', [ 'color' => SiteKitSanitizer::colour( $v['field_error_text'] ?? '' ) ] );
		if ( ! empty( $v['field_focus'] ) ) {
			$css .= 'input:focus,select:focus,textarea:focus,.wpe-input:focus{border-color:' . $v['field_focus'] . ';box-shadow:0 0 0 3px color-mix(in srgb,' . $v['field_focus'] . ' 25%,transparent);outline:2px solid ' . $v['field_focus'] . ';outline-offset:2px}';
		}
		if ( ! empty( $v['image_round'] ) && ! isset( $v['image_radius'] ) ) {
			$css .= '.wp-block-post-content img,.entry-content img{border-radius:var(--wp--custom--radius--md,12px)}';
		}
		$css .= $rule(
			self::IMAGE,
			[
				'border-width' => self::len( $v, 'image_border_width' ),
				'border-style' => self::len( $v, 'image_border_width' ) !== '' ? 'solid' : '',
				'border-color' => SiteKitSanitizer::colour( $v['image_border_color'] ?? '' ),
				'object-fit' => in_array( $v['image_fit'] ?? '', [ 'cover', 'contain', 'fill', 'none', 'scale-down' ], true ) ? $v['image_fit'] : '',
			]
		);
		$css .= $rule( self::CAPTION, [ 'color' => SiteKitSanitizer::colour( $v['image_caption_color'] ?? '' ) ] );
		if ( self::len( $v, 'image_zoom', '' ) !== '' ) {
			$css .= '.wp-block-image,.wp-block-post-featured-image{overflow:hidden}.wp-block-image img,.wp-block-post-featured-image img{transition:transform var(--wp--custom--hover--duration,.3s) var(--wp--custom--hover--ease,ease)}.wp-block-image:hover img,.wp-block-post-featured-image:hover img{transform:scale(' . self::len( $v, 'image_zoom', '' ) . ')}';
		}
		if ( ( $v['sticky_header'] ?? '' ) === 'off' ) {
			$css .= self::HEADER . '{position:relative!important;top:auto!important}';
		}
		if ( self::len( $v, 'paragraph_space', 'em' ) !== '' ) {
			$css .= '.wp-block-post-content p,.entry-content p{margin-block:0 ' . self::len( $v, 'paragraph_space', 'em' ) . '}';
		}
		$css .= $rule(
			'::selection',
			[
				'background' => (string) ( $v['selection_bg'] ?? '' ),
				'color'      => (string) ( $v['selection_text'] ?? '' ),
			]
		);
		$css .= $rule(
			'a',
			[
				'text-decoration-thickness' => self::len( $v, 'link_thickness' ),
				'text-underline-offset'     => self::len( $v, 'link_offset' ),
			]
		);
		$css .= $rule( self::LINK, [ 'text-decoration-style' => in_array( $v['link_decoration_style'] ?? '', [ 'solid', 'double', 'dotted', 'dashed', 'wavy' ], true ) ? $v['link_decoration_style'] : '' ] );
		foreach ( [
			'visited'       => 'link_visited',
			'focus-visible' => 'link_focus',
			'active'        => 'link_active',
		] as $state => $key ) {
			$css .= $rule( self::LINK . ':' . $state, [ 'color' => SiteKitSanitizer::colour( $v[ $key ] ?? '' ) ] );
		}
		if ( ( $v['link_underline'] ?? '' ) === 'text' ) {
			$css .= '.wp-block-post-content p a,.entry-content p a,.wp-block-post-content li a{text-decoration:underline}';
		}
		if ( self::len( $v, 'btn_lift' ) !== '' ) {
			$css .= self::hover( self::BUTTON ) . '{transform:translateY(-' . self::len( $v, 'btn_lift' ) . ')}';
		}
		$css .= $rule( self::hover( self::BUTTON ), [ 'border-color' => (string) ( $v['btn_hover_border'] ?? '' ) ] );
		foreach ( [
			'focus-visible'                     => 'focus',
			'active'                            => 'active',
			'is(:disabled,[aria-disabled="true"])' => 'disabled',
		] as $pseudo => $state ) {
			$selector = ':is(' . self::BUTTON . '):' . $pseudo;
			$css .= $rule(
				$selector,
				[
					'background-color' => SiteKitSanitizer::colour( $v[ 'btn_' . $state . '_bg' ] ?? '' ),
					'color'            => SiteKitSanitizer::colour( $v[ 'btn_' . $state . '_text' ] ?? '' ),
					'border-color'     => SiteKitSanitizer::colour( $v[ 'btn_' . $state . '_border' ] ?? '' ),
				]
			);
		}
		$ring = SiteKitSanitizer::colour( $v['btn_focus_ring'] ?? '' );
		$width = self::len( $v, 'btn_focus_width' );
		if ( $ring !== '' || $width !== '' ) {
			$css .= $rule(
				':is(' . self::BUTTON . '):focus-visible',
				[
					'outline'        => ( $width ?: '2px' ) . ' solid ' . ( $ring ?: 'currentColor' ),
					'outline-offset' => '2px',
				]
			);
		}
		$css .= $rule(
			self::HEADER,
			[
				'background'          => ! empty( $v['header_glass'] ) ? 'color-mix(in srgb,' . ( $v['header_bg'] ?? 'var(--wp--preset--color--surface,#fff)' ) . ' 72%,transparent)' : (string) ( $v['header_bg'] ?? '' ),
				'color'               => (string) ( $v['header_text'] ?? '' ),
				'border-color'        => (string) ( $v['header_border'] ?? '' ),
				'border-bottom-style' => ! empty( $v['header_border'] ) ? 'solid' : '',
				'border-bottom-width' => ! empty( $v['header_border'] ) ? '1px' : '',
				'backdrop-filter'     => ! empty( $v['header_glass'] ) ? 'saturate(1.6) blur(14px)' : '',
				'min-height'          => self::len( $v, 'header_height' ),
			]
		);
		if ( ! empty( $v['header_text'] ) ) {
			$css .= self::HEADER . ' a,' . self::HEADER . ' .wp-block-navigation-item__content{color:' . $v['header_text'] . '}';
		}
		$css .= $rule( self::HEADER . ' .wp-block-navigation,' . self::HEADER . ' nav', [ 'font-weight' => (string) ( $v['menu_weight'] ?? '' ) ] );
		$css .= $rule(
			self::FOOTER,
			[
				'background' => (string) ( $v['footer_bg'] ?? '' ),
				'color'      => (string) ( $v['footer_text'] ?? '' ),
			]
		);
		$css .= $rule( self::FOOTER . ' :is(h1,h2,h3,h4,h5,h6,.wp-block-heading)', [ 'color' => (string) ( $v['footer_heading'] ?? '' ) ] );
		$css .= $rule( self::FOOTER . ' a', [ 'color' => (string) ( $v['footer_link'] ?? '' ) ] );
		$css .= $rule( self::FOOTER . ' a:hover,' . self::FOOTER . ' a:focus-visible', [ 'color' => (string) ( $v['footer_link_hover'] ?? '' ) ] );
		if ( ! empty( $v['focus_color'] ) || self::len( $v, 'focus_width' ) !== '' ) {
			$css .= ':focus-visible{outline:' . ( self::len( $v, 'focus_width' ) !== '' ? self::len( $v, 'focus_width' ) : '3px' ) . ' solid ' . ( $v['focus_color'] ?? 'var(--wp--preset--color--primary,currentColor)' ) . ';outline-offset:2px}';
		}
		if ( ( $v['skip_link'] ?? '' ) === 'show' ) {
			$css .= '.skip-link.screen-reader-text{position:static!important;clip:auto!important;clip-path:none!important;width:auto!important;height:auto!important;display:block;padding:8px 16px}';
		}
		if ( self::len( $v, 'min_text' ) !== '' ) {
			$css .= 'small,.has-small-font-size,.has-xs-font-size,figcaption{font-size:max(1em,' . self::len( $v, 'min_text' ) . ')}';
		}
		if ( self::len( $v, 'hover_lift' ) !== '' ) {
			$css .= ':root{--wpe-site-hover-lift:-' . self::len( $v, 'hover_lift' ) . '}';
		}
		$gradients = '';
		foreach ( [ 'brand', 'night', 'fade-bottom' ] as $slug ) {
			$gradient = self::gradient( $slug, $v );
			if ( $gradient !== '' ) {
				$gradients .= '--wp--preset--gradient--' . $slug . ':' . $gradient . ';';
			}
		}
		if ( $gradients !== '' ) {
			$css .= ':root{' . $gradients . '}';
		}
		$shadowVars = '';
		foreach ( SiteKitTokens::shadows( $v['custom_shadows'] ?? [] ) as $token ) {
			$shadowVars .= '--wp--preset--shadow--' . $token['slug'] . ':' . self::namedShadow( $token['shadow'] ) . ';';
		}
		if ( $shadowVars !== '' ) {
			$css .= ':root{' . $shadowVars . '}';
		}
		$css .= self::tokenCss( $v );
		$css .= self::controls( $v );
		if ( ! empty( $v['motion_off'] ) ) {
			$css .= '*,*::before,*::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}';
		} else {
			$css .= '@media (prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}}';
		}

		return $css;
	}

	private static function hover( string $selectors ): string {
		return implode( ',', array_map( static fn ( string $s ): string => $s . ':hover', explode( ',', $selectors ) ) );
	}

	/** Number with its unit, or the desktop value of a responsive setting. @param array<string,mixed> $v */
	public static function len( array $v, string $key, string $unit = 'px' ): string {
		$value = $v[ $key ] ?? null;
		if ( is_array( $value ) ) {
			return self::size( $value['desktop'] ?? '', $unit );
		}

		return is_numeric( $value ) ? ( 0 + $value ) . $unit : '';
	}

	private static function size( mixed $value, string $unit = 'px' ): string {
		if ( is_array( $value ) ) {
			$unit  = (string) ( $value['unit'] ?? $unit );
			$value = $value['size'] ?? '';
		}

		return is_numeric( $value ) ? SiteKitUnits::css( 0 + $value, $unit, self::$units ) : '';
	}

	/** @param array<string,mixed> $d Dimensions {top,right,bottom,left,unit}. */
	public static function dims( array $d ): string {
		$unit  = (string) ( $d['unit'] ?? 'px' );
		$sides = [];
		foreach ( [ 'top', 'right', 'bottom', 'left' ] as $side ) {
			if ( ! isset( $d[ $side ] ) || ! is_numeric( $d[ $side ] ) ) {
				return '';
			}
			$sides[] = SiteKitUnits::css( 0 + $d[ $side ], $unit, self::$units );
		}

		return implode( ' ', $sides );
	}

	/** @param array<string,mixed> $v */
	public static function gradient( string $slug, array $v ): string {
		if ( $slug === 'fade-bottom' && ! empty( $v['grad_fade'] ) ) {
			$amount = is_numeric( $v['grad_fade_amount'] ?? null ) ? (int) $v['grad_fade_amount'] : 85;

			return 'linear-gradient(180deg, color-mix(in srgb, ' . $v['grad_fade'] . ' 0%, transparent) 0%, color-mix(in srgb, ' . $v['grad_fade'] . ' ' . $amount . '%, transparent) 100%)';
		}
		if ( ! in_array( $slug, [ 'brand', 'night' ], true ) ) {
			return '';
		}
		$own = is_array( $v[ 'grad_' . $slug ] ?? null ) ? self::gradientCss( $v[ 'grad_' . $slug ] ) : '';
		if ( $own !== '' ) {
			return $own;
		}
		if ( $slug === 'brand' && ! empty( $v['grad_brand_auto'] ) ) {
			return 'linear-gradient(120deg, var(--wp--preset--color--primary) 0%, var(--wp--preset--color--secondary) 100%)';
		}

		return '';
	}

	/** @param array<string,mixed> $g {type, angle, from, fromStop, to, toStop, position} */
	public static function gradientCss( array $g ): string {
		if ( empty( $g['from'] ) && empty( $g['to'] ) ) {
			return '';
		}
		$from = (string) ( $g['from'] ?? $g['to'] );
		$to   = (string) ( $g['to'] ?? $g['from'] );
		$a    = is_numeric( $g['fromStop'] ?? null ) ? (int) $g['fromStop'] : 0;
		$b    = is_numeric( $g['toStop'] ?? null ) ? (int) $g['toStop'] : 100;
		if ( ( $g['type'] ?? '' ) === 'radial' ) {
			return 'radial-gradient(circle at ' . preg_replace( '/[^a-z ]/', '', (string) ( $g['position'] ?? 'center center' ) ) . ', ' . $from . ' ' . $a . '%, ' . $to . ' ' . $b . '%)';
		}
		$angle = is_numeric( $g['angle'] ?? null ) ? (int) $g['angle'] : 180;

		return 'linear-gradient(' . $angle . 'deg, ' . $from . ' ' . $a . '%, ' . $to . ' ' . $b . '%)';
	}

	/**
	 * @param array<string,mixed> $preset {slug, shadow}
	 * @param array<string,mixed> $v
	 */
	public static function shadow( array $preset, array $v ): string {
		$slug = (string) ( $preset['slug'] ?? '' );
		$box  = is_array( $v[ 'shadow_' . $slug ] ?? null ) ? $v[ 'shadow_' . $slug ] : [];
		if ( isset( $box['x'] ) || isset( $box['y'] ) || isset( $box['blur'] ) ) {
			$n = static fn ( string $k ): string => ( is_numeric( $box[ $k ] ?? null ) ? 0 + $box[ $k ] : 0 ) . 'px';

			return ( ! empty( $box['inset'] ) ? 'inset ' : '' ) . $n( 'x' ) . ' ' . $n( 'y' ) . ' ' . $n( 'blur' ) . ' ' . $n( 'spread' ) . ' ' . ( $box['color'] ?? 'rgba(0,0,0,.15)' );
		}
		if ( empty( $v['shadow_color'] ) && ! isset( $v['shadow_strength'] ) ) {
			return '';
		}
		$hex      = ltrim( (string) ( $v['shadow_color'] ?? '#0a0f21' ), '#' );
		$strength = is_numeric( $v['shadow_strength'] ?? null ) ? $v['shadow_strength'] / 100 : 1;
		if ( strlen( $hex ) !== 6 ) {
			return '';
		}
		$rgb = implode( ',', array_map( 'hexdec', str_split( $hex, 2 ) ) );

		// Same offsets as the theme preset, new colour and opacity.
		return (string) preg_replace_callback(
			'/rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*,\s*([\d.]+)\s*\)/',
			static fn ( array $m ): string => 'rgba(' . $rgb . ',' . round( min( 1, (float) $m[1] * $strength ), 3 ) . ')',
			(string) ( $preset['shadow'] ?? '' )
		);
	}

	/** @param array{x:float,y:float,blur:float,spread:float,inset:bool,color:string} $box Sanitized named shadow. */
	public static function namedShadow( array $box ): string {
		return ( $box['inset'] ? 'inset ' : '' ) . $box['x'] . 'px ' . $box['y'] . 'px ' . $box['blur'] . 'px ' . $box['spread'] . 'px ' . $box['color'];
	}

	/**
	 * Typography set as declarations per device.
	 *
	 * @param array<string,mixed> $t
	 * @return array<string,array<string,string>>
	 */
	public static function typography( array $t, array $devices = [ 'desktop', 'tablet', 'mobile' ] ): array {
		$out    = array_fill_keys( $devices, [] );
		$family = (string) ( $t['family'] ?? '' );
		if ( str_starts_with( $family, 'google:' ) && SiteKitFonts::exists( substr( $family, 7 ) ) ) {
			$out['desktop']['font-family'] = SiteKitFonts::stack( substr( $family, 7 ) );
		} elseif ( str_starts_with( $family, 'theme:' ) ) {
			$out['desktop']['font-family'] = 'var(--wp--preset--font-family--' . preg_replace( '/[^a-z0-9-]/', '', substr( $family, 6 ) ) . ')';
		}
		foreach ( [
			'size'          => 'font-size',
			'lineHeight'    => 'line-height',
			'letterSpacing' => 'letter-spacing',
		] as $key => $property ) {
			$value = is_array( $t[ $key ] ?? null ) ? $t[ $key ] : [ 'desktop' => $t[ $key ] ?? '' ];
			foreach ( array_keys( $out ) as $device ) {
				$unit = $key === 'lineHeight' ? '' : ( $key === 'letterSpacing' ? 'em' : 'px' );
				$css  = self::size( $value[ $device ] ?? '', $unit );
				if ( $css !== '' ) {
					$out[ $device ][ $property ] = $css;
				}
			}
		}
		foreach ( [
			'weight'     => 'font-weight',
			'transform'  => 'text-transform',
			'style'      => 'font-style',
			'decoration' => 'text-decoration',
		] as $key => $property ) {
			if ( ! empty( $t[ $key ] ) ) {
				$out['desktop'][ $property ] = (string) $t[ $key ];
			}
		}

		return $out;
	}

	/**
	 * @param array<string,mixed> $b {type: classic|gradient, color, image{url}, position, size, repeat, attachment, gradient{}}
	 * @return array<string,string>
	 */
	public static function background( array $b ): array {
		if ( ( $b['type'] ?? '' ) === 'gradient' ) {
			$gradient = is_array( $b['gradient'] ?? null ) ? self::gradientCss( $b['gradient'] ) : '';

			return $gradient !== '' ? [ 'background-image' => $gradient ] : [];
		}
		$d = [];
		if ( ! empty( $b['color'] ) ) {
			$d['background-color'] = (string) $b['color'];
		}
		if ( ! empty( $b['image']['url'] ) ) {
			$d['background-image'] = 'url("' . str_replace( '"', '', (string) $b['image']['url'] ) . '")';
			foreach ( [ 'position', 'size', 'repeat', 'attachment' ] as $k ) {
				if ( ! empty( $b[ $k ] ) ) {
					$d[ 'background-' . $k ] = (string) $b[ $k ];
				}
			}
		}

		return $d;
	}


	/**
	 * Token-only CSS for the editor canvas and front end. Register block styles by stable slug.
	 *
	 * @param array<string,mixed> $v
	 */
	public static function tokenCss( array $v ): string {
		self::$units = is_array( $v['units'] ?? null ) ? $v['units'] : [];
		$devices    = SiteKitBreakpoints::enabled( (array) ( $v['breakpoints'] ?? [] ) );
		$css        = '';
		$colours    = '';
		foreach ( SiteKitTokens::colours( $v['custom_colours'] ?? [] ) as $token ) {
			$colours .= '--wp--preset--color--' . $token['slug'] . ':' . $token['color'] . ';';
		}
		if ( $colours !== '' ) {
			$css .= ':root{' . $colours . '}';
		}
		foreach ( SiteKitTokens::typography( $v['custom_typography'] ?? [] ) as $token ) {
			$slug   = $token['slug'];
			$styles = self::typography( $token['typography'], array_keys( $devices ) );
			$props  = [];
			foreach ( $styles as $device => $declarations ) {
				$vars = '';
				foreach ( $declarations as $property => $value ) {
					$var                = '--wpe-typography-' . $slug . '-' . $property;
					$vars              .= $var . ':' . $value . ';';
					$props[ $property ] = 'var(' . $var . ',inherit)';
					if ( $property === 'font-size' ) {
						$vars .= '--wp--preset--font-size--' . $slug . ':' . $value . ';';
					}
				}
				if ( $vars !== '' ) {
					$rule  = ':root{' . $vars . '}';
					$media = $devices[ $device ]['media'];
					$css  .= $media === '' ? $rule : $media . '{' . $rule . '}';
				}
			}
			if ( $props !== [] ) {
				$css .= '.is-style-' . $slug . '{';
				foreach ( $props as $property => $value ) {
					$css .= $property . ':' . $value . ';';
				}
				$css .= '}';
			}
		}

		return $css;
	}

	/** Responsive and composite settings, one media query per enabled breakpoint. @param array<string,mixed> $v */
	private static function controls( array $v ): string {
		$breakpoints = SiteKitBreakpoints::enabled( is_array( $v['breakpoints'] ?? null ) ? $v['breakpoints'] : [] );
		$rules       = array_fill_keys( array_keys( $breakpoints ), [] );
		$add         = static function ( string $device, string $selector, string $property, string $value ) use ( &$rules ): void {
			if ( ! isset( $rules[ $device ] ) ) {
				return;
			}
			foreach ( explode( '|', $property ) as $p ) {
				$rules[ $device ][ $selector ][ $p ] = $value;
			}
		};
		$presets = array_column( SiteKitTokens::typography( $v['custom_typography'] ?? [] ), 'typography', 'slug' );
		foreach ( self::TARGETS as $key => [ $selector, $property ] ) {
			if ( ! isset( $v[ $key ] ) || $v[ $key ] === '' || $v[ $key ] === [] || $v[ $key ] === false ) {
				continue;
			}
			$value = $v[ $key ];
			if ( str_starts_with( $key, 'typo_' ) && is_array( $value ) ) {
				$preset = is_string( $value['preset'] ?? null ) ? $value['preset'] : '';
				$value  = array_replace_recursive( $presets[ $preset ] ?? [], $value );
				foreach ( self::typography( $value, array_keys( $rules ) ) as $device => $declarations ) {
					foreach ( $declarations as $p => $x ) {
						$add( $device, $selector, $p, $x );
					}
				}
				continue;
			}
			if ( str_starts_with( $key, 'site_bg' ) && is_array( $value ) ) {
				foreach ( self::background( $value ) as $p => $x ) {
					$add( 'desktop', $selector, $p, $x );
				}
				continue;
			}
			$value = is_array( $value ) ? $value : [ 'desktop' => $value ];
			foreach ( array_keys( $rules ) as $device ) {
				if ( ! isset( $value[ $device ] ) ) {
					continue;
				}
				$x = is_array( $value[ $device ] ) && isset( $value[ $device ]['top'] ) ? self::dims( $value[ $device ] ) : self::size( $value[ $device ] );
				if ( $x !== '' ) {
					$add( $device, $selector, $property, $x );
				}
			}
		}
		$css = '';
		foreach ( $rules as $device => $selectors ) {
			$block = '';
			foreach ( $selectors as $selector => $declarations ) {
				$block .= $selector . '{';
				foreach ( $declarations as $p => $x ) {
					$block .= $p . ':' . $x . ';';
				}
				$block .= '}';
			}
			if ( $block !== '' ) {
				$media = $breakpoints[ $device ]['media'] ?? '';
				$css  .= $media !== '' ? $media . '{' . $block . '}' : $block;
			}
		}

		return $css;
	}
}
