<?php

declare(strict_types=1);

namespace WPEssential\Platform\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Closure;
use WP_Error;
use WP_REST_Request;

/**
 * REST API exposure: who may use it, which routes stay open, discovery, JSONP, XML-RPC and an
 * anonymous rate limit, with a log of what was blocked. Editors keep full access by default so the
 * block editor always works.
 */
final class RestControl {

	public const OPTION     = 'wpessential_rest_control';
	public const LOG_OPTION = 'wpessential_rest_control_log';
	public const LOG_SIZE   = 100;

	/** Namespaces that always stay reachable (AI Connect and its OAuth, oEmbed). */
	public const ALWAYS_ON = [ 'wpessential/v1', 'oembed/1.0' ];

	/** Namespaces common plugins need in public, offered as one-click exceptions when present. */
	public const KNOWN_PUBLIC = [
		'contact-form-7/v1' => 'Contact Form 7',
		'wc/store'          => 'WooCommerce Cart & Checkout blocks',
		'wc/store/v1'       => 'WooCommerce Store API',
		'jetpack/v4'        => 'Jetpack',
		'elementor/v1'      => 'Elementor forms',
		'wpforms/v1'        => 'WPForms',
		'gf/v2'             => 'Gravity Forms',
	];

	public const DEFAULTS = [
		'logged_in_only'      => false,
		'allow_routes'        => [],
		'restricted_roles'    => [],
		'hide_users'          => true,
		'disabled_namespaces' => [],
		'rate_limit'          => 0,
		'rate_exempt_ips'     => [],
		'remove_discovery'    => false,
		'disable_jsonp'       => true,
		'disable_xmlrpc'      => false,
		'log_blocked'         => true,
	];

	/** @param Closure(): bool $enabled */
	public function __construct( private readonly Closure $enabled ) {}

	/**
	 * @return array{logged_in_only:bool,allow_routes:list<string>,restricted_roles:list<string>,hide_users:bool,disabled_namespaces:list<string>,rate_limit:int,rate_exempt_ips:list<string>,remove_discovery:bool,disable_jsonp:bool,disable_xmlrpc:bool,log_blocked:bool}
	 */
	public static function settings(): array {
		$saved = function_exists( 'get_option' ) ? get_option( self::OPTION, [] ) : [];

		return self::normalize( is_array( $saved ) ? $saved : [] );
	}

