<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_REST_Request;
use WP_REST_Response;

/**
 * OAuth 2.1 for remote MCP clients (Claude custom connectors, ChatGPT): discovery (RFC 9728 / RFC 8414),
 * dynamic client registration (RFC 7591), authorization code with PKCE S256, and refresh.
 *
 * The site owner approves the client on a WordPress consent screen; approval creates a normal
 * "WPEssential AI" session (application password), and the access token is that session's
 * connector key. Disconnecting the session in AI Connect revokes the token.
 */
final class AiOAuth {

	public const AUTHORIZE_ACTION = 'wpe_ai_oauth_authorize';
	private const CLIENT_OPTION   = 'wpe_ai_oauth_clients';
	private const CODE_PREFIX     = 'wpe_ai_oauth_code_';
	private const MAX_CLIENTS     = 100;

	public function __construct( private readonly AiConnect $connect ) {}

	public function register(): void {
		if ( ! function_exists( 'add_action' ) ) {
			return;
		}
		add_action( 'init', [ $this, 'wellKnown' ], 0 );
		add_action( 'rest_api_init', [ $this, 'routes' ] );
		add_action( 'admin_post_' . self::AUTHORIZE_ACTION, [ $this, 'authorize' ] );
		add_action( 'admin_post_nopriv_' . self::AUTHORIZE_ACTION, [ $this, 'authorize' ] );
		add_filter( 'rest_post_dispatch', [ $this, 'challenge' ], 10, 3 );
	}

	public static function issuer(): string {
		return untrailingslashit( home_url() );
	}

	public static function resourceMetadataUrl(): string {
		return self::issuer() . '/.well-known/oauth-protected-resource';
	}

	/** @return array<string,mixed> */
	public static function resourceMetadata(): array {
		return [
			'resource'                 => AiConnect::endpoint(),
			'authorization_servers'    => [ self::issuer() ],
			'bearer_methods_supported' => [ 'header' ],
			'resource_name'            => 'WPEssential',
		];
	}

	/** @return array<string,mixed> */
	public static function serverMetadata(): array {
		return [
			'issuer'                                => self::issuer(),
			'authorization_endpoint'                => add_query_arg( 'action', self::AUTHORIZE_ACTION, admin_url( 'admin-post.php' ) ),
			'token_endpoint'                        => rest_url( McpServer::NAMESPACE . '/oauth/token' ),
			'registration_endpoint'                 => rest_url( McpServer::NAMESPACE . '/oauth/register' ),
			'response_types_supported'              => [ 'code' ],
			'grant_types_supported'                 => [ 'authorization_code', 'refresh_token' ],
			'code_challenge_methods_supported'      => [ 'S256' ],
			'token_endpoint_auth_methods_supported' => [ 'none' ],
		];
	}

	/** Serves the two discovery documents at the site root (any path suffix, per RFC 8414 / 9728). */
	public function wellKnown(): void {
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only compared.
		$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
		$base = rtrim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
		if ( ! str_starts_with( $path, $base . '/.well-known/oauth-' ) && ! str_starts_with( $path, '/.well-known/oauth-' ) ) {
			return;
		}
		if ( ! $this->connect->enabled() ) {
			return;
		}
		if ( str_contains( $path, '/.well-known/oauth-protected-resource' ) ) {
			self::json( self::resourceMetadata() );
		}
		if ( str_contains( $path, '/.well-known/oauth-authorization-server' ) || str_contains( $path, '/.well-known/openid-configuration' ) ) {
			self::json( self::serverMetadata() );
		}
	}

