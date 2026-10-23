<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Kit settings: groups and fields, ported from the hi-wpessential "Site Settings" panel and
 * made theme-agnostic. Every field defaults to "" (= keep the theme's own value).
 *
 * Field types: color, toggle, range (number + unit, optionally responsive), select, choose, font,
 * fonts, text, dimensions, typography, background, gradient, shadow.
 */
final class SiteKitSchema {

	public const OWNER_SURFACE_ID = 49;

	/** @var array<string,array{title:string,description:string,icon:string,fields:array<string,array<string,mixed>>}>|null */
	private static ?array $sections = null;

	/** Palette slugs the global colours map to (theme.json `color.palette` slugs). */
	public const COLOURS = [
		'primary',
		'primary-hover',
		'secondary',
		'accent',
		'ink',
		'text',
		'text-muted',
		'surface',
		'surface-alt',
		'border',
		'on-primary',
		'success',
		'warning',
		'danger',
		'primary-soft',
		'on-ink',
		'on-ink-muted',
		'pro',
		'star',
	];

	/** Breakpoints for responsive values (desktop first). */
	public const DEVICES = [
		'desktop' => '',
		'tablet'  => '@media (max-width: 1023px)',
		'mobile'  => '@media (max-width: 767px)',
	];

	/** @return array<string,array{title:string,description:string,icon:string,fields:array<string,array<string,mixed>>}> */
	public static function sections(): array {
		return self::$sections ??= self::build();
	}

	/** @return array<string,array<string,mixed>> Field key => field, across all groups. */
	public static function fields(): array {
		$all = [];
		foreach ( self::sections() as $id => $section ) {
			foreach ( $section['fields'] as $key => $field ) {
				if ( $field['type'] !== 'heading' ) {
					$all[ $key ] = $field + [ 'section' => $id ];
				}
			}
		}

		return $all;
	}

	/** @return array<string,mixed>|null */
	public static function field( string $key ): ?array {
		return self::fields()[ $key ] ?? null;
	}

	/**
	 * @return array<string,array{title:string,description:string,icon:string,fields:array<string,array<string,mixed>>}>
	 */
	private static function build(): array {
		$labels = [
			'primary'       => self::t( 'Primary (brand)' ),
			'primary-hover' => self::t( 'Primary on hover' ),
			'secondary'     => self::t( 'Secondary' ),
			'accent'        => self::t( 'Accent' ),
			'ink'           => self::t( 'Ink (dark sections, headings)' ),
			'text'          => self::t( 'Text' ),
			'text-muted'    => self::t( 'Muted text' ),
			'surface'       => self::t( 'Page background' ),
			'surface-alt'   => self::t( 'Alternate background' ),
			'border'        => self::t( 'Borders' ),
			'on-primary'    => self::t( 'Text on primary' ),
			'success'       => self::t( 'Success' ),
			'warning'       => self::t( 'Warning' ),
			'danger'        => self::t( 'Error' ),
			'primary-soft'  => self::t( 'Primary tint (soft backgrounds)' ),
			'on-ink'        => self::t( 'Text on ink' ),
			'on-ink-muted'  => self::t( 'Muted text on ink' ),
			'pro'           => self::t( 'Pro badge' ),
			'star'          => self::t( 'Rating stars' ),
		];
		$light  = [];
		$dark   = [];
		foreach ( self::COLOURS as $slug ) {
			$light[ 'color_' . $slug ] = self::colour( $labels[ $slug ] );
			$dark[ 'dark_' . $slug ]   = self::colour( $labels[ $slug ] );
		}

		$weights = [
			''    => self::t( 'Theme default' ),
			'400' => '400 · Regular',
			'500' => '500 · Medium',
			'600' => '600 · Semibold',
			'700' => '700 · Bold',
			'800' => '800 · Extra bold',
		];
		$case    = [
			''           => self::t( 'Theme default' ),
			'none'       => self::t( 'As typed' ),
			'uppercase'  => self::t( 'UPPERCASE' ),
			'capitalize' => self::t( 'Capitalize' ),
		];
		$onOff   = static fn ( string $on, string $off ): array => [
			''    => $on,
			'off' => $off,
		];

		$type = [
			'fonts_delivery'  => self::select(
				self::t( 'Google Fonts are served' ),
				[
					''       => self::t( 'From this site (downloaded on save, privacy-friendly)' ),
					'google' => self::t( 'From Google servers' ),
				]
			),
			'font_body'       => self::font( self::t( 'Body font' ) ),
			'font_heading'    => self::font( self::t( 'Heading font' ) ),
			'font_mono'       => self::font( self::t( 'Code font' ) ),
			'fonts_extra'     => [
				'type'  => 'fonts',
				'label' => self::t( 'More fonts for the editors' ),
				'help'  => self::t( 'Google Fonts listed here (separated by |) can be picked in any block and Elementor widget.' ),
			],
			'size_body'       => self::range( self::t( 'Body text size' ), 12, 22, 1, 'px', true ),
			'line_body'       => self::range( self::t( 'Body line height' ), 1, 2.2, 0.05, '' ),
			'body_weight'     => self::select(
				self::t( 'Body text weight' ),
				[
					''    => self::t( 'Theme default' ),
					'300' => '300 · Light',
					'400' => '400 · Regular',
					'500' => '500 · Medium',
					'600' => '600 · Semibold',
				]
			),
			'paragraph_space' => self::range( self::t( 'Space between paragraphs (em)' ), 0, 3, 0.05, 'em' ),
			'heading_weight'  => self::select( self::t( 'Heading weight' ), $weights ),
			'heading_spacing' => self::range( self::t( 'Heading letter spacing (em)' ), -0.08, 0.1, 0.005, 'em' ),
			'heading_line'    => self::range( self::t( 'Heading line height' ), 0.9, 1.6, 0.05, '' ),
			'heading_case'    => [
				'type'    => 'choose',
				'label'   => self::t( 'Heading letter case' ),
				'choices' => $case,
			],
			'heading_color'   => self::colour( self::t( 'Heading colour' ) ),
			'selection_bg'    => self::colour( self::t( 'Selected text background' ) ),
			'selection_text'  => self::colour( self::t( 'Selected text colour' ) ),
		];
		foreach ( [
			'display' => [ 'Display', 40, 64 ],
			'h1'      => [ 'H1', 34, 48 ],
			'h2'      => [ 'H2', 28, 38 ],
			'h3'      => [ 'H3', 22, 28 ],
			'h4'      => [ 'H4', 18, 20 ],
			'body-l'  => [ self::t( 'Large body text' ), 16, 18 ],
		] as $slug => [ $label ] ) {
			/* translators: %s: text style, e.g. H1. */
			$type[ 'size_' . $slug . '_min' ] = self::range( sprintf( self::t( '%s size on phones' ), $label ), 12, 120 );
			/* translators: %s: text style, e.g. H1. */
			$type[ 'size_' . $slug . '_max' ] = self::range( sprintf( self::t( '%s size on desktop' ), $label ), 12, 160 );
		}
		$type['size_h5']    = self::range( self::t( 'H5 size' ), 12, 40 );
		$type['size_h6']    = self::range( self::t( 'H6 size' ), 10, 32 );
		$type['size_small'] = self::range( self::t( 'Small text size' ), 10, 18 );

		$globalType = [];
		foreach ( [
			'typo_body'   => self::t( 'Body text' ),
			'typo_h1'     => 'H1',
			'typo_h2'     => 'H2',
			'typo_h3'     => 'H3',
			'typo_h4'     => 'H4',
			'typo_h5'     => 'H5',
			'typo_h6'     => 'H6',
			'typo_button' => self::t( 'Buttons' ),
			'typo_menu'   => self::t( 'Menu' ),
			'typo_small'  => self::t( 'Small text and captions' ),
		] as $key => $label ) {
			$globalType[ $key ] = [
				'type'  => 'typography',
				'label' => $label,
			];
		}

		return [
			'colors'    => self::section( self::t( 'Global colours' ), self::t( 'The site palette used by every block and Elementor widget. Leave a colour empty to keep the theme\'s.' ), 'brush', $light ),
			'dark'      => self::section(
				self::t( 'Dark mode' ),
				self::t( 'The colours visitors see in dark mode, and how dark mode works.' ),
				'moon',
				[
					'scheme_toggle' => self::select( self::t( 'Light / dark switch for visitors' ), $onOff( self::t( 'Show a floating switch' ), self::t( 'Hide, always use the default' ) ) ),
					'scheme_follow' => self::select(
						self::t( 'First visit' ),
						[
							''      => self::t( 'Follow the visitor\'s device' ),
							'fixed' => self::t( 'Always start light' ),
						]
					),
				] + $dark
			),
			'type'      => self::section( self::t( 'Typography' ), self::t( 'Pick theme fonts or any Google Font. Google Fonts are saved on your site unless you choose Google\'s servers. Heading sizes scale smoothly between the phone and the desktop value.' ), 'file', $type ),
			'gtype'     => self::section( self::t( 'Global typography' ), self::t( 'One typography set per text style: font, size per device, weight, case, style, decoration, line height and letter spacing.' ), 'layers', $globalType ),
			'bg'        => self::section(
				self::t( 'Site background' ),
				'',
				'image',
				[
					'site_bg'      => [
						'type'  => 'background',
						'label' => self::t( 'Background' ),
					],
					'site_bg_dark' => [
						'type'  => 'background',
						'label' => self::t( 'Background in dark mode' ),
					],
				]
			),
			'button'    => self::section(
				self::t( 'Buttons' ),
				'',
				'grid',
				[
					'btn_radius'       => self::range( self::t( 'Corner radius' ), 0, 60 ),
					'btn_padding'      => self::dimensions( self::t( 'Padding' ) ),
					'btn_size'         => self::range( self::t( 'Text size' ), 11, 22, 1, 'px', true ),
					'btn_weight'       => self::select( self::t( 'Text weight' ), $weights ),
					'btn_case'         => [
						'type'    => 'choose',
						'label'   => self::t( 'Letter case' ),
						'choices' => array_slice( $case, 0, 3, true ),
					],
					'btn_lift'         => self::range( self::t( 'Lift on hover' ), 0, 8 ),
					'btn_spacing'      => self::range( self::t( 'Letter spacing (em)' ), 0, 0.2, 0.01, 'em' ),
					'btn_border_w'     => self::range( self::t( 'Border width' ), 0, 6 ),
					'btn_shadow'       => self::select(
						self::t( 'Shadow' ),
						[
							''     => self::t( 'Theme default' ),
							'none' => self::t( 'None' ),
							'sm'   => self::t( 'Small' ),
							'md'   => self::t( 'Medium' ),
							'lg'   => self::t( 'Large' ),
						]
					),
					'btn_bg'           => [ 'tab' => 'normal' ] + self::colour( self::t( 'Background' ) ),
					'btn_text'         => [ 'tab' => 'normal' ] + self::colour( self::t( 'Text colour' ) ),
					'btn_border'       => [ 'tab' => 'normal' ] + self::colour( self::t( 'Border colour' ) ),
					'btn_hover_bg'     => [ 'tab' => 'hover' ] + self::colour( self::t( 'Background on hover' ) ),
					'btn_hover_text'   => [ 'tab' => 'hover' ] + self::colour( self::t( 'Text colour on hover' ) ),
					'btn_hover_border' => [ 'tab' => 'hover' ] + self::colour( self::t( 'Border colour on hover' ) ),
				]
			),
			'forms'     => self::section(
				self::t( 'Form fields' ),
				'',
				'form',
				[
					'field_radius'  => self::range( self::t( 'Corner radius' ), 0, 30 ),
					'field_height'  => self::range( self::t( 'Height' ), 32, 64, 1, 'px', true ),
					'field_padding' => self::dimensions( self::t( 'Padding' ) ),
					'field_border'  => self::colour( self::t( 'Border colour' ) ),
					'field_bg'      => self::colour( self::t( 'Background' ) ),
					'field_focus'   => self::colour( self::t( 'Focus ring colour' ) ),
				]
			),
			'shape'     => self::section(
				self::t( 'Corners & images' ),
				self::t( 'The radius scale cards, images and elements use.' ),
				'columns',
				[
					'radius_sm'   => self::range( self::t( 'Small radius' ), 0, 24 ),
					'radius_md'   => self::range( self::t( 'Medium radius' ), 0, 32 ),
					'radius_lg'   => self::range( self::t( 'Large radius' ), 0, 48 ),
					'radius_xl'   => self::range( self::t( 'Extra large radius' ), 0, 64 ),
					'image_zoom'  => self::range( self::t( 'Image zoom on hover' ), 1, 1.2, 0.01, '' ),
					'image_round' => self::toggle( self::t( 'Round the corners of images in content' ) ),
				]
			),
			'layout'    => self::section(
				self::t( 'Layout' ),
				'',
				'monitor',
				[
					'content_width' => self::range( self::t( 'Content width' ), 480, 1200 ),
					'wide_width'    => self::range( self::t( 'Wide width' ), 900, 1920 ),
					'section_space' => self::range( self::t( 'Space between sections' ), 24, 200, 1, 'px', true ),
					'gutter'        => self::range( self::t( 'Side space' ), 0, 120, 1, 'px', true ),
					'block_gap'     => self::range( self::t( 'Space between blocks' ), 0, 80 ),
					'header_height' => self::range( self::t( 'Header height' ), 48, 120, 1, 'px', true ),
					'sticky_header' => self::select( self::t( 'Sticky header' ), $onOff( self::t( 'Theme default' ), self::t( 'Off' ) ) ),
					'back_to_top'   => self::select(
						self::t( 'Back-to-top button' ),
						[
							''     => self::t( 'Theme default' ),
							'show' => self::t( 'Show a floating button' ),
						]
					),
				]
			),
			'gradients' => self::section(
				self::t( 'Gradients' ),
				self::t( 'Brand gradient (gradient buttons, accent words), Night (dark sections) and the image fade.' ),
				'brush',
				[
					'grad_brand_auto'  => self::toggle( self::t( 'Brand gradient follows Primary → Secondary' ) ),
					'grad_brand'       => [
						'type'  => 'gradient',
						'label' => self::t( 'Brand gradient' ),
					],
					'grad_night'       => [
						'type'  => 'gradient',
						'label' => self::t( 'Night gradient (dark sections)' ),
					],
					'grad_fade'        => self::colour( self::t( 'Image fade colour' ) ),
					'grad_fade_amount' => self::range( self::t( 'Image fade strength (%)' ), 0, 100, 5, '%' ),
				]
			),
			'links'     => self::section(
				self::t( 'Links' ),
				'',
				'link',
				[
					'link_color'     => self::colour( self::t( 'Link colour' ) ),
					'link_hover'     => self::colour( self::t( 'Link hover colour' ) ),
					'link_underline' => self::select(
						self::t( 'Underline' ),
						[
							''       => self::t( 'Theme default' ),
							'always' => self::t( 'Always' ),
							'never'  => self::t( 'Never' ),
							'text'   => self::t( 'Always inside text, on hover elsewhere' ),
						]
					),
					'link_thickness' => self::range( self::t( 'Underline thickness' ), 1, 4 ),
					'link_offset'    => self::range( self::t( 'Underline distance' ), 0, 10 ),
					'link_weight'    => self::select( self::t( 'Link weight' ), $weights ),
				]
			),
			'shadows'   => self::section(
				self::t( 'Shadows' ),
				self::t( 'The small, medium and large shadows cards and elements use.' ),
				'layers',
				[
					'shadow_color'    => self::colour( self::t( 'Shadow colour' ) ),
					'shadow_strength' => self::range( self::t( 'Shadow strength (%)' ), 0, 300, 10, '%' ),
					'shadow_sm'       => [
						'type'  => 'shadow',
						'label' => self::t( 'Small shadow' ),
					],
					'shadow_md'       => [
						'type'  => 'shadow',
						'label' => self::t( 'Medium shadow' ),
					],
					'shadow_lg'       => [
						'type'  => 'shadow',
						'label' => self::t( 'Large shadow' ),
					],
				]
			),
			'header'    => self::section(
				self::t( 'Header & footer' ),
				self::t( 'Applies to block-theme header and footer template parts and common theme header/footer markup.' ),
				'menu',
				[
					'h_header'          => self::heading( self::t( 'Header' ) ),
					'header_bg'         => self::colour( self::t( 'Header background' ) ),
					'header_text'       => self::colour( self::t( 'Header text and menu' ) ),
					'header_border'     => self::colour( self::t( 'Header bottom line' ) ),
					'header_glass'      => self::toggle( self::t( 'Glass header (see-through with blur)' ) ),
					'menu_size'         => self::range( self::t( 'Menu text size' ), 11, 22, 1, 'px', true ),
					'menu_weight'       => self::select( self::t( 'Menu text weight' ), $weights ),
					'menu_gap'          => self::range( self::t( 'Space between menu items' ), 0, 64, 1, 'px', true ),
					'h_footer'          => self::heading( self::t( 'Footer' ) ),
					'footer_bg'         => self::colour( self::t( 'Footer background' ) ),
					'footer_text'       => self::colour( self::t( 'Footer text' ) ),
					'footer_heading'    => self::colour( self::t( 'Footer headings' ) ),
					'footer_link'       => self::colour( self::t( 'Footer links' ) ),
					'footer_link_hover' => self::colour( self::t( 'Footer links on hover' ) ),
				]
			),
			'a11y'      => self::section(
				self::t( 'Accessibility' ),
				self::t( 'Keyboard focus and readability for every visitor.' ),
				'users',
				[
					'focus_color' => self::colour( self::t( 'Keyboard focus ring colour' ) ),
					'focus_width' => self::range( self::t( 'Focus ring width' ), 1, 6 ),
					'skip_link'   => self::select(
						self::t( '"Skip to content" link' ),
						[
							''     => self::t( 'Show on keyboard focus (recommended)' ),
							'show' => self::t( 'Always visible' ),
						]
					),
					'min_text'    => self::range( self::t( 'Smallest text size allowed' ), 10, 16 ),
				]
			),
			'motion'    => self::section(
				self::t( 'Hover & motion' ),
				self::t( 'Visitors who ask their device for less motion always get less motion.' ),
				'spark',
				[
					'hover_duration' => self::range( self::t( 'Hover speed (ms)' ), 0, 800, 10, 'ms' ),
					'hover_ease'     => self::select(
						self::t( 'Hover easing' ),
						[
							''                             => self::t( 'Theme default' ),
							'ease'                         => self::t( 'Ease' ),
							'linear'                       => self::t( 'Linear' ),
							'cubic-bezier(.2,.8,.2,1)'     => self::t( 'Smooth out' ),
							'cubic-bezier(.34,1.56,.64,1)' => self::t( 'Springy' ),
						]
					),
					'hover_lift'     => self::range( self::t( 'Card lift on hover' ), 0, 16 ),
					'motion_off'     => self::toggle( self::t( 'Turn off all animations' ) ),
				]
			),
			'seo'       => self::section(
				self::t( 'SEO basics' ),
				self::t( 'Meta description, Open Graph tags and schema.org data. Skipped automatically when Yoast, Rank Math, SEOPress, AIOSEO or The SEO Framework is active.' ),
				'search',
				[
					'seo_tags' => self::select(
						self::t( 'SEO tags' ),
						[
							''   => self::t( 'Off (the theme or an SEO plugin handles them)' ),
							'on' => self::t( 'Output description, Open Graph, Twitter card and schema.org' ),
						]
					),
				]
			),
		];
	}

	/**
	 * @param array<string,array<string,mixed>> $fields
	 * @return array{title:string,description:string,icon:string,fields:array<string,array<string,mixed>>}
	 */
	private static function section( string $title, string $description, string $icon, array $fields ): array {
		return [
			'title'       => $title,
			'description' => $description,
			'icon'        => $icon,
			'fields'      => $fields,
		];
	}

	/** Translates when WordPress is loaded; the schema is also read by unit tests and tooling. */
	private static function t( string $text ): string {
		return function_exists( '__' ) ? __( $text, 'wpessential' ) : $text; // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- strings are literal at every call site.
	}

	/** @return array<string,mixed> */
	private static function colour( string $label ): array {
		return [
			'type'  => 'color',
			'label' => $label,
		];
	}

	/** @return array<string,mixed> */
	private static function toggle( string $label ): array {
		return [
			'type'  => 'toggle',
			'label' => $label,
		];
	}

	/** @return array<string,mixed> */
	private static function heading( string $label ): array {
		return [
			'type'  => 'heading',
			'label' => $label,
		];
	}

	/** @return array<string,mixed> */
	private static function font( string $label ): array {
		return [
			'type'  => 'font',
			'label' => $label,
		];
	}

	/**
	 * @param array<int|string,string> $choices
	 * @return array<string,mixed>
	 */
	private static function select( string $label, array $choices ): array {
		return [
			'type'    => 'select',
			'label'   => $label,
			'choices' => $choices,
		];
	}

	/** @return array<string,mixed> */
	private static function range( string $label, float $min, float $max, float $step = 1, string $unit = 'px', bool $responsive = false ): array {
		return [
			'type'       => 'range',
			'label'      => $label,
			'min'        => $min,
			'max'        => $max,
			'step'       => $step,
			'unit'       => $unit,
			'responsive' => $responsive,
		];
	}

	/** @return array<string,mixed> */
	private static function dimensions( string $label ): array {
		return [
			'type'       => 'dimensions',
			'label'      => $label,
			'responsive' => true,
			'units'      => [ 'px', 'em', 'rem' ],
		];
	}
}
