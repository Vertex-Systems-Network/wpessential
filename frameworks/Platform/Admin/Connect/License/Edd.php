<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect\License;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;

/**
 * Easy Digital Downloads Software Licensing.
 */
final class Edd extends Adapter {

	private function message( string $code ): string {
		$messages = [
			'expired'             => __( 'This licence has expired. Renew it to keep receiving demos and updates.', 'wpessential' ),
			'disabled'            => __( 'This licence has been disabled.', 'wpessential' ),
			'revoked'             => __( 'This licence has been revoked.', 'wpessential' ),
			'missing'             => __( 'This licence key does not exist.', 'wpessential' ),
			'invalid'             => __( 'This licence key is not valid.', 'wpessential' ),
			'site_inactive'       => __( 'This licence is not active for this domain.', 'wpessential' ),
			'item_name_mismatch'  => __( 'This key belongs to a different product.', 'wpessential' ),
			'no_activations_left' => __( 'This licence has reached its activation limit. Deactivate it on another site first.', 'wpessential' ),
		];

		return $messages[ $code ] ?? $messages['invalid'];
	}

	/** @return array<string,mixed>|WP_Error */
	private function call( string $action, string $key, string $domain ): array|WP_Error {
		$body = $this->request(
			'POST',
			$this->config['endpoint'],
			[
				'edd_action' => $action,
				'license'    => $key,
				'item_id'    => $this->config['product_id'],
				'url'        => $domain,
			]
		);
		if ( $body instanceof WP_Error ) {
			return $body;
		}
		if ( $action === 'deactivate_license' ) {
			return $this->result( 'inactive' );
		}

		$status = is_string( $body['license'] ?? null ) ? $body['license'] : 'invalid';
		if ( empty( $body['success'] ) && $status !== 'valid' ) {
			$code = is_string( $body['error'] ?? null ) ? sanitize_key( $body['error'] ) : sanitize_key( $status );
			return new WP_Error( 'wpe_license_' . $code, $this->message( $code ) );
		}

		return $this->result( $status === 'valid' ? 'active' : $status, $body['expires'] ?? '', $body['price_id'] ?? '' );
	}

	public function activate( string $key, string $domain ): array|WP_Error {
		return $this->call( 'activate_license', $key, $domain );
	}

	public function deactivate( string $key, string $domain ): array|WP_Error {
		return $this->call( 'deactivate_license', $key, $domain );
	}

	public function check( string $key, string $domain ): array|WP_Error {
		return $this->call( 'check_license', $key, $domain );
	}

	public function packageUrl( string $key, string $domain, string $item, string $context ): string|WP_Error {
		$body = $this->request(
			'POST',
			$this->config['endpoint'],
			[
				'edd_action' => 'get_version',
				'license'    => $key,
				'item_id'    => $this->config['product_id'],
				'url'        => $domain,
			]
		);
		if ( $body instanceof WP_Error ) {
			return $body;
		}

		$url = $body['download_link'] ?? ( $body['package'] ?? '' );
		$url = apply_filters( 'wpessential_edd_package_url', $url, $item, $context, $body );

		return is_string( $url ) && $url !== ''
			? esc_url_raw( $url )
			: new WP_Error( 'wpe_package', __( 'The store did not return a download link for this licence.', 'wpessential' ) );
	}
}
