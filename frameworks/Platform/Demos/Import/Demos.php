<?php
/**
 * Demo catalogue: bundled demos plus the remote manifest.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Platform\Demos\Import;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads demos and resolves their packages to a local folder.
 */
class Demos {

	/** @var null|\WPEssential\Platform\Admin\Connect\LicenseService */
	private static $license = null;

	/** @var null|callable(): bool */
	private static $premium = null;

	/**
	 * Wire the licence service and the premium check (set at boot by the Free plugin).
	 *
	 * @param \WPEssential\Platform\Admin\Connect\LicenseService $license Licence service.
	 * @param callable(): bool                                       $premium True with Pro or a Pro trial.
	 */
	public static function useLicense( $license, callable $premium ) {
		self::$license = $license;
		self::$premium = $premium;
	}

	/** Pro demos unlock with WPEssential Premium (a Pro licence or the Pro trial). */
	public static function hasPro() {
		return null !== self::$premium && (bool) ( self::$premium )();
	}

	/**
	 * Signed download URL for a licensed package.
	 *
	 * @param string $item    Demo id or plugin slug.
	 * @param string $context Builder or "plugin".
	 * @return string|\WP_Error
	 */
	public static function packageUrl( $item, $context ) {
		return null === self::$license
			? new \WP_Error( 'wpe_license', __( 'This is a Pro item. Activate your licence to unlock it.', 'wpessential' ) )
			: self::$license->packageUrl( (string) $item, (string) $context );
	}

	const CACHE = 'wpessential_core_manifest';

	/**
	 * Manifest URL (filterable by the theme or a site).
	 *
	 * @return string
	 */
	public static function manifest_url() {
		$default = function_exists( 'wpessential_demo_manifest_url' ) ? wpessential_demo_manifest_url() : 'https://wpessential.org/wp-json/wpessential-server/v1/demos';
		return (string) apply_filters( 'wpessential_core_manifest_url', $default );
	}

	/**
	 * Every demo, bundled first. Adds a "notice" when the remote library is unreachable.
	 *
	 * @param bool $refresh Skip the cache.
	 * @return array{demos: array, notices: array}
	 */
	public static function all( $refresh = false ) {
		$notices = array();
		$demos   = self::bundled();

		$remote = $refresh ? false : get_transient( self::CACHE );
		if ( false === $remote ) {
			$response = wp_safe_remote_get( self::manifest_url(), array( 'timeout' => 8 ) );
			$code     = wp_remote_retrieve_response_code( $response );
			$body     = json_decode( (string) wp_remote_retrieve_body( $response ), true );
			if ( is_wp_error( $response ) || 200 !== (int) $code || empty( $body['demos'] ) ) {
				$remote = array();
				set_transient( self::CACHE, $remote, 15 * MINUTE_IN_SECONDS );
				$notices[] = array(
					'level'   => 'info',
					'message' => __( 'The online demo library could not be reached, so only the demos bundled with the theme are listed. They import fully offline.', 'wpessential' ),
				);
			} else {
				$remote = array_map(
					static function ( $demo ) {
						$demo['source'] = 'remote';
						return $demo;
					},
					$body['demos']
				);
				set_transient( self::CACHE, $remote, 12 * HOUR_IN_SECONDS );
			}
		}

		$ids = wp_list_pluck( $demos, 'id' );
		foreach ( (array) $remote as $demo ) {
			if ( isset( $demo['id'] ) && ! in_array( $demo['id'], $ids, true ) ) {
				$demos[] = $demo;
			}
		}

		$imported = Importer::imported();
		foreach ( $demos as &$demo ) {
			$demo['imported'] = isset( $imported['demo'] ) && $imported['demo'] === $demo['id'];
			$demo['locked']   = 'pro' === ( $demo['type'] ?? 'free' ) && ! self::hasPro();
		}
		unset( $demo );

		return array(
			'demos'   => apply_filters( 'wpessential_core_demos', $demos ),
			'notices' => $notices,
		);
	}

