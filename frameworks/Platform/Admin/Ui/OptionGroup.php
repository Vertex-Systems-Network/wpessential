<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collapsible option group used by every settings-style screen (Penpot reference: "Group / …").
 * Summary: title, row count, tier chip and contract key; body: one option row per setting.
 */
final class OptionGroup {

	/**
	 * $rows must already be escaped HTML built with row(). $count overrides the badge when a row holds a list (e.g. a table).
	 *
	 * @param list<string> $rows
	 */
	public static function render( string $id, string $title, string $tier, string $key, array $rows, bool $open = true, ?int $count = null ): string {
		$html  = '<details class="wpe-ui-group" id="' . esc_attr( $id ) . '"' . ( $open ? ' open' : '' ) . '><summary>';
		$html .= '<span class="wpe-ui-group__title">' . esc_html( $title ) . '</span>' . Badge::count( $count ?? count( $rows ) );
		$html .= $tier !== '' ? '<span class="wpe-ui-chip wpe-ui-chip--' . esc_attr( strtolower( $tier ) ) . '">' . esc_html( $tier ) . '</span>' : '';
		$html .= $key !== '' ? '<code class="wpe-ui-code">' . esc_html( $key ) . '</code>' : '';

		return $html . '</summary>' . implode( '', $rows ) . '</details>';
	}

	/** One setting: label and help on the left, the control on the right. $control must already be escaped HTML. */
	public static function row( string $label, string $help, string $control ): string {
		$html  = '<div class="wpe-ui-option"><div class="wpe-ui-option__copy"><span class="wpe-ui-option__label">' . esc_html( $label ) . '</span>';
		$html .= $help !== '' ? '<span class="wpe-ui-muted">' . esc_html( $help ) . '</span>' : '';

		return $html . '</div><div class="wpe-ui-option__meta">' . $control . '</div></div>';
	}

	/** Read-only value with a lock, for diagnostics and values the screen cannot change. */
	public static function readonly( string $value ): string {
		return '<span class="wpe-ui-readonly">' . Icon::render( 'lock', 14 ) . '<code>' . esc_html( $value ) . '</code></span>';
	}
}
