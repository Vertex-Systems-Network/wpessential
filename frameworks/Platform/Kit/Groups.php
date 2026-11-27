<?php
/**
 * Group options for kit elements: typography, spacing, border, shadow, background and
 * normal/hover colours, plus the common "Advanced" options every block gets (Elementor already
 * adds its own Advanced tab to every widget).
 *
 * An element lists its groups in element.json:
 *   "groups": { "titleTypography": { "type": "typography", "label": "Title", "selector": ".wpe-heading__title", "panel": "style" } }
 * The block stores one object per group; Elementor gets the matching native group control with
 * the same selector, so both builders style the same part.
 *
 * @package WPEssential
 */

namespace WPEssential\Platform\Kit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Group definitions and their CSS.
 */
class Groups {

	/**
	 * Fields of each group type. "prop" is the CSS property; "state" => hover writes :hover.
	 *
	 * @return array
	 */
	public static function fields() {
		$fonts   = array( '' => __( 'Default', 'wpessential' ) ) + self::fonts();
		$presets = array( '' => __( 'Default / custom', 'wpessential' ) ) + array_column( Kit::typography_presets(), 'name', 'slug' );
		$px    = array(
			'type' => 'range',
			'unit' => 'px',
			'min'  => 0,
			'max'  => 200,
		);
		$side  = static fn( $prop, $label ) => array_merge(
			$px,
			array(
				'label'      => $label,
				'prop'       => $prop,
				'responsive' => true,
				'min'        => -200,
			)
		);
		return array(
			'typography'  => array(
				'preset'        => array(
					'type'    => 'select',
					'label'   => __( 'Global typography preset', 'wpessential' ),
					'prop'    => '',
					'options' => $presets,
				),
				'family'        => array(
					'type'    => 'select',
					'label'   => __( 'Font', 'wpessential' ),
					'prop'    => 'font-family',
					'options' => $fonts,
					'font'    => true,
				),
				'size'          => array_merge(
					$px,
					array(
						'label'      => __( 'Size', 'wpessential' ),
						'prop'       => 'font-size',
						'responsive' => true,
						'max'        => 200,
					)
				),
				'sizeMin'       => array_merge(
					$px,
					array(
						'label' => __( 'Fluid size: at 360px', 'wpessential' ),
						'prop'  => 'font-size',
						'fluid' => 'min',
						'max'   => 200,
					)
				),
				'sizeMax'       => array_merge(
					$px,
					array(
						'label' => __( 'Fluid size: at 1440px', 'wpessential' ),
						'prop'  => 'font-size',
						'fluid' => 'max',
						'max'   => 240,
					)
				),
				'weight'        => array(
					'type'    => 'select',
					'label'   => __( 'Weight', 'wpessential' ),
					'prop'    => 'font-weight',
					'options' => array(
						''    => __( 'Default', 'wpessential' ),
						'100' => '100',
						'200' => '200',
						'300' => '300',
						'400' => '400',
						'500' => '500',
						'600' => '600',
						'700' => '700',
						'800' => '800',
						'900' => '900',
					),
				),
				'transform'     => array(
					'type'    => 'select',
					'label'   => __( 'Transform', 'wpessential' ),
					'prop'    => 'text-transform',
					'options' => array(
						''           => __( 'Default', 'wpessential' ),
						'uppercase'  => 'UPPERCASE',
						'lowercase'  => 'lowercase',
						'capitalize' => 'Capitalize',
						'none'       => __( 'Normal', 'wpessential' ),
					),
				),
				'style'         => array(
					'type'    => 'select',
					'label'   => __( 'Style', 'wpessential' ),
					'prop'    => 'font-style',
					'options' => array(
						''       => __( 'Default', 'wpessential' ),
						'normal' => __( 'Normal', 'wpessential' ),
						'italic' => __( 'Italic', 'wpessential' ),
					),
				),
				'decoration'    => array(
					'type'    => 'select',
					'label'   => __( 'Decoration', 'wpessential' ),
					'prop'    => 'text-decoration',
					'options' => array(
						''             => __( 'Default', 'wpessential' ),
						'none'         => __( 'None', 'wpessential' ),
						'underline'    => __( 'Underline', 'wpessential' ),
						'line-through' => __( 'Line through', 'wpessential' ),
					),
				),
				'lineHeight'    => array(
					'type'       => 'range',
					'label'      => __( 'Line height', 'wpessential' ),
					'prop'       => 'line-height',
					'unit'       => '',
					'min'        => 0.5,
					'max'        => 3,
					'step'       => 0.05,
					'responsive' => true,
				),
				'letterSpacing' => array(
					'type'       => 'range',
					'label'      => __( 'Letter spacing', 'wpessential' ),
					'prop'       => 'letter-spacing',
					'unit'       => 'px',
					'min'        => -5,
					'max'        => 20,
					'step'       => 0.1,
					'responsive' => true,
				),
				'wordSpacing'   => array(
					'type'       => 'range',
					'label'      => __( 'Word spacing', 'wpessential' ),
					'prop'       => 'word-spacing',
					'unit'       => 'px',
					'min'        => -20,
					'max'        => 50,
					'responsive' => true,
				),
			),
			'spacing'     => array(
				'top'    => $side( '{prop}-top', __( 'Top', 'wpessential' ) ),
				'right'  => $side( '{prop}-right', __( 'Right', 'wpessential' ) ),
				'bottom' => $side( '{prop}-bottom', __( 'Bottom', 'wpessential' ) ),
				'left'   => $side( '{prop}-left', __( 'Left', 'wpessential' ) ),
			),
			'border'      => array(
				'style'      => array(
					'type'    => 'select',
					'label'   => __( 'Border', 'wpessential' ),
					'prop'    => 'border-style',
					'options' => array(
						''       => __( 'Default', 'wpessential' ),
						'none'   => __( 'None', 'wpessential' ),
						'solid'  => __( 'Solid', 'wpessential' ),
						'dashed' => __( 'Dashed', 'wpessential' ),
						'dotted' => __( 'Dotted', 'wpessential' ),
						'double' => __( 'Double', 'wpessential' ),
					),
				),
				'width'      => array_merge(
					$px,
					array(
						'label'      => __( 'Width', 'wpessential' ),
						'prop'       => 'border-width',
						'responsive' => true,
						'max'        => 20,
					)
				),
				'color'      => array(
					'type'  => 'color',
					'label' => __( 'Colour', 'wpessential' ),
					'prop'  => 'border-color',
				),
				'hoverColor' => array(
					'type'  => 'color',
					'label' => __( 'Hover colour', 'wpessential' ),
					'prop'  => 'border-color',
					'state' => 'hover',
				),
				'darkColor'  => array(
					'type'   => 'color',
					'label'  => __( 'Colour in dark mode', 'wpessential' ),
					'prop'   => 'border-color',
					'scheme' => 'dark',
				),
				'radius'     => array_merge(
					$px,
					array(
						'label'      => __( 'Radius', 'wpessential' ),
						'prop'       => 'border-radius',
						'responsive' => true,
						'max'        => 999,
					)
				),
			),
			'shadow'      => array(
				'preset'      => array(
					'type'    => 'select',
					'label'   => __( 'Shadow', 'wpessential' ),
					'prop'    => 'box-shadow',
					'options' => self::shadow_labels(),
				),
				'hoverPreset' => array(
					'type'    => 'select',
					'label'   => __( 'Hover shadow', 'wpessential' ),
					'prop'    => 'box-shadow',
					'state'   => 'hover',
					'options' => self::shadow_labels(),
				),
				'darkPreset'  => array(
					'type'    => 'select',
					'label'   => __( 'Shadow in dark mode', 'wpessential' ),
					'prop'    => 'box-shadow',
					'scheme'  => 'dark',
					'options' => self::shadow_labels(),
				),
				'custom'      => array(
					'type'  => 'text',
					'label' => __( 'Custom shadow (CSS)', 'wpessential' ),
					'prop'  => 'box-shadow',
				),
			),
			'background'  => array(
				'color'      => array(
					'type'  => 'color',
					'label' => __( 'Colour', 'wpessential' ),
					'prop'  => 'background-color',
				),
				'hoverColor' => array(
					'type'  => 'color',
					'label' => __( 'Hover colour', 'wpessential' ),
					'prop'  => 'background-color',
					'state' => 'hover',
				),
				'darkColor'  => array(
					'type'   => 'color',
					'label'  => __( 'Colour in dark mode', 'wpessential' ),
					'prop'   => 'background-color',
					'scheme' => 'dark',
				),
				'darkImage'  => array(
					'type'   => 'url',
					'label'  => __( 'Image URL in dark mode', 'wpessential' ),
					'prop'   => 'background-image',
					'image'  => true,
					'scheme' => 'dark',
				),
				'gradient'   => array(
					'type'    => 'select',
					'label'   => __( 'Gradient', 'wpessential' ),
					'prop'    => 'background-image',
					'options' => self::gradient_labels(),
				),
				'image'      => array(
					'type'  => 'url',
					'label' => __( 'Image URL', 'wpessential' ),
					'prop'  => 'background-image',
					'image' => true,
				),
				'size'       => array(
					'type'       => 'select',
					'label'      => __( 'Image size', 'wpessential' ),
					'prop'       => 'background-size',
					'responsive' => true,
					'options'    => array(
						''        => __( 'Default', 'wpessential' ),
						'cover'   => __( 'Cover', 'wpessential' ),
						'contain' => __( 'Contain', 'wpessential' ),
						'auto'    => __( 'Auto', 'wpessential' ),
					),
				),
				'position'   => array(
					'type'       => 'select',
					'label'      => __( 'Image position', 'wpessential' ),
					'prop'       => 'background-position',
					'responsive' => true,
					'options'    => array(
						''              => __( 'Default', 'wpessential' ),
						'center center' => __( 'Center', 'wpessential' ),
						'center top'    => __( 'Top', 'wpessential' ),
						'center bottom' => __( 'Bottom', 'wpessential' ),
						'left center'   => __( 'Left', 'wpessential' ),
						'right center'  => __( 'Right', 'wpessential' ),
					),
				),
				'repeat'     => array(
					'type'    => 'select',
					'label'   => __( 'Repeat', 'wpessential' ),
					'prop'    => 'background-repeat',
					'options' => array(
						''          => __( 'Default', 'wpessential' ),
						'no-repeat' => __( 'No repeat', 'wpessential' ),
						'repeat'    => __( 'Repeat', 'wpessential' ),
					),
				),
				'attachment' => array(
					'type'    => 'select',
					'label'   => __( 'Attachment', 'wpessential' ),
					'prop'    => 'background-attachment',
					'options' => array(
						''       => __( 'Default', 'wpessential' ),
						'scroll' => __( 'Scroll', 'wpessential' ),
						'fixed'  => __( 'Fixed (parallax)', 'wpessential' ),
					),
				),
			),
			'colors'      => array(
				'normal'    => array(
					'type'  => 'color',
					'label' => __( 'Colour', 'wpessential' ),
					'prop'  => '{prop}',
				),
				'hover'     => array(
					'type'  => 'color',
					'label' => __( 'Hover colour', 'wpessential' ),
					'prop'  => '{prop}',
					'state' => 'hover',
				),
				'focus'     => array(
					'type'  => 'color',
					'label' => __( 'Keyboard focus colour', 'wpessential' ),
					'prop'  => '{prop}',
					'state' => 'focus',
				),
				'active'    => array(
					'type'  => 'color',
					'label' => __( 'Pressed colour', 'wpessential' ),
					'prop'  => '{prop}',
					'state' => 'active',
				),
				'dark'      => array(
					'type'   => 'color',
					'label'  => __( 'Colour in dark mode', 'wpessential' ),
					'prop'   => '{prop}',
					'scheme' => 'dark',
				),
				'darkHover' => array(
					'type'   => 'color',
					'label'  => __( 'Hover colour in dark mode', 'wpessential' ),
					'prop'   => '{prop}',
					'scheme' => 'dark',
					'state'  => 'hover',
				),
			),
			'textEffects' => array(
				'shadow'      => array(
					'type'  => 'text',
					'label' => __( 'Text shadow (e.g. 0 2px 8px rgb(0 0 0 / .3))', 'wpessential' ),
					'prop'  => 'text-shadow',
				),
				'hoverShadow' => array(
					'type'  => 'text',
					'label' => __( 'Text shadow on hover', 'wpessential' ),
					'prop'  => 'text-shadow',
					'state' => 'hover',
				),
				'strokeWidth' => array(
					'type'  => 'range',
					'label' => __( 'Text stroke width', 'wpessential' ),
					'prop'  => '-webkit-text-stroke-width',
					'unit'  => 'px',
					'min'   => 0,
					'max'   => 10,
					'step'  => 0.5,
				),
				'strokeColor' => array(
					'type'  => 'color',
					'label' => __( 'Text stroke colour', 'wpessential' ),
					'prop'  => '-webkit-text-stroke-color',
				),
				'blend'       => array(
					'type'    => 'select',
					'label'   => __( 'Blend mode', 'wpessential' ),
					'prop'    => 'mix-blend-mode',
					'options' => array(
						''            => __( 'Normal', 'wpessential' ),
						'multiply'    => 'Multiply',
						'screen'      => 'Screen',
						'overlay'     => 'Overlay',
						'darken'      => 'Darken',
						'lighten'     => 'Lighten',
						'color-dodge' => 'Color dodge',
						'difference'  => 'Difference',
						'exclusion'   => 'Exclusion',
						'hue'         => 'Hue',
						'luminosity'  => 'Luminosity',
					),
				),
			),
			'filters'     => array(
				'blur'            => array(
					'type'  => 'range',
					'label' => __( 'Blur', 'wpessential' ),
					'fn'    => 'blur',
					'unit'  => 'px',
					'min'   => 0,
					'max'   => 20,
				),
				'brightness'      => array(
					'type'  => 'range',
					'label' => __( 'Brightness', 'wpessential' ),
					'fn'    => 'brightness',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 200,
				),
				'contrast'        => array(
					'type'  => 'range',
					'label' => __( 'Contrast', 'wpessential' ),
					'fn'    => 'contrast',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 200,
				),
				'saturate'        => array(
					'type'  => 'range',
					'label' => __( 'Saturation', 'wpessential' ),
					'fn'    => 'saturate',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 200,
				),
				'hue'             => array(
					'type'  => 'range',
					'label' => __( 'Hue rotate', 'wpessential' ),
					'fn'    => 'hue-rotate',
					'unit'  => 'deg',
					'min'   => 0,
					'max'   => 360,
				),
				'grey'            => array(
					'type'  => 'range',
					'label' => __( 'Greyscale', 'wpessential' ),
					'fn'    => 'grayscale',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 100,
				),
				'hoverBlur'       => array(
					'type'  => 'range',
					'label' => __( 'Blur on hover', 'wpessential' ),
					'fn'    => 'blur',
					'unit'  => 'px',
					'min'   => 0,
					'max'   => 20,
					'state' => 'hover',
				),
				'hoverBrightness' => array(
					'type'  => 'range',
					'label' => __( 'Brightness on hover', 'wpessential' ),
					'fn'    => 'brightness',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 200,
					'state' => 'hover',
				),
				'hoverContrast'   => array(
					'type'  => 'range',
					'label' => __( 'Contrast on hover', 'wpessential' ),
					'fn'    => 'contrast',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 200,
					'state' => 'hover',
				),
				'hoverSaturate'   => array(
					'type'  => 'range',
					'label' => __( 'Saturation on hover', 'wpessential' ),
					'fn'    => 'saturate',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 200,
					'state' => 'hover',
				),
				'hoverHue'        => array(
					'type'  => 'range',
					'label' => __( 'Hue rotate on hover', 'wpessential' ),
					'fn'    => 'hue-rotate',
					'unit'  => 'deg',
					'min'   => 0,
					'max'   => 360,
					'state' => 'hover',
				),
				'hoverGrey'       => array(
					'type'  => 'range',
					'label' => __( 'Greyscale on hover', 'wpessential' ),
					'fn'    => 'grayscale',
					'unit'  => '%',
					'min'   => 0,
					'max'   => 100,
					'state' => 'hover',
				),
				'opacity'         => array(
					'type'  => 'range',
					'label' => __( 'Opacity (%)', 'wpessential' ),
					'prop'  => 'opacity',
					'pct'   => true,
					'min'   => 0,
					'max'   => 100,
				),
				'hoverOpacity'    => array(
					'type'  => 'range',
					'label' => __( 'Opacity on hover (%)', 'wpessential' ),
					'prop'  => 'opacity',
					'pct'   => true,
					'min'   => 0,
					'max'   => 100,
					'state' => 'hover',
				),
			),
		);
	}

