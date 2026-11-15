<?php
/**
 * Site tools moved out of the Hi WPEssential theme so the theme meets the WordPress.org
 * theme rules (these are plugin territory): SEO fallbacks, the WPML / Polylang language
 * switcher (with its shortcode), the custom CSS / JS snippet manager, and the "quiet
 * Elementor admin" option. They work with any theme.
 *
 * They load on after_setup_theme and skip themselves when an older copy of the theme still
 * defines the same functions, so updating the plugin before the theme never redeclares one.
 *
 * @package WPEssential
 */

namespace WPEssential\Platform\SiteTools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loader.
 */
final class SiteTools {

	/** @var string Base URL of this folder. */
	private static $url = '';

	/** @var string Asset version. */
	private static $version = '0';

	/** Base URL of this folder (with trailing slash). */
	public static function url(): string {
		return self::$url;
	}

	/** Asset version. */
	public static function version(): string {
		return self::$version;
	}

	/**
	 * Hook the loader.
	 *
	 * @param string $url     Plugin URL (with trailing slash).
	 * @param string $version Asset version.
	 */
	public static function boot( string $url, string $version ): void {
		self::$url     = $url . 'frameworks/Platform/SiteTools/';
		self::$version = $version;
		add_action( 'after_setup_theme', array( self::class, 'load' ), 20 );
	}

	/**
	 * Load each tool unless the active theme still ships its own copy.
	 */
	public static function load(): void {
		$tools = array(
			'seo.php'             => 'wpessential_seo_plugin_active',
			'multilingual.php'    => 'wpessential_ml',
			'snippets.php'        => 'wpessential_snippets',
			'elementor-quiet.php' => 'wpessential_elementor_remove_notices',
		);
		foreach ( $tools as $file => $marker ) {
			if ( ! function_exists( $marker ) && (bool) apply_filters( 'wpessential_site_tool_enabled', true, basename( $file, '.php' ) ) ) {
				require_once __DIR__ . '/' . $file;
			}
		}
	}
}
