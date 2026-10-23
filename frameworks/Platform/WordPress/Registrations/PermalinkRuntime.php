<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Post;
use WP_Term;

/**
 * Permalink choices for builder post types and taxonomies, like the WordPress Permalinks screen:
 * - "hide_base": /my-book/ instead of /books/my-book/ (terms: /fiction/ instead of /genre/fiction/);
 * - "custom" (post types): a pattern such as books/%book_genre%/%postname%, where %taxonomy%,
 *   %year%, %monthnum%, %day% and %author% are filled in for each post.
 * Real pages and posts always win over a hidden-base URL. Rewrite rules are flushed once, when
 * the URL settings change.
 */
final class PermalinkRuntime {

	/** Key the projectors put the permalink choice under; removed before WordPress sees the args. */
	public const ARG = 'wpessential_permalink';

	public const OPTION = 'wpessential_permalink_signature_v1';

	private const DATE_TOKENS = [ 'year', 'monthnum', 'day', 'author' ];

	/** @var array<string, array{mode:string, tokens:list<string>}> */
	private array $postTypes = [];

	/** @var array<string, string> */
	private array $taxonomies = [];

	/** @var array<string, mixed> */
	private array $signature = [];

	private bool $booted = false;

	/**
	 * Takes the permalink choice out of compiled args.
	 *
	 * @param array<string,mixed> $args
	 * @return array{mode:string, tokens:list<string>}
	 */
	public static function extract( array &$args ): array {
		$config = $args[ self::ARG ] ?? null;
		unset( $args[ self::ARG ] );
		$mode   = is_array( $config ) && is_string( $config['mode'] ?? null ) ? $config['mode'] : 'default';
		$tokens = is_array( $config ) && is_array( $config['tokens'] ?? null ) ? array_values( array_filter( $config['tokens'], 'is_string' ) ) : [];

		return [
			'mode'   => in_array( $mode, [ 'default', 'hide_base', 'custom' ], true ) ? $mode : 'default',
			'tokens' => $tokens,
		];
	}

	/**
	 * @param array{mode:string, tokens:list<string>} $config
	 * @param array<string,mixed>                     $args   The args WordPress registers.
	 */
	public function addPostType( string $key, array $config, array $args ): void {
		$this->signature[ 'post_type:' . $key ] = [ $config, $args['rewrite'] ?? true, $args['has_archive'] ?? false, $args['hierarchical'] ?? false ];
		if ( $config['mode'] !== 'default' ) {
			$this->postTypes[ $key ] = $config;
		}
	}

	/** @param array<string,mixed> $args */
	public function addTaxonomy( string $key, string $mode, array $args ): void {
		$this->signature[ 'taxonomy:' . $key ] = [ $mode, $args['rewrite'] ?? true ];
		if ( $mode === 'hide_base' ) {
			$this->taxonomies[ $key ] = $mode;
		}
	}

	public function register(): void {
		if ( function_exists( 'add_action' ) ) {
			// After the registrars (init 20 and 21).
			add_action( 'init', [ $this, 'boot' ], 30 );
		}
	}

	public function boot(): void {
		if ( $this->booted || ! function_exists( 'add_filter' ) ) {
			return;
		}
		$this->booted = true;

		foreach ( $this->postTypes as $config ) {
			foreach ( $config['tokens'] as $token ) {
				if ( ! in_array( $token, self::DATE_TOKENS, true ) && function_exists( 'add_rewrite_tag' ) && function_exists( 'taxonomy_exists' ) && taxonomy_exists( $token ) ) {
					add_rewrite_tag( '%' . $token . '%', '([^/]+)', $token . '=' );
				}
			}
		}
		if ( $this->postTypes !== [] ) {
			add_filter( 'post_type_link', [ $this, 'postLink' ], 10, 2 );
		}
		if ( $this->taxonomies !== [] ) {
			add_filter( 'term_link', [ $this, 'termLink' ], 10, 3 );
		}
		if ( $this->hidesBase() ) {
			add_filter( 'request', [ $this, 'resolveRequest' ] );
		}
		$this->flushWhenChanged();
	}

	public function postLink( string $link, WP_Post $post ): string {
		$config = $this->postTypes[ $post->post_type ] ?? null;
		if ( $config === null || ! $this->prettyPermalinks() || in_array( $post->post_status, [ 'draft', 'pending', 'auto-draft' ], true ) ) {
			return $link;
		}
		if ( $config['mode'] === 'hide_base' ) {
			$path = function_exists( 'get_page_uri' ) && is_post_type_hierarchical( $post->post_type ) ? (string) get_page_uri( $post ) : $post->post_name;

			return home_url( user_trailingslashit( $path ) );
		}

		foreach ( $config['tokens'] as $token ) {
			$link = str_replace( '%' . $token . '%', $this->tokenValue( $token, $post ), $link );
		}

		return $link;
	}

