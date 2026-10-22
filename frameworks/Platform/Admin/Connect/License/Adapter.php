<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect\License;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;

/**
 * Talks to one licence provider. Errors with code `wpe_http` mean "server unreachable";
 * callers keep the last known state in that case.
 */
abstract class Adapter {

	/** @param array<string,string> $config */
	public function __construct( protected readonly array $config ) {}

	/** @return array<string,mixed>|WP_Error */
	abstract public function activate( string $key, string $domain ): array|WP_Error;

	/** @return array<string,mixed>|WP_Error */
	abstract public function deactivate( string $key, string $domain ): array|WP_Error;

	/** @return array<string,mixed>|WP_Error */
	abstract public function check( string $key, string $domain ): array|WP_Error;

	/** Signed download URL for a licensed package (Pro demo or plugin). */
	public function packageUrl( string $key, string $domain, string $item, string $context ): string|WP_Error {
		$body = $this->request(
			'POST',
			trailingslashit( $this->config['endpoint'] ) . 'package',
			[
				'license' => $key,
				'domain'  => $domain,
				'item'    => $item,
				'context' => $context,
			]
		);
		if ( $body instanceof WP_Error ) {
			return $body;
		}

		return empty( $body['url'] ) || ! is_string( $body['url'] )
			? new WP_Error( 'wpe_package', __( 'The licence server did not return a download link.', 'wpessential' ) )
			: esc_url_raw( $body['url'] );
	}

	/**
	 * @param array<string,string> $args
	 * @return array<string,mixed>|WP_Error
	 */
	protected function request( string $method, string $url, array $args ): array|WP_Error {
		$params = [
			'timeout' => 15,
			'headers' => [ 'Accept' => 'application/json' ],
		];
		$response = $method === 'GET'
			? wp_safe_remote_get( add_query_arg( $args, $url ), $params )
			: wp_safe_remote_post( $url, $params + [ 'body' => $args ] );

		if ( $response instanceof WP_Error ) {
			/* translators: %s: error message. */
			return new WP_Error( 'wpe_http', sprintf( __( 'The licence server could not be reached: %s', 'wpessential' ), $response->get_error_message() ) );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( $code >= 500 || ! is_array( $body ) ) {
			/* translators: %d: HTTP status code. */
			return new WP_Error( 'wpe_http', sprintf( __( 'The licence server answered with an unexpected response (HTTP %d). Try again in a few minutes.', 'wpessential' ), $code ) );
		}

		return $body;
	}

	/** @return array{status:string,expires:string,plan:string,message:string} */
	protected function result( string $status, mixed $expires = '', mixed $plan = '', mixed $message = '' ): array {
		$expires = is_scalar( $expires ) ? (string) $expires : '';
		if ( $expires !== '' && $expires !== 'lifetime' ) {
			$time    = is_numeric( $expires ) ? (int) $expires : strtotime( $expires );
			$expires = $time !== false ? gmdate( 'Y-m-d', $time ) : '';
		}

		return [
			'status'  => $status,
			'expires' => $expires,
			'plan'    => is_scalar( $plan ) ? (string) $plan : '',
			'message' => is_scalar( $message ) ? (string) $message : '',
		];
	}
}
