<?php
/**
 * Live demos in WordPress Playground.
 *
 * Instead of hosting a demo site per demo, the library opens WordPress Playground (WordPress
 * running in the visitor's browser) with a blueprint that installs the theme and plugins,
 * unzips the same demo package customers import, and runs the WPEssential importer on it.
 * Every click, hover and page visit is the real site; nothing is hosted except the zips.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Blueprints + theme zip.
 */
class Playground {

	const PLAYGROUND = 'https://playground.wordpress.net/';

	/**
	 * Private path of the uploaded theme zip.
	 *
	 * @return string
	 */
	public static function theme_path() {
		return Install::protect_packages() . '/wpessential-theme.zip';
	}

	/**
	 * Live previews are on and the theme zip is uploaded.
	 *
	 * @return bool
	 */
	public static function enabled() {
		return (bool) Plugin::settings( 'playground' ) && is_readable( self::theme_path() );
	}

	/**
	 * Blueprint URL for a demo, used by the library's "Live demo" button.
	 *
	 * @param string $demo    Demo id.
	 * @param string $builder gutenberg|elementor.
	 * @return string
	 */
	public static function blueprint_url( $demo, $builder ) {
		return add_query_arg(
			array(
				'demo'    => $demo,
				'builder' => $builder,
			),
			rest_url( Api::NS . '/playground' )
		);
	}

	/**
	 * Link that opens the demo in Playground.
	 *
	 * @param string $demo    Demo id.
	 * @param string $builder Builder.
	 * @return string
	 */
	public static function open_url( $demo, $builder ) {
		return self::PLAYGROUND . '?blueprint-url=' . rawurlencode( self::blueprint_url( $demo, $builder ) );
	}

	/**
	 * Allow Playground (another origin) to fetch blueprints and zips.
	 *
	 * @param \WP_REST_Response|null $response Response to decorate, or null to send headers now.
	 * @return \WP_REST_Response|null
	 */
	public static function cors( $response = null ) {
		if ( $response ) {
			$response->header( 'Access-Control-Allow-Origin', '*' );
			return $response;
		}
		header( 'Access-Control-Allow-Origin: *' );
		return null;
	}

	/**
	 * Blueprint JSON.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function blueprint( $request ) {
		$id      = sanitize_key( (string) $request->get_param( 'demo' ) );
		$builder = sanitize_key( (string) $request->get_param( 'builder' ) );
		$demo    = null;
		foreach ( Demos::manifest()['demos'] as $d ) {
			if ( $d['id'] === $id ) {
				$demo = $d;
			}
		}
		if ( ! self::enabled() || ! $demo || empty( $demo['builders'][ $builder ] ) ) {
			return new \WP_Error( 'not_found', __( 'Live preview is not available for this demo.', 'wpessential-server' ), array( 'status' => 404 ) );
		}

		// Pro packages get a short-lived signed link; the visitor only ever sees a sandbox copy.
		$package = 'pro' === $demo['type'] ? Demos::signed_url( $id, $builder ) : Demos::public_url( $id, $builder );
		$dir     = '/wordpress/wp-content/uploads/wpessential-demos/' . $id;
		$steps   = array(
			array(
				'step'      => 'installTheme',
				'themeData' => array(
					'resource' => 'url',
					'url'      => rest_url( Api::NS . '/theme' ),
				),
				'options'   => array( 'activate' => true ),
			),
		);
		foreach ( (array) $demo['builders'][ $builder ]['plugins'] as $plugin ) {
			if ( 'wporg' !== ( $plugin['source'] ?? 'wporg' ) || 'conflict' === ( $plugin['state'] ?? '' ) ) {
				continue;
			}
			$steps[] = array(
				'step'       => 'installPlugin',
				'pluginData' => array(
					'resource' => 'wordpress.org/plugins',
					'slug'     => sanitize_key( $plugin['slug'] ),
				),
				'options'    => array( 'activate' => true ),
			);
		}
		$steps[] = array(
			'step' => 'mkdir',
			'path' => $dir,
		);
		$steps[] = array(
			'step'          => 'unzip',
			'zipFile'       => array(
				'resource' => 'url',
				'url'      => $package,
			),
			'extractToPath' => $dir . '/' . $builder,
		);
		$steps[] = array(
			'step'    => 'setSiteOptions',
			'options' => array(
				'blogname'               => $demo['title'],
				'permalink_structure'    => '/%postname%/',
				'wpessential_playground' => 1,
			),
		);
		$steps[] = array(
			'step' => 'runPHP',
			'code' => self::import_php( $demo, $builder, $dir ),
		);

		$blueprint = array(
			'$schema'           => 'https://playground.wordpress.net/blueprint-schema.json',
			'meta'              => array(
				'title'  => $demo['title'] . ' — WPEssential',
				'author' => 'WPEssential',
			),
			'landingPage'       => '/',
			'preferredVersions' => array(
				'php' => '8.2',
				'wp'  => 'latest',
			),
			'features'          => array( 'networking' => true ),
			'login'             => true,
			'steps'             => $steps,
		);
		$response  = rest_ensure_response( $blueprint );
		$response->header( 'Cache-Control', 'no-store' );
		return self::cors( $response );
	}

	/**
	 * PHP run inside Playground: register the unzipped package as a local demo and import it
	 * with the same importer customers use.
	 *
	 * @param array  $demo    Manifest entry.
	 * @param string $builder Builder.
	 * @param string $dir     Folder holding <builder>/content.json.
	 * @return string
	 */
	private static function import_php( $demo, $builder, $dir ) {
		$entry = array(
			'id'       => $demo['id'],
			'title'    => $demo['title'],
			'type'     => 'free',
			'dir'      => $dir,
			'builders' => array(
				$builder => array(
					'package' => $builder,
					'plugins' => array(),
				),
			),
		);
		return '<?php
require "/wordpress/wp-load.php";
wp_set_current_user( 1 );
set_transient( "wpessential_core_manifest", array(), HOUR_IN_SECONDS );
$wpe_demo = json_decode( \'' . str_replace( array( '\\', "'" ), array( '\\\\', "\\'" ), (string) wp_json_encode( $entry ) ) . '\', true );
add_filter( "wpessential_core_demos", function ( $demos ) use ( $wpe_demo ) {
	$wpe_demo["source"] = "bundled";
	$wpe_demo["locked"] = false;
	$demos[] = $wpe_demo;
	return $demos;
} );
$started = WPEssential\Core\Importer::start( $wpe_demo["id"], "' . sanitize_key( $builder ) . '", array( "plugins" => false ) );
if ( is_wp_error( $started ) ) {
	echo $started->get_error_message();
	return;
}
for ( $i = 0; $i < 400; $i++ ) {
	$job = WPEssential\Core\Importer::run( 0 );
	if ( "running" !== $job["status"] ) {
		break;
	}
}
';
	}

	/**
	 * Stream the theme zip.
	 *
	 * @return \WP_Error|void
	 */
	public static function theme() {
		$file = self::theme_path();
		if ( ! is_readable( $file ) ) {
			return new \WP_Error( 'not_found', __( 'Theme package not uploaded.', 'wpessential-server' ), array( 'status' => 404 ) );
		}
		self::cors();
		header( 'Content-Type: application/zip' );
		header( 'Content-Length: ' . filesize( $file ) );
		header( 'Content-Disposition: attachment; filename="hi-wpessential.zip"' );
		header( 'Cache-Control: public, max-age=3600' );
		readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Streaming a private file.
		exit;
	}
}
