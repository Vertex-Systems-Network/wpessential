<?php
/**
 * Public REST API used by theme dashboards: wpessential-server/v1.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * API routes.
 */
class Api {

	const NS = 'wpessential-server/v1';

	/**
	 * Routes.
	 */
	public function routes() {
		$public = '__return_true';
		$auth   = array( $this, 'authenticate' );
		$routes = array(
			'/license/activate'   => array( 'POST', 'license_activate', $public ),
			'/license/deactivate' => array( 'POST', 'license_deactivate', $public ),
			'/license/check'      => array( 'POST', 'license_check', $public ),
			'/license/package'    => array( 'POST', 'license_package', $public ),
			'/demos'              => array( 'GET', 'demos', $public ),
			'/download'           => array( 'GET', 'download', $public ),
			'/playground'         => array( 'GET', 'playground', $public ),
			'/theme'              => array( 'GET', 'theme', $public ),
			'/thumbnail'          => array( 'GET', 'thumbnail', $public ),
			'/plans'              => array( 'GET', 'plans', $public ),
			'/account/register'   => array( 'POST', 'signup', $public ),
			'/account/login'      => array( 'POST', 'signin', $public ),
			'/account/me'         => array( 'GET', 'me', $auth ),
			'/account/logout'     => array( 'POST', 'logout', $auth ),
			'/account/license'    => array( 'POST', 'license_action', $auth ),
			'/account/checkout'   => array( 'POST', 'checkout', $auth ),
			'/account/connect'    => array( 'POST', 'connect', $auth ),
			'/release'            => array( 'POST', 'release', array( $this, 'can_release' ) ),
		);
		foreach ( $routes as $route => $def ) {
			register_rest_route(
				self::NS,
				$route,
				array(
					'methods'             => $def[0],
					'callback'            => array( $this, $def[1] ),
					'permission_callback' => $def[2],
				)
			);
		}
	}

	/**
	 * Error response in the shape clients expect ({ success:false, status, message }).
	 *
	 * @param \WP_Error $error  Error.
	 * @param int       $status HTTP status.
	 * @return \WP_REST_Response
	 */
	private function fail( $error, $status = 200 ) {
		return new \WP_REST_Response(
			array(
				'success' => false,
				'status'  => $error->get_error_code(),
				'message' => $error->get_error_message(),
			),
			$status
		);
	}

	/**
	 * Bearer token → user.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return bool|\WP_Error
	 */
	public function authenticate( $request ) {
		$header = (string) $request->get_header( 'authorization' );
		$token  = 0 === stripos( $header, 'Bearer ' ) ? trim( substr( $header, 7 ) ) : '';
		$user   = Accounts::user_for_token( $token );
		if ( ! $user ) {
			return new \WP_Error( 'signed_out', __( 'Your session ended. Sign in again.', 'wpessential-server' ), array( 'status' => 401 ) );
		}
		$request->set_param( '_user', $user );
		$request->set_param( '_token', $token );
		return true;
	}

	/**
	 * Activate.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function license_activate( $request ) {
		if ( Accounts::throttled( 'license' ) ) {
			return $this->fail( new \WP_Error( 'throttled', __( 'Too many attempts. Try again in a few minutes.', 'wpessential-server' ) ), 429 );
		}
		$license = Licenses::activate( (string) $request->get_param( 'license' ), (string) $request->get_param( 'domain' ) );
		return is_wp_error( $license ) ? $this->fail( $license ) : rest_ensure_response( Licenses::public_shape( Licenses::get( $license['id'] ) ) + array( 'message' => __( 'License activated for this website.', 'wpessential-server' ) ) );
	}

	/**
	 * Deactivate.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function license_deactivate( $request ) {
		if ( Accounts::throttled( 'license' ) ) {
			return $this->fail( new \WP_Error( 'throttled', __( 'Too many attempts. Try again in a few minutes.', 'wpessential-server' ) ), 429 );
		}
		$license = Licenses::by_key( (string) $request->get_param( 'license' ) );
		if ( ! $license ) {
			return $this->fail( new \WP_Error( 'invalid', __( 'This license key does not exist.', 'wpessential-server' ) ) );
		}
		Licenses::deactivate( $license['id'], (string) $request->get_param( 'domain' ) );
		return rest_ensure_response(
			array(
				'success' => true,
				'status'  => 'inactive',
				'message' => __( 'Website removed from the license.', 'wpessential-server' ),
			)
		);
	}

	/**
	 * Daily check from client sites.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function license_check( $request ) {
		$license = Licenses::by_key( (string) $request->get_param( 'license' ) );
		$valid   = Licenses::validate( $license );
		if ( is_wp_error( $valid ) ) {
			return $this->fail( $valid );
		}
		if ( ! Licenses::has_domain( $license, (string) $request->get_param( 'domain' ) ) ) {
			return $this->fail( new \WP_Error( 'site_inactive', __( 'This website was removed from the license in your account. Activate it again to continue.', 'wpessential-server' ) ) );
		}
		Licenses::activate( $license['license_key'], (string) $request->get_param( 'domain' ) ); // Refreshes last_check.
		return rest_ensure_response( Licenses::public_shape( $license ) );
	}

	/**
	 * Signed download for a Pro demo or premium plugin.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function license_package( $request ) {
		$license = Licenses::by_key( (string) $request->get_param( 'license' ) );
		$valid   = Licenses::validate( $license );
		if ( is_wp_error( $valid ) ) {
			return $this->fail( $valid );
		}
		if ( ! Licenses::has_domain( $license, (string) $request->get_param( 'domain' ) ) ) {
			return $this->fail( new \WP_Error( 'site_inactive', __( 'Activate the license on this website first.', 'wpessential-server' ) ) );
		}
		$item    = sanitize_key( (string) $request->get_param( 'item' ) );
		$context = sanitize_key( (string) $request->get_param( 'context' ) );
		$plan    = Plans::from_product( (int) $license['product_id'] );
		$pro     = 'plugin' === $context ? true : (bool) Demos::is_pro( $item );
		if ( $pro && ( ! $plan || ! $plan['pro'] ) ) {
			return $this->fail( new \WP_Error( 'upgrade', __( 'Your plan includes free demos only. Upgrade to unlock this Pro item.', 'wpessential-server' ) ) );
		}
		$url = 'plugin' === $context ? Demos::signed_url( 'plugin', $item ) : Demos::signed_url( $item, $context );
		return rest_ensure_response(
			array(
				'success' => true,
				'url'     => $url,
			)
		);
	}

	/**
	 * Demo manifest.
	 */
	public function demos() {
		$response = rest_ensure_response( Demos::manifest() );
		$response->header( 'Cache-Control', 'public, max-age=600' );
		return $response;
	}

