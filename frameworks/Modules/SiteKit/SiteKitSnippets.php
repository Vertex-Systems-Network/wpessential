<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom CSS, HTML and JavaScript snippets that survive theme changes (ported from hi-wpessential
 * inc/options.php). JavaScript needs unfiltered_html. Admins can load a page with
 * ?wpe-safe-mode=1 to skip every snippet.
 */
final class SiteKitSnippets {

	public const OPTION = 'wpessential_site_kit_snippets';
	public const LOCATIONS = [ 'head', 'footer', 'body_open', 'before_content', 'after_content', 'admin', 'login' ];
	public const SCOPES = [
		'all'     => '',
		'desktop' => '(min-width: 1025px)',
		'tablet'  => '(min-width: 768px) and (max-width: 1024px)',
		'mobile'  => '(max-width: 767px)',
	];

	public function register(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'styles' ], 100 );
		add_action( 'wp_head', [ $this, 'headScripts' ], 99 );
		add_action( 'wp_footer', [ $this, 'footerScripts' ], 99 );
		add_action( 'wp_body_open', [ $this, 'bodyOpen' ], 99 );
		add_filter( 'the_content', [ $this, 'content' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this, 'adminStyles' ], 100 );
		add_action( 'login_enqueue_scripts', [ $this, 'loginStyles' ], 100 );
		add_action( 'admin_footer', [ $this, 'adminScripts' ], 99 );
		add_action( 'login_footer', [ $this, 'loginScripts' ], 99 );
	}

	/** @return list<array{id:string,title:string,type:string,scope:string,location:string,enabled:bool,code:string,updated:int,condition?:string,page_ids?:list<int>,priority?:int}> */
	public function all(): array {
		$stored = function_exists( 'get_option' ) ? get_option( self::OPTION, [] ) : [];

		return array_values( array_filter( is_array( $stored ) ? $stored : [], 'is_array' ) );
	}

	/**
	 * Saves submitted edits and explicit deletions. Omitted or invalid existing rows are preserved.
	 *
	 * @param list<array<string,mixed>> $snippets
	 * @return list<array<string,mixed>>
	 */
	public function save( array $snippets, bool $canJs ): array {
		$out = self::mergeRows( $this->all(), $snippets, $canJs );
		update_option( self::OPTION, $out );

		return $out;
	}

	/**
	 * Restricted users cannot replace, relabel or remove existing privileged snippets.
	 *
	 * @param list<array<string,mixed>> $existing
	 * @param list<array<string,mixed>> $submitted
	 * @return list<array<string,mixed>>
	 */
	public static function mergeRows( array $existing, array $submitted, bool $canJs ): array {
		$stored = [];
		foreach ( $existing as $row ) {
			if ( is_string( $row['id'] ?? null ) ) {
				$stored[ $row['id'] ] = $row;
			}
		}
		$out = [];
		$seen = [];
		foreach ( $submitted as $raw ) {
			$id = self::id( $raw['id'] ?? '' );
			if ( $id !== '' && isset( $seen[ $id ] ) ) {
				continue;
			}
			if ( $id !== '' ) {
				$seen[ $id ] = true;
			}
			if ( isset( $stored[ $id ] ) && ! $canJs && self::privileged( $stored[ $id ] ) ) {
				continue;
			}
			if ( ( $raw['remove'] ?? '' ) === '1' ) {
				unset( $stored[ $id ] );
				continue;
			}
			$clean = self::clean( $raw, $canJs );
			if ( $clean !== null && ! isset( $out[ $clean['id'] ] ) ) {
				$out[ $clean['id'] ] = $clean;
				unset( $stored[ $clean['id'] ] );
			}
		}
		return array_values( $out + $stored );
	}

	/** @param array<string,mixed> $row */
	public static function privileged( array $row ): bool {
		return ! in_array( $row['type'] ?? '', [ 'css', 'scss' ], true ) || in_array( $row['location'] ?? '', [ 'admin', 'login' ], true );
	}

	private static function id( mixed $value ): string {
		return substr( strtolower( (string) preg_replace( '/[^a-z0-9]/i', '', is_scalar( $value ) ? (string) $value : '' ) ), 0, 32 );
	}

	/**
	 * @param array<string,mixed> $snippet
	 * @return array{id:string,title:string,type:string,scope:string,location:string,enabled:bool,code:string,updated:int,condition?:string,page_ids?:list<int>,priority?:int}|null
	 */
	public static function clean( array $snippet, bool $canJs ): ?array {
		$type = $snippet['type'] ?? 'css';
		if ( ! in_array( $type, [ 'css', 'scss', 'js', 'html' ], true ) ) {
			return null;
		}
		if ( self::privileged( $snippet + [ 'type' => $type ] ) && ! $canJs ) {
			return null;
		}
		$code = is_scalar( $snippet['code'] ?? null ) ? (string) $snippet['code'] : '';
		if ( $type === 'css' ) {
			$code = function_exists( 'wp_strip_all_tags' ) ? wp_strip_all_tags( $code ) : $code;
		}
		if ( $type === 'html' ) {
			$code = function_exists( 'wp_kses_post' ) ? wp_kses_post( $code ) : '';
		}
		if ( trim( $code ) === '' || strlen( $code ) > 262144 ) {
			return null;
		}
		if ( $type === 'scss' && self::compileScssLite( $code ) === null ) {
			return null;
		}
		$id = self::id( $snippet['id'] ?? '' );
		$title = is_scalar( $snippet['title'] ?? null ) ? trim( wp_strip_all_tags( (string) $snippet['title'] ) ) : '';

		$location = $snippet['location'] ?? 'footer';
		if ( ! in_array( $location, self::LOCATIONS, true ) ) {
			return null;
		}
		// CSS remains an enqueued stylesheet on public pages, as in the legacy contract.
		$condition = $snippet['condition'] ?? 'all';
		if ( ! in_array( $condition, [ 'all', 'front', 'pages' ], true ) || ( in_array( $location, [ 'admin', 'login' ], true ) && $condition !== 'all' ) ) {
			return null;
		}
		$ids = $snippet['page_ids'] ?? [];
		$ids = is_string( $ids ) ? preg_split( '/[\s,]+/', $ids, -1, PREG_SPLIT_NO_EMPTY ) : $ids;
		$pageIds = [];
		foreach ( is_array( $ids ) ? $ids : [] as $value ) {
			if ( ( is_int( $value ) || is_string( $value ) ) && preg_match( '/^[1-9][0-9]*$/D', (string) $value ) === 1 ) {
				$pageIds[] = (int) $value;
			}
		}
		if ( $condition === 'pages' && $pageIds === [] ) {
			return null;
		}
		$scope = $snippet['scope'] ?? 'all';
		$priority = is_numeric( $snippet['priority'] ?? null ) ? max( 0, min( 1000, (int) $snippet['priority'] ) ) : 10;

		return [
			'id'       => $id !== '' ? substr( $id, 0, 32 ) : substr( md5( uniqid( '', true ) ), 0, 8 ),
			'title'    => $title !== '' ? substr( $title, 0, 120 ) : 'Untitled snippet',
			'type'     => $type,
			'scope'    => is_string( $scope ) && array_key_exists( $scope, self::SCOPES ) ? $scope : 'all',
			'location' => $location,
			'enabled'  => ! empty( $snippet['enabled'] ),
			'code'     => $code,
			'updated'  => time(),
			'condition' => $condition,
			'page_ids' => array_values( array_unique( array_slice( $pageIds, 0, 100 ) ) ),
			'priority' => $priority,
		];
	}

	/**
	 * Stable priority ordering, preserving legacy insertion order on ties.
	 *
	 * @param list<array<string,mixed>> $rows
	 * @return list<array<string,mixed>>
	 */
	public static function ordered( array $rows ): array {
		usort( $rows, static fn ( array $a, array $b ): int => (int) ( $a['priority'] ?? 10 ) <=> (int) ( $b['priority'] ?? 10 ) );
		return $rows;
	}

	/** @param array<string,mixed> $row */
	public static function matches( array $row, bool $front, int $pageId ): bool {
		return match ( $row['condition'] ?? 'all' ) {
			'all'   => true,
			'front' => $front,
			'pages' => $pageId > 0 && in_array( $pageId, (array) ( $row['page_ids'] ?? [] ), true ),
			default => false,
		};
	}

	/**
	 * Bounded, dependency-free SCSS subset: literal variables followed by flat rules.
	 * Unsupported syntax is rejected, never executed or fetched.
	 */
	public static function compileScssLite( string $source ): ?string {
		if ( strlen( $source ) > 65536 ) {
			return null;
		}
		$rest = trim( $source );
		$vars = [];
		while ( preg_match( '/^\$([a-zA-Z_][a-zA-Z0-9_-]{0,63})\s*:\s*([#a-zA-Z0-9.%+-]{1,80})\s*;\s*/', $rest, $match ) === 1 ) {
			if ( count( $vars ) >= 32 || isset( $vars[ $match[1] ] ) ) {
				return null;
			}
			$vars[ $match[1] ] = $match[2];
			$rest = substr( $rest, strlen( $match[0] ) );
		}
		$rules = [];
		while ( $rest !== '' ) {
			if ( count( $rules ) >= 128 || preg_match( '/^([a-zA-Z0-9_.#:\s,>*+~-]+)\{([^{}]+)\}\s*/', $rest, $match ) !== 1 ) {
				return null;
			}
			$selector = trim( $match[1] );
			if ( $selector === '' ) {
				return null;
			}
			$declarations = [];
			foreach ( explode( ';', $match[2] ) as $item ) {
				if ( trim( $item ) === '' ) {
					continue;
				}
				if ( count( $declarations ) >= 128 || preg_match( '/^\s*([a-zA-Z-][a-zA-Z0-9-]*)\s*:\s*([^;]+?)\s*$/D', $item, $decl ) !== 1 ) {
					return null;
				}
				$value = preg_replace_callback(
					'/\$([a-zA-Z_][a-zA-Z0-9_-]*)/',
					static fn ( array $ref ): string => $vars[ $ref[1] ] ?? '$',
					$decl[2]
				);
				if ( ! is_string( $value ) || preg_match( '/^[#a-zA-Z0-9.% ,()+\/-]+$/D', $value ) !== 1 || trim( $value ) === '' ) {
					return null;
				}
				$declarations[] = $decl[1] . ': ' . $value . ';';
			}
			if ( $declarations === [] ) {
				return null;
			}
			$rules[] = $selector . ' { ' . implode( ' ', $declarations ) . ' }';
			$rest = substr( $rest, strlen( $match[0] ) );
		}
		return $rules === [] ? null : implode( "\n", $rules );
	}

	/** @return list<array<string,mixed>> */
	private function eligible( string $context = 'public' ): array {
		return array_values(
			array_filter(
				self::ordered( $this->all() ),
				static function ( array $row ) use ( $context ): bool {
					$location = $row['location'] ?? 'footer';
					if ( ! in_array( $location, self::LOCATIONS, true ) ) {
						return false;
					}
					if ( $context !== 'public' ) {
						return $location === $context && ( $row['condition'] ?? 'all' ) === 'all';
					}
					return ! in_array( $location, [ 'admin', 'login' ], true ) && self::matches( $row, is_front_page(), is_page() ? get_queried_object_id() : 0 );
				}
			)
		);
	}

	private static function safeMode(): bool {
		return isset( $_GET['wpe-safe-mode'] ) && current_user_can( 'manage_options' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only switch for admins.
	}

	public function styles(): void {
		$this->enqueueStyles( 'public' );
	}

	public function adminStyles(): void {
		$this->enqueueStyles( 'admin' );
	}

	public function loginStyles(): void {
		$this->enqueueStyles( 'login' );
	}

	private function enqueueStyles( string $context ): void {
		if ( self::safeMode() ) {
			return;
		}
		$css = '';
		foreach ( $this->eligible( $context ) as $snippet ) {
			if ( ! in_array( $snippet['type'], [ 'css', 'scss' ], true ) || ! $snippet['enabled'] || trim( $snippet['code'] ) === '' ) {
				continue;
			}
			$source = $snippet['type'] === 'scss' ? self::compileScssLite( $snippet['code'] ) : $snippet['code'];
			if ( $source === null ) {
				continue;
			}
			$block = '/* ' . str_replace( '*/', '', $snippet['title'] ) . " */\n" . $source . "\n";
			$media = self::SCOPES[ $snippet['scope'] ] ?? '';
			$css  .= $media !== '' ? '@media ' . $media . "{\n" . $block . "}\n" : $block;
		}
		if ( $css !== '' ) {
			wp_register_style( 'wpessential-site-kit-snippets', false, [], null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- inline only.
			wp_enqueue_style( 'wpessential-site-kit-snippets' );
			wp_add_inline_style( 'wpessential-site-kit-snippets', $css );
		}
	}

	public function headScripts(): void {
		$this->scripts( 'head' );
	}

	public function footerScripts(): void {
		$this->scripts( 'footer' );
	}

	public function bodyOpen(): void {
		$this->scripts( 'body_open' );
	}

	public function adminScripts(): void {
		$this->scripts( 'admin', 'admin' );
	}

	public function loginScripts(): void {
		$this->scripts( 'login', 'login' );
	}

	public function content( string $content ): string {
		if ( self::safeMode() || ! is_singular() || ! in_the_loop() || ! is_main_query() || is_feed() ) {
			return $content;
		}
		return $this->markup( 'before_content' ) . $content . $this->markup( 'after_content' );
	}

	private function scripts( string $location, string $context = 'public' ): void {
		// HTML is filtered at render time as well as save time. Scripts use the WP inline tag helper.
		echo $this->markup( $location, $context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted JS via wp_get_inline_script_tag, HTML via wp_kses_post.
	}

	private function markup( string $location, string $context = 'public' ): string {
		if ( self::safeMode() ) {
			return '';
		}
		$out = '';
		foreach ( $this->eligible( $context ) as $snippet ) {
			if ( empty( $snippet['enabled'] ) || ( $snippet['location'] ?? 'footer' ) !== $location || trim( (string) ( $snippet['code'] ?? '' ) ) === '' ) {
				continue;
			}
			if ( $snippet['type'] === 'js' ) {
				$out .= wp_get_inline_script_tag( $snippet['code'], [ 'id' => 'wpe-snippet-' . self::id( $snippet['id'] ) ] );
			} elseif ( $snippet['type'] === 'html' ) {
				$out .= wp_kses_post( $snippet['code'] );
			}
		}
		return $out;
	}
}
