<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Named global colour tokens. A reserved slug prefix protects the built-in palette.
 * Dark overrides use the same preset slug as the corresponding light colour.
 */
final class SiteKitTokens {

	public const MAX_COLOURS = 100;

	/**
	 * @return list<array{name:string,slug:string,color:string,dark:string}>
	 */
	public static function colours( mixed $value ): array {
		$rows = is_array( $value ) && is_array( $value['tokens'] ?? null ) ? $value['tokens'] : [];
		$out  = [];
		$seen = [];
		foreach ( array_slice( $rows, 0, self::MAX_COLOURS ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$name = is_string( $row['name'] ?? null ) ? trim( wp_strip_all_tags( $row['name'] ) ) : '';
			$slug = is_string( $row['slug'] ?? null ) ? strtolower( trim( $row['slug'] ) ) : '';
			if ( str_starts_with( $slug, 'wpe-custom-' ) ) {
				$slug = substr( $slug, 11 );
			}
			$color = self::hex( $row['color'] ?? '' );
			$dark  = self::hex( $row['dark'] ?? '' );
			if ( $name === '' || strlen( $name ) > 120 || preg_match( '/[[:cntrl:]]/', $name ) === 1 || preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug ) !== 1 || strlen( $slug ) > 48 || $color === '' ) {
				continue;
			}
			$slug = 'wpe-custom-' . $slug;
			if ( isset( $seen[ $slug ] ) ) {
				continue;
			}
			$seen[ $slug ] = true;
			$out[]         = [
				'name'  => $name,
				'slug'  => $slug,
				'color' => $color,
				'dark'  => $dark,
			];
		}

		return $out;
	}


	/**
	 * Named typography presets, using the existing responsive typography control value shape.
	 *
	 * @return list<array{name:string,slug:string,typography:array<string,mixed>}>
	 */
	public static function typography( mixed $value ): array {
		$rows = is_array( $value ) && is_array( $value['tokens'] ?? null ) ? $value['tokens'] : [];
		$out  = [];
		$seen = [];
		foreach ( array_slice( $rows, 0, self::MAX_COLOURS ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$name = is_string( $row['name'] ?? null ) ? trim( wp_strip_all_tags( $row['name'] ) ) : '';
			$slug = is_string( $row['slug'] ?? null ) ? strtolower( trim( $row['slug'] ) ) : '';
			if ( str_starts_with( $slug, 'wpe-custom-' ) ) {
				$slug = substr( $slug, 11 );
			}
			if ( $name === '' || strlen( $name ) > 120 || preg_match( '/[[:cntrl:]]/', $name ) === 1
				|| preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug ) !== 1 || strlen( $slug ) > 48 ) {
				continue;
			}
			$slug = 'wpe-custom-' . $slug;
			if ( isset( $seen[ $slug ] ) || ! is_array( $row['typography'] ?? null ) ) {
				continue;
			}
			$style = self::typographyStyle( $row['typography'] );
			if ( $style === [] ) {
				continue;
			}
			$seen[ $slug ] = true;
			$out[]         = [
				'name'       => $name,
				'slug'       => $slug,
				'typography' => $style,
			];
		}

		return $out;
	}

