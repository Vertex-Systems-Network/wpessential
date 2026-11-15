<?php
/**
 * Elementor widget built from a kit element definition: native controls (responsive where the
 * option is), and the same render as the Gutenberg block.
 *
 * @package WPEssential
 */

namespace WPEssential\Platform\Kit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base widget; each element subclass sets KIT to its folder name.
 */
abstract class Widget extends \Elementor\Widget_Base {

	const KIT = '';

	/**
	 * Definition.
	 *
	 * @return array
	 */
	protected function def() {
		return Kit::defs()[ static::KIT ] ?? array( 'attributes' => array() );
	}

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'wpe-' . static::KIT;
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->def()['title'] ?? static::KIT;
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return $this->def()['elementor_icon'] ?? 'eicon-star';
	}

	/**
	 * Category.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'wpessential' );
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return $this->def()['keywords'] ?? array();
	}

	/**
	 * Style handles.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'wpessential-kit-' . static::KIT );
	}

	/**
	 * The element's script, when it has one.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return file_exists( ( $this->def()['dir'] ?? '' ) . '/script.js' ) ? array( 'wpessential-kit-' . static::KIT ) : array();
	}

	/**
	 * Controls from the definition, one section per panel.
	 */
	protected function register_controls() {
		$def    = $this->def();
		$panels = $def['panels'] ?? array( 'content' => __( 'Content', 'wpessential' ) );
		$types  = array(
			'text'     => \Elementor\Controls_Manager::TEXT,
			'textarea' => \Elementor\Controls_Manager::TEXTAREA,
			'url'      => \Elementor\Controls_Manager::TEXT,
			'image'    => \Elementor\Controls_Manager::MEDIA,
			'html'     => \Elementor\Controls_Manager::WYSIWYG,
			'select'   => \Elementor\Controls_Manager::SELECT,
			'color'    => \Elementor\Controls_Manager::SELECT,
			'toggle'   => \Elementor\Controls_Manager::SWITCHER,
			'range'    => \Elementor\Controls_Manager::SLIDER,
			'icon'     => \Elementor\Controls_Manager::ICONS,
		);
		foreach ( $panels as $panel => $label ) {
			$this->start_controls_section(
				'section_' . $panel,
				array(
					'label' => $label,
					'tab'   => 'content' === $panel ? \Elementor\Controls_Manager::TAB_CONTENT : \Elementor\Controls_Manager::TAB_STYLE,
				)
			);
			foreach ( $def['attributes'] as $key => $a ) {
				if ( ( $a['panel'] ?? 'content' ) !== $panel ) {
					continue;
				}
				$args = array(
					'label' => $a['label'] ?? $key,
					'type'  => $types[ $a['type'] ] ?? \Elementor\Controls_Manager::TEXT,
				);
				if ( 'select' === $a['type'] ) {
					$args['options'] = $a['options'];
				} elseif ( 'color' === $a['type'] ) {
					$args['options'] = array( '' => __( 'Default', 'wpessential' ) ) + Kit::palette();
				} elseif ( 'range' === $a['type'] ) {
					$args['size_units'] = Kit::units( $a );
					$args['range']      = array();
					foreach ( $args['size_units'] as $unit ) {
						$args['range'][ $unit ] = self::unit_range( $a, $unit );
					}
				} elseif ( 'icon' === $a['type'] ) {
					$args['skin']        = 'inline';
					$args['label_block'] = false;
					if ( ! empty( $a['default']['value'] ) ) {
						$args['default'] = array(
							'value'   => 'wpei wpei-' . $a['default']['value'],
							'library' => 'wpessential',
						);
					}
				} elseif ( 'textarea' === $a['type'] ) {
					$args['rows'] = 3;
				}
				if ( isset( $a['default'] ) && '' !== $a['default'] && 'icon' !== $a['type'] ) {
					$args['default'] = 'toggle' === $a['type'] ? ( $a['default'] ? 'yes' : '' ) : ( 'range' === $a['type'] ? array(
						'size' => $a['default'],
						'unit' => $a['unit'] ?? 'px',
					) : $a['default'] );
				}
				if ( 'repeater' === $a['type'] ) {
					$args['type']        = \Elementor\Controls_Manager::REPEATER;
					$args['fields']      = self::repeater_fields( $a['fields'], $types );
					$args['title_field'] = '{{{ ' . ( $a['title_field'] ?? 'text' ) . ' }}}';
					$args['default']     = array_map( static fn( $item ) => self::elementor_default( $a['fields'], $item ), $a['default'] ?? array() );
				}
				if ( ! empty( $a['responsive'] ) ) {
					$this->add_responsive_control( $key, $args );
				} else {
					$this->add_control( $key, $args );
				}
			}
			foreach ( $def['groups'] ?? array() as $key => $group ) {
				if ( ( $group['panel'] ?? 'style' ) === $panel ) {
					Groups::elementor( $this, $key, $group );
				}
			}
			$this->end_controls_section();
		}
		// Custom CSS for every widget (Elementor has this only in Pro).
		$this->start_controls_section(
			'section_wpe_custom_css',
			array(
				'label' => __( 'Custom CSS (WPEssential)', 'wpessential' ),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			)
		);
		$this->add_control(
			'wpe_custom_css',
			array(
				'label'       => __( 'Custom CSS', 'wpessential' ),
				'type'        => \Elementor\Controls_Manager::CODE,
				'language'    => 'css',
				'render_type' => 'template',
				'description' => __( 'Use "selector" for this element, e.g. selector { border-radius: 20px; } or selector .wpe-button__link:hover { … }', 'wpessential' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Elementor repeater fields from our item fields.
	 *
	 * @param array $fields Item fields.
	 * @param array $types  Our type => Elementor control type.
	 * @return array
	 */
	private static function repeater_fields( array $fields, array $types ) {
		$repeater = new \Elementor\Repeater();
		foreach ( $fields as $key => $f ) {
			$args = array(
				'label'       => $f['label'] ?? $key,
				'type'        => $types[ $f['type'] ] ?? \Elementor\Controls_Manager::TEXT,
				'label_block' => in_array( $f['type'], array( 'text', 'textarea', 'url' ), true ),
			);
			if ( 'select' === $f['type'] ) {
				$args['options'] = $f['options'];
			}
			if ( 'icon' === $f['type'] ) {
				$args['skin']        = 'inline';
				$args['label_block'] = false;
			}
			if ( 'textarea' === $f['type'] ) {
				$args['rows'] = 2;
			}
			$repeater->add_control( $key, $args );
		}
		return $repeater->get_controls();
	}

	/**
	 * One default item in Elementor's shape (icons, toggles).
	 *
	 * @param array $fields Item fields.
	 * @param array $item   Our default item.
	 * @return array
	 */
	private static function elementor_default( array $fields, array $item ) {
		foreach ( $fields as $key => $f ) {
			if ( ! isset( $item[ $key ] ) ) {
				continue;
			}
			if ( 'icon' === $f['type'] && is_array( $item[ $key ] ) && 'wpessential' === ( $item[ $key ]['library'] ?? '' ) ) {
				$item[ $key ] = array(
					'value'   => 'wpei wpei-' . $item[ $key ]['value'],
					'library' => 'wpessential',
				);
			} elseif ( 'toggle' === $f['type'] ) {
				$item[ $key ] = $item[ $key ] ? 'yes' : '';
			}
		}
		return $item;
	}

	/**
	 * Slider range for one unit, scaled from the option's own range.
	 *
	 * @param array  $a    Option.
	 * @param string $unit Unit.
	 * @return array
	 */
	private static function unit_range( array $a, $unit ) {
		$own = $a['unit'] ?? 'px';
		if ( $unit === $own ) {
			return array(
				'min'  => $a['min'] ?? 0,
				'max'  => $a['max'] ?? 200,
				'step' => $a['step'] ?? 1,
			);
		}
		if ( in_array( $unit, array( 'em', 'rem' ), true ) ) {
			return array(
				'min'  => 0,
				'max'  => max( 1, round( ( $a['max'] ?? 200 ) / 16, 1 ) ),
				'step' => 0.1,
			);
		}
		return array(
			'min'  => 0,
			'max'  => 100,
			'step' => 1,
		);
	}

	/**
	 * Same output as the block.
	 */
	protected function render() {
		echo Kit::render( static::KIT, Kit::from_elementor( $this->def(), $this->get_settings_for_display() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in render.php.
	}
}
