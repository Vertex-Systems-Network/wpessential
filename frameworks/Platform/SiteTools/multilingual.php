<?php
/**
 * WPML and Polylang compatibility.
 *
 * - Language switcher: [wpe_language_switcher] shortcode, a pattern, and an automatic item in the
 *   header navigation (filter `wpessential_nav_language_switcher` to turn it off).
 * - Imported demo content is assigned to the default language so it can be translated at once.
 * - Labels drawn by CSS are printed in the current language.
 * - SEO tags use the current language (home URL, og:locale and its alternates).
 * - wpml-config.xml (read by both plugins) declares which fields are translated or copied.
 *
 * @package WPEssential
 */

namespace WPEssential\Platform\SiteTools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WPML's own filters and actions.

/**
 * Active multilingual plugin.
 *
 * @return string 'wpml', 'polylang' or ''.
 */
function wpessential_ml() {
	if ( function_exists( 'pll_the_languages' ) ) {
		return 'polylang';
	}
	if ( defined( 'ICL_SITEPRESS_VERSION' ) && has_filter( 'wpml_active_languages' ) ) {
		return 'wpml';
	}
	return '';
}

/**
 * Languages of the current page, with translation URLs.
 *
 * @return array[] code, name, url, flag, locale, current.
 */
function wpessential_ml_languages() {
	$out = array();
	if ( 'polylang' === wpessential_ml() ) {
		$raw = (array) pll_the_languages(
			array(
				'raw'           => 1,
				'hide_if_empty' => 0,
			)
		);
		foreach ( $raw as $lang ) {
			$out[] = array(
				'code'    => $lang['slug'],
				'name'    => $lang['name'],
				'url'     => $lang['url'],
				'flag'    => $lang['flag'] ?? '',
				'locale'  => $lang['locale'] ?? '',
				'current' => ! empty( $lang['current_lang'] ),
			);
		}
	} elseif ( 'wpml' === wpessential_ml() ) {
		$raw = (array) apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
		foreach ( $raw as $lang ) {
			$out[] = array(
				'code'    => $lang['language_code'] ?? $lang['code'] ?? '',
				'name'    => $lang['native_name'] ?? '',
				'url'     => $lang['url'] ?? '',
				'flag'    => $lang['country_flag_url'] ?? '',
				'locale'  => $lang['default_locale'] ?? '',
				'current' => ! empty( $lang['active'] ),
			);
		}
	}
	return $out;
}

/**
 * Language switcher markup.
 *
 * @param array $args { flags: bool, names: 'name'|'code' }.
 * @return string
 */
function wpessential_language_switcher( $args = array() ) {
	$args  = wp_parse_args(
		$args,
		array(
			'flags' => true,
			'names' => 'code',
		)
	);
	$langs = wpessential_ml_languages();
	if ( count( $langs ) < 2 ) {
		return '';
	}
	$items = '';
	foreach ( $langs as $lang ) {
		$flag   = ( $args['flags'] && $lang['flag'] ) ? sprintf( '<img src="%s" alt="" width="18" height="12" />', esc_url( $lang['flag'] ) ) : '';
		$label  = 'name' === $args['names'] ? $lang['name'] : strtoupper( $lang['code'] );
		$items .= sprintf(
			'<li class="wpe-lang__item%1$s"><a href="%2$s" hreflang="%3$s" lang="%3$s"%4$s>%5$s<span>%6$s</span></a></li>',
			$lang['current'] ? ' is-current' : '',
			esc_url( $lang['url'] ),
			esc_attr( $lang['code'] ),
			$lang['current'] ? ' aria-current="true"' : '',
			$flag,
			esc_html( $label )
		);
	}
	return sprintf(
		'<nav class="wpe-lang" aria-label="%s"><ul class="wpe-lang__list">%s</ul></nav>',
		esc_attr__( 'Language', 'wpessential' ),
		$items
	);
}

add_shortcode(
	'wpe_language_switcher',
	static function ( $atts ) {
		$atts = shortcode_atts(
			array(
				'flags' => 'yes',
				'names' => 'code',
			),
			$atts,
			'wpe_language_switcher'
		);
		return wpessential_language_switcher(
			array(
				'flags' => 'yes' === $atts['flags'],
				'names' => $atts['names'],
			)
		);
	}
);

/**
 * Add the switcher as the last item of the first navigation block (the header menu).
 *
 * @param string $html  Block HTML.
 * @return string
 */
