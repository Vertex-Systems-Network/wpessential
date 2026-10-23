<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figma: UI/Check chip. A checkbox inside a pill-shaped label.
 */
final class Check {

	/** @param array<string,string|int|bool|null> $attrs Attributes of the checkbox input. */
	public static function render( string $label, array $attrs = [], bool $checked = false ): string {
		$for = isset( $attrs['id'] ) ? ' for="' . esc_attr( (string) $attrs['id'] ) . '"' : '';

		return '<label class="wpe-ui-check"' . $for . '><input' . Attributes::render( [ 'type' => 'checkbox' ] + $attrs + [ 'checked' => $checked ] ) . '> ' . esc_html( $label ) . '</label>';
	}
}
