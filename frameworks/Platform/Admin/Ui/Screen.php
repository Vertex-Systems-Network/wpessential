<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page frame shared by every WPEssential screen: toolkit wrap, top bar, then the screen body.
 */
final class Screen {

	/** $content and $notice must already be escaped HTML built from toolkit components. */
	/**
	 * $appData (Dashboard, Modules, Demo Library, Elements) feeds the React dashboard app, which
	 * takes over the view and switches between these screens with React Router. The server
	 * markup stays as the first paint and the no-JavaScript fallback.
	 *
	 * @param array<string,mixed>|null $appData
	 */
	public static function render( string $current, string $content, string $notice = '', string $surface = 'core', ?array $appData = null ): void {
		echo '<div class="wrap wpe-ui" data-wpessential-surface="' . esc_attr( $surface ) . '">';
		echo Shell::topbar( $current ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by Notice::render().
		if ( $appData === null ) {
			echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built only from escaping toolkit components.
			echo '</div>';
			return;
		}
		echo '<div class="wpe-ui-app-view" data-wpe-app-view="' . esc_attr( $current ) . '">';
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built only from escaping toolkit components.
		echo '</div>';
		$json = function_exists( 'wp_json_encode' ) ? (string) wp_json_encode( $appData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES ) : '{}';
		echo '<script type="application/json" data-wpe-app-data="' . esc_attr( $current ) . '">' . $json . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON encoded with JSON_HEX_* flags.
		echo '</div>';
	}
}
