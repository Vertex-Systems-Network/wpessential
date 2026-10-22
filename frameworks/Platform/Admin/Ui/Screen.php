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
	public static function render( string $current, string $content, string $notice = '', string $surface = 'core' ): void {
		echo '<div class="wrap wpe-ui" data-wpessential-surface="' . esc_attr( $surface ) . '">';
		echo Shell::topbar( $current ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- toolkit components escape their own output.
		echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by Notice::render().
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built only from escaping toolkit components.
		echo '</div>';
	}
}
