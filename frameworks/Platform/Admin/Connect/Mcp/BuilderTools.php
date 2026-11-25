<?php
/**
 * Builder tools for the WPEssential MCP server: Elementor documents and Kit, WooCommerce
 * products and pages, global styles (theme.json) and a page render check, so an assistant can
 * build a demo to its Figma design in Gutenberg and Elementor without raw REST calls.
 *
 * @package WPEssential
 */

namespace WPEssential\Platform\Admin\Connect\Mcp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extra MCP tools, added through the wpessential_mcp_tools filter.
 */
final class BuilderTools {

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_filter( 'wpessential_mcp_tools', array( __CLASS__, 'tools' ) );
	}

	/**
	 * Tool definitions.
	 *
	 * @param array $tools Tools so far.
	 * @return array
	 */
	public static function tools( $tools ) {
		$obj  = static fn( array $props, array $required = array() ) => array_filter(
			array(
				'type'       => 'object',
				'properties' => $props ? $props : new \stdClass(),
				'required'   => $required,
			)
		);
		$str  = static fn( $d ) => array(
			'type'        => 'string',
			'description' => $d,
		);
		$int  = static fn( $d ) => array(
			'type'        => 'integer',
			'description' => $d,
		);
		$any  = static fn( $d ) => array(
			'type'        => 'object',
			'description' => $d,
		);
		$arr  = static fn( $d ) => array(
			'type'        => 'array',
			'items'       => array( 'type' => 'object' ),
			'description' => $d,
		);
		$tool = static fn( $desc, $schema, $run ) => array(
			'description' => $desc,
			'inputSchema' => $schema,
			'run'         => array( __CLASS__, $run ),
		);

		return array_merge(
			(array) $tools,
			array(
				'block_templates'  => $tool(
					'Gutenberg site editor: list templates and template parts (header, footer…), or pass id (e.g. hi-wpessential//footer) with content to save block markup into it.',
					$obj(
						array(
							'type'    => $str( 'wp_template or wp_template_part. Default wp_template_part.' ),
							'id'      => $str( 'Template id theme//slug.' ),
							'content' => $str( 'Block markup to save.' ),
						)
					),
					'block_templates'
				),
				'block_patterns'   => $tool(
					'Registered block patterns (name, title, categories). Pass name to get its markup.',
					$obj(
						array(
							'name'   => $str( 'Pattern name.' ),
							'search' => $str( 'Optional words.' ),
						)
					),
					'block_patterns'
				),
				'block_check'      => $tool(
					'Parse block markup (or a post by id) and report the block tree, unknown blocks, and Custom HTML / freeform blocks that break the core-blocks-only rule.',
					$obj(
						array(
							'id'      => $int( 'Post ID.' ),
							'content' => $str( 'Or block markup.' ),
						)
					),
					'block_check'
				),
				'elementor_get'    => $tool( 'Elementor document of a page or template: elements tree (_elementor_data), page settings and edit mode.', $obj( array( 'id' => $int( 'Post ID.' ) ), array( 'id' ) ), 'elementor_get' ),
				'elementor_save'   => $tool(
					'Save an Elementor document. data is the full elements array (containers and native widgets, same shape as elementor_get). Switches the page to Elementor and clears the CSS cache.',
					$obj(
						array(
							'id'            => $int( 'Post ID.' ),
							'data'          => $arr( 'Elements tree.' ),
							'page_settings' => $any( 'Optional page settings.' ),
						),
						array( 'id', 'data' )
					),
					'elementor_save'
				),
				'elementor_kit'    => $tool( 'Read the active Elementor Kit (global colours, fonts, layout). Pass settings to merge new values in, e.g. {"system_colors":[…],"custom_colors":[…],"container_width":{"size":1200,"unit":"px"}}.', $obj( array( 'settings' => $any( 'Kit settings to merge.' ) ) ), 'elementor_kit' ),
				'global_styles'    => $tool(
					'Read the merged theme.json (palette, fonts, spacing, layout) and the user global styles. Pass settings and/or styles to merge into the user global styles.',
					$obj(
						array(
							'settings' => $any( 'theme.json settings to merge.' ),
							'styles'   => $any( 'theme.json styles to merge.' ),
						)
					),
					'global_styles'
				),
				'woo_products'     => $tool(
					'List WooCommerce products with price, categories, image and link.',
					$obj(
						array(
							'search'   => $str( 'Optional search words.' ),
							'category' => $str( 'Optional category slug.' ),
							'per_page' => $int( 'Default 50.' ),
						)
					),
					'woo_products'
				),
				'woo_save_product' => $tool(
					'Create (no id) or update a simple WooCommerce product. categories are names or slugs (created when missing); image is an attachment ID.',
					$obj(
						array(
							'id'                => $int( 'Product ID to update.' ),
							'name'              => $str( 'Name.' ),
							'regular_price'     => $str( 'e.g. 49.00' ),
							'sale_price'        => $str( 'Optional.' ),
							'description'       => $str( 'Long description (HTML or blocks).' ),
							'short_description' => $str( 'Short description.' ),
							'sku'               => $str( 'SKU.' ),
							'categories'        => array(
								'type'  => 'array',
								'items' => array( 'type' => 'string' ),
							),
							'image'             => $int( 'Attachment ID.' ),
							'virtual'           => array( 'type' => 'boolean' ),
							'downloadable'      => array( 'type' => 'boolean' ),
							'status'            => $str( 'publish or draft.' ),
						)
					),
					'woo_save_product'
				),
				'woo_pages'        => $tool( 'Make sure the WooCommerce Shop, Cart, Checkout and My account pages exist and return their IDs and links.', $obj( array() ), 'woo_pages' ),
				'render_page'      => $tool(
					'Fetch a page as a visitor and report status, title, headings, h1 count, images without alt, links and, when html is true, the HTML (first 200 KB). Use it to check a page against its design.',
					$obj(
						array(
							'id'   => $int( 'Post ID.' ),
							'url'  => $str( 'Or a URL on this site.' ),
							'html' => array( 'type' => 'boolean' ),
						)
					),
					'render_page'
				),
			)
		);
	}

	/**
	 * List or save block templates and template parts.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function block_templates( array $a ) {
		$type = 'wp_template' === ( $a['type'] ?? '' ) ? 'wp_template' : 'wp_template_part';
		if ( empty( $a['id'] ) ) {
			return array_map(
				static fn( $t ) => array(
					'id'     => $t->id,
					'title'  => $t->title,
					'area'   => $t->area ?? '',
					'source' => $t->source,
				),
				get_block_templates( array(), $type )
			);
		}
		$tpl = get_block_template( (string) $a['id'], $type );
		if ( ! $tpl ) {
			return new \WP_Error( 'wpe_mcp_404', 'Template not found.' );
		}
		if ( ! isset( $a['content'] ) ) {
			return array(
				'id'      => $tpl->id,
				'title'   => $tpl->title,
				'source'  => $tpl->source,
				'content' => $tpl->content,
			);
		}
		$base = 'wp_template' === $type ? '/wp/v2/templates/' : '/wp/v2/template-parts/';
		return SiteTools::tool_rest(
			array(
				'method' => 'POST',
				'path'   => $base . $tpl->id,
				'body'   => array( 'content' => (string) $a['content'] ),
			)
		);
	}

	/**
	 * Registered patterns.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function block_patterns( array $a ) {
		$reg = \WP_Block_Patterns_Registry::get_instance();
		if ( ! empty( $a['name'] ) ) {
			$p = $reg->get_registered( (string) $a['name'] );
			return $p ? $p : new \WP_Error( 'wpe_mcp_404', 'Pattern not found.' );
		}
		$words = strtolower( (string) ( $a['search'] ?? '' ) );
		$out   = array();
		foreach ( $reg->get_all_registered() as $p ) {
			if ( $words && false === strpos( strtolower( $p['name'] . ' ' . $p['title'] ), $words ) ) {
				continue;
			}
			$out[] = array(
				'name'       => $p['name'],
				'title'      => $p['title'],
				'categories' => $p['categories'] ?? array(),
			);
		}
		return $out;
	}

	/**
	 * Parse and audit block markup.
	 *
	 * @param array $a Arguments.
	 * @return array
	 */
	public static function block_check( array $a ) {
		$content = isset( $a['content'] ) ? (string) $a['content'] : (string) get_post_field( 'post_content', (int) ( $a['id'] ?? 0 ) );
		$reg     = \WP_Block_Type_Registry::get_instance();
		$out     = array(
			'blocks'  => 0,
			'tree'    => array(),
			'unknown' => array(),
			'html'    => 0,
		);
		$walk    = static function ( $blocks, $depth ) use ( &$walk, &$out, $reg ) {
			foreach ( $blocks as $b ) {
				if ( null === $b['blockName'] ) {
					if ( '' !== trim( $b['innerHTML'] ) ) {
						++$out['html'];
					}
					continue;
				}
				++$out['blocks'];
				if ( 'core/html' === $b['blockName'] || 'core/freeform' === $b['blockName'] ) {
					++$out['html'];
				}
				if ( ! $reg->is_registered( $b['blockName'] ) ) {
					$out['unknown'][] = $b['blockName'];
				}
				if ( $depth < 4 ) {
					$out['tree'][] = str_repeat( '  ', $depth ) . $b['blockName'] . ( empty( $b['attrs']['className'] ) ? '' : ' .' . $b['attrs']['className'] );
				}
				$walk( $b['innerBlocks'], $depth + 1 );
			}
		};
		$walk( parse_blocks( $content ), 0 );
		$out['unknown'] = array_values( array_unique( $out['unknown'] ) );
		return $out;
	}

	/**
	 * Fail unless Elementor is active.
	 *
	 * @return \WP_Error|null
	 */
	private static function need_elementor() {
		return did_action( 'elementor/loaded' ) ? null : new \WP_Error( 'wpe_mcp_elementor', 'Elementor is not active. Use install_plugin with slug elementor.' );
	}

	/**
	 * Fail unless WooCommerce is active.
	 *
	 * @return \WP_Error|null
	 */
	private static function need_woo() {
		return class_exists( 'WooCommerce' ) ? null : new \WP_Error( 'wpe_mcp_woo', 'WooCommerce is not active. Use install_plugin with slug woocommerce.' );
	}

	/**
	 * Elementor document.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function elementor_get( array $a ) {
		$id = (int) ( $a['id'] ?? 0 );
		if ( ! get_post( $id ) ) {
			return new \WP_Error( 'wpe_mcp_404', 'Not found.' );
		}
		return array(
			'id'            => $id,
			'edit_mode'     => get_post_meta( $id, '_elementor_edit_mode', true ),
			'template_type' => get_post_meta( $id, '_elementor_template_type', true ),
			'page_settings' => (array) get_post_meta( $id, '_elementor_page_settings', true ),
			'data'          => json_decode( (string) get_post_meta( $id, '_elementor_data', true ), true ),
		);
	}

	/**
	 * Save an Elementor document.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function elementor_save( array $a ) {
		$err = self::need_elementor();
		if ( $err ) {
			return $err;
		}
		$id = (int) ( $a['id'] ?? 0 );
		if ( ! get_post( $id ) ) {
			return new \WP_Error( 'wpe_mcp_404', 'Not found.' );
		}
		update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( array_values( (array) ( $a['data'] ?? array() ) ) ) ) );
		update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		if ( ! get_post_meta( $id, '_elementor_template_type', true ) ) {
			update_post_meta( $id, '_elementor_template_type', 'wp-' . get_post_type( $id ) );
		}
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			update_post_meta( $id, '_elementor_version', ELEMENTOR_VERSION );
		}
		if ( isset( $a['page_settings'] ) && is_array( $a['page_settings'] ) ) {
			update_post_meta( $id, '_elementor_page_settings', $a['page_settings'] );
		}
		self::clear_elementor_cache();
		return array(
			'id'   => $id,
			'link' => get_permalink( $id ),
		);
	}

	/**
	 * Read or merge the active Kit.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function elementor_kit( array $a ) {
		$err = self::need_elementor();
		if ( $err ) {
			return $err;
		}
		$kit = (int) get_option( 'elementor_active_kit' );
		if ( ! $kit ) {
			return new \WP_Error( 'wpe_mcp_kit', 'No active Elementor Kit.' );
		}
		$settings = (array) get_post_meta( $kit, '_elementor_page_settings', true );
		if ( ! empty( $a['settings'] ) && is_array( $a['settings'] ) ) {
			$settings = array_merge( $settings, $a['settings'] );
			update_post_meta( $kit, '_elementor_page_settings', $settings );
			self::clear_elementor_cache();
		}
		return array(
			'kit'      => $kit,
			'settings' => $settings,
		);
	}

	/**
	 * Clear Elementor's generated CSS.
	 */
	private static function clear_elementor_cache() {
		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}

	/**
	 * Read or merge global styles.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function global_styles( array $a ) {
		$post_id = \WP_Theme_JSON_Resolver::get_user_global_styles_post_id();
		$user    = json_decode( (string) get_post_field( 'post_content', $post_id ), true );
		$user    = is_array( $user ) ? $user : array(
			'version'                     => 2,
			'isGlobalStylesUserThemeJSON' => true,
		);
		$changed = false;
		foreach ( array( 'settings', 'styles' ) as $key ) {
			if ( ! empty( $a[ $key ] ) && is_array( $a[ $key ] ) ) {
				$user[ $key ] = array_replace_recursive( (array) ( $user[ $key ] ?? array() ), $a[ $key ] );
				$changed      = true;
			}
		}
		if ( $changed ) {
			$saved = wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => wp_slash( wp_json_encode( $user ) ),
				),
				true
			);
			if ( is_wp_error( $saved ) ) {
				return $saved;
			}
			if ( class_exists( '\WP_Theme_JSON_Resolver' ) ) {
				\WP_Theme_JSON_Resolver::clean_cached_data();
			}
		}
		$merged = \WP_Theme_JSON_Resolver::get_merged_data()->get_raw_data();
		return array(
			'user'   => $user,
			'merged' => array(
				'settings' => $merged['settings'] ?? array(),
				'styles'   => $merged['styles'] ?? array(),
			),
		);
	}

	/**
	 * Product summary.
	 *
	 * @param \WC_Product $p Product.
	 * @return array
	 */
	private static function product_row( $p ) {
		return array(
			'id'            => $p->get_id(),
			'name'          => $p->get_name(),
			'slug'          => $p->get_slug(),
			'status'        => $p->get_status(),
			'regular_price' => $p->get_regular_price(),
			'sale_price'    => $p->get_sale_price(),
			'sku'           => $p->get_sku(),
			'categories'    => wp_get_post_terms( $p->get_id(), 'product_cat', array( 'fields' => 'slugs' ) ),
			'image'         => $p->get_image_id(),
			'link'          => get_permalink( $p->get_id() ),
		);
	}

	/**
	 * List products.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function woo_products( array $a ) {
		$err = self::need_woo();
		if ( $err ) {
			return $err;
		}
		$query = array(
			'limit'  => min( 200, (int) ( $a['per_page'] ?? 50 ) ),
			'status' => array( 'publish', 'draft', 'private' ),
		);
		if ( ! empty( $a['search'] ) ) {
			$query['s'] = (string) $a['search'];
		}
		if ( ! empty( $a['category'] ) ) {
			$query['category'] = array( sanitize_title( $a['category'] ) );
		}
		return array_map( array( __CLASS__, 'product_row' ), wc_get_products( $query ) );
	}

	/**
	 * Create or update a simple product.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function woo_save_product( array $a ) {
		$err = self::need_woo();
		if ( $err ) {
			return $err;
		}
		$p = empty( $a['id'] ) ? new \WC_Product_Simple() : wc_get_product( (int) $a['id'] );
		if ( ! $p ) {
			return new \WP_Error( 'wpe_mcp_404', 'Product not found.' );
		}
		$setters = array(
			'name'              => 'set_name',
			'regular_price'     => 'set_regular_price',
			'sale_price'        => 'set_sale_price',
			'description'       => 'set_description',
			'short_description' => 'set_short_description',
			'sku'               => 'set_sku',
			'virtual'           => 'set_virtual',
			'downloadable'      => 'set_downloadable',
			'image'             => 'set_image_id',
		);
		foreach ( $setters as $key => $setter ) {
			if ( isset( $a[ $key ] ) ) {
				$p->$setter( $a[ $key ] );
			}
		}
		$p->set_status( isset( $a['status'] ) ? sanitize_key( $a['status'] ) : ( empty( $a['id'] ) ? 'publish' : $p->get_status() ) );
		if ( isset( $a['categories'] ) ) {
			$ids = array();
			foreach ( (array) $a['categories'] as $cat ) {
				$term = get_term_by( 'slug', sanitize_title( $cat ), 'product_cat' );
				if ( ! $term ) {
					$term = get_term_by( 'name', $cat, 'product_cat' );
				}
				if ( ! $term ) {
					$made = wp_insert_term( $cat, 'product_cat' );
					if ( is_wp_error( $made ) ) {
						return $made;
					}
					$ids[] = (int) $made['term_id'];
				} else {
					$ids[] = (int) $term->term_id;
				}
			}
			$p->set_category_ids( $ids );
		}
		$p->save();
		return self::product_row( $p );
	}

	/**
	 * Ensure the WooCommerce pages.
	 *
	 * @return array|\WP_Error
	 */
	public static function woo_pages() {
		$err = self::need_woo();
		if ( $err ) {
			return $err;
		}
		if ( class_exists( '\WC_Install' ) ) {
			\WC_Install::create_pages();
		}
		$out = array();
		foreach ( array( 'shop', 'cart', 'checkout', 'myaccount' ) as $page ) {
			$id           = wc_get_page_id( $page );
			$out[ $page ] = array(
				'id'   => $id,
				'link' => $id > 0 ? get_permalink( $id ) : '',
			);
		}
		return $out;
	}

	/**
	 * Fetch a page as a visitor and summarise it.
	 *
	 * @param array $a Arguments.
	 * @return array|\WP_Error
	 */
	public static function render_page( array $a ) {
		$url = ! empty( $a['id'] ) ? get_permalink( (int) $a['id'] ) : (string) ( $a['url'] ?? home_url( '/' ) );
		if ( ! $url || 0 !== strpos( $url, home_url() ) ) {
			return new \WP_Error( 'wpe_mcp_url', 'Give a post ID or a URL on this site.' );
		}
		$res = wp_remote_get(
			$url,
			array(
				'timeout'   => 30,
				'sslverify' => false,
			)
		);
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		$html = (string) wp_remote_retrieve_body( $res );
		$out  = array(
			'url'    => $url,
			'status' => wp_remote_retrieve_response_code( $res ),
			'bytes'  => strlen( $html ),
		);
		$dom  = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( '<?xml encoding="utf-8"?>' . $html );
		libxml_clear_errors();
		$title           = $dom->getElementsByTagName( 'title' )->item( 0 );
		$out['title']    = $title ? trim( $title->textContent ) : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API.
		$out['h1']       = $dom->getElementsByTagName( 'h1' )->length;
		$out['headings'] = array();
		$xpath           = new \DOMXPath( $dom );
		foreach ( $xpath->query( '//h1|//h2|//h3' ) as $h ) {
			$out['headings'][] = $h->nodeName . ': ' . trim( preg_replace( '/\s+/', ' ', $h->textContent ) ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOM API.
		}
		$out['images_without_alt'] = array();
		foreach ( $dom->getElementsByTagName( 'img' ) as $img ) {
			if ( '' === trim( $img->getAttribute( 'alt' ) ) && 'presentation' !== $img->getAttribute( 'role' ) ) {
				$out['images_without_alt'][] = $img->getAttribute( 'src' );
			}
		}
		$out['links'] = $dom->getElementsByTagName( 'a' )->length;
		if ( ! empty( $a['html'] ) ) {
			$out['html'] = substr( $html, 0, 200000 );
		}
		return $out;
	}
}
