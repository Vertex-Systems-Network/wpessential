<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pure legacy theme migration assessment and palette/scalar ownership contract.
 */
final class SiteKitLegacyPreview {

	public const HANDOFF_MOD = 'wpessential_site_kit_palette_handoff';

	/** @return list<string> */
	public static function paletteKeys(): array {
		return array_values(
			array_filter(
				array_keys( SiteKitSchema::fields() ),
				static fn ( string $key ): bool => str_starts_with( $key, 'color_' ) || str_starts_with( $key, 'dark_' )
			)
		);
	}

	/**
	 * Explicitly handed-off fields stay canonical even after a reset. Blank legacy fields
	 * need no migration. Nonempty, not-yet-handed-off fields remain with the legacy theme.
	 *
	 * @param array<string,mixed> $legacy
	 * @param list<string> $handedOff
	 * @return list<string>
	 */
	public static function ownedKeys( array $legacy, array $handedOff ): array {
		return array_values(
			array_filter(
				self::paletteKeys(),
				static fn ( string $key ): bool => in_array( $key, $handedOff, true ) || ! isset( $legacy[ $key ] ) || $legacy[ $key ] === ''
			)
		);
	}

	/**
	 * Only valid legacy palette values may be handed off. Conflicting canonical values
	 * win; the legacy source is retained unchanged for rollback.
	 *
	 * @param array<string,mixed> $legacy
	 * @return list<string>
	 */
	public static function handoffKeys( array $legacy ): array {
		return array_values(
			array_filter(
				self::paletteKeys(),
				static fn ( string $key ): bool => SiteKitSanitizer::colour( $legacy[ $key ] ?? '' ) !== ''
			)
		);
	}

	/**
	 * A new ownership marker requires exact readback of the expected canonical value.
	 * Existing conflict winners are included in expected; reset markers are handled
	 * separately and do not need a stored value.
	 *
	 * @param list<string> $eligible
	 * @param array<string,mixed> $expected
	 * @param array<string,mixed> $stored
	 * @return list<string>
	 */
	public static function confirmedReadback( array $eligible, array $expected, array $stored ): array {
		$confirmed = [];
		foreach ( $eligible as $key ) {
			if ( array_key_exists( $key, $expected ) && array_key_exists( $key, $stored ) && $stored[ $key ] === $expected[ $key ] ) {
				$confirmed[] = $key;
			}
		}

		return array_values( array_unique( $confirmed ) );
	}

	/** Whether the active companion implements the explicit ownership contract. */
	public static function supported(): bool {
		return function_exists( 'get_template' ) && get_template() === 'hi-wpessential' && function_exists( 'apply_filters' ) && apply_filters( 'wpessential_site_kit_handoff_contract', 0 ) === 1;
	}

	/** @return list<string> */
	public static function themeOwnedKeys(): array {
		if ( ! self::supported() ) {
			return [];
		}
		$legacy = get_theme_mod( 'wpessential_site', [] );
		$handed = get_theme_mod( self::HANDOFF_MOD, [] );
		$legacy = is_array( $legacy ) ? $legacy : [];
		$handed = is_array( $handed ) ? array_values( array_filter( $handed, 'is_string' ) ) : [];
		return array_values( array_unique( array_merge( self::ownedKeys( $legacy, $handed ), SiteKitLegacyScalarPreview::confirmedKeys( [], [], $handed ), SiteKitLegacyFontPreview::ownedKeys( $handed ), SiteKitLegacyBreakpointPreview::model() === [] ? [] : [ 'breakpoints' ] ) ) );
	}

	/**
	 * @param array<string,mixed> $legacy
	 * @param array<string,mixed> $canonical
	 * @return array{ready:array<string,string>,conflicts:list<string>,deferred:list<string>}
	 */
	public static function assess( array $legacy, array $canonical ): array {
		$ready     = [];
		$conflicts = [];
		$deferred  = [];
		foreach ( $legacy as $key => $value ) {
			if ( $value === '' || $value === null || $value === false || $value === [] ) {
				continue;
			}
			$field = SiteKitSchema::field( $key );
			if ( $field === null || ( $field['type'] ?? '' ) !== 'color' || ( ! str_starts_with( $key, 'color_' ) && ! str_starts_with( $key, 'dark_' ) ) ) {
				$deferred[] = $key;
				continue;
			}
			$clean = SiteKitSanitizer::colour( $value );
			if ( $clean === '' ) {
				$deferred[] = $key;
				continue;
			}
			if ( array_key_exists( $key, $canonical ) ) {
				if ( $canonical[ $key ] !== $clean ) {
					$conflicts[] = $key;
				}
				continue;
			}
			$ready[ $key ] = $clean;
		}
		return [
			'ready'     => $ready,
			'conflicts' => $conflicts,
			'deferred'  => $deferred,
		];
	}
}
