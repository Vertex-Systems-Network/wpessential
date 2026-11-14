<?php
/**
 * 30-day Pro trial: one per website and one per account, never sold, no grace period.
 *
 * The trial is an ordinary license on the private "Pro Trial" plan, so activation, checks,
 * package downloads and in-place upgrades work exactly like paid licenses. Buying a Pro plan
 * while a trial is active upgrades the trial license in place, so the site keeps its key.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Trial issuance and rules.
 */
class Trials {

	const PLAN = 'pro_trial';

	const DAYS = 30;

	/** Days before the end when a reminder is sent. */
	const REMINDER_DAYS = array( 7, 2 );

	/**
	 * Trial plan definition.
	 *
	 * @return array
	 */
	public static function plan() {
		return array(
			'name'     => __( 'Pro Trial', 'wpessential-server' ),
			'price'    => '0',
			'months'   => -1,
			'sites'    => 1,
			'white'    => 0,
			'pro'      => 1,
			'features' => array( __( 'Every Pro feature for 30 days', 'wpessential-server' ), __( '1 website', 'wpessential-server' ), __( 'Your data is kept when the trial ends', 'wpessential-server' ) ),
		);
	}

	/**
	 * Private, non-purchasable product for the trial plan (created once).
	 *
	 * @return int
	 */
	public static function product() {
		$id = Plans::product_id( self::PLAN );
		if ( $id ) {
			return $id;
		}
		$plan    = self::plan();
		$product = new \WC_Product_Simple();
		/* translators: %s: plan name. */
		$product->set_name( sprintf( __( 'WPEssential %s', 'wpessential-server' ), $plan['name'] ) );
		$product->set_status( 'private' );
		$product->set_regular_price( $plan['price'] );
		$product->set_virtual( true );
		$product->set_sold_individually( true );
		$product->set_catalog_visibility( 'hidden' );
		$product->update_meta_data( '_wpes_plan', self::PLAN );
		$product->update_meta_data( '_wpes_months', $plan['months'] );
		$product->update_meta_data( '_wpes_sites', $plan['sites'] );
		$product->update_meta_data( '_wpes_white_label', $plan['white'] );
		$product->update_meta_data( '_wpes_pro', $plan['pro'] );
		$product->save();
		return $product->get_id();
	}

	/**
	 * Whether a license row is on the trial plan.
	 *
	 * @param array|null $license License.
	 * @return bool
	 */
	public static function is_trial( $license ) {
		return is_array( $license ) && self::PLAN === ( $license['plan'] ?? '' );
	}

	/**
	 * Eligibility rule (pure, unit-tested). Returns an error code, or null when allowed.
	 *
	 * @param string $domain       Normalised domain.
	 * @param bool   $staging      Domain is local/staging.
	 * @param bool   $site_used    A trial was ever started for this domain.
	 * @param bool   $account_used This account ever started a trial.
	 * @param bool   $has_pro      This account already has a usable paid Pro license.
	 * @return string|null
	 */
	public static function refusal( $domain, $staging, $site_used, $account_used, $has_pro ) {
		if ( '' === $domain ) {
			return 'domain';
		}
		if ( $staging ) {
			return 'trial_staging';
		}
		if ( $has_pro ) {
			return 'trial_has_pro';
		}
		if ( $site_used ) {
			return 'trial_used_site';
		}
		if ( $account_used ) {
			return 'trial_used_account';
		}
		return null;
	}

	/**
	 * Messages for refusal codes.
	 *
	 * @param string $code Code.
	 * @return string
	 */
	public static function message( $code ) {
		$messages = array(
			'domain'             => __( 'Send the site address.', 'wpessential-server' ),
			'trial_staging'      => __( 'Start the trial on your live website. Local and staging sites can use Pro with a paid plan at no extra cost.', 'wpessential-server' ),
			'trial_has_pro'      => __( 'Your account already has Pro. Connect this website to your subscription instead.', 'wpessential-server' ),
			'trial_used_site'    => __( 'This website already had a Pro trial. Choose a plan to keep using Pro.', 'wpessential-server' ),
			'trial_used_account' => __( 'Your account already used its Pro trial. Choose a plan to keep using Pro.', 'wpessential-server' ),
			'throttled'          => __( 'Too many attempts. Wait 15 minutes and try again.', 'wpessential-server' ),
			'trial_failed'       => __( 'The trial could not be started. Try again in a minute.', 'wpessential-server' ),
		);
		return $messages[ $code ] ?? $messages['trial_failed'];
	}

	/**
	 * End date of a trial starting at a time.
	 *
	 * @param int $start Unix time.
	 * @return string MySQL datetime (UTC).
	 */
	public static function ends_at( $start ) {
		return gmdate( 'Y-m-d H:i:s', $start + self::DAYS * DAY_IN_SECONDS );
	}

	/**
	 * Reminder stage due for a trial ending at $end (largest threshold crossed), or 0.
	 *
	 * @param int $end Unix end time.
	 * @param int $now Unix now.
	 * @return int Days threshold (7 or 2), 0 when none is due.
	 */
	public static function reminder_stage( $end, $now ) {
		$left = $end - $now;
		if ( $left <= 0 ) {
			return 0;
		}
		$stage = 0;
		foreach ( self::REMINDER_DAYS as $days ) {
			if ( $left <= $days * DAY_IN_SECONDS ) {
				$stage = $days;
			}
		}
		return $stage;
	}

