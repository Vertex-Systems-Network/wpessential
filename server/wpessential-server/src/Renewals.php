<?php
/**
 * Auto-renewal, reminders and expiry (daily cron).
 *
 * Renewal orders are normal WooCommerce orders marked with _wpes_renew. When the
 * customer saved a card with a gateway that supports tokens, the gateway charges it
 * through the `wpes_charge_renewal` filter; otherwise the customer gets a pay link.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Renewals.
 */
class Renewals {

	const CRON = 'wpes_server_daily';

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( self::CRON, array( __CLASS__, 'run' ) );
		add_action(
			'init',
			static function () {
				if ( ! wp_next_scheduled( self::CRON ) ) {
					wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON );
				}
			}
		);
	}

	/**
	 * Remove the cron on deactivation.
	 */
	public static function unschedule() {
		wp_clear_scheduled_hook( self::CRON );
	}

	/**
	 * Daily job.
	 *
	 * @return array Counts for logging and tests.
	 */
	public static function run() {
		global $wpdb;
		$table  = $wpdb->prefix . 'wpes_licenses';
		$remind = (int) Plugin::settings( 'remind_days' );
		$counts = array(
			'renewal_orders' => 0,
			'charged'        => 0,
			'reminders'      => 0,
			'expired'        => 0,
		);

		// Licenses ending within the reminder window.
		$due = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
			$wpdb->prepare( "SELECT * FROM {$table} WHERE status = 'active' AND expires_at IS NOT NULL AND expires_at <= %s", gmdate( 'Y-m-d H:i:s', time() + $remind * DAY_IN_SECONDS ) ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name.
			ARRAY_A
		);
		$run = Events::begin( 'cron' );
		Events::log( 'renewal.run_start', sprintf( 'Renewal run started: %d license(s) due.', count( (array) $due ) ), array( 'due' => count( (array) $due ) ) );
		foreach ( (array) $due as $license ) {
			// Each license gets its own trace so its steps read as one story.
			Events::begin( 'cron' );
			try {
				self::process( $license, $counts );
			} catch ( \Throwable $e ) {
				Events::error( 'renewal.exception', $e->getMessage(), Events::of( $license, array( 'file' => basename( $e->getFile() ) . ':' . $e->getLine() ) ) );
			}
		}
		Events::begin( 'cron' );
		Events::log( 'renewal.run_end', 'Renewal run finished.', array_merge( $counts, array( 'run' => $run ) ) );
		Events::end();
		return $counts;
	}

	/**
	 * One due license.
	 *
	 * @param array $license License.
	 * @param array $counts  Counts (by reference).
	 */
	private static function process( $license, array &$counts ) {
		if ( Licenses::is_expired( $license ) ) {
			Licenses::update( $license['id'], array( 'status' => 'expired' ) );
			self::mail( $license, 'expired' );
			++$counts['expired'];
			return;
		}
		if ( $license['auto_renew'] ) {
			$result = self::renewal_order( $license );
			if ( $result ) {
				++$counts['renewal_orders'];
				if ( 'charged' === $result ) {
					++$counts['charged'];
				}
			}
		} elseif ( ! get_option( 'wpes_reminded_' . $license['id'] . '_' . $license['expires_at'] ) ) {
			self::mail( $license, 'reminder' );
			update_option( 'wpes_reminded_' . $license['id'] . '_' . $license['expires_at'], 1, false );
			++$counts['reminders'];
			Events::log( 'subscription.reminder_sent', sprintf( 'Reminder sent: license %1$s ends %2$s and auto-renew is off.', $license['license_key'], $license['expires_at'] ), Events::of( $license ) );
		}
	}

	/**
	 * Pending renewal order for a license (reused if one exists).
	 *
	 * @param array $license License.
	 * @return \WC_Order|null
	 */
	public static function pending_order( $license ) {
		$orders = wc_get_orders(
			array(
				'limit'       => 1,
				'customer_id' => (int) $license['user_id'],
				'status'      => array( 'pending', 'failed', 'on-hold' ),
				'meta_key'    => '_wpes_renewal_of', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Indexed by customer first.
				'meta_value'  => (int) $license['id'], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Indexed by customer first.
			)
		);
		return $orders ? $orders[0] : null;
	}

	/**
	 * Create the renewal order and try to charge it.
	 *
	 * @param array $license License.
	 * @return string|false "charged", "invoiced" or false.
	 */
	public static function renewal_order( $license ) {
		$pending = self::pending_order( $license );
		if ( $pending ) {
			Events::log( 'renewal.awaiting_payment', sprintf( 'Renewal order #%1$d for license %2$s is still %3$s.', $pending->get_id(), $license['license_key'], $pending->get_status() ), Events::of( $license, array( 'order_id' => $pending->get_id() ) ), 'warning' );
			return false;
		}
		$product = wc_get_product( (int) $license['product_id'] );
		if ( ! $product ) {
			Events::error( 'renewal.product_missing', sprintf( 'Plan product #%1$d of license %2$s no longer exists; no renewal order created.', $license['product_id'], $license['license_key'] ), Events::of( $license ) );
			return false;
		}
		$last  = $license['order_id'] ? wc_get_order( (int) $license['order_id'] ) : null;
		$order = wc_create_order( array( 'customer_id' => (int) $license['user_id'] ) );
		$item  = new \WC_Order_Item_Product();
		$item->set_product( $product );
		$item->set_quantity( 1 );
		$item->set_subtotal( $product->get_price() );
		$item->set_total( $product->get_price() );
		$item->add_meta_data( '_wpes_renew', (int) $license['id'], true );
		$order->add_item( $item );
		if ( $last ) {
			$order->set_address( $last->get_address( 'billing' ), 'billing' );
			$order->set_payment_method( $last->get_payment_method() );
			$order->set_payment_method_title( $last->get_payment_method_title() );
		} else {
			$customer = new \WC_Customer( (int) $license['user_id'] );
			$order->set_billing_email( $customer->get_email() );
			$order->set_billing_first_name( $customer->get_first_name() );
			$order->set_billing_last_name( $customer->get_last_name() );
		}
		$order->update_meta_data( '_wpes_renewal_of', (int) $license['id'] );
		$order->calculate_totals();
		/* translators: %s: key. */
		$order->add_order_note( sprintf( __( 'Automatic renewal for license %s.', 'wpessential-server' ), $license['license_key'] ) );
		$order->save();
		$ctx = Events::of(
			$license,
			array(
				'order_id' => $order->get_id(),
				'total'    => $order->get_total(),
				'gateway'  => $order->get_payment_method(),
			)
		);
		Events::log( 'renewal.order_created', sprintf( 'Renewal order #%1$d created for license %2$s.', $order->get_id(), $license['license_key'] ), $ctx );

		if ( (float) $order->get_total() <= 0 ) {
			$order->payment_complete();
			Events::log( 'renewal.free', 'Renewal total is 0; completed without payment.', $ctx );
			return 'charged';
		}

		$token = self::token( $license, $order->get_payment_method() );
		if ( $token ) {
			$charged = false;
			try {
				$charged = (bool) apply_filters( 'wpes_charge_renewal', false, $order, $token, $license );
			} catch ( \Throwable $e ) {
				Events::error( 'renewal.charge_exception', $e->getMessage(), $ctx );
			}
			if ( $charged ) {
				Events::log( 'renewal.charged', sprintf( 'Saved card charged for renewal order #%d.', $order->get_id() ), array_merge( $ctx, array( 'token' => $token->get_id() ) ) );
				return 'charged';
			}
			Events::error( 'renewal.charge_failed', sprintf( 'Saved card could not be charged for renewal order #%d; invoice sent instead.', $order->get_id() ), array_merge( $ctx, array( 'token' => $token->get_id() ) ) );
		} else {
			Events::log( 'renewal.no_saved_card', 'No saved card for this gateway; invoice sent.', $ctx );
		}
		$mailer = WC()->mailer()->get_emails();
		if ( isset( $mailer['WC_Email_Customer_Invoice'] ) ) {
			$mailer['WC_Email_Customer_Invoice']->trigger( $order->get_id(), $order );
			Events::log( 'renewal.invoiced', sprintf( 'Invoice with pay link emailed for renewal order #%d.', $order->get_id() ), $ctx );
		} else {
			Events::error( 'renewal.invoice_missing', 'WooCommerce invoice email is not available; the customer was not emailed.', $ctx );
		}
		return 'invoiced';
	}

	/**
	 * Saved payment token for the customer and gateway.
	 *
	 * @param array  $license License.
	 * @param string $gateway Gateway id.
	 * @return \WC_Payment_Token|null
	 */
	private static function token( $license, $gateway ) {
		if ( ! $gateway || ! class_exists( 'WC_Payment_Tokens' ) ) {
			return null;
		}
		$token = \WC_Payment_Tokens::get_customer_default_token( (int) $license['user_id'] );
		return $token && $token->get_gateway_id() === $gateway ? $token : null;
	}

	/**
	 * Reminder / expiry email.
	 *
	 * @param array  $license License.
	 * @param string $type    reminder|expired.
	 */
	public static function mail( $license, $type ) {
		$user = get_userdata( (int) $license['user_id'] );
		if ( ! $user ) {
			return;
		}
		$link = wc_get_account_endpoint_url( MyAccount::ENDPOINT );
		if ( 'expired' === $type ) {
			/* translators: %s: key. */
			$subject = sprintf( __( 'Your WPEssential license %s has expired', 'wpessential-server' ), $license['license_key'] );
			$body    = __( 'Pro demos and updates are paused on your websites. Renew in one click to continue — your sites keep working either way.', 'wpessential-server' );
		} else {
			/* translators: 1: key, 2: date. */
			$subject = sprintf( __( 'Your WPEssential license %1$s ends on %2$s', 'wpessential-server' ), $license['license_key'], substr( $license['expires_at'], 0, 10 ) );
			$body    = __( 'Automatic renewal is off for this license. Renew now to keep Pro demos, updates and support.', 'wpessential-server' );
		}
		$mailer  = WC()->mailer();
		$message = $mailer->wrap_message( $subject, '<p>' . esc_html( $body ) . '</p><p><a href="' . esc_url( $link ) . '">' . esc_html__( 'Manage your licenses', 'wpessential-server' ) . '</a></p>' );
		$sent    = $mailer->send( $user->user_email, $subject, $message );
		Events::log( $sent ? 'mail.' . $type : 'mail.' . $type . '_failed', $subject, Events::of( $license ), $sent ? 'info' : 'error' );
	}
}