	/**
	 * Shadow presets (theme tokens where the theme has them).
	 *
	 * @return array
	 */
	public static function shadows() {
		$out = array(
			'none'  => 'none',
			'sm'    => '0 1px 2px rgba(11,26,61,.06), 0 1px 3px rgba(11,26,61,.08)',
			'md'    => '0 4px 12px rgba(11,26,61,.08), 0 12px 32px rgba(11,26,61,.08)',
			'lg'    => '0 18px 40px rgba(11,26,61,.14)',
			'xl'    => '0 30px 70px rgba(11,26,61,.22)',
			'glow'  => '0 0 0 4px color-mix(in srgb, var(--wp--preset--color--primary) 25%, transparent), 0 12px 32px color-mix(in srgb, var(--wp--preset--color--primary) 40%, transparent)',
			'inner' => 'inset 0 2px 6px rgba(11,26,61,.12)',
		);
		foreach ( self::custom_shadow_labels() as $slug => $label ) {
			$out[ $slug ] = 'var(--wp--preset--shadow--' . $slug . ',none)';
		}
		return $out;
	}

	/**
	 * Labels for the shadow select.
	 *
	 * @return array
	 */
	private static function shadow_labels() {
		return array(
			''      => __( 'Default', 'wpessential' ),
			'none'  => __( 'None', 'wpessential' ),
			'sm'    => __( 'Small', 'wpessential' ),
			'md'    => __( 'Medium', 'wpessential' ),
			'lg'    => __( 'Large', 'wpessential' ),
			'xl'    => __( 'Extra large', 'wpessential' ),
			'glow'  => __( 'Brand glow', 'wpessential' ),
			'inner' => __( 'Inner', 'wpessential' ),
		) + self::custom_shadow_labels();
	}

