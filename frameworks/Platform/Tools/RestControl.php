<?php

declare(strict_types=1);

namespace WPEssential\Platform\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;
use WP_REST_Request;

/**
 * REST API exposure for visitors: require login, hide the user list, switch off namespaces and
 * rate-limit anonymous requests. Logged-in editors keep full access so the block editor works.
 */
final class RestControl {

	public const OPTION = 'wpessential_rest_control';

	/** Namespaces that always stay reachable (the AI Connect server and its OAuth endpoints, oEmbed). */
	public const ALWAYS_ON = [ 'wpessential/v1', 'oembed/1.0' ];

	public const DEFAULTS = [
		'logged_in_only'      => false,
		'hide_users'          => true,
		'disabled_namespaces' => [],
		'rate_limit'          => 0,
	];

	public function __construct( private readonly \Closure $enabled ) {}

	/** @return array{logged_in_only:bool,hide_users:bool,disabled_namespaces:list<string>,rate_limit:int} */
	public static function settings(): array {
		$saved = function_exists( 'get_option' ) ? get_option( self::OPTION, [] ) : [];

		return self::normalize( is_array( $saved ) ? $saved : [] );
	}

	/**
	 * @param array<string,mixed> $input
	 * @return array{logged_in_only:bool,hide_users:bool,disabled_namespaces:list<string>,rate_limit:int}
	 */
	public static function normalize( array $input ): array {
		$namespaces = [];
		foreach ( is_array( $input['disabled_namespaces'] ?? null ) ? $input['disabled_namespaces'] : [] as $namespace ) {
			$namespace = is_string( $namespace ) ? trim( $namespace, " /\t" ) : '';
			if ( $namespace !== '' && preg_match( '#^[a-z0-9_.\-]+(/[a-z0-9_.\-]+)*$#i', $namespace ) === 1 && ! in_array( $namespace, self::ALWAYS_ON, true ) ) {
				$namespaces[] = $namespace;
			}
		}

		return [
			'logged_in_only'      => (bool) ( $input['logged_in_only'] ?? self::DEFAULTS['logged_in_only'] ),
			'hide_users'          => (bool) ( $input['hide_users'] ?? self::DEFAULTS['hide_users'] ),
			'disabled_namespaces' => array_values( array_unique( $namespaces ) ),
			'rate_limit'          => max( 0, min( 10000, (int) ( $input['rate_limit'] ?? 0 ) ) ),
		];
	}

	public function register(): void {
		if ( ! function_exists( 'add_filter' ) ) {
			return;
		}
		add_filter( 'rest_pre_dispatch', [ $this, 'gate' ], 5, 3 );
	}

	/** Decides a request before it reaches its route. */
	public function gate( mixed $result, mixed $server, mixed $request ): mixed {
		if ( $result !== null || ! $request instanceof WP_REST_Request || ! ( $this->enabled )() ) {
			return $result;
		}
		$decision = self::decide( self::settings(), $request->get_route(), is_user_logged_in(), current_user_can( 'edit_posts' ) );
		if ( $decision !== null ) {
			return new WP_Error( $decision[0], self::message( $decision[0] ), [ 'status' => $decision[1] ] );
		}
		$settings = self::settings();
		if ( $settings['rate_limit'] > 0 && ! is_user_logged_in() && ! $this->allowRate( $settings['rate_limit'] ) ) {
			return new WP_Error( 'wpessential_rest_rate_limited', __( 'Too many requests. Try again in a minute.', 'wpessential' ), [ 'status' => 429 ] );
		}

		return $result;
	}

	/**
	 * Pure decision for one request: [error code, HTTP status], or null to let it through.
	 *
	 * @param array{logged_in_only:bool,hide_users:bool,disabled_namespaces:list<string>,rate_limit:int} $settings
	 * @return array{0:string,1:int}|null
	 */
	public static function decide( array $settings, string $route, bool $loggedIn, bool $canEdit ): ?array {
		$route = '/' . ltrim( $route, '/' );
		foreach ( self::ALWAYS_ON as $namespace ) {
			if ( $route === '/' . $namespace || str_starts_with( $route, '/' . $namespace . '/' ) ) {
				return null;
			}
		}
		if ( $canEdit ) {
			return null;
		}
		if ( $settings['logged_in_only'] && ! $loggedIn && $route !== '/' ) {
			return [ 'wpessential_rest_login_required', 401 ];
		}
		foreach ( $settings['disabled_namespaces'] as $namespace ) {
			if ( $route === '/' . $namespace || str_starts_with( $route, '/' . $namespace . '/' ) ) {
				return [ 'rest_no_route', 404 ];
			}
		}
		if ( $settings['hide_users'] && ! $loggedIn && preg_match( '#^/wp/v2/users(/|$)#', $route ) === 1 ) {
			return [ 'rest_user_cannot_view', 401 ];
		}

		return null;
	}

	private static function message( string $code ): string {
		return match ( $code ) {
			'wpessential_rest_login_required' => __( 'The REST API on this site is available to signed-in users only.', 'wpessential' ),
			'rest_user_cannot_view'           => __( 'Sorry, you are not allowed to list users.', 'wpessential' ),
			default                           => __( 'No route was found matching the URL and request method.', 'wpessential' ),
		};
	}

	private function allowRate( int $perMinute ): bool {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key = 'wpe_rest_rl_' . md5( $ip . '|' . gmdate( 'YmdHi' ) );
		$hit = (int) get_transient( $key ) + 1;
		set_transient( $key, $hit, 120 );

		return $hit <= $perMinute;
	}
}
