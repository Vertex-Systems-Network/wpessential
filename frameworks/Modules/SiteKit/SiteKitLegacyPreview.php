<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pure legacy theme migration assessment and palette ownership contract.
 * Only a narrow set of semantically matching colour fields is import-ready.
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

	/** @return list<string> */
	public static function themeOwnedKeys(): array {
		if ( get_template() !== 'hi-wpessential' ) {
			return [];
		}
		$legacy = get_theme_mod( 'wpessential_site', [] );
		$handed = get_theme_mod( self::HANDOFF_MOD, [] );
		return self::ownedKeys( is_array( $legacy ) ? $legacy : [], is_array( $handed ) ? array_values( array_filter( $handed, 'is_string' ) ) : [] );
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
