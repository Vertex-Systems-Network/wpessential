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