	public function routes(): void {
		register_rest_route(
			McpServer::NAMESPACE,
			'/oauth/register',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'registerClient' ],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			McpServer::NAMESPACE,
			'/oauth/token',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'token' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/** Tells a client without a token where to find the authorization server. */
	public function challenge( mixed $response, mixed $server, mixed $request ): mixed {
		if ( $response instanceof WP_REST_Response && $request instanceof WP_REST_Request
			&& $request->get_route() === '/' . McpServer::NAMESPACE . McpServer::ROUTE
			&& in_array( $response->get_status(), [ 401, 403 ], true ) && ! is_user_logged_in() ) {
			$response->set_status( 401 );
			$response->header( 'WWW-Authenticate', 'Bearer resource_metadata="' . self::resourceMetadataUrl() . '"' );
		}

		return $response;
	}

	public function registerClient( WP_REST_Request $request ): WP_REST_Response {
		if ( ! $this->connect->enabled() ) {
			return self::oauthError( 'access_denied', 'AI Connect is off.', 403 );
		}
		$body  = self::params( $request );
		$uris  = is_array( $body['redirect_uris'] ?? null ) ? array_values( array_filter( $body['redirect_uris'], 'is_string' ) ) : [];
		$valid = array_values( array_filter( $uris, [ self::class, 'redirectAllowed' ] ) );
		if ( $valid === [] || count( $valid ) !== count( $uris ) ) {
			return self::oauthError( 'invalid_redirect_uri', 'redirect_uris must be https (or loopback http) URLs.' );
		}
		$name           = is_string( $body['client_name'] ?? null ) ? sanitize_text_field( $body['client_name'] ) : 'MCP client';
		$id             = wp_generate_uuid4();
		$clients        = self::clients();
		$clients        = array_slice( $clients, - ( self::MAX_CLIENTS - 1 ), null, true );
		$clients[ $id ] = [
			'name'          => substr( $name, 0, 80 ),
			'redirect_uris' => $valid,
			'created'       => time(),
		];
		update_option( self::CLIENT_OPTION, $clients, false );

		return new WP_REST_Response(
			[
				'client_id'                  => $id,
				'client_id_issued_at'        => time(),
				'client_name'                => $clients[ $id ]['name'],
				'redirect_uris'              => $valid,
				'grant_types'                => [ 'authorization_code', 'refresh_token' ],
				'response_types'             => [ 'code' ],
				'token_endpoint_auth_method' => 'none',
			],
			201
		);
	}

	/** Consent screen (GET) and decision (POST) of the authorization endpoint. */
	public function authorize(): void {
		// phpcs:disable WordPress.Security.NonceVerification -- GET shows the consent form; POST is nonce-checked below.
		$args = [];
		foreach ( [ 'client_id', 'redirect_uri', 'state', 'code_challenge', 'code_challenge_method', 'response_type', 'scope', 'resource' ] as $key ) {
			$args[ $key ] = isset( $_REQUEST[ $key ] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST[ $key ] ) ) : '';
		}
		// phpcs:enable
		$client = self::clients()[ $args['client_id'] ] ?? null;
		if ( ! is_array( $client ) || ! in_array( $args['redirect_uri'], $client['redirect_uris'], true ) ) {
			wp_die( esc_html__( 'Unknown AI client or redirect address. Remove the connector and add it again.', 'wpessential' ), 'WPEssential', [ 'response' => 400 ] );
		}
		if ( $args['response_type'] !== 'code' || $args['code_challenge'] === '' || $args['code_challenge_method'] !== 'S256' ) {
			self::redirectBack( $args, [ 'error' => 'invalid_request' ] );
		}
		if ( ! is_user_logged_in() ) {
			auth_redirect();
		}
		if ( ! current_user_can( 'manage_options' ) || ! $this->connect->enabled() ) {
			self::redirectBack( $args, [ 'error' => 'access_denied' ] );
		}

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			check_admin_referer( self::AUTHORIZE_ACTION );
			if ( ! isset( $_POST['allow'] ) ) {
				self::redirectBack( $args, [ 'error' => 'access_denied' ] );
			}
			$user    = get_current_user_id();
			$created = $this->connect->connect( $user, self::clientKind( (string) $client['name'] ) );
			if ( $created instanceof \WP_Error ) {
				self::redirectBack( $args, [ 'error' => 'server_error' ] );
			}
			$code = wp_generate_password( 40, false );
			set_transient(
				self::CODE_PREFIX . hash( 'sha256', $code ),
				[
					'client_id'    => $args['client_id'],
					'redirect_uri' => $args['redirect_uri'],
					'challenge'    => $args['code_challenge'],
					'key'          => AiConnect::key( $user, $created['uuid'], $created['password'] ),
				],
				600
			);
			self::redirectBack( $args, [ 'code' => $code ] );
		}

