<?php
/**
 * WPEssential kit: our own elements, each defined once in kit/<name>/element.json and
 * shipped as a Gutenberg block (wpessential/<name>) and an Elementor widget (wpe-<name>) with the
 * same options, the same render (kit/<name>/render.php) and the theme's breakpoints.
 *
 * Option types in element.json ("attributes"):
 *   text, textarea, url, select, toggle, range, color (theme palette slug).
 *   "responsive": true stores one value per breakpoint: { "base": …, "1920": …, "1280": …, … }.
 *   "css": "--var" turns the value into a CSS custom property on the element (with "unit");
 *   "map": { value: "--a:x;--b:y" } sets several properties for one choice.
 * "groups" add typography, spacing, border, shadow, background and colour groups (Kit\Groups);
 * every block also gets the common Advanced options (Elementor has its own Advanced tab).
 *
 * @package WPEssential
 */

namespace WPEssential\Platform\Kit;

use WPEssential\Platform\Admin\Elements\ElementRegistry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kit loader and shared render helpers.
 */
class Kit {

	/**
	 * Breakpoints of the theme (assets/css/responsive.css): key => media query. "base" is 1440.
	 */
	const BREAKPOINTS = array(
		'1920' => '(min-width: 1920px)',
		'1280' => '(max-width: 1439px)',
		'1024' => '(max-width: 1279px)',
		'768'  => '(max-width: 1023px)',
		'560'  => '(max-width: 767px)',
		'480'  => '(max-width: 559px)',
		'360'  => '(max-width: 479px)',
		'320'  => '(max-width: 359px)',
	);

	/**
	 * Elementor device => our breakpoint key. Elementor has at most seven devices, so 360 and 320
	 * follow 480 there; Gutenberg has all of them.
	 */
	const ELEMENTOR_DEVICES = array(
		'widescreen'   => '1920',
		'laptop'       => '1280',
		'tablet_extra' => '1024',
		'tablet'       => '768',
		'mobile_extra' => '560',
		'mobile'       => '480',
	);

	/**
	 * Loaded definitions.
	 *
	 * @var array|null
	 */
	private static $defs = null;

	/**
	 * Units offered for every length option (px options get all of them).
	 */
	const LENGTH_UNITS = array( 'px', 'em', 'rem', '%', 'vw', 'vh' );

	/**
	 * Icon set (kit/icons.json): name => SVG body.
	 *
	 * @var array|null
	 */
	private static $icons = null;

	/**
	 * Free plugin folder and URL (kit/ lives there), and the asset version.
	 *
	 * @var string
	 */
	private static $dir = '';

	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	private static $base_url = '';

	/**
	 * Asset version.
	 *
	 * @var string
	 */
	private static $version = '';

	/**
	 * Site-wide element switches (Elements screen).
	 *
	 * @var ElementRegistry|null
	 */
	private static $registry = null;

	/**
	 * Wire the kit to the Free plugin and hook it into WordPress.
	 *
	 * @param string          $dir      Free plugin folder.
	 * @param string          $url      Free plugin URL (trailing slash).
	 * @param string          $version  Asset version.
	 * @param ElementRegistry $registry Element switches.
	 */
	public static function boot( $dir, $url, $version, ElementRegistry $registry ) {
		self::$dir      = rtrim( (string) $dir, '/' );
		self::$base_url = (string) $url;
		self::$version  = (string) $version;
		self::$registry = $registry;
		self::$defs     = null;
		if ( function_exists( 'add_action' ) && function_exists( 'add_filter' ) ) {
			self::hooks();
		}
	}

	/**
	 * URL of a file in the Free plugin.
	 *
	 * @param string $path Relative path.
	 * @return string
	 */
	private static function url( $path ) {
		return self::$base_url . ltrim( (string) $path, '/' );
	}