	/**
	 * Package stream.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function download( $request ) {
		return Demos::download( $request );
	}

	/**
	 * Playground blueprint for a live demo.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function playground( $request ) {
		return Playground::blueprint( $request );
	}

	/**
	 * Theme zip for Playground.
	 */
	public function theme() {
		return Playground::theme();
	}

	/**
	 * Plans for pricing tables.
	 */
	public function plans() {
		return rest_ensure_response( Plans::all() );
	}

	/**
	 * Sign up.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function signup( $request ) {
		$result = Accounts::register( $request->get_params() );
		return is_wp_error( $result ) ? $this->fail( $result, 400 ) : rest_ensure_response( array( 'success' => true ) + $result );
	}

	/**
	 * Sign in.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function signin( $request ) {
		$result = Accounts::login( $request->get_params() );
		return is_wp_error( $result ) ? $this->fail( $result, 401 ) : rest_ensure_response( array( 'success' => true ) + $result );
	}

	/**
	 * Account.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function me( $request ) {
		return rest_ensure_response( array( 'success' => true ) + Accounts::me( (int) $request->get_param( '_user' ) ) + array( 'plans' => Plans::all() ) );
	}

	/**
	 * Connect a site to the signed-in customer without a key: keep the license already on the
	 * domain, else take a free slot on the best active license (Pro first, most free slots).
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function connect( $request ) {
		$domain = Licenses::domain( (string) $request->get_param( 'site' ) );
		if ( '' === $domain ) {
			return $this->fail( new \WP_Error( 'domain', __( 'Send the site address.', 'wpessential-server' ) ), 400 );
		}
		$pick = self::pick( Licenses::for_user( (int) $request->get_param( '_user' ) ), $domain );
		if ( ! $pick ) {
			return $this->fail( new \WP_Error( 'no_slot', __( 'Every site slot on your subscriptions is in use. Upgrade or free a slot to connect this site.', 'wpessential-server' ) ), 409 );
		}
		$license = Licenses::activate( $pick['license_key'], $domain );
		if ( is_wp_error( $license ) ) {
			return $this->fail( $license, 400 );
		}
		return rest_ensure_response(
			Licenses::owner_shape( Licenses::get( $license['id'] ) ) + array( 'message' => __( 'This website is connected to your subscription.', 'wpessential-server' ) )
		);
	}

	/**
	 * Choose the license for a domain.
	 *
	 * @param array  $licenses Licenses of the customer (database rows).
	 * @param string $domain   Normalised domain.
	 * @return array|null
	 */
	public static function pick( array $licenses, $domain ) {
		$usable = array();
		foreach ( $licenses as $license ) {
			if ( true !== Licenses::validate( $license ) ) {
				continue;
			}
			if ( Licenses::has_domain( $license, $domain ) ) {
				return $license;
			}
			$limit = (int) $license['sites_limit'];
			if ( 0 === $limit || Licenses::is_staging( $domain ) || Licenses::used_sites( $license['id'] ) < $limit ) {
				$usable[] = $license;
			}
		}
		usort(
			$usable,
			static function ( $a, $b ) {
				$pro  = static function ( $l ) {
					$plan = Plans::from_product( (int) $l['product_id'] );
					return $plan && $plan['pro'] ? 1 : 0;
				};
				$free = static function ( $l ) {
					return 0 === (int) $l['sites_limit'] ? PHP_INT_MAX : (int) $l['sites_limit'] - Licenses::used_sites( $l['id'] );
				};
				return array( $pro( $b ), $free( $b ) ) <=> array( $pro( $a ), $free( $a ) );
			}
		);
		return $usable ? $usable[0] : null;
	}

