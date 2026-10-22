<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figma: UI Kit Badge (Free / Pro).
 */
final class Badge {

	public static function edition( string $edition ): string {
		return $edition === 'pro'
			? '<span class="wpe-ui-badge wpe-ui-badge--pro">' . esc_html__( 'Premium', 'wpessential' ) . '</span>'
			: '<span class="wpe-ui-badge wpe-ui-badge--free">' . esc_html__( 'Free', 'wpessential' ) . '</span>';
	}

	public static function count( int $count ): string {
		return '<span class="wpe-ui-count">' . esc_html( (string) $count ) . '</span>';
	}
}