	/** @param array<mixed> $raw @return array<string,mixed> */
	private static function typographyStyle( array $raw ): array {
		$out    = [];
		$family = SiteKitSanitizer::font( $raw['family'] ?? '' );
		if ( $family !== '' ) {
			$out['family'] = $family;
		}
		foreach ( [
			'weight'     => [ '100', '200', '300', '400', '500', '600', '700', '800', '900' ],
			'transform'  => [ 'none', 'uppercase', 'lowercase', 'capitalize' ],
			'style'      => [ 'normal', 'italic' ],
			'decoration' => [ 'none', 'underline', 'line-through' ],
		] as $key => $allowed ) {
			$value = is_scalar( $raw[ $key ] ?? null ) ? (string) $raw[ $key ] : '';
			if ( in_array( $value, $allowed, true ) ) {
				$out[ $key ] = $value;
			}
		}
		foreach ( [ 'size', 'lineHeight', 'letterSpacing' ] as $key ) {
			$values = is_array( $raw[ $key ] ?? null ) ? $raw[ $key ] : [ 'desktop' => $raw[ $key ] ?? '' ];
			foreach ( array_keys( SiteKitBreakpoints::ALL ) as $device ) {
				$value = $values[ $device ] ?? '';
				$unit  = $key === 'lineHeight' ? '' : ( $key === 'letterSpacing' ? 'em' : 'px' );
				if ( is_array( $value ) ) {
					$unit  = is_string( $value['unit'] ?? null ) ? $value['unit'] : $unit;
					$value = $value['size'] ?? '';
				}
				if ( ! is_numeric( $value ) || ! is_finite( (float) $value )
					|| abs( (float) $value ) > 10000 || ( $key !== 'letterSpacing' && (float) $value <= 0 )
					|| ( $unit === '' ? $key !== 'lineHeight' : preg_match( '/^(?:[a-z]{1,8}|%)$/D', $unit ) !== 1 ) ) {
					continue;
				}
				$out[ $key ][ $device ] = [
					'size' => (float) $value,
					'unit' => $unit,
				];
			}
		}

		return $out;
	}

	/**
	 * Named shadows, with a bounded geometry and a safe colour.
	 *
	 * @return list<array{name:string,slug:string,shadow:array{x:float,y:float,blur:float,spread:float,inset:bool,color:string}}>
	 */
	public static function shadows( mixed $value ): array {
		$rows = is_array( $value ) && is_array( $value['tokens'] ?? null ) ? $value['tokens'] : [];
		$out  = [];
		$seen = [];
		foreach ( array_slice( $rows, 0, self::MAX_COLOURS ) as $row ) {
			if ( ! is_array( $row ) || ! is_array( $row['shadow'] ?? null ) ) {
				continue;
			}
			$name = is_string( $row['name'] ?? null ) ? trim( wp_strip_all_tags( $row['name'] ) ) : '';
			$slug = is_string( $row['slug'] ?? null ) ? strtolower( trim( $row['slug'] ) ) : '';
			if ( str_starts_with( $slug, 'wpe-custom-' ) ) {
				$slug = substr( $slug, 11 );
			}
			if ( $name === '' || strlen( $name ) > 120 || preg_match( '/[[:cntrl:]]/', $name ) === 1
				|| strlen( $slug ) > 48 || preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug ) !== 1 ) {
				continue;
			}
			$slug = 'wpe-custom-' . $slug;
			if ( isset( $seen[ $slug ] ) ) {
				continue;
			}
			$raw = $row['shadow'];
			$color = SiteKitSanitizer::colour( $raw['color'] ?? '' );
			if ( $color === '' ) {
				continue;
			}
			$length = static function ( string $key, float $min, float $max ) use ( $raw ): float {
				$n = $raw[ $key ] ?? 0;
				return is_numeric( $n ) && is_finite( (float) $n ) ? max( $min, min( $max, (float) $n ) ) : 0.0;
			};
			$seen[ $slug ] = true;
			$out[] = [
				'name'   => $name,
				'slug'   => $slug,
				'shadow' => [
					'x'      => $length( 'x', -100, 100 ),
					'y'      => $length( 'y', -100, 100 ),
					'blur'   => $length( 'blur', 0, 200 ),
					'spread' => $length( 'spread', -100, 100 ),
					'inset'  => in_array( $raw['inset'] ?? false, [ true, 1, '1' ], true ),
					'color'  => $color,
				],
			];
		}
		return $out;
	}

	private static function hex( mixed $value ): string {
		$value = is_string( $value ) ? trim( $value ) : '';

		return preg_match( '/^#(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/iD', $value ) === 1 ? strtolower( $value ) : '';
	}
}
