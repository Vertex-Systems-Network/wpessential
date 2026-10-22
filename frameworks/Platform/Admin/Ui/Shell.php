<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figma: Core/App Header. The WPEssential top bar shared by every WPEssential admin screen.
 */
final class Shell {

	public const DASHBOARD_SLUG = 'wpessential';
	public const MODULES_SLUG   = 'wpessential-modules';
	public const STATUS_SLUG    = 'wpessential-status';
	public const UPGRADE_URL    = 'https://wpessential.org/pricing/';

	private static bool $premiumAvailable = false;

	public static function configure( bool $premiumAvailable ): void {
		self::$premiumAvailable = $premiumAvailable;
	}

	public static function premiumAvailable(): bool {
		return self::$premiumAvailable;
	}

	/** $current is a page slug; module screens pass their own slug and highlight "Modules". */
	public static function topbar( string $current ): string {
		$html  = '<div class="wpe-ui-topbar">';
		$html .= '<a class="wpe-ui-logo" href="' . esc_url( AdminUrl::page( self::DASHBOARD_SLUG ) ) . '">';
		$html .= '<span class="wpe-ui-logo__mark" aria-hidden="true">W<i></i></span><span class="wpe-ui-logo__word">wpessential</span><span class="wpe-ui-logo__tag">' . esc_html__( 'Framework', 'wpessential' ) . '</span>';
		$html .= '<span class="screen-reader-text"> ' . esc_html__( 'dashboard', 'wpessential' ) . '</span></a>';
		$html .= '<nav class="wpe-ui-nav" aria-label="' . esc_attr__( 'WPEssential', 'wpessential' ) . '">';
		foreach (
			[
				self::DASHBOARD_SLUG => __( 'Dashboard', 'wpessential' ),
				self::MODULES_SLUG   => __( 'Modules', 'wpessential' ),
				self::STATUS_SLUG    => __( 'System Status', 'wpessential' ),
			] as $slug => $label
		) {
			$isCurrent = $current === $slug || ( $slug === self::MODULES_SLUG && ! in_array( $current, [ self::DASHBOARD_SLUG, self::STATUS_SLUG ], true ) );
			$html     .= '<a class="wpe-ui-nav__item' . ( $isCurrent ? ' is-current' : '' ) . '" href="' . esc_url( AdminUrl::page( $slug ) ) . '"' . ( $isCurrent ? ' aria-current="page"' : '' ) . '>' . esc_html( $label ) . '</a>';
		}
		$html .= '</nav><div class="wpe-ui-topbar__end">';
		$html .= self::$premiumAvailable
			? '<span class="wpe-ui-badge wpe-ui-badge--pro">' . esc_html__( 'Premium active', 'wpessential' ) . '</span>'
			: Button::link( __( 'Go Premium', 'wpessential' ), self::UPGRADE_URL, 'primary', true, true );

		return $html . '</div></div>';
	}
}
