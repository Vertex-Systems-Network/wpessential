<?php
/**
 * Proves to the licence server that a connect or trial request really comes from this site.
 *
 * Before such a request, Free makes a random one-time token, keeps only its SHA-256 hash for a
 * few minutes and sends the token to the server. The server then reads the hash from this site's
 * public `wpessential/v1/site-proof` route and compares. Someone who names another person's
 * domain cannot make that domain publish the hash of their token, so they cannot claim its trial.
 * The route only ever shows a short-lived hash, never a token or any site data.
 *
 * @package WPEssential
 */

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_REST_Response;

final class SiteProof {

	public const NAMESPACE = 'wpessential/v1';
	public const ROUTE     = '/site-proof';
	private const OPTION   = 'wpessential_site_proof';
	private const TTL      = 600;

	public function register(): void {
		if ( function_exists( 'add_action' ) ) {
			add_action( 'rest_api_init', [ $this, 'routes' ] );
		}
	}

	public function routes(): void {
		register_rest_route(
			self::NAMESPACE,
			self::ROUTE,
			[
				'methods'             => 'GET',
				'callback'            => fn (): WP_REST_Response => new WP_REST_Response( [ 'proof' => $this->published( time() ) ], 200, [ 'Cache-Control' => 'no-store' ] ),
				'permission_callback' => '__return_true',
			]
		);
	}

	/** New one-time token for the next request; only its hash is kept on this site. */
	public function issue( int $now ): string {
		$token = bin2hex( random_bytes( 32 ) );
		update_option(
			self::OPTION,
			[
				'hash'    => self::hash( $token ),
				'expires' => $now + self::TTL,
			],
			false
		);

		return $token;
	}

	/** Hash currently published, or '' when none is pending. */
	public function published( int $now ): string {
		$stored = get_option( self::OPTION, [] );
		if ( ! is_array( $stored ) || ! is_string( $stored['hash'] ?? null ) || (int) ( $stored['expires'] ?? 0 ) < $now ) {
			return '';
		}

		return $stored['hash'];
	}

	public static function hash( string $token ): string {
		return hash( 'sha256', 'wpessential-site-proof|' . $token );
	}
}