	/**
	 * Demos shipped in core/demos/{id}/demo.json.
	 *
	 * @return array
	 */
	public static function bundled() {
		$demos = array();
		/**
		 * Folders scanned for bundled demos (each holds <id>/demo.json and builder packages).
		 *
		 * @param string[] $dirs Absolute paths.
		 */
		$dirs  = (array) apply_filters( 'wpessential_core_demo_dirs', array() );
		$files = array();
		foreach ( $dirs as $dir ) {
			$files = array_merge( $files, (array) glob( trailingslashit( $dir ) . '*/demo.json' ) );
		}
		foreach ( $files as $file ) {
			$demo = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
			if ( ! is_array( $demo ) || empty( $demo['id'] ) ) {
				continue;
			}
			$demo['source']    = 'bundled';
			$demo['dir']       = dirname( $file );
			$resolve           = static function ( $value ) use ( $file ) {
				// "demo:" paths are relative to the demo folder (development copies outside Core).
				if ( 0 === strpos( (string) $value, 'demo:' ) ) {
					$path = wp_normalize_path( dirname( $file ) . '/' . substr( $value, 5 ) );
					$root = wp_normalize_path( WP_CONTENT_DIR );
					$real = wp_normalize_path( (string) realpath( $path ) );
					foreach ( array( $path, $real ) as $candidate ) {
						if ( 0 === strpos( $candidate, $root ) ) {
							return content_url( substr( $candidate, strlen( $root ) ) );
						}
					}
					$theme = wp_normalize_path( get_theme_root() . '/' . get_stylesheet() );
					$base  = wp_normalize_path( (string) realpath( $theme ) );
					return 0 === strpos( $real, $base ) ? get_theme_file_uri( substr( $real, strlen( $base ) + 1 ) ) : '';
				}
				return self::resolve_asset( $value );
			};
			$demo['thumbnail'] = $resolve( $demo['thumbnail'] ?? '' );
			$demo['preview']   = array_map( $resolve, (array) ( $demo['preview'] ?? array() ) );
			$demos[]           = $demo;
		}
		return $demos;
	}

	/**
	 * Turn "theme:assets/x.webp" or "core:demos/x.webp" into a URL.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public static function resolve_asset( $value ) {
		if ( 0 === strpos( (string) $value, 'theme:' ) ) {
			return get_theme_file_uri( substr( $value, 6 ) );
		}
		if ( 0 === strpos( (string) $value, 'core:' ) ) {
			return (string) apply_filters( 'wpessential_core_asset_url', '', substr( $value, 5 ) );
		}
		return (string) $value;
	}

	/**
	 * Find one demo.
	 *
	 * @param string $id Demo id.
	 * @return array|null
	 */
	public static function get( $id ) {
		foreach ( self::all()['demos'] as $demo ) {
			if ( $demo['id'] === $id ) {
				return $demo;
			}
		}
		return null;
	}

	/**
	 * Local folder holding content.json for a demo + builder. Downloads remote packages.
	 *
	 * @param array  $demo    Demo.
	 * @param string $builder Builder key.
	 * @return string|\WP_Error
	 */
	public static function package_dir( $demo, $builder ) {
		$package = $demo['builders'][ $builder ]['package'] ?? '';
		if ( 'bundled' === $demo['source'] ) {
			$dir = ( $demo['dir'] ?? '' ) . '/' . ( $package ? $package : $builder );
			return is_readable( $dir . '/content.json' ) ? $dir : new \WP_Error( 'wpe_package_missing', __( 'The bundled demo package is incomplete. Re-upload the theme and try again.', 'wpessential' ) );
		}

		if ( 'licensed' === $package ) {
			$package = self::packageUrl( $demo['id'], $builder );
			if ( is_wp_error( $package ) ) {
				return $package;
			}
		}
		if ( ! wp_http_validate_url( $package ) ) {
			return new \WP_Error( 'wpe_package_url', __( 'This demo has no download address in the library.', 'wpessential' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		$uploads = wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'wpessential-demos/' . sanitize_key( $demo['id'] . '-' . $builder );
		// Download on every import: a copy left from an earlier import would hide a newer package on the server.
		$tmp = download_url( $package, 120 );
		if ( is_wp_error( $tmp ) ) {
			/* translators: %s: error message. */
			return new \WP_Error( 'wpe_download', sprintf( __( 'Downloading the demo package failed: %s', 'wpessential' ), $tmp->get_error_message() ) );
		}
		if ( ! WP_Filesystem() ) {
			wp_delete_file( $tmp );
			return new \WP_Error( 'wpe_fs', __( 'WordPress cannot write to the uploads folder. Check its permissions.', 'wpessential' ) );
		}
		global $wp_filesystem;
		if ( $wp_filesystem && $wp_filesystem->is_dir( $dir ) ) {
			$wp_filesystem->delete( $dir, true );
		}
		$result = unzip_file( $tmp, $dir );
		wp_delete_file( $tmp );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		clearstatcache( true, $dir . '/content.json' );
		return file_exists( $dir . '/content.json' ) ? $dir : new \WP_Error( 'wpe_package_invalid', __( 'The downloaded package does not contain content.json.', 'wpessential' ) );
	}
}
