<?php
/**
 * Lemon Squeezy: hosted checkout (cards, PayPal) for international customers, in USD.
 *
 * Checkout: Api::checkout() asks for a hosted checkout URL with the buyer, plan, license and
 * mode in custom data. Webhooks (signed with the store's signing secret) then change licenses
 * through Fulfilment, like a paid WooCommerce order:
 * - order_created (paid)              → create / renew / upgrade
 * - subscription_created              → remember the subscription for that license
 * - subscription_payment_success      → renewal payments extend the license
 * - subscription_cancelled / resumed  → auto-renew off / on
 * - subscription_expired              → license expired (data is never deleted)
 * - order_refunded                    → licence revoked (or back to its trial)
 *
 * Every webhook is handled once (by event and object id), and nothing runs while the switch in
 * Settings is off.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Lemon Squeezy provider.
 */
class LemonSqueezy {

	const API     = 'https://api.lemonsqueezy.com/v1';
	const SECRETS = 'wpes_ls_secrets';

	/**
	 * Saved secrets (kept out of the autoloaded settings).
	 *
	 * @return array{api_key:string,secret:string}
	 */
	public static function secrets() {
		$s = get_option( self::SECRETS, array() );
		return array(
			'api_key' => is_array( $s ) && is_string( $s['api_key'] ?? null ) ? $s['api_key'] : '',
			'secret'  => is_array( $s ) && is_string( $s['secret'] ?? null ) ? $s['secret'] : '',
		);
	}

	/**
	 * Switched on and fully configured.
	 *
	 * @return bool
	 */
	public static function enabled() {
		$s = self::secrets();
		return (bool) Plugin::settings( 'ls_enabled' ) && '' !== (string) Plugin::settings( 'ls_store_id' ) && '' !== $s['api_key'] && '' !== $s['secret'];
	}

	/**
	 * Whether a license is billed by Lemon Squeezy (its renewals are not WooCommerce orders).
	 *
	 * @param array $license License.
	 * @return bool
	 */
	public static function manages( $license ) {
		return is_array( $license ) && '' !== (string) get_option( 'wpes_ls_sub_' . (int) $license['id'], '' );
	}

	// ---------------------------------------------------------------- pure helpers (unit-tested)

	/**
	 * Valid webhook signature: hex HMAC-SHA256 of the raw body with the signing secret.
	 *
	 * @param string $body      Raw body.
	 * @param string $signature X-Signature header.
	 * @param string $secret    Signing secret.
	 * @return bool
	 */
	public static function valid_signature( $body, $signature, $secret ) {
		return '' !== (string) $secret && '' !== (string) $signature
			&& hash_equals( hash_hmac( 'sha256', (string) $body, (string) $secret ), strtolower( trim( (string) $signature ) ) );
	}

	/**
	 * Purchase intent from checkout custom data.
	 *
	 * @param array $custom meta.custom_data.
	 * @return array{user:int,plan:string,renew:int,upgrade:int}|null
	 */
	public static function intent( $custom ) {
		if ( ! is_array( $custom ) ) {
			return null;
		}
		$user    = (int) ( $custom['wpes_user'] ?? 0 );
		$plan    = preg_replace( '/[^a-z0-9_]/', '', strtolower( (string) ( $custom['wpes_plan'] ?? '' ) ) );
		$license = (int) ( $custom['wpes_license'] ?? 0 );
		$mode    = (string) ( $custom['wpes_mode'] ?? 'buy' );
		if ( $user <= 0 || '' === $plan ) {
			return null;
		}
		return array(
			'user'    => $user,
			'plan'    => $plan,
			'renew'   => 'renew' === $mode ? $license : 0,
			'upgrade' => 'upgrade' === $mode ? $license : 0,
		);
	}

