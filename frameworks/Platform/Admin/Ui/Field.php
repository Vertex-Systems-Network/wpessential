<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figma: UI/Input (Text input, Select, Number, Textarea). Label, control and help text.
 */
final class Field {

	/** @param array<string,string|int|bool|null> $attrs */
	public static function input( string $id, string $label, array $attrs = [], string $help = '' ): string {
		return self::wrap( $id, $label, '<input' . Attributes::render( [ 'type' => $attrs['type'] ?? 'text' ] + $attrs + [ 'id' => $id ] ) . '>', $help );
	}

	/**
	 * @param array<string,string>               $options Value => label.
	 * @param array<string,string|int|bool|null> $attrs
	 */
	public static function select( string $id, string $label, array $options, string $selected = '', array $attrs = [], string $help = '' ): string {
		$html = '';
		foreach ( $options as $value => $text ) {
			$html .= '<option value="' . esc_attr( (string) $value ) . '"' . selected( (string) $value, $selected, false ) . '>' . esc_html( $text ) . '</option>';
		}

		return self::wrap( $id, $label, '<select' . Attributes::render( $attrs + [ 'id' => $id ] ) . '>' . $html . '</select>', $help );
	}

	/** Label + an already escaped control + help. */
	public static function wrap( string $id, string $label, string $control, string $help = '' ): string {
		return '<p class="wpe-ui-field"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>' . $control
			. ( $help !== '' ? '<span class="description wpe-ui-muted">' . esc_html( $help ) . '</span>' : '' ) . '</p>';
	}

	/** Toolbar search box (no visible label). */
	public static function search( string $id, string $placeholder, array $attrs = [] ): string {
		return '<input' . Attributes::render(
			[
				'type'        => 'search',
				'id'          => $id,
				'class'       => 'wpe-ui-input',
				'placeholder' => $placeholder,
				'aria-label'  => $placeholder,
			] + $attrs
		) . '>';
	}
}
