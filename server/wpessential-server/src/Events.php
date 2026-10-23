<?php
/**
 * Event log and tracing for licenses, subscriptions, orders, renewals and the REST API.
 *
 * Every event is one row in wpes_events with a trace id shared by everything that happened in
 * the same request or cron run, and is mirrored to the WooCommerce log (source
 * "wpessential-server") so it shows under WooCommerce → Status → Logs too.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Events.
 */
class Events {

	const LEVELS = array( 'info', 'warning', 'error' );

	/**
	 * Trace id of this request.
	 *
	 * @var string
	 */
	private static $trace = '';

	/**
	 * Where this request came from.
	 *
	 * @var string
	 */
	private static $source = '';

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'wpes_license_created', array( __CLASS__, 'on_created' ) );
		add_action( 'wpes_license_activated', array( __CLASS__, 'on_activated' ), 10, 2 );
		add_action( 'wpes_license_action', array( __CLASS__, 'on_action' ), 10, 3 );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'on_order_status' ), 10, 4 );
		add_action( 'woocommerce_payment_complete', array( __CLASS__, 'on_payment_complete' ) );
		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'on_checkout' ), 10, 3 );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'on_rest' ), 10, 3 );
		add_action( 'wp_mail_failed', array( __CLASS__, 'on_mail_failed' ) );
		add_action( Renewals::CRON, array( __CLASS__, 'prune' ), 99 );
	}

	/**
	 * Trace id for this request: the client's X-WPES-Trace header when valid, else a new one.
	 *
	 * @return string
	 */
	public static function trace() {
		if ( '' === self::$trace ) {
			$given       = isset( $_SERVER['HTTP_X_WPES_TRACE'] ) ? sanitize_key( wp_unslash( $_SERVER['HTTP_X_WPES_TRACE'] ) ) : '';
			self::$trace = preg_match( '/^[a-z0-9]{8,32}$/', $given ) ? $given : strtolower( wp_generate_password( 16, false, false ) );
		}
		return self::$trace;
	}

	/**
	 * Start a new trace (each license in a cron run gets its own).
	 *
	 * @param string $source Source name.
	 * @return string
	 */
	public static function begin( $source ) {
		self::$trace  = strtolower( wp_generate_password( 16, false, false ) );
		self::$source = $source;
		return self::$trace;
	}

	/**
	 * Back to the request's own trace and source after a cron run.
	 */
	public static function end() {
		self::$trace  = '';
		self::$source = '';
	}

	/**
	 * Where the current request came from.
	 *
	 * @return string
	 */
	private static function source() {
		if ( self::$source ) {
			return self::$source;
		}
		if ( wp_doing_cron() ) {
			return 'cron';
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return 'api';
		}
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return 'cli';
		}
		return is_admin() ? 'admin' : 'site';
	}

	/**
	 * Record an event.
	 *
	 * @param string $event   Dotted name, e.g. renewal.charge_failed.
	 * @param string $message Human message.
	 * @param array  $context license_id, order_id, user_id, domain and any extra data.
	 * @param string $level   info|warning|error.
	 * @return int Row id (0 when the table is missing).
	 */
	public static function log( $event, $message, array $context = array(), $level = 'info' ) {
		global $wpdb;
		$level = in_array( $level, self::LEVELS, true ) ? $level : 'info';
		$cols  = array(
			'license_id' => (int) ( $context['license_id'] ?? 0 ),
			'order_id'   => (int) ( $context['order_id'] ?? 0 ),
			'user_id'    => (int) ( $context['user_id'] ?? get_current_user_id() ),
			'domain'     => substr( (string) ( $context['domain'] ?? '' ), 0, 191 ),
		);
		$extra = array_diff_key( $context, $cols );
		$row   = array_merge(
			$cols,
			array(
				'created_at' => current_time( 'mysql', true ),
				'level'      => $level,
				'event'      => substr( sanitize_text_field( $event ), 0, 64 ),
				'trace_id'   => self::trace(),
				'source'     => self::source(),
				'message'    => wp_strip_all_tags( (string) $message ),
				'context'    => $extra ? wp_json_encode( $extra, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) : '',
				'ip'         => self::ip(),
			)
		);
		$ok    = $wpdb->insert( $wpdb->prefix . 'wpes_events', $row ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table.
		if ( function_exists( 'wc_get_logger' ) ) {
			wc_get_logger()->log(
				$level,
				sprintf( '[%s] %s: %s', $row['trace_id'], $row['event'], $row['message'] ),
				array_merge(
					array( 'source' => 'wpessential-server' ),
					array_filter( $cols ),
					$extra ? array( 'data' => $extra ) : array()
				)
			);
		}
		return $ok ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Shortcut for errors.
	 *
	 * @param string $event   Event.
	 * @param string $message Message.
	 * @param array  $context Context.
	 * @return int
	 */
	public static function error( $event, $message, array $context = array() ) {
		return self::log( $event, $message, $context, 'error' );
	}

	/**
	 * Visitor IP (the first address, proxies trusted only for display).
	 *
	 * @return string
	 */
	private static function ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return (string) filter_var( $ip, FILTER_VALIDATE_IP );
	}

	/**
	 * Context for a license row.
	 *
	 * @param array $license License.
	 * @param array $extra   Extra.
	 * @return array
	 */
	public static function of( $license, array $extra = array() ) {
		return array_merge(
			array(
				'license_id' => (int) ( $license['id'] ?? 0 ),
				'user_id'    => (int) ( $license['user_id'] ?? 0 ),
				'order_id'   => (int) ( $license['order_id'] ?? 0 ),
			),
			$extra
		);
	}

	/**
	 * Record which fields of a license changed.
	 *
	 * @param array|null $before Row before.
	 * @param array|null $after  Row after.
	 */
	public static function changed( $before, $after ) {
		if ( ! $before || ! $after ) {
			self::error( 'license.update_failed', 'License update did not save.', array( 'license_id' => (int) ( $before['id'] ?? 0 ) ) );
			return;
		}
		$diff = array();
		foreach ( $after as $key => $value ) {
			if ( 'updated_at' !== $key && array_key_exists( $key, $before ) && (string) $before[ $key ] !== (string) $value ) {
				$diff[ $key ] = array( $before[ $key ], $value );
			}
		}
		if ( ! $diff ) {
			return;
		}
		$event = isset( $diff['status'] ) ? 'license.' . $after['status'] : 'license.updated';
		/* translators: 1: key, 2: changed fields. */
		self::log( $event, sprintf( 'License %1$s changed: %2$s.', $after['license_key'], implode( ', ', array_keys( $diff ) ) ), self::of( $after, array( 'changes' => $diff ) ), in_array( $after['status'], array( 'revoked', 'expired' ), true ) ? 'warning' : 'info' );
	}

	// Listeners.

	/**
	 * License created.
	 *
	 * @param array $license License.
	 */
	public static function on_created( $license ) {
		self::log( 'license.created', sprintf( 'License %1$s created on plan %2$s.', $license['license_key'], $license['plan'] ), self::of( $license, array( 'expires_at' => $license['expires_at'] ) ) );
	}

	/**
	 * Domain activated.
	 *
	 * @param array  $license License.
	 * @param string $domain  Domain.
	 */
	public static function on_activated( $license, $domain ) {
		self::log( 'license.site_activated', sprintf( 'License %1$s activated on %2$s.', $license['license_key'], $domain ), self::of( $license, array( 'domain' => $domain ) ) );
	}

	/**
	 * Customer action from My Account or the dashboard.
	 *
	 * @param string $action     Action.
	 * @param int    $license_id License.
	 * @param int    $user_id    User.
	 */
	public static function on_action( $action, $license_id, $user_id ) {
		self::log(
			'subscription.' . sanitize_key( $action ),
			sprintf( 'Customer ran %1$s on license #%2$d.', $action, $license_id ),
			array(
				'license_id' => (int) $license_id,
				'user_id'    => (int) $user_id,
			)
		);
	}

	/**
	 * Plan orders only.
	 *
	 * @param \WC_Order $order Order.
	 * @return bool
	 */
	private static function is_plan_order( $order ) {
		if ( ! $order ) {
			return false;
		}
		foreach ( $order->get_items() as $item ) {
			if ( Plans::from_product( $item->get_product_id() ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Order context, with the renewal's license when there is one.
	 *
	 * @param \WC_Order $order Order.
	 * @param array     $extra Extra.
	 * @return array
	 */
	private static function order_context( $order, array $extra = array() ) {
		return array_merge(
			array(
				'order_id'   => $order->get_id(),
				'user_id'    => $order->get_customer_id(),
				'license_id' => (int) ( $order->get_meta( '_wpes_renewal_of' ) ? $order->get_meta( '_wpes_renewal_of' ) : current( (array) $order->get_meta( '_wpes_licenses' ) ) ),
				'total'      => $order->get_total(),
				'currency'   => $order->get_currency(),
				'gateway'    => $order->get_payment_method(),
				'renewal'    => (bool) $order->get_meta( '_wpes_renewal_of' ),
			),
			$extra
		);
	}

	/**
	 * Order status changes of plan orders. Failed and cancelled payments are errors/warnings.
	 *
	 * @param int       $order_id Order.
	 * @param string    $from     Old status.
	 * @param string    $to       New status.
	 * @param \WC_Order $order    Order.
	 */
	public static function on_order_status( $order_id, $from, $to, $order ) {
		if ( ! self::is_plan_order( $order ) ) {
			return;
		}
		$level = 'failed' === $to ? 'error' : ( in_array( $to, array( 'cancelled', 'refunded', 'on-hold' ), true ) ? 'warning' : 'info' );
		self::log( 'order.' . $to, sprintf( 'Order #%1$d changed from %2$s to %3$s.', $order_id, $from, $to ), self::order_context( $order, array( 'from' => $from ) ), $level );
	}

	/**
	 * Payment received.
	 *
	 * @param int $order_id Order.
	 */
	public static function on_payment_complete( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( self::is_plan_order( $order ) ) {
			self::log( 'payment.complete', sprintf( 'Payment for order #%d received.', $order_id ), self::order_context( $order, array( 'transaction' => $order->get_transaction_id() ) ) );
		}
	}

	/**
	 * Checkout placed.
	 *
	 * @param int       $order_id Order.
	 * @param array     $data     Posted data.
	 * @param \WC_Order $order    Order.
	 */
	public static function on_checkout( $order_id, $data, $order ) {
		if ( self::is_plan_order( $order ) ) {
			self::log( 'checkout.placed', sprintf( 'Checkout placed order #%d.', $order_id ), self::order_context( $order ) );
		}
	}

	/**
	 * Every failed call to this plugin's REST API, with the trace id sent back as a header.
	 *
	 * @param \WP_REST_Response $response Response.
	 * @param \WP_REST_Server   $server   Server.
	 * @param \WP_REST_Request  $request  Request.
	 * @return \WP_REST_Response
	 */
	public static function on_rest( $response, $server, $request ) {
		$route = (string) $request->get_route();
		if ( 0 !== strpos( $route, '/' . Api::NS ) ) {
			return $response;
		}
		$response->header( 'X-WPES-Trace', self::trace() );
		$data   = $response->get_data();
		$status = $response->get_status();
		$failed = $status >= 400 || ( is_array( $data ) && array_key_exists( 'success', $data ) && false === $data['success'] );
		if ( $failed ) {
			$code = is_array( $data ) ? (string) ( $data['status'] ?? $data['code'] ?? 'error' ) : 'error';
			self::log(
				'api.' . sanitize_key( $code ),
				is_array( $data ) ? (string) ( $data['message'] ?? '' ) : '',
				array(
					'route'  => $route,
					'method' => $request->get_method(),
					'http'   => $status,
					'domain' => (string) $request->get_param( 'domain' ),
				),
				$status >= 500 ? 'error' : 'warning'
			);
		}
		return $response;
	}

	/**
	 * Mail errors.
	 *
	 * @param \WP_Error $error Error.
	 */
	public static function on_mail_failed( $error ) {
		$data = (array) $error->get_error_data();
		self::error( 'mail.failed', $error->get_error_message(), array( 'to' => $data['to'] ?? array() ) );
	}

	/**
	 * Delete events older than the retention setting.
	 */
	public static function prune() {
		global $wpdb;
		$days = max( 30, (int) Plugin::settings( 'event_days' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wpes_events WHERE created_at < %s", gmdate( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Custom table.
	}

	/**
	 * Query events.
	 *
	 * @param array $args level, event, license_id, order_id, trace_id, search, page, per_page.
	 * @return array rows, total
	 */
	public static function query( array $args ) {
		global $wpdb;
		$where  = array( '1 = %d' );
		$params = array( 1 );
		foreach ( array( 'level', 'trace_id' ) as $key ) {
			if ( ! empty( $args[ $key ] ) ) {
				$where[]  = "{$key} = %s";
				$params[] = (string) $args[ $key ];
			}
		}
		foreach ( array( 'license_id', 'order_id', 'user_id' ) as $key ) {
			if ( ! empty( $args[ $key ] ) ) {
				$where[]  = "{$key} = %d";
				$params[] = (int) $args[ $key ];
			}
		}
		if ( ! empty( $args['event'] ) ) {
			$where[]  = 'event LIKE %s';
			$params[] = $wpdb->esc_like( (string) $args['event'] ) . '%';
		}
		if ( ! empty( $args['search'] ) ) {
			$like     = '%' . $wpdb->esc_like( (string) $args['search'] ) . '%';
			$where[]  = '(message LIKE %s OR domain LIKE %s OR context LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
		$per   = max( 1, min( 200, (int) ( $args['per_page'] ?? 50 ) ) );
		$page  = max( 1, (int) ( $args['page'] ?? 1 ) );
		$table = $wpdb->prefix . 'wpes_events';
		$sql   = implode( ' AND ', $where );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Built from fixed fragments above.
		$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$sql}", $params ) );
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$sql} ORDER BY id DESC LIMIT %d OFFSET %d", array_merge( $params, array( $per, ( $page - 1 ) * $per ) ) ), ARRAY_A );
		// phpcs:enable
		return array(
			'rows'  => (array) $rows,
			'total' => $total,
		);
	}

	/**
	 * Events table markup.
	 *
	 * @param array $rows Rows.
	 */
	public static function table( array $rows ) {
		echo '<table class="widefat striped wpes-events"><thead><tr><th>' . esc_html__( 'Time (UTC)', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Level', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Event', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Message', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Links', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Trace', 'wpessential-server' ) . '</th></tr></thead><tbody>';
		if ( ! $rows ) {
			echo '<tr><td colspan="6">' . esc_html__( 'No events yet.', 'wpessential-server' ) . '</td></tr>';
		}
		$colors = array(
			'info'    => '#2271b1',
			'warning' => '#b26b00',
			'error'   => '#c2362b',
		);
		$base   = admin_url( 'admin.php?page=wpes-events' );
		foreach ( $rows as $r ) {
			$links = array();
			if ( $r['license_id'] ) {
				$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wpes-server&license=' . (int) $r['license_id'] ) ) . '">' . esc_html( sprintf( /* translators: %d: id. */ __( 'License #%d', 'wpessential-server' ), $r['license_id'] ) ) . '</a>';
			}
			if ( $r['order_id'] ) {
				$links[] = '<a href="' . esc_url( admin_url( 'post.php?post=' . (int) $r['order_id'] . '&action=edit' ) ) . '">' . esc_html( sprintf( /* translators: %d: order. */ __( 'Order #%d', 'wpessential-server' ), $r['order_id'] ) ) . '</a>';
			}
			if ( $r['domain'] ) {
				$links[] = esc_html( $r['domain'] );
			}
			echo '<tr><td>' . esc_html( $r['created_at'] ) . '<br><small>' . esc_html( $r['source'] . ( $r['ip'] ? ' · ' . $r['ip'] : '' ) ) . '</small></td>';
			echo '<td><strong style="color:' . esc_attr( $colors[ $r['level'] ] ?? '#2271b1' ) . '">' . esc_html( ucfirst( $r['level'] ) ) . '</strong></td>';
			echo '<td><code>' . esc_html( $r['event'] ) . '</code></td><td>' . esc_html( $r['message'] );
			if ( $r['context'] ) {
				echo '<details><summary>' . esc_html__( 'Details', 'wpessential-server' ) . '</summary><pre style="white-space:pre-wrap;margin:6px 0 0">' . esc_html( (string) wp_json_encode( json_decode( $r['context'], true ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) . '</pre></details>';
			}
			echo '</td><td>' . implode( '<br>', $links ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
			echo '<td><a href="' . esc_url( add_query_arg( 'trace_id', $r['trace_id'], $base ) ) . '"><code>' . esc_html( $r['trace_id'] ) . '</code></a></td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Admin screen: WPE Server → Events.
	 */
	public static function screen() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only filters.
		$args = array(
			'level'      => isset( $_GET['level'] ) ? sanitize_key( wp_unslash( $_GET['level'] ) ) : '',
			'event'      => isset( $_GET['event'] ) ? sanitize_text_field( wp_unslash( $_GET['event'] ) ) : '',
			'trace_id'   => isset( $_GET['trace_id'] ) ? sanitize_key( wp_unslash( $_GET['trace_id'] ) ) : '',
			'license_id' => isset( $_GET['license_id'] ) ? absint( $_GET['license_id'] ) : 0,
			'order_id'   => isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0,
			'search'     => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
			'page'       => isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1,
			'per_page'   => 50,
		);
		// phpcs:enable
		$result = self::query( $args );
		echo '<div class="wrap"><h1>' . esc_html__( 'Events', 'wpessential-server' ) . '</h1>';
		echo '<p>' . esc_html__( 'Everything that happens to licenses, subscriptions, orders, payments, renewals and the API. One trace id links every step of the same request or renewal run. Copies also go to WooCommerce → Status → Logs (source: wpessential-server).', 'wpessential-server' ) . '</p>';
		echo '<form method="get"><input type="hidden" name="page" value="wpes-events"><p class="search-box" style="float:none;display:flex;gap:6px;flex-wrap:wrap"><select name="level"><option value="">' . esc_html__( 'All levels', 'wpessential-server' ) . '</option>';
		foreach ( self::LEVELS as $l ) {
			echo '<option value="' . esc_attr( $l ) . '"' . selected( $args['level'], $l, false ) . '>' . esc_html( ucfirst( $l ) ) . '</option>';
		}
		echo '</select><input name="event" value="' . esc_attr( $args['event'] ) . '" placeholder="' . esc_attr__( 'Event, e.g. renewal.', 'wpessential-server' ) . '">';
		echo '<input type="number" name="license_id" value="' . esc_attr( $args['license_id'] ? $args['license_id'] : '' ) . '" placeholder="' . esc_attr__( 'License #', 'wpessential-server' ) . '" style="width:110px">';
		echo '<input type="number" name="order_id" value="' . esc_attr( $args['order_id'] ? $args['order_id'] : '' ) . '" placeholder="' . esc_attr__( 'Order #', 'wpessential-server' ) . '" style="width:110px">';
		echo '<input name="trace_id" value="' . esc_attr( $args['trace_id'] ) . '" placeholder="' . esc_attr__( 'Trace id', 'wpessential-server' ) . '">';
		echo '<input type="search" name="s" value="' . esc_attr( $args['search'] ) . '" placeholder="' . esc_attr__( 'Message, domain or data', 'wpessential-server' ) . '"> <button class="button">' . esc_html__( 'Filter', 'wpessential-server' ) . '</button></p></form>';
		self::table( $result['rows'] );
		$pages = (int) ceil( $result['total'] / $args['per_page'] );
		if ( $pages > 1 ) {
			echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(
				paginate_links(
					array(
						'base'    => add_query_arg( 'paged', '%#%' ),
						'current' => $args['page'],
						'total'   => $pages,
					)
				)
			) . '</div></div>';
		}
		echo '</div>';
	}
}