		$this->consent( $args, (string) $client['name'] );
		exit;
	}

	public function token( WP_REST_Request $request ): WP_REST_Response {
		$body  = self::params( $request );
		$grant = is_string( $body['grant_type'] ?? null ) ? $body['grant_type'] : '';

		if ( $grant === 'refresh_token' ) {
			$key = is_string( $body['refresh_token'] ?? null ) ? $body['refresh_token'] : '';

			return $this->connect->enabled() && $this->connect->verifyKey( $key ) !== null ? self::tokens( $key ) : self::oauthError( 'invalid_grant', 'The connection was revoked. Connect again.' );
		}
		if ( $grant !== 'authorization_code' ) {
			return self::oauthError( 'unsupported_grant_type', 'Use authorization_code or refresh_token.' );
		}
		$code      = is_string( $body['code'] ?? null ) ? $body['code'] : '';
		$transient = self::CODE_PREFIX . hash( 'sha256', $code );
		$stored    = get_transient( $transient );
		delete_transient( $transient );
		$verifier = is_string( $body['code_verifier'] ?? null ) ? $body['code_verifier'] : '';
		if ( ! is_array( $stored )
			|| ( $body['client_id'] ?? '' ) !== $stored['client_id']
			|| ( $body['redirect_uri'] ?? $stored['redirect_uri'] ) !== $stored['redirect_uri']
			|| ! hash_equals( (string) $stored['challenge'], self::challengeFor( $verifier ) ) ) {
			return self::oauthError( 'invalid_grant', 'The authorization code is invalid or expired.' );
		}

		return self::tokens( (string) $stored['key'] );
	}

	public static function challengeFor( string $verifier ): string {
		return rtrim( strtr( base64_encode( hash( 'sha256', $verifier, true ) ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- PKCE S256 (RFC 7636).
	}

	public static function redirectAllowed( string $uri ): bool {
		$parts = wp_parse_url( $uri );
		if ( ! is_array( $parts ) || ! isset( $parts['scheme'], $parts['host'] ) || isset( $parts['fragment'] ) ) {
			return false;
		}

		return $parts['scheme'] === 'https' || ( $parts['scheme'] === 'http' && in_array( $parts['host'], [ 'localhost', '127.0.0.1', '[::1]' ], true ) );
	}

	/** Maps a registered client name to an AI Connect client label. */
	public static function clientKind( string $name ): string {
		$name = strtolower( $name );

		return match ( true ) {
			str_contains( $name, 'chatgpt' ) || str_contains( $name, 'openai' ) => 'chatgpt',
			str_contains( $name, 'claude' ) || str_contains( $name, 'anthropic' ) => 'claude-ai',
			default => 'generic',
		};
	}

	/** @param array<string,string> $args */
	private function consent( array $args, string $clientName ): void {
		$host = (string) wp_parse_url( $args['redirect_uri'], PHP_URL_HOST );
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'X-Frame-Options: DENY' );
		echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . esc_html__( 'Connect AI client', 'wpessential' ) . '</title>';
		echo '<style>body{font:15px/1.5 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#f0f0f1;margin:0;display:grid;place-items:center;min-height:100vh}main{background:#fff;max-width:420px;padding:32px;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08)}h1{font-size:20px;margin:0 0 12px}p{color:#50575e}.actions{display:flex;gap:8px;margin-top:24px}button{flex:1;padding:10px;border-radius:8px;border:1px solid #c3c4c7;background:#fff;font-size:15px;cursor:pointer}button.allow{background:#2271b1;border-color:#2271b1;color:#fff}</style></head><body><main>';
		echo '<h1>' . esc_html(
			sprintf(
				/* translators: %s: AI client name, e.g. Claude. */
				__( 'Allow %s to connect to WPEssential?', 'wpessential' ),
				$clientName
			)
		) . '</h1>';
		echo '<p>' . esc_html(
			sprintf(
				/* translators: 1: site name, 2: return address host. */
				__( 'It will use the WPEssential tools on %1$s as you, within what AI Connect allows (read-only unless "Allow AI to change settings" is on). You will be sent back to %2$s. You can disconnect it any time under WPEssential → AI Connect.', 'wpessential' ),
				(string) get_bloginfo( 'name' ),
				$host
			)
		) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( self::AUTHORIZE_ACTION );
		echo '<input type="hidden" name="action" value="' . esc_attr( self::AUTHORIZE_ACTION ) . '">';
		foreach ( $args as $name => $value ) {
			echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
		}
		echo '<div class="actions"><button type="submit" name="deny" value="1">' . esc_html__( 'Cancel', 'wpessential' ) . '</button><button class="allow" type="submit" name="allow" value="1">' . esc_html__( 'Allow', 'wpessential' ) . '</button></div></form></main></body></html>';
	}

	/**
	 * @param array<string,string> $args
	 * @param array<string,string> $result
	 */
	private static function redirectBack( array $args, array $result ): never {
		if ( $args['state'] !== '' ) {
			$result['state'] = $args['state'];
		}
		$result['iss'] = self::issuer();
		// The redirect URI was matched against the client's registration, so it is not open.
		wp_redirect( add_query_arg( array_map( 'rawurlencode', $result ), $args['redirect_uri'] ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Registered OAuth redirect URI on another host.
		exit;
	}

	private static function tokens( string $key ): WP_REST_Response {
		$response = new WP_REST_Response(
			[
				'access_token'  => $key,
				'token_type'    => 'Bearer',
				'expires_in'    => 31536000,
				'refresh_token' => $key,
			],
			200
		);
		$response->header( 'Cache-Control', 'no-store' );

		return $response;
	}

	private static function oauthError( string $code, string $description, int $status = 400 ): WP_REST_Response {
		return new WP_REST_Response(
			[
				'error'             => $code,
				'error_description' => $description,
			],
			$status
		);
	}

	/** @return array<string,mixed> */
	private static function params( WP_REST_Request $request ): array {
		$json = $request->get_json_params();
		if ( is_array( $json ) && $json !== [] ) {
			return $json;
		}
		$body = $request->get_body_params();

		return is_array( $body ) ? $body : [];
	}

	/** @return array<string,array{name:string,redirect_uris:list<string>,created:int}> */
	private static function clients(): array {
		$clients = get_option( self::CLIENT_OPTION, [] );

		return is_array( $clients ) ? $clients : [];
	}

	/** @param array<string,mixed> $data */
	private static function json( array $data ): never {
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Access-Control-Allow-Origin: *' );
		echo wp_json_encode( $data, JSON_UNESCAPED_SLASHES );
		exit;
	}
}