	/**
	 * Key that makes a webhook delivery idempotent.
	 *
	 * @param array $payload Decoded body.
	 * @return string
	 */
	public static function event_key( $payload ) {
		$event = (string) ( $payload['meta']['event_name'] ?? '' );
		$id    = (string) ( $payload['data']['id'] ?? '' );
		$at    = (string) ( $payload['data']['attributes']['updated_at'] ?? '' );
		return md5( $event . '|' . $id . '|' . $at );
	}

	// ---------------------------------------------------------------- checkout

	/**
	 * Hosted checkout URL.
	 *
	 * @param int    $user_id    Buyer.
	 * @param array  $plan       Plan from Plans::from_product().
	 * @param int    $license_id License to renew/upgrade (0 for a new one).
	 * @param string $mode       buy|renew|upgrade.
	 * @return string|\WP_Error
	 */
	public static function checkout_url( $user_id, array $plan, $license_id, $mode ) {
		if ( ! self::enabled() ) {
			return new \WP_Error( 'ls_off', __( 'Card and PayPal payments are not available right now.', 'wpessential-server' ) );
		}
		$variant = (string) get_post_meta( (int) $plan['product_id'], '_wpes_ls_variant', true );
		if ( '' === $variant ) {
			return new \WP_Error( 'ls_plan', __( 'This plan cannot be paid by card or PayPal yet.', 'wpessential-server' ) );
		}
		$user     = get_userdata( (int) $user_id );
		$body     = array(
			'data' => array(
				'type'          => 'checkouts',
				'attributes'    => array(
					'checkout_data'   => array(
						'email'  => $user ? $user->user_email : '',
						'custom' => array(
							'wpes_user'    => (string) (int) $user_id,
							'wpes_plan'    => (string) $plan['key'],
							'wpes_license' => (string) (int) $license_id,
							'wpes_mode'    => in_array( $mode, array( 'renew', 'upgrade' ), true ) ? $mode : 'buy',
						),
					),
					'product_options' => array(
						'redirect_url' => wc_get_account_endpoint_url( MyAccount::ENDPOINT ),
					),
					'test_mode'       => (bool) Plugin::settings( 'ls_test' ),
				),
				'relationships' => array(
					'store'   => array(
						'data' => array(
							'type' => 'stores',
							'id'   => (string) Plugin::settings( 'ls_store_id' ),
						),
					),
					'variant' => array(
						'data' => array(
							'type' => 'variants',
							'id'   => $variant,
						),
					),
				),
			),
		);
		$response = wp_remote_post(
			self::API . '/checkouts',
			array(
				'timeout' => 20,
				'headers' => array(
					'Accept'        => 'application/vnd.api+json',
					'Content-Type'  => 'application/vnd.api+json',
					'Authorization' => 'Bearer ' . self::secrets()['api_key'],
				),
				'body'    => wp_json_encode( $body ),
			)
		);
		$data     = is_wp_error( $response ) ? null : json_decode( (string) wp_remote_retrieve_body( $response ), true );
		$url      = is_array( $data ) ? (string) ( $data['data']['attributes']['url'] ?? '' ) : '';
		if ( '' === $url ) {
			Events::error( 'ls.checkout_failed', 'Lemon Squeezy checkout could not be created.', array( 'user_id' => (int) $user_id ) );
			return new \WP_Error( 'ls_checkout', __( 'The card and PayPal checkout could not be opened. Try again in a minute.', 'wpessential-server' ) );
		}
		return esc_url_raw( $url );
	}

	// ---------------------------------------------------------------- webhooks

