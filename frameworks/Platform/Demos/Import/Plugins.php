<?php
/**
 * Install / activate / deactivate / delete plugins a demo needs.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Platform\Demos\Import;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin manager.
 */
class Plugins {

	/**
	 * Load the admin plugin API.
	 */
	private static function load() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
	}

	/**
	 * Plugin basename for a slug, if installed.
	 *
	 * @param string $slug Slug.
	 * @return string
	 */
	public static function file( $slug ) {
		self::load();
		foreach ( array_keys( get_plugins() ) as $file ) {
			if ( dirname( $file ) === $slug || basename( $file, '.php' ) === $slug ) {
				return $file;
			}
		}
		return '';
	}

	/**
	 * State of one plugin: missing, inactive or active.
	 *
	 * @param array $plugin Manifest entry.
	 * @return array
	 */
	public static function describe( $plugin ) {
		self::load();
		$file    = self::file( $plugin['slug'] );
		$data    = $file ? get_plugins()[ $file ] : array();
		$status  = $file ? ( is_plugin_active( $file ) ? 'active' : 'inactive' ) : 'missing';
		$wanted  = $plugin['state'] ?? 'required';
		$action  = 'none';
		$pending = false;
		if ( 'conflicts' === $wanted ) {
			$action  = 'active' === $status ? 'deactivate' : 'none';
			$pending = 'active' === $status;
		} elseif ( 'missing' === $status ) {
			$action  = 'install';
			$pending = true;
		} elseif ( 'inactive' === $status ) {
			$action  = 'activate';
			$pending = true;
		}
		return array(
			'slug'    => $plugin['slug'],
			'name'    => $plugin['name'] ?? ( $data['Name'] ?? $plugin['slug'] ),
			'state'   => $wanted,
			'source'  => $plugin['source'] ?? 'wporg',
			'status'  => $status,
			'version' => $data['Version'] ?? '',
			'action'  => $action,
			'pending' => $pending,
		);
	}

	/**
	 * Bring a plugin to the state the demo asks for.
	 *
	 * @param array $plugin Manifest entry.
	 * @return string|\WP_Error What was done.
	 */
	public static function apply( $plugin ) {
		$info = self::describe( $plugin );
		switch ( $info['action'] ) {
			case 'install':
				$result = self::install( $plugin );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
				$result = self::activate( $plugin['slug'] );
				return is_wp_error( $result ) ? $result : 'installed';
			case 'activate':
				$result = self::activate( $plugin['slug'] );
				return is_wp_error( $result ) ? $result : 'activated';
			case 'deactivate':
				self::deactivate( $plugin['slug'] );
				return 'deactivated';
		}
		return 'ok';
	}

	/**
	 * Download and install.
	 *
	 * @param array $plugin Manifest entry (slug, source, url).
	 * @return true|\WP_Error
	 */
	public static function install( $plugin ) {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return new \WP_Error( 'wpe_cap', __( 'Your account is not allowed to install plugins.', 'wpessential' ) );
		}
		self::load();
		$source = $plugin['source'] ?? 'wporg';
		$url    = '';
		if ( 'wporg' === $source ) {
			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $plugin['slug'],
					'fields' => array( 'sections' => false ),
				)
			);
			if ( is_wp_error( $api ) ) {
				/* translators: 1: plugin name, 2: error. */
				return new \WP_Error( 'wpe_wporg', sprintf( __( 'WordPress.org did not answer for %1$s (%2$s). Check the server can reach wordpress.org, or install it manually.', 'wpessential' ), $plugin['name'] ?? $plugin['slug'], $api->get_error_message() ) );
			}
			$url = is_object( $api ) && isset( $api->download_link ) ? (string) $api->download_link : '';
		} elseif ( 'licensed' === $source ) {
			$url = Demos::packageUrl( $plugin['slug'], 'plugin' );
			if ( is_wp_error( $url ) ) {
				return $url;
			}
		} elseif ( 'bundled' === $source ) {
			$url = '';
		} else {
			$url = $plugin['url'] ?? '';
		}
		$url = apply_filters( 'wpessential_core_plugin_package', $url, $plugin );
		if ( ! $url ) {
			return new \WP_Error( 'wpe_no_source', __( 'No download source is set for this plugin.', 'wpessential' ) );
		}

		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $url, array( 'overwrite_package' => true ) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		if ( $skin->get_errors()->has_errors() ) {
			return $skin->get_errors();
		}
		if ( ! $result ) {
			return new \WP_Error( 'wpe_install', __( 'The plugin could not be installed. The server may lack write access to wp-content/plugins.', 'wpessential' ) );
		}
		wp_clean_plugins_cache();
		return true;
	}

	/**
	 * Activate by slug.
	 *
	 * @param string $slug Slug.
	 * @return true|\WP_Error
	 */
	public static function activate( $slug ) {
		self::load();
		$file = self::file( $slug );
		if ( ! $file ) {
			return new \WP_Error( 'wpe_missing', __( 'Plugin is not installed.', 'wpessential' ) );
		}
		if ( is_plugin_active( $file ) ) {
			return true;
		}
		$result = activate_plugin( $file, '', false, false );
		// Stop plugin welcome screens from hijacking the next admin page.
		foreach ( array( '_wc_activation_redirect', 'elementor_activation_redirect', '_wpforms_activation_redirect' ) as $transient ) {
			delete_transient( $transient );
		}
		return is_wp_error( $result ) ? $result : true;
	}

	/**
	 * Deactivate by slug.
	 *
	 * @param string $slug Slug.
	 */
	public static function deactivate( $slug ) {
		self::load();
		$file = self::file( $slug );
		if ( $file ) {
			deactivate_plugins( $file, true );
		}
	}

	/**
	 * Delete by slug (deactivates first).
	 *
	 * @param string $slug Slug.
	 * @return true|\WP_Error
	 */
	public static function delete( $slug ) {
		if ( ! current_user_can( 'delete_plugins' ) ) {
			return new \WP_Error( 'wpe_cap', __( 'Your account is not allowed to delete plugins.', 'wpessential' ) );
		}
		self::load();
		$file = self::file( $slug );
		if ( ! $file ) {
			return true;
		}
		deactivate_plugins( $file, true );
		$result = delete_plugins( array( $file ) );
		return is_wp_error( $result ) ? $result : true;
	}

	/**
	 * Every plugin mentioned by any demo, de-duplicated.
	 *
	 * @return array
	 */
	public static function catalogue() {
		$seen = array();
		foreach ( Demos::all()['demos'] as $demo ) {
			foreach ( (array) ( $demo['builders'] ?? array() ) as $pack ) {
				foreach ( (array) ( $pack['plugins'] ?? array() ) as $plugin ) {
					if ( empty( $seen[ $plugin['slug'] ] ) && 'conflicts' !== ( $plugin['state'] ?? '' ) ) {
						$seen[ $plugin['slug'] ] = self::describe( $plugin ) + array( 'url' => $plugin['url'] ?? '' );
					}
				}
			}
		}
		return array_values( $seen );
	}
}
