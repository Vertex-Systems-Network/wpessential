<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;
use WPEssential\Platform\Admin\Connect\License\Adapter;
use WPEssential\Platform\Admin\Connect\License\Custom;
use WPEssential\Platform\Admin\Connect\License\Edd;
use WPEssential\Platform\Admin\Connect\License\Woo;

/**
 * Licence key + domain activation (ported from hi-wpessential core `License`).
 *
 * The key is stored in a non-autoloaded option. It is never sent to the browser in full and is
 * not encrypted at rest; the UI states that limitation (docs/ARCHITECTURE.md §21).
 */
final class LicenseService {

	public const OPTION = 'wpessential_license';
	public const CRON   = 'wpessential_license_check';

	/** @return array<string,string> */
	public function config(): array {
		$filtered = function_exists( 'apply_filters' ) ? apply_filters( 'wpessential_license_config', [] ) : [];
		$config   = array_merge(
			[
				'provider'   => 'custom',
				'api'        => 'https://wpessential.org/wp-json/wpessential-server/v1',
				'endpoint'   => 'https://wpessential.org/wp-json/wpessential-server/v1/license',
				'product_id' => 'wpessential',
			],
			is_array( $filtered ) ? $filtered : []
		);

		return array_map( static fn ( mixed $v ): string => is_scalar( $v ) ? (string) $v : '', $config );
	}

	public function adapter(): Adapter {
		$config = $this->config();
		$map    = function_exists( 'apply_filters' )
			? apply_filters(
				'wpessential_license_adapters',
				[
					'custom' => Custom::class,
					'edd'    => Edd::class,
					'woo'    => Woo::class,
				]
			)
			: [];
		$class  = is_array( $map ) && isset( $map[ $config['provider'] ] ) && is_string( $map[ $config['provider'] ] ) && is_subclass_of( $map[ $config['provider'] ], Adapter::class )
			? $map[ $config['provider'] ]
			: Custom::class;

		return new $class( $config );
	}

	/** @return array{key:string,status:string,expires:string,plan:string,checked:int,message:string,pro:bool,white_label:bool,sites_used:int,sites_limit:int} */
	public function data(): array {
		$stored = function_exists( 'get_option' ) ? get_option( self::OPTION, [] ) : [];
		$stored = is_array( $stored ) ? $stored : [];

		return [
			'key'         => is_string( $stored['key'] ?? null ) ? $stored['key'] : '',
			'status'      => is_string( $stored['status'] ?? null ) ? $stored['status'] : 'inactive',
			'expires'     => is_string( $stored['expires'] ?? null ) ? $stored['expires'] : '',
			'plan'        => is_string( $stored['plan'] ?? null ) ? $stored['plan'] : '',
			'checked'     => (int) ( $stored['checked'] ?? 0 ),
			'message'     => is_string( $stored['message'] ?? null ) ? $stored['message'] : '',
			'pro'         => ! empty( $stored['pro'] ),
			'white_label' => ! empty( $stored['white_label'] ),
			'sites_used'  => (int) ( $stored['sites_used'] ?? 0 ),
			'sites_limit' => (int) ( $stored['sites_limit'] ?? 0 ),
		];
	}

	public static function mask( string $key, int $head = 4, int $tail = 4 ): string {
		if ( $key === '' ) {
			return '';
		}

		return strlen( $key ) <= $head + $tail
			? str_repeat( '•', 8 )
			: substr( $key, 0, $head ) . str_repeat( '•', 8 ) . substr( $key, -$tail );
	}

	/** @return array<string,mixed> Licence data safe for the browser (masked key). */
	public function publicData(): array {
		$data        = $this->data();
		$data['key'] = self::mask( $data['key'] );
		$labels      = [
			'custom' => 'WPEssential',
			'edd'    => 'Easy Digital Downloads',
			'woo'    => 'WooCommerce API Manager',
		];
		$provider    = $this->config()['provider'];

		return $data + [
			'domain'   => self::domain(),
			'provider' => $labels[ $provider ] ?? $provider,
		];
	}

	public function isActive(): bool {
		return $this->data()['status'] === 'active';
	}

	/** An active licence that unlocks Premium. Licences from EDD/Woo stores count as Premium. */
	public function hasPro(): bool {
		return $this->isActive() && ( $this->data()['pro'] || $this->config()['provider'] !== 'custom' );
	}

	public static function domain(): string {
		$host = function_exists( 'home_url' ) ? wp_parse_url( home_url(), PHP_URL_HOST ) : '';

		return is_string( $host ) ? $host : '';
	}

	/** @return array<string,mixed>|WP_Error */
	public function activate( string $key ): array|WP_Error {
		$key = trim( sanitize_text_field( $key ) );
		if ( strlen( $key ) < 8 ) {
			return new WP_Error( 'wpe_key', __( 'That licence key looks too short. Copy it again from your purchase email.', 'wpessential' ) );
		}

		$result = $this->adapter()->activate( $key, self::domain() );
		if ( $result instanceof WP_Error ) {
			return $result;
		}

		update_option(
			self::OPTION,
			array_merge(
				$result,
				[
					'key'     => $key,
					'checked' => time(),
				]
			),
			false
		);

		return $this->publicData();
	}

	/** @return array<string,mixed>|WP_Error */
	public function deactivate(): array|WP_Error {
		$data = $this->data();
		if ( $data['key'] !== '' ) {
			$result = $this->adapter()->deactivate( $data['key'], self::domain() );
			if ( $result instanceof WP_Error && $result->get_error_code() !== 'wpe_http' ) {
				return $result;
			}
		}
		delete_option( self::OPTION );

		return $this->publicData();
	}

	/** Daily re-check. Keeps the last known state while the server is unreachable. */
	public function refresh(): void {
		$data = $this->data();
		if ( $data['key'] === '' ) {
			return;
		}

		$result = $this->adapter()->check( $data['key'], self::domain() );
		if ( $result instanceof WP_Error ) {
			if ( $result->get_error_code() !== 'wpe_http' ) {
				$data['status']  = 'invalid';
				$data['message'] = $result->get_error_message();
			}
		} else {
			$data = array_merge( $data, $result );
		}
		$data['checked'] = time();
		update_option( self::OPTION, $data, false );
	}

	public function packageUrl( string $item, string $context ): string|WP_Error {
		if ( ! $this->isActive() ) {
			return new WP_Error( 'wpe_license', __( 'This is a Premium item. Activate your licence on the License screen to unlock it.', 'wpessential' ) );
		}

		return $this->adapter()->packageUrl( $this->data()['key'], self::domain(), $item, $context );
	}

	public function schedule(): void {
		if ( ! function_exists( 'add_action' ) ) {
			return;
		}

		add_action( self::CRON, [ $this, 'refresh' ] );
		add_action(
			'admin_init',
			static function (): void {
				if ( ! wp_next_scheduled( self::CRON ) ) {
					wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', self::CRON );
				}
			}
		);
	}
}
