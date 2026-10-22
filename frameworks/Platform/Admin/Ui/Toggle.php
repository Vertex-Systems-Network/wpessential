<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figma: Core/Toggle (On / Off / Locked). Submits through a nonce-protected admin-post form.
 */
final class Toggle {

	/** @param array<string,string> $hidden */
	public static function form( string $action, string $nonceAction, bool $enabled, string $accessibleLabel, array $hidden = [] ): string {
		$html  = '<form class="wpe-ui-toggle-form" method="post" action="' . esc_url( function_exists( 'admin_url' ) ? (string) admin_url( 'admin-post.php' ) : 'admin-post.php' ) . '">';
		$html .= function_exists( 'wp_nonce_field' ) ? (string) wp_nonce_field( $nonceAction, '_wpnonce', true, false ) : '';
		$html .= '<input type="hidden" name="action" value="' . esc_attr( $action ) . '">';
		foreach ( $hidden as $name => $value ) {
			$html .= '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
		}
		$html .= '<input type="hidden" name="enabled" value="' . ( $enabled ? '0' : '1' ) . '">';
		$html .= '<button type="submit" class="wpe-ui-toggle" role="switch" aria-checked="' . ( $enabled ? 'true' : 'false' ) . '" aria-label="' . esc_attr( $accessibleLabel ) . '">';
		$html .= '<span class="wpe-ui-toggle__track"><span class="wpe-ui-toggle__thumb"></span></span>';
		$html .= '<span>' . ( $enabled ? esc_html__( 'On', 'wpessential' ) : esc_html__( 'Off', 'wpessential' ) ) . '</span></button></form>';

		return $html;
	}

	public static function locked( string $upgradeUrl ): string {
		return '<a class="wpe-ui-toggle wpe-ui-toggle--locked" href="' . esc_url( $upgradeUrl ) . '" target="_blank" rel="noopener noreferrer">' . Icon::render( 'lock', 14 ) . esc_html__( 'Premium', 'wpessential' ) . '</a>';
	}
}
