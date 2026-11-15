<?php
/**
 * SEO fallbacks: meta description, Open Graph / Twitter tags and JSON-LD.
 *
 * Steps aside when an SEO plugin (Yoast, Rank Math, SEOPress, AIOSEO, The SEO Framework) is active.
 * A page can set its own description in the `_wpe_seo_description` meta (demo packages do).
 *
 * @package WPEssential
 */

namespace WPEssential\Platform\SiteTools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a dedicated SEO plugin handles the head.
 *
 * @return bool
 */
function wpessential_seo_plugin_active() {
	$active = defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
	return (bool) apply_filters( 'wpessential_seo_plugin_active', $active );
}

/**
 * Description for the current view (max 160 characters).
 *
 * @return string
 */
function wpessential_seo_description() {
	$text = '';
	if ( is_singular() ) {
		$post = get_queried_object();
		$text = (string) get_post_meta( $post->ID, '_wpe_seo_description', true );
		if ( '' === $text && has_excerpt( $post ) ) {
			$text = $post->post_excerpt;
		}
		if ( '' === $text ) {
			// First real paragraph (skips eyebrows and short labels).
			$content = $post->post_content;
			$text    = '';
			if ( preg_match_all( '#<p[^>]*>(.*?)</p>#si', $content, $m ) ) {
				foreach ( $m[1] as $paragraph ) {
					if ( mb_strlen( wp_strip_all_tags( $paragraph ) ) >= 70 ) {
						$text = $paragraph;
						break;
					}
				}
			}
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$text = term_description();
	} elseif ( is_post_type_archive( 'product' ) && function_exists( 'wc_get_page_id' ) ) {
		$text = (string) get_post_meta( wc_get_page_id( 'shop' ), '_wpe_seo_description', true );
	}
	if ( '' === trim( wp_strip_all_tags( $text ) ) && ( is_front_page() || is_home() || '' === $text ) ) {
		$text = get_bloginfo( 'description' );
	}
	$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( strip_shortcodes( $text ) ) ) );
	return mb_strlen( $text ) > 160 ? rtrim( mb_substr( $text, 0, 157 ) ) . '…' : $text;
}

/**
 * Print meta and social tags.
 */
function wpessential_seo_head() {
	if ( wpessential_seo_plugin_active() || is_404() ) {
		return;
	}
	$description = wpessential_seo_description();
	$title       = wp_get_document_title();
	$url         = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
	$image       = '';
	if ( is_singular() && has_post_thumbnail() ) {
		$image = get_the_post_thumbnail_url( null, 'large' );
	} elseif ( has_site_icon() ) {
		$image = get_site_icon_url( 512 );
	}
	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}
	if ( is_search() ) {
		echo '<meta name="robots" content="noindex, follow" />' . "\n";
	}
	$tags = array(
		'og:type'        => is_singular( 'post' ) ? 'article' : 'website',
		'og:site_name'   => get_bloginfo( 'name' ),
		'og:title'       => $title,
		'og:description' => $description,
		'og:url'         => $url,
		'og:image'       => $image,
		'og:locale'      => get_locale(),
	);
	foreach ( array_filter( $tags ) as $property => $content ) {
		printf( '<meta property="%s" content="%s" />' . "\n", esc_attr( $property ), esc_attr( $content ) );
	}
	printf( '<meta name="twitter:card" content="%s" />' . "\n", $image ? 'summary_large_image' : 'summary' );
	wpessential_seo_schema( $description, $image );
}
add_action( 'wp_head', __NAMESPACE__ . '\wpessential_seo_head', 2 );

/**
 * JSON-LD: Organization + WebSite (with search) on every page, Article and breadcrumbs on singular views.
 *
 * @param string $description Description.
 * @param string $image       Image URL.
 */
function wpessential_seo_schema( $description, $image ) {
	$home  = function_exists( __NAMESPACE__ . '\wpessential_ml_home_url' ) ? wpessential_ml_home_url() : home_url( '/' );
	$graph = array(
		array(
			'@type' => 'Organization',
			'@id'   => $home . '#organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => $home,
			'logo'  => has_site_icon() ? get_site_icon_url( 512 ) : null,
		),
		array(
			'@type'           => 'WebSite',
			'@id'             => $home . '#website',
			'url'             => $home,
			'name'            => get_bloginfo( 'name' ),
			'description'     => get_bloginfo( 'description' ),
			'publisher'       => array( '@id' => $home . '#organization' ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => $home . '?s={search_term_string}',
				'query-input' => 'required name=search_term_string',
			),
		),
	);
	if ( is_singular() && ! is_front_page() ) {
		$post    = get_queried_object();
		$crumbs  = array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => __( 'Home', 'wpessential' ),
				'item'     => $home,
			),
		);
		$parents = array_reverse( get_post_ancestors( $post ) );
		foreach ( $parents as $parent ) {
			$crumbs[] = array(
				'@type'    => 'ListItem',
				'position' => count( $crumbs ) + 1,
				'name'     => get_the_title( $parent ),
				'item'     => get_permalink( $parent ),
			);
		}
		$crumbs[] = array(
			'@type'    => 'ListItem',
			'position' => count( $crumbs ) + 1,
			'name'     => get_the_title( $post ),
		);
		$graph[]  = array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $crumbs,
		);
		if ( 'post' === $post->post_type ) {
			$graph[] = array(
				'@type'            => 'Article',
				'headline'         => get_the_title( $post ),
				'description'      => $description,
				'image'            => $image ? $image : null,
				'datePublished'    => get_the_date( 'c', $post ),
				'dateModified'     => get_the_modified_date( 'c', $post ),
				'author'           => array(
					'@type' => 'Person',
					'name'  => get_the_author_meta( 'display_name', (int) $post->post_author ),
				),
				'publisher'        => array( '@id' => $home . '#organization' ),
				'mainEntityOfPage' => get_permalink( $post ),
			);
		}
	}
	$graph = array_map( 'array_filter', $graph );
	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		)
	);
}