	/** @return array<string,string> Named global shadows supplied by Site Tools Kit. */
	private static function custom_shadow_labels(): array {
		$presets = function_exists( 'apply_filters' ) ? (array) apply_filters( 'wpessential_kit_shadow_presets', array() ) : array();
		return array_filter( $presets, static fn( $slug ): bool => is_string( $slug ) && preg_match( '/^wpe-custom-[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug ) === 1, ARRAY_FILTER_USE_KEY );
	}

	/**
	 * Gradient presets from the palette (they follow light/dark).
	 *
	 * @return array
	 */
	public static function gradients() {
		$c = static fn( $s ) => 'var(--wp--preset--color--' . $s . ')';
		return array(
			'brand'   => 'linear-gradient(110deg, #e8701a, #f7a23e)',
			'primary' => 'linear-gradient(135deg, ' . $c( 'primary' ) . ', ' . $c( 'secondary' ) . ')',
			'ink'     => 'linear-gradient(160deg, ' . $c( 'ink' ) . ', color-mix(in srgb, ' . $c( 'ink' ) . ' 70%, ' . $c( 'secondary' ) . '))',
			'soft'    => 'linear-gradient(180deg, ' . $c( 'surface' ) . ', ' . $c( 'surface-alt' ) . ')',
			'fade'    => 'linear-gradient(180deg, transparent, rgba(0,0,0,.6))',
		);
	}

	/**
	 * Labels for the gradient select.
	 *
	 * @return array
	 */
	private static function gradient_labels() {
		return array(
			''        => __( 'None', 'wpessential' ),
			'brand'   => __( 'Brand orange', 'wpessential' ),
			'primary' => __( 'Primary → secondary', 'wpessential' ),
			'ink'     => __( 'Ink', 'wpessential' ),
			'soft'    => __( 'Soft surface', 'wpessential' ),
			'fade'    => __( 'Image fade', 'wpessential' ),
		);
	}

	/**
	 * Theme font families: slug => name.
	 *
	 * @return array
	 */
	public static function fonts() {
		$out = array();
		if ( function_exists( 'wp_get_global_settings' ) ) {
			foreach ( (array) wp_get_global_settings( array( 'typography', 'fontFamilies', 'theme' ) ) as $f ) {
				if ( ! empty( $f['slug'] ) ) {
					$out[ $f['slug'] ] = $f['name'] ?? $f['slug'];
				}
			}
		}
		return $out;
	}

	/**
	 * Common options of every block, in the "Advanced" panel (Elementor has its own).
	 *
	 * @return array array( attributes, groups )
	 */
	public static function common() {
		$px   = array(
			'type'       => 'range',
			'unit'       => 'px',
			'min'        => -500,
			'max'        => 2000,
			'responsive' => true,
			'panel'      => 'advanced',
			'common'     => true,
		);
		$attr = array(
			'customCss'           => array(
				'type'  => 'textarea',
				'panel' => 'advanced',
				'label' => __( 'Custom CSS (write "selector" for this element)', 'wpessential' ),
				'code'  => true,
			),
			'cssClass'            => array(
				'type'   => 'text',
				'label'  => __( 'CSS classes', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'attributes'          => array(
				'type'   => 'textarea',
				'label'  => __( 'Custom attributes (key|value, one per line)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'width'               => array(
				'type'       => 'select',
				'label'      => __( 'Width', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'responsive' => true,
				'css'        => 'width',
				'raw'        => true,
				'options'    => array(
					''            => __( 'Default', 'wpessential' ),
					'100%'        => __( 'Full width', 'wpessential' ),
					'auto'        => __( 'Auto', 'wpessential' ),
					'fit-content' => __( 'Fit content', 'wpessential' ),
				),
			),
			'maxWidth'            => array_merge(
				$px,
				array(
					'label' => __( 'Max width', 'wpessential' ),
					'css'   => 'max-width',
					'raw'   => true,
					'min'   => 0,
				)
			),
			'alignSelf'           => array(
				'type'       => 'select',
				'label'      => __( 'Align self (in flex/grid)', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'responsive' => true,
				'css'        => 'align-self',
				'raw'        => true,
				'options'    => array(
					''           => __( 'Default', 'wpessential' ),
					'flex-start' => __( 'Start', 'wpessential' ),
					'center'     => __( 'Center', 'wpessential' ),
					'flex-end'   => __( 'End', 'wpessential' ),
					'stretch'    => __( 'Stretch', 'wpessential' ),
				),
			),
			'order'               => array(
				'type'       => 'range',
				'label'      => __( 'Order', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'responsive' => true,
				'css'        => 'order',
				'raw'        => true,
				'unit'       => '',
				'min'        => -10,
				'max'        => 10,
			),
			'position'            => array(
				'type'       => 'select',
				'label'      => __( 'Position', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'responsive' => true,
				'css'        => 'position',
				'raw'        => true,
				'options'    => array(
					''         => __( 'Default', 'wpessential' ),
					'relative' => __( 'Relative', 'wpessential' ),
					'absolute' => __( 'Absolute', 'wpessential' ),
					'fixed'    => __( 'Fixed', 'wpessential' ),
					'sticky'   => __( 'Sticky', 'wpessential' ),
				),
			),
			'top'                 => array_merge(
				$px,
				array(
					'label' => __( 'Offset top', 'wpessential' ),
					'css'   => 'top',
					'raw'   => true,
				)
			),
			'right'               => array_merge(
				$px,
				array(
					'label' => __( 'Offset right', 'wpessential' ),
					'css'   => 'right',
					'raw'   => true,
				)
			),
			'bottom'              => array_merge(
				$px,
				array(
					'label' => __( 'Offset bottom', 'wpessential' ),
					'css'   => 'bottom',
					'raw'   => true,
				)
			),
			'left'                => array_merge(
				$px,
				array(
					'label' => __( 'Offset left', 'wpessential' ),
					'css'   => 'left',
					'raw'   => true,
				)
			),
			// Logical inline spacing respects direction: start is left in LTR, right in RTL.
			'logicalMarginStart'  => array_merge(
				$px,
				array(
					'label'     => __( 'Margin inline start (RTL-aware)', 'wpessential' ),
					'css'       => 'margin-inline-start',
					'raw'       => true,
					'important' => true,
				)
			),
			'logicalMarginEnd'    => array_merge(
				$px,
				array(
					'label'     => __( 'Margin inline end (RTL-aware)', 'wpessential' ),
					'css'       => 'margin-inline-end',
					'raw'       => true,
					'important' => true,
				)
			),
			'logicalPaddingStart' => array_merge(
				$px,
				array(
					'label'     => __( 'Padding inline start (RTL-aware)', 'wpessential' ),
					'css'       => 'padding-inline-start',
					'raw'       => true,
					'important' => true,
					'min'       => 0,
				)
			),
			'logicalPaddingEnd'   => array_merge(
				$px,
				array(
					'label'     => __( 'Padding inline end (RTL-aware)', 'wpessential' ),
					'css'       => 'padding-inline-end',
					'raw'       => true,
					'important' => true,
					'min'       => 0,
				)
			),
			'zIndex'              => array(
				'type'   => 'range',
				'label'  => __( 'Z-index', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => 'z-index',
				'raw'    => true,
				'unit'   => '',
				'min'    => -10,
				'max'    => 999,
			),
			'opacity'             => array(
				'type'   => 'range',
				'label'  => __( 'Opacity', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => 'opacity',
				'raw'    => true,
				'unit'   => '',
				'min'    => 0,
				'max'    => 1,
				'step'   => 0.05,
			),
			'hoverOpacity'        => array(
				'type'   => 'range',
				'label'  => __( 'Hover opacity', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => 'opacity',
				'raw'    => true,
				'state'  => 'hover',
				'unit'   => '',
				'min'    => 0,
				'max'    => 1,
				'step'   => 0.05,
			),
			'rotate'              => array(
				'type'   => 'range',
				'label'  => __( 'Rotate', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-rot',
				'unit'   => 'deg',
				'min'    => -180,
				'max'    => 180,
			),
			'scale'               => array(
				'type'   => 'range',
				'label'  => __( 'Scale', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-sc',
				'unit'   => '',
				'min'    => 0,
				'max'    => 3,
				'step'   => 0.05,
			),
			'moveX'               => array(
				'type'   => 'range',
				'label'  => __( 'Move X', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-tx',
				'unit'   => 'px',
				'min'    => -300,
				'max'    => 300,
			),
			'moveY'               => array(
				'type'   => 'range',
				'label'  => __( 'Move Y', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-ty',
				'unit'   => 'px',
				'min'    => -300,
				'max'    => 300,
			),
			'hoverRotate'         => array(
				'type'   => 'range',
				'label'  => __( 'Hover rotate', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-h-rot',
				'unit'   => 'deg',
				'min'    => -180,
				'max'    => 180,
			),
			'hoverScale'          => array(
				'type'   => 'range',
				'label'  => __( 'Hover scale', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-h-sc',
				'unit'   => '',
				'min'    => 0,
				'max'    => 3,
				'step'   => 0.05,
			),
			'hoverMoveY'          => array(
				'type'   => 'range',
				'label'  => __( 'Hover move Y', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-h-ty',
				'unit'   => 'px',
				'min'    => -100,
				'max'    => 100,
			),
			'transition'          => array(
				'type'   => 'range',
				'label'  => __( 'Transition (ms)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-dur',
				'unit'   => 'ms',
				'min'    => 0,
				'max'    => 3000,
				'step'   => 50,
			),
			'transitionEase'      => array(
				'type'    => 'select',
				'label'   => __( 'Transition easing', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'css'     => '--wpek-ease',
				'raw'     => true,
				'options' => array(
					''                             => __( 'Theme default', 'wpessential' ),
					'ease'                         => 'Ease',
					'linear'                       => __( 'Linear', 'wpessential' ),
					'cubic-bezier(.2,.8,.2,1)'     => __( 'Smooth out', 'wpessential' ),
					'cubic-bezier(.65,0,.35,1)'    => __( 'In-out', 'wpessential' ),
					'cubic-bezier(.34,1.56,.64,1)' => __( 'Spring', 'wpessential' ),
				),
			),
			'transitionDelay'     => array(
				'type'   => 'range',
				'label'  => __( 'Transition delay (ms)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-tdelay',
				'unit'   => 'ms',
				'min'    => 0,
				'max'    => 2000,
				'step'   => 50,
			),
			'hoverPreset'         => array(
				'type'    => 'select',
				'label'   => __( 'Hover preset (from the demo)', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''       => __( 'None', 'wpessential' ),
					'lift'   => __( 'Lift', 'wpessential' ),
					'zoom'   => __( 'Zoom', 'wpessential' ),
					'glow'   => __( 'Glow', 'wpessential' ),
					'shadow' => __( 'Shadow', 'wpessential' ),
					'dim'    => __( 'Dim others', 'wpessential' ),
					'tilt'   => __( 'Tilt', 'wpessential' ),
				),
			),
			'rootAriaLabel'       => array(
				'type'   => 'text',
				'label'  => __( 'Wrapper ARIA label (needs a role)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'rootRole'            => array(
				'type'    => 'select',
				'label'   => __( 'Wrapper ARIA role', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''              => __( 'Default', 'wpessential' ),
					'region'        => 'region',
					'banner'        => 'banner',
					'complementary' => 'complementary',
					'note'          => 'note',
					'presentation'  => 'presentation',
					'group'         => 'group',
				),
			),
			'rootAriaHidden'      => array(
				'type'   => 'toggle',
				'label'  => __( 'Decorative: hide from screen readers', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'rootAriaLive'        => array(
				'type'    => 'select',
				'label'   => __( 'Announce changes (aria-live)', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''          => __( 'Off', 'wpessential' ),
					'polite'    => __( 'Polite', 'wpessential' ),
					'assertive' => __( 'Assertive', 'wpessential' ),
				),
			),
			'animation'           => array(
				'type'    => 'select',
				'label'   => __( 'Entrance animation', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'default' => '',
				'options' => self::animations(),
			),
			'animDuration'        => array(
				'type'   => 'range',
				'label'  => __( 'Animation duration (ms)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-adur',
				'unit'   => 'ms',
				'min'    => 100,
				'max'    => 3000,
				'step'   => 50,
			),
			'animDelay'           => array(
				'type'   => 'range',
				'label'  => __( 'Animation delay (ms)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-adelay',
				'unit'   => 'ms',
				'min'    => 0,
				'max'    => 3000,
				'step'   => 50,
			),
			'animTrigger'         => array(
				'type'    => 'select',
				'label'   => __( 'Entrance trigger', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''       => __( 'Default scroll (CSS)', 'wpessential' ),
					'scroll' => __( 'Scroll into view', 'wpessential' ),
					'hover'  => __( 'Hover or keyboard focus', 'wpessential' ),
					'click'  => __( 'Click or tap', 'wpessential' ),
					'load'   => __( 'Page load', 'wpessential' ),
				),
			),
			'animReduced'         => array(
				'type'    => 'select',
				'label'   => __( 'Reduced-motion alternative', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''     => __( 'No animation', 'wpessential' ),
					'fade' => __( 'Gentle fade (no movement)', 'wpessential' ),
				),
			),
			'staggerChildren'     => array(
				'type'   => 'toggle',
				'label'  => __( 'Stagger direct child elements', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'staggerStep'         => array(
				'type'   => 'range',
				'label'  => __( 'Time between children (ms)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-stagger-step',
				'unit'   => 'ms',
				'min'    => 0,
				'max'    => 500,
				'step'   => 25,
			),
			'stickyEnabled'       => array(
				'type'   => 'toggle',
				'label'  => __( 'Sticky within parent container', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'stickyOffset'        => array(
				'type'       => 'range',
				'label'      => __( 'Sticky top offset (px)', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-sticky-offset',
				'unit'       => 'px',
				'min'        => 0,
				'max'        => 300,
				'responsive' => true,
			),
			'stickyFrom'          => array(
				'type'    => 'select',
				'label'   => __( 'Sticky enabled from screen width', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''        => __( 'Every breakpoint', 'wpessential' ),
					'tablet'  => __( 'Tablet and desktop (768px+)', 'wpessential' ),
					'desktop' => __( 'Desktop only (1024px+)', 'wpessential' ),
				),
			),
			'parallaxEnabled'     => array(
				'type'   => 'toggle',
				'label'  => __( 'Parallax: enable direct-child layers', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'parallaxAxis'        => array(
				'type'    => 'select',
				'label'   => __( 'Parallax axis', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''  => __( 'Vertical', 'wpessential' ),
					'x' => __( 'Horizontal', 'wpessential' ),
				),
			),
			'parallaxLayer1'      => array(
				'type'       => 'range',
				'label'      => __( 'Parallax layer 1 travel (px, 0 disables)', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-parallax-layer-1',
				'unit'       => 'px',
				'units'      => array( 'px' ),
				'min'        => -240,
				'max'        => 240,
				'step'       => 5,
				'responsive' => true,
			),
			'parallaxLayer2'      => array(
				'type'       => 'range',
				'label'      => __( 'Parallax layer 2 travel (px, 0 disables)', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-parallax-layer-2',
				'unit'       => 'px',
				'units'      => array( 'px' ),
				'min'        => -240,
				'max'        => 240,
				'step'       => 5,
				'responsive' => true,
			),
			'parallaxLayer3'      => array(
				'type'       => 'range',
				'label'      => __( 'Parallax layer 3 travel (px, 0 disables)', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-parallax-layer-3',
				'unit'       => 'px',
				'units'      => array( 'px' ),
				'min'        => -240,
				'max'        => 240,
				'step'       => 5,
				'responsive' => true,
			),
			'parallaxLayer4'      => array(
				'type'       => 'range',
				'label'      => __( 'Parallax layer 4 travel (px, 0 disables)', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-parallax-layer-4',
				'unit'       => 'px',
				'units'      => array( 'px' ),
				'min'        => -240,
				'max'        => 240,
				'step'       => 5,
				'responsive' => true,
			),
			'stickyStopId'        => array(
				'type'   => 'text',
				'label'  => __( 'Sticky stop at element ID (same page)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'focusRingStyle'      => array(
				'type'    => 'select',
				'label'   => __( 'Keyboard focus outline style', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'css'     => '--wpek-focus-style',
				'options' => array(
					''       => __( 'Theme default', 'wpessential' ),
					'solid'  => __( 'Solid', 'wpessential' ),
					'dashed' => __( 'Dashed', 'wpessential' ),
					'double' => __( 'Double', 'wpessential' ),
				),
			),
			'focusRingColor'      => array(
				'type'   => 'color',
				'label'  => __( 'Keyboard focus outline colour', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-focus-color',
			),
			'focusRingWidth'      => array(
				'type'       => 'range',
				'label'      => __( 'Keyboard focus outline width', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-focus-width',
				'unit'       => 'px',
				'min'        => 1,
				'max'        => 9,
				'step'       => 1,
				'responsive' => true,
			),
			'focusRingOffset'     => array(
				'type'       => 'range',
				'label'      => __( 'Keyboard focus outline offset', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-focus-offset',
				'unit'       => 'px',
				'min'        => 0,
				'max'        => 15,
				'step'       => 1,
				'responsive' => true,
			),
			'glass'               => array(
				'type'   => 'toggle',
				'label'  => __( 'Glass / translucent surface', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'glassBlur'           => array(
				'type'       => 'range',
				'label'      => __( 'Glass backdrop blur', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-glass-blur',
				'unit'       => 'px',
				'min'        => 0,
				'max'        => 60,
				'step'       => 1,
				'responsive' => true,
			),
			'glassOpacity'        => array(
				'type'       => 'range',
				'label'      => __( 'Glass surface opacity', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-glass-opacity',
				'unit'       => '%',
				'min'        => 30,
				'max'        => 100,
				'step'       => 1,
				'responsive' => true,
			),
			'hidePrint'           => array(
				'type'   => 'toggle',
				'label'  => __( 'Hide this element when printing', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'hide'                => array(
				'type'   => 'breakpoints',
				'label'  => __( 'Hide on', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
		);

		// Opt-in shared visual effects; classes use only allowlisted values.
		$attr += array(
			'effectGradientBorder' => array(
				'type'   => 'toggle',
				'label'  => __( 'Enable gradient border', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'effectBorderFrom'     => array(
				'type'   => 'color',
				'label'  => __( 'Gradient border start colour', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-gradient-a',
			),
			'effectBorderTo'       => array(
				'type'   => 'color',
				'label'  => __( 'Gradient border end colour', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-gradient-b',
			),
			'effectBorderWidth'    => array(
				'type'       => 'range',
				'label'      => __( 'Gradient border width', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-gradient-width',
				'unit'       => 'px',
				'min'        => 1,
				'max'        => 16,
				'responsive' => true,
			),
			'effectBorderAngle'    => array(
				'type'   => 'range',
				'label'  => __( 'Gradient border angle', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-gradient-angle',
				'unit'   => 'deg',
				'min'    => 0,
				'max'    => 360,
			),
			'effectBorderSpin'     => array(
				'type'   => 'toggle',
				'label'  => __( 'Animate gradient border', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'effectBorderSpeed'    => array(
				'type'   => 'range',
				'label'  => __( 'Animated border cycle (ms)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'css'    => '--wpek-gradient-speed',
				'unit'   => 'ms',
				'min'    => 1500,
				'max'    => 12000,
				'step'   => 500,
			),
			'effectBlend'          => array(
				'type'    => 'select',
				'label'   => __( 'Mix blend mode', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''           => __( 'Default', 'wpessential' ),
					'multiply'   => __( 'Multiply', 'wpessential' ),
					'screen'     => __( 'Screen', 'wpessential' ),
					'overlay'    => __( 'Overlay', 'wpessential' ),
					'darken'     => __( 'Darken', 'wpessential' ),
					'lighten'    => __( 'Lighten', 'wpessential' ),
					'difference' => __( 'Difference', 'wpessential' ),
					'exclusion'  => __( 'Exclusion', 'wpessential' ),
				),
			),
			'effectMask'           => array(
				'type'    => 'select',
				'label'   => __( 'Fade-edge gradient mask', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''           => __( 'No mask', 'wpessential' ),
					'top'        => __( 'Fade top edge', 'wpessential' ),
					'bottom'     => __( 'Fade bottom edge', 'wpessential' ),
					'left'       => __( 'Fade left edge', 'wpessential' ),
					'right'      => __( 'Fade right edge', 'wpessential' ),
					'vertical'   => __( 'Fade both top/bottom', 'wpessential' ),
					'horizontal' => __( 'Fade both left/right', 'wpessential' ),
				),
			),
		);

		// Opt-in shape, texture and state-aware elevation settings.
		$attr += array(
			'clipShape'      => array(
				'type'    => 'select',
				'label'   => __( 'Clip-path shape', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''      => __( 'None', 'wpessential' ),
					'slant' => __( 'Slant', 'wpessential' ),
					'wave'  => __( 'Wave', 'wpessential' ),
					'blob'  => __( 'Blob', 'wpessential' ),
				),
			),
			'grainEnabled'   => array(
				'type'   => 'toggle',
				'label'  => __( 'Enable noise / grain texture', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'grainOpacity'   => array(
				'type'       => 'range',
				'label'      => __( 'Grain intensity (percentage)', 'wpessential' ),
				'panel'      => 'advanced',
				'common'     => true,
				'css'        => '--wpek-grain-opacity',
				'unit'       => '%',
				'min'        => 1,
				'max'        => 20,
				'step'       => 1,
				'responsive' => true,
			),
			'elevationRest'  => array(
				'type'    => 'select',
				'label'   => __( 'Elevation at rest', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''   => __( 'Default', 'wpessential' ),
					'sm' => __( 'Small', 'wpessential' ),
					'md' => __( 'Medium', 'wpessential' ),
					'lg' => __( 'Large', 'wpessential' ),
				),
			),
			'elevationHover' => array(
				'type'    => 'select',
				'label'   => __( 'Elevation on hover or focus-within', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''   => __( 'Default', 'wpessential' ),
					'sm' => __( 'Small', 'wpessential' ),
					'md' => __( 'Medium', 'wpessential' ),
					'lg' => __( 'Large', 'wpessential' ),
				),
			),
			'elevationFocus' => array(
				'type'    => 'select',
				'label'   => __( 'Elevation on keyboard focus', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''   => __( 'Default', 'wpessential' ),
					'sm' => __( 'Small', 'wpessential' ),
					'md' => __( 'Medium', 'wpessential' ),
					'lg' => __( 'Large', 'wpessential' ),
				),
			),
		);

		// Advanced responsive foundations shared by Gutenberg and Elementor.
		$attr += array(
			'containerQuerySource'    => array(
				'type'   => 'toggle',
				'label'  => __( 'Act as a CSS size query container', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'containerQueryHideBelow' => array(
				'type'   => 'range',
				'label'  => __( 'Hide below nearest container width', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'unit'   => 'px',
				'min'    => 160,
				'max'    => 1920,
				'step'   => 1,
			),
			'containerQueryHideAbove' => array(
				'type'   => 'range',
				'label'  => __( 'Hide above nearest container width', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'unit'   => 'px',
				'min'    => 160,
				'max'    => 1920,
				'step'   => 1,
			),
			'fluidPaddingMin'         => array(
				'type'   => 'range',
				'label'  => __( 'Fluid padding at viewport 360px', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'unit'   => 'px',
				'min'    => 0,
				'max'    => 260,
				'step'   => 1,
			),
			'fluidPaddingMax'         => array(
				'type'   => 'range',
				'label'  => __( 'Fluid padding at viewport 1440px', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'unit'   => 'px',
				'min'    => 0,
				'max'    => 260,
				'step'   => 1,
			),
			'fluidGapMin'             => array(
				'type'   => 'range',
				'label'  => __( 'Fluid gap at viewport 360px', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'unit'   => 'px',
				'min'    => 0,
				'max'    => 260,
				'step'   => 1,
			),
			'fluidGapMax'             => array(
				'type'   => 'range',
				'label'  => __( 'Fluid gap at viewport 1440px', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
				'unit'   => 'px',
				'min'    => 0,
				'max'    => 260,
				'step'   => 1,
			),
			'paddingToken'            => array(
				'type'      => 'select',
				'label'     => __( 'Theme padding token', 'wpessential' ),
				'panel'     => 'advanced',
				'common'    => true,
				'css'       => 'padding',
				'important' => true,
				'map'       => array(
					'sm' => 'padding:var(--wp--preset--spacing--20,8px)',
					'md' => 'padding:var(--wp--preset--spacing--40,16px)',
					'lg' => 'padding:var(--wp--preset--spacing--50,24px)',
					'xl' => 'padding:var(--wp--preset--spacing--70,48px)',
				),
				'options'   => array(
					''   => __( 'Default', 'wpessential' ),
					'sm' => 'Small (8px)',
					'md' => 'Medium (16px)',
					'lg' => 'Large (24px)',
					'xl' => 'Extra large (48px)',
				),
			),
			'radiusToken'             => array(
				'type'      => 'select',
				'label'     => __( 'Theme radius token', 'wpessential' ),
				'panel'     => 'advanced',
				'common'    => true,
				'css'       => 'border-radius',
				'important' => true,
				'map'       => array(
					'sm' => 'border-radius:var(--wp--custom--radius--sm,6px)',
					'md' => 'border-radius:var(--wp--custom--radius--md,10px)',
					'lg' => 'border-radius:var(--wp--custom--radius--lg,16px)',
					'xl' => 'border-radius:var(--wp--custom--radius--xl,24px)',
				),
				'options'   => array(
					''   => __( 'Default', 'wpessential' ),
					'sm' => 'Small',
					'md' => 'Medium',
					'lg' => 'Large',
					'xl' => 'Extra large',
				),
			),
		);

		// Conditional display rules are evaluated before rendering content.
		$attr  += array(
			'conditionLogin'            => array(
				'type'    => 'select',
				'label'   => __( 'Visibility: visitor login state', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''    => __( 'Everyone', 'wpessential' ),
					'in'  => __( 'Logged-in visitors only', 'wpessential' ),
					'out' => __( 'Logged-out visitors only', 'wpessential' ),
				),
			),
			'conditionRole'             => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: required user role slug (single role)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionCart'             => array(
				'type'    => 'select',
				'label'   => __( 'Visibility: WooCommerce cart', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''         => __( 'Any cart', 'wpessential' ),
					'nonempty' => __( 'Cart must contain items', 'wpessential' ),
					'empty'    => __( 'Cart must be empty', 'wpessential' ),
				),
			),
			'conditionPurchasedProduct' => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: purchased WooCommerce product ID', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionStart'            => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: start date (YYYY-MM-DD HH:mm, site time)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionEnd'              => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: end date (YYYY-MM-DD HH:mm, site time)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionPath'             => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: exact URL path (e.g. /offers/)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionQueryKey'         => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: URL query parameter name', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionQueryValue'       => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: required URL query value (optional)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionReferrer'         => array(
				'type'   => 'text',
				'label'  => __( 'Visibility: exact referrer domain (not secure)', 'wpessential' ),
				'panel'  => 'advanced',
				'common' => true,
			),
			'conditionDevice'           => array(
				'type'    => 'select',
				'label'   => __( 'Visibility: device viewport (CSS only)', 'wpessential' ),
				'panel'   => 'advanced',
				'common'  => true,
				'options' => array(
					''        => __( 'Any screen', 'wpessential' ),
					'mobile'  => __( 'Mobile under 768px', 'wpessential' ),
					'tablet'  => __( 'Tablet 768–1023px', 'wpessential' ),
					'desktop' => __( 'Desktop 1024px and wider', 'wpessential' ),
				),
			),
		);
		$groups = array(
			'advMargin'     => array(
				'type'     => 'spacing',
				'label'    => __( 'Margin', 'wpessential' ),
				'prop'     => 'margin',
				'selector' => '',
				'panel'    => 'advanced',
				'common'   => true,
			),
			'advPadding'    => array(
				'type'     => 'spacing',
				'label'    => __( 'Padding', 'wpessential' ),
				'prop'     => 'padding',
				'selector' => '',
				'panel'    => 'advanced',
				'common'   => true,
			),
			'advBackground' => array(
				'type'     => 'background',
				'label'    => __( 'Background', 'wpessential' ),
				'selector' => '',
				'panel'    => 'advanced',
				'common'   => true,
			),
			'advBorder'     => array(
				'type'     => 'border',
				'label'    => __( 'Border', 'wpessential' ),
				'selector' => '',
				'panel'    => 'advanced',
				'common'   => true,
			),
			'advShadow'     => array(
				'type'     => 'shadow',
				'label'    => __( 'Box shadow', 'wpessential' ),
				'selector' => '',
				'panel'    => 'advanced',
				'common'   => true,
			),
		);
		return array( $attr, $groups );
	}

	/**
	 * Entrance animations (kit/common.css).
	 *
	 * @return array
	 */
	public static function animations() {
		return array(
			''           => __( 'None', 'wpessential' ),
			'fade'       => __( 'Fade in', 'wpessential' ),
			'fade-up'    => __( 'Fade up', 'wpessential' ),
			'fade-down'  => __( 'Fade down', 'wpessential' ),
			'fade-left'  => __( 'Fade from right', 'wpessential' ),
			'fade-right' => __( 'Fade from left', 'wpessential' ),
			'zoom-in'    => __( 'Zoom in', 'wpessential' ),
			'zoom-out'   => __( 'Zoom out', 'wpessential' ),
			'slide-up'   => __( 'Slide up', 'wpessential' ),
			'flip-x'     => __( 'Flip', 'wpessential' ),
			'blur-in'    => __( 'Blur in', 'wpessential' ),
			'reveal'     => __( 'Reveal (clip)', 'wpessential' ),
			'rotate-in'  => __( 'Rotate in', 'wpessential' ),
		);
	}

	/**
	 * CSS declarations of one group: [ breakpoint ][ selector ][] = declaration.
	 *
	 * @param array  $group Group definition.
	 * @param array  $value Stored values.
	 * @param string $root  Root selector (#id).
	 * @param array  $rules Rules to add to.
	 */
	public static function css( array $group, array $value, $root, array &$rules ) {
		$fields = self::fields()[ $group['type'] ] ?? array();
		$base   = trim( $root . ' ' . ( $group['selector'] ?? '' ) );
		$parts  = array_map( 'trim', explode( ',', $base ) );
		if ( 'typography' === $group['type'] && is_string( $value['preset'] ?? null ) ) {
			$decls = self::preset_declarations( $value['preset'], Kit::typography_presets() );
			foreach ( $decls as $decl ) {
				$rules['base'][ $base ][] = $decl;
			}
		}
		// Fluid font size: one clamp() between the 360px and the 1440px value.
		if ( 'typography' === $group['type'] && is_numeric( $value['sizeMin'] ?? '' ) && is_numeric( $value['sizeMax'] ?? '' ) ) {
			$min                      = (float) $value['sizeMin'];
			$max                      = (float) $value['sizeMax'];
			$slope                    = ( $max - $min ) / ( 1440 - 360 );
			$rules['base'][ $base ][] = sprintf( 'font-size:clamp(%1$spx, calc(%2$spx + %3$svw), %4$spx)', min( $min, $max ), round( $min - $slope * 360, 3 ), round( $slope * 100, 4 ), max( $min, $max ) );
		}
		$states = array(
			'hover'  => array( ':hover', ':focus-visible' ),
			'focus'  => array( ':focus-visible' ),
			'active' => array( ':active' ),
		);
		// CSS filters: the functions of one state become one filter declaration.
		foreach ( array(
			''      => $parts,
			'hover' => null,
		) as $state => $sels ) {
			$fns = array();
			foreach ( $fields as $key => $f ) {
				if ( ! empty( $f['fn'] ) && ( $f['state'] ?? '' ) === $state && is_numeric( $value[ $key ] ?? '' ) ) {
					$fns[] = $f['fn'] . '(' . ( 0 + $value[ $key ] ) . $f['unit'] . ')';
				}
			}
			if ( $fns ) {
				$sels                                     = $sels ?? array_merge( ...array_map( static fn( $x ) => array( $x . ':hover', $x . ':focus-visible' ), $parts ) );
				$rules['base'][ implode( ',', $sels ) ][] = 'filter:' . implode( ' ', $fns );
			}
		}
		foreach ( $fields as $key => $f ) {
			if ( '' === $f['prop'] || ! isset( $value[ $key ] ) || ! empty( $f['fluid'] ) || ! empty( $f['fn'] ) ) {
				continue;
			}
			$values = ! empty( $f['responsive'] ) && is_array( $value[ $key ] ) ? $value[ $key ] : array( 'base' => $value[ $key ] );
			$prop   = str_replace( '{prop}', $group['prop'] ?? 'color', $f['prop'] );
			$sels   = $parts;
			if ( ! empty( $f['state'] ) ) {
				$sels = array();
				foreach ( $parts as $part ) {
					foreach ( $states[ $f['state'] ] as $pseudo ) {
						$sels[] = $part . $pseudo;
					}
				}
			}
			if ( ! empty( $f['scheme'] ) ) {
				// The theme's visitor toggle sets data-wpe-scheme on <html>.
				$sels = array_map( static fn( $x ) => ':root[data-wpe-scheme="dark"] ' . $x, $sels );
			}
			$sel = implode( ',', $sels );
			foreach ( $values as $bp => $v ) {
				if ( '' === $v || null === $v ) {
					continue;
				}
				$css = self::value( $f, $v );
				if ( '' !== $css ) {
					$rules[ (string) $bp ][ $sel ][] = $prop . ':' . $css;
				}
			}
		}
	}

	/**
	 * A validated preset slug becomes references to the Site Tools Kit variables.
	 * Local group declarations follow these, so explicit overrides win.
	 *
	 * @param string $slug Preset identifier.
	 * @param array  $presets Available registry.
	 * @return array
	 */
	public static function preset_declarations( string $slug, array $presets ): array {
		if ( preg_match( '/^wpe-custom-[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug ) !== 1 ) {
			return array();
		}
		$allowed = array( 'font-family', 'font-size', 'font-weight', 'line-height', 'letter-spacing', 'text-transform', 'font-style', 'text-decoration' );
		foreach ( $presets as $preset ) {
			if ( $slug !== ( $preset['slug'] ?? '' ) ) {
				continue;
			}
			$out = array();
			foreach ( array_intersect( $allowed, (array) ( $preset['properties'] ?? array() ) ) as $property ) {
				$out[] = $property . ':var(--wpe-typography-' . $slug . '-' . $property . ',inherit)';
			}
			return $out;
		}
		return array();
	}

	/**
	 * One field value as CSS.
	 *
	 * @param array $f Field.
	 * @param mixed $v Value.
	 * @return string
	 */
	private static function value( array $f, $v ) {
		if ( 'color' === $f['type'] ) {
			return 'var(--wp--preset--color--' . sanitize_key( $v ) . ')';
		}
		if ( ! empty( $f['font'] ) ) {
			return 'var(--wp--preset--font-family--' . sanitize_key( $v ) . ')';
		}
		if ( 'box-shadow' === $f['prop'] && 'text' !== $f['type'] ) {
			return self::shadows()[ $v ] ?? '';
		}
		if ( 'background-image' === $f['prop'] ) {
			return ! empty( $f['image'] ) ? 'url("' . esc_url_raw( $v ) . '")' : ( self::gradients()[ $v ] ?? '' );
		}
		if ( ! empty( $f['pct'] ) && is_numeric( $v ) ) {
			return (string) ( ( 0 + $v ) / 100 );
		}
		if ( 'range' === $f['type'] && is_numeric( $v ) ) {
			return ( 0 + $v ) . ( $f['unit'] ?? '' );
		}
		return preg_replace( '/[^a-z0-9#%.,()\/ \-]/i', '', (string) $v );
	}

	/**
	 * Native Elementor controls for one group.
	 *
	 * @param \Elementor\Widget_Base $w     Widget.
	 * @param string                 $key   Group key.
	 * @param array                  $group Group.
	 */
	public static function elementor( $w, $key, array $group ) {
		$sel   = '{{WRAPPER}} ' . ( $group['selector'] ?? '' );
		$hover = implode( ',', array_map( static fn( $s ) => '{{WRAPPER}} ' . trim( $s ) . ':hover', explode( ',', $group['selector'] ?? '' ) ) );
		$label = $group['label'] ?? $key;
		switch ( $group['type'] ) {
			case 'typography':
				$presets = Kit::typography_presets();
				$choices = array( '' => __( 'Default / custom', 'wpessential' ) );
				$map     = array();
				foreach ( $presets as $preset ) {
					$slug  = (string) ( $preset['slug'] ?? '' );
					$decls = self::preset_declarations( $slug, $presets );
					if ( $decls ) {
						$choices[ $slug ] = (string) $preset['name'];
						$map[ $slug ]     = implode( ';', $decls ) . ';';
					}
				}
				if ( $map ) {
					$w->add_control(
						$key . '_wpe_preset',
						array(
							'label'                => __( 'Global typography preset', 'wpessential' ),
							'type'                 => \Elementor\Controls_Manager::SELECT,
							'options'              => $choices,
							'default'              => '',
							'selectors_dictionary' => $map,
							'selectors'            => array( $sel => '{{VALUE}}' ),
						)
					);
				}
				$w->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					array(
						'name'     => $key,
						'label'    => $label,
						'selector' => $sel,
					)
				);
				break;
			case 'spacing':
				$w->add_responsive_control(
					$key,
					array(
						'label'      => $label,
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', 'em', 'rem', '%', 'vw' ),
						'selectors'  => array( $sel => ( $group['prop'] ?? 'padding' ) . ': {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
					)
				);
				break;
			case 'border':
				$w->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					array(
						'name'     => $key,
						'label'    => $label,
						'selector' => $sel,
					)
				);
				$w->add_responsive_control(
					$key . '_radius',
					array(
						'label'      => __( 'Border radius', 'wpessential' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => array( 'px', '%', 'em' ),
						'selectors'  => array( $sel => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
					)
				);
				$w->add_control(
					$key . '_hover_color',
					array(
						'label'     => __( 'Hover border colour', 'wpessential' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => array( $hover => 'border-color: {{VALUE}};' ),
					)
				);
				break;
			case 'shadow':
				$w->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					array(
						'name'     => $key,
						'label'    => $label,
						'selector' => $sel,
					)
				);
				$w->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					array(
						'name'     => $key . '_hover',
						'label'    => __( 'Hover shadow', 'wpessential' ),
						'selector' => $hover,
					)
				);
				break;
			case 'background':
				$w->add_group_control(
					\Elementor\Group_Control_Background::get_type(),
					array(
						'name'     => $key,
						'label'    => $label,
						'types'    => array( 'classic', 'gradient' ),
						'selector' => $sel,
					)
				);
				$w->add_group_control(
					\Elementor\Group_Control_Background::get_type(),
					array(
						'name'     => $key . '_hover',
						'label'    => __( 'Hover background', 'wpessential' ),
						'types'    => array( 'classic', 'gradient' ),
						'selector' => $hover,
					)
				);
				break;
			case 'textEffects':
				$w->add_group_control(
					\Elementor\Group_Control_Text_Shadow::get_type(),
					array(
						'name'     => $key,
						'label'    => $label,
						'selector' => $sel,
					)
				);
				$w->add_group_control(
					\Elementor\Group_Control_Text_Shadow::get_type(),
					array(
						'name'     => $key . '_hover',
						'label'    => __( 'Text shadow on hover', 'wpessential' ),
						'selector' => $hover,
					)
				);
				$w->add_group_control(
					\Elementor\Group_Control_Text_Stroke::get_type(),
					array(
						'name'     => $key . '_stroke',
						'selector' => $sel,
					)
				);
				$w->add_control(
					$key . '_blend',
					array(
						'label'     => __( 'Blend mode', 'wpessential' ),
						'type'      => \Elementor\Controls_Manager::SELECT,
						'options'   => self::fields()['textEffects']['blend']['options'],
						'selectors' => array( $sel => 'mix-blend-mode: {{VALUE}};' ),
					)
				);
				break;
			case 'filters':
				$w->add_group_control(
					\Elementor\Group_Control_Css_Filter::get_type(),
					array(
						'name'     => $key,
						'label'    => $label,
						'selector' => $sel,
					)
				);
				$w->add_group_control(
					\Elementor\Group_Control_Css_Filter::get_type(),
					array(
						'name'     => $key . '_hover',
						'label'    => __( 'CSS filters on hover', 'wpessential' ),
						'selector' => $hover,
					)
				);
				foreach ( array(
					''       => array( __( 'Opacity', 'wpessential' ), $sel ),
					'_hover' => array( __( 'Opacity on hover', 'wpessential' ), $hover ),
				) as $suffix => $o ) {
					$w->add_control(
						$key . '_opacity' . $suffix,
						array(
							'label'     => $o[0],
							'type'      => \Elementor\Controls_Manager::SLIDER,
							'range'     => array(
								'px' => array(
									'min'  => 0,
									'max'  => 1,
									'step' => 0.01,
								),
							),
							'selectors' => array( $o[1] => 'opacity: {{SIZE}};' ),
						)
					);
				}
				break;
			case 'colors':
				$palette = Kit::palette();
				if ( $palette ) {
					$w->add_control(
						$key . '_wpe_global',
						array(
							'label'     => __( 'Global colour', 'wpessential' ),
							'type'      => \Elementor\Controls_Manager::SELECT,
							'options'   => array( '' => __( 'Default / custom', 'wpessential' ) ) + $palette,
							'default'   => '',
							'selectors' => array( $sel => ( $group['prop'] ?? 'color' ) . ':var(--wp--preset--color--{{VALUE}});' ),
						)
					);
				}
				$w->add_control(
					$key,
					array(
						'label'     => $label,
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => array( $sel => ( $group['prop'] ?? 'color' ) . ': {{VALUE}};' ),
					)
				);
				$w->add_control(
					$key . '_hover',
					array(
						/* translators: %s: option label. */
						'label'     => sprintf( __( '%s (hover)', 'wpessential' ), $label ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => array( $hover => ( $group['prop'] ?? 'color' ) . ': {{VALUE}};' ),
					)
				);
				$each  = static fn( $suffix ) => implode( ',', array_map( static fn( $x ) => '{{WRAPPER}} ' . trim( $x ) . $suffix, explode( ',', $group['selector'] ?? '' ) ) );
				$dark  = ':root[data-wpe-scheme="dark"] ';
				$extra = array(
					'_focus'      => array( /* translators: %s: option label. */ __( '%s (keyboard focus)', 'wpessential' ), $each( ':focus-visible' ) ),
					'_active'     => array( /* translators: %s: option label. */ __( '%s (pressed)', 'wpessential' ), $each( ':active' ) ),
					'_dark'       => array( /* translators: %s: option label. */ __( '%s (dark mode)', 'wpessential' ), implode( ',', array_map( static fn( $x ) => $dark . $x, explode( ',', $each( '' ) ) ) ) ),
					'_dark_hover' => array( /* translators: %s: option label. */ __( '%s (dark mode, hover)', 'wpessential' ), implode( ',', array_map( static fn( $x ) => $dark . $x, explode( ',', $each( ':hover' ) ) ) ) ),
				);
				foreach ( $extra as $suffix => $def ) {
					$w->add_control(
						$key . $suffix,
						array(
							'label'     => sprintf( $def[0], $label ),
							'type'      => \Elementor\Controls_Manager::COLOR,
							'selectors' => array( $def[1] => ( $group['prop'] ?? 'color' ) . ': {{VALUE}};' ),
						)
					);
				}
				break;
		}
	}
}