function wpessential_ml_nav_switcher( $html ) {
	static $done = false;
	if ( $done || ! wpessential_ml() || ! apply_filters( 'wpessential_nav_language_switcher', true ) ) {
		return $html;
	}
	$switcher = wpessential_language_switcher();
	$pos      = strrpos( $html, '</ul>' );
	if ( '' === $switcher || false === $pos ) {
		return $html;
	}
	$done = true;
	$item = '<li class="wp-block-navigation-item wpe-lang-item">' . $switcher . '</li>';
	return substr_replace( $html, $item, $pos, 0 );
}
add_filter( 'render_block_core/navigation', __NAMESPACE__ . '\wpessential_ml_nav_switcher' );

/**
 * Pattern for placing the switcher anywhere (footer, top bar).
 */
function wpessential_ml_pattern() {
	if ( ! wpessential_ml() ) {
		return;
	}
	register_block_pattern(
		'wpessential/language-switcher',
		array(
			'title'      => __( 'Language switcher', 'wpessential' ),
			'categories' => array( 'wpessential' ),
			'content'    => '<!-- wp:shortcode -->[wpe_language_switcher]<!-- /wp:shortcode -->',
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\wpessential_ml_pattern', 20 );

/**
 * Put freshly imported demo content into the default language.
 *
 * @param string $demo    Demo id.
 * @param string $builder Builder.
 * @param array  $map     Imported ids: posts, media, terms.
 */
function wpessential_ml_assign_demo( $demo, $builder, $map ) {
	$ml = wpessential_ml();
	if ( ! $ml ) {
		return;
	}
	$posts = array_merge( array_values( (array) ( $map['posts'] ?? array() ) ), array_values( (array) ( $map['media'] ?? array() ) ) );
	if ( 'polylang' === $ml ) {
		$lang = pll_default_language();
		foreach ( $posts as $id ) {
			if ( pll_is_translated_post_type( get_post_type( $id ) ) && ! pll_get_post_language( $id ) ) {
				pll_set_post_language( $id, $lang );
			}
		}
		foreach ( (array) ( $map['terms'] ?? array() ) as $term ) {
			if ( pll_is_translated_taxonomy( $term[1] ) && ! pll_get_term_language( $term[0] ) ) {
				pll_set_term_language( $term[0], $lang );
			}
		}
		return;
	}
	$lang = apply_filters( 'wpml_default_language', null );
	foreach ( $posts as $id ) {
		$type = 'post_' . get_post_type( $id );
		if ( ! apply_filters(
			'wpml_element_language_code',
			null,
			array(
				'element_id'   => $id,
				'element_type' => $type,
			)
		) ) {
			do_action(
				'wpml_set_element_language_details',
				array(
					'element_id'    => $id,
					'element_type'  => $type,
					'trid'          => false,
					'language_code' => $lang,
				)
			);
		}
	}
	foreach ( (array) ( $map['terms'] ?? array() ) as $term ) {
		$tt = get_term( $term[0], $term[1] );
		if ( $tt && ! is_wp_error( $tt ) ) {
			do_action(
				'wpml_set_element_language_details',
				array(
					'element_id'    => $tt->term_taxonomy_id,
					'element_type'  => 'tax_' . $term[1],
					'trid'          => false,
					'language_code' => $lang,
				)
			);
		}
	}
}
add_action( 'wpessential_demo_imported', __NAMESPACE__ . '\wpessential_ml_assign_demo', 5, 3 );

/**
 * CSS-generated labels ("Featured", "Author") in the current language.
 */
function wpessential_ml_css_labels() {
	$css = sprintf(
		'.sticky .wp-block-post-title::before,.is-sticky-post .wp-block-post-title::before{content:"%1$s"}.bypostauthor .wp-block-comment-author-name::after{content:"%2$s"}',
		esc_attr( __( 'Featured', 'wpessential' ) ),
		esc_attr( __( 'Author', 'wpessential' ) )
	);
	wp_add_inline_style( 'wpessential', $css );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\wpessential_ml_css_labels', 20 );

/**
 * Home URL in the current language (used by the SEO schema).
 *
 * @return string
 */
function wpessential_ml_home_url() {
	if ( function_exists( 'pll_home_url' ) ) {
		return pll_home_url();
	}
	return (string) apply_filters( 'wpml_home_url', home_url( '/' ) );
}

/**
 * Open Graph alternate locales for the other languages.
 */
function wpessential_ml_og_alternates() {
	if ( ! wpessential_ml() || ( function_exists( __NAMESPACE__ . '\wpessential_seo_plugin_active' ) && wpessential_seo_plugin_active() ) ) {
		return;
	}
	foreach ( wpessential_ml_languages() as $lang ) {
		if ( ! $lang['current'] && $lang['locale'] ) {
			printf( '<meta property="og:locale:alternate" content="%s" />' . "\n", esc_attr( str_replace( '-', '_', $lang['locale'] ) ) );
		}
	}
}
add_action( 'wp_head', __NAMESPACE__ . '\wpessential_ml_og_alternates', 3 );
