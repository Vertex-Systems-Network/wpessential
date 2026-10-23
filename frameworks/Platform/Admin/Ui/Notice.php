<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figma: UI/Notice & toast (Kind=Success / Info / Warning / Error).
 */
final class Notice {

	public const TONES = [ 'success', 'info', 'warning', 'error' ];

	private const ICONS = [
		'success' => 'ok',
		'info'    => 'info',
		'warning' => 'warn',
		'error'   => 'error',
	];

	public static function render( string $tone, string $message, bool $icon = false ): string {
		$tone = in_array( $tone, self::TONES, true ) ? $tone : 'info';
		$body = $icon ? Icon::render( self::ICONS[ $tone ], 18 ) . '<span>' . esc_html( $message ) . '</span>' : esc_html( $message );

		return '<div class="wpe-ui-notice wpe-ui-notice--' . esc_attr( $tone ) . '" role="' . ( $tone === 'error' ? 'alert' : 'status' ) . '">' . $body . '</div>';
	}
}