	public function termLink( string $link, WP_Term $term, string $taxonomy ): string {
		if ( ! isset( $this->taxonomies[ $taxonomy ] ) || ! $this->prettyPermalinks() ) {
			return $link;
		}
		$path = $term->slug;
		if ( is_taxonomy_hierarchical( $taxonomy ) ) {
			$ancestors = array_reverse( get_ancestors( $term->term_id, $taxonomy, 'taxonomy' ) );
			$slugs     = [];
			foreach ( $ancestors as $ancestor ) {
				$parent = get_term( $ancestor, $taxonomy );
				if ( $parent instanceof WP_Term ) {
					$slugs[] = $parent->slug;
				}
			}
			$path = implode( '/', [ ...$slugs, $term->slug ] );
		}

		return home_url( user_trailingslashit( $path ) );
	}

	/**
	 * A hidden-base URL arrives as a page or post request (/my-book/). When no real page or post has
	 * that path, it is resolved to the builder post type or term that does.
	 *
	 * @param array<string,mixed> $vars
	 * @return array<string,mixed>
	 */
	public function resolveRequest( array $vars ): array {
		$path = $vars['pagename'] ?? $vars['name'] ?? null;
		if ( ! is_string( $path ) || $path === '' || count( $vars ) > 2 || isset( $vars['post_type'] ) ) {
			return $vars;
		}
		if ( get_page_by_path( $path, OBJECT, [ 'page', 'post' ] ) !== null ) {
			return $vars;
		}
		foreach ( array_keys( $this->postTypes ) as $type ) {
			if ( $this->postTypes[ $type ]['mode'] === 'hide_base' && get_page_by_path( $path, OBJECT, $type ) !== null ) {
				$object    = get_post_type_object( $type );
				$queryVar  = $object !== null && is_string( $object->query_var ) && $object->query_var !== '' ? $object->query_var : $type;
				$resolved  = [
					'post_type' => $type,
					$queryVar   => $path,
				];
				$resolved += is_post_type_hierarchical( $type ) ? [] : [ 'name' => basename( $path ) ];

				return $resolved;
			}
		}
		$slug = basename( $path );
		foreach ( array_keys( $this->taxonomies ) as $taxonomy ) {
			if ( get_term_by( 'slug', $slug, $taxonomy ) instanceof WP_Term ) {
				return [ $taxonomy => $slug ];
			}
		}

		return $vars;
	}

	/** @return array<string, mixed> */
	public function signature(): array {
		return $this->signature;
	}

	private function hidesBase(): bool {
		return $this->taxonomies !== [] || in_array( 'hide_base', array_column( $this->postTypes, 'mode' ), true );
	}

	private function prettyPermalinks(): bool {
		return function_exists( 'get_option' ) && (string) get_option( 'permalink_structure' ) !== '';
	}

	private function tokenValue( string $token, WP_Post $post ): string {
		return match ( $token ) {
			'year'     => (string) mysql2date( 'Y', $post->post_date ),
			'monthnum' => (string) mysql2date( 'm', $post->post_date ),
			'day'      => (string) mysql2date( 'd', $post->post_date ),
			'author'   => (string) get_the_author_meta( 'user_nicename', (int) $post->post_author ),
			default    => $this->termSlug( $token, $post ),
		};
	}

	/** The post's first term in the taxonomy (WordPress does the same for %category%). */
	private function termSlug( string $taxonomy, WP_Post $post ): string {
		$terms = get_the_terms( $post, $taxonomy );
		if ( is_array( $terms ) && $terms !== [] ) {
			usort( $terms, static fn ( WP_Term $a, WP_Term $b ): int => $a->term_id <=> $b->term_id );

			return $terms[0]->slug;
		}

		return 'uncategorized';
	}

	/** Rewrite rules are rebuilt once after any URL setting changes, never on every request. */
	private function flushWhenChanged(): void {
		if ( ! function_exists( 'get_option' ) || ! function_exists( 'flush_rewrite_rules' ) ) {
			return;
		}
		ksort( $this->signature );
		$hash = md5( (string) wp_json_encode( $this->signature ) );
		if ( get_option( self::OPTION ) === $hash ) {
			return;
		}
		flush_rewrite_rules( false );
		update_option( self::OPTION, $hash, true );
	}
}
