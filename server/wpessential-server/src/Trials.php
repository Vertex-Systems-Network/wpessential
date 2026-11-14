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

	/** Local/staging copies one trial may run on, besides its live website. */
	const COPIES = 3;

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
	 * @param bool   $verified     The request proved it comes from that site.
	 * @param bool   $staging      Domain is local/staging.
	 * @param bool   $site_used    A trial was ever started for this domain.
	 * @param bool   $account_used This account ever started a trial.
	 * @param bool   $has_pro      This account already has a usable paid Pro license.
	 * @return string|null
	 */
	public static function refusal( $domain, $verified, $staging, $site_used, $account_used, $has_pro ) {
		if ( '' === $domain ) {
			return 'domain';
		}
		if ( ! $verified ) {
			return 'trial_site_unverified';
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
	 * Can a trial license be activated on one more site? (pure, unit-tested)
	 *
	 * @param bool  $staging     The new site is local/staging.
	 * @param array $activations Existing activation rows (with is_staging).
	 * @return string|null Error code, or null when allowed.
	 */
	public static function activation_refusal( $staging, array $activations ) {
		$copies = count(
			array_filter(
				$activations,
				static function ( $a ) {
					return ! empty( $a['is_staging'] );
				}
			)
		);
		$live   = count( $activations ) - $copies;
		if ( $staging ) {
			return $copies >= self::COPIES ? 'trial_copies_used' : null;
		}
		return $live >= 1 ? 'trial_live_used' : null;
	}

	/**
	 * Whether a trial license may take this live domain: only the domain reserved for the
	 * account's trial (set at start with site proof, or by attach_live()).
	 *
	 * @param array  $license Trial license.
	 * @param string $domain  Normalised live domain.
	 * @return bool
	 */
	public static function live_domain_reserved( $license, $domain ) {
		global $wpdb;
		$table = $wpdb->prefix . 'wpes_trials';
		return $domain === (string) $wpdb->get_var( $wpdb->prepare( "SELECT domain FROM {$table} WHERE user_id = %d", (int) $license['user_id'] ) ); // phpcs:ignore WordPress.DB -- Custom table.
	}

	/**
	 * Messages for refusal codes.
	 *
	 * @param string $code Code.
	 * @return string
	 */
	public static function message( $code ) {
		$messages = array(
			'domain'                => __( 'Send the site address.', 'wpessential-server' ),
			'trial_site_unverified' => __( 'WPEssential could not confirm that this request came from that website. Start the trial from the WPEssential plugin on the website itself, and make sure its REST API is reachable.', 'wpessential-server' ),
			'trial_staging'         => __( 'Start the trial on your live website or on a local copy (localhost, .local, .test). Once it runs, it also works on staging copies of that site.', 'wpessential-server' ),
			'trial_live_used'       => __( 'Your Pro trial is already on a live website. Use it on staging or local copies of that site, or choose a plan.', 'wpessential-server' ),
			'trial_copies_used'     => __( 'Your Pro trial is already on 3 staging or local copies. Remove one from your account first.', 'wpessential-server' ),
			'trial_has_pro'         => __( 'Your account already has Pro. Connect this website to your subscription instead.', 'wpessential-server' ),
			'trial_used_site'       => __( 'This website already had a Pro trial. Choose a plan to keep using Pro.', 'wpessential-server' ),
			'trial_used_account'    => __( 'Your account already used its Pro trial. Choose a plan to keep using Pro.', 'wpessential-server' ),
			'throttled'             => __( 'Too many attempts. Wait 15 minutes and try again.', 'wpessential-server' ),
			'trial_failed'          => __( 'The trial could not be started. Try again in a minute.', 'wpessential-server' ),
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
	 * @param string $proof   One-time token the site published the hash of (see SiteProof).
	 * @return array|\WP_Error Owner-shaped license.
	 */
	public static function start( $user_id, $site, $proof = '' ) {
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
		// A local development site can start the account's trial (it cannot be public, so there is
		// no one to take a trial from); public staging hosts cannot.
		$local     = '' !== $domain && Licenses::looks_local( $domain );
		$staging   = '' !== $domain && ! $local && Licenses::looks_staging( $domain );
		$site_used = $site_used && ! $local;
		// Only reach out to a public site once the cheap rules pass.
		$verified = $local || ( null === self::refusal( $domain, true, $staging, $site_used, $account_used, $has_pro ) && SiteProof::verify( $site, $proof ) );
		$refusal  = self::refusal( $domain, $verified, $staging, $site_used, $account_used, $has_pro );
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
				// Local names (localhost, *.test) are shared by everyone, so they are not reserved.
				'domain'     => $local ? 'local:' . (int) $user_id : $domain,
				'license_id' => 0,
				'started_at' => gmdate( 'Y-m-d H:i:s', $now ),
				'ends_at'    => self::ends_at( $now ),
			)
		);
		if ( ! $inserted ) {
			// Unique keys on domain and user_id: a concurrent request already started a trial.
			return self::error( $site_used ? 'trial_used_site' : 'trial_used_account' );
		}
		$trial_id = (int) $wpdb->insert_id;

		// The end date is written in the same insert, so a trial can never exist without one.
		$license = Licenses::create(
			(int) $user_id,
			Plans::from_product( self::product() ),
			0,
			array(
				'expires_at' => self::ends_at( $now ),
				'auto_renew' => 0,
			)
		);
		if ( ! $license || empty( $license['expires_at'] ) ) {
			if ( $license ) {
				Licenses::update( $license['id'], array( 'status' => 'revoked' ) );
			}
			$wpdb->delete( $table, array( 'id' => $trial_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
			return self::error( 'trial_failed' );
		}
		$wpdb->update( $table, array( 'license_id' => (int) $license['id'] ), array( 'id' => $trial_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
		$activated = Licenses::activate( $license['license_key'], $domain );
		if ( is_wp_error( $activated ) ) {
			return $activated;
		}
		Events::log( 'trial.started', sprintf( 'Pro trial %1$s started for %2$s, ends %3$s.', $license['license_key'], $domain, $license['expires_at'] ), Events::of( $license, array( 'domain' => $domain ) ) );
		return Licenses::owner_shape( Licenses::get( $license['id'] ) );
	}

	/**
	 * Move the account's running trial onto a live website (e.g. a trial started on a local copy
	 * when the site goes live). The site must prove the request comes from it, and the domain
	 * becomes this account's trial site.
	 *
	 * @param int    $user_id User.
	 * @param string $site    Site URL.
	 * @param string $proof   Site proof token.
	 * @return array|\WP_Error|null Owner-shaped license, an error, or null when there is no trial to move.
	 */
	public static function attach_live( $user_id, $site, $proof ) {
		global $wpdb;
		$domain = Licenses::domain( (string) $site );
		$id     = self::active_trial_id( (int) $user_id );
		if ( ! $id || '' === $domain || Licenses::looks_staging( $domain ) ) {
			return null;
		}
		$table    = $wpdb->prefix . 'wpes_trials';
		$reserved = (string) $wpdb->get_var( $wpdb->prepare( "SELECT domain FROM {$table} WHERE user_id = %d", (int) $user_id ) ); // phpcs:ignore WordPress.DB -- Custom table.
		$license  = Licenses::get( $id );
		if ( $reserved === $domain ) {
			// The trial's own live website (e.g. reconnecting it): no move needed.
			$activated = Licenses::activate( $license['license_key'], $domain );
			return is_wp_error( $activated ) ? $activated : Licenses::owner_shape( Licenses::get( $id ) );
		}
		// A trial moves to a live website once: from a local start. A trial that already has its
		// live website keeps it, so every domain that ever had a trial stays reserved.
		if ( 0 !== strpos( $reserved, 'local:' ) ) {
			return self::error( 'trial_live_used' );
		}
		$taken = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$table} WHERE domain = %s", $domain ) ); // phpcs:ignore WordPress.DB -- Custom table.
		if ( null !== $taken ) {
			return self::error( 'trial_used_site' );
		}
		if ( ! SiteProof::verify( $site, $proof ) ) {
			return self::error( 'trial_site_unverified' );
		}
		// Reserve the domain only if the row still holds the local marker (and the unique key holds).
		$moved = $wpdb->update( // phpcs:ignore WordPress.DB -- Custom table.
			$table,
			array( 'domain' => $domain ),
			array(
				'user_id' => (int) $user_id,
				'domain'  => $reserved,
			)
		);
		if ( 1 !== $moved ) {
			return self::error( 'trial_used_site' );
		}
		$activated = Licenses::activate( $license['license_key'], $domain );
		if ( is_wp_error( $activated ) ) {
			$wpdb->update( // phpcs:ignore WordPress.DB -- Custom table.
				$table,
				array( 'domain' => $reserved ),
				array(
					'user_id' => (int) $user_id,
					'domain'  => $domain,
				)
			);
			return $activated;
		}
		Events::log( 'trial.moved_live', sprintf( 'Pro trial %1$s now runs on %2$s.', $license['license_key'], $domain ), Events::of( $license, array( 'domain' => $domain ) ) );
		return Licenses::owner_shape( Licenses::get( $id ) );
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
	 * Remember that a paid order upgraded this trial license, so a refund can restore the trial.
	 *
	 * @param array  $license Trial license before the upgrade.
	 * @param string $ref     Purchase reference ("wc:123", "ls:456").
	 */
	public static function remember_upgrade( $license, $ref ) {
		update_option(
			'wpes_trial_upgrade_' . sanitize_key( (string) $ref ),
			array(
				'license_id' => (int) $license['id'],
				'product_id' => (int) $license['product_id'],
				'expires_at' => (string) $license['expires_at'],
			),
			false
		);
	}

	/**
	 * On refund of an order that upgraded a trial: give back the rest of the trial instead of
	 * revoking the license. Returns true when the license was restored.
	 *
	 * @param int    $license_id License.
	 * @param string $ref        Refunded purchase reference ("wc:123", "ls:456").
	 * @return bool
	 */
	public static function restore_after_refund( $license_id, $ref ) {
		$option = 'wpes_trial_upgrade_' . sanitize_key( (string) $ref );
		$saved  = get_option( $option );
		if ( ! is_array( $saved ) || (int) $saved['license_id'] !== (int) $license_id ) {
			return false;
		}
		delete_option( $option );
		$active = strtotime( $saved['expires_at'] . ' UTC' ) > time();
		Licenses::update(
			$license_id,
			array(
				'plan'        => self::PLAN,
				'product_id'  => (int) $saved['product_id'],
				'sites_limit' => 1,
				'white_label' => 0,
				'major'       => 0,
				'auto_renew'  => 0,
				'expires_at'  => $saved['expires_at'],
				'status'      => $active ? 'active' : 'expired',
				'order_id'    => 0,
			)
		);
		Events::log(
			'trial.restored_after_refund',
			sprintf( 'Purchase %1$s refunded: license #%2$d is back on its trial until %3$s.', $ref, $license_id, $saved['expires_at'] ),
			array(
				'license_id' => (int) $license_id,
				'ref'        => (string) $ref,
			),
			'warning'
		);
		return true;
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
			// The stage is marked only when the email went out, so a failed send is retried tomorrow.
			if ( $stage && ! get_option( $flag ) && Renewals::mail( $license, 'trial_reminder' ) ) {
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
