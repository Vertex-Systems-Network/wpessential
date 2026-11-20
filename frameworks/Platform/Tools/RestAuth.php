<?php

declare(strict_types=1);

namespace WPEssential\Platform\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Closure;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST API authentication beyond cookies and Application Passwords (REST step 2):
 *  - per-user API keys "wpek_<id>_<secret>" sent as "Authorization: Bearer …" or "X-WPE-API-Key",
 *    read-only or read/write, optional expiry; only a keyed hash is stored;
 *  - JWT: POST wpessential/v1/token (username + password) returns a short-lived HS256 access token
 *    and a rotating refresh token; /token/refresh and /token/revoke.
 * Both are off until switched on in REST API Control. Secrets are never logged or shown again.
 */
final class RestAuth {

	public const KEYS_META    = '_wpessential_api_keys';
	public const KEY_INDEX    = 'wpessential_api_key_index';
	public const JWT_SECRET   = 'wpessential_jwt_secret';
	public const JWT_VERSION  = '_wpessential_jwt_version';
	public const REFRESH_META = '_wpessential_jwt_refresh';
	public const NAMESPACE    = 'wpessential/v1';
	private const REFRESH_TTL = 30 * DAY_IN_SECONDS;

	/** Scope of the key that signed this request in; null when no key was used. */
	private static ?string $scope = null;
	/** A key or token was sent but did not check out. */
	private static ?string $failure = null;

	/** @param Closure(): array<string,mixed> $settings */
	public function __construct( private readonly Closure $settings ) {}

	public function register(): void {
		add_filter( 'determine_current_user', [ $this, 'authenticate' ], 25 );
		add_filter( 'rest_authentication_errors', [ $this, 'errors' ], 25 );
		add_filter( 'rest_pre_dispatch', [ $this, 'enforceScope' ], 5, 3 );
		add_action( 'rest_api_init', [ $this, 'routes' ] );
	}

	// ── API keys ────────────────────────────────────────────────────────────────

	/**
	 * Creates a key for a user and returns the full key once.
	 *
	 * @return array{key:string,id:string}
	 */
	public static function createKey( int $userId, string $name, string $scope, int $days ): array {
		$id     = bin2hex( random_bytes( 6 ) );
		$secret = self::random( 32 );
		$keys   = self::keys( $userId );
		$keys[ $id ] = [
			'name'      => mb_substr( trim( wp_strip_all_tags( $name ) ), 0, 80 ) ?: __( 'API key', 'wpessential' ),
			'hash'      => self::hash( $secret ),
			'scope'     => $scope === 'write' ? 'write' : 'read',
			'created'   => time(),
			'expires'   => $days > 0 ? time() + $days * DAY_IN_SECONDS : 0,
			'last_used' => 0,
		];
		update_user_meta( $userId, self::KEYS_META, $keys );
		$index        = (array) get_option( self::KEY_INDEX, [] );
		$index[ $id ] = $userId;
		update_option( self::KEY_INDEX, $index, false );

		return [
			'key' => 'wpek_' . $id . '_' . $secret,
			'id'  => $id,
		];
	}

	public static function revokeKey( int $userId, string $id ): void {
		$keys = self::keys( $userId );
		unset( $keys[ $id ] );
		update_user_meta( $userId, self::KEYS_META, $keys );
		$index = (array) get_option( self::KEY_INDEX, [] );
		unset( $index[ $id ] );
		update_option( self::KEY_INDEX, $index, false );
	}

	/** @return array<string,array{name:string,hash:string,scope:string,created:int,expires:int,last_used:int}> */
	public static function keys( int $userId ): array {
		$keys = get_user_meta( $userId, self::KEYS_META, true );

		return is_array( $keys ) ? $keys : [];
	}

	/** @return array{0:string,1:string}|null Key id and secret from "wpek_<id>_<secret>". */
	public static function parseKey( string $key ): ?array {
		return preg_match( '/^wpek_([a-f0-9]{12})_([A-Za-z0-9]{32})$/', $key, $m ) === 1 ? [ $m[1], $m[2] ] : null;
	}

	// ── JWT ─────────────────────────────────────────────────────────────────────

	/** @param array<string,mixed> $claims */
	public static function sign( array $claims, string $secret ): string {
		$head = self::b64( (string) wp_json_encode( [ 'alg' => 'HS256', 'typ' => 'JWT' ] ) );
		$body = self::b64( (string) wp_json_encode( $claims ) );

		return $head . '.' . $body . '.' . self::b64( hash_hmac( 'sha256', $head . '.' . $body, $secret, true ) );
	}

