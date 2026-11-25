<?php
/**
 * Site tools of the WPEssential MCP server, ported from the Hi WPEssential theme (core/src/Mcp.php):
 * site overview, any REST route, content list/get/save, plugin install, WPForms forms and demos.
 *
 * @package WPEssential
 */

namespace WPEssential\Platform\Admin\Connect\Mcp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tool definitions: name => array( description, inputSchema, run ). The wpessential_mcp_tools filter
 * extends them (BuilderTools adds Gutenberg, Elementor and WooCommerce tools).
 */
final class SiteTools {

	/** Tools that change the site: offered only while "Allow AI to change settings" is on. */
	const WRITES = array( 'save_content', 'install_plugin', 'create_form', 'import_demo', 'elementor_save', 'woo_save_product', 'woo_pages' );

	/**
	 * Notes the assistant receives on connect.
	 *
	 * @return string
	 */
	public static function instructions() {
		return 'You are connected to the WordPress site "' . get_bloginfo( 'name' ) . '" (' . home_url( '/' ) . ') running the WPEssential plugin (' . get_template() . ' theme). '
			. 'Pages and posts are block markup (Gutenberg). Use site_info first. Prefer theme patterns and the theme.json palette (slugs: primary, secondary, ink, surface, surface-alt, text, text-muted). '
			. 'wp_rest calls any WordPress REST route as the connected administrator (e.g. /wp/v2/pages, /wp/v2/templates, /wp/v2/global-styles, /wc/v3/products, /wpforms routes when available). '
			. 'Use create_form for forms (WPForms), import_demo for one-click demos. Ask before deleting content.';
	}

	/**
	 * Tool definitions: name => array( description, inputSchema, run ).
	 *
	 * @return array
	 */
	public static function tools() {
		$obj  = static function ( array $props, array $required = array() ) {
			return array_filter(
				array(
					'type'       => 'object',
					'properties' => $props ? $props : new \stdClass(),
					'required'   => $required,
				)
			);
		};
		$str  = static fn( $d ) => array(
			'type'        => 'string',
			'description' => $d,
		);
		$int  = static fn( $d ) => array(
			'type'        => 'integer',
			'description' => $d,
		);
		$list = array(
			'site_info'      => array(
				'description' => 'Site name, URL, WordPress and theme versions, active plugins, front page, menus and the theme style variations.',
				'inputSchema' => $obj( array() ),
				'run'         => array( __CLASS__, 'tool_site_info' ),
			),
			'wp_rest'        => array(
				'description' => 'Call any WordPress REST API route on this site as the connected administrator. Example: {"method":"POST","path":"/wp/v2/pages","body":{"title":"About","status":"publish","content":"<!-- wp:paragraph --><p>Hi</p><!-- /wp:paragraph -->"}}.',
				'inputSchema' => $obj(
					array(
						'method' => array(
							'type' => 'string',
							'enum' => array( 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ),
						),
						'path'   => $str( 'Route, e.g. /wp/v2/pages?per_page=20' ),
						'body'   => array(
							'type'        => 'object',
							'description' => 'JSON body for POST/PUT/PATCH.',
						),
					),
					array( 'method', 'path' )
				),
				'run'         => array( __CLASS__, 'tool_rest' ),
			),
			'list_content'   => array(
				'description' => 'List posts, pages, products or any post type with id, title, status, slug and link.',
				'inputSchema' => $obj(
					array(
						'post_type' => $str( 'post, page, product, wp_template, wp_template_part… Default page.' ),
						'search'    => $str( 'Optional search words.' ),
						'per_page'  => $int( 'Default 50.' ),
					)
				),
				'run'         => array( __CLASS__, 'tool_list' ),
			),
			'get_content'    => array(
				'description' => 'Full block markup and details of one post, page or product.',
				'inputSchema' => $obj( array( 'id' => $int( 'Post ID.' ) ), array( 'id' ) ),
				'run'         => array( __CLASS__, 'tool_get' ),
			),
			'save_content'   => array(
				'description' => 'Create (no id) or update a post, page or other post type with block markup.',
				'inputSchema' => $obj(
					array(
						'id'        => $int( 'Existing post ID to update.' ),
						'post_type' => $str( 'Default page.' ),
						'title'     => $str( 'Title.' ),
						'content'   => $str( 'Block markup.' ),
						'status'    => $str( 'publish, draft, private.' ),
						'slug'      => $str( 'URL slug.' ),
					)
				),
				'run'         => array( __CLASS__, 'tool_save' ),
			),
			'install_plugin' => array(
				'description' => 'Install (from WordPress.org) and activate a plugin by slug, e.g. wpforms-lite, woocommerce.',
				'inputSchema' => $obj( array( 'slug' => $str( 'WordPress.org plugin slug.' ) ), array( 'slug' ) ),
				'run'         => array( __CLASS__, 'tool_plugin' ),
			),
			'create_form'    => array(
				'description' => 'Create a WPForms form (installs WPForms Lite if missing). Fields: [{"type":"name|email|text|textarea|select|radio|checkbox|number|phone|url","label":"…","required":true,"choices":["A","B"]}]. Returns the form id and its shortcode.',
				'inputSchema' => $obj(
					array(
						'title'   => $str( 'Form title.' ),
						'fields'  => array(
							'type'  => 'array',
							'items' => array( 'type' => 'object' ),
						),
						'submit'  => $str( 'Submit button text.' ),
						'message' => $str( 'Confirmation message.' ),
					),
					array( 'title', 'fields' )
				),
				'run'         => array( __CLASS__, 'tool_form' ),
			),
			'list_demos'     => array(
				'description' => 'Demos available in the WPEssential library (id, title, builders, free or pro).',
				'inputSchema' => $obj( array() ),
				'run'         => array( __CLASS__, 'tool_demos' ),
			),
			'import_demo'    => array(
				'description' => 'Import a WPEssential demo (pages, products, menus, images, style). Replaces the previous demo\'s content.',
				'inputSchema' => $obj(
					array(
						'demo'    => $str( 'Demo id from list_demos.' ),
						'builder' => $str( 'gutenberg or elementor.' ),
					),
					array( 'demo', 'builder' )
				),
				'run'         => array( __CLASS__, 'tool_import' ),
			),
		);
		$list = (array) apply_filters( 'wpessential_mcp_tools', $list );
		foreach ( $list as $name => $tool ) {
			$list[ $name ]['name'] = $name;
		}
		return $list;
	}

