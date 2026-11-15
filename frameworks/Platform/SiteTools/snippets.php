<?php
/**
 * Appearance → Custom Code (WPEssential): a custom CSS / JS snippet manager.
 * Moved from the Hi WPEssential theme (snippets are plugin territory for WordPress.org themes).
 *
 * Snippets are named, can be switched on/off, scoped to devices (CSS) or head/footer (JS),
 * and edited with WordPress' code editor. Admins can open any page with ?wpe-safe-mode=1
 * to load it without snippets after a broken change.
 *
 * @package WPEssential
 */

namespace WPEssential\Platform\SiteTools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Saved snippets.
 *
 * @return array
 */
function wpessential_snippets() {
	return array_values( array_filter( (array) get_option( 'wpessential_snippets', array() ), 'is_array' ) );
}

/**
 * Sanitize one snippet.
 *
 * @param array $s Raw snippet.
 * @return array|null
 */
function wpessential_sanitize_snippet( $s ) {
	$type = in_array( $s['type'] ?? '', array( 'css', 'js' ), true ) ? $s['type'] : 'css';
	if ( 'js' === $type && ! current_user_can( 'unfiltered_html' ) ) {
		return null;
	}
	$code = (string) ( $s['code'] ?? '' );
	if ( 'css' === $type ) {
		$code = wp_strip_all_tags( $code );
	}
	return array(
		'id'       => preg_replace( '/[^a-z0-9]/', '', strtolower( (string) ( $s['id'] ?? '' ) ) ) ? preg_replace( '/[^a-z0-9]/', '', strtolower( (string) $s['id'] ) ) : wp_generate_password( 8, false ),
		'title'    => sanitize_text_field( $s['title'] ?? '' ) ? sanitize_text_field( $s['title'] ) : __( 'Untitled snippet', 'wpessential' ),
		'type'     => $type,
		'scope'    => in_array( $s['scope'] ?? '', array( 'all', 'desktop', 'tablet', 'mobile' ), true ) ? $s['scope'] : 'all',
		'location' => in_array( $s['location'] ?? '', array( 'head', 'footer' ), true ) ? $s['location'] : 'footer',
		'enabled'  => ! empty( $s['enabled'] ),
		'code'     => $code,
		'updated'  => time(),
	);
}

/**
 * Whether snippets are skipped for this request.
 *
 * @return bool
 */
function wpessential_safe_mode() {
	return isset( $_GET['wpe-safe-mode'] ) && current_user_can( 'manage_options' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only switch for admins.
}

/* Front end output. */

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( wpessential_safe_mode() ) {
			return;
		}
		$media = array(
			'all'     => '',
			'desktop' => '(min-width: 1025px)',
			'tablet'  => '(min-width: 768px) and (max-width: 1024px)',
			'mobile'  => '(max-width: 767px)',
		);
		$css   = '';
		foreach ( wpessential_snippets() as $s ) {
			if ( 'css' !== $s['type'] || ! $s['enabled'] || '' === trim( $s['code'] ) ) {
				continue;
			}
			$block = "/* {$s['title']} */\n" . $s['code'] . "\n";
			$css  .= $media[ $s['scope'] ] ? '@media ' . $media[ $s['scope'] ] . "{\n" . $block . "}\n" : $block;
		}
		if ( $css ) {
			wp_register_style( 'wpessential-custom', false, array(), \WPEssential\Platform\SiteTools\SiteTools::version() );
			wp_enqueue_style( 'wpessential-custom' );
			wp_add_inline_style( 'wpessential-custom', $css );
		}
	},
	100
);

/**
 * Print JS snippets for a location.
 *
 * @param string $location head|footer.
 */
function wpessential_print_snippets( $location ) {
	if ( wpessential_safe_mode() ) {
		return;
	}
	foreach ( wpessential_snippets() as $s ) {
		if ( 'js' === $s['type'] && $s['enabled'] && $location === $s['location'] && '' !== trim( $s['code'] ) ) {
			wp_print_inline_script_tag( $s['code'], array( 'id' => 'wpe-snippet-' . $s['id'] ) );
		}
	}
}
add_action(
	'wp_head',
	static function () {
		wpessential_print_snippets( 'head' );
	},
	99
);
add_action(
	'wp_footer',
	static function () {
		wpessential_print_snippets( 'footer' );
	},
	99
);

/* Admin. */

add_action(
	'admin_menu',
	static function () {
		add_theme_page( __( 'Custom Code', 'wpessential' ), __( 'Custom Code', 'wpessential' ), 'edit_theme_options', 'wpessential-options', __NAMESPACE__ . '\wpessential_options_page' );
	}
);

add_action(
	'admin_enqueue_scripts',
	static function ( $hook ) {
		if ( 'appearance_page_wpessential-options' !== $hook ) {
			return;
		}
		$css = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
		$js  = wp_enqueue_code_editor( array( 'type' => 'text/javascript' ) );
		wp_enqueue_style( 'wpessential-options', \WPEssential\Platform\SiteTools\SiteTools::url() . 'assets/snippets.css', array(), \WPEssential\Platform\SiteTools\SiteTools::version() );
		wp_enqueue_script( 'wpessential-options', \WPEssential\Platform\SiteTools\SiteTools::url() . 'assets/snippets.js', array( 'wp-api-fetch', 'wp-i18n', 'code-editor' ), \WPEssential\Platform\SiteTools\SiteTools::version(), true );
		wp_set_script_translations( 'wpessential-options', 'wpessential' );
		wp_localize_script(
			'wpessential-options',
			'wpessentialOptions',
			array(
				'snippets' => wpessential_snippets(),
				'canJs'    => current_user_can( 'unfiltered_html' ),
				'editor'   => array(
					'css' => $css,
					'js'  => $js,
				),
				'safe'     => add_query_arg( 'wpe-safe-mode', '1', home_url( '/' ) ),
				'i18n'     => array(
					'saved'   => __( 'Saved', 'wpessential' ),
					'error'   => __( 'Could not save', 'wpessential' ),
					'confirm' => __( 'Delete this snippet?', 'wpessential' ),
					'empty'   => __( 'No snippets yet. Add CSS or JavaScript without editing theme files — it survives theme updates.', 'wpessential' ),
				),
			)
		);
	}
);

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'wpessential/v1',
			'/options',
			array(
				'methods'             => 'POST',
				'permission_callback' => static function () {
					return current_user_can( 'edit_theme_options' );
				},
				'callback'            => static function ( \WP_REST_Request $request ) {
					if ( null !== $request->get_param( 'snippets' ) ) {
						$existing = array();
						foreach ( wpessential_snippets() as $s ) {
							$existing[ $s['id'] ] = $s;
						}
						$out = array();
						foreach ( (array) $request->get_param( 'snippets' ) as $raw ) {
							$clean = wpessential_sanitize_snippet( (array) $raw );
							if ( $clean ) {
								$out[] = $clean;
							} elseif ( isset( $raw['id'], $existing[ $raw['id'] ] ) ) {
								$out[] = $existing[ $raw['id'] ]; // Keep JS an editor without unfiltered_html cannot change.
							}
						}
						update_option( 'wpessential_snippets', $out );
					}
					return array(
						'snippets' => wpessential_snippets(),
					);
				},
			)
		);
	}
);

/**
 * Page shell (the app renders into it).
 */
function wpessential_options_page() {
	echo '<div class="wrap wpe-options"><h1>' . esc_html__( 'Custom Code', 'wpessential' ) . '</h1><div id="wpe-options-app"><p>' . esc_html__( 'Loading…', 'wpessential' ) . '</p></div></div>';
}
