<?php
/**
 * Turns one paid plan purchase into a license change: create, renew or upgrade.
 * Shared by WooCommerce orders (Orders) and Lemon Squeezy webhooks (LemonSqueezy).
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Purchase fulfilment.
 */
class Fulfilment {

	/**
	 * Which change a purchase makes (pure, unit-tested).
	 *
	 * A trial is never renewed (renewing it upgrades it), and buying Pro during a trial upgrades
	 * the trial in place so the website keeps its key.
	 *
	 * @param int  $renew        License to renew (0 none).
	 * @param bool $renew_trial  That license is a trial.
	 * @param int  $upgrade      License to upgrade (0 none).
	 * @param bool $plan_pro     The bought plan is Pro.
	 * @param int  $active_trial The buyer's running trial (0 none).
	 * @return array{0:string,1:int} [ 'renew'|'upgrade'|'create', license id ]
	 */
	public static function decide( $renew, $renew_trial, $upgrade, $plan_pro, $active_trial ) {
		if ( $renew && $renew_trial ) {
			return array( 'upgrade', (int) $renew );
		}
		if ( $renew ) {
			return array( 'renew', (int) $renew );
		}
		if ( $upgrade ) {
			return array( 'upgrade', (int) $upgrade );
		}
		if ( $plan_pro && $active_trial ) {
			return array( 'upgrade', (int) $active_trial );
		}
		return array( 'create', 0 );
	}

	/**
	 * Apply a paid purchase.
	 *
	 * @param int    $user_id  Buyer.
	 * @param array  $plan     Plan from Plans::from_product().
	 * @param int    $renew    License to renew (0 none).
	 * @param int    $upgrade  License to upgrade (0 none).
	 * @param int    $order_id WooCommerce order (0 for other providers).
	 * @param string $ref      Purchase reference for the trial-refund record ("wc:123", "ls:456").
	 * @param array  $extra    Extra license columns (e.g. auto_renew handled by the provider).
	 * @return array{action:string,license:array}
	 */
	public static function apply( $user_id, array $plan, $renew, $upgrade, $order_id, $ref, array $extra = array() ) {
		$renew_row = $renew ? Licenses::get( $renew ) : null;
		$up_row    = $upgrade ? Licenses::get( $upgrade ) : null;
		if ( ( $renew && ! $renew_row ) || ( $upgrade && ! $up_row ) ) {
			Events::error(
				'order.license_missing',
				sprintf( 'Purchase %1$s points at license #%2$d, which does not exist. A new license is created instead.', $ref, $renew ? $renew : $upgrade ),
				array(
					'order_id' => (int) $order_id,
					'user_id'  => (int) $user_id,
				)
			);
		}
		list( $action, $id ) = self::decide(
			$renew_row ? (int) $renew : 0,
			Trials::is_trial( $renew_row ),
			$up_row ? (int) $upgrade : 0,
			(bool) $plan['pro'],
			Trials::active_trial_id( (int) $user_id )
		);

		// A WooCommerce payment now bills a license Lemon Squeezy billed: stop that subscription.
		if ( $order_id > 0 && 'create' !== $action && LemonSqueezy::manages( Licenses::get( $id ) ) ) {
			LemonSqueezy::detach( Licenses::get( $id ) );
		}

		if ( 'renew' === $action ) {
			$license = Licenses::get( $id );
			$license = Licenses::update(
				$id,
				array(
					'status'     => 'active',
					'expires_at' => Licenses::expiry( $plan['months'], $license['expires_at'] ),
					'order_id'   => (int) $order_id,
				) + $extra
			);
			Events::log( 'subscription.renewed', sprintf( 'License %1$s renewed until %2$s (%3$s).', $license['license_key'], (string) $license['expires_at'], $ref ), Events::of( $license, array( 'order_id' => (int) $order_id ) ) );
		} elseif ( 'upgrade' === $action ) {
			$before    = Licenses::get( $id );
			$was_trial = Trials::is_trial( $before );
			if ( $was_trial ) {
				Trials::remember_upgrade( $before, $ref );
			}
			$license = Licenses::update(
				$id,
				array(
					'plan'        => $plan['key'],
					'product_id'  => $plan['product_id'],
					'sites_limit' => $plan['sites'],
					'white_label' => $plan['white_label'] ? 1 : 0,
					'status'      => 'active',
					'auto_renew'  => $plan['months'] > 0 ? 1 : 0,
					'expires_at'  => Licenses::expiry( $plan['months'] ),
					'major'       => Licenses::lifetime_major( $plan ),
					'order_id'    => (int) $order_id,
				) + $extra
			);
			if ( $was_trial ) {
				Licenses::reclassify_activations( (int) $license['id'] );
			}
			Events::log( 'subscription.upgraded', sprintf( 'License %1$s upgraded to %2$s (%3$s).', $license['license_key'], $plan['name'], $ref ), Events::of( $license, array( 'order_id' => (int) $order_id ) ) );
		} else {
			$license = Licenses::create( (int) $user_id, $plan, (int) $order_id, $extra );
		}
		Payments::clear_pending( (int) $license['id'] );

		return array(
			'action'  => $action,
			'license' => $license,
		);
	}
}
