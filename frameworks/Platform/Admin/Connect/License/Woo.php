<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect\License;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;

/**
 * WooCommerce API Manager.
 */
final class Woo extends Adapter {

	public const INSTANCE_OPTION = 'wpessential_license_instance';

	private function instanceId(): string {
		$id = get_option( self::INSTANCE_OPTION );
		if ( ! is_string( $id ) || $id === '' ) {
			$id = wp_generate_password( 20, false );
			update_option( self::INSTANCE_OPTION, $id, false );
		}

		return $id;
	}

	/**
	 * @param array<string,string> $extra
	 * @return array<string,mixed>|WP_Error
	 */
	private function call( string $action, string $key, string $domain, array $extra = [] ): array|WP_Error {
		$body = $this->request(
			'GET',
			add_query_arg( 'wc-api', 'wc-am-api', trailingslashit( $this->config['endpoint'] ) ),
			[
				'wc_am_action' => $action,
				'api_key'      => $key,
				'product_id'   => $this->config['product_id'],
				'instance'     => $this->instanceId(),
				'object'       => $domain,
			] + $extra
		);
		if ( $body instanceof WP_Error ) {
			return $body;
		}
		if ( empty( $body['success'] ) ) {
			return new WP_Error( 'wpe_license_invalid', is_string( $body['error'] ?? null ) ? $body['error'] : __( 'This licence key is not valid.', 'wpessential' ) );
		}

		return $body;
	}

	public function activate( string $key, string $domain ): array|WP_Error {
		$body = $this->call( 'activate', $key, $domain );

		return $body instanceof WP_Error ? $body : $this->result( 'active', '', '', $body['message'] ?? '' );
	}

	public function deactivate( string $key, string $domain ): array|WP_Error {
		$body = $this->call( 'deactivate', $key, $domain );

		return $body instanceof WP_Error ? $body : $this->result( 'inactive' );
	}

	public function check( string $key, string $domain ): array|WP_Error {
		$body = $this->call( 'status', $key, $domain );
		if ( $body instanceof WP_Error ) {
			return $body;
		}

		return $this->result( ( $body['status_check'] ?? '' ) === 'active' ? 'active' : 'expired' );
	}

	public function packageUrl( string $key, string $domain, string $item, string $context ): string|WP_Error {
		$body = $this->call( 'update', $key, $domain, [ 'plugin_name' => $item ] );
		if ( $body instanceof WP_Error ) {
			return $body;
		}

		$url = $body['data']['package']['package'] ?? '';

		return is_string( $url ) && $url !== ''
			? esc_url_raw( $url )
			: new WP_Error( 'wpe_package', __( 'The store did not return a download link for this licence.', 'wpessential' ) );
	}
}