	/**
	 * Webhook endpoint (POST /lemonsqueezy).
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public static function webhook( $request ) {
		$body = (string) $request->get_body();
		if ( ! Plugin::settings( 'ls_enabled' ) || ! self::valid_signature( $body, (string) $request->get_header( 'x-signature' ), self::secrets()['secret'] ) ) {
			return new \WP_REST_Response( array( 'success' => false ), 401 );
		}
		$payload = json_decode( $body, true );
		if ( ! is_array( $payload ) ) {
			return new \WP_REST_Response( array( 'success' => false ), 400 );
		}
		$seen = 'wpes_ls_seen_' . self::event_key( $payload );
		if ( get_option( $seen ) ) {
			return new \WP_REST_Response( array( 'success' => true, 'duplicate' => true ), 200 ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound -- Short response.
		}
		$event = (string) ( $payload['meta']['event_name'] ?? '' );
		$data  = is_array( $payload['data'] ?? null ) ? $payload['data'] : array();
		$attrs = is_array( $data['attributes'] ?? null ) ? $data['attributes'] : array();
		$id    = (string) ( $data['id'] ?? '' );
		Events::begin( 'lemonsqueezy' );
		try {
			switch ( $event ) {
				case 'order_created':
					self::order_created( $id, $attrs, $payload['meta']['custom_data'] ?? array() );
					break;
				case 'subscription_created':
					self::link_subscription( $id, (string) ( $attrs['order_id'] ?? '' ) );
					self::remember_portal( $id, $attrs );
					break;
				case 'subscription_updated':
					self::remember_portal( $id, $attrs );
					break;
				case 'subscription_payment_success':
					self::renewal( (string) ( $attrs['subscription_id'] ?? '' ), (string) ( $attrs['billing_reason'] ?? '' ), $id );
					break;
				case 'subscription_cancelled':
				case 'subscription_resumed':
				case 'subscription_expired':
					self::subscription_state( $id, $event );
					break;
				case 'order_refunded':
					self::refunded( $id );
					break;
			}
		} catch ( \Throwable $e ) {
			Events::error( 'ls.webhook_exception', $e->getMessage(), array( 'event' => $event ) );
			return new \WP_REST_Response( array( 'success' => false ), 500 );
		}
		update_option( $seen, time(), false );
		return new \WP_REST_Response( array( 'success' => true ), 200 );
	}

	/**
	 * Paid order: create, renew or upgrade the license.
	 *
	 * @param string $order_id Lemon Squeezy order.
	 * @param array  $attrs    Order attributes.
	 * @param mixed  $custom   Checkout custom data.
	 */
	private static function order_created( $order_id, array $attrs, $custom ) {
		if ( 'paid' !== (string) ( $attrs['status'] ?? '' ) ) {
			return;
		}
		$intent = self::intent( $custom );
		$plan   = $intent ? Plans::from_product( Plans::product_id( $intent['plan'] ) ) : null;
		if ( ! $intent || ! $plan || ! get_userdata( $intent['user'] ) ) {
			Events::error( 'ls.order_unmatched', sprintf( 'Lemon Squeezy order %s has no matching buyer or plan.', $order_id ), array() );
			return;
		}
		foreach ( array( 'renew', 'upgrade' ) as $k ) {
			$row = $intent[ $k ] ? Licenses::get( $intent[ $k ] ) : null;
			if ( $intent[ $k ] && ( ! $row || (int) $row['user_id'] !== $intent['user'] ) ) {
				$intent[ $k ] = 0; // Not the buyer's license: create a new one instead.
			}
		}
		$result = Fulfilment::apply( $intent['user'], $plan, $intent['renew'], $intent['upgrade'], 0, 'ls:' . $order_id );
		$id     = (int) $result['license']['id'];
		update_option( 'wpes_ls_order_' . $order_id, $id, false );
		$pending_sub = (string) get_option( 'wpes_ls_order_sub_' . $order_id, '' );
		if ( '' !== $pending_sub ) {
			self::link_subscription( $pending_sub, $order_id );
		}
	}

	/**
	 * Remember which license a subscription bills (the order may arrive after it).
	 *
	 * @param string $subscription_id Subscription.
	 * @param string $order_id        Its first order.
	 */
	private static function link_subscription( $subscription_id, $order_id ) {
		$license_id = (int) get_option( 'wpes_ls_order_' . $order_id, 0 );
		if ( ! $license_id ) {
			update_option( 'wpes_ls_order_sub_' . $order_id, $subscription_id, false );
			return;
		}
		update_option( 'wpes_ls_sub_' . $license_id, $subscription_id, false );
		update_option( 'wpes_ls_sub_license_' . $subscription_id, $license_id, false );
		delete_option( 'wpes_ls_order_sub_' . $order_id );
		// Lemon Squeezy renews it; our cron must not create WooCommerce renewal orders.
		Licenses::update( $license_id, array( 'auto_renew' => 1 ) );
	}

