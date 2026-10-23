<?php
/**
 * The server only ever runs on the owner's sites.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Host allowlist: wpessential.org and vertexsystemsnetwork.com (with or without www). A local
 * development copy is allowed only when WPES_SERVER_DEV is true and the environment is "local".
 */
class HostLock {

	/**
	 * Owner hosts.
	 *
	 * @var string[]
	 */
	const HOSTS = array( 'wpessential.org', 'vertexsystemsnetwork.com' );

	/**
	 * Whether this host may run the server.
	 *
	 * @param string $host Host name.
	 * @param bool   $dev  Local development allowed.
	 * @return bool
	 */
	public static function allowed( $host, $dev = false ) {
		$host = strtolower( trim( (string) $host, ". \t\n" ) );
		$host = 0 === strpos( $host, 'www.' ) ? substr( $host, 4 ) : $host;
		if ( in_array( $host, self::HOSTS, true ) ) {
			return true;
		}
		return $dev && ( 'localhost' === $host || (bool) preg_match( '/\.(local|test)$/', $host ) );
	}

	/**
	 * This site's host.
	 *
	 * @return string
	 */
	public static function host() {
		$host = wp_parse_url( (string) get_option( 'home' ), PHP_URL_HOST );
		return is_string( $host ) ? $host : '';
	}

	/**
	 * Local development switch.
	 *
	 * @return bool
	 */
	public static function dev() {
		return defined( 'WPES_SERVER_DEV' ) && WPES_SERVER_DEV && function_exists( 'wp_get_environment_type' ) && 'local' === wp_get_environment_type();
	}

	/**
	 * Whether the current site may run the server.
	 *
	 * @return bool
	 */
	public static function ok() {
		return self::allowed( self::host(), self::dev() );
	}
}
