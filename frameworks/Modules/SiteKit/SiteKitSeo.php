<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Basic SEO tags when no SEO plugin is active (ported from hi-wpessential inc/seo.php): meta
 * description, Open Graph, Twitter card and a schema.org graph (Organization, WebSite,
 * BreadcrumbList, Article). Opt-in through the "SEO basics" Site Kit group.
 */
final class SiteKitSeo {

	public function __construct( private readonly SiteKitStore $store ) {}

	public function register(): void {
		add_action( 'wp_head', [ $this, 'head' ], 2 );
	}

	public static function pluginActive(): bool {
		$active = defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );

		return (bool) apply_filters( 'wpessential_seo_plugin_active', $active );
	}

	public static function description(): string {
		$text = '';
		if ( is_singular() ) {
			$post = get_queried_object();
			if ( $post instanceof \WP_Post ) {
				$text = (string) get_post_meta( $post->ID, '_wpe_seo_description', true );
				if ( $text === '' && has_excerpt( $post ) ) {
					$text = $post->post_excerpt;
				}
				if ( $text === '' && preg_match_all( '#<p[^>]*>(.*?)</p>#si', $post->post_content, $paragraphs ) ) {
					foreach ( $paragraphs[1] as $paragraph ) {
						if ( mb_strlen( wp_strip_all_tags( $paragraph ) ) >= 70 ) {
							$text = $paragraph;
							break;
						}
					}
				}
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$text = (string) term_description();
		}
		if ( trim( wp_strip_all_tags( $text ) ) === '' ) {
			$text = (string) get_bloginfo( 'description' );
		}
		$text = trim( (string) preg_replace( '/\s+/', ' ', wp_strip_all_tags( strip_shortcodes( $text ) ) ) );

		return mb_strlen( $text ) > 160 ? rtrim( mb_substr( $text, 0, 157 ) ) . '…' : $text;
	}

	public function head(): void {
		if ( ( $this->store->values()['seo_tags'] ?? '' ) !== 'on' || self::pluginActive() || is_404() ) {
			return;
		}
		$description = self::description();
		$image       = '';
		if ( is_singular() && has_post_thumbnail() ) {
			$image = (string) get_the_post_thumbnail_url( null, 'large' );
		} elseif ( has_site_icon() ) {
			$image = (string) get_site_icon_url( 512 );
		}
		if ( $description !== '' ) {
			printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
		}
		if ( is_search() ) {
			echo '<meta name="robots" content="noindex, follow" />' . "\n";
		}
		$tags = [
			'og:type'        => is_singular( 'post' ) ? 'article' : 'website',
			'og:site_name'   => get_bloginfo( 'name' ),
			'og:title'       => wp_get_document_title(),
			'og:description' => $description,
			'og:url'         => is_singular() ? (string) get_permalink() : home_url( add_query_arg( [] ) ),
			'og:image'       => $image,
			'og:locale'      => get_locale(),
		];
		foreach ( array_filter( $tags ) as $property => $content ) {
			printf( '<meta property="%s" content="%s" />' . "\n", esc_attr( $property ), esc_attr( (string) $content ) );
		}
		printf( '<meta name="twitter:card" content="%s" />' . "\n", esc_attr( $image !== '' ? 'summary_large_image' : 'summary' ) );
		printf( '<script type="application/ld+json">%s</script>' . "\n", (string) wp_json_encode( self::schema( $description, $image ), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-LD encoded with JSON_HEX_TAG.
	}

	/** @return array<string,mixed> */
	public static function schema( string $description, string $image ): array {
		$home  = home_url( '/' );
		$graph = [
			[
				'@type' => 'Organization',
				'@id'   => $home . '#organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => $home,
				'logo'  => has_site_icon() ? get_site_icon_url( 512 ) : null,
			],
			[
				'@type'           => 'WebSite',
				'@id'             => $home . '#website',
				'url'             => $home,
				'name'            => get_bloginfo( 'name' ),
				'description'     => get_bloginfo( 'description' ),
				'publisher'       => [ '@id' => $home . '#organization' ],
				'potentialAction' => [
					'@type'       => 'SearchAction',
					'target'      => $home . '?s={search_term_string}',
					'query-input' => 'required name=search_term_string',
				],
			],
		];
		$post  = is_singular() && ! is_front_page() ? get_queried_object() : null;
		if ( $post instanceof \WP_Post ) {
			$crumbs = [
				[
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => __( 'Home', 'wpessential' ),
					'item'     => $home,
				],
			];
			foreach ( array_reverse( get_post_ancestors( $post ) ) as $parent ) {
				$crumbs[] = [
					'@type'    => 'ListItem',
					'position' => count( $crumbs ) + 1,
					'name'     => get_the_title( $parent ),
					'item'     => get_permalink( $parent ),
				];
			}
			$crumbs[] = [
				'@type'    => 'ListItem',
				'position' => count( $crumbs ) + 1,
				'name'     => get_the_title( $post ),
			];
			$graph[]  = [
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $crumbs,
			];
			if ( $post->post_type === 'post' ) {
				$graph[] = [
					'@type'            => 'Article',
					'headline'         => get_the_title( $post ),
					'description'      => $description,
					'image'            => $image !== '' ? $image : null,
					'datePublished'    => get_the_date( 'c', $post ),
					'dateModified'     => get_the_modified_date( 'c', $post ),
					'author'           => [
						'@type' => 'Person',
						'name'  => get_the_author_meta( 'display_name', (int) $post->post_author ),
					],
					'publisher'        => [ '@id' => $home . '#organization' ],
					'mainEntityOfPage' => get_permalink( $post ),
				];
			}
		}

		return [
			'@context' => 'https://schema.org',
			'@graph'   => array_map( 'array_filter', $graph ),
		];
	}
}
