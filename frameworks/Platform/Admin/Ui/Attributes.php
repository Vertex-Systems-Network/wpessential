<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Escaped HTML attribute string for the toolkit components. true renders a bare attribute;
 * false and null are skipped.
 */
final class Attributes {

	/** @param array<string,string|int|bool|null> $attrs */
	public static function render( array $attrs ): string {
		$html = '';
		foreach ( $attrs as $name => $value ) {
			if ( $value === false || $value === null ) {
				continue;
			}
			$html .= ' ' . esc_attr( $name ) . ( $value === true ? '' : '="' . esc_attr( (string) $value ) . '"' );
		}

		return $html;
	}
}