	/**
	 * Keep the customer portal link, where the customer cancels or changes the card.
	 *
	 * @param string $subscription_id Subscription.
	 * @param array  $attrs           Subscription attributes.
	 */
	private static function remember_portal( $subscription_id, array $attrs ) {
		$url = esc_url_raw( (string) ( $attrs['urls']['customer_portal'] ?? '' ) );
		if ( '' !== $url ) {
			update_option( 'wpes_ls_portal_' . $subscription_id, $url, false );
		}
	}

	/**
	 * Customer portal for a license billed by Lemon Squeezy ('' when unknown).
	 *
	 * @param array $license License.
	 * @return string
	 */
	public static function portal( $license ) {
		$sub = (string) get_option( 'wpes_ls_sub_' . (int) $license['id'], '' );
		return '' === $sub ? '' : (string) get_option( 'wpes_ls_portal_' . $sub, '' );
	}

	/**
	 * Subscription renewal paid.
	 *
	 * @param string $subscription_id Subscription.
	 * @param string $reason          billing_reason.
	 * @param string $invoice_id      Invoice.
	 */
	private static function renewal( $subscription_id, $reason, $invoice_id ) {
		if ( 'renewal' !== $reason ) {
			return; // The first payment is fulfilled by order_created.
		}
		$license_id = (int) get_option( 'wpes_ls_sub_license_' . $subscription_id, 0 );
		$license    = $license_id ? Licenses::get( $license_id ) : null;
		$plan       = $license ? Plans::from_product( (int) $license['product_id'] ) : null;
		if ( ! $license || ! $plan ) {
			Events::error( 'ls.renewal_unmatched', sprintf( 'Lemon Squeezy subscription %s has no license.', $subscription_id ), array() );
			return;
		}
		Fulfilment::apply( (int) $license['user_id'], $plan, $license_id, 0, 0, 'ls:invoice:' . $invoice_id );
	}

	/**
	 * Cancel / resume / expire.
	 *
	 * @param string $subscription_id Subscription.
	 * @param string $event           Event name.
	 */
	private static function subscription_state( $subscription_id, $event ) {
		$license_id = (int) get_option( 'wpes_ls_sub_license_' . $subscription_id, 0 );
		if ( ! $license_id ) {
			return;
		}
		$fields = array(
			'subscription_cancelled' => array( 'auto_renew' => 0 ),
			'subscription_resumed'   => array( 'auto_renew' => 1 ),
			'subscription_expired'   => array(
				'auto_renew' => 0,
				'status'     => 'expired',
			),
		);
		Licenses::update( $license_id, $fields[ $event ] );
		Events::log( 'ls.' . $event, sprintf( 'Lemon Squeezy %1$s for license #%2$d.', $event, $license_id ), array( 'license_id' => $license_id ) );
	}

	/**
	 * Refund: back to the trial if the order upgraded one, else revoke.
	 *
	 * @param string $order_id Order.
	 */
	private static function refunded( $order_id ) {
		$license_id = (int) get_option( 'wpes_ls_order_' . $order_id, 0 );
		if ( ! $license_id || Trials::restore_after_refund( $license_id, 'ls:' . $order_id ) ) {
			return;
		}
		Licenses::update(
			$license_id,
			array(
				'status'     => 'revoked',
				'auto_renew' => 0,
			)
		);
		Events::log( 'ls.refunded', sprintf( 'Lemon Squeezy order %1$s refunded: license #%2$d revoked.', $order_id, $license_id ), array( 'license_id' => $license_id ), 'warning' );
	}
}
