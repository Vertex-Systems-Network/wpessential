<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;

/**
 * Account on wpessential.org (ported from hi-wpessential core `Account`).
 *
 * Server-to-server only: the Bearer token lives in a non-autoloaded option, is shared by the
 * site's administrators and is never sent to the browser.
 */
final class Account {

	public const OPTION         = 'wpessential_account';
	public const LICENSE_ACTIONS = [ 'auto_renew_on', 'auto_renew_off', 'cancel', 'deactivate_domain' ];

	public function __construct( private readonly LicenseService $license ) {}

	public function api(): string {
		return untrailingslashit( $this->license->config()['api'] );
	}

	/**
	 * @param array<string,scalar> $body
	 * @return array<string,mixed>|WP_Error
	 */
	private function request( string $method, string $route, array $body = [], bool $auth = true ): array|WP_Error {
		$args  = [
			'method'  => $method,
			'timeout' => 20,
			'headers' => [ 'Accept' => 'application/json' ],
		];
		$token = $this->token();
		if ( $auth && $token !== '' ) {
			$args['headers']['Authorization'] = 'Bearer ' . $token;
		}
		if ( $method !== 'GET' ) {
			$args['body'] = $body;
		}

		$response = wp_safe_remote_request( $this->api() . $route, $args );
		if ( $response instanceof WP_Error ) {
			/* translators: %s: error message. */
			return new WP_Error( 'wpe_http', sprintf( __( 'WPEssential could not be reached: %s', 'wpessential' ), $response->get_error_message() ) );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( $code === 401 && $auth ) {
			delete_option( self::OPTION );
			return new WP_Error( 'wpe_signed_out', __( 'Your WPEssential session ended. Sign in again.', 'wpessential' ) );
		}
		if ( ! is_array( $data ) ) {
			/* translators: %d: HTTP status code. */
			return new WP_Error( 'wpe_http', sprintf( __( 'WPEssential answered with an unexpected response (HTTP %d).', 'wpessential' ), $code ) );
		}
		if ( isset( $data['success'] ) && ! $data['success'] ) {
			return new WP_Error( 'wpe_' . sanitize_key( is_string( $data['status'] ?? null ) ? $data['status'] : 'error' ), is_string( $data['message'] ?? null ) ? $data['message'] : __( 'Something went wrong.', 'wpessential' ) );
		}
		if ( isset( $data['code'], $data['message'] ) && $code >= 400 ) {
			return new WP_Error( 'wpe_' . sanitize_key( (string) $data['code'] ), (string) $data['message'] );
		}

		return $data;
	}

	private function token(): string {
		$stored = function_exists( 'get_option' ) ? get_option( self::OPTION, [] ) : [];

		return is_array( $stored ) && is_string( $stored['token'] ?? null ) ? $stored['token'] : '';
	}

	/** @param array<string,mixed> $data */
	private function store( array $data ): void {
		update_option(
			self::OPTION,
			[
				'token' => is_string( $data['token'] ?? null ) ? $data['token'] : '',
				'user'  => is_array( $data['user'] ?? null ) ? $data['user'] : [],
			],
			false
		);
	}

	/**
	 * Browser-safe account view: licence keys are masked and the free plan is hidden.
	 *
	 * @param array<string,mixed> $data
	 * @return array{signed_in:true,user:array<string,mixed>,account:string,plans:list<array<string,mixed>>,licenses:list<array<string,mixed>>}
	 */
	public function shape( array $data ): array {
		$domain = preg_replace( '/^www\./', '', strtolower( LicenseService::domain() ) );
		$plans  = [];
		foreach ( (array) ( $data['plans'] ?? [] ) as $plan ) {
			if ( is_array( $plan ) && ( $plan['key'] ?? '' ) !== 'free' ) {
				$plans[] = $plan;
			}
		}
		$licenses = [];
		foreach ( (array) ( $data['licenses'] ?? [] ) as $license ) {
			if ( ! is_array( $license ) ) {
				continue;
			}
			$key             = is_string( $license['key'] ?? null ) ? $license['key'] : '';
			$license['here'] = in_array( $domain, (array) ( $license['domains'] ?? [] ), true );
			$license['mask'] = LicenseService::mask( $key, 8, 4 );
			unset( $license['key'] );
			$licenses[] = $license;
		}

		return [
			'signed_in' => true,
			'user'      => is_array( $data['user'] ?? null ) ? $data['user'] : [],
			'account'   => is_string( $data['account'] ?? null ) ? $data['account'] : '',
			'plans'     => $plans,
			'licenses'  => $licenses,
		];
	}

	public function signedIn(): bool {
		return $this->token() !== '';
	}

	/** @return array<string,mixed>|WP_Error */
	public function me(): array|WP_Error {
		if ( ! $this->signedIn() ) {
			return [ 'signed_in' => false ];
		}
		$data = $this->request( 'GET', '/account/me' );

		return $data instanceof WP_Error ? $data : $this->shape( $data );
	}

	/** @return array<string,mixed>|WP_Error */
	public function login( string $login, string $password ): array|WP_Error {
		$data = $this->request(
			'POST',
			'/account/login',
			[
				'login'    => $login,
				'password' => $password,
				'site'     => home_url(),
			],
			false
		);
		if ( $data instanceof WP_Error ) {
			return $data;
		}
		$this->store( $data );
		$this->connectSite();

		return $this->me();
	}

	/** @return array<string,mixed>|WP_Error */
	public function register( string $name, string $email, string $password ): array|WP_Error {
		$data = $this->request(
			'POST',
			'/account/register',
			[
				'name'     => $name,
				'email'    => $email,
				'password' => $password,
				'site'     => home_url(),
			],
			false
		);
		if ( $data instanceof WP_Error ) {
			return $data;
		}
		$this->store( $data );
		$this->connectSite();

		return $this->me();
	}

	/**
	 * Connects this site to the signed-in account without a key: keeps the subscription already on
	 * this domain, otherwise takes a free slot on the best active subscription (Premium first).
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	public function connectSite(): array|WP_Error {
		$data = $this->request( 'GET', '/account/me' );
		if ( $data instanceof WP_Error ) {
			return $data;
		}
		$licenses = array_values( array_filter( (array) ( $data['licenses'] ?? [] ), 'is_array' ) );
		$pick     = self::pickLicense( $licenses, LicenseService::domain() );
		if ( $pick === null ) {
			return new WP_Error( 'wpe_no_slot', __( 'Every site slot on your subscriptions is in use. Upgrade or free a slot to connect this site.', 'wpessential' ) );
		}
		$key = (string) $pick['key'];
		if ( $this->license->isActive() && $this->license->data()['key'] !== $key ) {
			$this->license->deactivate();
		}

		return $this->license->activate( $key );
	}

	/**
	 * @param list<array<string,mixed>> $licenses Owner-shaped licences from account/me (with keys).
	 * @return array<string,mixed>|null
	 */
	public static function pickLicense( array $licenses, string $domain ): ?array {
		$domain = preg_replace( '/^www\./', '', strtolower( $domain ) );
		$usable = [];
		foreach ( $licenses as $license ) {
			if ( ( $license['status'] ?? '' ) !== 'active' || ! is_string( $license['key'] ?? null ) || $license['key'] === '' ) {
				continue;
			}
			$domains = array_map( static fn ( mixed $d ): string => preg_replace( '/^www\./', '', strtolower( (string) $d ) ) ?? '', (array) ( $license['domains'] ?? [] ) );
			if ( in_array( $domain, $domains, true ) ) {
				return $license;
			}
			$limit = (int) ( $license['sites_limit'] ?? 1 );
			$used  = (int) ( $license['sites_used'] ?? count( $domains ) );
			if ( $limit === 0 || $used < $limit ) {
				$usable[] = $license;
			}
		}
		usort(
			$usable,
			static function ( array $a, array $b ): int {
				$free = static fn ( array $l ): int => (int) ( $l['sites_limit'] ?? 1 ) === 0 ? PHP_INT_MAX : (int) ( $l['sites_limit'] ?? 1 ) - (int) ( $l['sites_used'] ?? 0 );

				return [ empty( $b['pro'] ) ? 0 : 1, $free( $b ) ] <=> [ empty( $a['pro'] ) ? 0 : 1, $free( $a ) ];
			}
		);

		return $usable[0] ?? null;
	}

	/** @return array{signed_in:false} */
	public function logout(): array {
		if ( $this->signedIn() ) {
			$this->request( 'POST', '/account/logout' );
		}
		delete_option( self::OPTION );

		return [ 'signed_in' => false ];
	}

	/** @return array<string,mixed>|WP_Error */
	public function useLicense( int $licenseId ): array|WP_Error {
		$data = $this->request( 'GET', '/account/me' );
		if ( $data instanceof WP_Error ) {
			return $data;
		}
		foreach ( (array) ( $data['licenses'] ?? [] ) as $license ) {
			if ( is_array( $license ) && (int) ( $license['id'] ?? 0 ) === $licenseId && is_string( $license['key'] ?? null ) ) {
				if ( $this->license->isActive() && $this->license->data()['key'] !== $license['key'] ) {
					$this->license->deactivate();
				}

				return $this->license->activate( $license['key'] );
			}
		}

		return new WP_Error( 'wpe_license', __( 'That licence is not in your account.', 'wpessential' ) );
	}

	/** Checkout URL on wpessential.org for buy / renew / upgrade. */
	public function checkout( string $plan, int $licenseId = 0, string $mode = 'buy' ): string|WP_Error {
		$data = $this->request(
			'POST',
			'/account/checkout',
			[
				'plan'       => $plan,
				'license_id' => $licenseId,
				'mode'       => in_array( $mode, [ 'buy', 'renew', 'upgrade' ], true ) ? $mode : 'buy',
			]
		);
		if ( $data instanceof WP_Error ) {
			return $data;
		}

		$url  = is_string( $data['url'] ?? null ) ? esc_url_raw( $data['url'] ) : '';
		$host = wp_parse_url( $url, PHP_URL_HOST );
		$api  = wp_parse_url( $this->api(), PHP_URL_HOST );

		// Only follow checkout links that stay on the account server's own host.
		return $url !== '' && is_string( $host ) && $host === $api
			? $url
			: new WP_Error( 'wpe_checkout', __( 'The checkout link could not be verified.', 'wpessential' ) );
	}

	/** @return array<string,mixed>|WP_Error */
	public function licenseAction( int $licenseId, string $action, string $domain = '' ): array|WP_Error {
		if ( ! in_array( $action, self::LICENSE_ACTIONS, true ) ) {
			return new WP_Error( 'wpe_action', __( 'Unknown action.', 'wpessential' ) );
		}
		$data = $this->request(
			'POST',
			'/account/license',
			[
				'license_id' => $licenseId,
				'action'     => $action,
				'domain'     => $domain,
			]
		);

		return $data instanceof WP_Error ? $data : $this->me();
	}
}
