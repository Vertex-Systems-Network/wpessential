<?php
/**
 * Server checks for the System Status tab and the importer preflight.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Platform\Demos\Import;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * System status.
 */
class Status {

	/**
	 * Every check: label, value, state (ok|warn|fail), hint, blocking.
	 *
	 * @return array
	 */
	public static function checks() {
		global $wp_version;
		$memory  = wp_convert_hr_to_bytes( (string) ini_get( 'memory_limit' ) );
		$time    = (int) ini_get( 'max_execution_time' );
		$upload  = wp_max_upload_size();
		$uploads = wp_upload_dir();
		$checks  = array(
			array(
				'label' => __( 'WordPress version', 'wpessential' ),
				'value' => $wp_version,
				'state' => version_compare( $wp_version, '6.5', '>=' ) ? 'ok' : 'fail',
				'hint'  => __( 'Update WordPress to 6.5 or newer.', 'wpessential' ),
				'block' => true,
			),
			array(
				'label' => __( 'PHP version', 'wpessential' ),
				'value' => PHP_VERSION,
				'state' => version_compare( PHP_VERSION, '7.4', '>=' ) ? ( version_compare( PHP_VERSION, '8.1', '>=' ) ? 'ok' : 'warn' ) : 'fail',
				'hint'  => __( 'PHP 8.1 or newer is faster and still supported. Ask your host to upgrade.', 'wpessential' ),
				'block' => true,
			),
			array(
				'label' => __( 'Memory limit', 'wpessential' ),
				'value' => $memory < 0 ? __( 'Unlimited', 'wpessential' ) : size_format( $memory ),
				'state' => $memory < 0 || $memory >= 256 * MB_IN_BYTES ? 'ok' : ( $memory >= 128 * MB_IN_BYTES ? 'warn' : 'fail' ),
				'hint'  => __( 'Set memory_limit to 256M in php.ini, or define WP_MEMORY_LIMIT in wp-config.php.', 'wpessential' ),
				'block' => false,
			),
			array(
				'label' => __( 'Max execution time', 'wpessential' ),
				'value' => $time ? $time . 's' : __( 'Unlimited', 'wpessential' ),
				'state' => ( 0 === $time || $time >= 30 ) ? 'ok' : 'warn',
				'hint'  => __( 'The importer works in short batches, so 30 seconds is enough.', 'wpessential' ),
				'block' => false,
			),
			array(
				'label' => __( 'Max upload size', 'wpessential' ),
				'value' => size_format( $upload ),
				'state' => $upload >= 8 * MB_IN_BYTES ? 'ok' : 'warn',
				'hint'  => __( 'Raise upload_max_filesize and post_max_size to at least 8M.', 'wpessential' ),
				'block' => false,
			),
			array(
				'label' => __( 'Uploads folder', 'wpessential' ),
				'value' => empty( $uploads['error'] ) && wp_is_writable( $uploads['basedir'] ) ? __( 'Writable', 'wpessential' ) : __( 'Not writable', 'wpessential' ),
				'state' => empty( $uploads['error'] ) && wp_is_writable( $uploads['basedir'] ) ? 'ok' : 'fail',
				'hint'  => __( 'Give the web server write access to wp-content/uploads.', 'wpessential' ),
				'block' => true,
			),
			array(
				'label' => __( 'Plugins folder', 'wpessential' ),
				'value' => wp_is_writable( WP_PLUGIN_DIR ) ? __( 'Writable', 'wpessential' ) : __( 'Not writable', 'wpessential' ),
				'state' => wp_is_writable( WP_PLUGIN_DIR ) ? 'ok' : 'warn',
				'hint'  => __( 'Without write access, required plugins must be installed by hand.', 'wpessential' ),
				'block' => false,
			),
			array(
				'label' => __( 'ZIP support', 'wpessential' ),
				'value' => class_exists( 'ZipArchive' ) ? 'ZipArchive' : 'PclZip',
				'state' => class_exists( 'ZipArchive' ) ? 'ok' : 'warn',
				'hint'  => __( 'Enable the PHP zip extension for faster package extraction.', 'wpessential' ),
				'block' => false,
			),
			array(
				'label' => __( 'Image editor', 'wpessential' ),
				'value' => wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ? __( 'WebP supported', 'wpessential' ) : __( 'No WebP support', 'wpessential' ),
				'state' => wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ? 'ok' : 'warn',
				'hint'  => __( 'Images still import, but WordPress cannot create smaller sizes. Ask your host to enable Imagick or GD with WebP.', 'wpessential' ),
				'block' => false,
			),
			array(
				'label' => __( 'Permalinks', 'wpessential' ),
				'value' => get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : __( 'Plain', 'wpessential' ),
				'state' => get_option( 'permalink_structure' ) ? 'ok' : 'warn',
				'hint'  => __( 'Choose "Post name" under Settings → Permalinks for clean shop and page addresses.', 'wpessential' ),
				'block' => false,
			),
		);

		return array_map(
			static function ( $check ) {
				return array(
					'label'    => $check['label'],
					'value'    => (string) $check['value'],
					'state'    => $check['state'],
					'hint'     => 'ok' === $check['state'] ? '' : $check['hint'],
					'blocking' => $check['block'],
				);
			},
			$checks
		);
	}

	/**
	 * Environment summary.
	 *
	 * @return array
	 */
	public static function environment() {
		$theme = wp_get_theme();
		return array(
			array( __( 'Theme', 'wpessential' ), $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) ),
			array( __( 'WPEssential', 'wpessential' ), defined( 'WPE_VERSION' ) ? (string) WPE_VERSION : '' ),
			array( __( 'Site address', 'wpessential' ), home_url() ),
			array( __( 'Multisite', 'wpessential' ), is_multisite() ? __( 'Yes', 'wpessential' ) : __( 'No', 'wpessential' ) ),
			array( __( 'Server', 'wpessential' ), isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '' ),
			array( __( 'Database', 'wpessential' ), $GLOBALS['wpdb']->db_server_info() ),
			array( __( 'Active plugins', 'wpessential' ), (string) count( (array) get_option( 'active_plugins', array() ) ) ),
		);
	}
}
