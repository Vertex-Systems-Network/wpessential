<?php
/**
 * Plans are WooCommerce products with plan meta.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Plans.
 */
class Plans {

	/**
	 * Percent off twelve monthly payments for yearly billing.
	 */
	const YEARLY_DISCOUNT = 20;

	/**
	 * The owner's own plan: every feature, unlimited websites, never expires. It is not in
	 * defaults(), so it is never listed, priced, sold or offered as an upgrade; only a site
	 * administrator can issue it from WPEssential → Add license.
	 */
	const OWNER = 'owner';

	/**
	 * Owner plan definition.
	 *
	 * @return array
	 */
	public static function owner() {
		return array(
			'name'     => __( 'Owner', 'wpessential-server' ),
			'price'    => '0',
			'months'   => -1,
			'sites'    => 0,
			'white'    => 1,
			'pro'      => 1,
			'features' => array( __( 'Every free and Pro feature', 'wpessential-server' ), __( 'Unlimited websites', 'wpessential-server' ), __( 'Never expires', 'wpessential-server' ) ),
		);
	}

	/**
	 * Product for the owner plan, created on first use. Private, so it cannot be bought.
	 *
	 * @return int Product id.
	 */
	public static function owner_product() {
		$id = self::product_id( self::OWNER );
		if ( $id ) {
			return $id;
		}
		$plan    = self::owner();
		$product = new \WC_Product_Simple();
		/* translators: %s: plan name. */
		$product->set_name( sprintf( __( 'WPEssential %s', 'wpessential-server' ), $plan['name'] ) );
		$product->set_status( 'private' );
		$product->set_regular_price( $plan['price'] );
		$product->set_virtual( true );
		$product->set_sold_individually( true );
		$product->set_catalog_visibility( 'hidden' );
		$product->update_meta_data( '_wpes_plan', self::OWNER );
		$product->update_meta_data( '_wpes_months', $plan['months'] );
		$product->update_meta_data( '_wpes_sites', $plan['sites'] );
		$product->update_meta_data( '_wpes_white_label', $plan['white'] );
		$product->update_meta_data( '_wpes_pro', $plan['pro'] );
		$product->save();
		return $product->get_id();
	}

	/**
	 * The owner plan can never be bought, even if the product is published by mistake.
	 *
	 * @param bool        $purchasable Purchasable.
	 * @param \WC_Product $product     Product.
	 * @return bool
	 */
	public static function owner_not_purchasable( $purchasable, $product ) {
		return self::OWNER === $product->get_meta( '_wpes_plan' ) ? false : $purchasable;
	}