	/**
	 * Folders that hold elements. Free's own kit/ is always first; WPEssential Pro adds its kit/
	 * through the `wpessential_kit_roots` filter (dir, url, edition, namespace of its widgets).
	 *
	 * @return array<int,array{dir:string,url:string,edition:string,namespace:string}>
	 */
	public static function roots() {
		$roots = array(
			array(
				'dir'       => self::$dir . '/kit',
				'url'       => self::url( 'kit' ),
				'edition'   => 'free',
				'namespace' => __NAMESPACE__ . '\\Elements',
			),
		);
		$extra = function_exists( 'apply_filters' ) ? apply_filters( 'wpessential_kit_roots', array() ) : array();
		foreach ( is_array( $extra ) ? $extra : array() as $root ) {
			if ( is_array( $root ) && is_string( $root['dir'] ?? null ) && is_string( $root['url'] ?? null ) && is_dir( $root['dir'] ) ) {
				$roots[] = array(
					'dir'       => rtrim( $root['dir'], '/' ),
					'url'       => rtrim( $root['url'], '/' ),
					'edition'   => 'pro' === ( $root['edition'] ?? '' ) ? 'pro' : 'free',
					'namespace' => is_string( $root['namespace'] ?? null ) ? $root['namespace'] : '',
				);
			}
		}
		return $roots;
	}

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
		add_filter( 'elementor/icons_manager/additional_tabs', array( __CLASS__, 'elementor_icons' ) );
		add_action(
			'enqueue_block_editor_assets',
			static function () {
				wp_enqueue_style( 'wpessential-kit-editor', self::url( 'kit/editor/editor.css' ), array(), self::$version );
			}
		);
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'elementor_category' ) );
		add_filter( 'block_categories_all', array( __CLASS__, 'block_category' ) );
		add_filter( 'wpessential_elements', array( __CLASS__, 'elements' ) );
		// The Hi WPEssential theme bundles an older copy of this kit (and its demo importer) in
		// core/. With WPEssential active the plugin owns them, so the theme must not load its copy.
		add_filter( 'wpessential_load_bundled_core', '__return_false' );
	}

	/**
	 * Definitions switched on for one builder (Elements screen), keyed by name.
	 *
	 * @param string $type gutenberg|elementor.
	 * @return array
	 */
	public static function enabled_defs( $type = 'gutenberg' ) {
		$out = array();
		foreach ( self::defs() as $name => $def ) {
			if ( null === self::$registry || self::$registry->isEnabled( $type, $name ) ) {
				$out[ $name ] = $def;
			}
		}
		return $out;
	}

	/**
	 * Kit elements for the Elements screen (`wpessential_elements`): one entry per builder.
	 * Pro elements are listed even without WPEssential Pro (kit/pro-catalog.json), so the
	 * screen can show them as Pro.
	 *
	 * @param mixed $elements Elements from other modules.
	 * @return array
	 */
	public static function elements( $elements ) {
		$elements = is_array( $elements ) ? $elements : array();
		$items    = array();
		foreach ( self::defs() as $name => $def ) {
			$items[ $name ] = array(
				'title'       => (string) $def['title'],
				'description' => (string) ( $def['description'] ?? '' ),
				'icon'        => (string) ( $def['icon'] ?? 'layers' ),
				'edition'     => (string) $def['edition'],
			);
		}
		$catalog = self::$dir . '/kit/pro-catalog.json';
		$pro     = is_readable( $catalog ) ? json_decode( (string) file_get_contents( $catalog ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
		foreach ( is_array( $pro ) ? $pro : array() as $name => $item ) {
			if ( is_string( $name ) && is_array( $item ) && ! isset( $items[ $name ] ) ) {
				$items[ $name ] = array(
					'title'       => (string) ( $item['title'] ?? $name ),
					'description' => (string) ( $item['description'] ?? '' ),
					'icon'        => (string) ( $item['icon'] ?? 'layers' ),
					'edition'     => 'pro',
				);
			}
		}
		foreach ( $items as $name => $item ) {
			foreach ( array( 'gutenberg', 'elementor' ) as $type ) {
				$elements[] = array(
					'id'          => $name,
					'name'        => trim( str_replace( '(WPEssential)', '', $item['title'] ) ),
					'type'        => $type,
					'description' => $item['description'],
					'icon'        => $item['icon'],
					'edition'     => $item['edition'],
				);
			}
		}
		return $elements;
	}

	/**
	 * The WPEssential icon set.
	 *
	 * @return array
	 */
	public static function icons() {
		if ( null === self::$icons ) {
			self::$icons = (array) json_decode( (string) file_get_contents( self::$dir . '/kit/icons.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
		}
		return self::$icons;
	}

	/**
	 * Units a range option offers: all length units when its unit is a length, else its own unit.
	 *
	 * @param array $opt Option.
	 * @return string[]
	 */
	public static function units( array $opt ) {
		$unit = $opt['unit'] ?? 'px';
		if ( isset( $opt['units'] ) ) {
			return (array) $opt['units'];
		}
		if ( ! empty( $opt['fluid'] ) || ! in_array( $unit, array_merge( self::LENGTH_UNITS, array( 'ch' ) ), true ) ) {
			return array( $unit );
		}
		return array_values( array_unique( array_merge( array( $unit ), self::LENGTH_UNITS ) ) );
	}

	/**
	 * Our icon set as a tab in Elementor's icon library.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public static function elementor_icons( $tabs ) {
		$tabs['wpessential'] = array(
			'name'          => 'wpessential',
			'label'         => __( 'WPEssential', 'wpessential' ),
			'url'           => self::url( 'kit/icons.css' ),
			'enqueue'       => array(),
			'prefix'        => 'wpei-',
			'displayPrefix' => 'wpei',
			'labelIcon'     => 'wpei wpei-spark',
			'ver'           => self::$version,
			'fetchJson'     => self::url( 'kit/icons-elementor.json' ),
			'native'        => false,
		);
		return $tabs;
	}

	/**
	 * Icon markup for an icon value: { library, value } from either builder.
	 * Our set and Elementor's "wpei" tab print inline SVG; uploaded SVG/images print <img>;
	 * other Elementor libraries (Font Awesome…) go through Elementor.
	 *
	 * @param mixed  $icon      Value.
	 * @param string $css_class CSS class.
	 * @return string
	 */
	public static function icon_html( $icon, $css_class = 'wpe-icon' ) {
		if ( is_string( $icon ) ) {
			$legacy = array(
				'arrow' => 'arrow-up-right',
				'right' => 'arrow-right',
			);
			$icon   = array(
				'library' => 'wpessential',
				'value'   => $legacy[ $icon ] ?? $icon,
			);
		}
		if ( ! is_array( $icon ) || empty( $icon['value'] ) ) {
			return '';
		}
		$lib   = (string) ( $icon['library'] ?? 'wpessential' );
		$value = $icon['value'];
		if ( 'svg' === $lib || 'upload' === $lib ) {
			$url = is_array( $value ) ? ( $value['url'] ?? '' ) : (string) $value;
			return $url ? '<img class="' . esc_attr( $css_class ) . ' is-upload" src="' . esc_url( $url ) . '" alt="" aria-hidden="true" width="24" height="24">' : '';
		}
		$name = is_string( $value ) ? preg_replace( '/^wpei\s+wpei-/', '', $value ) : '';
		if ( 'wpessential' === $lib && isset( self::icons()[ $name ] ) ) {
			return '<svg class="' . esc_attr( $css_class ) . '" viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . self::icons()[ $name ] . '</svg>';
		}
		if ( class_exists( '\Elementor\Icons_Manager' ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon(
				$icon,
				array(
					'aria-hidden' => 'true',
					'class'       => $css_class,
				)
			);
			return (string) ob_get_clean();
		}
		return '';
	}

	/**
	 * Every element definition, keyed by name.
	 *
	 * @return array
	 */
	public static function defs() {
		if ( null === self::$defs ) {
			self::$defs = array();
			foreach ( self::roots() as $root ) {
				foreach ( (array) glob( $root['dir'] . '/*/element.json' ) as $file ) {
					$def = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
					if ( is_array( $def ) && ! empty( $def['name'] ) && ! isset( self::$defs[ $def['name'] ] ) ) {
						$def['dir']                 = dirname( $file );
						$def['url']                 = $root['url'] . '/' . basename( $def['dir'] );
						$def['edition']             = $root['edition'];
						$def['namespace']           = $root['namespace'];
						self::$defs[ $def['name'] ] = $def;
					}
				}
			}
		}
		return self::$defs;
	}

	/**
	 * Default value of every attribute.
	 *
	 * @param array $def Definition.
	 * @return array
	 */
	public static function defaults( array $def ) {
		$out = array();
		foreach ( self::groups( $def ) as $key => $g ) {
			$out[ $key ] = array();
		}
		foreach ( self::attributes( $def ) as $key => $a ) {
			$value       = $a['default'] ?? ( 'toggle' === $a['type'] ? false : '' );
			$out[ $key ] = ! empty( $a['responsive'] ) ? array( 'base' => $value ) : $value;
		}
		return $out;
	}

	/**
	 * Element options plus the common Advanced options.
	 *
	 * @param array $def Definition.
	 * @return array
	 */
	public static function attributes( array $def ) {
		return $def['attributes'] + Groups::common()[0];
	}

	/**
	 * Element groups plus the common Advanced groups.
	 *
	 * @param array $def Definition.
	 * @return array
	 */
	public static function groups( array $def ) {
		return ( $def['groups'] ?? array() ) + Groups::common()[1];
	}

	/**
	 * Gutenberg category.
	 *
	 * @param array $cats Categories.
	 * @return array
	 */
	public static function block_category( $cats ) {
		array_unshift(
			$cats,
			array(
				'slug'  => 'wpessential',
				'title' => __( 'WPEssential', 'wpessential' ),
			)
		);
		return $cats;
	}

	/**
	 * Elementor category.
	 *
	 * @param \Elementor\Elements_Manager $manager Manager.
	 */
	public static function elementor_category( $manager ) {
		$manager->add_category(
			'wpessential',
			array(
				'title' => __( 'WPEssential', 'wpessential' ),
				'icon'  => 'eicon-star',
			)
		);
	}

	/**
	 * Register every element as a block, plus the shared editor script and styles.
	 */
	public static function register_blocks() {
		wp_register_script( 'wpessential-kit-editor', self::url( 'kit/editor/editor.js' ), array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n', 'wp-data' ), self::$version, true );
		wp_register_script( 'wpessential-kit-motion', self::url( 'kit/motion.js' ), array(), self::$version, true );
		wp_register_script( 'wpessential-kit-motion-layout', self::url( 'kit/motion-layout.js' ), array(), self::$version, true );
		wp_register_style( 'wpessential-kit-common', self::url( 'kit/common.css' ), array(), self::$version );
		$editor = array();
		foreach ( self::enabled_defs( 'gutenberg' ) as $name => $def ) {
			$handle = 'wpessential-kit-' . $name;
			wp_register_style( $handle, $def['url'] . '/style.css', array( 'wpessential-kit-common' ), self::$version );
			// Optional behaviour: kit/<name>/script.js, loaded only on pages that use the element.
			if ( file_exists( $def['dir'] . '/script.js' ) ) {
				wp_register_script( $handle, $def['url'] . '/script.js', array(), self::$version, true );
			}
			$attrs = array();
			foreach ( self::attributes( $def ) as $key => $a ) {
				// Responsive controls save an object keyed by breakpoint; repeaters save a list.
				$attrs[ $key ] = ! empty( $a['responsive'] ) ? array( 'type' => 'object' ) : ( 'repeater' === $a['type'] ? array( 'type' => 'array' ) : ( in_array( $a['type'], array( 'breakpoints', 'image', 'icon' ), true ) ? array( 'type' => 'object' ) : array( 'type' => 'toggle' === $a['type'] ? 'boolean' : ( 'range' === $a['type'] ? array( 'number', 'string' ) : 'string' ) ) ) );
			}
			foreach ( self::attributes( $def ) as $key => $a ) {
				// Defaults in the block too, so the sidebar shows the value the page really uses.
				if ( isset( $a['default'] ) && '' !== $a['default'] && array() !== $a['default'] && empty( $a['responsive'] ) ) {
					$attrs[ $key ]['default'] = $a['default'];
				}
			}
			foreach ( self::groups( $def ) as $key => $g ) {
				$attrs[ $key ] = array( 'type' => 'object' );
			}
			register_block_type(
				'wpessential/' . $name,
				array(
					'api_version'     => '3',
					'title'           => $def['title'],
					'category'        => 'wpessential',
					'icon'            => $def['icon'] ?? 'star-filled',
					'keywords'        => $def['keywords'] ?? array(),
					'attributes'      => $attrs,
					'supports'        => array(
						'anchor' => true,
						'html'   => false,
					),
					'style_handles'   => array( $handle ),
					'editor_script'   => 'wpessential-kit-editor',
					'render_callback' => static function ( $attributes, $content = '' ) use ( $name ) {
						return self::render( $name, (array) $attributes, (string) $content );
					},
				)
			);
			$editor[ $name ] = array_merge(
				array_intersect_key( $def, array_flip( array( 'name', 'title', 'icon' ) ) ),
				array(
					'attributes' => self::attributes( $def ),
					'groups'     => self::groups( $def ),
					'panels'     => ( $def['panels'] ?? array( 'content' => __( 'Content', 'wpessential' ) ) ) + array( 'advanced' => __( 'Layout & effects', 'wpessential' ) ),
				)
			);
		}
		wp_add_inline_script(
			'wpessential-kit-editor',
			'window.wpessentialKit = ' . wp_json_encode(
				array(
					'elements'    => $editor,
					'breakpoints' => array_merge( array( 'base' ), array_map( 'strval', array_keys( self::BREAKPOINTS ) ) ),
					'palette'     => self::palette(),
					'groupFields' => Groups::fields(),
					'icons'       => self::icons(),
					'lengthUnits' => self::LENGTH_UNITS,
				)
			) . ';',
			'before'
		);
	}

	/**
	 * Theme palette slugs and names (colour options use slugs, so light/dark both work).
	 *
	 * @return array
	 */
	public static function palette() {
		$out = array();
		if ( function_exists( 'wp_get_global_settings' ) ) {
			foreach ( (array) wp_get_global_settings( array( 'color', 'palette', 'theme' ) ) as $c ) {
				$out[ $c['slug'] ] = $c['name'];
			}
		}
		return $out;
	}

	/**
	 * Register every element as an Elementor widget.
	 *
	 * @param \Elementor\Widgets_Manager $manager Manager.
	 */
	public static function register_widgets( $manager ) {
		foreach ( self::enabled_defs( 'elementor' ) as $name => $def ) {
			$class = $def['namespace'] . '\\' . str_replace( ' ', '', ucwords( str_replace( '-', ' ', $name ) ) );
			if ( class_exists( $class ) ) {
				$manager->register( new $class() );
			}
		}
	}

	/**
	 * Render an element from saved attributes (block) or mapped settings (widget).
	 *
	 * @param string $name       Element.
	 * @param array  $attributes Values.
	 * @param string $content    Already-rendered inner blocks (for nested container).
	 * @return string
	 */
	public static function render( $name, array $attributes, $content = '' ) {
		$defs = self::defs();
		if ( empty( $defs[ $name ] ) ) {
			return '';
		}
		$def   = $defs[ $name ];
		$a     = array();
		$attrs = self::attributes( $def );
		foreach ( self::defaults( $def ) as $key => $default ) {
			$value = $attributes[ $key ] ?? $default;
			// A list of items is taken as saved: merging it with the default items would bring them back.
			if ( 'repeater' === ( $attrs[ $key ]['type'] ?? '' ) ) {
				$a[ $key ] = is_array( $value ) ? array_values( array_filter( $value, 'is_array' ) ) : array();
				continue;
			}
			$a[ $key ] = is_array( $default ) ? array_replace( $default, array_filter( (array) $value, static fn( $v ) => '' !== $v && null !== $v ) ) : $value;
		}
		// Server-side conditions must suppress the full HTML (and its dynamic CSS).
		// Device breakpoints are presentation-only CSS and are not an access control.
		if ( ! self::display_allowed( $a ) ) {
			return '';
		}
		wp_enqueue_style( 'wpessential-kit-' . $name );
		if ( wp_script_is( 'wpessential-kit-' . $name, 'registered' ) ) {
			wp_enqueue_script( 'wpessential-kit-' . $name );
		}
		if ( ! empty( $a['animation'] ) && in_array( (string) ( $a['animTrigger'] ?? '' ), array( 'scroll', 'hover', 'click', 'load' ), true ) ) {
			wp_enqueue_script( 'wpessential-kit-motion' );
		}
		if ( ! empty( $a['parallaxEnabled'] ) || ( ! empty( $a['stickyEnabled'] ) && ! empty( $a['stickyStopId'] ) ) ) {
			wp_enqueue_script( 'wpessential-kit-motion-layout' );
		}
		$id   = 'wpe-' . $name . '-' . substr( md5( wp_json_encode( $a ) . wp_rand() ), 0, 8 );
		$css  = self::css( $def, $a, $id );
		$html = self::template( $def['dir'] . '/render.php', $a, $id, $content );
		return ( $css ? '<style>' . $css . '</style>' : '' ) . self::root_extras( $html, $id, $a );
	}

	/**
	 * Evaluate visibility gates before rendering any widget markup.
	 *
	 * Rules are ANDed. Invalid configured values fail closed rather than leaking
	 * content. Rules are for presentation, NOT authorization or private data.
	 *
	 * @param array $a Saved attributes.
	 * @return bool
	 */
	private static function display_allowed( array $a ) {
		$wpe_login = (string) ( $a['conditionLogin'] ?? '' );
		$wpe_role  = trim( (string) ( $a['conditionRole'] ?? '' ) );
		$wpe_cart  = (string) ( $a['conditionCart'] ?? '' );
		$wpe_buy   = trim( (string) ( $a['conditionPurchasedProduct'] ?? '' ) );
		$wpe_start = trim( (string) ( $a['conditionStart'] ?? '' ) );
		$wpe_end   = trim( (string) ( $a['conditionEnd'] ?? '' ) );
		$wpe_path  = trim( (string) ( $a['conditionPath'] ?? '' ) );
		$wpe_key   = trim( (string) ( $a['conditionQueryKey'] ?? '' ) );
		$wpe_value = (string) ( $a['conditionQueryValue'] ?? '' );
		$wpe_host  = trim( (string) ( $a['conditionReferrer'] ?? '' ) );

		// Pages containing visitor-specific server rendering must not be cached
		// across different visitors by caches honouring DONOTCACHEPAGE.
		if ( '' !== $wpe_login || '' !== $wpe_role || '' !== $wpe_cart || '' !== $wpe_buy || '' !== $wpe_start || '' !== $wpe_end || '' !== $wpe_path || '' !== $wpe_key || '' !== $wpe_host ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Standard WordPress cache bypass constant.
			}
		}

		if ( '' !== $wpe_login ) {
			if ( ! in_array( $wpe_login, array( 'in', 'out' ), true ) || ( 'in' === $wpe_login ) !== is_user_logged_in() ) {
				return false;
			}
		}
		if ( '' !== $wpe_role ) {
			if ( ! preg_match( '/^[a-z0-9_-]+$/', $wpe_role ) || ! is_user_logged_in() ) {
				return false;
			}
			$wpe_user = wp_get_current_user();
			if ( ! $wpe_user || ! in_array( $wpe_role, (array) $wpe_user->roles, true ) ) {
				return false;
			}
		}
		if ( '' !== $wpe_cart ) {
			if ( ! in_array( $wpe_cart, array( 'empty', 'nonempty' ), true ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
				return false;
			}
			$wpe_has_items = (int) WC()->cart->get_cart_contents_count() > 0;
			if ( ( 'nonempty' === $wpe_cart ) !== $wpe_has_items ) {
				return false;
			}
		}
		if ( '' !== $wpe_buy ) {
			if ( ! ctype_digit( $wpe_buy ) || ! absint( $wpe_buy ) || ! is_user_logged_in() || ! function_exists( 'wc_customer_bought_product' ) ) {
				return false;
			}
			$wpe_user = wp_get_current_user();
			if ( ! wc_customer_bought_product( (string) $wpe_user->user_email, (int) $wpe_user->ID, absint( $wpe_buy ) ) ) {
				return false;
			}
		}
		if ( '' !== $wpe_start || '' !== $wpe_end ) {
			$wpe_now = current_datetime();
			foreach ( array( $wpe_start, $wpe_end ) as $wpe_date ) {
				if ( '' === $wpe_date ) {
					continue;
				}
				$wpe_parsed = \DateTimeImmutable::createFromFormat( '!Y-m-d H:i', $wpe_date, wp_timezone() );
				if ( ! $wpe_parsed || $wpe_parsed->format( 'Y-m-d H:i' ) !== $wpe_date ) {
					return false;
				}
				if ( ( $wpe_date === $wpe_start && $wpe_now < $wpe_parsed ) || ( $wpe_date === $wpe_end && $wpe_now > $wpe_parsed ) ) {
					return false;
				}
			}
		}
		if ( '' !== $wpe_path ) {
			if ( 0 !== strpos( $wpe_path, '/' ) || preg_match( '/[\s<>\\\\]/', $wpe_path ) ) {
				return false;
			}
			$wpe_actual = wp_parse_url( sanitize_text_field( wp_unslash( (string) ( $_SERVER['REQUEST_URI'] ?? '/' ) ) ), PHP_URL_PATH );
			if ( ! is_string( $wpe_actual ) || $wpe_path !== $wpe_actual ) {
				return false;
			}
		}
		if ( '' !== $wpe_key ) {
			// Read-only GET checks control presentation; no state mutation requires a nonce.
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( sanitize_key( $wpe_key ) !== $wpe_key || ! array_key_exists( $wpe_key, $_GET ) || ! is_scalar( $_GET[ $wpe_key ] ) ) {
				return false;
			}
			if ( '' !== $wpe_value && sanitize_text_field( wp_unslash( (string) $_GET[ $wpe_key ] ) ) !== $wpe_value ) {
				return false;
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		} elseif ( '' !== $wpe_value ) {
			return false;
		}
		if ( '' !== $wpe_host ) {
			if ( ! preg_match( '/^[a-z0-9.-]+$/i', $wpe_host ) ) {
				return false;
			}
			$wpe_ref      = sanitize_url( wp_unslash( (string) ( $_SERVER['HTTP_REFERER'] ?? '' ) ) );
			$wpe_ref_host = wp_parse_url( $wpe_ref, PHP_URL_HOST );
			if ( ! is_string( $wpe_ref_host ) || strtolower( $wpe_ref_host ) !== strtolower( $wpe_host ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Common options that live on the root tag: extra classes, custom attributes, transform and
	 * entrance animation classes.
	 *
	 * @param string $html Markup.
	 * @param string $id   Root id.
	 * @param array  $a    Values.
	 * @return string
	 */
	private static function root_extras( $html, $id, array $a ) {
		$classes = array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', (string) ( $a['cssClass'] ?? '' ) ) ) );
		foreach ( array( 'rotate', 'scale', 'moveX', 'moveY', 'hoverRotate', 'hoverScale', 'hoverMoveY' ) as $t ) {
			if ( isset( $a[ $t ] ) && '' !== $a[ $t ] ) {
				$classes[] = 'wpek-tf';
				break;
			}
		}
		foreach ( array( 'transition', 'transitionEase', 'transitionDelay' ) as $t ) {
			if ( ! empty( $a[ $t ] ) ) {
				$classes[] = 'wpek-tr';
				break;
			}
		}
		if ( ! empty( $a['hoverPreset'] ) ) {
			$classes[] = 'wpek-hp-' . sanitize_html_class( $a['hoverPreset'] );
		}
		if ( ! empty( $a['animation'] ) && isset( Groups::animations()[ $a['animation'] ] ) ) {
			$classes[] = 'wpek-anim';
			$classes[] = 'wpek-anim-' . sanitize_html_class( $a['animation'] );
		}
		$wpe_anim = (string) ( $a['animation'] ?? '' );
		if ( '' !== $wpe_anim && isset( Groups::animations()[ $wpe_anim ] ) ) {
			$wpe_trigger = (string) ( $a['animTrigger'] ?? '' );
			if ( in_array( $wpe_trigger, array( 'scroll', 'hover', 'click', 'load' ), true ) ) {
				$classes[] = 'wpek-motion-trigger-' . $wpe_trigger;
			}
			if ( 'fade' === (string) ( $a['animReduced'] ?? '' ) ) {
				$classes[] = 'wpek-motion-reduced-fade';
			}
			if ( ! empty( $a['staggerChildren'] ) ) {
				$classes[] = 'wpek-stagger';
			}
		}
		if ( ! empty( $a['parallaxEnabled'] ) ) {
			$classes[] = 'wpek-parallax';
			if ( 'x' === (string) ( $a['parallaxAxis'] ?? '' ) ) {
				$classes[] = 'wpek-parallax-x';
			}
		}
		if ( ! empty( $a['stickyEnabled'] ) ) {
			$classes[]       = 'wpek-sticky';
			$wpe_sticky_from = (string) ( $a['stickyFrom'] ?? '' );
			if ( in_array( $wpe_sticky_from, array( 'tablet', 'desktop' ), true ) ) {
				$classes[] = 'wpek-sticky-from-' . $wpe_sticky_from;
			}
		}
		$wpe_device = (string) ( $a['conditionDevice'] ?? '' );
		if ( in_array( $wpe_device, array( 'mobile', 'tablet', 'desktop' ), true ) ) {
			$classes[] = 'wpek-device-' . $wpe_device;
		}
		if ( ! empty( $a['containerQuerySource'] ) ) {
			$classes[] = 'wpek-query-container';
		}
		if ( ! empty( $a['effectGradientBorder'] ) ) {
			$classes[] = 'wpek-gradient-border';
			if ( ! empty( $a['effectBorderSpin'] ) ) {
				$classes[] = 'wpek-gradient-border-animated';
			}
		}
		$wpe_blend = (string) ( $a['effectBlend'] ?? '' );
		if ( in_array( $wpe_blend, array( 'multiply', 'screen', 'overlay', 'darken', 'lighten', 'difference', 'exclusion' ), true ) ) {
			$classes[] = 'wpek-blend-' . $wpe_blend;
		}
		$wpe_mask = (string) ( $a['effectMask'] ?? '' );
		if ( in_array( $wpe_mask, array( 'top', 'bottom', 'left', 'right', 'vertical', 'horizontal' ), true ) ) {
			$classes[] = 'wpek-mask-' . $wpe_mask;
		}
		$wpe_clip = (string) ( $a['clipShape'] ?? '' );
		if ( in_array( $wpe_clip, array( 'slant', 'wave', 'blob' ), true ) ) {
			$classes[] = 'wpek-clip-' . $wpe_clip;
		}
		if ( ! empty( $a['grainEnabled'] ) ) {
			$classes[] = 'wpek-grain';
		}
		$wpe_has_elevation = false;
		foreach (
			array(
				'elevationRest'  => 'rest',
				'elevationHover' => 'hover',
				'elevationFocus' => 'focus',
			) as $wpe_key => $wpe_state
		) {
			$wpe_tier = (string) ( $a[ $wpe_key ] ?? '' );
			if ( in_array( $wpe_tier, array( 'sm', 'md', 'lg' ), true ) ) {
				$classes[]         = 'wpek-elev-' . $wpe_state . '-' . $wpe_tier;
				$wpe_has_elevation = true;
			}
		}
		if ( $wpe_has_elevation ) {
			$classes[] = 'wpek-elevation';
		}
		if ( ! empty( $a['glass'] ) ) {
			$classes[] = 'wpek-glass';
		}
		if ( ! empty( $a['hidePrint'] ) ) {
			$classes[] = 'wpek-hide-print';
		}
		$wpe_focus = ! empty( $a['focusRingStyle'] ) || ! empty( $a['focusRingColor'] );
		foreach ( array( 'focusRingWidth', 'focusRingOffset' ) as $wpe_key ) {
			if ( ! empty( array_filter( (array) ( $a[ $wpe_key ] ?? array() ), static fn( $v ) => '' !== $v && null !== $v ) ) ) {
				$wpe_focus = true;
			}
		}
		if ( $wpe_focus ) {
			$classes[] = 'wpek-focus-ring';
		}
		$attrs = '';
		$aria  = array(
			'role'        => (string) ( $a['rootRole'] ?? '' ),
			// A label on a plain div means nothing to screen readers, so it needs a role.
			'aria-label'  => empty( $a['rootRole'] ) ? '' : (string) ( $a['rootAriaLabel'] ?? '' ),
			'aria-hidden' => empty( $a['rootAriaHidden'] ) ? '' : 'true',
			'aria-live'   => (string) ( $a['rootAriaLive'] ?? '' ),
		);
		// Only strict same-page element IDs are accepted; no CSS selectors or HTML.
		if ( ! empty( $a['stickyEnabled'] ) && ! empty( $a['stickyStopId'] ) ) {
			$wpe_stop_id = trim( (string) $a['stickyStopId'] );
			if ( preg_match( '/^[A-Za-z][A-Za-z0-9_-]{0,63}$/D', $wpe_stop_id ) ) {
				$classes[] = 'wpek-sticky-stop';
				$attrs    .= ' data-wpek-sticky-stop="' . esc_attr( $wpe_stop_id ) . '"';
			}
		}
		foreach ( array_filter( $aria ) as $k => $v ) {
			$attrs .= ' ' . $k . '="' . esc_attr( $v ) . '"';
		}
		foreach ( preg_split( '/\r?\n/', (string) ( $a['attributes'] ?? '' ) ) as $line ) {
			$pair = array_map( 'trim', explode( '|', $line, 2 ) );
			$key  = strtolower( preg_replace( '/[^a-zA-Z0-9_\-:]/', '', $pair[0] ) );
			if ( '' === $key || 0 === strpos( $key, 'on' ) || in_array( $key, array( 'id', 'class', 'style' ), true ) ) {
				continue; // No event handlers or overrides of what the element owns.
			}
			$attrs .= ' ' . $key . '="' . esc_attr( $pair[1] ?? '' ) . '"';
		}
		if ( ! $classes && '' === $attrs ) {
			return $html;
		}
		$pos = strpos( $html, 'id="' . $id . '"' );
		if ( false === $pos ) {
			return $html;
		}
		$tag_end = strpos( $html, '>', $pos );
		$tag     = substr( $html, $pos, $tag_end - $pos );
		if ( $classes ) {
			$tag = false !== strpos( $tag, 'class="' ) ? preg_replace( '/class="/', 'class="' . esc_attr( implode( ' ', $classes ) ) . ' ', $tag, 1 ) : $tag . ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
		}
		return substr( $html, 0, $pos ) . $tag . $attrs . substr( $html, $tag_end );
	}

	/**
	 * Run an element's render.php with its values ($a) and element id ($id) in scope.
	 *
	 * @param string $file Template.
	 * @param array  $a    Values.
	 * @param string $id      Element id.
	 * @param string $content Rendered inner-block content when supplied.
	 * @return string
	 */
	private static function template( $file, array $a, $id, $content = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Used by the included template.
		ob_start();
		include $file;
		return (string) ob_get_clean();
	}

	/**
	 * CSS custom properties per breakpoint for options with "css".
	 *
	 * @param array  $def Definition.
	 * @param array  $a   Values.
	 * @param string $id  Element id.
	 * @return string
	 */
	private static function css( array $def, array $a, $id ) {
		$root  = '#' . $id;
		$rules = array( 'base' => array() );
		foreach ( self::attributes( $def ) as $key => $opt ) {
			if ( 'breakpoints' === $opt['type'] ) {
				foreach ( (array) ( $a[ $key ] ?? array() ) as $bp => $on ) {
					if ( $on ) {
						$rules[ (string) $bp ][ $root ][] = 'display:none';
					}
				}
				continue;
			}
			if ( empty( $opt['css'] ) ) {
				continue;
			}
			$sel    = empty( $opt['state'] ) ? $root : $root . ':hover,' . $root . ':focus-within';
			$values = ! empty( $opt['responsive'] ) ? (array) $a[ $key ] : array( 'base' => $a[ $key ] );
			foreach ( $values as $bp => $value ) {
				if ( '' === $value || null === $value || false === $value ) {
					continue;
				}
				// Directly loaded saved attributes cannot bypass the visual-effect ranges.
				$wpe_effect_ranges = array(
					'effectBorderWidth' => array( 1, 16 ),
					'effectBorderAngle' => array( 0, 360 ),
					'effectBorderSpeed' => array( 1500, 12000 ),
				);
				if ( isset( $wpe_effect_ranges[ $key ] ) && ( ! is_numeric( $value ) || (float) $value < $wpe_effect_ranges[ $key ][0] || (float) $value > $wpe_effect_ranges[ $key ][1] ) ) {
					continue;
				}
				// Saved range attributes are untrusted; clip visual intensity to 1–20%.
				if ( 'grainOpacity' === $key && ( ! is_numeric( $value ) || (float) $value < 1 || (float) $value > 20 ) ) {
					continue;
				}
				// Never emit arbitrary or unbounded parallax travel values.
				if ( 0 === strpos( $key, 'parallaxLayer' ) && ( ! is_numeric( $value ) || abs( (float) $value ) > 240 ) ) {
					continue;
				}
				// "map" turns one choice into several declarations, e.g. center => text-align + auto margins.
				$wpe_decl = isset( $opt['map'][ $value ] ) ? preg_replace( '/[^a-z0-9:;%#.,() \\-]/i', '', $opt['map'][ $value ] ) : $opt['css'] . ':' . self::css_value( $opt, $value );

				$rules[ (string) $bp ][ $sel ][] = $wpe_decl . ( ! empty( $opt['important'] ) ? '!important' : '' );
			}
		}
		foreach ( self::groups( $def ) as $key => $group ) {
			if ( ! empty( $a[ $key ] ) && is_array( $a[ $key ] ) ) {
				Groups::css( $group, $a[ $key ], $root, $rules );
			}
		}
		// Fluid padding/gap interpolates from viewport 360px to 1440px.
		// Only paired, finite and bounded numbers are accepted; never emit user CSS.
		foreach (
			array(
				'Padding' => 'padding',
				'Gap'     => 'gap',
			) as $wpe_suffix => $wpe_property
		) {
			$wpe_small = $a[ 'fluid' . $wpe_suffix . 'Min' ] ?? '';
			$wpe_large = $a[ 'fluid' . $wpe_suffix . 'Max' ] ?? '';
			if ( ! is_numeric( $wpe_small ) || ! is_numeric( $wpe_large ) ) {
				continue;
			}
			$wpe_min = (float) $wpe_small;
			$wpe_max = (float) $wpe_large;
			if ( ! is_finite( $wpe_min ) || ! is_finite( $wpe_max ) || $wpe_min < 0 || $wpe_max > 260 || $wpe_max < $wpe_min ) {
				continue;
			}
			$wpe_slope     = ( $wpe_max - $wpe_min ) * 100 / 1080;
			$wpe_intercept = $wpe_min - 3.6 * $wpe_slope;
			$wpe_mid       = 'calc(' . round( $wpe_intercept, 6 ) . 'px + ' . round( $wpe_slope, 6 ) . 'vw)';

			$rules['base'][ $root ][] = $wpe_property . ':clamp(' . $wpe_min . 'px,' . $wpe_mid . ',' . $wpe_max . 'px)!important';
		}
		$out = '';
		// Custom CSS: "selector" means this element, like Elementor Pro.
		$custom = trim( (string) ( $a['customCss'] ?? '' ) );
		if ( '' !== $custom ) {
			$custom = str_ireplace( array( '</', '<', '@import', 'expression(', 'javascript:' ), '', wp_strip_all_tags( $custom ) );
			$custom = preg_replace( '/\bselector\b/', $root, $custom );
		}
		foreach ( array( 'base' => '' ) + self::BREAKPOINTS as $bp => $query ) {
			if ( empty( $rules[ (string) $bp ] ) ) {
				continue;
			}
			$block = '';
			foreach ( $rules[ (string) $bp ] as $sel => $decls ) {
				$block .= $sel . '{' . implode( ';', $decls ) . '}';
			}
			$out .= 'base' === $bp ? $block : '@media ' . $query . '{' . $block . '}';
		}
		// Container queries run against the nearest ancestor with container-type,
		// not the viewport. Each optional threshold only hides this element.
		foreach (
			array(
				'containerQueryHideBelow' => 'max-width',
				'containerQueryHideAbove' => 'min-width',
			) as $wpe_key => $wpe_feature
		) {
			$wpe_value = $a[ $wpe_key ] ?? '';
			if ( ! is_numeric( $wpe_value ) || (float) $wpe_value < 160 || (float) $wpe_value > 1920 ) {
				continue;
			}
			$out .= '@container (' . $wpe_feature . ':' . (float) $wpe_value . 'px){' . $root . '{display:none!important}}';
		}
		return $out . ( '' !== $custom && false === strpos( $custom, '{' ) ? $root . '{' . $custom . '}' : $custom );
	}

	/**
	 * One CSS value: palette slug -> preset variable, numbers get their unit.
	 *
	 * @param array $opt   Option.
	 * @param mixed $value Value.
	 * @return string
	 */
	private static function css_value( array $opt, $value ) {
		if ( 'color' === $opt['type'] ) {
			return 'var(--wp--preset--color--' . sanitize_key( $value ) . ')';
		}
		if ( is_numeric( $value ) ) {
			return ( 0 + $value ) . ( $opt['unit'] ?? '' );
		}
		return preg_replace( '/[^a-z0-9#%.,()\/ \-]/i', '', (string) $value );
	}

	/**
	 * Elementor settings -> our attribute shape (responsive values per breakpoint).
	 *
	 * @param array $def      Definition.
	 * @param array $settings Widget settings.
	 * @return array
	 */
	public static function from_elementor( array $def, array $settings ) {
		$out = array();
		// Full elements inherit common Advanced controls; nested repeater fields do not.
		$fields = isset( $def['name'] ) ? self::attributes( $def ) : $def['attributes'];
		foreach ( $fields as $key => $opt ) {
			$get = static function ( $k ) use ( $settings, $opt ) {
				$v = $settings[ $k ] ?? '';
				if ( 'icon' === $opt['type'] ) {
					return is_array( $v ) && ! empty( $v['value'] ) ? $v : array();
				}
				if ( 'repeater' === $opt['type'] ) {
					return array_map(
						static fn( $item ) => self::from_elementor( array( 'attributes' => $opt['fields'] ), (array) $item ),
						is_array( $v ) ? array_values( $v ) : array()
					);
				}
				if ( 'range' === $opt['type'] && is_array( $v ) ) {
					if ( ! isset( $v['size'] ) || '' === $v['size'] ) {
						return '';
					}
					$unit = (string) ( $v['unit'] ?? '' );
					return '' === $unit || ( $opt['unit'] ?? 'px' ) === $unit ? $v['size'] : $v['size'] . $unit;
				}
				if ( 'image' === $opt['type'] ) {
					return is_array( $v ) ? array(
						'id'  => (int) ( $v['id'] ?? 0 ),
						'url' => (string) ( $v['url'] ?? '' ),
					) : array();
				}
				if ( is_array( $v ) && array_key_exists( 'size', $v ) ) {
					$v = $v['size'];
				}
				if ( is_array( $v ) && array_key_exists( 'url', $v ) ) {
					$v = $v['url'];
				}
				return 'toggle' === $opt['type'] ? ( 'yes' === $v ) : $v;
			};
			if ( ! empty( $opt['responsive'] ) ) {
				$out[ $key ] = array( 'base' => $get( $key ) );
				foreach ( self::ELEMENTOR_DEVICES as $device => $bp ) {
					$out[ $key ][ $bp ] = $get( $key . '_' . $device );
				}
			} else {
				$out[ $key ] = $get( $key );
			}
		}
		if ( ! empty( $settings['wpe_custom_css'] ) ) {
			$out['customCss'] = (string) $settings['wpe_custom_css'];
		}
		return $out;
	}
}
