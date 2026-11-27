<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read-only font migration assessment. Bundled fonts require a verified companion
 * preset reference; never turn a bundled font into a remote font implicitly.
 */
final class SiteKitLegacyFontPreview {

	private const ROLES = [ 'font_body', 'font_heading', 'font_mono' ];

	/** @return array<string,string> */
	public static function bundledReferences(): array {
		$out = [];
		foreach ( SiteKitFonts::bundledPresets() as $family ) {
			$out[ (string) $family['name'] ] = 'theme:' . $family['slug'];
		}
		return $out;
	}

	/**
	 * Only locally bundled role references are eligible for this runtime slice.
	 * Google lists and delivery changes remain review-only.
	 *
	 * @param array<string,mixed> $legacy
	 * @return array<string,string>
	 */
	public static function bundledCandidates( array $legacy ): array {
		$refs = self::bundledReferences();
		$out  = [];
		foreach ( self::ROLES as $key ) {
			$raw = $legacy[ $key ] ?? '';
			if ( is_string( $raw ) && isset( $refs[ $raw ] ) ) {
				$out[ $key ] = $refs[ $raw ];
			}
		}
		return $out;
	}

	/** The extended contract explicitly supports suppressing legacy extra font lists. */
	public static function remoteSupported(): bool {
		return SiteKitLegacyPreview::supported() && apply_filters( 'wpessential_site_kit_remote_font_handoff', false ) === true;
	}

	/**
	 * Remote-only slice. Local cache ownership is intentionally not transferred:
	 * copying a local selection without its cached files can cause a remote request.
	 *
	 * @param array<string,mixed> $legacy
	 * @param array<string,mixed> $canonical
	 * @return array<string,string>
	 */
	public static function remoteCandidates( array $legacy, array $canonical ): array {
		if ( SiteKitFonts::local( $legacy ) || SiteKitFonts::local( $canonical ) ) {
			return [];
		}
		return self::assess( $legacy, [ 'fonts_delivery' => 'google' ], [] )['ready'];
	}

	/**
	 * @param array<string,mixed> $legacy
	 * @param array<string,mixed> $canonical
	 * @return array<string,string>
	 */
	public static function candidates( array $legacy, array $canonical ): array {
		$bundled = self::bundledCandidates( $legacy );
		return self::remoteSupported() ? $bundled + self::remoteCandidates( $legacy, $canonical ) : $bundled;
	}

	/**
	 * @param list<string> $handed
	 * @return list<string>
	 */
	public static function ownedKeys( array $handed ): array {
		$eligible = self::bundledReferences() === [] ? [] : self::ROLES;
		if ( self::remoteSupported() ) {
			$eligible = array_merge( $eligible, [ 'fonts_extra' ] );
		}
		return array_values( array_intersect( $handed, $eligible ) );
	}

	/**
	 * @param array<string,mixed> $legacy
	 * @param array<string,mixed> $canonical
	 * @param array<string,string> $bundled Exact theme family name => verified theme:slug reference.
	 * @return array{ready:array<string,string>,conflicts:list<string>,unchanged:list<string>,deferred:list<string>}
	 */
	public static function assess( array $legacy, array $canonical, array $bundled ): array {
		$ready     = [];
		$conflicts = [];
		$unchanged = [];
		$deferred  = [];
		$sameDelivery = SiteKitFonts::local( $legacy ) === SiteKitFonts::local( $canonical );
		foreach ( [ 'font_body', 'font_heading', 'font_mono', 'fonts_extra' ] as $key ) {
			$raw = $legacy[ $key ] ?? '';
			if ( $raw === '' || $raw === false ) {
				continue;
			}
			$clean = '';
			if ( is_string( $raw ) ) {
				if ( $key === 'fonts_extra' ) {
					$families = array_map( 'trim', explode( '|', $raw ) );
					$valid    = SiteKitFonts::extraFamilies( $raw );
					// Never silently drop unknown entries or change the delivery mode.
					if ( $sameDelivery && array_values( array_unique( $families ) ) === $valid ) {
						$clean = implode( '|', $valid );
					}
				} elseif ( str_starts_with( $raw, 'google:' ) ) {
					if ( $sameDelivery && SiteKitFonts::exists( substr( $raw, 7 ) ) ) {
						$clean = $raw;
					}
				} elseif ( isset( $bundled[ $raw ] ) && preg_match( '/^theme:[a-z0-9-]+$/', $bundled[ $raw ] ) === 1 ) {
					$clean = $bundled[ $raw ];
				}
			}
			if ( $clean === '' ) {
				$deferred[] = $key;
				continue;
			}
			if ( array_key_exists( $key, $canonical ) ) {
				$existing = $key === 'fonts_extra' ? implode( '|', SiteKitFonts::extraFamilies( $canonical[ $key ] ) ) : $canonical[ $key ];
				if ( $existing === $clean ) {
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
