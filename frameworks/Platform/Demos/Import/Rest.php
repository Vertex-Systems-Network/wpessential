<?php
/**
 * REST routes for the demo importer (moved from the Hi WPEssential theme's Core module):
 * wpessential/v1/demos/import/{start,run,status,cancel,remove} and demos/import/plugins.
 * Administrators only.
 *
 * @package WPEssential
 */

namespace WPEssential\Platform\Demos\Import;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Importer REST controller.
 */
class Rest {

	const NS = 'wpessential/v1';

	/**
	 * Hook the routes.
	 */
	public static function boot() {
		add_action( 'rest_api_init', array( new self(), 'routes' ) );
		add_action( 'after_switch_theme', array( __CLASS__, 'queue_default' ) );
		add_action( 'admin_init', array( __CLASS__, 'open_default' ) );
		// A demo set WPForms up already: keep its first-run setup wizard from taking over the admin.
		add_action(
			'init',
			static function () {
				add_filter(
					'wpforms_setup_wizard_setup_wizard_is_disabled',
					static function ( $off ) {
						return $off || (bool) Importer::imported();
					}
				);
			}
		);
	}

	const AUTOSTART = 'wpessential_core_autostart';

	/**
	 * Theme activated: queue its default demo (theme filter) when the site has none yet.
	 */
	public static function queue_default() {
		$demo = (string) apply_filters( 'wpessential_core_default_demo', '' );
		$job  = Importer::current();
		if ( '' === $demo || Importer::imported() || ( ! empty( $job['status'] ) && 'running' === $job['status'] ) ) {
			return;
		}
		$auto = array(
			'demo'     => sanitize_key( $demo ),
			'builder'  => 'gutenberg',
			'redirect' => true,
		);
		update_option( self::AUTOSTART, $auto, false );
	}

	/**
	 * Send the admin to the Demo Library once, where the queued import starts with visible progress.
	 */
	public static function open_default() {
		$auto = get_option( self::AUTOSTART );
		if ( ! is_array( $auto ) || empty( $auto['redirect'] ) || wp_doing_ajax() || ! current_user_can( 'manage_options' ) || isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only flag.
			return;
		}
		$auto['redirect'] = false;
		update_option( self::AUTOSTART, $auto, false );
		wp_safe_redirect( admin_url( 'admin.php?page=wpessential-demos' ) );
		exit;
	}

	/**
	 * Register routes.
	 */
	public function routes() {
		$routes = array(
			'/demos/import/library' => array( 'GET', 'library' ),
			'/demos/import/plugins' => array( 'GET', 'plugins' ),
			'/demos/import/start'   => array( 'POST', 'start' ),
			'/demos/import/run'     => array( 'POST', 'run' ),
			'/demos/import/status'  => array( 'GET', 'status' ),
			'/demos/import/cancel'  => array( 'POST', 'cancel' ),
			'/demos/import/remove'  => array( 'POST', 'remove' ),
		);
		foreach ( $routes as $route => $def ) {
			register_rest_route(
				self::NS,
				$route,
				array(
					'methods'             => $def[0],
					'callback'            => array( $this, $def[1] ),
					'permission_callback' => static function () {
						return current_user_can( 'manage_options' );
					},
				)
			);
		}
	}

	/**
	 * Wrap results.
	 *
	 * @param mixed $data Data or WP_Error.
	 * @return \WP_REST_Response|\WP_Error
	 */
	private function respond( $data ) {
		if ( is_wp_error( $data ) ) {
			$data->add_data( array( 'status' => 400 ) );
			return $data;
		}
		return rest_ensure_response( $data );
	}

	/**
	 * GET demos/import/library: demos, the imported one and an unfinished job.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function library( $request ) {
		$all             = Demos::all( (bool) $request->get_param( 'refresh' ) );
		$imported        = Importer::imported();
		$all['imported'] = $imported ? array_intersect_key( $imported, array_flip( array( 'demo', 'title', 'builder', 'time' ) ) ) : null;
		$job             = Importer::current();
		$all['job']      = ! empty( $job['id'] ) && in_array( $job['status'], array( 'running', 'failed' ), true ) ? array(
			'demo'    => $job['demo'],
			'title'   => $job['title'],
			'builder' => $job['builder'],
			'status'  => $job['status'],
		) : null;
		return $this->respond( $all );
	}

	/**
	 * GET demos/import/plugins?demo=&builder=.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function plugins( $request ) {
		$demo = Demos::get( (string) $request->get_param( 'demo' ) );
		if ( ! $demo ) {
			return $this->respond( new \WP_Error( 'wpe_demo', __( 'Demo not found.', 'wpessential' ) ) );
		}
		$plugins = (array) ( $demo['builders'][ (string) $request->get_param( 'builder' ) ]['plugins'] ?? array() );
		return $this->respond( array_map( array( Plugins::class, 'describe' ), $plugins ) );
	}

	/**
	 * POST demos/import/start.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function start( $request ) {
		return $this->respond( Importer::start( (string) $request->get_param( 'demo' ), (string) $request->get_param( 'builder' ), (array) $request->get_param( 'options' ) ) );
	}

	/**
	 * POST demos/import/run: one bounded slice of work. "retry" resumes a failed step.
	 *
	 * @param \WP_REST_Request $request Request.
	 */
	public function run( $request ) {
		$job = Importer::current();
		if ( ! empty( $job['id'] ) && 'failed' === $job['status'] && $request->get_param( 'retry' ) ) {
			$job['status']                           = 'running';
			$job['finished']                         = 0;
			$job['steps'][ $job['index'] ]['status'] = 'pending';
			$job['inflight']                         = array(
				'step'     => '',
				'cursor'   => -1,
				'attempts' => 0,
			);
			update_option( Importer::JOB, $job, false );
		}
		return $this->respond( Importer::run( (int) $request->get_param( 'since' ) ) );
	}

	/**
	 * GET demos/import/status.
	 */
	public function status() {
		$job = Importer::current();
		return $this->respond( empty( $job['id'] ) ? null : Importer::run( 0 ) );
	}

	/**
	 * POST demos/import/cancel.
	 */
	public function cancel() {
		return $this->respond( Importer::cancel() );
	}

	/**
	 * POST demos/import/remove: delete what the demo added and restore earlier settings.
	 */
	public function remove() {
		$record = Importer::imported();
		if ( empty( $record['demo'] ) ) {
			return $this->respond( new \WP_Error( 'wpe_nothing', __( 'No imported demo was found.', 'wpessential' ) ) );
		}
		$count = Importer::remove();
		return $this->respond(
			array(
				/* translators: 1: demo, 2: count. */
				'message' => sprintf( __( '%1$s removed: %2$d items deleted and your previous settings restored.', 'wpessential' ), $record['title'], $count ),
			)
		);
	}
}
