<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figma: Core/Status Pill.
 */
final class StatusPill {

	public const TONES = [ 'running', 'off', 'locked', 'soon', 'waiting' ];

	public static function render( string $tone, string $label ): string {
		$tone = in_array( $tone, self::TONES, true ) ? $tone : 'off';

		return '<span class="wpe-ui-pill wpe-ui-pill--' . esc_attr( $tone ) . '">' . esc_html( $label ) . '</span>';
	}
}
