<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cleans one Site Kit value for its field type. Returns '' (or []) for "use the theme default".
 * Composite values (typography, background, gradient, shadow, dimensions, responsive ranges)
 * are kept as arrays of CSS-safe strings and numbers.
 */
final class SiteKitSanitizer {

	/** @param array<string,mixed> $field */
	public static function clean( array $field, mixed $value ): mixed {
		$type = (string) ( $field['type'] ?? 'text' );
		// The admin controls and AI clients send composite values as JSON strings.
		if ( is_string( $value ) && str_starts_with( ltrim( $value ), '{' ) ) {
			$decoded = json_decode( $value, true );
			$value   = is_array( $decoded ) ? $decoded : '';
		}

		if ( in_array( $type, [ 'dimensions', 'typography', 'background', 'gradient', 'shadow' ], true ) || ( $type === 'range' && is_array( $value ) ) ) {
			return is_array( $value ) ? self::tree( $value ) : [];
		}

		return match ( $type ) {
			'color'            => self::colour( $value ),
			'toggle'           => ( $value === true || $value === 1 || $value === '1' || $value === 'on' ),
			'range'            => is_numeric( $value ) ? self::number( $value, $field ) : '',
			'select', 'choose' => is_scalar( $value ) && array_key_exists( (string) $value, (array) ( $field['choices'] ?? [] ) ) ? (string) $value : '',
			'font'             => self::font( $value ),
			'fonts'            => implode( '|', array_filter( array_map( [ self::class, 'font' ], explode( '|', is_scalar( $value ) ? (string) $value : '' ) ), static fn ( string $f ): bool => $f !== '' && ! str_starts_with( $f, 'theme:' ) ) ),
			default            => self::text( $value ),
		};
	}

	/** A hex colour, or a CSS colour keyword/var() limited to safe characters. */
	public static function colour( mixed $value ): string {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';
		if ( preg_match( '/^#(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value ) === 1 ) {
			return strtolower( $value );
		}
		if ( preg_match( '/^(?:rgba?|hsla?)\([0-9.,%\s\/]+\)$/i', $value ) === 1 || preg_match( '/^var\(--[a-z0-9-]+\)$/i', $value ) === 1 ) {
			return $value;
		}

		return '';
	}

	/** Font reference: "google:Family", "theme:slug" or "". */
	public static function font( mixed $value ): string {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';
		if ( str_starts_with( $value, 'theme:' ) ) {
			$slug = strtolower( (string) preg_replace( '/[^a-z0-9-]/i', '', substr( $value, 6 ) ) );

			return $slug === '' ? '' : 'theme:' . $slug;
		}
		$family = str_starts_with( $value, 'google:' ) ? substr( $value, 7 ) : $value;
		$family = trim( (string) preg_replace( '/[^A-Za-z0-9 ]/', '', $family ) );

		return $family !== '' && SiteKitFonts::exists( $family ) ? 'google:' . $family : '';
	}

	/** @param array<string,mixed> $field */
	private static function number( mixed $value, array $field ): float|int {
		$number = 0 + $value;
		if ( isset( $field['min'], $field['max'] ) ) {
			$number = max( (float) $field['min'], min( (float) $field['max'], (float) $number ) );
		}

		return (float) $number === floor( (float) $number ) ? (int) $number : round( (float) $number, 4 );
	}

	private static function text( mixed $value ): string {
		$value = is_scalar( $value ) ? (string) $value : '';

		return function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $value ) : trim( strip_tags( $value ) );
	}

	/**
	 * Composite value: keys [A-Za-z0-9_-], CSS-safe strings, numbers and booleans, max depth 3.
	 *
	 * @param array<mixed> $value
	 * @return array<string,mixed>
	 */
	public static function tree( array $value, int $depth = 0 ): array {
		if ( $depth > 3 ) {
			return [];
		}
		$out = [];
		foreach ( $value as $key => $item ) {
			$key = (string) preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $key );
			if ( $key === '' ) {
				continue;
			}
			if ( is_array( $item ) ) {
				$item = self::tree( $item, $depth + 1 );
			} elseif ( $key === 'url' ) {
				$item = function_exists( 'esc_url_raw' ) ? esc_url_raw( (string) $item ) : ( filter_var( (string) $item, FILTER_VALIDATE_URL ) ? (string) $item : '' );
			} elseif ( ! is_bool( $item ) && ! is_int( $item ) && ! is_float( $item ) ) {
				$item = (string) preg_replace( '/[^a-zA-Z0-9#%.,()\/ :_\-]/', '', is_scalar( $item ) ? (string) $item : '' );
			}
			if ( $item !== '' && $item !== null && $item !== [] ) {
				$out[ $key ] = $item;
			}
		}

		return $out;
	}
}
