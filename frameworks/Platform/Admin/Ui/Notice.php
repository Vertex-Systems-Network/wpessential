<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figma: Core/Notice.
 */
final class Notice {

	public const TONES = [ 'success', 'info', 'warning', 'error' ];

	public static function render( string $tone, string $message ): string {
		$tone = in_array( $tone, self::TONES, true ) ? $tone : 'info';

		return '<div class="wpe-ui-notice wpe-ui-notice--' . esc_attr( $tone ) . '" role="' . ( $tone === 'error' ? 'alert' : 'status' ) . '">' . esc_html( $message ) . '</div>';
	}
}
