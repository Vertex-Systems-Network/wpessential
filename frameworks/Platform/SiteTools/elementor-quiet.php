<?php
/**
 * Quiet Elementor admin: hides Elementor's promotional notices, notification feed,
 * dashboard widget and onboarding redirect. On by default only with the Hi WPEssential
 * theme (as before it moved here); turn on or off with the wpessential_elementor_quiet_admin filter.
 *
 * @package WPEssential
 */

namespace WPEssential\Platform\SiteTools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Quiet wp-admin: no Elementor banners, promotions or notification feeds while this theme is active.
 * Errors that block Elementor itself (PHP/WordPress version) and database update notices stay.
 * Turn off with add_filter( 'wpessential_elementor_quiet_admin', '__return_false' ).
 */
if ( is_admin() && apply_filters( 'wpessential_elementor_quiet_admin', 'hi-wpessential' === get_template() ) ) {
	// Notice classes added by Elementor and its add-ons, the notifications bell feed and editor announcements.
	add_filter( 'elementor/core/admin/notices', '__return_empty_array', PHP_INT_MAX );
	add_filter( 'elementor/core/admin/notifications', '__return_empty_array', PHP_INT_MAX );
	add_filter( 'elementor/announcements/raw_announcements', '__return_empty_array', PHP_INT_MAX );

	/**
	 * Remove Elementor's own admin notice printers (rating, tracking, connect) and promotion banners
	 * (e.g. "Elevate your site with Elementor Pro" in the header, the rating request in the footer).
	 */
	function wpessential_elementor_remove_notices() {
		global $wp_filter;
		foreach ( array( 'admin_notices', 'all_admin_notices', 'network_admin_notices', 'in_admin_header', 'admin_footer_text' ) as $hook ) {
			if ( empty( $wp_filter[ $hook ] ) ) {
				continue;
			}
			foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					$function = $callback['function'];
					$object   = is_array( $function ) && is_object( $function[0] ) ? $function[0] : null;
					if ( ! $object ) {
						continue;
					}
					$class = get_class( $object );
					if ( 0 !== strpos( $class, 'Elementor' ) || false !== stripos( $class, 'Upgrade' ) || false !== stripos( $class, 'TemplateLibrary' ) ) {
						continue;
					}
					// In the header keep Elementor's navigation (top bar, sidebar); drop only promotions.
					if ( 'in_admin_header' === $hook && false === strpos( $class, 'Promotions' ) ) {
						continue;
					}
					remove_action( $hook, $function, $priority );
				}
			}
		}
	}
	add_action( 'current_screen', __NAMESPACE__ . '\wpessential_elementor_remove_notices', PHP_INT_MAX );

	// No "Elementor Overview" dashboard widget.
	add_action(
		'wp_dashboard_setup',
		static function () {
			remove_meta_box( 'e-dashboard-overview', 'dashboard', 'normal' );
		},
		PHP_INT_MAX
	);

	// No redirect to Elementor's welcome screen after activation, and no onboarding prompt.
	add_action(
		'admin_init',
		static function () {
			delete_transient( 'elementor_activation_redirect' );
		},
		1
	);
	add_filter( 'pre_option_elementor_onboarded', '__return_true' );

	// Promotional boxes Elementor prints without a hook (dismissible promos, "Get Pro" plugin row link).
	add_action(
		'admin_head',
		static function () {
			echo '<style id="wpessential-elementor-quiet">.e-notice--dismissible,.e-notice--extended,.elementor-plugins-gopro,#e-conversion-banner{display:none!important}</style>';
		}
	);
}
