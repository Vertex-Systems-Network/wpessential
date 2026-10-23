<?php
/**
 * Demo library: demos are posts (wpes_demo), packages live in a private folder.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Demos + packages.
 */
class Demos {

	const CPT = 'wpes_demo';

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'boxes' ) );
		add_action( 'save_post_' . self::CPT, array( __CLASS__, 'save' ) );
		add_action( 'post_edit_form_tag', array( __CLASS__, 'multipart' ) );
	}

	/**
	 * Post type.
	 */
	public static function register() {
		register_post_type(
			self::CPT,
			array(
				'labels'       => array(
					'name'          => __( 'Demos', 'wpessential-server' ),
					'singular_name' => __( 'Demo', 'wpessential-server' ),
					'add_new_item'  => __( 'Add demo', 'wpessential-server' ),
					'edit_item'     => __( 'Edit demo', 'wpessential-server' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'wpes-server',
				'supports'     => array( 'title', 'thumbnail', 'page-attributes' ),
				'menu_icon'    => 'dashicons-layout',
			)
		);
	}

	/**
	 * Allow file inputs on the demo screen.
	 *
	 * @param \WP_Post $post Post.
	 */
	public static function multipart( $post ) {
		if ( self::CPT === $post->post_type ) {
			echo ' enctype="multipart/form-data"';
		}
	}

	/**
	 * Meta boxes.
	 */
	public static function boxes() {
		add_meta_box( 'wpes-demo', __( 'Demo details', 'wpessential-server' ), array( __CLASS__, 'box' ), self::CPT, 'normal', 'high' );
	}

	/**
	 * Fields.
	 *
	 * @param \WP_Post $post Post.
	 */
	public static function box( $post ) {
		wp_nonce_field( 'wpes_demo', 'wpes_demo_nonce' );
		$m     = static function ( $key ) use ( $post ) {
			return (string) get_post_meta( $post->ID, '_wpes_' . $key, true );
		};
		$field = static function ( $key, $label, $help = '' ) use ( $m ) {
			printf( '<p><label><strong>%1$s</strong><br><input type="text" class="widefat" name="wpes[%2$s]" value="%3$s"></label>%4$s</p>', esc_html( $label ), esc_attr( $key ), esc_attr( $m( $key ) ), $help ? '<span class="description">' . esc_html( $help ) . '</span>' : '' );
		};
		$field( 'demo_id', __( 'Demo ID', 'wpessential-server' ), __( 'Lowercase, e.g. maison. Must match the package.', 'wpessential-server' ) );
		$field( 'tagline', __( 'Tagline', 'wpessential-server' ) );
		$field( 'niche', __( 'Niches', 'wpessential-server' ), __( 'Comma separated: business, shop, fashion, events…', 'wpessential-server' ) );
		$field( 'pages', __( 'Pages', 'wpessential-server' ), __( 'Comma separated, shown in the preview.', 'wpessential-server' ) );
		$field( 'highlights', __( 'Highlights', 'wpessential-server' ), __( 'Comma separated, e.g. 9 pages, 24 products.', 'wpessential-server' ) );
		$field( 'preview_url', __( 'Live preview URL', 'wpessential-server' ) );
		$field( 'min_theme', __( 'Minimum theme version', 'wpessential-server' ) );
		echo '<p><label><strong>' . esc_html__( 'Plan', 'wpessential-server' ) . '</strong><br><select name="wpes[type]"><option value="free"' . selected( $m( 'type' ), 'free', false ) . '>' . esc_html__( 'Free', 'wpessential-server' ) . '</option><option value="pro"' . selected( $m( 'type' ), 'pro', false ) . '>' . esc_html__( 'Pro', 'wpessential-server' ) . '</option></select></label></p>';

		foreach ( array(
			'gutenberg' => 'Gutenberg',
			'elementor' => 'Elementor',
		) as $builder => $label ) {
			$file = self::package_path( $m( 'demo_id' ), $builder );
			echo '<hr><h3>' . esc_html( $label ) . '</h3>';
			echo '<p>' . ( $file && is_readable( $file ) ? '✅ ' . esc_html( sprintf( /* translators: %s: size. */ __( 'Package uploaded (%s)', 'wpessential-server' ), size_format( filesize( $file ) ) ) ) : esc_html__( 'No package yet — this builder is hidden from the library.', 'wpessential-server' ) ) . '</p>';
			echo '<p><input type="file" accept=".zip" name="wpes_package_' . esc_attr( $builder ) . '"></p>';
			echo '<p><label><strong>' . esc_html__( 'Plugins (JSON)', 'wpessential-server' ) . '</strong><br><textarea class="widefat code" rows="4" name="wpes[plugins_' . esc_attr( $builder ) . ']">' . esc_textarea( $m( 'plugins_' . $builder ) ) . '</textarea></label><span class="description">[{"slug":"woocommerce","name":"WooCommerce","source":"wporg","state":"required"}]</span></p>';
		}
	}

	/**
	 * Save fields and uploaded packages.
	 *
	 * @param int $post_id Post.
	 */
	public static function save( $post_id ) {
		if ( ! isset( $_POST['wpes_demo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpes_demo_nonce'] ) ), 'wpes_demo' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$data = isset( $_POST['wpes'] ) ? (array) wp_unslash( $_POST['wpes'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitised per field below.
		foreach ( array( 'tagline', 'niche', 'pages', 'highlights', 'min_theme' ) as $key ) {
			update_post_meta( $post_id, '_wpes_' . $key, sanitize_text_field( $data[ $key ] ?? '' ) );
		}
		update_post_meta( $post_id, '_wpes_demo_id', sanitize_key( $data['demo_id'] ?? '' ) );
		update_post_meta( $post_id, '_wpes_preview_url', esc_url_raw( $data['preview_url'] ?? '' ) );
		update_post_meta( $post_id, '_wpes_type', ( isset( $data['type'] ) && 'pro' === $data['type'] ) ? 'pro' : 'free' );
		foreach ( array( 'gutenberg', 'elementor' ) as $builder ) {
			$json = json_decode( (string) ( $data[ 'plugins_' . $builder ] ?? '' ), true );
			update_post_meta( $post_id, '_wpes_plugins_' . $builder, is_array( $json ) ? wp_json_encode( $json ) : '' );
			$field = 'wpes_package_' . $builder;
			if ( ! empty( $_FILES[ $field ]['tmp_name'] ) && isset( $_FILES[ $field ]['error'], $_FILES[ $field ]['name'] ) && UPLOAD_ERR_OK === $_FILES[ $field ]['error'] ) {
				$demo = sanitize_key( $data['demo_id'] ?? '' );
				$name = sanitize_file_name( wp_unslash( $_FILES[ $field ]['name'] ) );
				if ( $demo && '.zip' === strtolower( substr( $name, -4 ) ) ) {
					move_uploaded_file( $_FILES[ $field ]['tmp_name'], self::package_path( $demo, $builder ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,Generic.PHP.ForbiddenFunctions.Found -- Uploaded zip moved to the private folder.
				}
			}
		}
	}

	/**
	 * Find or create the library entry for a demo id.
	 *
	 * @param string $demo Demo id.
	 * @return int Post ID.
	 */
	public static function ensure( $demo ) {
		$ids = get_posts( array( 'post_type' => self::CPT, 'post_status' => 'any', 'meta_key' => '_wpes_demo_id', 'meta_value' => $demo, 'fields' => 'ids', 'numberposts' => 1 ) ); // phpcs:ignore WordPress.DB.SlowDBQuery, WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound -- Admin-only release call.
		if ( $ids ) {
			return (int) $ids[0];
		}
		$id = wp_insert_post(
			array(
				'post_type'   => self::CPT,
				'post_status' => 'draft',
				'post_title'  => ucfirst( $demo ),
			)
		);
		update_post_meta( $id, '_wpes_demo_id', $demo );
		return (int) $id;
	}

	/**
	 * Fill a library entry from the demo.json and thumbnail inside a package, without
	 * overwriting anything already entered in the admin. A new entry is published once
	 * it has its details, so the demo shows up in the theme right away.
	 *
	 * @param string $demo    Demo id.
	 * @param string $builder Builder of the package.
	 * @param string $zip     Package path.
	 */
	public static function sync_details( $demo, $builder, $zip ) {
		$id      = self::ensure( $demo );
		$archive = new \ZipArchive();
		if ( ! $id || true !== $archive->open( $zip ) ) {
			return;
		}
		$info  = json_decode( (string) $archive->getFromName( 'demo.json' ), true );
		$thumb = $archive->getFromName( 'thumbnail.webp' );
		$archive->close();
		if ( ! is_array( $info ) ) {
			return;
		}
		$csv     = static function ( $value ) {
			return sanitize_text_field( implode( ', ', array_map( 'strval', (array) $value ) ) );
		};
		$fill    = array(
			'tagline'    => sanitize_text_field( (string) ( $info['tagline'] ?? '' ) ),
			'niche'      => $csv( $info['niche'] ?? array() ),
			'pages'      => $csv( $info['pages'] ?? array() ),
			'highlights' => $csv( $info['highlights'] ?? array() ),
			'type'       => 'pro' === ( $info['type'] ?? '' ) ? 'pro' : 'free',
		);
		$plugins = $info['builders'][ $builder ]['plugins'] ?? null;
		if ( is_array( $plugins ) ) {
			$fill[ 'plugins_' . $builder ] = wp_json_encode( $plugins );
		}
		foreach ( $fill as $key => $value ) {
			if ( '' === (string) get_post_meta( $id, '_wpes_' . $key, true ) && '' !== $value ) {
				update_post_meta( $id, '_wpes_' . $key, $value );
			}
		}
		if ( $thumb && ! has_post_thumbnail( $id ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$tmp = wp_tempnam( $demo . '.webp' );
			file_put_contents( $tmp, $thumb ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Temp file.
			$file  = array(
				'name'     => $demo . '-thumbnail.webp',
				'tmp_name' => $tmp,
			);
			$media = media_handle_sideload( $file, $id, (string) ( $info['title'] ?? $demo ) );
			if ( ! is_wp_error( $media ) ) {
				set_post_thumbnail( $id, $media );
			}
		}
		$post = get_post( $id );
		if ( $post && 'draft' === $post->post_status ) {
			wp_update_post(
				array(
					'ID'          => $id,
					'post_title'  => sanitize_text_field( (string) ( $info['title'] ?? $post->post_title ) ),
					'post_status' => 'publish',
				)
			);
		}
	}

	/**
	 * Thumbnail stored inside a demo's package, for entries without a featured image.
	 *
	 * @param string $demo Demo id.
	 * @return string Image bytes, or '' when no package has one.
	 */
	public static function package_image( $demo ) {
		foreach ( array( 'gutenberg', 'elementor' ) as $builder ) {
			$zip  = new \ZipArchive();
			$path = self::package_path( $demo, $builder );
			if ( $path && is_readable( $path ) && true === $zip->open( $path ) ) {
				$image = $zip->getFromName( 'thumbnail.webp' );
				$zip->close();
				if ( $image ) {
					return $image;
				}
			}
		}
		return '';
	}

	/**
	 * Private path of a package.
	 *
	 * @param string $demo    Demo id or "plugin".
	 * @param string $builder Builder or plugin slug.
	 * @return string
	 */
	public static function package_path( $demo, $builder ) {
		if ( ! $demo ) {
			return '';
		}
		return Install::protect_packages() . '/' . sanitize_key( $demo ) . '-' . sanitize_key( $builder ) . '.zip';
	}

	/**
	 * Manifest in the format the theme's Core module reads.
	 *
	 * @return array
	 */
	public static function manifest() {
		$posts = get_posts(
			array(
				'post_type'   => self::CPT,
				'post_status' => 'publish',
				'numberposts' => 200, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts -- Whole catalogue, cached by clients.
				'orderby'     => array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				),
			)
		);
		$demos = array();
		foreach ( $posts as $post ) {
			$m        = static function ( $key ) use ( $post ) {
				return (string) get_post_meta( $post->ID, '_wpes_' . $key, true );
			};
			$id       = $m( 'demo_id' );
			$list     = static function ( $value ) {
				return array_values( array_filter( array_map( 'trim', explode( ',', $value ) ) ) );
			};
			$builders = array();
			foreach ( array( 'gutenberg', 'elementor' ) as $builder ) {
				if ( ! is_readable( self::package_path( $id, $builder ) ) ) {
					continue;
				}
				$builders[ $builder ] = array(
					'package' => 'pro' === $m( 'type' ) ? 'licensed' : self::public_url( $id, $builder ),
					'plugins' => json_decode( $m( 'plugins_' . $builder ), true ) ? json_decode( $m( 'plugins_' . $builder ), true ) : array(),
				);
			}
			if ( ! $id || ! $builders ) {
				continue;
			}
			$demos[] = array(
				'id'                => $id,
				'title'             => get_the_title( $post ),
				'tagline'           => $m( 'tagline' ),
				'niche'             => array_map( 'sanitize_key', $list( $m( 'niche' ) ) ),
				'type'              => $m( 'type' ) ? $m( 'type' ) : 'free',
				'min_theme_version' => $m( 'min_theme' ),
				'preview_url'       => $m( 'preview_url' ),
				'thumbnail'         => has_post_thumbnail( $post ) ? (string) get_the_post_thumbnail_url( $post, 'large' ) : add_query_arg( 'demo', $id, rest_url( Api::NS . '/thumbnail' ) ),
				'pages'             => $list( $m( 'pages' ) ),
				'highlights'        => $list( $m( 'highlights' ) ),
				'builders'          => $builders,
				'playground'        => Playground::enabled() ? array_combine(
					array_keys( $builders ),
					array_map(
						static function ( $builder ) use ( $id ) {
							return Playground::open_url( $id, $builder );
						},
						array_keys( $builders )
					)
				) : array(),
			);
		}
		return array(
			'version' => '1',
			'demos'   => $demos,
		);
	}

	/**
	 * Unsigned URL for a free package.
	 *
	 * @param string $demo    Demo.
	 * @param string $builder Builder.
	 * @return string
	 */
	public static function public_url( $demo, $builder ) {
		return add_query_arg(
			array(
				'demo'    => $demo,
				'builder' => $builder,
			),
			rest_url( Api::NS . '/download' )
		);
	}

	/**
	 * Is a demo Pro?
	 *
	 * @param string $demo Demo id.
	 * @return bool|null Null when unknown.
	 */
	public static function is_pro( $demo ) {
		foreach ( self::manifest()['demos'] as $d ) {
			if ( $d['id'] === $demo ) {
				return 'pro' === $d['type'];
			}
		}
		return null;
	}

	/**
	 * Short-lived signed URL.
	 *
	 * @param string $demo    Demo id or "plugin".
	 * @param string $builder Builder or plugin slug.
	 * @return string
	 */
	public static function signed_url( $demo, $builder ) {
		$exp = time() + 60 * (int) Plugin::settings( 'download_mins' );
		return add_query_arg(
			array(
				'demo'    => $demo,
				'builder' => $builder,
				'exp'     => $exp,
				'sig'     => hash_hmac( 'sha256', $demo . '|' . $builder . '|' . $exp, Plugin::secret() ),
			),
			rest_url( Api::NS . '/download' )
		);
	}

	/**
	 * Stream a package.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_Error|void
	 */
	public static function download( $request ) {
		$demo    = sanitize_key( (string) $request->get_param( 'demo' ) );
		$builder = sanitize_key( (string) $request->get_param( 'builder' ) );
		$free    = 'plugin' !== $demo && false === self::is_pro( $demo );
		if ( ! $free ) {
			$exp = (int) $request->get_param( 'exp' );
			$sig = (string) $request->get_param( 'sig' );
			if ( $exp < time() || ! hash_equals( hash_hmac( 'sha256', $demo . '|' . $builder . '|' . $exp, Plugin::secret() ), $sig ) ) {
				return new \WP_Error( 'link_expired', __( 'This download link has expired. Start the import again.', 'wpessential-server' ), array( 'status' => 403 ) );
			}
		}
		$file = self::package_path( $demo, $builder );
		if ( ! $file || ! is_readable( $file ) ) {
			return new \WP_Error( 'not_found', __( 'Package not found.', 'wpessential-server' ), array( 'status' => 404 ) );
		}
		nocache_headers();
		Playground::cors();
		header( 'Content-Type: application/zip' );
		header( 'Content-Length: ' . filesize( $file ) );
		header( 'Content-Disposition: attachment; filename="' . basename( $file ) . '"' );
		readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Streaming a private file.
		exit;
	}
}