	// Tools.

	/**
	 * Site overview.
	 *
	 * @return array
	 */
	public static function tool_site_info() {
		global $wp_version;
		$theme = wp_get_theme();
		$menus = get_posts(
			array(
				'post_type'   => 'wp_navigation',
				'numberposts' => 20,
			)
		);
		return array(
			'name'             => get_bloginfo( 'name' ),
			'tagline'          => get_bloginfo( 'description' ),
			'url'              => home_url( '/' ),
			'wordpress'        => $wp_version,
			'theme'            => $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ),
			'style_variations' => array_map( static fn( $f ) => basename( $f, '.json' ), (array) glob( get_template_directory() . '/styles/*.json' ) ),
			'front_page'       => (int) get_option( 'page_on_front' ),
			'posts_page'       => (int) get_option( 'page_for_posts' ),
			'active_plugins'   => array_values( (array) get_option( 'active_plugins', array() ) ),
			'navigation_menus' => array_map(
				static fn( $p ) => array(
					'id'    => $p->ID,
					'title' => $p->post_title,
				),
				$menus
			),
			'woocommerce'      => class_exists( 'WooCommerce' ),
			'wpforms'          => function_exists( 'wpforms' ),
		);
	}

	/**
	 * Internal REST request.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function tool_rest( array $a ) {
		$path  = '/' . ltrim( (string) ( $a['path'] ?? '' ), '/' );
		$parts = wp_parse_url( $path );
		$req   = new \WP_REST_Request( strtoupper( (string) ( $a['method'] ?? 'GET' ) ), $parts['path'] ?? '/' );
		if ( ! empty( $parts['query'] ) ) {
			parse_str( $parts['query'], $query );
			$req->set_query_params( $query );
		}
		if ( ! empty( $a['body'] ) && is_array( $a['body'] ) ) {
			$req->set_header( 'content-type', 'application/json' );
			$req->set_body( wp_json_encode( $a['body'] ) );
			$req->set_body_params( $a['body'] );
		}
		$res = rest_do_request( $req );
		return array(
			'status' => $res->get_status(),
			'data'   => rest_get_server()->response_to_data( $res, false ),
		);
	}

	/**
	 * List content.
	 *
	 * @param array $a Arguments.
	 * @return array
	 */
	public static function tool_list( array $a ) {
		$posts = get_posts(
			array(
				'post_type'   => sanitize_key( $a['post_type'] ?? 'page' ),
				'post_status' => 'any',
				's'           => (string) ( $a['search'] ?? '' ),
				'numberposts' => min( 200, (int) ( $a['per_page'] ?? 50 ) ),
			)
		);
		return array_map(
			static fn( $p ) => array(
				'id'     => $p->ID,
				'title'  => $p->post_title,
				'status' => $p->post_status,
				'slug'   => $p->post_name,
				'link'   => get_permalink( $p ),
			),
			$posts
		);
	}

	/**
	 * One post.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function tool_get( array $a ) {
		$p = get_post( (int) ( $a['id'] ?? 0 ) );
		if ( ! $p ) {
			return new \WP_Error( 'wpe_mcp_404', 'Not found.' );
		}
		return array(
			'id'        => $p->ID,
			'post_type' => $p->post_type,
			'title'     => $p->post_title,
			'status'    => $p->post_status,
			'slug'      => $p->post_name,
			'link'      => get_permalink( $p ),
			'template'  => get_page_template_slug( $p ),
			'content'   => $p->post_content,
		);
	}

	/**
	 * Create or update.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function tool_save( array $a ) {
		$data = array_filter(
			array(
				'ID'           => (int) ( $a['id'] ?? 0 ),
				'post_type'    => empty( $a['id'] ) ? sanitize_key( $a['post_type'] ?? 'page' ) : null,
				'post_title'   => isset( $a['title'] ) ? sanitize_text_field( $a['title'] ) : null,
				'post_content' => $a['content'] ?? null,
				'post_status'  => isset( $a['status'] ) ? sanitize_key( $a['status'] ) : ( empty( $a['id'] ) ? 'draft' : null ),
				'post_name'    => isset( $a['slug'] ) ? sanitize_title( $a['slug'] ) : null,
			),
			static fn( $v ) => null !== $v && 0 !== $v
		);
		$id   = empty( $data['ID'] ) ? wp_insert_post( wp_slash( $data ), true ) : wp_update_post( wp_slash( $data ), true );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		return array(
			'id'   => $id,
			'link' => get_permalink( $id ),
		);
	}

	/**
	 * Install and activate a plugin.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function tool_plugin( array $a ) {
		$slug = sanitize_key( $a['slug'] ?? '' );
		$res  = \WPEssential\Platform\Demos\Import\Plugins::apply(
			array(
				'slug'   => $slug,
				'name'   => $slug,
				'source' => 'wporg',
				'state'  => 'required',
			)
		);
		return is_wp_error( $res ) ? $res : array( 'active' => $slug );
	}

	/**
	 * Create a WPForms form.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function tool_form( array $a ) {
		$id = \WPEssential\Platform\Demos\Import\Forms::create( (string) ( $a['title'] ?? 'Form' ), (array) ( $a['fields'] ?? array() ), (string) ( $a['submit'] ?? '' ), (string) ( $a['message'] ?? '' ) );
		return is_wp_error( $id ) ? $id : array(
			'id'        => $id,
			'shortcode' => '[wpforms id="' . $id . '"]',
			'block'     => '<!-- wp:wpforms/form-selector {"formId":"' . $id . '"} /-->',
		);
	}

	/**
	 * Demo list.
	 *
	 * @return array
	 */
	public static function tool_demos() {
		return array_map(
			static fn( $d ) => array(
				'id'       => $d['id'],
				'title'    => $d['title'] ?? $d['id'],
				'type'     => $d['type'] ?? 'free',
				'builders' => array_keys( (array) ( $d['builders'] ?? array() ) ),
				'locked'   => ! empty( $d['locked'] ),
			),
			(array) ( \WPEssential\Platform\Demos\Import\Demos::all()['demos'] ?? array() )
		);
	}

	/**
	 * Import a demo to the end.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function tool_import( array $a ) {
		$job = \WPEssential\Platform\Demos\Import\Importer::start(
			sanitize_key( $a['demo'] ?? '' ),
			sanitize_key( $a['builder'] ?? 'gutenberg' ),
			array(
				'plugins'  => 1,
				'media'    => 1,
				'content'  => 1,
				'products' => 1,
				'settings' => 1,
				'reset'    => 1,
			)
		);
		if ( is_wp_error( $job ) ) {
			return $job;
		}
		$state = array();
		for ( $i = 0; $i < 400; $i++ ) {
			$state = \WPEssential\Platform\Demos\Import\Importer::run();
			if ( is_wp_error( $state ) || 'running' !== ( $state['status'] ?? '' ) ) {
				break;
			}
		}
		return is_wp_error( $state ) ? $state : array(
			'status'     => $state['status'] ?? '',
			'front_page' => home_url( '/' ),
		);
	}
}