	/**
	 * Default plans. Prices and limits can be changed later on each product.
	 * months: 0 = lifetime (updates for the major version bought, see Licenses::validate()), -1 = free (no expiry, no billing).
	 *
	 * @return array
	 */
	public static function defaults() {
		$plans = array(
			'free' => array(
				'name'     => __( 'Free', 'wpessential-server' ),
				'price'    => '0',
				'months'   => -1,
				'sites'    => 1,
				'white'    => 0,
				'pro'      => 0,
				'features' => array( __( 'Free demos', 'wpessential-server' ), __( '1 website', 'wpessential-server' ), __( 'Community support', 'wpessential-server' ) ),
			),
		);
		// Each tier sells monthly, yearly (20% off twelve months) and lifetime (one major version).
		$tiers   = array(
			'starter' => array(
				'name'     => __( 'Starter', 'wpessential-server' ),
				'monthly'  => 10,
				'lifetime' => 239,
				'sites'    => 1,
				'white'    => 0,
				'features' => array( __( '1 theme + its demos', 'wpessential-server' ), __( 'Gutenberg & Elementor', 'wpessential-server' ), __( '1 website', 'wpessential-server' ), __( 'Updates & support', 'wpessential-server' ) ),
			),
			'pro'     => array(
				'name'     => __( 'Pro', 'wpessential-server' ),
				'monthly'  => 30,
				'lifetime' => 719,
				'sites'    => 5,
				'white'    => 0,
				'features' => array( __( 'All themes + every demo', 'wpessential-server' ), __( 'Early access to new demos', 'wpessential-server' ), __( '5 websites', 'wpessential-server' ), __( 'Priority support', 'wpessential-server' ) ),
			),
			'agency'  => array(
				'name'     => __( 'Agency', 'wpessential-server' ),
				'monthly'  => 100,
				'lifetime' => 2399,
				'sites'    => 0,
				'white'    => 1,
				'features' => array( __( 'Everything in Pro', 'wpessential-server' ), __( 'Unlimited websites', 'wpessential-server' ), __( 'White-label dashboard', 'wpessential-server' ), __( 'Dedicated success manager', 'wpessential-server' ) ),
			),
		);
		$billing = array(
			/* translators: %s: plan name. */
			'monthly'  => array( __( '%s Monthly', 'wpessential-server' ), 1 ),
			/* translators: %s: plan name. */
			'yearly'   => array( __( '%s Yearly', 'wpessential-server' ), 12 ),
			/* translators: %s: plan name. */
			'lifetime' => array( __( '%s Lifetime', 'wpessential-server' ), 0 ),
		);
		foreach ( $tiers as $tier => $t ) {
			foreach ( $billing as $period => $b ) {
				if ( 'lifetime' === $period ) {
					$price = $t['lifetime'];
				} else {
					$price = 'yearly' === $period ? $t['monthly'] * 12 * ( 100 - self::YEARLY_DISCOUNT ) / 100 : $t['monthly'];
				}
				$plans[ $tier . '_' . $period ] = array(
					'name'     => sprintf( $b[0], $t['name'] ),
					'price'    => (string) round( $price ),
					'months'   => $b[1],
					'sites'    => $t['sites'],
					'white'    => $t['white'],
					'pro'      => 1,
					'features' => $t['features'],
				);
			}
		}
		return $plans;
	}

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'fields' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save' ) );
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'one_plan_in_cart' ), 10, 2 );
		add_filter( 'woocommerce_is_purchasable', array( __CLASS__, 'owner_not_purchasable' ), 10, 2 );
	}

	/**
	 * Create one virtual product per plan (skips plans that already have one).
	 */
	public static function create_products() {
		foreach ( self::defaults() as $key => $plan ) {
			if ( self::product_id( $key ) ) {
				continue;
			}
			$product = new \WC_Product_Simple();
			/* translators: %s: plan name. */
			$product->set_name( sprintf( __( 'WPEssential %s', 'wpessential-server' ), $plan['name'] ) );
			$product->set_regular_price( $plan['price'] );
			$product->set_virtual( true );
			$product->set_sold_individually( true );
			$product->set_catalog_visibility( 'hidden' );
			$product->set_short_description( implode( ' · ', $plan['features'] ) );
			$product->update_meta_data( '_wpes_plan', $key );
			$product->update_meta_data( '_wpes_months', $plan['months'] );
			$product->update_meta_data( '_wpes_sites', $plan['sites'] );
			$product->update_meta_data( '_wpes_white_label', $plan['white'] );
			$product->update_meta_data( '_wpes_pro', $plan['pro'] );
			$product->save();
		}
	}

	/**
	 * Product id for a plan key.
	 *
	 * @param string $key Plan key.
	 * @return int
	 */
	public static function product_id( $key ) {
		$ids = wc_get_products(
			array(
				'limit'      => 1,
				'status'     => array( 'publish', 'private' ),
				'return'     => 'ids',
				'meta_key'   => '_wpes_plan', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Small set.
				'meta_value' => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Small set.
			)
		);
		return $ids ? (int) $ids[0] : 0;
	}

	/**
	 * Plan data from a product.
	 *
	 * @param \WC_Product|int $product Product.
	 * @return array|null
	 */
	public static function from_product( $product ) {
		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
		if ( ! $product || ! $product->get_meta( '_wpes_plan' ) ) {
			return null;
		}
		$key      = $product->get_meta( '_wpes_plan' );
		$defaults = self::OWNER === $key ? self::owner() : ( self::defaults()[ $key ] ?? array( 'features' => array() ) );
		return array(
			'key'         => $key,
			'product_id'  => $product->get_id(),
			'name'        => $defaults['name'] ?? $product->get_name(),
			'price'       => (float) $product->get_price(),
			'currency'    => get_woocommerce_currency(),
			'months'      => (int) $product->get_meta( '_wpes_months' ),
			'sites'       => (int) $product->get_meta( '_wpes_sites' ),
			'white_label' => (bool) $product->get_meta( '_wpes_white_label' ),
			'pro'         => (bool) $product->get_meta( '_wpes_pro' ),
			'features'    => $defaults['features'],
		);
	}

	/**
	 * All plans in display order.
	 *
	 * @return array
	 */
	public static function all() {
		$plans = array();
		foreach ( array_keys( self::defaults() ) as $key ) {
			$id = self::product_id( $key );
			if ( $id ) {
				$plans[] = self::from_product( $id );
			}
		}
		return $plans;
	}

	/**
	 * Rank used for upgrades (higher = bigger plan).
	 *
	 * @param string $key Plan key.
	 * @return int
	 */
	public static function rank( $key ) {
		$rank = array_flip( array_keys( self::defaults() ) );
		return $rank[ $key ] ?? 0;
	}

	/**
	 * Plan fields in the General tab.
	 */
	public static function fields() {
		global $post;
		echo '<div class="options_group wpes-plan-fields">';
		woocommerce_wp_select(
			array(
				'id'      => '_wpes_plan',
				'label'   => __( 'Plan', 'wpessential-server' ),
				'options' => array( '' => __( 'Not a plan', 'wpessential-server' ) ) + wp_list_pluck( self::defaults(), 'name' ) + ( self::OWNER === get_post_meta( $post->ID, '_wpes_plan', true ) ? array( self::OWNER => self::owner()['name'] ) : array() ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'                => '_wpes_months',
				'label'             => __( 'Billing period (months)', 'wpessential-server' ),
				'description'       => __( '0 = lifetime, -1 = free.', 'wpessential-server' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => -1 ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => '_wpes_sites',
				'label'       => __( 'Websites', 'wpessential-server' ),
				'description' => __( '0 = unlimited. Staging and local domains are not counted.', 'wpessential-server' ),
				'type'        => 'number',
			)
		);
		woocommerce_wp_checkbox(
			array(
				'id'          => '_wpes_pro',
				'label'       => __( 'Pro demos', 'wpessential-server' ),
				'description' => __( 'Unlocks Pro demos and premium plugins.', 'wpessential-server' ),
				'cbvalue'     => '1',
				'value'       => get_post_meta( $post->ID, '_wpes_pro', true ),
			)
		);
		woocommerce_wp_checkbox(
			array(
				'id'          => '_wpes_white_label',
				'label'       => __( 'White label', 'wpessential-server' ),
				'description' => __( 'Customer can rename the dashboard and hide WPEssential branding.', 'wpessential-server' ),
				'cbvalue'     => '1',
				'value'       => get_post_meta( $post->ID, '_wpes_white_label', true ),
			)
		);
		echo '</div>';
	}

	/**
	 * Save plan fields.
	 *
	 * @param int $post_id Product.
	 */
	public static function save( $post_id ) {
		// Nonce checked by WooCommerce before this action.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$plan = isset( $_POST['_wpes_plan'] ) ? sanitize_key( wp_unslash( $_POST['_wpes_plan'] ) ) : '';
		update_post_meta( $post_id, '_wpes_plan', array_key_exists( $plan, self::defaults() ) || self::OWNER === $plan ? $plan : '' );
		update_post_meta( $post_id, '_wpes_months', isset( $_POST['_wpes_months'] ) ? (int) $_POST['_wpes_months'] : 0 );
		update_post_meta( $post_id, '_wpes_sites', isset( $_POST['_wpes_sites'] ) ? max( 0, (int) $_POST['_wpes_sites'] ) : 1 );
		update_post_meta( $post_id, '_wpes_pro', empty( $_POST['_wpes_pro'] ) ? 0 : 1 );
		update_post_meta( $post_id, '_wpes_white_label', empty( $_POST['_wpes_white_label'] ) ? 0 : 1 );
		// phpcs:enable
	}

	/**
	 * Keep one plan per cart so licenses map cleanly to orders.
	 *
	 * @param bool $valid      Valid.
	 * @param int  $product_id Product.
	 * @return bool
	 */
	public static function one_plan_in_cart( $valid, $product_id ) {
		if ( ! self::from_product( $product_id ) || ! WC()->cart ) {
			return $valid;
		}
		foreach ( WC()->cart->get_cart() as $key => $item ) {
			if ( self::from_product( $item['product_id'] ) ) {
				WC()->cart->remove_cart_item( $key );
			}
		}
		return $valid;
	}
}
