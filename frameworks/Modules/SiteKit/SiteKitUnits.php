<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Units offered in size fields (ADR-0239 WP4). Site Tools Kit → Layout → Units picks which CSS
 * units appear and defines custom units as "1 name = factor base", e.g. 1 hm = 2 px.
 * The compiler converts custom units, so the browser only ever sees real CSS units.
 */
final class SiteKitUnits {

	public const BUILTIN = [ 'px', 'em', 'rem', '%', 'vw', 'vh', 'vmin', 'vmax', 'svh', 'dvh', 'ch', 'ex', 'pt', 'cm', 'mm', 'in' ];

	public const DEFAULT_ON = [ 'px', 'em', 'rem', '%', 'vw', 'vh' ];

	/**
	 * Enabled built-in units.
	 *
	 * @param array<string,mixed> $stored The `units` value: {on: "px,em", custom: [{n, f, b}]}.
	 * @return list<string>
	 */
	public static function enabled( array $stored = [] ): array {
		if ( ! isset( $stored['on'] ) || ! is_string( $stored['on'] ) || $stored['on'] === '' ) {
			return self::DEFAULT_ON;
		}
		$on = array_values( array_intersect( self::BUILTIN, array_map( 'trim', explode( ',', $stored['on'] ) ) ) );

		return $on !== [] ? $on : self::DEFAULT_ON;
	}

	/**
	 * Valid custom units keyed by name.
	 *
	 * @param array<string,mixed> $stored
	 * @return array<string,array{factor:float,base:string}>
	 */
	public static function custom( array $stored = [] ): array {
		$out = [];
		foreach ( is_array( $stored['custom'] ?? null ) ? $stored['custom'] : [] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$name = strtolower( (string) ( $row['n'] ?? '' ) );
			$base = (string) ( $row['b'] ?? 'px' );
			if ( preg_match( '/^[a-z]{1,8}$/', $name ) !== 1 || in_array( $name, self::BUILTIN, true ) || ! in_array( $base, self::BUILTIN, true ) || ! is_numeric( $row['f'] ?? null ) || (float) $row['f'] <= 0 ) {
				continue;
			}
			$out[ $name ] = [
				'factor' => (float) $row['f'],
				'base'   => $base,
			];
		}

		return $out;
	}

	/**
	 * CSS length for a number and unit; custom units become their base unit.
	 *
	 * @param array<string,mixed> $stored
	 */
	public static function css( float|int $number, string $unit, array $stored = [] ): string {
		$custom = self::custom( $stored );
		if ( isset( $custom[ $unit ] ) ) {
			$value = round( $number * $custom[ $unit ]['factor'], 4 );

			return ( 0 + $value ) . $custom[ $unit ]['base'];
		}

		return ( 0 + $number ) . ( in_array( $unit, self::BUILTIN, true ) || $unit === '' ? $unit : 'px' );
	}
}
