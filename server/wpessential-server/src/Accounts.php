<?php
/**
 * Customer accounts for the theme dashboard: sign up, sign in, API tokens, one-time checkout links.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Accounts.
 */
class Accounts {

	const META = '_wpes_tokens';

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'template_redirect', array( __CLASS__, 'autologin' ), 1 );
	}

	/**
	 * Too many attempts from this IP?
	 *
	 * @param string $action Action.
	 * @return bool
	 */
	public static function throttled( $action ) {
		$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key   = 'wpes_rl_' . $action . '_' . md5( $ip );
		$count = (int) get_transient( $key );
		set_transient( $key, $count + 1, 15 * MINUTE_IN_SECONDS );
		return $count >= (int) apply_filters( 'wpes_rate_limit', 10, $action );
	}

	/**
	 * Register a customer and give them the Free plan.
	 *
	 * @param array $data name, email, password.
	 * @return array|\WP_Error
	 */
	public static function register( array $data ) {
		if ( self::throttled( 'register' ) ) {
			return new \WP_Error( 'throttled', __( 'Too many attempts. Wait 15 minutes and try again.', 'wpessential-server' ) );
		}
		$email = sanitize_email( $data['email'] ?? '' );
		$pass  = (string) ( $data['password'] ?? '' );
		$name  = sanitize_text_field( $data['name'] ?? '' );
		if ( ! is_email( $email ) ) {
			return new \WP_Error( 'email', __( 'Enter a valid email address.', 'wpessential-server' ) );
		}
		if ( strlen( $pass ) < 8 ) {
			return new \WP_Error( 'password', __( 'Use at least 8 characters for your password.', 'wpessential-server' ) );
		}
		if ( email_exists( $email ) ) {
			return new \WP_Error( 'exists', __( 'An account with this email already exists. Sign in instead.', 'wpessential-server' ) );
		}
		$parts   = explode( ' ', $name, 2 );
		$user_id = wc_create_new_customer(
			$email,
			'',
			$pass,
			array(
				'first_name' => $parts[0] ?? '',
				'last_name'  => $parts[1] ?? '',
			)
		);
		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}
		$free = Plans::product_id( 'free' );
		if ( $free ) {
			Licenses::create( $user_id, Plans::from_product( $free ) );
		}
		return self::session( $user_id, $data['site'] ?? '' );
	}

	/**
	 * Sign in.
	 *
	 * @param array $data login, password.
	 * @return array|\WP_Error
	 */
	public static function login( array $data ) {
		if ( self::throttled( 'login' ) ) {
			return new \WP_Error( 'throttled', __( 'Too many attempts. Wait 15 minutes and try again.', 'wpessential-server' ) );
		}
		$user = wp_authenticate( sanitize_text_field( $data['login'] ?? '' ), (string) ( $data['password'] ?? '' ) );
		if ( is_wp_error( $user ) ) {
			return new \WP_Error( 'login', __( 'Email or password is not correct.', 'wpessential-server' ) );
		}
		return self::session( $user->ID, $data['site'] ?? '' );
	}

	/**
	 * New token + account payload.
	 *
	 * @param int    $user_id User.
	 * @param string $site    Site that asked.
	 * @return array
	 */
	public static function session( $user_id, $site ) {
		$token  = wp_generate_password( 48, false );
		$tokens = self::tokens( $user_id );

		$tokens[ hash_hmac( 'sha256', $token, Plugin::secret() ) ] = array(
			'site'    => Licenses::domain( $site ),
			'created' => time(),
		);
		update_user_meta( $user_id, self::META, array_slice( $tokens, -20, null, true ) );
		return array( 'token' => $token ) + self::me( $user_id );
	}

	/**
	 * Stored tokens without expired ones.
	 *
	 * @param int $user_id User.
	 * @return array
	 */
	private static function tokens( $user_id ) {
		$ttl = (int) Plugin::settings( 'token_days' ) * DAY_IN_SECONDS;
		return array_filter(
			(array) get_user_meta( $user_id, self::META, true ),
			static function ( $t ) use ( $ttl ) {
				return is_array( $t ) && $t['created'] + $ttl > time();
			}
		);
	}

	/**
	 * User for a bearer token.
	 *
	 * @param string $token Token.
	 * @return int User id or 0.
	 */
	public static function user_for_token( $token ) {
		if ( strlen( (string) $token ) < 20 ) {
			return 0;
		}
		$hash  = hash_hmac( 'sha256', $token, Plugin::secret() );
		$users = get_users(
			array(
				'fields'     => 'ID',
				'number'     => 5,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Token lookup.
					array(
						'key'     => self::META,
						'value'   => $hash,
						'compare' => 'LIKE',
					),
				),
			)
		);
		foreach ( $users as $id ) {
			if ( isset( self::tokens( (int) $id )[ $hash ] ) ) {
				return (int) $id;
			}
		}
		return 0;
	}

	/**
	 * Remove a token.
	 *
	 * @param int    $user_id User.
	 * @param string $token   Token.
	 */
	public static function logout( $user_id, $token ) {
		$tokens = self::tokens( $user_id );
		unset( $tokens[ hash_hmac( 'sha256', $token, Plugin::secret() ) ] );
		update_user_meta( $user_id, self::META, $tokens );
	}

	/**
	 * Account payload.
	 *
	 * @param int $user_id User.
	 * @return array
	 */
	public static function me( $user_id ) {
		$user = get_userdata( $user_id );
		return array(
			'user'     => array(
				'id'     => (int) $user_id,
				'name'   => $user ? $user->display_name : '',
				'email'  => $user ? $user->user_email : '',
				'avatar' => get_avatar_url( $user_id, array( 'size' => 96 ) ),
			),
			'licenses' => array_map( array( Licenses::class, 'owner_shape' ), Licenses::for_user( $user_id ) ),
			'account'  => wc_get_account_endpoint_url( MyAccount::ENDPOINT ),
		);
	}

	/**
	 * One-time link that signs the customer in and opens checkout with a plan.
	 *
	 * @param int   $user_id    User.
	 * @param int   $product_id Plan product.
	 * @param array $extra      Extra query args (wpes_renew / wpes_upgrade).
	 * @return string
	 */
	public static function checkout_link( $user_id, $product_id, array $extra = array() ) {
		$code = wp_generate_password( 32, false );
		set_transient(
			'wpes_al_' . hash_hmac( 'sha256', $code, Plugin::secret() ),
			array(
				'user'    => (int) $user_id,
				'product' => (int) $product_id,
				'extra'   => $extra,
			),
			10 * MINUTE_IN_SECONDS
		);
		return add_query_arg( 'wpes_checkout', $code, home_url( '/' ) );
	}

	/**
	 * Consume a checkout link.
	 */
	public static function autologin() {
		if ( empty( $_GET['wpes_checkout'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- One-time code is the credential.
			return;
		}
		$key  = 'wpes_al_' . hash_hmac( 'sha256', sanitize_text_field( wp_unslash( $_GET['wpes_checkout'] ) ), Plugin::secret() ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- One-time code.
		$data = get_transient( $key );
		delete_transient( $key );
		if ( ! $data ) {
			wp_safe_redirect( add_query_arg( 'wpes_link', 'expired', wc_get_page_permalink( 'myaccount' ) ) );
			exit;
		}
		if ( get_current_user_id() !== $data['user'] ) {
			wp_set_current_user( $data['user'] );
			wp_set_auth_cookie( $data['user'], false, is_ssl() );
		}
		WC()->cart->empty_cart();
		foreach ( $data['extra'] as $key2 => $value ) {
			$_REQUEST[ $key2 ] = $value; // Read by Orders::cart_item_data().
		}
		WC()->cart->add_to_cart( $data['product'] );
		wp_safe_redirect( wc_get_checkout_url() );
		exit;
	}
}
