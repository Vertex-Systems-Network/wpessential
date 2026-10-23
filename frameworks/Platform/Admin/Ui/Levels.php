<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figma: UI/Tabs & navigation (Kind=Levels). The Essential / Advanced / Expert switch used by
 * builder screens so non-technical users see only what most sites need. Each level button keeps
 * its one-word accessible name; the plain-language description is attached with aria-describedby.
 * Sections opt in with data-wpessential-{prefix}-tier-min="advanced|expert".
 */
final class Levels {

	/** @return array<string,array{0:string,1:string}> Level => [name, description]. */
	public static function levels(): array {
		return [
			'essential' => [ __( 'Essential', 'wpessential' ), __( 'What most sites need. Safe defaults for everything else.', 'wpessential' ) ],
			'advanced'  => [ __( 'Advanced', 'wpessential' ), __( 'Menus, web addresses, labels and visibility.', 'wpessential' ) ],
			'expert'    => [ __( 'Expert', 'wpessential' ), __( 'Permissions, REST API and developer settings.', 'wpessential' ) ],
		];
	}

	public static function render( string $prefix, string $status ): string {
		$html = '<div class="wpe-ui-levels wpessential-' . esc_attr( $prefix ) . '-tier-navigation" role="group" aria-label="' . esc_attr__( 'Settings level', 'wpessential' ) . '">';
		foreach ( self::levels() as $tier => [ $label, $description ] ) {
			$active = $tier === 'essential';
			$descId = 'wpessential-' . $prefix . '-tier-' . $tier . '-desc';
			$html  .= '<div class="wpe-ui-levels__item' . ( $active ? ' is-active' : '' ) . '"><button type="button" class="wpe-ui-levels__button" data-wpessential-' . esc_attr( $prefix ) . '-tier="' . esc_attr( $tier ) . '" aria-pressed="' . ( $active ? 'true' : 'false' ) . '" aria-describedby="' . esc_attr( $descId ) . '">' . esc_html( $label ) . '</button>'
				. '<span class="wpe-ui-levels__desc" id="' . esc_attr( $descId ) . '">' . esc_html( $description ) . '</span></div>';
		}
		$html .= '</div>';

		return $html . '<p id="wpessential-' . esc_attr( $prefix ) . '-tier-status" class="description wpe-ui-muted" aria-live="polite">' . esc_html( $status ) . '</p>';
	}
}
