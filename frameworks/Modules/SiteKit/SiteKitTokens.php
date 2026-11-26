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
			$name = is_string( $row['name'] ?? null ) ? trim( strip_tags( $row['name'] ) ) : '';
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

	private static function hex( mixed $value ): string {
		$value = is_string( $value ) ? trim( $value ) : '';

		return preg_match( '/^#(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/iD', $value ) === 1 ? strtolower( $value ) : '';
	}
}
