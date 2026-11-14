<?php
/**
 * Checks that a connect/trial request comes from the site it names: the site publishes the hash
 * of a one-time token at wpessential/v1/site-proof and the request carries the token.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Site ownership proof.
 */
class SiteProof {

	/**
	 * Hash the site publishes for a token (must match Free's SiteProof::hash()).
	 *
	 * @param string $token Token.
	 * @return string
	 */
	public static function hash( $token ) {
		return hash( 'sha256', 'wpessential-site-proof|' . $token );
	}

	/**
	 * Whether a published hash matches the token (pure, unit-tested).
	 *
	 * @param string $token     Token sent with the request.
	 * @param string $published Hash read from the site.
	 * @return bool
	 */
	public static function matches( $token, $published ) {
		return is_string( $token ) && strlen( $token ) >= 32 && is_string( $published ) && '' !== $published
			&& hash_equals( $published, self::hash( $token ) );
	}

	/**
	 * Read the site's published hash and compare.
	 *
	 * @param string $site  Site URL as sent by the plugin (home_url()).
	 * @param string $token Token.
	 * @return bool
	 */
	public static function verify( $site, $token ) {
		$token = (string) $token;
		if ( strlen( $token ) < 32 ) {
			return false;
		}
		$site = esc_url_raw( (string) $site );
		if ( '' === $site || ! wp_http_validate_url( $site ) ) {
			return false;
		}
		// ?rest_route works with and without pretty permalinks.
		$url      = add_query_arg( 'rest_route', '/wpessential/v1/site-proof', trailingslashit( $site ) );
		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'     => 8,
				'redirection' => 2,
				'headers'     => array( 'Accept' => 'application/json' ),
			)
		);
		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}
		$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		return self::matches( $token, is_array( $data ) ? (string) ( $data['proof'] ?? '' ) : '' );
	}
}
