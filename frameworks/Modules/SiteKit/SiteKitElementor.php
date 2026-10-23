<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keeps Elementor's active kit (global colours and fonts) in sync with the theme.json palette and
 * fonts Site Kit writes (ported from hi-wpessential inc/elementor.php).
 */
final class SiteKitElementor {

	public const HASH_OPTION = 'wpessential_site_kit_elementor_hash';

	public static function register(): void {
		add_action( 'admin_init', [ self::class, 'maybeSync' ] );
	}

	/** @return array<string,string> slug => colour */
	public static function palette(): array {
		$palettes = function_exists( 'wp_get_global_settings' ) ? wp_get_global_settings( [ 'color', 'palette' ] ) : [];
		$palette  = [];
		foreach ( array_merge( (array) ( $palettes['theme'] ?? [] ), (array) ( $palettes['custom'] ?? [] ) ) as $colour ) {
			if ( isset( $colour['slug'], $colour['color'] ) ) {
				$palette[ (string) $colour['slug'] ] = (string) $colour['color'];
			}
		}

		return $palette;
	}

	public static function maybeSync(): void {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		$hash = md5( (string) wp_json_encode( self::palette() ) );
		if ( get_option( self::HASH_OPTION ) === $hash ) {
			return;
		}
		if ( self::sync() ) {
			update_option( self::HASH_OPTION, $hash, false );
		}
	}

	public static function sync(): bool {
		if ( ! class_exists( '\Elementor\Plugin' ) || empty( \Elementor\Plugin::$instance->kits_manager ) ) {
			return false;
		}
		$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
		if ( ! $kit || ! $kit->get_id() || ! get_post( $kit->get_id() ) ) {
			return false;
		}
		$palette = self::palette();
		$system  = [];
		foreach ( [ 'primary', 'secondary', 'text', 'accent' ] as $id ) {
			if ( isset( $palette[ $id ] ) ) {
				$system[] = [
					'_id'   => $id,
					'title' => ucfirst( $id ),
					'color' => $palette[ $id ],
				];
			}
		}
		$custom = [];
		foreach ( array_diff_key( $palette, array_flip( [ 'primary', 'secondary', 'text', 'accent' ] ) ) as $slug => $colour ) {
			$custom[] = [
				'_id'   => 'wpe' . substr( md5( $slug ), 0, 7 ),
				'title' => 'WPE ' . ucwords( str_replace( '-', ' ', $slug ) ),
				'color' => $colour,
			];
		}
		$fonts    = [
			'body'    => '',
			'heading' => '',
		];
		$families = function_exists( 'wp_get_global_settings' ) ? wp_get_global_settings( [ 'typography', 'fontFamilies' ] ) : [];
		foreach ( array_merge( (array) ( $families['theme'] ?? [] ), (array) ( $families['custom'] ?? [] ) ) as $family ) {
			if ( isset( $family['slug'], $fonts[ $family['slug'] ] ) && ! empty( $family['name'] ) ) {
				$fonts[ $family['slug'] ] = (string) $family['name'];
			}
		}
		$typography = [];
		foreach ( [
			'primary'   => [ 'heading', '700' ],
			'secondary' => [ 'heading', '600' ],
			'text'      => [ 'body', '400' ],
			'accent'    => [ 'body', '600' ],
		] as $id => [ $slot, $weight ] ) {
			if ( $fonts[ $slot ] !== '' ) {
				$typography[] = [
					'_id'                    => $id,
					'title'                  => ucfirst( $id ),
					'typography_typography'  => 'custom',
					'typography_font_family' => $fonts[ $slot ],
					'typography_font_weight' => $weight,
				];
			}
		}
		$settings                  = $kit->get_settings();
		$settings['system_colors'] = $system;
		$settings['custom_colors'] = $custom;
		if ( $typography !== [] ) {
			$settings['system_typography'] = $typography;
		}
		try {
			$kit->save( [ 'settings' => $settings ] );
		} catch ( \Throwable ) {
			return false;
		}
		if ( isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		return true;
	}
}
