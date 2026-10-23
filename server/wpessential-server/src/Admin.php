<?php
/**
 * Admin: licenses, settings.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Admin screens.
 */
class Admin {

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_wpes_license_save', array( __CLASS__, 'save_license' ) );
		add_action( 'admin_post_wpes_settings', array( __CLASS__, 'save_settings' ) );
		add_action( 'admin_post_wpes_run_cron', array( __CLASS__, 'run_cron' ) );
		add_action( 'admin_post_wpes_release', array( __CLASS__, 'save_release' ) );
		add_action( 'admin_post_wpes_license_add', array( __CLASS__, 'add_license' ) );
		add_action( 'admin_post_wpes_release_pull', array( __CLASS__, 'pull_release' ) );
	}

	/**
	 * Menu.
	 */
	public static function menu() {
		add_menu_page( __( 'WPEssential Server', 'wpessential-server' ), __( 'WPE Server', 'wpessential-server' ), 'manage_woocommerce', 'wpes-server', array( __CLASS__, 'licenses' ), 'dashicons-admin-network', 56 );
		add_submenu_page( 'wpes-server', __( 'Licenses', 'wpessential-server' ), __( 'Licenses', 'wpessential-server' ), 'manage_woocommerce', 'wpes-server', array( __CLASS__, 'licenses' ) );
		add_submenu_page( 'wpes-server', __( 'Add license', 'wpessential-server' ), __( 'Add license', 'wpessential-server' ), 'manage_woocommerce', 'wpes-license-new', array( __CLASS__, 'new_license' ) );
		add_submenu_page( 'wpes-server', __( 'Events', 'wpessential-server' ), __( 'Events', 'wpessential-server' ), 'manage_woocommerce', 'wpes-events', array( Events::class, 'screen' ) );
		add_submenu_page( 'wpes-server', __( 'Upload release', 'wpessential-server' ), __( 'Upload release', 'wpessential-server' ), 'manage_options', 'wpes-release', array( __CLASS__, 'release' ) );
		add_submenu_page( 'wpes-server', __( 'Settings', 'wpessential-server' ), __( 'Settings', 'wpessential-server' ), 'manage_options', 'wpes-settings', array( __CLASS__, 'settings' ) );
	}

	/**
	 * Licenses list / edit.
	 */
	public static function licenses() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only filters.
		if ( ! empty( $_GET['license'] ) ) {
			self::edit( absint( $_GET['license'] ) );
			return;
		}
		$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$page   = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		// phpcs:enable
		$result = Licenses::query(
			array(
				'status'   => $status,
				'search'   => $search,
				'page'     => $page,
				'per_page' => 25,
			)
		);
		echo '<div class="wrap"><h1 class="wp-heading-inline">' . esc_html__( 'Licenses', 'wpessential-server' ) . '</h1>';
		echo '<a class="page-title-action" href="' . esc_url( admin_url( 'admin.php?page=wpes-license-new' ) ) . '">' . esc_html__( 'Add license', 'wpessential-server' ) . '</a>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline">' . wp_nonce_field( 'wpes_run_cron', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="wpes_run_cron"><button class="page-title-action">' . esc_html__( 'Run renewals now', 'wpessential-server' ) . '</button></form>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Nonce markup.
		echo '<hr class="wp-header-end">';
		echo '<form method="get"><input type="hidden" name="page" value="wpes-server"><p class="search-box"><select name="status"><option value="">' . esc_html__( 'All statuses', 'wpessential-server' ) . '</option>';
		foreach ( array( 'active', 'expired', 'cancelled', 'revoked' ) as $s ) {
			echo '<option value="' . esc_attr( $s ) . '"' . selected( $status, $s, false ) . '>' . esc_html( ucfirst( $s ) ) . '</option>';
		}
		echo '</select> <input type="search" name="s" value="' . esc_attr( $search ) . '" placeholder="' . esc_attr__( 'Key, email or domain', 'wpessential-server' ) . '"> <button class="button">' . esc_html__( 'Filter', 'wpessential-server' ) . '</button></p></form>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Key', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Customer', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Plan', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Status', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Websites', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Expires', 'wpessential-server' ) . '</th><th>' . esc_html__( 'Auto-renew', 'wpessential-server' ) . '</th></tr></thead><tbody>';
		if ( ! $result['rows'] ) {
			echo '<tr><td colspan="7">' . esc_html__( 'No licenses found.', 'wpessential-server' ) . '</td></tr>';
		}
		foreach ( $result['rows'] as $row ) {
			$url = add_query_arg(
				array(
					'page'    => 'wpes-server',
					'license' => $row['id'],
				),
				admin_url( 'admin.php' )
			);
			printf(
				'<tr><td><a href="%1$s"><code>%2$s</code></a></td><td>%3$s</td><td>%4$s</td><td>%5$s</td><td>%6$s</td><td>%7$s</td><td>%8$s</td></tr>',
				esc_url( $url ),
				esc_html( $row['license_key'] ),
				esc_html( (string) $row['user_email'] ),
				esc_html( ucwords( str_replace( '_', ' ', $row['plan'] ) ) ),
				esc_html( ucfirst( $row['status'] ) ),
				esc_html( Licenses::used_sites( $row['id'] ) . ' / ' . ( $row['sites_limit'] ? $row['sites_limit'] : '∞' ) ),
				esc_html( $row['expires_at'] ? substr( $row['expires_at'], 0, 10 ) : __( 'Never', 'wpessential-server' ) ),
				esc_html( $row['auto_renew'] ? __( 'On', 'wpessential-server' ) : __( 'Off', 'wpessential-server' ) )
			);
		}
		echo '</tbody></table>';
		$pages = (int) ceil( $result['total'] / 25 );
		if ( $pages > 1 ) {
			echo '<p>' . wp_kses_post(
				paginate_links(
					array(
						'base'    => add_query_arg( 'paged', '%#%' ),
						'current' => $page,
						'total'   => $pages,
					)
				)
			) . '</p>';
		}
		echo '</div>';
	}

	/**
	 * Edit one license.
	 *
	 * @param int $id License.
	 */
	private static function edit( $id ) {
		$license = Licenses::get( $id );
		if ( ! $license ) {
			echo '<div class="wrap"><p>' . esc_html__( 'License not found.', 'wpessential-server' ) . '</p></div>';
			return;
		}
		$user = get_userdata( (int) $license['user_id'] );
		echo '<div class="wrap"><h1><code>' . esc_html( $license['license_key'] ) . '</code></h1>';
		echo '<p>' . esc_html( $user ? $user->display_name . ' · ' . $user->user_email : '' ) . ( $license['order_id'] ? ' · <a href="' . esc_url( admin_url( 'post.php?post=' . (int) $license['order_id'] . '&action=edit' ) ) . '">' . esc_html( sprintf( /* translators: %d: order. */ __( 'Order #%d', 'wpessential-server' ), $license['order_id'] ) ) . '</a>' : '' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . wp_nonce_field( 'wpes_license_save', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="wpes_license_save"><input type="hidden" name="id" value="' . (int) $id . '"><table class="form-table">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Nonce markup.
		echo '<tr><th>' . esc_html__( 'Plan', 'wpessential-server' ) . '</th><td>' . self::plan_select( $license['plan'] ) . '<p class="description">' . esc_html__( 'Decides Pro demo access. Websites, expiry and white label below stay as you set them.', 'wpessential-server' ) . '</p></td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in plan_select().
		echo '<tr><th>' . esc_html__( 'Status', 'wpessential-server' ) . '</th><td><select name="status">';
		foreach ( array( 'active', 'expired', 'cancelled', 'revoked' ) as $s ) {
			echo '<option value="' . esc_attr( $s ) . '"' . selected( $license['status'], $s, false ) . '>' . esc_html( ucfirst( $s ) ) . '</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><th>' . esc_html__( 'Expires (UTC)', 'wpessential-server' ) . '</th><td><input type="date" name="expires" value="' . esc_attr( $license['expires_at'] ? substr( $license['expires_at'], 0, 10 ) : '' ) . '"> <span class="description">' . esc_html__( 'Empty = never.', 'wpessential-server' ) . '</span></td></tr>';
		echo '<tr><th>' . esc_html__( 'Websites', 'wpessential-server' ) . '</th><td><input type="number" min="0" name="sites" value="' . (int) $license['sites_limit'] . '"> <span class="description">' . esc_html__( '0 = unlimited.', 'wpessential-server' ) . '</span></td></tr>';
		echo '<tr><th>' . esc_html__( 'Auto-renew', 'wpessential-server' ) . '</th><td><label><input type="checkbox" name="auto_renew" value="1"' . checked( $license['auto_renew'], 1, false ) . '> ' . esc_html__( 'On', 'wpessential-server' ) . '</label></td></tr>';
		echo '<tr><th>' . esc_html__( 'White label', 'wpessential-server' ) . '</th><td><label><input type="checkbox" name="white_label" value="1"' . checked( $license['white_label'], 1, false ) . '> ' . esc_html__( 'On', 'wpessential-server' ) . '</label></td></tr>';
		echo '<tr><th>' . esc_html__( 'Activated websites', 'wpessential-server' ) . '</th><td>';
		$activations = Licenses::activations( $id );
		if ( ! $activations ) {
			esc_html_e( 'None yet.', 'wpessential-server' );
		}
		foreach ( $activations as $a ) {
			echo '<label style="display:block"><input type="checkbox" name="remove[]" value="' . esc_attr( $a['domain'] ) . '"> ' . esc_html__( 'Remove', 'wpessential-server' ) . ' <strong>' . esc_html( $a['domain'] ) . '</strong>' . ( $a['is_staging'] ? ' (' . esc_html__( 'staging', 'wpessential-server' ) . ')' : '' ) . ' · ' . esc_html( sprintf( /* translators: %s: date. */ __( 'last seen %s', 'wpessential-server' ), $a['last_check'] ) ) . '</label>';
		}
		echo '</td></tr></table>';
		submit_button();
		echo '</form>';
		echo '<h2>' . esc_html__( 'Timeline', 'wpessential-server' ) . ' <a class="page-title-action" href="' . esc_url( admin_url( 'admin.php?page=wpes-events&license_id=' . (int) $id ) ) . '">' . esc_html__( 'All events', 'wpessential-server' ) . '</a></h2>';
		Events::table(
			Events::query(
				array(
					'license_id' => $id,
					'per_page'   => 30,
				)
			)['rows']
		);
		echo '</div>';
	}

	/**
	 * Save license edits.
	 */
	public static function save_license() {
		check_admin_referer( 'wpes_license_save' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'wpessential-server' ) );
		}
		$id      = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$status  = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'active';
		$expires = isset( $_POST['expires'] ) ? sanitize_text_field( wp_unslash( $_POST['expires'] ) ) : '';
		$plan    = self::plan_by_key( isset( $_POST['plan'] ) ? sanitize_key( wp_unslash( $_POST['plan'] ) ) : '' );
		Licenses::update(
			$id,
			array_filter(
				array(
					'plan'       => $plan ? $plan['key'] : null,
					'product_id' => $plan ? (int) $plan['product_id'] : null,
				),
				'is_scalar'
			)
		);
		Licenses::update(
			$id,
			array(
				'status'      => in_array( $status, array( 'active', 'expired', 'cancelled', 'revoked' ), true ) ? $status : 'active',
				'expires_at'  => $expires ? gmdate( 'Y-m-d 23:59:59', strtotime( $expires ) ) : null,
				'sites_limit' => isset( $_POST['sites'] ) ? max( 0, (int) $_POST['sites'] ) : 1,
				'auto_renew'  => empty( $_POST['auto_renew'] ) ? 0 : 1,
				'white_label' => empty( $_POST['white_label'] ) ? 0 : 1,
			)
		);
		foreach ( isset( $_POST['remove'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['remove'] ) ) : array() as $domain ) {
			Licenses::deactivate( $id, $domain );
		}
		wp_safe_redirect( add_query_arg( array( 'page' => 'wpes-server', 'license' => $id, 'updated' => 1 ), admin_url( 'admin.php' ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound -- Short redirect args.
		exit;
	}

	/**
	 * Plan dropdown.
	 *
	 * @param string $current Selected plan key.
	 * @return string
	 */
	private static function plan_select( $current = '' ) {
		$out = '<select name="plan">';
		foreach ( Plans::all() as $plan ) {
			$out .= '<option value="' . esc_attr( $plan['key'] ) . '"' . selected( $current, $plan['key'], false ) . '>' . esc_html( $plan['name'] ) . '</option>';
		}
		return $out . '</select>';
	}

	/**
	 * Plan by key.
	 *
	 * @param string $key Plan key.
	 * @return array|null
	 */
	private static function plan_by_key( $key ) {
		$id = $key ? Plans::product_id( $key ) : 0;
		return $id ? Plans::from_product( $id ) : null;
	}

	/**
	 * Add license: give any user any plan with a custom website limit and end date.
	 */
	public static function new_license() {
		echo '<div class="wrap"><h1>' . esc_html__( 'Add license', 'wpessential-server' ) . '</h1>';
		echo '<p>' . esc_html__( 'Give a customer a license without an order: a free upgrade, a partner deal or extra websites. Leave a field empty to use the plan default.', 'wpessential-server' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . wp_nonce_field( 'wpes_license_add', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="wpes_license_add"><table class="form-table">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Nonce markup.
		echo '<tr><th>' . esc_html__( 'User', 'wpessential-server' ) . '</th><td><input type="text" class="regular-text" name="user" required placeholder="' . esc_attr__( 'Email or username', 'wpessential-server' ) . '"><p class="description">' . esc_html__( 'A new customer account is created for an email that has none.', 'wpessential-server' ) . '</p></td></tr>';
		echo '<tr><th>' . esc_html__( 'Plan', 'wpessential-server' ) . '</th><td>' . self::plan_select( 'pro_yearly' ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in plan_select().
		echo '<tr><th>' . esc_html__( 'Websites', 'wpessential-server' ) . '</th><td><input type="number" min="0" name="sites"> <span class="description">' . esc_html__( 'Empty = plan default, 0 = unlimited.', 'wpessential-server' ) . '</span></td></tr>';
		echo '<tr><th>' . esc_html__( 'Expires (UTC)', 'wpessential-server' ) . '</th><td><input type="date" name="expires"> <label><input type="checkbox" name="never" value="1"> ' . esc_html__( 'Never', 'wpessential-server' ) . '</label> <span class="description">' . esc_html__( 'Empty = plan default.', 'wpessential-server' ) . '</span></td></tr>';
		echo '<tr><th>' . esc_html__( 'White label', 'wpessential-server' ) . '</th><td><select name="white"><option value="">' . esc_html__( 'Plan default', 'wpessential-server' ) . '</option><option value="1">' . esc_html__( 'On', 'wpessential-server' ) . '</option><option value="0">' . esc_html__( 'Off', 'wpessential-server' ) . '</option></select></td></tr>';
		echo '<tr><th>' . esc_html__( 'Copies', 'wpessential-server' ) . '</th><td><input type="number" min="1" max="50" name="count" value="1"> <span class="description">' . esc_html__( 'How many license keys to create for this user.', 'wpessential-server' ) . '</span></td></tr>';
		echo '</table>';
		submit_button( __( 'Create license', 'wpessential-server' ) );
		echo '</form></div>';
	}

	/**
	 * Create licenses from the Add license form.
	 */
	public static function add_license() {
		check_admin_referer( 'wpes_license_add' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'wpessential-server' ) );
		}
		$who  = isset( $_POST['user'] ) ? sanitize_text_field( wp_unslash( $_POST['user'] ) ) : '';
		$user = is_email( $who ) ? get_user_by( 'email', $who ) : get_user_by( 'login', $who );
		if ( ! $user && is_email( $who ) ) {
			$uid  = wc_create_new_customer( $who );
			$user = is_wp_error( $uid ) ? null : get_userdata( $uid );
		}
		$plan = self::plan_by_key( isset( $_POST['plan'] ) ? sanitize_key( wp_unslash( $_POST['plan'] ) ) : '' );
		if ( ! $user || ! $plan ) {
			wp_die( esc_html__( 'Unknown user or plan.', 'wpessential-server' ), '', array( 'back_link' => true ) );
		}
		$fields = array();
		if ( isset( $_POST['sites'] ) && '' !== $_POST['sites'] ) {
			$fields['sites_limit'] = max( 0, (int) $_POST['sites'] );
		}
		if ( ! empty( $_POST['never'] ) ) {
			$fields['expires_at'] = null;
			$fields['auto_renew'] = 0;
		} elseif ( ! empty( $_POST['expires'] ) ) {
			$fields['expires_at'] = gmdate( 'Y-m-d 23:59:59', strtotime( sanitize_text_field( wp_unslash( $_POST['expires'] ) ) ) );
			$fields['auto_renew'] = 0;
		}
		if ( isset( $_POST['white'] ) && '' !== $_POST['white'] ) {
			$fields['white_label'] = (int) $_POST['white'] ? 1 : 0;
		}
		$count = isset( $_POST['count'] ) ? min( 50, max( 1, (int) $_POST['count'] ) ) : 1;
		$last  = 0;
		for ( $i = 0; $i < $count; $i++ ) {
			$license = Licenses::create( $user->ID, $plan );
			if ( $license && $fields ) {
				Licenses::update( (int) $license['id'], $fields );
			}
			$last = $license ? (int) $license['id'] : $last;
		}
		wp_safe_redirect( 1 === $count && $last ? add_query_arg( array( 'page' => 'wpes-server', 'license' => $last ), admin_url( 'admin.php' ) ) : add_query_arg( array( 'page' => 'wpes-server', 's' => $user->user_email ), admin_url( 'admin.php' ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound -- Short redirect args.
		exit;
	}

	/**
	 * Settings screen.
	 */
	public static function settings() {
		$s = Plugin::settings();
		echo '<div class="wrap"><h1>' . esc_html__( 'WPEssential Server settings', 'wpessential-server' ) . '</h1>';
		echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . wp_nonce_field( 'wpes_settings', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="wpes_settings"><table class="form-table">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Nonce markup.
		$rows = array(
			'grace_days'    => array( __( 'Grace period (days)', 'wpessential-server' ), __( 'Licenses keep working this long after the end date while a renewal is paid.', 'wpessential-server' ) ),
			'remind_days'   => array( __( 'Renew / remind before (days)', 'wpessential-server' ), __( 'Renewal orders are created (or reminders sent) this many days before the end date.', 'wpessential-server' ) ),
			'token_days'    => array( __( 'Dashboard sign-in lasts (days)', 'wpessential-server' ), '' ),
			'download_mins' => array( __( 'Download links last (minutes)', 'wpessential-server' ), '' ),
			'event_days'    => array( __( 'Keep events (days)', 'wpessential-server' ), __( 'Older events are deleted by the daily job (minimum 30).', 'wpessential-server' ) ),
		);
		foreach ( $rows as $key => $row ) {
			echo '<tr><th>' . esc_html( $row[0] ) . '</th><td><input type="number" min="1" name="' . esc_attr( $key ) . '" value="' . (int) $s[ $key ] . '">' . ( $row[1] ? '<p class="description">' . esc_html( $row[1] ) . '</p>' : '' ) . '</td></tr>';
		}
		echo '<tr><th>' . esc_html__( 'Free staging sites', 'wpessential-server' ) . '</th><td><label><input type="checkbox" name="free_staging" value="1"' . checked( $s['free_staging'], 1, false ) . '> ' . esc_html__( 'Do not count localhost, *.local, *.test and staging.* domains against the website limit.', 'wpessential-server' ) . '</label></td></tr>';
		echo '<tr><th>' . esc_html__( 'Live demos', 'wpessential-server' ) . '</th><td><label><input type="checkbox" name="playground" value="1"' . checked( $s['playground'], 1, false ) . '> ' . esc_html__( 'Open demos in WordPress Playground (runs in the visitor\'s browser, no demo sites to host).', 'wpessential-server' ) . '</label>';
		echo '<p class="description">' . ( is_readable( Playground::theme_path() ) ? esc_html( sprintf( /* translators: %s: size. */ __( 'Theme zip uploaded (%s). Upload a new one after each release:', 'wpessential-server' ), size_format( filesize( Playground::theme_path() ) ) ) ) : esc_html__( 'Upload the theme zip to enable live demos:', 'wpessential-server' ) ) . '</p><p><input type="file" name="wpes_theme_zip" accept=".zip"></p></td></tr>';
		echo '<tr><th>' . esc_html__( 'Theme endpoint', 'wpessential-server' ) . '</th><td><code>' . esc_html( rest_url( Api::NS . '/license' ) ) . '</code><p class="description">' . esc_html__( 'Use this as the license endpoint and the demos manifest URL below in the theme.', 'wpessential-server' ) . '</p><code>' . esc_html( rest_url( Api::NS . '/demos' ) ) . '</code></td></tr>';
		echo '</table>';
		submit_button();
		echo '</form></div>';
	}

	/**
	 * Upload release: the same job as the CI release route, for sites where a firewall
	 * (e.g. Cloudflare Bot Fight Mode) blocks GitHub. Takes the zips from a "Build zips" run
	 * as they download: hi-wpessential.zip, <demo>-<builder>.zip, or hi-wpessential-demos.zip
	 * holding the demo packages.
	 */
	public static function release() {
		echo '<div class="wrap"><h1>' . esc_html__( 'Upload release', 'wpessential-server' ) . '</h1>';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only notice.
		$done = isset( $_GET['done'] ) ? sanitize_text_field( wp_unslash( $_GET['done'] ) ) : '';
		if ( '' !== $done ) {
			echo '<div class="notice notice-success"><p>' . esc_html( sprintf( /* translators: %s: uploaded packages. */ __( 'Uploaded: %s', 'wpessential-server' ), $done ) ) . '</p></div>';
		}
		echo '<p>' . esc_html__( 'Download the artifacts of a "Build zips" run on GitHub and upload them here. Theme zip, demo packages and the demos bundle are recognised by file name.', 'wpessential-server' ) . '</p>';
		echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . wp_nonce_field( 'wpes_release', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="wpes_release"><table class="form-table">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Nonce markup.
		echo '<tr><th>' . esc_html__( 'Zip files', 'wpessential-server' ) . '</th><td><input type="file" name="wpes_zips[]" accept=".zip" multiple required></td></tr>';
		echo '<tr><th>' . esc_html__( 'Version', 'wpessential-server' ) . '</th><td><input type="text" name="version" value="' . esc_attr( (string) get_option( 'wpes_theme_release', '1.0.0' ) ) . '"></td></tr>';
		echo '</table>';
		submit_button( __( 'Upload', 'wpessential-server' ) );
		echo '</form>';
		echo '<h2>' . esc_html__( 'Pull from GitHub', 'wpessential-server' ) . '</h2><p>' . esc_html__( 'This site fetches the zips of the latest successful "Build zips" run itself, so a firewall in front of the site does not matter. The token needs read access to Actions on the repository.', 'wpessential-server' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">' . wp_nonce_field( 'wpes_release_pull', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="wpes_release_pull"><table class="form-table">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Nonce markup.
		echo '<tr><th>' . esc_html__( 'Repository', 'wpessential-server' ) . '</th><td><input type="text" class="regular-text" name="repo" value="' . esc_attr( (string) get_option( 'wpes_github_repo', 'Vertex-Systems-Network/hi-wpessential' ) ) . '"></td></tr>';
		echo '<tr><th>' . esc_html__( 'GitHub token', 'wpessential-server' ) . '</th><td><input type="password" class="regular-text" name="token" autocomplete="off" placeholder="' . esc_attr( get_option( 'wpes_github_token' ) ? __( 'Saved. Leave empty to keep it.', 'wpessential-server' ) : 'github_pat_…' ) . '"></td></tr>';
		echo '</table>';
		submit_button( __( 'Pull latest build', 'wpessential-server' ) );
		echo '</form></div>';
	}

	/**
	 * Save an uploaded release.
	 */
	public static function save_release() {
		check_admin_referer( 'wpes_release' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'wpessential-server' ) );
		}
		$done = array();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- File array, checked below.
		$files = isset( $_FILES['wpes_zips']['tmp_name'] ) ? (array) $_FILES['wpes_zips'] : array();
		foreach ( (array) ( $files['tmp_name'] ?? array() ) as $i => $tmp ) {
			if ( $tmp && is_uploaded_file( $tmp ) ) {
				$done = array_merge( $done, self::place_zip( $tmp, sanitize_file_name( (string) $files['name'][ $i ] ), true ) );
			}
		}
		if ( ! empty( $_POST['version'] ) ) {
			update_option( 'wpes_theme_release', sanitize_text_field( wp_unslash( $_POST['version'] ) ), false );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=wpes-release&done=' . rawurlencode( $done ? implode( ', ', $done ) : __( 'nothing recognised', 'wpessential-server' ) ) ) );
		exit;
	}

	/**
	 * Fetch the artifacts of the latest successful "Build zips" run on main.
	 */
	public static function pull_release() {
		check_admin_referer( 'wpes_release_pull' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'wpessential-server' ) );
		}
		$repo = isset( $_POST['repo'] ) ? preg_replace( '#[^A-Za-z0-9_.\-/]#', '', sanitize_text_field( wp_unslash( $_POST['repo'] ) ) ) : '';
		update_option( 'wpes_github_repo', $repo, false );
		if ( ! empty( $_POST['token'] ) ) {
			update_option( 'wpes_github_token', sanitize_text_field( wp_unslash( $_POST['token'] ) ), false );
		}
		$done = self::pull( $repo, (string) get_option( 'wpes_github_token' ) );
		$note = is_wp_error( $done ) ? $done->get_error_message() : ( $done ? implode( ', ', $done ) : __( 'nothing recognised', 'wpessential-server' ) );
		wp_safe_redirect( admin_url( 'admin.php?page=wpes-release&done=' . rawurlencode( $note ) ) );
		exit;
	}

	/**
	 * Download and store the latest build's artifacts.
	 *
	 * @param string $repo  owner/name.
	 * @param string $token GitHub token.
	 * @return string[]|\WP_Error Stored package names.
	 */
	private static function pull( $repo, $token ) {
		$api  = 'https://api.github.com/repos/' . $repo;
		$head = array(
			'Accept'               => 'application/vnd.github+json',
			'Authorization'        => 'Bearer ' . $token,
			'X-GitHub-Api-Version' => '2022-11-28',
		);
		$runs = self::github_json( $api . '/actions/workflows/build.yml/runs?branch=main&status=success&per_page=1', $head );
		if ( is_wp_error( $runs ) ) {
			return $runs;
		}
		if ( empty( $runs['workflow_runs'][0]['id'] ) ) {
			return new \WP_Error( 'no_run', __( 'No successful "Build zips" run on main yet.', 'wpessential-server' ) );
		}
		$list = self::github_json( $api . '/actions/runs/' . (int) $runs['workflow_runs'][0]['id'] . '/artifacts', $head );
		if ( is_wp_error( $list ) ) {
			return $list;
		}
		$done = array();
		foreach ( (array) ( $list['artifacts'] ?? array() ) as $artifact ) {
			if ( 'wpessential-server.zip' === $artifact['name'] || ! empty( $artifact['expired'] ) ) {
				continue;
			}
			// GitHub answers with a redirect to signed storage, which must not get the token.
			$res = wp_remote_get( $artifact['archive_download_url'], array( 'headers' => $head, 'redirection' => 0, 'timeout' => 30 ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound -- Short args.
			$url = wp_remote_retrieve_header( $res, 'location' );
			$tmp = $url ? download_url( $url, 300 ) : new \WP_Error( 'download', __( 'GitHub did not return a download link.', 'wpessential-server' ) );
			if ( is_wp_error( $tmp ) ) {
				return $tmp;
			}
			$done = array_merge( $done, self::place_zip( $tmp, $artifact['name'], false ) );
			wp_delete_file( $tmp );
		}
		return $done;
	}

	/**
	 * GET a GitHub API URL.
	 *
	 * @param string $url  URL.
	 * @param array  $head Headers.
	 * @return array|\WP_Error
	 */
	private static function github_json( $url, $head ) {
		$res = wp_remote_get( $url, array( 'headers' => $head, 'timeout' => 20 ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound -- Short args.
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		$code = wp_remote_retrieve_response_code( $res );
		if ( 200 !== $code ) {
			/* translators: 1: HTTP status, 2: GitHub message. */
			return new \WP_Error( 'github', sprintf( __( 'GitHub returned %1$d: %2$s', 'wpessential-server' ), $code, (string) ( json_decode( wp_remote_retrieve_body( $res ), true )['message'] ?? '' ) ) );
		}
		return (array) json_decode( wp_remote_retrieve_body( $res ), true );
	}

	/**
	 * Store one zip by its file name, opening a bundle of zips.
	 *
	 * @param string $src    Path of the file.
	 * @param string $name   Original file name.
	 * @param bool   $upload Whether $src is a PHP upload.
	 * @return string[] Stored package names.
	 */
	private static function place_zip( $src, $name, $upload ) {
		if ( self::is_bundle( $src ) ) {
			return self::open_bundle( $src );
		}
		$base = strtolower( preg_replace( '/(\.zip)+$/i', '', $name ) );
		if ( 'hi-wpessential' === $base ) {
			$target = Playground::theme_path();
		} elseif ( preg_match( '/^([a-z0-9]+)-(gutenberg|elementor)$/', $base, $m ) ) {
			$target = Demos::package_path( $m[1], $m[2] );
		} else {
			return array();
		}
		wp_mkdir_p( dirname( $target ) );
		$ok = $upload ? move_uploaded_file( $src, $target ) : copy( $src, $target ); // phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- Release zip moved to the private folder.
		if ( $ok && ! empty( $m ) ) {
			Demos::sync_details( $m[1], $m[2], $target );
		}
		return $ok ? array( $base ) : array();
	}

	/**
	 * Whether a zip only holds other zips (an artifact download).
	 *
	 * @param string $src Path.
	 * @return bool
	 */
	private static function is_bundle( $src ) {
		$zip = new \ZipArchive();
		if ( true !== $zip->open( $src ) ) {
			return false;
		}
		$all = $zip->numFiles > 0; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- ZipArchive API.
		for ( $i = 0; $all && $i < $zip->numFiles; $i++ ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- ZipArchive API.
			$all = '.zip' === strtolower( substr( (string) $zip->getNameIndex( $i ), -4 ) );
		}
		$zip->close();
		return $all;
	}

	/**
	 * Store the zips inside a bundle (the demos artifact).
	 *
	 * @param string $src Bundle path.
	 * @return string[] Stored package names.
	 */
	private static function open_bundle( $src ) {
		$zip = new \ZipArchive();
		if ( true !== $zip->open( $src ) ) {
			return array();
		}
		$done = array();
		$tmp  = wp_tempnam( 'wpes-release' );
		for ( $i = 0; $i < $zip->numFiles; $i++ ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- ZipArchive API.
			$entry = (string) $zip->getNameIndex( $i );
			if ( '.zip' === strtolower( substr( $entry, -4 ) ) && false !== file_put_contents( $tmp, $zip->getFromIndex( $i ) ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Temp file.
				$done = array_merge( $done, self::place_zip( $tmp, basename( $entry ), false ) );
			}
		}
		$zip->close();
		wp_delete_file( $tmp );
		return $done;
	}

	/**
	 * Save settings.
	 */
	public static function save_settings() {
		check_admin_referer( 'wpes_settings' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'wpessential-server' ) );
		}
		$out = array();
		foreach ( array( 'grace_days', 'remind_days', 'token_days', 'download_mins', 'event_days' ) as $key ) {
			$out[ $key ] = isset( $_POST[ $key ] ) ? max( 1, (int) $_POST[ $key ] ) : Plugin::settings( $key );
		}
		$out['free_staging'] = empty( $_POST['free_staging'] ) ? 0 : 1;
		$out['playground']   = empty( $_POST['playground'] ) ? 0 : 1;
		if ( ! empty( $_FILES['wpes_theme_zip']['tmp_name'] ) && '.zip' === strtolower( substr( sanitize_file_name( wp_unslash( $_FILES['wpes_theme_zip']['name'] ?? '' ) ), -4 ) ) ) {
			move_uploaded_file( $_FILES['wpes_theme_zip']['tmp_name'], Playground::theme_path() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,Generic.PHP.ForbiddenFunctions.Found -- Uploaded zip moved to the private folder.
		}
		update_option( 'wpes_server_settings', $out );
		wp_safe_redirect( admin_url( 'admin.php?page=wpes-settings&updated=1' ) );
		exit;
	}

	/**
	 * Manual cron run.
	 */
	public static function run_cron() {
		check_admin_referer( 'wpes_run_cron' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'wpessential-server' ) );
		}
		Renewals::run();
		wp_safe_redirect( admin_url( 'admin.php?page=wpes-server' ) );
		exit;
	}
}