	/**
	 * Start a trial for a signed-in customer on a website.
	 *
	 * @param int    $user_id User.
	 * @param string $site    Site address.
	 * @return array|\WP_Error Owner-shaped license.
	 */
	public static function start( $user_id, $site ) {
		global $wpdb;
		if ( Accounts::throttled( 'trial' ) ) {
			return self::error( 'throttled' );
		}
		$domain   = Licenses::domain( (string) $site );
		$licenses = Licenses::for_user( (int) $user_id );
		$has_pro  = false;
		foreach ( $licenses as $license ) {
			$plan = Plans::from_product( (int) $license['product_id'] );
			if ( ! self::is_trial( $license ) && $plan && $plan['pro'] && true === Licenses::validate( $license ) ) {
				$has_pro = true;
				break;
			}
		}
		$table        = $wpdb->prefix . 'wpes_trials';
		$site_used    = '' !== $domain && (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE domain = %s", $domain ) ); // phpcs:ignore WordPress.DB -- Custom table.
		$account_used = (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE user_id = %d", (int) $user_id ) ); // phpcs:ignore WordPress.DB -- Custom table.
		$refusal      = self::refusal( $domain, '' !== $domain && Licenses::is_staging( $domain ), $site_used, $account_used, $has_pro );
		if ( null !== $refusal ) {
			Events::log(
				'trial.refused',
				sprintf( 'Trial refused for %1$s: %2$s.', $domain, $refusal ),
				array(
					'user_id' => (int) $user_id,
					'domain'  => $domain,
				),
				'warning'
			);
			return self::error( $refusal );
		}

		// The unique domain key makes two simultaneous requests for one site start one trial.
		$now      = time();
		$inserted = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table.
			$table,
			array(
				'user_id'    => (int) $user_id,
				'domain'     => $domain,
				'license_id' => 0,
				'started_at' => gmdate( 'Y-m-d H:i:s', $now ),
				'ends_at'    => self::ends_at( $now ),
			)
		);
		if ( ! $inserted ) {
			return self::error( 'trial_used_site' );
		}
		$trial_id = (int) $wpdb->insert_id;

		$license = Licenses::create( (int) $user_id, Plans::from_product( self::product() ) );
		if ( ! $license ) {
			$wpdb->delete( $table, array( 'id' => $trial_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
			return self::error( 'trial_failed' );
		}
		$license = Licenses::update(
			$license['id'],
			array(
				'expires_at' => self::ends_at( $now ),
				'auto_renew' => 0,
			)
		);
		$wpdb->update( $table, array( 'license_id' => (int) $license['id'] ), array( 'id' => $trial_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
		$activated = Licenses::activate( $license['license_key'], $domain );
		if ( is_wp_error( $activated ) ) {
			return $activated;
		}
		Events::log( 'trial.started', sprintf( 'Pro trial %1$s started for %2$s, ends %3$s.', $license['license_key'], $domain, $license['expires_at'] ), Events::of( $license, array( 'domain' => $domain ) ) );
		return Licenses::owner_shape( Licenses::get( $license['id'] ) );
	}

	/**
	 * Active (unexpired) trial license id of a customer, or 0.
	 *
	 * @param int $user_id User.
	 * @return int
	 */
	public static function active_trial_id( $user_id ) {
		foreach ( Licenses::for_user( (int) $user_id ) as $license ) {
			if ( self::is_trial( $license ) && 'active' === $license['status'] && ! Licenses::is_expired( $license ) ) {
				return (int) $license['id'];
			}
		}
		return 0;
	}

	/**
	 * Daily: reminders 7 and 2 days before the end (once each).
	 *
	 * @return int Reminders sent.
	 */
	public static function remind() {
		global $wpdb;
		$table = $wpdb->prefix . 'wpes_licenses';
		$rows  = $wpdb->get_results( // phpcs:ignore WordPress.DB -- Custom table.
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE plan = %s AND status = 'active' AND expires_at IS NOT NULL AND expires_at <= %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name.
				self::PLAN,
				gmdate( 'Y-m-d H:i:s', time() + max( self::REMINDER_DAYS ) * DAY_IN_SECONDS )
			),
			ARRAY_A
		);
		$sent  = 0;
		foreach ( (array) $rows as $license ) {
			$stage = self::reminder_stage( strtotime( $license['expires_at'] . ' UTC' ), time() );
			$flag  = 'wpes_trial_reminded_' . $license['id'] . '_' . $stage;
			if ( $stage && ! get_option( $flag ) ) {
				Renewals::mail( $license, 'trial_reminder' );
				update_option( $flag, 1, false );
				++$sent;
			}
		}
		return $sent;
	}

	/**
	 * Error with its message.
	 *
	 * @param string $code Code.
	 * @return \WP_Error
	 */
	private static function error( $code ) {
		return new \WP_Error( $code, self::message( $code ) );
	}
}
