<?php
/**
 * License storage and rules.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Licenses and domain activations.
 */
class Licenses {

	/**
	 * Table names.
	 *
	 * @return array{0: string, 1: string}
	 */
	private static function tables() {
		global $wpdb;
		return array( $wpdb->prefix . 'wpes_licenses', $wpdb->prefix . 'wpes_activations' );
	}

	/**
	 * New random key: WPE-XXXX-XXXX-XXXX-XXXX.
	 *
	 * @return string
	 */
	public static function generate_key() {
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$parts = array();
		for ( $i = 0; $i < 4; $i++ ) {
			$part = '';
			for ( $j = 0; $j < 4; $j++ ) {
				$part .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
			}
			$parts[] = $part;
		}
		return 'WPE-' . implode( '-', $parts );
	}

	/**
	 * Expiry date for a plan starting from a date.
	 *
	 * @param int    $months Months (0 lifetime, -1 free).
	 * @param string $from   MySQL date (UTC) or empty for now.
	 * @return string|null
	 */
	public static function expiry( $months, $from = '' ) {
		if ( $months <= 0 ) {
			return null;
		}
		$base = $from && strtotime( $from ) > time() ? strtotime( $from ) : time();
		return gmdate( 'Y-m-d H:i:s', strtotime( '+' . (int) $months . ' months', $base ) );
	}

	/**
	 * Major version of the current theme release (1 for "1.4.2"), from the last /release upload.
	 *
	 * @return int
	 */
	public static function current_major() {
		return max( 1, (int) get_option( 'wpes_theme_release', '1' ) );
	}

	/**
	 * Lifetime plans cover the major version they were bought for: a v1 lifetime license
	 * works until v2 is released. Other plans are limited by their end date instead (0).
	 *
	 * @param array $plan Plan from Plans::from_product().
	 * @return int
	 */
	public static function lifetime_major( array $plan ) {
		return 0 === (int) $plan['months'] ? self::current_major() : 0;
	}

