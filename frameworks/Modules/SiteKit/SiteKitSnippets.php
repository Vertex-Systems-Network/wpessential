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

	/** @return list<array{id:string,title:string,type:string,scope:string,location:string,enabled:bool,code:string,updated:int}> */
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
		$existing = [];
		foreach ( $this->all() as $snippet ) {
			$existing[ $snippet['id'] ] = $snippet;
		}
		$out = [];
		foreach ( $snippets as $raw ) {
			$clean = self::clean( (array) $raw, $canJs );
			if ( $clean !== null ) {
				$out[] = $clean;
			} elseif ( isset( $raw['id'], $existing[ $raw['id'] ] ) ) {
				$out[] = $existing[ $raw['id'] ];
			}
		}
		update_option( self::OPTION, $out );

		return $out;
	}

	/**
	 * @param array<string,mixed> $snippet
	 * @return array{id:string,title:string,type:string,scope:string,location:string,enabled:bool,code:string,updated:int}|null
	 */
	public static function clean( array $snippet, bool $canJs ): ?array {
		$type = in_array( $snippet['type'] ?? '', [ 'css', 'js' ], true ) ? (string) $snippet['type'] : 'css';
		if ( $type === 'js' && ! $canJs ) {
			return null;
		}
		$code = is_scalar( $snippet['code'] ?? null ) ? (string) $snippet['code'] : '';
		if ( $type === 'css' ) {
			$code = function_exists( 'wp_strip_all_tags' ) ? wp_strip_all_tags( $code ) : strip_tags( $code );
		}
		$id    = strtolower( (string) preg_replace( '/[^a-z0-9]/i', '', is_scalar( $snippet['id'] ?? null ) ? (string) $snippet['id'] : '' ) );
		$title = is_scalar( $snippet['title'] ?? null ) ? trim( strip_tags( (string) $snippet['title'] ) ) : '';

		return [
			'id'       => $id !== '' ? substr( $id, 0, 32 ) : substr( md5( uniqid( '', true ) ), 0, 8 ),
			'title'    => $title !== '' ? substr( $title, 0, 120 ) : 'Untitled snippet',
			'type'     => $type,
			'scope'    => array_key_exists( (string) ( $snippet['scope'] ?? '' ), self::SCOPES ) ? (string) $snippet['scope'] : 'all',
			'location' => in_array( $snippet['location'] ?? '', [ 'head', 'footer' ], true ) ? (string) $snippet['location'] : 'footer',
			'enabled'  => ! empty( $snippet['enabled'] ),
			'code'     => $code,
			'updated'  => time(),
		];
	}

	private static function safeMode(): bool {
		return isset( $_GET['wpe-safe-mode'] ) && current_user_can( 'manage_options' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only switch for admins.
	}

	public function styles(): void {
		if ( self::safeMode() ) {
			return;
		}
		$css = '';
		foreach ( $this->all() as $snippet ) {
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
		foreach ( $this->all() as $snippet ) {
			if ( $snippet['type'] === 'js' && $snippet['enabled'] && $snippet['location'] === $location && trim( $snippet['code'] ) !== '' ) {
				wp_print_inline_script_tag( $snippet['code'], [ 'id' => 'wpe-snippet-' . $snippet['id'] ] );
			}
		}
	}
}
