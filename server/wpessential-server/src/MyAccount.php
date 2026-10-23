<?php
/**
 * My Account → Licenses: keys, websites and subscription actions.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Customer license management.
 */
class MyAccount {

	const ENDPOINT = 'licenses';

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'init', array( __CLASS__, 'endpoint' ) );
		add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'query_var' ) );
		add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'menu' ) );
		add_action( 'woocommerce_account_' . self::ENDPOINT . '_endpoint', array( __CLASS__, 'render' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	/**
	 * Rewrite endpoint.
	 */
	public static function endpoint() {
		add_rewrite_endpoint( self::ENDPOINT, EP_ROOT | EP_PAGES );
		if ( get_option( 'wpes_flush' ) !== WPES_SERVER_VERSION ) {
			flush_rewrite_rules( false );
			update_option( 'wpes_flush', WPES_SERVER_VERSION );
		}
	}

	/**
	 * WooCommerce query var.
	 *
	 * @param array $vars Vars.
	 * @return array
	 */
	public static function query_var( $vars ) {
		$vars[ self::ENDPOINT ] = self::ENDPOINT;
		return $vars;
	}

	/**
	 * Menu item after Orders.
	 *
	 * @param array $items Items.
	 * @return array
	 */
	public static function menu( $items ) {
		$out = array();
		foreach ( $items as $key => $label ) {
			$out[ $key ] = $label;
			if ( 'orders' === $key ) {
				$out[ self::ENDPOINT ] = __( 'Licenses', 'wpessential-server' );
			}
		}
		return $out;
	}

	/**
	 * Styles for the licenses screen.
	 */
	public static function assets() {
		if ( is_account_page() ) {
			wp_enqueue_style( 'wpes-account', plugins_url( 'assets/account.css', WPES_SERVER_FILE ), array(), WPES_SERVER_VERSION );
		}
	}

	/**
	 * Apply an owner action.
	 *
	 * @param int    $user_id    User.
	 * @param int    $license_id License.
	 * @param string $action     Action.
	 * @param string $domain     Domain (for deactivate_domain).
	 * @return true|\WP_Error
	 */
	public static function apply( $user_id, $license_id, $action, $domain = '' ) {
		$license = Licenses::get( $license_id );
		if ( ! $license || (int) $license['user_id'] !== (int) $user_id ) {
			return new \WP_Error( 'owner', __( 'This license is not in your account.', 'wpessential-server' ) );
		}
		switch ( $action ) {
			case 'auto_renew_on':
				if ( empty( $license['expires_at'] ) ) {
					return new \WP_Error( 'lifetime', __( 'This plan never expires, so it does not renew.', 'wpessential-server' ) );
				}
				Licenses::update(
					$license_id,
					array(
						'auto_renew' => 1,
						'status'     => 'cancelled' === $license['status'] ? 'active' : $license['status'],
					)
				);
				break;
			case 'auto_renew_off':
			case 'cancel':
				// Cancelling keeps access until the paid period ends.
				Licenses::update( $license_id, array( 'auto_renew' => 0 ) );
				break;
			case 'deactivate_domain':
				Licenses::deactivate( $license_id, $domain );
				break;
			case 'regenerate':
				do {
					$key = Licenses::generate_key();
				} while ( Licenses::by_key( $key ) );
				Licenses::update( $license_id, array( 'license_key' => $key ) );
				break;
			default:
				return new \WP_Error( 'action', __( 'Unknown action.', 'wpessential-server' ) );
		}
		do_action( 'wpes_license_action', $action, $license_id, $user_id );
		return true;
	}

	/**
	 * Handle form posts and renew/upgrade links.
	 */
	public static function handle() {
		if ( ! is_user_logged_in() || empty( $_POST['wpes_action'] ) ) {
			return;
		}
		check_admin_referer( 'wpes_license' );
		$action = sanitize_key( wp_unslash( $_POST['wpes_action'] ) );
		$id     = isset( $_POST['license_id'] ) ? absint( $_POST['license_id'] ) : 0;
		$domain = isset( $_POST['domain'] ) ? sanitize_text_field( wp_unslash( $_POST['domain'] ) ) : '';

		if ( in_array( $action, array( 'renew', 'upgrade' ), true ) ) {
			$license = Licenses::get( $id );
			$plan    = isset( $_POST['plan'] ) ? sanitize_key( wp_unslash( $_POST['plan'] ) ) : ( $license ? $license['plan'] : '' );
			$product = Plans::product_id( $plan );
			if ( $license && get_current_user_id() === (int) $license['user_id'] && $product ) {
				WC()->cart->empty_cart();
				$_REQUEST[ 'renew' === $action ? 'wpes_renew' : 'wpes_upgrade' ] = $id;
				WC()->cart->add_to_cart( $product );
				wp_safe_redirect( wc_get_checkout_url() );
				exit;
			}
			wc_add_notice( __( 'That plan is not available.', 'wpessential-server' ), 'error' );
			return;
		}

		$result = self::apply( get_current_user_id(), $id, $action, $domain );
		if ( is_wp_error( $result ) ) {
			wc_add_notice( $result->get_error_message(), 'error' );
		} else {
			$messages = array(
				'auto_renew_on'     => __( 'Automatic renewal is on.', 'wpessential-server' ),
				'auto_renew_off'    => __( 'Automatic renewal is off. Your license works until its end date.', 'wpessential-server' ),
				'cancel'            => __( 'Subscription cancelled. Your license works until its end date.', 'wpessential-server' ),
				'deactivate_domain' => __( 'Website removed. You can use the license on another website now.', 'wpessential-server' ),
				'regenerate'        => __( 'New key created. Update it on your websites.', 'wpessential-server' ),
			);
			wc_add_notice( $messages[ $action ] ?? __( 'Saved.', 'wpessential-server' ) );
		}
		wp_safe_redirect( wc_get_account_endpoint_url( self::ENDPOINT ) );
		exit;
	}

	/**
	 * Small POST form button.
	 *
	 * @param int    $id      License.
	 * @param string $action  Action.
	 * @param string $label   Label.
	 * @param string $css     Extra class.
	 * @param array  $fields  Extra hidden fields.
	 * @param string $confirm Confirmation text.
	 * @return string
	 */
	private static function button( $id, $action, $label, $css = '', array $fields = array(), $confirm = '' ) {
		$html  = '<form method="post" class="wpes-inline"' . ( $confirm ? ' onsubmit="return confirm(' . esc_attr( wp_json_encode( $confirm ) ) . ')"' : '' ) . '>' . wp_nonce_field( 'wpes_license', '_wpnonce', true, false );
		$html .= '<input type="hidden" name="wpes_action" value="' . esc_attr( $action ) . '"><input type="hidden" name="license_id" value="' . (int) $id . '">';
		foreach ( $fields as $k => $v ) {
			$html .= '<input type="hidden" name="' . esc_attr( $k ) . '" value="' . esc_attr( $v ) . '">';
		}
		return $html . '<button type="submit" class="button wpes-btn ' . esc_attr( $css ) . '">' . esc_html( $label ) . '</button></form>';
	}

	/**
	 * Render the screen.
	 */
	public static function render() {
		$licenses = Licenses::for_user( get_current_user_id() );
		$plans    = Plans::all();
		echo '<div class="wpes-account">';
		if ( ! $licenses ) {
			echo '<div class="wpes-empty"><h3>' . esc_html__( 'No licenses yet', 'wpessential-server' ) . '</h3><p>' . esc_html__( 'Pick a plan to unlock Pro demos, updates and support.', 'wpessential-server' ) . '</p></div>';
		}
		foreach ( $licenses as $license ) {
			$shape  = Licenses::owner_shape( $license );
			$status = $shape['status'];
			$labels = array(
				'active'    => __( 'Active', 'wpessential-server' ),
				'expired'   => __( 'Expired', 'wpessential-server' ),
				'cancelled' => __( 'Cancelled', 'wpessential-server' ),
				'revoked'   => __( 'Revoked', 'wpessential-server' ),
			);
			if ( 'lifetime' === $shape['expires'] ) {
				/* translators: %s: major version, e.g. 1.x. */
				$ends = $shape['covers'] ? sprintf( __( 'When version %s ends', 'wpessential-server' ), $shape['covers'] ) : __( 'Never', 'wpessential-server' );
			} else {
				$ends = date_i18n( get_option( 'date_format' ), strtotime( $shape['expires'] ) );
			}
			$limit = $shape['sites_limit'] ? $shape['sites_limit'] : '∞';
			echo '<section class="wpes-license wpes-license--' . esc_attr( $status ) . '">';
			echo '<header><div><span class="wpes-plan">' . esc_html( $shape['plan'] ) . '</span><span class="wpes-status">' . esc_html( $labels[ $status ] ?? $status ) . '</span></div>';
			echo '<code class="wpes-key" title="' . esc_attr__( 'License key', 'wpessential-server' ) . '">' . esc_html( $shape['key'] ) . '</code></header>';
			echo '<dl class="wpes-meta"><div><dt>' . esc_html( $shape['auto_renew'] ? __( 'Renews on', 'wpessential-server' ) : __( 'Ends on', 'wpessential-server' ) ) . '</dt><dd>' . esc_html( $ends ) . '</dd></div>';
			echo '<div><dt>' . esc_html__( 'Websites', 'wpessential-server' ) . '</dt><dd>' . esc_html( $shape['sites_used'] . ' / ' . $limit ) . '</dd></div>';
			echo '<div><dt>' . esc_html__( 'Auto-renew', 'wpessential-server' ) . '</dt><dd>' . esc_html( 'lifetime' === $shape['expires'] ? '—' : ( $shape['auto_renew'] ? __( 'On', 'wpessential-server' ) : __( 'Off', 'wpessential-server' ) ) ) . '</dd></div></dl>';

			$activations = Licenses::activations( $license['id'] );
			if ( $activations ) {
				echo '<ul class="wpes-domains">';
				foreach ( $activations as $a ) {
					echo '<li><span>' . esc_html( $a['domain'] ) . ( $a['is_staging'] ? ' <em>' . esc_html__( 'staging · free', 'wpessential-server' ) . '</em>' : '' ) . '</span>' . self::button( $license['id'], 'deactivate_domain', __( 'Remove', 'wpessential-server' ), 'wpes-btn--ghost', array( 'domain' => $a['domain'] ), __( 'Remove this website from the license?', 'wpessential-server' ) ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- button() escapes.
				}
				echo '</ul>';
			}

			echo '<footer>';
			$buttons = '';
			if ( 'lifetime' !== $shape['expires'] && 'free' !== $license['plan'] ) {
				$buttons .= self::button( $license['id'], 'renew', __( 'Renew now', 'wpessential-server' ), 'wpes-btn--primary' );
				$buttons .= $shape['auto_renew']
					? self::button( $license['id'], 'auto_renew_off', __( 'Turn off auto-renew', 'wpessential-server' ), 'wpes-btn--ghost', array(), __( 'Stop automatic renewal? Your license keeps working until its end date.', 'wpessential-server' ) )
					: self::button( $license['id'], 'auto_renew_on', __( 'Turn on auto-renew', 'wpessential-server' ), 'wpes-btn--ghost' );
			}
			$buttons .= self::button( $license['id'], 'regenerate', __( 'New key', 'wpessential-server' ), 'wpes-btn--ghost', array(), __( 'Create a new key? The old key stops working and your websites must be activated again.', 'wpessential-server' ) );
			echo $buttons; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- button() escapes.

			$higher = array_filter(
				$plans,
				static function ( $p ) use ( $license ) {
					return Plans::rank( $p['key'] ) > Plans::rank( $license['plan'] );
				}
			);
			if ( $higher && 'revoked' !== $status ) {
				echo '<form method="post" class="wpes-upgrade">' . wp_nonce_field( 'wpes_license', '_wpnonce', true, false ) . '<input type="hidden" name="wpes_action" value="upgrade"><input type="hidden" name="license_id" value="' . (int) $license['id'] . '"><select name="plan" aria-label="' . esc_attr__( 'Upgrade to', 'wpessential-server' ) . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Nonce field markup.
				foreach ( $higher as $p ) {
					echo '<option value="' . esc_attr( $p['key'] ) . '">' . esc_html( $p['name'] . ' — ' . wp_strip_all_tags( wc_price( $p['price'] ) ) ) . '</option>';
				}
				echo '</select><button type="submit" class="button wpes-btn wpes-btn--accent">' . esc_html__( 'Upgrade', 'wpessential-server' ) . '</button></form>';
			}
			echo '</footer></section>';
		}

		echo '<section class="wpes-plans"><h3>' . esc_html__( 'Plans', 'wpessential-server' ) . '</h3><div class="wpes-plans__grid">';
		foreach ( $plans as $p ) {
			if ( 'free' === $p['key'] ) {
				continue;
			}
			$period = $p['months'] > 0 ? sprintf( /* translators: %d: months. */ _n( '/ %d month', '/ %d months', $p['months'], 'wpessential-server' ), $p['months'] ) : __( 'once', 'wpessential-server' );
			echo '<article class="wpes-plan-card' . ( 'pro_yearly' === $p['key'] ? ' is-featured' : '' ) . '"><h4>' . esc_html( $p['name'] ) . '</h4><p class="wpes-price">' . wp_kses_post( wc_price( $p['price'] ) ) . ' <small>' . esc_html( $period ) . '</small></p><ul>';
			foreach ( $p['features'] as $f ) {
				echo '<li>' . esc_html( $f ) . '</li>';
			}
			echo '</ul><a class="button wpes-btn wpes-btn--primary" href="' . esc_url( add_query_arg( 'add-to-cart', $p['product_id'], wc_get_checkout_url() ) ) . '">' . esc_html__( 'Choose', 'wpessential-server' ) . '</a></article>';
		}
		echo '</div></section></div>';
	}
}
