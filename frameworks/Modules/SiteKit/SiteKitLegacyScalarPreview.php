<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Next-stage scalar legacy assessment, intentionally not wired to migration writes.
 * The reviewed theme and Kit schemas agree on these scalar ranges and default units.
 * Responsive trees, theme font identifiers and differing choices need separate decisions.
 */
final class SiteKitLegacyScalarPreview {

	private const UNITS = [
		'btn_radius'     => 'px',
		'field_radius'   => 'px',
		'radius_sm'      => 'px',
		'radius_md'      => 'px',
		'radius_lg'      => 'px',
		'radius_xl'      => 'px',
		'btn_lift'       => 'px',
		'hover_lift'     => 'px',
		'hover_duration' => 'ms',
	];

	/**
	 * Scalar keys whose saved legacy values can be transferred without conversion loss.
	 * Existing canonical conflicts remain canonical; the original value is retained.
	 *
	 * @param array<string,mixed> $legacy
	 * @return list<string>
	 */
	public static function handoffKeys( array $legacy ): array {
		return array_keys( self::assess( $legacy, [] )['ready'] );
	}

	/**
	 * Only acknowledge keys already transferred or successfully persisted in canonical storage.
	 * A transferred key remains owned even when its canonical setting is subsequently reset.
	 *
	 * @param array<string,mixed> $legacy
	 * @param array<string,mixed> $stored
	 * @param list<string> $previous
	 * @return list<string>
	 */
	public static function confirmedKeys( array $legacy, array $stored, array $previous ): array {
		$confirmed = array_values( array_intersect( $previous, array_keys( self::UNITS ) ) );
		foreach ( self::handoffKeys( $legacy ) as $key ) {
			if ( array_key_exists( $key, $stored ) ) {
				$confirmed[] = $key;
			}
		}

		return array_values( array_unique( $confirmed ) );
	}

	/**
	 * Assess without writing, clamping out-of-range values or silently replacing canonical data.
	 *
	 * @param array<string,mixed> $legacy
	 * @param array<string,mixed> $canonical
	 * @return array{ready:array<string,float|int>,conflicts:list<string>,unchanged:list<string>,deferred:list<string>}
	 */
	public static function assess( array $legacy, array $canonical ): array {
		$ready     = [];
		$conflicts = [];
		$unchanged = [];
		$deferred  = [];
		foreach ( $legacy as $key => $raw ) {
			if ( $raw === '' || $raw === null || $raw === false || $raw === [] ) {
				continue;
			}
			$field = SiteKitSchema::field( $key );
			if ( ! isset( self::UNITS[ $key ] ) || $field === null || ( $field['type'] ?? '' ) !== 'range' || ! empty( $field['responsive'] ) || ( $field['unit'] ?? '' ) !== self::UNITS[ $key ] || ! is_numeric( $raw ) || ! isset( $field['min'], $field['max'] ) ) {
				$deferred[] = $key;
				continue;
			}
			$number = (float) $raw;
			if ( ! is_finite( $number ) || $number < (float) $field['min'] || $number > (float) $field['max'] ) {
				$deferred[] = $key;
				continue;
			}
			$clean = SiteKitSanitizer::clean( $field, $raw );
			if ( ( ! is_int( $clean ) && ! is_float( $clean ) ) || (float) $clean !== $number ) {
				$deferred[] = $key;
				continue;
			}
			if ( array_key_exists( $key, $canonical ) ) {
				if ( is_numeric( $canonical[ $key ] ) && (float) $canonical[ $key ] === $number ) {
					$unchanged[] = $key;
				} else {
					$conflicts[] = $key;
				}
				continue;
			}
			$ready[ $key ] = $clean;
		}
		return [
			'ready'     => $ready,
			'conflicts' => $conflicts,
			'unchanged' => $unchanged,
			'deferred'  => $deferred,
		];
	}
}