	/**
	 * @param array<string,mixed> $input
	 * @return array{logged_in_only:bool,allow_routes:list<string>,restricted_roles:list<string>,hide_users:bool,disabled_namespaces:list<string>,rate_limit:int,rate_exempt_ips:list<string>,remove_discovery:bool,disable_jsonp:bool,disable_xmlrpc:bool,log_blocked:bool}
	 */
	public static function normalize( array $input ): array {
		$bool  = static fn ( string $key ): bool => (bool) ( $input[ $key ] ?? self::DEFAULTS[ $key ] );
		$roles = [];
		foreach ( self::lines( $input['restricted_roles'] ?? [] ) as $role ) {
			$role = strtolower( (string) preg_replace( '/[^a-z0-9_\-]/i', '', $role ) );
			if ( $role !== '' && $role !== 'administrator' ) {
				$roles[] = $role;
			}
		}
		$ips = [];
		foreach ( self::lines( $input['rate_exempt_ips'] ?? [] ) as $ip ) {
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) !== false ) {
				$ips[] = $ip;
			}
		}

		return [
			'logged_in_only'      => $bool( 'logged_in_only' ),
			'allow_routes'        => self::routes( $input['allow_routes'] ?? [], false ),
			'restricted_roles'    => array_values( array_unique( $roles ) ),
			'hide_users'          => $bool( 'hide_users' ),
			'disabled_namespaces' => self::routes( $input['disabled_namespaces'] ?? [], true ),
			'rate_limit'          => max( 0, min( 10000, (int) ( $input['rate_limit'] ?? 0 ) ) ),
			'rate_exempt_ips'     => array_values( array_unique( $ips ) ),
			'remove_discovery'    => $bool( 'remove_discovery' ),
			'disable_jsonp'       => $bool( 'disable_jsonp' ),
			'disable_xmlrpc'      => $bool( 'disable_xmlrpc' ),
			'log_blocked'         => $bool( 'log_blocked' ),
		];
	}

	/** @return list<string> One entry per array item or text line. */
	private static function lines( mixed $value ): array {
		if ( is_string( $value ) ) {
			$value = preg_split( '/[\r\n,]+/', $value );
		}
		$out = [];
		foreach ( is_array( $value ) ? $value : [] as $line ) {
			$line = is_string( $line ) ? trim( $line ) : '';
			if ( $line !== '' ) {
				$out[] = $line;
			}
		}

		return $out;
	}

	/** @return list<string> Route prefixes like "wp/v2/posts" or "contact-form-7/v1". */
	private static function routes( mixed $value, bool $skipAlwaysOn ): array {
		$out = [];
		foreach ( self::lines( $value ) as $route ) {
			$route = trim( $route, " /\t" );
			if ( $route !== '' && preg_match( '#^[a-z0-9_.\-]+(/[a-z0-9_.\-]+)*$#i', $route ) === 1 && ! ( $skipAlwaysOn && in_array( $route, self::ALWAYS_ON, true ) ) ) {
				$out[] = $route;
			}
		}

		return array_values( array_unique( $out ) );
	}

	public function register(): void {
		if ( ! function_exists( 'add_filter' ) || ! function_exists( 'add_action' ) ) {
			return;
		}
		add_filter( 'rest_pre_dispatch', [ $this, 'gate' ], 5, 3 );
		add_action( 'init', [ $this, 'boot' ], 1 );
	}

	/** Discovery, JSONP and XML-RPC switches; they only apply while the module is on. */
	public function boot(): void {
		if ( ! ( $this->enabled )() ) {
			return;
		}
		$settings = self::settings();
		if ( $settings['remove_discovery'] ) {
			remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
			remove_action( 'template_redirect', 'rest_output_link_header', 11 );
			remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
		}
		if ( $settings['disable_jsonp'] ) {
			add_filter( 'rest_jsonp_enabled', '__return_false' );
		}
		if ( $settings['disable_xmlrpc'] ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_filter( 'xmlrpc_methods', '__return_empty_array' );
			add_filter( 'wp_headers', [ self::class, 'dropPingback' ] );
			remove_action( 'wp_head', 'rsd_link' );
		}
	}

	/**
	 * @param array<string,string> $headers
	 * @return array<string,string>
	 */
	public static function dropPingback( array $headers ): array {
		unset( $headers['X-Pingback'] );

		return $headers;
	}

	/** Decides a request before it reaches its route. */
	public function gate( mixed $result, mixed $server, mixed $request ): mixed {
		if ( $result !== null || ! $request instanceof WP_REST_Request || ! ( $this->enabled )() ) {
			return $result;
		}
		$settings = self::settings();
		$user     = wp_get_current_user();
		$roles    = array_values( array_map( 'strval', (array) $user->roles ) );
		$decision = self::decide( $settings, $request->get_route(), is_user_logged_in(), current_user_can( 'edit_posts' ), $roles );
		if ( $decision === null && $settings['rate_limit'] > 0 && ! is_user_logged_in() && ! in_array( self::ip(), $settings['rate_exempt_ips'], true ) && ! $this->allowRate( $settings['rate_limit'] ) ) {
			$decision = [ 'wpessential_rest_rate_limited', 429 ];
		}
		if ( $decision === null ) {
			return $result;
		}
		if ( $settings['log_blocked'] ) {
			self::log( $request->get_route(), $request->get_method(), $decision[0], $decision[1] );
		}

		return new WP_Error( $decision[0], self::message( $decision[0] ), [ 'status' => $decision[1] ] );
	}

	/**
	 * Pure decision for one request: [error code, HTTP status], or null to let it through.
	 *
	 * @param array{logged_in_only:bool,allow_routes:list<string>,restricted_roles:list<string>,hide_users:bool,disabled_namespaces:list<string>,rate_limit:int,rate_exempt_ips:list<string>,remove_discovery:bool,disable_jsonp:bool,disable_xmlrpc:bool,log_blocked:bool} $settings
	 * @param list<string> $roles The current user's roles.
	 * @return array{0:string,1:int}|null
	 */
	public static function decide( array $settings, string $route, bool $loggedIn, bool $canEdit, array $roles = [] ): ?array {
		$route = '/' . ltrim( $route, '/' );
		if ( self::matches( $route, self::ALWAYS_ON ) || in_array( 'administrator', $roles, true ) ) {
			return null;
		}
		// A user whose every role is restricted is treated like a visitor, even if it can edit posts.
		$restricted = $loggedIn && $roles !== [] && $settings['restricted_roles'] !== [] && array_diff( $roles, $settings['restricted_roles'] ) === [];
		$visitor    = ! $loggedIn || $restricted;
		if ( $canEdit && ! $restricted ) {
			return null;
		}
		$allowed = self::matches( $route, $settings['allow_routes'] );
		if ( ( $settings['logged_in_only'] || $restricted ) && $visitor && $route !== '/' && ! $allowed ) {
			return $restricted ? [ 'wpessential_rest_role_restricted', 403 ] : [ 'wpessential_rest_login_required', 401 ];
		}
		if ( ! $allowed && self::matches( $route, $settings['disabled_namespaces'] ) ) {
			return [ 'rest_no_route', 404 ];
		}
		if ( $settings['hide_users'] && $visitor && ! $allowed && preg_match( '#^/wp/v2/users(/|$)#', $route ) === 1 ) {
			return [ 'rest_user_cannot_view', 401 ];
		}

		return null;
	}

	/** @param list<string> $prefixes */
	private static function matches( string $route, array $prefixes ): bool {
		foreach ( $prefixes as $prefix ) {
			if ( $route === '/' . $prefix || str_starts_with( $route, '/' . $prefix . '/' ) ) {
				return true;
			}
		}

		return false;
	}

	private static function message( string $code ): string {
		return match ( $code ) {
			'wpessential_rest_login_required'  => __( 'The REST API on this site is available to signed-in users only.', 'wpessential' ),
			'wpessential_rest_role_restricted' => __( 'Your account is not allowed to use the REST API on this site.', 'wpessential' ),
			'wpessential_rest_rate_limited'    => __( 'Too many requests. Try again in a minute.', 'wpessential' ),
			'rest_user_cannot_view'            => __( 'Sorry, you are not allowed to list users.', 'wpessential' ),
			default                            => __( 'No route was found matching the URL and request method.', 'wpessential' ),
		};
	}

	/** Keeps the latest blocked requests; the visitor IP is stored masked. */
	private static function log( string $route, string $method, string $code, int $status ): void {
		$log   = get_option( self::LOG_OPTION, [] );
		$log   = is_array( $log ) ? array_values( $log ) : [];
		$log[] = [
			'time'   => time(),
			'route'  => substr( $route, 0, 200 ),
			'method' => substr( $method, 0, 10 ),
			'code'   => $code,
			'status' => $status,
			'ip'     => self::maskIp( self::ip() ),
		];
		update_option( self::LOG_OPTION, array_slice( $log, -self::LOG_SIZE ), false );
	}

	/** @return list<array<string,mixed>> Newest first. */
	public static function blocked(): array {
		$log = function_exists( 'get_option' ) ? get_option( self::LOG_OPTION, [] ) : [];

		return array_reverse( is_array( $log ) ? array_values( $log ) : [] );
	}

	public static function maskIp( string $ip ): string {
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) !== false ) {
			return (string) preg_replace( '/\.\d+$/', '.0', $ip );
		}
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) !== false ) {
			return implode( ':', array_slice( explode( ':', $ip ), 0, 3 ) ) . '::';
		}

		return '';
	}

	private static function ip(): string {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) ) : '';
	}

	private function allowRate( int $perMinute ): bool {
		$key = 'wpe_rest_rl_' . md5( self::ip() . '|' . gmdate( 'YmdHi' ) );
		$hit = (int) get_transient( $key ) + 1;
		set_transient( $key, $hit, 120 );

		return $hit <= $perMinute;
	}
}
