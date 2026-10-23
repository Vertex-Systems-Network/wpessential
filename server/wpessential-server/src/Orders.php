<?php
/**
 * WooCommerce orders → licenses (new, renewal, upgrade, refund).
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Order handling.
 */
class Orders {

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'cart_item_data' ), 10, 2 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'cart_item_label' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'line_item_meta' ), 10, 3 );
		add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'upgrade_credit' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array( __CLASS__, 'autocomplete' ), 10, 3 );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'fulfil' ) );
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'fulfil' ) );
		add_action( 'woocommerce_order_status_refunded', array( __CLASS__, 'revoke' ) );
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'email_keys' ), 10, 2 );
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'thankyou_keys' ), 5 );
		add_filter( 'woocommerce_checkout_registration_required', array( __CLASS__, 'require_account' ) );
	}

	/**
	 * Licenses belong to accounts, so plan orders need one.
	 *
	 * @param bool $required Required.
	 * @return bool
	 */
	public static function require_account( $required ) {
		if ( WC()->cart ) {
			foreach ( WC()->cart->get_cart() as $item ) {
				if ( Plans::from_product( $item['product_id'] ) ) {
					return true;
				}
			}
		}
		return $required;
	}

	/**
	 * Read ?wpes_renew= / ?wpes_upgrade= when the plan is added to the cart.
	 *
	 * @param array $data       Item data.
	 * @param int   $product_id Product.
	 * @return array
	 */
	public static function cart_item_data( $data, $product_id ) {
		if ( ! Plans::from_product( $product_id ) ) {
			return $data;
		}
		// Links come from the customer's own account page; ownership is checked below.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		foreach ( array( 'wpes_renew', 'wpes_upgrade' ) as $param ) {
			if ( isset( $_REQUEST[ $param ] ) ) {
				$license = Licenses::get( absint( $_REQUEST[ $param ] ) );
				if ( $license && get_current_user_id() === (int) $license['user_id'] ) {
					$data[ $param ] = (int) $license['id'];
				}
			}
		}
		// phpcs:enable
		return $data;
	}

	/**
	 * Show "Renewal of WPE-…" under the cart item.
	 *
	 * @param array $meta Display meta.
	 * @param array $item Cart item.
	 * @return array
	 */
	public static function cart_item_label( $meta, $item ) {
		foreach ( array(
			'wpes_renew'   => __( 'Renewal of', 'wpessential-server' ),
			'wpes_upgrade' => __( 'Upgrade of', 'wpessential-server' ),
		) as $key => $label ) {
			if ( ! empty( $item[ $key ] ) ) {
				$license = Licenses::get( $item[ $key ] );
				if ( $license ) {
					$meta[] = array(
						'name'  => $label,
						'value' => $license['license_key'],
					);
				}
			}
		}
		return $meta;
	}

	/**
	 * Copy renewal/upgrade markers onto the order item.
	 *
	 * @param \WC_Order_Item_Product $item   Item.
	 * @param string                 $key    Cart key.
	 * @param array                  $values Cart values.
	 */
	public static function line_item_meta( $item, $key, $values ) {
		foreach ( array( 'wpes_renew', 'wpes_upgrade' ) as $marker ) {
			if ( ! empty( $values[ $marker ] ) ) {
				$item->add_meta_data( '_' . $marker, (int) $values[ $marker ], true );
			}
		}
	}

	/**
	 * Credit for the unused time of the current plan when upgrading.
	 *
	 * @param \WC_Cart $cart Cart.
	 */
	public static function upgrade_credit( $cart ) {
		foreach ( $cart->get_cart() as $item ) {
			if ( empty( $item['wpes_upgrade'] ) ) {
				continue;
			}
			$credit = self::unused_value( Licenses::get( $item['wpes_upgrade'] ) );
			$credit = min( $credit, (float) $item['data']->get_price() );
			if ( $credit > 0 ) {
				$cart->add_fee( __( 'Credit for your current plan', 'wpessential-server' ), -1 * round( $credit, 2 ) );
			}
		}
	}

	/**
	 * Value of the unused part of a license.
	 *
	 * @param array|null $license License.
	 * @return float
	 */
	public static function unused_value( $license ) {
		if ( ! $license || empty( $license['expires_at'] ) || 'active' !== $license['status'] ) {
			return 0.0;
		}
		$plan = Plans::from_product( (int) $license['product_id'] );
		if ( ! $plan || $plan['months'] <= 0 ) {
			return 0.0;
		}
		$end    = strtotime( $license['expires_at'] . ' UTC' );
		$start  = strtotime( '-' . $plan['months'] . ' months', $end );
		$remain = max( 0, $end - time() );
		return min( $plan['price'], $plan['price'] * ( $remain / max( 1, $end - $start ) ) );
	}

	/**
	 * Plan-only orders skip "processing".
	 *
	 * @param string    $status   Status.
	 * @param int       $order_id Order.
	 * @param \WC_Order $order    Order.
	 * @return string
	 */
	public static function autocomplete( $status, $order_id, $order ) {
		foreach ( $order->get_items() as $item ) {
			if ( ! Plans::from_product( $item->get_product_id() ) ) {
				return $status;
			}
		}
		return 'completed';
	}

	/**
	 * Create, renew or upgrade licenses for a paid order (runs once per order).
	 *
	 * @param int $order_id Order.
	 */
	public static function fulfil( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || $order->get_meta( '_wpes_fulfilled' ) ) {
			return;
		}
		$user_id = $order->get_customer_id();
		$ids     = array();
		Events::log(
			'order.fulfil',
			sprintf( 'Fulfilling licenses for order #%d.', $order_id ),
			array(
				'order_id' => $order_id,
				'user_id'  => $user_id,
			)
		);
		foreach ( $order->get_items() as $item ) {
			$plan = Plans::from_product( $item->get_product_id() );
			if ( ! $plan ) {
				continue;
			}
			$renew   = (int) $item->get_meta( '_wpes_renew' );
			$upgrade = (int) $item->get_meta( '_wpes_upgrade' );
			if ( ( $renew && ! Licenses::get( $renew ) ) || ( $upgrade && ! Licenses::get( $upgrade ) ) ) {
				Events::error(
					'order.license_missing',
					sprintf( 'Order #%1$d points at license #%2$d, which does not exist. A new license is created instead.', $order_id, $renew ? $renew : $upgrade ),
					array(
						'order_id' => $order_id,
						'user_id'  => $user_id,
					)
				);
			}
			if ( $renew && ( $license = Licenses::get( $renew ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found,Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure -- Compact lookup.
				$license = Licenses::update(
					$renew,
					array(
						'status'     => 'active',
						'expires_at' => Licenses::expiry( $plan['months'], $license['expires_at'] ),
						'order_id'   => $order_id,
					)
				);
				/* translators: %s: key. */
				$order->add_order_note( sprintf( __( 'License %s renewed.', 'wpessential-server' ), $license['license_key'] ) );
				Events::log( 'subscription.renewed', sprintf( 'License %1$s renewed until %2$s.', $license['license_key'], (string) $license['expires_at'] ), Events::of( $license, array( 'order_id' => $order_id ) ) );
			} elseif ( $upgrade && ( $license = Licenses::get( $upgrade ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found,Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure -- Compact lookup.
				$license = Licenses::update(
					$upgrade,
					array(
						'plan'        => $plan['key'],
						'product_id'  => $plan['product_id'],
						'sites_limit' => $plan['sites'],
						'white_label' => $plan['white_label'] ? 1 : 0,
						'status'      => 'active',
						'auto_renew'  => $plan['months'] > 0 ? 1 : 0,
						'expires_at'  => Licenses::expiry( $plan['months'] ),
						'major'       => Licenses::lifetime_major( $plan ),
						'order_id'    => $order_id,
					)
				);
				/* translators: 1: key, 2: plan. */
				$order->add_order_note( sprintf( __( 'License %1$s upgraded to %2$s.', 'wpessential-server' ), $license['license_key'], $plan['name'] ) );
				Events::log( 'subscription.upgraded', sprintf( 'License %1$s upgraded to %2$s.', $license['license_key'], $plan['name'] ), Events::of( $license, array( 'order_id' => $order_id ) ) );
			} else {
				$license = Licenses::create( $user_id, $plan, $order_id );
				/* translators: %s: key. */
				$order->add_order_note( sprintf( __( 'License %s created.', 'wpessential-server' ), $license['license_key'] ) );
			}
			$ids[] = (int) $license['id'];
		}
		if ( $ids ) {
			$order->update_meta_data( '_wpes_licenses', $ids );
			$order->update_meta_data( '_wpes_fulfilled', 1 );
			$order->save();
		}
	}

	/**
	 * Refund → revoke the licenses this order created.
	 *
	 * @param int $order_id Order.
	 */
	public static function revoke( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			Events::error( 'order.refund_missing', sprintf( 'Refunded order #%d not found.', $order_id ), array( 'order_id' => $order_id ) );
			return;
		}
		Events::log(
			'order.refund_revoke',
			sprintf( 'Order #%d refunded; revoking its licenses.', $order_id ),
			array(
				'order_id' => $order_id,
				'user_id'  => $order->get_customer_id(),
			),
			'warning'
		);
		foreach ( (array) $order->get_meta( '_wpes_licenses' ) as $id ) {
			$license = Licenses::get( $id );
			if ( $license && (int) $license['order_id'] === (int) $order_id ) {
				Licenses::update(
					$id,
					array(
						'status'     => 'revoked',
						'auto_renew' => 0,
					)
				);
			}
		}
	}

	/**
	 * License keys for an order.
	 *
	 * @param \WC_Order $order Order.
	 * @return array
	 */
	private static function keys( $order ) {
		return array_filter( array_map( array( Licenses::class, 'get' ), (array) $order->get_meta( '_wpes_licenses' ) ) );
	}

	/**
	 * Keys in the customer emails.
	 *
	 * @param \WC_Order $order         Order.
	 * @param bool      $sent_to_admin Admin email.
	 */
	public static function email_keys( $order, $sent_to_admin ) {
		$keys = self::keys( $order );
		if ( ! $keys || $sent_to_admin ) {
			return;
		}
		echo '<h2>' . esc_html__( 'Your license', 'wpessential-server' ) . '</h2>';
		foreach ( $keys as $license ) {
			echo '<p style="font-family:monospace;font-size:18px;letter-spacing:1px"><strong>' . esc_html( $license['license_key'] ) . '</strong></p>';
		}
		echo '<p>' . esc_html__( 'In WordPress go to WPEssential → License, paste the key and press Activate — or sign in from the demo import wizard.', 'wpessential-server' ) . '</p>';
	}

	/**
	 * Keys on the thank-you page.
	 *
	 * @param int $order_id Order.
	 */
	public static function thankyou_keys( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order && $order->get_customer_id() === get_current_user_id() ) {
			self::email_keys( $order, false );
		}
	}
}
