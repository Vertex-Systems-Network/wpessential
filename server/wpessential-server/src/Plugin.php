<?php
/**
 * Bootstrap.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Wires modules.
 */
final class Plugin {

	/**
	 * Instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook modules.
	 */
	private function __construct() {
		Install::maybe_upgrade();
		Events::hooks();
		Plans::hooks();
		Orders::hooks();
		Renewals::hooks();
		Demos::hooks();
		Accounts::hooks();
		MyAccount::hooks();
		add_action( 'rest_api_init', array( new Api(), 'routes' ) );
		if ( is_admin() ) {
			Admin::hooks();
		}
	}

	/**
	 * Settings with defaults.
	 *
	 * @param string $key Optional key.
	 * @return mixed
	 */
	public static function settings( $key = null ) {
		$settings = wp_parse_args(
			(array) get_option( 'wpes_server_settings', array() ),
			array(
				'grace_days'    => 7,
				'remind_days'   => 7,
				'free_staging'  => 1,
				'token_days'    => 30,
				'download_mins' => 15,
				'playground'    => 1,
				'event_days'    => 365,
			)
		);
		return null === $key ? $settings : ( $settings[ $key ] ?? null );
	}

	/**
	 * HMAC secret (created once).
	 *
	 * @return string
	 */
	public static function secret() {
		$secret = get_option( 'wpes_server_secret' );
		if ( ! $secret ) {
			$secret = wp_generate_password( 64, true, true );
			update_option( 'wpes_server_secret', $secret, false );
		}
		return $secret;
	}
}