	/**
	 * Sign out.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function logout( $request ) {
		Accounts::logout( (int) $request->get_param( '_user' ), (string) $request->get_param( '_token' ) );
		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Owner actions: auto_renew, cancel, deactivate_domain, regenerate.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function license_action( $request ) {
		$result = MyAccount::apply( (int) $request->get_param( '_user' ), (int) $request->get_param( 'license_id' ), (string) $request->get_param( 'action' ), (string) $request->get_param( 'domain' ) );
		return is_wp_error( $result ) ? $this->fail( $result, 400 ) : rest_ensure_response( array( 'success' => true ) + Accounts::me( (int) $request->get_param( '_user' ) ) );
	}

	/**
	 * One-time checkout link for buy / renew / upgrade.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function checkout( $request ) {
		$user    = (int) $request->get_param( '_user' );
		$product = Plans::product_id( sanitize_key( (string) $request->get_param( 'plan' ) ) );
		if ( ! $product ) {
			return $this->fail( new \WP_Error( 'plan', __( 'That plan is not for sale right now.', 'wpessential-server' ) ), 400 );
		}
		$extra   = array();
		$license = (int) $request->get_param( 'license_id' );
		$mode    = (string) $request->get_param( 'mode' );
		if ( $license ) {
			$row = Licenses::get( $license );
			if ( ! $row || (int) $row['user_id'] !== $user ) {
				return $this->fail( new \WP_Error( 'owner', __( 'This license is not in your account.', 'wpessential-server' ) ), 403 );
			}
			$extra[ 'renew' === $mode ? 'wpes_renew' : 'wpes_upgrade' ] = $license;
		}
		return rest_ensure_response(
			array(
				'success' => true,
				'url'     => Accounts::checkout_link( $user, $product, $extra ),
			)
		);
	}

	/**
	 * Demo thumbnail read from its package (public, cached).
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_Error|void
	 */
	public function thumbnail( $request ) {
		$image = Demos::package_image( sanitize_key( (string) $request->get_param( 'demo' ) ) );
		if ( ! $image ) {
			return new \WP_Error( 'not_found', __( 'No thumbnail.', 'wpessential-server' ), array( 'status' => 404 ) );
		}
		header( 'Content-Type: image/webp' );
		header( 'Cache-Control: public, max-age=86400' );
		echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Binary image.
		exit;
	}

	/**
	 * Release uploads need an administrator (WordPress application password over HTTPS).
	 *
	 * @return bool
	 */
	public function can_release() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Publish a release from CI: the theme zip (live demos) and demo packages.
	 *
	 * Multipart fields: `theme` (theme zip), `version`, and one file per package named
	 * `<demo>-<builder>` (e.g. maison-elementor). A package for a demo the library does not
	 * have yet creates a draft demo to complete in the admin.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function release( $request ) {
		$files = $request->get_file_params();
		$done  = array();
		$fail  = array();
		foreach ( $files as $field => $file ) {
			if ( ! empty( $file['error'] ) ) {
				// Usually UPLOAD_ERR_INI_SIZE: raise upload_max_filesize / post_max_size on the server.
				$fail[ $field ] = (int) $file['error'];
				continue;
			}
			if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) || '.zip' !== strtolower( substr( (string) $file['name'], -4 ) ) ) {
				continue;
			}
			if ( 'theme' === $field ) {
				$target = Playground::theme_path();
			} elseif ( preg_match( '/^([a-z0-9]+)-(gutenberg|elementor)$/', (string) $field, $m ) ) {
				$target = Demos::package_path( $m[1], $m[2] );
			} else {
				continue;
			}
			if ( move_uploaded_file( $file['tmp_name'], $target ) ) { // phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- Release zip moved to the private folder.
				if ( 'theme' !== $field ) {
					Demos::sync_details( $m[1], $m[2], $target );
				}
				$done[] = $field;
			}
		}
		if ( $request->get_param( 'version' ) ) {
			update_option( 'wpes_theme_release', sanitize_text_field( $request->get_param( 'version' ) ), false );
		}
		return rest_ensure_response(
			array(
				'success'  => (bool) $done && ! $fail,
				'uploaded' => $done,
				'failed'   => $fail,
			)
		);
	}
}
