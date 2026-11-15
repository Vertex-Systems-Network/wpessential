<?php
/**
 * Minimal WooCommerce, WPForms and Elementor API used by the demo importer
 * (frameworks/Platform/Demos/Import), for PHPStan only. The importer calls them only when the
 * plugin is active.
 *
 * @phpcs:disable
 */

namespace {
	class WC_Product {
		/** @param mixed $v */ public function set_name( $v ): void {}
		/** @param mixed $v */ public function set_status( $v ): void {}
		/** @param mixed $v */ public function set_regular_price( $v ): void {}
		/** @param mixed $v */ public function set_sale_price( $v ): void {}
		/** @param mixed $v */ public function set_attributes( $v ): void {}
		/** @param mixed $v */ public function set_parent_id( $v ): void {}
		/** @param mixed $v */ public function set_slug( $v ): void {}
		/** @param mixed $v */ public function set_short_description( $v ): void {}
		/** @param mixed $v */ public function set_description( $v ): void {}
		/** @param mixed $v */ public function set_featured( $v ): void {}
		/** @param mixed $v */ public function set_virtual( $v ): void {}
		/** @param mixed $v */ public function set_sold_individually( $v ): void {}
		/** @param mixed $v */ public function set_menu_order( $v ): void {}
		/** @param mixed $v */ public function set_stock_status( $v ): void {}
		/** @param mixed $v */ public function set_manage_stock( $v ): void {}
		/** @param mixed $v */ public function set_stock_quantity( $v ): void {}
		/** @param mixed $v */ public function set_category_ids( $v ): void {}
		/** @param mixed $v */ public function set_image_id( $v ): void {}
		/** @param mixed $v */ public function set_gallery_image_ids( $v ): void {}
		/** @param mixed $v */ public function set_children( $v ): void {}
		/** @param mixed $v */ public function update_meta_data( string $k, $v ): void {}
		/** @return list<int> */ public function get_children(): array { return array(); }
		public function save(): int { return 0; }
		public function get_id(): int { return 0; }
	}
	class WC_Product_Simple extends WC_Product {}
	class WC_Product_Grouped extends WC_Product {}
	class WC_Product_Variable extends WC_Product {
		/** @param mixed $product */ public static function sync( $product, bool $save = true ): void {}
	}
	class WC_Product_Variation extends WC_Product {}
	class WC_Product_Attribute {
		/** @param mixed $v */ public function set_id( $v ): void {}
		/** @param mixed $v */ public function set_name( $v ): void {}
		/** @param mixed $v */ public function set_options( $v ): void {}
		/** @param mixed $v */ public function set_visible( $v ): void {}
		/** @param mixed $v */ public function set_variation( $v ): void {}
	}
	class WC_Shipping_Zone {
		public function __construct( int $id = 0 ) {}
		public function set_zone_name( string $n ): void {}
		public function add_shipping_method( string $type ): int { return 0; }
		public function save(): int { return 0; }
	}
	class WC_Shipping_Zones {
		/** @return array<int,array<string,mixed>> */ public static function get_zones(): array { return array(); }
		/** @return object|false */ public static function get_shipping_method( int $id ) { return false; }
	}
	function wc_attribute_taxonomy_name( string $name ): string { return ''; }
	function wc_attribute_taxonomy_id_by_name( string $name ): int { return 0; }
	/** @param array<string,mixed> $args @return int|\WP_Error */
	function wc_create_attribute( array $args ) { return 0; }
	/** @return object */
	function wpforms() { return new \stdClass(); }
}

namespace Elementor {
	class Plugin {
		/** @var object */
		public static $instance;
	}
}

namespace Elementor\Core\Kits {
	class Manager {
		public static function create_default_kit(): int { return 0; }
	}
}

namespace {
	/** Polylang API used by the site tools (frameworks/Platform/SiteTools), PHPStan only. */
	/** @param array<string,mixed> $args @return mixed */
	function pll_the_languages( array $args = array() ) { return array(); }
	function pll_default_language( string $field = 'slug' ): string { return ''; }
	/** @return string|false */
	function pll_get_post_language( int $id, string $field = 'slug' ) { return false; }
	/** @return string|false */
	function pll_get_term_language( int $id, string $field = 'slug' ) { return false; }
	function pll_is_translated_post_type( string $type ): bool { return false; }
	function pll_is_translated_taxonomy( string $tax ): bool { return false; }
	function pll_set_post_language( int $id, string $lang ): void {}
	function pll_set_term_language( int $id, string $lang ): void {}
	function pll_home_url( string $lang = '' ): string { return ''; }
}
