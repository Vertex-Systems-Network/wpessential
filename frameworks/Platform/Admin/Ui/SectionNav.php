<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Two-column screen body: a sticky list of sections on the left, the option groups on the right
 * (Penpot reference: "Section nav" + "Sections").
 */
final class SectionNav {

	/**
	 * $sections: heading => list of [ group id, label, count ]. $content must already be escaped HTML.
	 *
	 * @param array<string,list<array{0:string,1:string,2:int}>> $sections
	 */
	public static function layout( string $label, array $sections, string $content ): string {
		$html = '<div class="wpe-ui-sections"><nav class="wpe-ui-sections__nav" aria-label="' . esc_attr( $label ) . '">';
		foreach ( $sections as $heading => $links ) {
			$html .= '<p class="wpe-ui-sections__heading">' . esc_html( $heading ) . '</p><ul>';
			foreach ( $links as [ $id, $text, $count ] ) {
				$html .= '<li><a href="#' . esc_attr( $id ) . '">' . esc_html( $text ) . Badge::count( $count ) . '</a></li>';
			}
			$html .= '</ul>';
		}

		return $html . '</nav><div class="wpe-ui-sections__body">' . $content . '</div></div>';
	}
}
