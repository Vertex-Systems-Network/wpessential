<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect\License;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;

/**
 * The wpessential.org licence server (WPEssential Server plugin).
 */
final class Custom extends Adapter {

	/** @return array<string,mixed>|WP_Error */
	private function call( string $action, string $key, string $domain ): array|WP_Error {
		$body = $this->request(
			'POST',
			trailingslashit( $this->config['endpoint'] ) . $action,
			[
				'license' => $key,
				'domain'  => $domain,
				'product' => $this->config['product_id'],
			]
		);
		if ( $body instanceof WP_Error ) {
			return $body;
		}
		if ( empty( $body['success'] ) ) {
			$status = is_string( $body['status'] ?? null ) ? sanitize_key( $body['status'] ) : 'invalid';
			return new WP_Error( 'wpe_license_' . $status, is_string( $body['message'] ?? null ) ? $body['message'] : __( 'This licence key is not valid.', 'wpessential' ) );
		}

		return $this->result( is_string( $body['status'] ?? null ) ? $body['status'] : 'active', $body['expires'] ?? '', $body['plan'] ?? '', $body['message'] ?? '' ) + [
			'pro'         => ! empty( $body['pro'] ),
			'white_label' => ! empty( $body['white_label'] ),
			'sites_used'  => (int) ( $body['sites_used'] ?? 0 ),
			'sites_limit' => (int) ( $body['sites_limit'] ?? 0 ),
		];
	}

	public function activate( string $key, string $domain ): array|WP_Error {
		return $this->call( 'activate', $key, $domain );
	}

	public function deactivate( string $key, string $domain ): array|WP_Error {
		return $this->call( 'deactivate', $key, $domain );
	}

	public function check( string $key, string $domain ): array|WP_Error {
		return $this->call( 'check', $key, $domain );
	}
}
