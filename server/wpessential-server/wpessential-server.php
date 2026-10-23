<?php
/**
 * Plugin Name:          WPEssential Server
 * Plugin URI:           https://wpessential.org/
 * Description:          Demo library, licensing and WooCommerce subscriptions (free, monthly, quarterly, yearly, lifetime, enterprise, white label) for WPEssential themes.
 * Version:              1.0.0
 * Requires at least:    6.5
 * Requires PHP:         7.4
 * Requires Plugins:     woocommerce
 * Author:               WPEssential Team
 * Author URI:           https://wpessential.org/
 * License:              GPL-2.0-or-later
 * Text Domain:          wpessential-server
 * WC requires at least: 8.0
 *
 * Runs only on the owner's sites (wpessential.org, vertexsystemsnetwork.com). On any other host it
 * loads nothing, refuses activation and deactivates itself. It is never part of the WPEssential
 * Free or Pro packages (see tools/release/build-free-pro-distributables.php).
 *
 * @package WPEssential\Server
 */

defined( 'ABSPATH' ) || exit;

define( 'WPES_SERVER_VERSION', '1.0.0' );
define( 'WPES_SERVER_FILE', __FILE__ );
define( 'WPES_SERVER_DIR', __DIR__ );

spl_autoload_register(
	static function ( $class_name ) {
		$prefix = 'WPEssential\\Server\\';
		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}
		$file = WPES_SERVER_DIR . '/src/' . str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

// Host lock: nothing below runs on a site that is not the owner's.
require_once WPES_SERVER_DIR . '/src/HostLock.php';
if ( ! WPEssential\Server\HostLock::ok() ) {
	register_activation_hook(
		__FILE__,
		static function () {
			wp_die( esc_html__( 'WPEssential Server only runs on wpessential.org and vertexsystemsnetwork.com.', 'wpessential-server' ), 'WPEssential Server', array( 'back_link' => true ) );
		}
	);
	add_action(
		'admin_init',
		static function () {
			if ( function_exists( 'deactivate_plugins' ) ) {
				deactivate_plugins( plugin_basename( WPES_SERVER_FILE ) );
			}
		}
	);
	return;
}

register_activation_hook( __FILE__, array( 'WPEssential\\Server\\Install', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WPEssential\\Server\\Renewals', 'unschedule' ) );

add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

add_action(
	'plugins_loaded',
	static function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-error"><p>' . esc_html__( 'WPEssential Server needs WooCommerce to sell plans. Activate WooCommerce.', 'wpessential-server' ) . '</p></div>';
				}
			);
			return;
		}
		WPEssential\Server\Plugin::instance();
	}
);