	/** @return array<string,mixed>|null Claims of a valid, unexpired HS256 token from this site. */
	public static function verify( string $token, string $secret, int $now, string $issuer ): ?array {
		$parts = explode( '.', $token );
		if ( count( $parts ) !== 3 ) {
			return null;
		}
		[ $head, $body, $sig ] = $parts;
		$header = json_decode( (string) self::unb64( $head ), true );
		if ( ! is_array( $header ) || ( $header['alg'] ?? '' ) !== 'HS256' ) {
			return null; // Never "none" or another algorithm.
		}
		if ( ! hash_equals( self::b64( hash_hmac( 'sha256', $head . '.' . $body, $secret, true ) ), $sig ) ) {
			return null;
		}
		$claims = json_decode( (string) self::unb64( $body ), true );
		if ( ! is_array( $claims ) || ! is_int( $claims['exp'] ?? null ) || $claims['exp'] <= $now || ( $claims['iss'] ?? '' ) !== $issuer || ! is_int( $claims['sub'] ?? null ) ) {
			return null;
		}

		return $claims;
	}

	public function routes(): void {
		$open = static fn (): bool => true;
		register_rest_route(
			self::NAMESPACE,
			'/token',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'issue' ],
				'permission_callback' => $open,
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/token/refresh',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'refresh' ],
				'permission_callback' => $open,
			]
		);
		register_rest_route(
			self::NAMESPACE,
			'/token/revoke',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'revoke' ],
				'permission_callback' => static fn (): bool => is_user_logged_in(),
			]
		);
	}

	public function issue( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$blocked = $this->jwtBlocked();
		if ( $blocked ) {
			return $blocked;
		}
		$user = wp_authenticate( (string) $request->get_param( 'username' ), (string) $request->get_param( 'password' ) );
		if ( is_wp_error( $user ) ) {
			return new WP_Error( 'wpe_jwt_login_failed', __( 'The username or password is not correct.', 'wpessential' ), [ 'status' => 401 ] );
		}

		return new WP_REST_Response( $this->tokens( (int) $user->ID ), 200 );
	}

	public function refresh( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$blocked = $this->jwtBlocked();
		if ( $blocked ) {
			return $blocked;
		}
		$token = (string) $request->get_param( 'refresh_token' );
		if ( preg_match( '/^(\d+)\.([A-Za-z0-9]{48})$/', $token, $m ) !== 1 ) {
			return new WP_Error( 'wpe_jwt_refresh_invalid', __( 'The refresh token is not valid.', 'wpessential' ), [ 'status' => 401 ] );
		}
		$userId = (int) $m[1];
		$stored = self::refreshTokens( $userId );
		$hash   = self::hash( $m[2] );
		if ( ! isset( $stored[ $hash ] ) || $stored[ $hash ] < time() ) {
			return new WP_Error( 'wpe_jwt_refresh_invalid', __( 'The refresh token is not valid.', 'wpessential' ), [ 'status' => 401 ] );
		}
		unset( $stored[ $hash ] ); // Each refresh token works once.
		update_user_meta( $userId, self::REFRESH_META, $stored );

		return new WP_REST_Response( $this->tokens( $userId ), 200 );
	}

	/** Signs out every token of the current user. */
	public function revoke(): WP_REST_Response {
		$userId = get_current_user_id();
		update_user_meta( $userId, self::JWT_VERSION, (int) get_user_meta( $userId, self::JWT_VERSION, true ) + 1 );
		delete_user_meta( $userId, self::REFRESH_META );

		return new WP_REST_Response( [ 'revoked' => true ], 200 );
	}

	// ── Request authentication ──────────────────────────────────────────────────

	public function authenticate( mixed $userId ): mixed {
		if ( ! empty( $userId ) || ! self::isRestRequest() ) {
			return $userId;
		}
		$settings = ( $this->settings )();
		$header   = self::header( 'HTTP_X_WPE_API_KEY' );
		$bearer   = preg_match( '/^Bearer\s+(\S+)$/i', self::header( 'HTTP_AUTHORIZATION' ), $m ) === 1 ? $m[1] : '';
		$key      = $header !== '' ? $header : ( str_starts_with( $bearer, 'wpek_' ) ? $bearer : '' );

		if ( $key !== '' ) {
			if ( empty( $settings['api_keys'] ) ) {
				return $userId;
			}
			$found = self::checkKey( $key );
			if ( $found === null ) {
				self::$failure = 'key';
				return $userId;
			}
			self::$scope = $found['scope'];

			return $found['user'];
		}
		// Only real JWTs ("eyJ…"); AI Connect keys "1.<uuid>.<secret>" also have two dots.
		if ( str_starts_with( $bearer, 'eyJ' ) && substr_count( $bearer, '.' ) === 2 && ! empty( $settings['jwt'] ) ) {
			$claims = self::verify( $bearer, self::secret(), time(), untrailingslashit( home_url() ) );
			if ( $claims === null || (int) ( $claims['ver'] ?? -1 ) !== (int) get_user_meta( (int) $claims['sub'], self::JWT_VERSION, true ) || ! get_userdata( (int) $claims['sub'] ) ) {
				self::$failure = 'jwt';
				return $userId;
			}

			return (int) $claims['sub'];
		}

		return $userId;
	}

	public function errors( mixed $result ): mixed {
		if ( $result !== null || self::$failure === null ) {
			return $result;
		}

		return self::$failure === 'key'
			? new WP_Error( 'wpe_api_key_invalid', __( 'The API key is not valid, has expired or was revoked.', 'wpessential' ), [ 'status' => 401 ] )
			: new WP_Error( 'wpe_jwt_invalid', __( 'The token is not valid or has expired.', 'wpessential' ), [ 'status' => 401 ] );
	}

	/** Read-only keys can only read. */
	public function enforceScope( mixed $result, mixed $server, mixed $request ): mixed {
		if ( $result === null && self::$scope === 'read' && $request instanceof WP_REST_Request && ! in_array( $request->get_method(), [ 'GET', 'HEAD', 'OPTIONS' ], true ) ) {
			return new WP_Error( 'wpe_api_key_read_only', __( 'This API key is read-only.', 'wpessential' ), [ 'status' => 403 ] );
		}

		return $result;
	}

	/** @return array{user:int,scope:string}|null */
	private static function checkKey( string $key ): ?array {
		$parts = self::parseKey( $key );
		if ( $parts === null ) {
			return null;
		}
		[ $id, $secret ] = $parts;
		$index  = (array) get_option( self::KEY_INDEX, [] );
		$userId = (int) ( $index[ $id ] ?? 0 );
		$keys   = $userId > 0 ? self::keys( $userId ) : [];
		$entry  = $keys[ $id ] ?? null;
		if ( ! is_array( $entry ) || ! hash_equals( (string) $entry['hash'], self::hash( $secret ) ) || ( $entry['expires'] > 0 && $entry['expires'] < time() ) ) {
			return null;
		}
		if ( $entry['last_used'] < time() - HOUR_IN_SECONDS ) {
			$keys[ $id ]['last_used'] = time();
			update_user_meta( $userId, self::KEYS_META, $keys );
		}

		return [
			'user'  => $userId,
			'scope' => (string) $entry['scope'],
		];
	}

	private function jwtBlocked(): ?WP_Error {
		if ( empty( ( $this->settings )()['jwt'] ) ) {
			return new WP_Error( 'wpe_jwt_off', __( 'JWT tokens are switched off on this site.', 'wpessential' ), [ 'status' => 404 ] );
		}
		if ( ! is_ssl() && wp_get_environment_type() !== 'local' ) {
			return new WP_Error( 'wpe_jwt_https', __( 'Tokens are only issued over HTTPS.', 'wpessential' ), [ 'status' => 403 ] );
		}

		return null;
	}

	/** @return array{access_token:string,token_type:string,expires_in:int,refresh_token:string} */
	private function tokens( int $userId ): array {
		$ttl    = max( 300, min( DAY_IN_SECONDS, (int) ( ( $this->settings )()['jwt_ttl'] ?? HOUR_IN_SECONDS ) ) );
		$now    = time();
		$access = self::sign(
			[
				'iss' => untrailingslashit( home_url() ),
				'sub' => $userId,
				'iat' => $now,
				'exp' => $now + $ttl,
				'jti' => bin2hex( random_bytes( 8 ) ),
				'ver' => (int) get_user_meta( $userId, self::JWT_VERSION, true ),
			],
			self::secret()
		);
		$secret = self::random( 48 );
		$stored = array_filter( self::refreshTokens( $userId ), static fn ( int $exp ): bool => $exp > $now );
		$stored = array_slice( $stored, -9, null, true ) + [ self::hash( $secret ) => $now + self::REFRESH_TTL ];
		update_user_meta( $userId, self::REFRESH_META, $stored );

		return [
			'access_token'  => $access,
			'token_type'    => 'Bearer',
			'expires_in'    => $ttl,
			'refresh_token' => $userId . '.' . $secret,
		];
	}

	/** @return array<string,int> Refresh token hash => expiry. */
	private static function refreshTokens( int $userId ): array {
		$stored = get_user_meta( $userId, self::REFRESH_META, true );

		return is_array( $stored ) ? array_map( 'intval', $stored ) : [];
	}

	private static function secret(): string {
		$secret = (string) get_option( self::JWT_SECRET, '' );
		if ( strlen( $secret ) < 64 ) {
			$secret = self::random( 64 );
			update_option( self::JWT_SECRET, $secret, false );
		}

		return $secret;
	}

	private static function hash( string $secret ): string {
		return hash_hmac( 'sha256', $secret, wp_salt( 'auth' ) );
	}

	private static function random( int $length ): string {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$out   = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$out .= $chars[ random_int( 0, 61 ) ];
		}

		return $out;
	}

	private static function b64( string $data ): string {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- JWT encoding.
	}

	private static function unb64( string $data ): string|false {
		return base64_decode( strtr( $data, '-_', '+/' ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- JWT decoding.
	}

	private static function header( string $name ): string {
		$value = $_SERVER[ $name ] ?? ( $name === 'HTTP_AUTHORIZATION' ? ( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '' ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Compared and pattern-checked only.

		return is_string( $value ) ? trim( wp_unslash( $value ) ) : '';
	}

	private static function isRestRequest(): bool {
		$uri = (string) ( $_SERVER['REQUEST_URI'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- Only searched.

		return ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || str_contains( $uri, '/' . rest_get_url_prefix() . '/' ) || str_contains( $uri, 'rest_route=' );
	}
}
