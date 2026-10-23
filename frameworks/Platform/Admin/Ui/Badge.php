<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Figma: UI/Pill (Kind=Free badge / Premium badge).
 */
final class Badge {

	public static function edition( string $edition ): string {
		return $edition === 'pro'
			? '<span class="wpe-ui-badge wpe-ui-badge--pro">' . esc_html__( 'Premium', 'wpessential' ) . '</span>'
			: '<span class="wpe-ui-badge wpe-ui-badge--free">' . esc_html__( 'Free', 'wpessential' ) . '</span>';
	}

	/** A Free or Premium badge with custom text, e.g. "12 Free". */
	public static function label( string $edition, string $text ): string {
		return '<span class="wpe-ui-badge wpe-ui-badge--' . ( $edition === 'pro' ? 'pro' : 'free' ) . '">' . esc_html( $text ) . '</span>';
	}

	public static function count( int $count ): string {
		return '<span class="wpe-ui-count">' . esc_html( (string) $count ) . '</span>';
	}
}
