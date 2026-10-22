<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figma: Core/Stat Tile.
 */
final class StatTile {

	public static function render( string $value, string $label ): string {
		return '<div class="wpe-ui-stat"><span class="wpe-ui-stat__value">' . esc_html( $value ) . '</span><span class="wpe-ui-stat__label">' . esc_html( $label ) . '</span></div>';
	}

	/** @param list<array{0:string,1:string}> $tiles */
	public static function row( array $tiles ): string {
		$html = '';
		foreach ( $tiles as [ $value, $label ] ) {
			$html .= self::render( $value, $label );
		}

		return '<div class="wpe-ui-stats">' . $html . '</div>';
	}
}
