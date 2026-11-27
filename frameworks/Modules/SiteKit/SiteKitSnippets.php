<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom CSS and JavaScript snippets that survive theme changes (ported from hi-wpessential
 * inc/options.php). JavaScript needs unfiltered_html. Admins can load a page with
 * ?wpe-safe-mode=1 to skip every snippet.
 */
final class SiteKitSnippets {

	public const OPTION = 'wpessential_site_kit_snippets';
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
	}

	/** @return list<array{id:string,title:string,type:string,scope:string,location:string,enabled:bool,code:string,updated:int,condition?:string,page_ids?:list<int>,priority?:int}> */
	public function all(): array {
		$stored = function_exists( 'get_option' ) ? get_option( self::OPTION, [] ) : [];

		return array_values( array_filter( is_array( $stored ) ? $stored : [], 'is_array' ) );
	}

	/**
	 * Replaces the snippet list. JavaScript a user without unfiltered_html cannot write is kept as it was.
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
		$protected = [];
		foreach ( $existing as $row ) {
			if ( ! $canJs && ( $row['type'] ?? '' ) !== 'css' && is_string( $row['id'] ?? null ) ) {
				$protected[ $row['id'] ] = $row;
			}
		}
		$out = [];
		foreach ( $submitted as $raw ) {
			$id = self::id( $raw['id'] ?? '' );
			if ( isset( $protected[ $id ] ) ) {
				continue;
			}
			$clean = self::clean( $raw, $canJs );
			if ( $clean !== null && ! isset( $out[ $clean['id'] ] ) ) {
				$out[ $clean['id'] ] = $clean;
			}
		}
		return array_values( $out + $protected );
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
		if ( ! in_array( $type, [ 'css', 'js' ], true ) ) {
			return null;
		}
		if ( $type === 'js' && ! $canJs ) {
			return null;
		}
		$code = is_scalar( $snippet['code'] ?? null ) ? (string) $snippet['code'] : '';
		if ( $type === 'css' ) {
			$code = function_exists( 'wp_strip_all_tags' ) ? wp_strip_all_tags( $code ) : $code;
		}
		if ( trim( $code ) === '' ) {
			return null;
		}
		$id = self::id( $snippet['id'] ?? '' );
		$title = is_scalar( $snippet['title'] ?? null ) ? trim( wp_strip_all_tags( (string) $snippet['title'] ) ) : '';

		$condition = $snippet['condition'] ?? 'all';
		if ( ! in_array( $condition, [ 'all', 'front', 'pages' ], true ) ) {
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
		$priority = is_numeric( $snippet['priority'] ?? null ) ? max( 0, min( 1000, (int) $snippet['priority'] ) ) : 10;

		return [
			'id'       => $id !== '' ? substr( $id, 0, 32 ) : substr( md5( uniqid( '', true ) ), 0, 8 ),
			'title'    => $title !== '' ? substr( $title, 0, 120 ) : 'Untitled snippet',
			'type'     => $type,
			'scope'    => array_key_exists( (string) ( $snippet['scope'] ?? '' ), self::SCOPES ) ? (string) $snippet['scope'] : 'all',
			'location' => in_array( $snippet['location'] ?? '', [ 'head', 'footer' ], true ) ? (string) $snippet['location'] : 'footer',
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

	/** @return list<array<string,mixed>> */
	private function eligible(): array {
		return array_values( array_filter( self::ordered( $this->all() ), static fn ( array $row ): bool => self::matches( $row, is_front_page(), is_page() ? get_queried_object_id() : 0 ) ) );
	}

	private static function safeMode(): bool {
		return isset( $_GET['wpe-safe-mode'] ) && current_user_can( 'manage_options' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only switch for admins.
	}

	public function styles(): void {
		if ( self::safeMode() ) {
			return;
		}
		$css = '';
		foreach ( $this->eligible() as $snippet ) {
			if ( $snippet['type'] !== 'css' || ! $snippet['enabled'] || trim( $snippet['code'] ) === '' ) {
				continue;
			}
			$block = '/* ' . str_replace( '*/', '', $snippet['title'] ) . " */\n" . $snippet['code'] . "\n";
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

	private function scripts( string $location ): void {
		if ( self::safeMode() ) {
			return;
		}
		foreach ( $this->eligible() as $snippet ) {
			if ( $snippet['type'] === 'js' && $snippet['enabled'] && $snippet['location'] === $location && trim( $snippet['code'] ) !== '' ) {
				wp_print_inline_script_tag( $snippet['code'], [ 'id' => 'wpe-snippet-' . $snippet['id'] ] );
			}
		}
	}
}
