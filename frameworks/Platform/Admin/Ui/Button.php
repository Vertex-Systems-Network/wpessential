<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figma: Core/Button. Variants: primary, secondary, ghost, danger; sizes: md, sm.
 */
final class Button {

	public const VARIANTS = [ 'primary', 'secondary', 'ghost', 'danger' ];

	public static function link( string $label, string $url, string $variant = 'primary', bool $small = false, bool $external = false ): string {
		return '<a class="' . esc_attr( self::classes( $variant, $small ) ) . '" href="' . esc_url( $url ) . '"'
			. ( $external ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . esc_html( $label ) . '</a>';
	}

	public static function classes( string $variant, bool $small = false ): string {
		$variant = in_array( $variant, self::VARIANTS, true ) ? $variant : 'ghost';

		return 'wpe-ui-button wpe-ui-button--' . $variant . ( $small ? ' wpe-ui-button--sm' : '' );
	}
}