	/**
	 * Create a license for a plan.
	 *
	 * @param int   $user_id User.
	 * @param array $plan    Plan from Plans::from_product().
	 * @param int   $order_id Order.
	 * @return array License row.
	 */
	public static function create( $user_id, array $plan, $order_id = 0 ) {
		global $wpdb;
		list( $table ) = self::tables();
		do {
			$key = self::generate_key();
		} while ( self::by_key( $key ) );
		$now = current_time( 'mysql', true );
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table.
			$table,
			array(
				'license_key' => $key,
				'user_id'     => (int) $user_id,
				'order_id'    => (int) $order_id,
				'product_id'  => (int) $plan['product_id'],
				'plan'        => $plan['key'],
				'status'      => 'active',
				'sites_limit' => (int) $plan['sites'],
				'white_label' => $plan['white_label'] ? 1 : 0,
				'auto_renew'  => $plan['months'] > 0 ? 1 : 0,
				'expires_at'  => self::expiry( $plan['months'] ),
				'major'       => self::lifetime_major( $plan ),
				'created_at'  => $now,
				'updated_at'  => $now,
			)
		);
		if ( ! $wpdb->insert_id ) {
			Events::error(
				'license.create_failed',
				'License could not be saved: ' . $wpdb->last_error,
				array(
					'user_id'  => (int) $user_id,
					'order_id' => (int) $order_id,
					'plan'     => $plan['key'],
				)
			);
		}
		$license = self::get( (int) $wpdb->insert_id );
		do_action( 'wpes_license_created', $license );
		return $license;
	}

	/**
	 * Update fields.
	 *
	 * @param int   $id     License.
	 * @param array $fields Fields.
	 * @return array|null
	 */
	public static function update( $id, array $fields ) {
		global $wpdb;
		list( $table )        = self::tables();
		$before               = self::get( $id );
		$fields['updated_at'] = current_time( 'mysql', true );
		$wpdb->update( $table, $fields, array( 'id' => (int) $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table.
		$after = self::get( $id );
		Events::changed( $before, $after );
		return $after;
	}

	/**
	 * Get by id.
	 *
	 * @param int $id Id.
	 * @return array|null
	 */
	public static function get( $id ) {
		global $wpdb;
		list( $table ) = self::tables();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table.
	}

	/**
	 * Get by key.
	 *
	 * @param string $key Key.
	 * @return array|null
	 */
	public static function by_key( $key ) {
		global $wpdb;
		list( $table ) = self::tables();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE license_key = %s", strtoupper( trim( $key ) ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table.
	}

	/**
	 * Licenses of a user.
	 *
	 * @param int $user_id User.
	 * @return array
	 */
	public static function for_user( $user_id ) {
		global $wpdb;
		list( $table ) = self::tables();
		return (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d ORDER BY id DESC", $user_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table.
	}

	/**
	 * Search for the admin list.
	 *
	 * @param array $args status, search, page, per_page.
	 * @return array{rows: array, total: int}
	 */
	public static function query( array $args ) {
		global $wpdb;
		list( $table ) = self::tables();
		$where         = '1=1';
		$params        = array();
		if ( ! empty( $args['status'] ) ) {
			$where   .= ' AND l.status = %s';
			$params[] = $args['status'];
		}
		if ( ! empty( $args['search'] ) ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where   .= ' AND ( l.license_key LIKE %s OR u.user_email LIKE %s OR l.id IN ( SELECT license_id FROM ' . $wpdb->prefix . 'wpes_activations WHERE domain LIKE %s ) )';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
		$per    = max( 1, (int) ( $args['per_page'] ?? 20 ) );
		$offset = ( max( 1, (int) ( $args['page'] ?? 1 ) ) - 1 ) * $per;
		$sql    = "FROM {$table} l LEFT JOIN {$wpdb->users} u ON u.ID = l.user_id WHERE {$where}";
		// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders -- Built from placeholders above.
		$total = (int) $wpdb->get_var( $params ? $wpdb->prepare( "SELECT COUNT(*) {$sql}", $params ) : "SELECT COUNT(*) {$sql}" );
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT l.*, u.user_email {$sql} ORDER BY l.id DESC LIMIT %d OFFSET %d", array_merge( $params, array( $per, $offset ) ) ), ARRAY_A );
		// phpcs:enable
		return array(
			'rows'  => (array) $rows,
			'total' => $total,
		);
	}

	/**
	 * Activations of a license.
	 *
	 * @param int $license_id License.
	 * @return array
	 */
	public static function activations( $license_id ) {
		global $wpdb;
		list( , $table ) = self::tables();
		return (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE license_id = %d ORDER BY id", $license_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table.
	}

	/**
	 * Normalise a domain (no scheme, no www, lowercase).
	 *
	 * @param string $domain Domain or URL.
	 * @return string
	 */
	public static function domain( $domain ) {
		$domain = strtolower( trim( (string) $domain ) );
		$host   = wp_parse_url( false === strpos( $domain, '://' ) ? 'http://' . $domain : $domain, PHP_URL_HOST );
		return preg_replace( '/^www\./', '', (string) $host );
	}

	/**
	 * Whether a domain is local or staging (free, not counted).
	 *
	 * @param string $domain Domain.
	 * @return bool
	 */
	public static function is_staging( $domain ) {
		if ( ! Plugin::settings( 'free_staging' ) ) {
			return false;
		}
		$patterns = apply_filters(
			'wpes_staging_patterns',
			array( '/^localhost$/', '/^127\./', '/^10\./', '/^192\.168\./', '/\.(local|test|localhost|invalid|example)$/', '/^(staging|stage|dev|test)\./', '/\.(wpengine|kinsta|flywheelsites|pantheonsite)\.(com|cloud|io)$/', '/\.instawp\.xyz$/' )
		);
		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $domain ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Is the license usable right now (status + expiry + grace)?
	 *
	 * @param array $license License.
	 * @return true|\WP_Error
	 */
	public static function validate( $license ) {
		if ( ! $license ) {
			return new \WP_Error( 'invalid', __( 'This license key does not exist. Copy it again from your account.', 'wpessential-server' ) );
		}
		if ( 'revoked' === $license['status'] ) {
			return new \WP_Error( 'revoked', __( 'This license was revoked after a refund or chargeback.', 'wpessential-server' ) );
		}
		if ( 'cancelled' === $license['status'] ) {
			return new \WP_Error( 'cancelled', __( 'This subscription was cancelled.', 'wpessential-server' ) );
		}
		if ( ! empty( $license['major'] ) && (int) $license['major'] < self::current_major() ) {
			return new \WP_Error(
				'major_ended',
				/* translators: 1: version the license covers, 2: current version. */
				sprintf( __( 'Your lifetime license covers version %1$d.x. Version %2$d is out: buy a new plan in your account to keep Pro demos and updates.', 'wpessential-server' ), (int) $license['major'], self::current_major() )
			);
		}
		if ( 'expired' === $license['status'] || self::is_expired( $license ) ) {
			return new \WP_Error( 'expired', __( 'This license has expired. Renew it in your account to keep Pro demos and updates.', 'wpessential-server' ) );
		}
		return true;
	}

	/**
	 * Past expiry plus grace days.
	 *
	 * @param array $license License.
	 * @return bool
	 */
	public static function is_expired( $license ) {
		if ( empty( $license['expires_at'] ) ) {
			return false;
		}
		return strtotime( $license['expires_at'] . ' UTC' ) + DAY_IN_SECONDS * (int) Plugin::settings( 'grace_days' ) < time();
	}

	/**
	 * Count production (non-staging) activations.
	 *
	 * @param int $license_id License.
	 * @return int
	 */
	public static function used_sites( $license_id ) {
		return count(
			array_filter(
				self::activations( $license_id ),
				static function ( $a ) {
					return ! $a['is_staging'];
				}
			)
		);
	}

	/**
	 * Activate a domain.
	 *
	 * @param string $key    Key.
	 * @param string $domain Domain.
	 * @return array|\WP_Error License.
	 */
	public static function activate( $key, $domain ) {
		global $wpdb;
		$license = self::by_key( $key );
		$valid   = self::validate( $license );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}
		$domain = self::domain( $domain );
		if ( ! $domain ) {
			return new \WP_Error( 'domain', __( 'The site address is missing.', 'wpessential-server' ) );
		}
		list( , $table ) = self::tables();
		$now             = current_time( 'mysql', true );
		foreach ( self::activations( $license['id'] ) as $row ) {
			if ( $row['domain'] === $domain ) {
				$wpdb->update( $table, array( 'last_check' => $now ), array( 'id' => $row['id'] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
				return $license;
			}
		}
		$staging = self::is_staging( $domain );
		if ( ! $staging && (int) $license['sites_limit'] > 0 && self::used_sites( $license['id'] ) >= (int) $license['sites_limit'] ) {
			/* translators: %d: site limit. */
			return new \WP_Error( 'no_activations_left', sprintf( _n( 'This license is already used on %d website. Deactivate it there from your account, or upgrade your plan for more websites.', 'This license is already used on %d websites. Deactivate one from your account, or upgrade your plan for more websites.', (int) $license['sites_limit'], 'wpessential-server' ), (int) $license['sites_limit'] ) );
		}
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table.
			$table,
			array(
				'license_id' => $license['id'],
				'domain'     => $domain,
				'is_staging' => $staging ? 1 : 0,
				'created_at' => $now,
				'last_check' => $now,
			)
		);
		do_action( 'wpes_license_activated', $license, $domain );
		return $license;
	}

	/**
	 * Remove a domain.
	 *
	 * @param int    $license_id License.
	 * @param string $domain     Domain.
	 */
	public static function deactivate( $license_id, $domain ) {
		global $wpdb;
		list( , $table ) = self::tables();
		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
			$table,
			array(
				'license_id' => (int) $license_id,
				'domain'     => self::domain( $domain ),
			)
		);
	}

	/**
	 * Is the domain active on the license?
	 *
	 * @param array  $license License.
	 * @param string $domain  Domain.
	 * @return bool
	 */
	public static function has_domain( $license, $domain ) {
		$domain = self::domain( $domain );
		foreach ( self::activations( $license['id'] ) as $row ) {
			if ( $row['domain'] === $domain ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Public shape returned to client sites.
	 *
	 * @param array $license License.
	 * @return array
	 */
	public static function public_shape( $license ) {
		$plan = Plans::from_product( (int) $license['product_id'] );
		return array(
			'success'     => true,
			'status'      => self::is_expired( $license ) ? 'expired' : $license['status'],
			'expires'     => $license['expires_at'] ? substr( $license['expires_at'], 0, 10 ) : 'lifetime',
			'plan'        => $plan ? $plan['name'] : ucfirst( $license['plan'] ),
			'plan_key'    => $license['plan'],
			'pro'         => $plan ? $plan['pro'] : false,
			'white_label' => (bool) $license['white_label'],
			'sites_limit' => (int) $license['sites_limit'],
			'sites_used'  => self::used_sites( $license['id'] ),
			'auto_renew'  => (bool) $license['auto_renew'],
			'covers'      => empty( $license['major'] ) ? '' : (int) $license['major'] . '.x',
		);
	}

	/**
	 * Shape for the account owner (includes key and domains).
	 *
	 * @param array $license License.
	 * @return array
	 */
	public static function owner_shape( $license ) {
		return array(
			'id'      => (int) $license['id'],
			'key'     => $license['license_key'],
			'domains' => wp_list_pluck( self::activations( $license['id'] ), 'domain' ),
		) + self::public_shape( $license );
	}
}
