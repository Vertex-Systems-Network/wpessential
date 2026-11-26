<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Responsive breakpoints (ADR-0239 WP3): desktop is the base, the others can be switched on or off
 * and given their own width in Site Tools Kit → Layout. Only enabled ones appear in device dropdowns.
 */
final class SiteKitBreakpoints {

	/** key => [label, default width, default on, "min" for wider-than or "max" for narrower-than]. */
	public const ALL = [
		'widescreen'   => [ 'Widescreen', 1920, false, 'min' ],
		'desktop'      => [ 'Desktop', 0, true, '' ],
		'laptop'       => [ 'Laptop', 1366, false, 'max' ],
		'tablet_extra' => [ 'Tablet landscape', 1200, false, 'max' ],
		'tablet'       => [ 'Tablet', 1023, true, 'max' ],
		'mobile_extra' => [ 'Phone landscape', 880, false, 'max' ],
		'mobile'       => [ 'Phone', 767, true, 'max' ],
		'mobile_small' => [ 'Small phone', 480, false, 'max' ],
	];

	/**
	 * Enabled breakpoints in cascade order (desktop first, then wider, then narrower by width).
	 *
	 * @param array<string,mixed> $stored The `breakpoints` value: key => {on?:bool, w?:int}.
	 * @return array<string,array{label:string,width:int,media:string,dir:string}>
	 */
	public static function enabled( array $stored = [] ): array {
		$out = [];
		foreach ( self::ALL as $key => [ $label, $width, $on, $dir ] ) {
			$row = is_array( $stored[ $key ] ?? null ) ? $stored[ $key ] : [];
			if ( $key !== 'desktop' && ! (bool) ( $row['on'] ?? $on ) ) {
				continue;
			}
			$w = isset( $row['w'] ) && is_numeric( $row['w'] ) ? max( 240, min( 3840, (int) $row['w'] ) ) : $width;
			$out[ $key ] = [
				'label' => $label,
				'width' => $key === 'desktop' ? 0 : $w,
				'media' => $dir === '' ? '' : sprintf( '@media (%s-width: %dpx)', $dir, $w ),
				'dir'   => $dir,
			];
		}
		// Wider-than first, then narrower-than from widest to smallest, so smaller screens win.
		uksort(
			$out,
			static function ( string $a, string $b ) use ( $out ): int {
				$rank = static fn ( array $r ): int => $r['dir'] === '' ? 0 : ( $r['dir'] === 'min' ? 1 : 2 );

				return [ $rank( $out[ $a ] ), -$out[ $a ]['width'] ] <=> [ $rank( $out[ $b ] ), -$out[ $b ]['width'] ];
			}
		);

		return $out;
	}

	/** @return array<string,string> Translated labels. */
	public static function labels(): array {
		return [
			'widescreen'   => function_exists( '__' ) ? __( 'Widescreen', 'wpessential' ) : 'Widescreen',
			'desktop'      => function_exists( '__' ) ? __( 'Desktop', 'wpessential' ) : 'Desktop',
			'laptop'       => function_exists( '__' ) ? __( 'Laptop', 'wpessential' ) : 'Laptop',
			'tablet_extra' => function_exists( '__' ) ? __( 'Tablet landscape', 'wpessential' ) : 'Tablet landscape',
			'tablet'       => function_exists( '__' ) ? __( 'Tablet', 'wpessential' ) : 'Tablet',
			'mobile_extra' => function_exists( '__' ) ? __( 'Phone landscape', 'wpessential' ) : 'Phone landscape',
			'mobile'       => function_exists( '__' ) ? __( 'Phone', 'wpessential' ) : 'Phone',
			'mobile_small' => function_exists( '__' ) ? __( 'Small phone', 'wpessential' ) : 'Small phone',
		];
	}

	/** Media query for a device key ('' for desktop or an unknown/disabled key). @param array<string,mixed> $stored */
	public static function media( string $device, array $stored = [] ): string {
		return self::enabled( $stored )[ $device ]['media'] ?? '';
	}
}
