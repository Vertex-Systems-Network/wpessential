<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figma: Core/Breadcrumb + Core/Page Header.
 */
final class PageHeader {

	/** @param list<array{0:string,1:?string}> $crumbs label + URL (null for the current page) */
	public static function breadcrumb( array $crumbs ): string {
		$parts = [];
		foreach ( $crumbs as [ $label, $url ] ) {
			$parts[] = $url === null
				? '<span aria-current="page">' . esc_html( $label ) . '</span>'
				: '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
		}

		return '<nav class="wpe-ui-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'wpessential' ) . '">' . implode( ' / ', $parts ) . '</nav>';
	}

	/** $actions must already be escaped HTML built from toolkit components. */
	public static function render( string $eyebrow, string $title, string $description = '', string $actions = '' ): string {
		$html  = '<header class="wpe-ui-page-header"><div class="wpe-ui-page-header__copy">';
		$html .= $eyebrow !== '' ? '<p class="wpe-ui-eyebrow">' . esc_html( $eyebrow ) . '</p>' : '';
		$html .= '<h1>' . esc_html( $title ) . '</h1>';
		$html .= $description !== '' ? '<p class="wpe-ui-page-header__desc">' . esc_html( $description ) . '</p>' : '';
		$html .= '</div>';
		$html .= $actions !== '' ? '<div class="wpe-ui-page-header__actions">' . $actions . '</div>' : '';

		return $html . '</header>';
	}
}
