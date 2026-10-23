<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Platform\Admin\Core\CoreAjaxRoutes;

/**
 * Figma: Core/App Header. The WPEssential top bar shared by every WPEssential admin screen.
 */
final class Shell {

	public const DASHBOARD_SLUG = 'wpessential';
	public const MODULES_SLUG   = 'wpessential-modules';
	public const DEMOS_SLUG     = 'wpessential-demos';
	public const ELEMENTS_SLUG  = 'wpessential-elements';
	public const AI_SLUG        = 'wpessential-ai';
	public const LICENSE_SLUG   = 'wpessential-license';
	public const DOCS_SLUG      = 'wpessential-docs';
	public const STATUS_SLUG    = 'wpessential-status';
	public const UPGRADE_URL    = 'https://wpessential.org/pricing/';
	public const MODE_ACTION    = 'wpessential_ui_mode';
	public const MODE_META      = 'wpessential_ui_mode';
	public const MODES          = [ 'system', 'light', 'dark' ];

	private static bool $premiumAvailable = false;

	private static ?CoreAjaxRoutes $ajax = null;

	public static function configureAjax( CoreAjaxRoutes $ajax ): void {
		self::$ajax = $ajax;
	}

	/** JSON bootstrap that lets ui.ts upgrade the toolkit forms to AJAX. Empty when AJAX is not wired. */
	public static function ajaxBootstrap(): string {
		if ( self::$ajax === null || ! function_exists( 'wp_json_encode' ) || ! function_exists( 'wp_create_nonce' ) ) {
			return '';
		}

		return '<script type="application/json" id="wpe-ui-ajax">' . (string) wp_json_encode( self::$ajax->bootstrap(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES ) . '</script>';
	}

	public static function configure( bool $premiumAvailable ): void {
		self::$premiumAvailable = $premiumAvailable;
	}

	public static function premiumAvailable(): bool {
		return self::$premiumAvailable;
	}

	/**
	 * Top-level screens with their Figma icon. Module builders and overview pages highlight "Modules".
	 *
	 * @return array<string,array{0:string,1:string}>
	 */
	public static function tabs(): array {
		return [
			self::DASHBOARD_SLUG => [ __( 'Dashboard', 'wpessential' ), 'home' ],
			self::MODULES_SLUG   => [ __( 'Modules', 'wpessential' ), 'layers' ],
			self::DEMOS_SLUG     => [ __( 'Demo Library', 'wpessential' ), 'grid' ],
			self::ELEMENTS_SLUG  => [ __( 'Elements', 'wpessential' ), 'brush' ],
			self::AI_SLUG        => [ __( 'AI Connect', 'wpessential' ), 'spark' ],
			self::LICENSE_SLUG   => [ __( 'License', 'wpessential' ), 'key' ],
			self::STATUS_SLUG    => [ __( 'System Status', 'wpessential' ), 'pulse' ],
		];
	}

	public static function version(): string {
		return defined( 'WPE_VERSION' ) ? (string) WPE_VERSION : '0.0.0';
	}

	/** The saved colour mode for the current user: system, light or dark. */
	public static function mode(): string {
		$mode = function_exists( 'get_current_user_id' ) && function_exists( 'get_user_meta' )
			? (string) get_user_meta( (int) get_current_user_id(), self::MODE_META, true )
			: '';

		return in_array( $mode, self::MODES, true ) ? $mode : 'system';
	}

	/** $current is a page slug; module screens pass their own slug and highlight "Modules". */
	public static function topbar( string $current ): string {
		$tabs  = self::tabs();
		$known = array_merge( array_keys( $tabs ), [ self::DOCS_SLUG ] );

		$html  = '<div class="wpe-ui-topbar">';
		$html .= '<a class="wpe-ui-logo" href="' . esc_url( AdminUrl::page( self::DASHBOARD_SLUG ) ) . '">';
		$html .= '<span class="wpe-ui-logo__mark" aria-hidden="true">W<i></i></span><span class="wpe-ui-logo__word">wpessential</span><span class="wpe-ui-logo__tag">' . esc_html__( 'Framework', 'wpessential' ) . '</span>';
		$html .= '<span class="wpe-ui-logo__version" title="' . esc_attr__( 'Plugin version', 'wpessential' ) . '">v' . esc_html( self::version() ) . '</span>';
		$html .= '<span class="screen-reader-text"> ' . esc_html__( 'dashboard', 'wpessential' ) . '</span></a>';
		$html .= '<nav class="wpe-ui-nav" aria-label="' . esc_attr__( 'WPEssential', 'wpessential' ) . '">';
		foreach ( $tabs as $slug => [ $label, $icon ] ) {
			$isCurrent = $current === $slug || ( $slug === self::MODULES_SLUG && ! in_array( $current, $known, true ) );
			$html     .= '<a class="wpe-ui-nav__item' . ( $isCurrent ? ' is-current' : '' ) . '" href="' . esc_url( AdminUrl::page( $slug ) ) . '"' . ( $isCurrent ? ' aria-current="page"' : '' ) . '>' . Icon::render( $icon, 16 ) . '<span>' . esc_html( $label ) . '</span></a>';
		}
		$html .= '</nav><div class="wpe-ui-topbar__end">';

		$docsCurrent = $current === self::DOCS_SLUG;
		$html       .= '<a class="wpe-ui-button wpe-ui-button--ghost wpe-ui-button--sm' . ( $docsCurrent ? ' is-current' : '' ) . '" href="' . esc_url( AdminUrl::page( self::DOCS_SLUG ) ) . '"' . ( $docsCurrent ? ' aria-current="page"' : '' ) . '>' . Icon::render( 'book', 16 ) . esc_html__( 'Docs', 'wpessential' ) . '</a>';
		$html       .= self::modeToggle( $current );
		$html       .= self::$premiumAvailable
			? '<span class="wpe-ui-badge wpe-ui-badge--pro">' . esc_html__( 'Premium active', 'wpessential' ) . '</span>'
			: Button::link( __( 'Go Premium', 'wpessential' ), self::UPGRADE_URL, 'primary', true, true );

		return $html . '</div></div>' . self::ajaxBootstrap() . '<div class="wpe-ui-toast-region" role="status" aria-live="polite" data-wpe-toasts></div>';
	}

	/**
	 * Light/dark switch. Without JavaScript it flips to the opposite of the saved mode; the toolkit
	 * script fills in the opposite of the effective (system-resolved) mode before submitting.
	 */
	private static function modeToggle( string $current ): string {
		$mode   = self::mode();
		$target = $mode === 'dark' ? 'light' : 'dark';
		$action = function_exists( 'admin_url' ) ? (string) admin_url( 'admin-post.php' ) : 'admin-post.php';

		$html  = '<form class="wpe-ui-mode" method="post" action="' . esc_url( $action ) . '" data-wpe-mode-form>';
		$html .= function_exists( 'wp_nonce_field' ) ? (string) wp_nonce_field( self::MODE_ACTION, '_wpnonce', true, false ) : '';
		$html .= '<input type="hidden" name="action" value="' . esc_attr( self::MODE_ACTION ) . '">';
		$html .= '<input type="hidden" name="return" value="' . esc_attr( $current ) . '">';
		$html .= '<input type="hidden" name="mode" value="' . esc_attr( $target ) . '" data-wpe-mode-target>';
		$html .= '<button type="submit" class="wpe-ui-button wpe-ui-button--ghost wpe-ui-button--sm wpe-ui-mode__button" title="' . esc_attr__( 'Switch light / dark mode', 'wpessential' ) . '">';
		$html .= '<span class="wpe-ui-mode__moon">' . Icon::render( 'moon', 16 ) . '</span><span class="wpe-ui-mode__sun">' . Icon::render( 'sun', 16 ) . '</span>';
		$html .= '<span class="screen-reader-text">' . esc_html__( 'Switch light / dark mode', 'wpessential' ) . '</span></button></form>';

		return $html;
	}
}
